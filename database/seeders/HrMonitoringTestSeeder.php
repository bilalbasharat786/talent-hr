<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\HrJob;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class HrMonitoringTestSeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::where('email', 'hr@test.com')->first();
        $candidate = User::where('email', 'candidate@test.com')->first();
        $candidate2 = User::where('email', 'candidate2@test.com')->first();
        $company = Company::where('email', 'company@test.com')->first();

        if (! $hr || ! $candidate || ! $candidate2 || ! $company) {
            return;
        }

        $job = HrJob::updateOrCreate(
            ['hr_id' => $hr->id, 'title' => 'Laravel Developer Intern'],
            [
                'company_id' => $company->id,
                'status' => 'active',
            ]
        );

        JobApplication::updateOrCreate(
            ['job_id' => $job->id, 'candidate_id' => $candidate->id],
            [
                'status' => 'shortlisted',
                'rejection_reason' => null,
            ]
        );

        JobApplication::updateOrCreate(
            ['job_id' => $job->id, 'candidate_id' => $candidate2->id],
            [
                'status' => 'rejected',
                'rejection_reason' => 'Insufficient technical skills',
            ]
        );
    }
}

