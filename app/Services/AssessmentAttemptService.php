<?php

namespace App\Services;

use App\Models\AssessmentSubmission;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class AssessmentAttemptService
{
    public function start(JobApplication $application, User $candidate, ?Request $request = null): AssessmentSubmission
    {
        $this->authorizeCandidateApplication($application, $candidate);
        $assessment = $this->assessmentFor($application);

        if ($application->cooldown_until && $application->cooldown_until->isFuture()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Candidate is in cooldown and cannot reattempt yet.',
                'cooldown_until' => $application->cooldown_until,
            ], 422));
        }

        $existingSubmission = AssessmentSubmission::where('assessment_id', $assessment->id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if ($existingSubmission && $assessment->one_attempt_only) {
            throw new HttpResponseException(response()->json([
                'message' => 'Only one assessment attempt is allowed.',
            ], 422));
        }

        $submission = $existingSubmission ?: AssessmentSubmission::create([
            'assessment_id' => $assessment->id,
            'candidate_id' => $candidate->id,
            'score' => 0,
            'status' => 'started',
            'started_at' => now(),
        ]);

        if ($assessment->status !== 'locked') {
            $assessment->update(['status' => 'locked']);
        }

        $application->update(['status' => 'assessment_pending']);

        ActivityLogger::log(
            'start_assessment',
            'assessment_attempts',
            "Candidate {$candidate->email} started assessment {$assessment->id} for application {$application->id}.",
            $request,
            $candidate->id
        );

        return $submission->fresh(['assessment:id,title,time_limit,status']);
    }

    public function submit(
        JobApplication $application,
        User $candidate,
        float $score,
        float $passingScore = 60,
        bool $autoSubmitted = false,
        ?Request $request = null
    ): AssessmentSubmission {
        $this->authorizeCandidateApplication($application, $candidate);
        $assessment = $this->assessmentFor($application);

        $submission = AssessmentSubmission::where('assessment_id', $assessment->id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if (! $submission) {
            throw new HttpResponseException(response()->json([
                'message' => 'Assessment attempt must be started before submission.',
            ], 422));
        }

        if ($submission->submitted_at && $assessment->one_attempt_only) {
            throw new HttpResponseException(response()->json([
                'message' => 'Assessment has already been submitted.',
            ], 422));
        }

        $passed = $score >= $passingScore;
        $submissionStatus = $autoSubmitted ? 'auto_submitted' : ($passed ? 'passed' : 'failed');

        $submission->update([
            'score' => $score,
            'status' => $submissionStatus,
            'submitted_at' => now(),
        ]);

        $applicationUpdates = [
            'status' => $passed ? 'passed' : 'failed',
        ];

        if (! $passed) {
            $applicationUpdates['cooldown_until'] = now()->addDays($assessment->cooldown_days);
        }

        $application->update($applicationUpdates);

        ActivityLogger::log(
            'submit_assessment',
            'assessment_attempts',
            "Candidate {$candidate->email} submitted assessment {$assessment->id} for application {$application->id} with score {$score}. Result: {$submissionStatus}.",
            $request,
            $candidate->id
        );

        return $submission->fresh(['assessment:id,title,time_limit,status']);
    }

    private function authorizeCandidateApplication(JobApplication $application, User $candidate): void
    {
        if ($candidate->role !== 'candidate' || $application->candidate_id !== $candidate->id) {
            throw new HttpResponseException(response()->json([
                'message' => 'Application not found for this candidate.',
            ], 404));
        }
    }

    private function assessmentFor(JobApplication $application)
    {
        $application->loadMissing('job.assessment');

        if (! $application->job || ! $application->job->assessment) {
            throw new HttpResponseException(response()->json([
                'message' => 'No assessment is attached to this job.',
            ], 422));
        }

        return $application->job->assessment;
    }
}
