<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('assessment_sessions')->cascadeOnDelete();
            $table->string('event_type');
            $table->timestamp('event_time');
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_logs');
    }
};
