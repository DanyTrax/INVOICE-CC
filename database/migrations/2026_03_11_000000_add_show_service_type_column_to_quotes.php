<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columna Trámite: visible solo si se activa (como RAA/Expediente) o si algún ítem tiene expediente vinculado.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->boolean('show_service_type_column')->default(false)->after('show_raa_column');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('show_service_type_column');
        });
    }
};
