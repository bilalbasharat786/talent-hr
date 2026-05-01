<?php

namespace Database\Seeders;

use App\Models\FraudLog;
use App\Models\Internship;
use Illuminate\Database\Seeder;

class FraudLogTestSeeder extends Seeder
{
    public function run(): void
    {
        $internship = Internship::first();

        FraudLog::updateOrCreate(
            [
                'type' => 'duplicate_internship_certificate',
                'reference_id' => optional($internship)->id,
            ],
            [
                'description' => 'Same internship certificate appears to be uploaded by multiple candidates.',
                'status' => 'open',
            ]
        );

        FraudLog::updateOrCreate(
            [
                'type' => 'suspicious_assessment_pattern',
                'reference_id' => null,
            ],
            [
                'description' => 'Candidate assessment answers show suspicious timing and repeated patterns.',
                'status' => 'open',
            ]
        );

        FraudLog::updateOrCreate(
            [
                'type' => 'fake_document',
                'reference_id' => optional($internship)->id,
            ],
            [
                'description' => 'Uploaded document metadata does not match claimed issuing source.',
                'status' => 'open',
            ]
        );
    }
}
