<?php

namespace Database\Seeders;

use App\Models\Internship;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InternshipTestSeeder extends Seeder
{
    public function run(): void
    {
        $candidate = User::updateOrCreate(
            ['email' => 'candidate@test.com'],
            [
                'name' => 'Test Candidate',
                'password' => Hash::make('123456'),
                'role' => 'candidate',
                'status' => 'active',
            ]
        );

        Internship::updateOrCreate(
            ['candidate_id' => $candidate->id, 'company_name' => 'Test Company'],
            [
                'duration' => '3 months',
                'supervisor_email' => 'supervisor@test.com',
                'certificate_path' => 'internship-certificates/test-certificate.pdf',
                'verification_email_response' => null,
                'status' => 'pending',
            ]
        );
    }
}
