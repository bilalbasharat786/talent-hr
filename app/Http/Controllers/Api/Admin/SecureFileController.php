<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Supervisor;
use App\Models\VerificationDocument;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SecureFileController extends Controller
{
    public function companyDocument(Request $request, VerificationDocument $document)
    {
        ActivityLogger::log(
            'view_file',
            'secure_file_access',
            "Viewed company verification document {$document->id}.",
            $request
        );

        return $this->fileResponse($document->file_path);
    }

    public function supervisorSelfie(Request $request, Supervisor $supervisor)
    {
        ActivityLogger::log(
            'view_file',
            'secure_file_access',
            "Viewed supervisor selfie for supervisor {$supervisor->id}.",
            $request
        );

        return $this->fileResponse($supervisor->selfie_path);
    }

    public function internshipCertificate(Request $request, Internship $internship)
    {
        ActivityLogger::log(
            'view_file',
            'secure_file_access',
            "Viewed internship certificate for internship {$internship->id}.",
            $request
        );

        return $this->fileResponse($internship->certificate_path);
    }

    private function fileResponse(?string $path)
    {
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return response()->json([
                'message' => 'File not found.',
            ], 404);
        }

        return Storage::disk('local')->response($path);
    }
}
