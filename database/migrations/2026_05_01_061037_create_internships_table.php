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
 Schema::create('internships', function (Blueprint $table) {
    $table->id();
    $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
    $table->string('company_name');
    $table->string('duration');
    $table->string('supervisor_email');
    $table->string('certificate_path')->nullable();
    $table->text('verification_email_response')->nullable();
    $table->enum('status', ['pending', 'verified', 'partial', 'rejected'])->default('pending');
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
