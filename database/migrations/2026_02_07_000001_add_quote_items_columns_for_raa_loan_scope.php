<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Re-ingeniería quote_items: posición, RAA, expediente anterior, is_loan, alcance.
     */
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->unsignedInteger('item_position')->default(0)->after('quote_id');
            $table->string('raa_code', 64)->nullable()->after('service_type_id');
            $table->string('previous_license', 64)->nullable()->after('raa_code'); // Expediente/Licencia anterior
            $table->text('scope')->nullable()->after('description'); // Alcance
            $table->boolean('is_loan')->default(false)->after('invima_rate_value');
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn(['item_position', 'raa_code', 'previous_license', 'scope', 'is_loan']);
        });
    }
};
