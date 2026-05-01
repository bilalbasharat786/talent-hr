<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTestSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Test HR',
                'email' => 'hr@test.com',
                'role' => 'hr',
            ],
            [
                'name' => 'Test Company User',
                'email' => 'company.user@test.com',
                'role' => 'company',
            ],
            [
                'name' => 'Second Candidate',
                'email' => 'candidate2@test.com',
                'role' => 'candidate',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('123456'),
                    'role' => $user['role'],
                    'status' => 'active',
                ]
            );
        }
    }
}

