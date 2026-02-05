<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ítems de cotización. Depende de quotes y service_types.
     */
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained('service_types')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->decimal('fee_value', 15, 2)->default(0);           // Honorario ítem
            $table->string('invima_rate_code', 32)->nullable();        // ej: 4001-37
            $table->decimal('invima_rate_value', 15, 2)->default(0);   // Valor tasa INVIMA
            $table->timestamps();
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->index('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
