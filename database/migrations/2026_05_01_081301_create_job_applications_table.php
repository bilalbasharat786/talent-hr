<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('job_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('job_id')->constrained('hr_jobs')->cascadeOnDelete();
    $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
    $table->enum('status', [
        'applied',
        'assessment_pending',
        'submitted',
        'passed',
        'failed',
        'shortlisted',
        'second_task_assigned',
        'interview_scheduled',
        'hired',
        'rejected',
    ])->default('applied');
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
