<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
      $this->call([
    SuperAdminSeeder::class,
    CompanyTestSeeder::class,
    InternshipTestSeeder::class,
    UserTestSeeder::class,
    HrMonitoringTestSeeder::class,
     FraudLogTestSeeder::class,
]);

    }
}

