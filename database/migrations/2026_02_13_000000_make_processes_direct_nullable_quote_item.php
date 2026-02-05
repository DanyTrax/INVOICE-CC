<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Procesos directos (sin cotización): quote_item_id nullable.
     * Añade service_type_id y product_reference para procesos sin quote.
     */
    public function up(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign(['quote_item_id']);
        });
        Schema::table('processes', function (Blueprint $table) {
            $table->unsignedBigInteger('quote_item_id')->nullable()->change();
            $table->foreign('quote_item_id')->references('id')->on('quote_items')->nullOnDelete();
        });

        Schema::table('processes', function (Blueprint $table) {
            $table->foreignId('service_type_id')->nullable()->after('quote_item_id')->constrained('service_types')->nullOnDelete();
            $table->string('product_reference', 500)->nullable()->after('service_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->dropColumn(['service_type_id', 'product_reference']);
        });
        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign(['quote_item_id']);
            $table->foreignId('quote_item_id')->nullable(false)->change();
            $table->foreign('quote_item_id')->references('id')->on('quote_items')->cascadeOnDelete();
        });
    }
};
