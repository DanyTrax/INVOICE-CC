<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('can_feed_timeline')->default(false);
            $table->boolean('can_manage_documents')->default(false);
            $table->timestamps();

            $table->unique(['process_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_user');
    }
};
