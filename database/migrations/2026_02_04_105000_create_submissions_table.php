<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sometimientos ante INVIMA. parent_id recursivo para rechazos y nuevo intento.
     */
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->string('submission_type', 32); // Inicial | Respuesta a Auto | Subsanación
            $table->string('filing_number', 64)->nullable();   // Radicado INVIMA
            $table->string('tracking_id', 64)->nullable();    // ej: R15-TY5-L715
            $table->date('filing_date')->nullable();          // Fecha radicación
            $table->string('status', 32)->default('En Estudio'); // En Estudio | Requerido | Aprobado | Rechazado/Negado
            $table->string('payment_reference', 64)->nullable(); // Número consignación
            $table->timestamps();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->index(['process_id', 'status']);
            $table->index('parent_id');
            $table->index('filing_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
