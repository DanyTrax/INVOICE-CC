<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('quotes', 'show_pdf_side_note')) {
                $table->boolean('show_pdf_side_note')->default(true)->after('pdf_side_note_html');
            }
            if (! Schema::hasColumn('quotes', 'show_pdf_footer')) {
                $table->boolean('show_pdf_footer')->default(true)->after('pdf_footer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'show_pdf_side_note')) {
                $table->dropColumn('show_pdf_side_note');
            }
            if (Schema::hasColumn('quotes', 'show_pdf_footer')) {
                $table->dropColumn('show_pdf_footer');
            }
        });
    }
};
