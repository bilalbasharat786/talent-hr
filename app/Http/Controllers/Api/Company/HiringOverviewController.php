<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\HrJob;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class HiringOverviewController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $jobs = HrJob::query()
            ->with([
                'hr:id,name,email,status',
                'applications.candidate:id,name,email,status',
            ])
            ->withCount([
                'applications as total_applications',
                'applications as applied_count' => function ($query) {
                    $query->where('status', 'applied');
                },
                'applications as shortlisted_count' => function ($query) {
                    $query->where('status', 'shortlisted');
                },
                'applications as rejected_count' => function ($query) {
                    $query->where('status', 'rejected');
                },
                'applications as hired_count' => function ($query) {
                    $query->where('status', 'hired');
                },
            ])
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(20);

        $pipelineStats = [
            'total_jobs' => HrJob::where('company_id', $company->id)->count(),
            'draft_jobs' => HrJob::where('company_id', $company->id)->where('status', 'draft')->count(),
            'pending_approval_jobs' => HrJob::where('company_id', $company->id)->where('status', 'pending_approval')->count(),
            'live_jobs' => HrJob::where('company_id', $company->id)->where('status', 'live')->count(),
            'closed_jobs' => HrJob::where('company_id', $company->id)->where('status', 'closed')->count(),
            'total_applications' => JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->count(),
            'applied' => JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->where('status', 'applied')->count(),
            'shortlisted' => JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->where('status', 'shortlisted')->count(),
            'rejected' => JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->where('status', 'rejected')->count(),
            'hired' => JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->where('status', 'hired')->count(),
        ];

        return response()->json([
            'message' => 'Hiring overview is read-only for company owner.',
            'pipeline_stats' => $pipelineStats,
            'jobs' => $jobs,
            'permissions' => [
                'can_create_job' => false,
                'can_edit_job' => false,
                'can_create_assessment' => false,
            ],
        ]);
    }
}
