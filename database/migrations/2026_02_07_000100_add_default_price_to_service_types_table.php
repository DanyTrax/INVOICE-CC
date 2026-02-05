<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar precio por defecto opcional a los tipos de trámite.
     */
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->decimal('default_price', 15, 2)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('default_price');
        });
    }
};
