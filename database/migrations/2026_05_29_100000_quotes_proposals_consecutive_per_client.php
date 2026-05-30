<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique(['consecutive']);
            $table->unique(['client_id', 'consecutive'], 'quotes_client_consecutive_unique');
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->dropUnique(['consecutive']);
            $table->unique(['client_id', 'consecutive'], 'proposals_client_consecutive_unique');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique('quotes_client_consecutive_unique');
            $table->unique('consecutive');
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->dropUnique('proposals_client_consecutive_unique');
            $table->unique('consecutive');
        });
    }
};
