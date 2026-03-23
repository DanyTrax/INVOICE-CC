<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columnas opcionales en cotización (visibilidad) e ítems (valores).
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('quotes', 'show_row_id_column')) {
                $table->boolean('show_row_id_column')->default(false);
            }
            if (!Schema::hasColumn('quotes', 'show_franquicia_column')) {
                $table->boolean('show_franquicia_column')->default(false);
            }
            if (!Schema::hasColumn('quotes', 'show_centro_costos_column')) {
                $table->boolean('show_centro_costos_column')->default(false);
            }
            if (!Schema::hasColumn('quotes', 'show_contacto_column')) {
                $table->boolean('show_contacto_column')->default(false);
            }
        });

        Schema::table('quote_items', function (Blueprint $table) {
            if (!Schema::hasColumn('quote_items', 'row_id')) {
                $table->string('row_id', 128)->nullable();
            }
            if (!Schema::hasColumn('quote_items', 'franquicia')) {
                $table->string('franquicia', 255)->nullable();
            }
            if (!Schema::hasColumn('quote_items', 'centro_costos')) {
                $table->string('centro_costos', 255)->nullable();
            }
            if (!Schema::hasColumn('quote_items', 'contacto')) {
                $table->string('contacto', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            foreach (['contacto', 'centro_costos', 'franquicia', 'row_id'] as $col) {
                if (Schema::hasColumn('quote_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('quotes', function (Blueprint $table) {
            foreach (['show_contacto_column', 'show_centro_costos_column', 'show_franquicia_column', 'show_row_id_column'] as $col) {
                if (Schema::hasColumn('quotes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
