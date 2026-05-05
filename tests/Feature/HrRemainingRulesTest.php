<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Company;
use App\Models\HrJob;
use App\Models\JobApplication;
use App\Models\Task;
use App\Models\User;
use App\Services\AssessmentAttemptService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HrRemainingRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_schedule_interview_after_shortlisting_and_action_is_logged(): void
    {
        [$hr, $candidate, $application] = $this->applicationFixture('shortlisted');

        Sanctum::actingAs($hr);

        $response = $this->postJson("/api/hr/applications/{$application->id}/schedule-interview", [
            'date' => now()->addDay()->toDateString(),
            'time' => '14:30',
            'mode' => 'online',
        ]);

        $response->assertOk()
            ->assertJsonPath('application.status', 'interview_scheduled')
            ->assertJsonPath('interview.mode', 'online');

        $this->assertDatabaseHas('interviews', [
            'application_id' => $application->id,
            'time' => '14:30',
            'mode' => 'online',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $hr->id,
            'action' => 'schedule_interview',
            'module' => 'hr_applications',
        ]);
    }

    public function test_hr_cannot_schedule_interview_before_candidate_is_ready(): void
    {
        [$hr, $candidate, $application] = $this->applicationFixture('applied');

        Sanctum::actingAs($hr);

        $response = $this->postJson("/api/hr/applications/{$application->id}/schedule-interview", [
            'date' => now()->addDay()->toDateString(),
            'time' => '14:30',
            'mode' => 'online',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Interview can only be scheduled after candidate is shortlisted or second task is assigned.');

        $this->assertDatabaseMissing('interviews', [
            'application_id' => $application->id,
        ]);
    }

    public function test_hr_can_review_second_round_task_and_action_is_logged(): void
    {
        [$hr, $candidate, $application] = $this->applicationFixture('second_task_assigned');

        $task = Task::create([
            'application_id' => $application->id,
            'title' => 'Case Study',
            'description' => 'Solve this practical assignment.',
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($hr);

        $response = $this->postJson("/api/hr/applications/{$application->id}/review-task", [
            'status' => 'passed',
        ]);

        $response->assertOk()
            ->assertJsonPath('task.status', 'passed');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'passed',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $hr->id,
            'action' => 'review_task',
            'module' => 'hr_applications',
        ]);
    }

    public function test_hr_cannot_schedule_interview_until_second_round_task_is_passed(): void
    {
        [$hr, $candidate, $application] = $this->applicationFixture('second_task_assigned');

        $task = Task::create([
            'application_id' => $application->id,
            'title' => 'Case Study',
            'description' => 'Solve this practical assignment.',
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($hr);

        $this->postJson("/api/hr/applications/{$application->id}/schedule-interview", [
            'date' => now()->addDay()->toDateString(),
            'time' => '14:30',
            'mode' => 'online',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Second round task must be passed before scheduling interview.');

        $task->update(['status' => 'passed']);

        $this->postJson("/api/hr/applications/{$application->id}/schedule-interview", [
            'date' => now()->addDay()->toDateString(),
            'time' => '14:30',
            'mode' => 'online',
        ])->assertOk()
            ->assertJsonPath('application.status', 'interview_scheduled');
    }

    public function test_candidate_starting_assessment_locks_assessment_and_blocks_hr_edits(): void
    {
        [$hr, $candidate, $application, $assessment] = $this->applicationFixture('applied');

        $submission = app(AssessmentAttemptService::class)->start($application, $candidate);

        $this->assertSame('started', $submission->status);

        $this->assertDatabaseHas('assessments', [
            'id' => $assessment->id,
            'status' => 'locked',
        ]);

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'assessment_pending',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $candidate->id,
            'action' => 'start_assessment',
            'module' => 'assessment_attempts',
        ]);

        Sanctum::actingAs($hr);

        $editResponse = $this->putJson("/api/hr/assessments/{$assessment->id}", [
            'title' => 'Edited title',
        ]);

        $editResponse->assertStatus(422)
            ->assertJsonPath('message', 'Assessment is locked and cannot be modified after attempts start.');
    }

    public function test_one_attempt_and_failed_cooldown_rules_are_enforced(): void
    {
        [$hr, $candidate, $application, $assessment] = $this->applicationFixture('applied', cooldownDays: 5);
        $attemptService = app(AssessmentAttemptService::class);

        $attemptService->start($application, $candidate);

        try {
            $attemptService->start($application->fresh(), $candidate);
            $this->fail('Second assessment attempt should be blocked.');
        } catch (HttpResponseException $exception) {
            $this->assertSame(422, $exception->getResponse()->getStatusCode());
            $this->assertSame('Only one assessment attempt is allowed.', $exception->getResponse()->getData(true)['message']);
        }

        $submission = $attemptService->submit($application->fresh(), $candidate, 42);

        $this->assertSame('failed', $submission->status);

        $application->refresh();

        $this->assertTrue($application->cooldown_until->isFuture());
        $this->assertSame('failed', $application->status);
        $this->assertTrue($application->cooldown_until->isSameDay(now()->addDays(5)));

        try {
            $attemptService->submit($application->fresh(), $candidate, 90);
            $this->fail('Resubmitting assessment should be blocked.');
        } catch (HttpResponseException $exception) {
            $this->assertSame(422, $exception->getResponse()->getStatusCode());
            $this->assertSame('Assessment has already been submitted.', $exception->getResponse()->getData(true)['message']);
        }

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $candidate->id,
            'action' => 'submit_assessment',
            'module' => 'assessment_attempts',
        ]);
    }

    private function applicationFixture(string $status = 'applied', int $cooldownDays = 7): array
    {
        $company = Company::create([
            'name' => 'Talent Co',
            'email' => uniqid('company').'@example.com',
            'status' => 'approved',
            'trust_level' => 'standard',
        ]);

        $hr = User::create([
            'name' => 'HR User',
            'email' => uniqid('hr').'@example.com',
            'password' => 'password',
            'role' => 'hr',
            'status' => 'active',
            'company_id' => $company->id,
        ]);

        $candidate = User::create([
            'name' => 'Candidate User',
            'email' => uniqid('candidate').'@example.com',
            'password' => 'password',
            'role' => 'candidate',
            'status' => 'active',
        ]);

        $assessment = Assessment::create([
            'hr_id' => $hr->id,
            'title' => 'Frontend Assessment',
            'time_limit' => 45,
            'one_attempt_only' => true,
            'auto_submit' => true,
            'randomize_questions' => false,
            'cooldown_days' => $cooldownDays,
            'status' => 'active',
        ]);

        $job = HrJob::create([
            'company_id' => $company->id,
            'hr_id' => $hr->id,
            'title' => 'Frontend Developer',
            'type' => 'full_time',
            'work_mode' => 'remote',
            'location' => 'Lahore',
            'skills' => ['React', 'JS'],
            'description' => 'Build frontend apps.',
            'candidates_required' => 1,
            'hiring_urgency' => 'medium',
            'assessment_id' => $assessment->id,
            'status' => 'pending_approval',
        ]);

        $application = JobApplication::create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'status' => $status,
        ]);

        return [$hr, $candidate, $application, $assessment];
    }
}
