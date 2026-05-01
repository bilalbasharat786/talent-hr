<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraudLog;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FraudLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => [
                'nullable',
                Rule::in([
                    'duplicate_internship_certificate',
                    'suspicious_assessment_pattern',
                    'fake_document',
                ]),
            ],
            'status' => ['nullable', Rule::in(['open', 'flagged', 'resolved', 'fraud'])],
        ]);

        $fraudLogs = FraudLog::with('resolver:id,name,email,role')
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20);

        return response()->json($fraudLogs);
    }

    public function show(FraudLog $fraudLog)
    {
        $fraudLog->load('resolver:id,name,email,role');

        return response()->json($fraudLog);
    }

    public function flag(Request $request, FraudLog $fraudLog)
    {
        $fraudLog->update([
            'status' => 'flagged',
            'resolved_by' => null,
            'resolved_at' => null,
        ]);

        ActivityLogger::log(
            'flag',
            'fraud_detection',
            "Fraud alert {$fraudLog->id} flagged.",
            $request
        );

        return response()->json([
            'message' => 'Fraud alert flagged successfully.',
            'fraud_log' => $fraudLog->fresh('resolver:id,name,email,role'),
        ]);
    }

    public function resolve(Request $request, FraudLog $fraudLog)
    {
        $fraudLog->update([
            'status' => 'resolved',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLogger::log(
            'resolve',
            'fraud_detection',
            "Fraud alert {$fraudLog->id} resolved.",
            $request
        );

        return response()->json([
            'message' => 'Fraud alert resolved successfully.',
            'fraud_log' => $fraudLog->fresh('resolver:id,name,email,role'),
        ]);
    }

    public function markAsFraud(Request $request, FraudLog $fraudLog)
    {
        $fraudLog->update([
            'status' => 'fraud',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLogger::log(
            'mark_as_fraud',
            'fraud_detection',
            "Fraud alert {$fraudLog->id} marked as fraud.",
            $request
        );

        return response()->json([
            'message' => 'Alert marked as fraud successfully.',
            'fraud_log' => $fraudLog->fresh('resolver:id,name,email,role'),
        ]);
    }
}
