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

        DB::statement("
            ALTER TABLE job_applications
            MODIFY status ENUM(
                'applied',
                'assessment_pending',
                'submitted',
                'passed',
                'failed',
                'shortlisted',
                'second_task_assigned',
                'interview_scheduled',
                'hired',
                'rejected'
            ) DEFAULT 'applied'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE job_applications
            MODIFY status ENUM(
                'applied',
                'shortlisted',
                'rejected',
                'hired'
            ) DEFAULT 'applied'
        ");
    }
};
