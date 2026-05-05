<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE hr_jobs MODIFY status ENUM('draft', 'active', 'pending_approval', 'live', 'closed') DEFAULT 'active'");

        DB::table('hr_jobs')
            ->where('status', 'active')
            ->update(['status' => 'live']);

        DB::statement("ALTER TABLE hr_jobs MODIFY status ENUM('draft', 'pending_approval', 'live', 'closed') DEFAULT 'draft'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE hr_jobs MODIFY status ENUM('draft', 'active', 'pending_approval', 'live', 'closed') DEFAULT 'draft'");

        DB::table('hr_jobs')
            ->where('status', 'live')
            ->update(['status' => 'active']);

        DB::statement("ALTER TABLE hr_jobs MODIFY status ENUM('draft', 'active', 'closed') DEFAULT 'active'");
    }
};

