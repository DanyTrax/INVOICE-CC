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
        if (Schema::hasTable('email_logs')) {
            return; // La tabla ya existe, no hacer nada
        }

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->enum('provider', ['smtp', 'zoho'])->default('smtp');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('related_type')->nullable(); // registration, company, etc.
            $table->unsignedBigInteger('related_id')->nullable();
            $table->boolean('is_test')->default(false); // Si es un correo de prueba
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['user_id']);
            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
