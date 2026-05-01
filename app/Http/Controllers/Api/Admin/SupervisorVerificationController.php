<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SupervisorVerificationController extends Controller
{
    public function index(Request $request)
    {
        $supervisors = Supervisor::with('company:id,name,email,status,trust_level')
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20);

        return response()->json($supervisors);
    }

    public function show(Supervisor $supervisor)
    {
        $supervisor->load('company:id,name,email,status,trust_level');

        return response()->json($supervisor);
    }

    public function approve(Request $request, Supervisor $supervisor)
    {
        $supervisor->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        ActivityLogger::log(
            'approve',
            'supervisor_verification',
            "Supervisor {$supervisor->name} approved.",
            $request
        );

        return response()->json([
            'message' => 'Supervisor approved successfully.',
            'supervisor' => $supervisor->fresh('company:id,name,email,status,trust_level'),
        ]);
    }

    public function reject(Request $request, Supervisor $supervisor)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $supervisor->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        ActivityLogger::log(
            'reject',
            'supervisor_verification',
            "Supervisor {$supervisor->name} rejected. Reason: {$request->reason}",
            $request
        );

        return response()->json([
            'message' => 'Supervisor rejected successfully.',
            'supervisor' => $supervisor->fresh('company:id,name,email,status,trust_level'),
        ]);
    }
}

