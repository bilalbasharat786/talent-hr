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
  Schema::create('hr_jobs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hr_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
    $table->string('title');
    $table->enum('status', ['draft', 'active', 'closed'])->default('active');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_jobs');
    }
};
