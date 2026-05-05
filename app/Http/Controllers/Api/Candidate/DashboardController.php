<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSession;
use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $candidate = $request->user();
        $applicationIds = $candidate->jobApplications()->pluck('id');

        return response()->json([
            'applied_jobs' => $candidate->jobApplications()->count(),
            'assessment_status' => [
                'pending' => $candidate->jobApplications()->where('status', 'assessment_pending')->count(),
                'in_progress' => AssessmentSession::where('candidate_id', $candidate->id)->where('status', 'in_progress')->count(),
                'submitted' => AssessmentSession::where('candidate_id', $candidate->id)->whereIn('status', ['submitted', 'auto_submitted'])->count(),
                'results' => $candidate->assessmentSubmissions()->whereIn('status', ['passed', 'failed', 'auto_submitted'])->count(),
            ],
            'verified_internships' => $candidate->internships()->where('status', 'verified')->count(),
            'notifications' => [
                'total' => Notification::where('user_id', $candidate->id)->count(),
                'unread' => Notification::where('user_id', $candidate->id)->whereNull('read_at')->count(),
            ],
            'recent_applications' => $candidate->jobApplications()
                ->with(['job:id,title,location,work_mode', 'task'])
                ->latest()
                ->take(10)
                ->get(),
            'task_submissions' => $candidate->taskSubmissions()->whereIn('task_id', function ($query) use ($applicationIds) {
                $query->select('id')
                    ->from('tasks')
                    ->whereIn('application_id', $applicationIds);
            })->count(),
        ]);
    }
}
