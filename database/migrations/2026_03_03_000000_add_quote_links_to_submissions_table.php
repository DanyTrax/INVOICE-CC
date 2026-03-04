<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuoteLinksToSubmissionsTable extends Migration
{
    public function up(): void
    {
        // Añadir columnas solo si no existen (idempotente)
        if (!Schema::hasColumn('submissions', 'quote_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->foreignId('quote_id')
                    ->nullable()
                    ->after('process_id')
                    ->constrained('quotes')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('submissions', 'quote_item_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->foreignId('quote_item_id')
                    ->nullable()
                    ->after('quote_id')
                    ->constrained('quote_items')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('submissions', 'quote_item_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropForeign(['quote_item_id']);
                $table->dropColumn('quote_item_id');
            });
        }

        if (Schema::hasColumn('submissions', 'quote_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropForeign(['quote_id']);
                $table->dropColumn('quote_id');
            });
        }
    }
}

