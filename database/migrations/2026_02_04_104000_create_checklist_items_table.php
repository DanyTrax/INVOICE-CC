<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestión documental por proceso. status: Pendiente, Recibido, En Traducción, Aprobado, Observado.
     */
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->cascadeOnDelete();
            $table->string('document_name');
            $table->boolean('is_translation_required')->default(false);
            $table->string('status', 32)->default('Pendiente');
            // Pendiente | Recibido | Traducción | Aprobado
            $table->text('observation_agent')->nullable();   // Interno
            $table->text('observation_client')->nullable(); // Visible al cliente
            $table->timestamps();
        });

        Schema::table('checklist_items', function (Blueprint $table) {
            $table->index(['process_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
