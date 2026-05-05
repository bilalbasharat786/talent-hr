<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\VerificationDocument;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $company = $request->user()->company;

        $request->validate([
            'secp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'ntn' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'address' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if (! $request->hasFile('secp') && ! $request->hasFile('ntn') && ! $request->hasFile('address')) {
            return response()->json([
                'message' => 'At least one document is required.',
            ], 422);
        }

        $uploaded = [];

        foreach (['secp', 'ntn', 'address'] as $type) {
            if (! $request->hasFile($type)) {
                continue;
            }

            $path = $request->file($type)->store("verification-documents/company-{$company->id}");

            $uploaded[] = VerificationDocument::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'type' => $type,
                ],
                [
                    'file_path' => $path,
                    'status' => 'pending',
                ]
            );
        }

        $company->update([
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        ActivityLogger::log(
            'upload',
            'company_documents',
            "Company {$company->name} uploaded verification documents.",
            $request
        );

        Notification::create([
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
            'type' => 'company_verification',
            'title' => 'Verification documents uploaded',
            'message' => 'Your company verification documents were uploaded and are pending admin review.',
        ]);

        return response()->json([
            'message' => 'Documents uploaded successfully.',
            'documents' => $uploaded,
        ]);
    }
}

