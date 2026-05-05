<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->string('session_token')->unique();
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'auto_submitted', 'expired'])->default('in_progress');
            $table->json('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedInteger('warning_count')->default(0);
            $table->unsignedInteger('violation_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_sessions');
    }
};
