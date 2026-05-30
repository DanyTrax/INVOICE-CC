<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['quote_pdf_templates', 'proposal_pdf_templates'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'signature_margin_top_px')) {
                    $table->unsignedSmallInteger('signature_margin_top_px')->default(130)->after('signature_position_font_size');
                }
                if (! Schema::hasColumn($tableName, 'letterhead_footer_reserve_mm')) {
                    $table->unsignedTinyInteger('letterhead_footer_reserve_mm')->default(42)->after('signature_margin_top_px');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['quote_pdf_templates', 'proposal_pdf_templates'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $cols = [];
                if (Schema::hasColumn($tableName, 'signature_margin_top_px')) {
                    $cols[] = 'signature_margin_top_px';
                }
                if (Schema::hasColumn($tableName, 'letterhead_footer_reserve_mm')) {
                    $cols[] = 'letterhead_footer_reserve_mm';
                }
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
