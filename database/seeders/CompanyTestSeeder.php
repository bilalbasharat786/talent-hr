<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Supervisor;
use App\Models\VerificationDocument;
use Illuminate\Database\Seeder;

class CompanyTestSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['email' => 'company@test.com'],
            [
                'name' => 'Test Company',
                'status' => 'pending',
                'trust_level' => 'basic',
            ]
        );

        VerificationDocument::updateOrCreate(
            ['company_id' => $company->id, 'type' => 'secp'],
            [
                'file_path' => 'verification-documents/secp-test.pdf',
                'status' => 'pending',
            ]
        );

        VerificationDocument::updateOrCreate(
            ['company_id' => $company->id, 'type' => 'ntn'],
            [
                'file_path' => 'verification-documents/ntn-test.pdf',
                'status' => 'pending',
            ]
        );

        VerificationDocument::updateOrCreate(
            ['company_id' => $company->id, 'type' => 'address'],
            [
                'file_path' => 'verification-documents/address-test.pdf',
                'status' => 'pending',
            ]
        );

        Supervisor::updateOrCreate(
            ['email' => 'supervisor@test.com'],
            [
                'company_id' => $company->id,
                'name' => 'Test Supervisor',
                'cnic' => '42101-1234567-1',
                'selfie_path' => 'supervisor-selfies/test.jpg',
                'status' => 'pending',
            ]
        );
    }
}

