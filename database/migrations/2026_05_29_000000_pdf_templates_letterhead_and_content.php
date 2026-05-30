<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['quote_pdf_templates', 'proposal_pdf_templates'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'letterhead_path')) {
                    $table->string('letterhead_path', 255)->nullable()->after('logo_path');
                }
                if (! Schema::hasColumn($tableName, 'side_note_html')) {
                    $table->text('side_note_html')->nullable()->after('body_html');
                }
                if (! Schema::hasColumn($tableName, 'closing_footer_html')) {
                    $table->text('closing_footer_html')->nullable()->after('side_note_html');
                }
                if (! Schema::hasColumn($tableName, 'signature_name_font_size')) {
                    $table->unsignedTinyInteger('signature_name_font_size')->default(11)->after('signature_position');
                }
                if (! Schema::hasColumn($tableName, 'signature_position_font_size')) {
                    $table->unsignedTinyInteger('signature_position_font_size')->default(11)->after('signature_name_font_size');
                }
            });
        }

        foreach (['quotes', 'proposals'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'pdf_body_html')) {
                    $table->text('pdf_body_html')->nullable()->after('pdf_footer');
                }
                if (! Schema::hasColumn($tableName, 'pdf_side_note_html')) {
                    $table->text('pdf_side_note_html')->nullable()->after('pdf_body_html');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['quote_pdf_templates', 'proposal_pdf_templates'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['letterhead_path', 'side_note_html', 'closing_footer_html', 'signature_name_font_size', 'signature_position_font_size'] as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        foreach (['quotes', 'proposals'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['pdf_body_html', 'pdf_side_note_html'] as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
