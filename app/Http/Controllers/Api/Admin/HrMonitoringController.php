<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $hrs = User::query()
            ->where('role', 'hr')
            ->withCount(['hrJobs as jobs_created'])
            ->with(['hrJobs.company:id,name'])
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20);

        $hrs->getCollection()->transform(function ($hr) {
            $jobIds = $hr->hrJobs->pluck('id');

            $totalApplications = JobApplication::whereIn('job_id', $jobIds)->count();
            $rejected = JobApplication::whereIn('job_id', $jobIds)->where('status', 'rejected')->count();
            $shortlisted = JobApplication::whereIn('job_id', $jobIds)->where('status', 'shortlisted')->count();

            $hr->company = optional($hr->hrJobs->first()?->company)->name;
            $hr->rejection_rate = $totalApplications > 0 ? round(($rejected / $totalApplications) * 100, 2) : 0;
            $hr->shortlist_rate = $totalApplications > 0 ? round(($shortlisted / $totalApplications) * 100, 2) : 0;

            unset($hr->hrJobs);

            return $hr;
        });

        return response()->json($hrs);
    }

    public function show(User $hr)
    {
        if ($hr->role !== 'hr') {
            return response()->json([
                'message' => 'Selected user is not an HR user.',
            ], 422);
        }

        $hr->load([
            'hrJobs.company:id,name,email,status,trust_level',
            'hrJobs.applications.candidate:id,name,email,status',
        ]);

        $jobIds = $hr->hrJobs->pluck('id');

        $totalApplications = JobApplication::whereIn('job_id', $jobIds)->count();
        $rejected = JobApplication::whereIn('job_id', $jobIds)->where('status', 'rejected')->count();
        $shortlisted = JobApplication::whereIn('job_id', $jobIds)->where('status', 'shortlisted')->count();

        $rejectionReasons = JobApplication::whereIn('job_id', $jobIds)
            ->where('status', 'rejected')
            ->whereNotNull('rejection_reason')
            ->select('rejection_reason', DB::raw('COUNT(*) as total'))
            ->groupBy('rejection_reason')
            ->orderByDesc('total')
            ->get();

        $hiringPatterns = JobApplication::whereIn('job_id', $jobIds)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'hr' => $hr,
            'analytics' => [
                'jobs_created' => $hr->hrJobs->count(),
                'total_applications' => $totalApplications,
                'rejection_rate' => $totalApplications > 0 ? round(($rejected / $totalApplications) * 100, 2) : 0,
                'shortlist_rate' => $totalApplications > 0 ? round(($shortlisted / $totalApplications) * 100, 2) : 0,
                'rejection_reasons' => $rejectionReasons,
                'hiring_patterns' => $hiringPatterns,
            ],
        ]);
    }
}

