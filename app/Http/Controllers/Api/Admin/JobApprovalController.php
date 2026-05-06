<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HrJob;
use App\Models\Notification;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class JobApprovalController extends Controller
{
    public function index(Request $request)
    {
        $jobs = HrJob::with([
            'hr:id,name,email,company_id',
            'company:id,name,email,status,trust_level',
            'assessment:id,title,status',
        ])
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest()
            ->paginate(20);

        return response()->json($jobs);
    }

    public function show(HrJob $job)
    {
        return response()->json(
            $job->load([
                'hr:id,name,email,company_id',
                'company:id,name,email,status,trust_level',
                'assessment.questions',
            ])
        );
    }

    public function approve(Request $request, HrJob $job)
    {
        if ($job->status !== 'pending_approval') {
            return response()->json([
                'message' => 'Only jobs in pending_approval status can be approved.',
            ], 422);
        }

        $job->update(['status' => 'live']);

        if ($job->hr_id) {
            Notification::create([
                'user_id' => $job->hr_id,
                'company_id' => $job->company_id,
                'type' => 'system_alert',
                'title' => 'Job approved',
                'message' => "Your job '{$job->title}' has been approved and is now live.",
            ]);
        }

        ActivityLogger::log(
            'approve',
            'admin_jobs',
            "Admin approved job {$job->id} ({$job->title}).",
            $request
        );

        return response()->json([
            'message' => 'Job approved and set live.',
            'job' => $job->fresh(['assessment:id,title,status']),
        ]);
    }

    public function reject(Request $request, HrJob $job)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $job->update(['status' => 'closed']);

        if ($job->hr_id) {
            Notification::create([
                'user_id' => $job->hr_id,
                'company_id' => $job->company_id,
                'type' => 'system_alert',
                'title' => 'Job rejected',
                'message' => "Your job '{$job->title}' was rejected. Reason: {$request->reason}",
            ]);
        }

        ActivityLogger::log(
            'reject',
            'admin_jobs',
            "Admin rejected job {$job->id} ({$job->title}). Reason: {$request->reason}",
            $request
        );

        return response()->json([
            'message' => 'Job rejected and closed.',
            'job' => $job->fresh(),
        ]);
    }
}
