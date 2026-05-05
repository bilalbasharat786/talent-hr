<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrJob;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $hr = $request->user();

        $jobIds = HrJob::where('hr_id', $hr->id)->pluck('id');

        return response()->json([
            'total_jobs' => HrJob::where('hr_id', $hr->id)->count(),
            'active_jobs' => HrJob::where('hr_id', $hr->id)->where('status', 'live')->count(),
            'candidates_in_pipeline' => JobApplication::whereIn('job_id', $jobIds)->count(),
            'pending_reviews' => JobApplication::whereIn('job_id', $jobIds)
                ->whereIn('status', ['submitted', 'passed'])
                ->count(),
            'recent_applications' => JobApplication::with(['candidate:id,name,email', 'job:id,title'])
                ->whereIn('job_id', $jobIds)
                ->latest()
                ->take(10)
                ->get(),
            'pipeline_overview' => [
                'applied' => JobApplication::whereIn('job_id', $jobIds)->where('status', 'applied')->count(),
                'assessment_pending' => JobApplication::whereIn('job_id', $jobIds)->where('status', 'assessment_pending')->count(),
                'submitted' => JobApplication::whereIn('job_id', $jobIds)->where('status', 'submitted')->count(),
                'passed' => JobApplication::whereIn('job_id', $jobIds)->where('status', 'passed')->count(),
                'shortlisted' => JobApplication::whereIn('job_id', $jobIds)->where('status', 'shortlisted')->count(),
                'interview' => JobApplication::whereIn('job_id', $jobIds)->where('status', 'interview_scheduled')->count(),
            ],
        ]);
    }
}

