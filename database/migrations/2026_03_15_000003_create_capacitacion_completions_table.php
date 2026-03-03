<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacitacion_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capacitacion_video_id')->constrained('capacitacion_videos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->unique(['capacitacion_video_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacitacion_completions');
    }
};
