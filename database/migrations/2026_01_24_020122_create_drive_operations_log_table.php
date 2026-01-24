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
        Schema::create('drive_operations_log', function (Blueprint $table) {
            $table->id();
            $table->string('operation_type'); // upload, create_folder, move, delete, etc.
            $table->string('resource_type')->nullable(); // file, folder
            $table->string('resource_name'); // nombre del archivo o carpeta
            $table->string('drive_id')->nullable(); // ID en Google Drive
            $table->string('drive_url')->nullable(); // URL en Google Drive
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->text('details')->nullable(); // JSON con detalles adicionales
            
            // Relaciones
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('registration_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamps();
            
            // Índices
            $table->index(['operation_type', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['user_id']);
            $table->index(['registration_id']);
            $table->index(['company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_operations_log');
    }
};
