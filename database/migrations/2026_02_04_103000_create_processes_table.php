<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expediente (entidad padre). Vinculado a quote_item y cliente (company).
     */
    public function up(): void
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_item_id')->constrained('quote_items')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('companies')->cascadeOnDelete();
            $table->string('status', 64)->default('Recolección Documentos');
            // Recolección Documentos | Radicado | En Requerimiento | Finalizado | Cancelado
            $table->string('expediente_invima', 64)->nullable(); // Se llena después del radicado
            $table->timestamps();
        });

        Schema::table('processes', function (Blueprint $table) {
            $table->index(['client_id', 'status']);
            $table->index('quote_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};
