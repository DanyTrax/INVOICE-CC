<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cotizaciones (pre-venta). client_id = companies.id (empresa cliente).
     */
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('companies')->cascadeOnDelete();
            $table->string('consecutive', 32)->unique(); // ej: 006-25
            $table->date('date');
            $table->string('currency', 3)->default('COP'); // COP | USD
            $table->string('status', 32)->default('Borrador'); // Borrador, Enviada, Aprobada, Rechazada
            $table->decimal('total_professional_fees', 15, 2)->default(0); // Honorarios
            $table->decimal('total_invima_fees', 15, 2)->default(0);   // Tasas INVIMA
            $table->decimal('total_loans', 15, 2)->default(0);         // Préstamos DV (dinero prestado para tasas)
            $table->timestamps();
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->index(['client_id', 'status']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
