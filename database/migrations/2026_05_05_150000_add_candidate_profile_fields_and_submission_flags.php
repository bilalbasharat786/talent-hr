<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'skills')) {
                $table->json('skills')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('users', 'education')) {
                $table->longText('education')->nullable()->after('skills');
            }

            if (! Schema::hasColumn('users', 'experience')) {
                $table->longText('experience')->nullable()->after('education');
            }

            if (! Schema::hasColumn('users', 'candidate_rating')) {
                $table->decimal('candidate_rating', 5, 2)->nullable()->after('experience');
            }
        });

        Schema::table('assessment_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('assessment_submissions', 'cheating_flag')) {
                $table->enum('cheating_flag', ['normal', 'suspicious', 'cheating_detected'])->default('normal')->after('status');
            }

            if (! Schema::hasColumn('assessment_submissions', 'plagiarism_report')) {
                $table->text('plagiarism_report')->nullable()->after('cheating_flag');
            }

            if (! Schema::hasColumn('assessment_submissions', 'answers_payload')) {
                $table->json('answers_payload')->nullable()->after('plagiarism_report');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessment_submissions', function (Blueprint $table) {
            foreach (['answers_payload', 'plagiarism_report', 'cheating_flag'] as $column) {
                if (Schema::hasColumn('assessment_submissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['candidate_rating', 'experience', 'education', 'skills'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
