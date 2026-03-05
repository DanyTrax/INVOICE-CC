<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('quotes', 'show_description_column')) {
                $table->boolean('show_description_column')->default(true)->after('show_service_type_column');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'show_description_column')) {
                $table->dropColumn('show_description_column');
            }
        });
    }
};

