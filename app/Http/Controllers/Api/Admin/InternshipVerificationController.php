<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class InternshipVerificationController extends Controller
{
    public function index(Request $request)
    {
        $internships = Internship::with('candidate:id,name,email,status')
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->candidate_id, function ($query) use ($request) {
                $query->where('candidate_id', $request->candidate_id);
            })
            ->latest()
            ->paginate(20);

        return response()->json($internships);
    }

    public function show(Internship $internship)
    {
        $internship->load('candidate:id,name,email,status');

        return response()->json($internship);
    }

    public function verify(Request $request, Internship $internship)
    {
        $request->validate([
            'verification_email_response' => ['nullable', 'string'],
        ]);

        $internship->update([
            'status' => 'verified',
            'verification_email_response' => $request->verification_email_response,
            'rejection_reason' => null,
        ]);

        ActivityLogger::log(
            'verify',
            'internship_verification',
            "Internship {$internship->id} verified for company {$internship->company_name}.",
            $request
        );

        return response()->json([
            'message' => 'Internship verified successfully.',
            'internship' => $internship->fresh('candidate:id,name,email,status'),
        ]);
    }

    public function partial(Request $request, Internship $internship)
    {
        $request->validate([
            'verification_email_response' => ['nullable', 'string'],
        ]);

        $internship->update([
            'status' => 'partial',
            'verification_email_response' => $request->verification_email_response,
            'rejection_reason' => null,
        ]);

        ActivityLogger::log(
            'partial_verify',
            'internship_verification',
            "Internship {$internship->id} partially verified for company {$internship->company_name}.",
            $request
        );

        return response()->json([
            'message' => 'Internship partially verified successfully.',
            'internship' => $internship->fresh('candidate:id,name,email,status'),
        ]);
    }

    public function reject(Request $request, Internship $internship)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:3'],
            'verification_email_response' => ['nullable', 'string'],
        ]);

        $internship->update([
            'status' => 'rejected',
            'verification_email_response' => $request->verification_email_response,
            'rejection_reason' => $request->reason,
        ]);

        ActivityLogger::log(
            'reject',
            'internship_verification',
            "Internship {$internship->id} rejected. Reason: {$request->reason}",
            $request
        );

        return response()->json([
            'message' => 'Internship rejected successfully.',
            'internship' => $internship->fresh('candidate:id,name,email,status'),
        ]);
    }
}

