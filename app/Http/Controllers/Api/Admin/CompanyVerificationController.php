<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyVerificationController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::withCount(['supervisors', 'verificationDocuments'])
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->trust_level, function ($query) use ($request) {
                $query->where('trust_level', $request->trust_level);
            })
            ->latest()
            ->paginate(20);

        return response()->json($companies);
    }

    public function show(Company $company)
    {
        $company->load([
            'verificationDocuments',
            'supervisors',
        ]);

        return response()->json($company);
    }

    public function approve(Request $request, Company $company)
    {
        $request->validate([
            'trust_level' => ['required', Rule::in(['basic', 'standard', 'gold', 'platinum'])],
        ]);

        $company->update([
            'status' => 'approved',
            'trust_level' => $request->trust_level,
            'rejection_reason' => null,
        ]);

        $company->verificationDocuments()->update([
            'status' => 'approved',
        ]);

        ActivityLogger::log(
            'approve',
            'company_verification',
            "Company {$company->name} approved with {$request->trust_level} trust level.",
            $request
        );

        return response()->json([
            'message' => 'Company approved successfully.',
            'company' => $company->fresh(['verificationDocuments', 'supervisors']),
        ]);
    }

    public function reject(Request $request, Company $company)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $company->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        $company->verificationDocuments()->update([
            'status' => 'rejected',
        ]);

        ActivityLogger::log(
            'reject',
            'company_verification',
            "Company {$company->name} rejected. Reason: {$request->reason}",
            $request
        );

        return response()->json([
            'message' => 'Company rejected successfully.',
            'company' => $company->fresh(['verificationDocuments', 'supervisors']),
        ]);
    }
}

