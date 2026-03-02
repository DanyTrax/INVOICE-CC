<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quotes', 'pdf_footer')) {
            return;
        }
        Schema::table('quotes', function (Blueprint $table) {
            $table->text('pdf_footer')->nullable()->after('cancellation_note');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('quotes', 'pdf_footer')) {
            return;
        }
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('pdf_footer');
        });
    }
};
