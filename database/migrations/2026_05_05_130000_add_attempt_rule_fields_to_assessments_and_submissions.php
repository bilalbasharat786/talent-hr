<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('assessments', 'cooldown_days')) {
                $table->unsignedInteger('cooldown_days')->default(7)->after('randomize_questions');
            }
        });

        Schema::table('assessment_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('assessment_submissions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('assessment_submissions', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('started_at');
            }
        });

        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->unique(['assessment_id', 'candidate_id'], 'assessment_candidate_unique_attempt');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->dropUnique('assessment_candidate_unique_attempt');

            if (Schema::hasColumn('assessment_submissions', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }

            if (Schema::hasColumn('assessment_submissions', 'started_at')) {
                $table->dropColumn('started_at');
            }
        });

        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'cooldown_days')) {
                $table->dropColumn('cooldown_days');
            }
        });
    }
};
