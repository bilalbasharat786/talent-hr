<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssessmentSubmission;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\Task;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $hr = $request->user();

        $applications = JobApplication::with([
            'candidate:id,name,email,status',
            'job:id,hr_id,company_id,title,assessment_id,status',
        ])
            ->whereHas('job', function ($query) use ($hr) {
                $query->where('hr_id', $hr->id)
                    ->where('company_id', $hr->company_id);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }

    public function show(Request $request, JobApplication $application)
    {
        $this->authorizeApplicationOwnership($request, $application);

        $application->load([
            'candidate:id,name,email,status,company_id',
            'job:id,hr_id,company_id,title,assessment_id,status',
            'task',
            'interview',
        ]);

        $assessmentSubmission = null;
        $scoreBreakdown = null;

        if ($application->job && $application->job->assessment_id) {
            $assessmentSubmission = AssessmentSubmission::where('assessment_id', $application->job->assessment_id)
                ->where('candidate_id', $application->candidate_id)
                ->first();

            if ($assessmentSubmission) {
                $scoreBreakdown = [
                    'assessment_id' => $assessmentSubmission->assessment_id,
                    'score' => $assessmentSubmission->score,
                    'status' => $assessmentSubmission->status,
                ];
            }
        }

        $candidateActivityLogs = ActivityLog::where('user_id', $application->candidate_id)
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'application' => $application,
            'assessment_score_breakdown' => $scoreBreakdown,
            'skill_match_percentage' => $application->skill_match_percentage,
            'experience_verification_status' => $application->experience_verification_status,
            'plagiarism_report' => $application->plagiarism_report,
            'anti_cheat_logs' => $application->anti_cheat_logs,
            'portfolio_links' => $application->portfolio_links,
            'activity_logs' => $candidateActivityLogs,
        ]);
    }

    public function shortlist(Request $request, JobApplication $application)
    {
        $this->authorizeApplicationOwnership($request, $application);

        $application->update([
            'status' => 'shortlisted',
            'rejection_reason' => null,
        ]);

        ActivityLogger::log(
            'shortlist',
            'hr_applications',
            "Application {$application->id} shortlisted by HR {$request->user()->email}.",
            $request
        );

        return response()->json([
            'message' => 'Candidate shortlisted successfully.',
            'application' => $application->fresh(['candidate:id,name,email', 'job:id,title']),
        ]);
    }

    public function reject(Request $request, JobApplication $application)
    {
        $this->authorizeApplicationOwnership($request, $application);

        $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        ActivityLogger::log(
            'reject',
            'hr_applications',
            "Application {$application->id} rejected by HR {$request->user()->email}. Reason: {$request->reason}",
            $request
        );

        return response()->json([
            'message' => 'Candidate rejected successfully.',
            'application' => $application->fresh(['candidate:id,name,email', 'job:id,title']),
        ]);
    }

    public function assignTask(Request $request, JobApplication $application)
    {
        $this->authorizeApplicationOwnership($request, $application);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'deadline' => ['nullable', 'date'],
            'instructions_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $taskData = [
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'status' => 'assigned',
        ];

        if ($request->hasFile('instructions_file')) {
            $taskData['instructions_file'] = $request->file('instructions_file')->store('second-round-tasks');
        }

        $task = Task::updateOrCreate(
            ['application_id' => $application->id],
            $taskData
        );

        $application->update([
            'status' => 'second_task_assigned',
        ]);

        ActivityLogger::log(
            'assign_task',
            'hr_applications',
            "Second round task assigned to application {$application->id} by HR {$request->user()->email}.",
            $request
        );

        return response()->json([
            'message' => 'Second round task assigned successfully.',
            'application' => $application->fresh(['candidate:id,name,email', 'job:id,title', 'task']),
            'task' => $task,
        ]);
    }

    public function reviewTask(Request $request, JobApplication $application)
    {
        $this->authorizeApplicationOwnership($request, $application);

        $request->validate([
            'status' => ['required', Rule::in(['passed', 'failed'])],
        ]);

        $task = $application->task;

        if (! $task) {
            return response()->json([
                'message' => 'Second round task not found for this application.',
            ], 404);
        }

        if (! in_array($task->status, ['assigned', 'submitted', 'passed', 'failed'], true)) {
            return response()->json([
                'message' => 'Second round task cannot be reviewed in its current status.',
            ], 422);
        }

        $task->update([
            'status' => $request->status,
        ]);

        ActivityLogger::log(
            'review_task',
            'hr_applications',
            "Second round task for application {$application->id} marked {$request->status} by HR {$request->user()->email}.",
            $request
        );

        return response()->json([
            'message' => 'Second round task reviewed successfully.',
            'application' => $application->fresh(['candidate:id,name,email', 'job:id,title', 'task']),
            'task' => $task->fresh(),
        ]);
    }

    private function authorizeApplicationOwnership(Request $request, JobApplication $application): void
    {
        $job = $application->job;

        if (! $job || $job->hr_id !== $request->user()->id || $job->company_id !== $request->user()->company_id) {
            throw new HttpResponseException(response()->json([
                'message' => 'Application not found for this HR user.',
            ], 404));
        }
    }

    public function scheduleInterview(Request $request, JobApplication $application)
    {
        $this->authorizeApplicationOwnership($request, $application);

        if (! in_array($application->status, ['shortlisted', 'second_task_assigned', 'interview_scheduled'], true)) {
            return response()->json([
                'message' => 'Interview can only be scheduled after candidate is shortlisted or second task is assigned.',
            ], 422);
        }

        if ($application->task && $application->task->status !== 'passed') {
            return response()->json([
                'message' => 'Second round task must be passed before scheduling interview.',
            ], 422);
        }

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'mode' => ['required', Rule::in(['onsite', 'online', 'hybrid'])],
        ]);

        $scheduledAt = Carbon::parse($request->date.' '.$request->time);

        if ($scheduledAt->isPast()) {
            return response()->json([
                'message' => 'Interview date and time must be in the future.',
            ], 422);
        }

        $interview = Interview::updateOrCreate(
            ['application_id' => $application->id],
            [
                'date' => $request->date,
                'time' => $request->time,
                'mode' => $request->mode,
            ]
        );

        $application->update([
            'status' => 'interview_scheduled',
        ]);

        ActivityLogger::log(
            'schedule_interview',
            'hr_applications',
            "Interview scheduled for application {$application->id} on {$scheduledAt->format('Y-m-d H:i')} ({$request->mode}) by HR {$request->user()->email}.",
            $request
        );

        return response()->json([
            'message' => 'Interview scheduled successfully.',
            'application' => $application->fresh(['candidate:id,name,email', 'job:id,title', 'interview']),
            'interview' => $interview,
        ]);
    }
}
