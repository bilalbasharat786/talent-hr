<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('hr_jobs', 'type')) {
                $table->enum('type', ['full_time', 'part_time', 'contract', 'internship'])->nullable()->after('title');
            }

            if (! Schema::hasColumn('hr_jobs', 'work_mode')) {
                $table->enum('work_mode', ['onsite', 'remote', 'hybrid'])->nullable()->after('type');
            }

            if (! Schema::hasColumn('hr_jobs', 'location')) {
                $table->string('location')->nullable()->after('work_mode');
            }

            if (! Schema::hasColumn('hr_jobs', 'skills')) {
                $table->json('skills')->nullable()->after('location');
            }

            if (! Schema::hasColumn('hr_jobs', 'experience_level')) {
                $table->string('experience_level')->nullable()->after('skills');
            }

            if (! Schema::hasColumn('hr_jobs', 'education')) {
                $table->string('education')->nullable()->after('experience_level');
            }

            if (! Schema::hasColumn('hr_jobs', 'description')) {
                $table->longText('description')->nullable()->after('education');
            }

            if (! Schema::hasColumn('hr_jobs', 'candidates_required')) {
                $table->unsignedInteger('candidates_required')->nullable()->after('description');
            }

            if (! Schema::hasColumn('hr_jobs', 'hiring_urgency')) {
                $table->enum('hiring_urgency', ['low', 'medium', 'high'])->nullable()->after('candidates_required');
            }

            if (! Schema::hasColumn('hr_jobs', 'assessment_id')) {
                $table->unsignedBigInteger('assessment_id')->nullable()->after('hiring_urgency');
            }
        });

        Schema::table('job_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('job_applications', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }

            if (! Schema::hasColumn('job_applications', 'skill_match_percentage')) {
                $table->decimal('skill_match_percentage', 5, 2)->nullable()->after('rejection_reason');
            }

            if (! Schema::hasColumn('job_applications', 'experience_verification_status')) {
                $table->enum('experience_verification_status', ['pending', 'verified', 'failed'])->default('pending')->after('skill_match_percentage');
            }

            if (! Schema::hasColumn('job_applications', 'plagiarism_report')) {
                $table->text('plagiarism_report')->nullable()->after('experience_verification_status');
            }

            if (! Schema::hasColumn('job_applications', 'anti_cheat_logs')) {
                $table->text('anti_cheat_logs')->nullable()->after('plagiarism_report');
            }

            if (! Schema::hasColumn('job_applications', 'portfolio_links')) {
                $table->json('portfolio_links')->nullable()->after('anti_cheat_logs');
            }

            if (! Schema::hasColumn('job_applications', 'cooldown_until')) {
                $table->timestamp('cooldown_until')->nullable()->after('portfolio_links');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_jobs', function (Blueprint $table) {
            $columns = [
                'type',
                'work_mode',
                'location',
                'skills',
                'experience_level',
                'education',
                'description',
                'candidates_required',
                'hiring_urgency',
                'assessment_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('hr_jobs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $columns = [
                'rejection_reason',
                'skill_match_percentage',
                'experience_verification_status',
                'plagiarism_report',
                'anti_cheat_logs',
                'portfolio_links',
                'cooldown_until',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('job_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

