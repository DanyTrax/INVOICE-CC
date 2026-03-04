<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Servicio seleccionado en el ítem (opcional; rellena descripción y alcance por defecto).
     */
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('quote_id')->constrained('services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });
    }
};
