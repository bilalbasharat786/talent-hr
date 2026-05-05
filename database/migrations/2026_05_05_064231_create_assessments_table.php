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
    Schema::create('assessments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hr_id')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->unsignedInteger('time_limit')->nullable();
    $table->boolean('one_attempt_only')->default(true);
    $table->boolean('auto_submit')->default(true);
    $table->boolean('randomize_questions')->default(false);
    $table->enum('status', ['draft', 'active', 'locked'])->default('draft');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
