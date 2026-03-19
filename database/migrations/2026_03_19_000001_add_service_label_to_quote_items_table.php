<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite personalizar el texto visible del servicio por ítem
     * (manteniendo el service_id como servicio base seleccionado).
     */
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->string('service_label', 255)->nullable()->after('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('service_label');
        });
    }
};

