<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade quote_id a processes para vincular expedientes a una cotización (organización en acordeones).
     */
    public function up(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->foreignId('quote_id')->nullable()->after('quote_item_id')->constrained('quotes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign(['quote_id']);
        });
    }
};
