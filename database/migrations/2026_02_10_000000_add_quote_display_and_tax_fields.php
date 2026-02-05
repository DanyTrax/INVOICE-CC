<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columnas de visualización (Expediente/INVIMA, RAA) e impuesto (IVA).
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->boolean('show_prev_license_column')->default(false)->after('exchange_rate');
            $table->boolean('show_raa_column')->default(false)->after('show_prev_license_column');
            $table->boolean('apply_tax')->default(false)->after('total_loans');
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('apply_tax');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'show_prev_license_column',
                'show_raa_column',
                'apply_tax',
                'tax_percentage',
            ]);
        });
    }
};
