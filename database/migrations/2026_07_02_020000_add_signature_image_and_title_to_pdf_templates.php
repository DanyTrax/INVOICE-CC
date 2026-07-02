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
                if (! Schema::hasColumn($tableName, 'signature_image_path')) {
                    $table->string('signature_image_path')->nullable()->after('signature_position');
                }
                if (! Schema::hasColumn($tableName, 'signature_image_drive_id')) {
                    $table->string('signature_image_drive_id')->nullable()->after('signature_image_path');
                }
                if (! Schema::hasColumn($tableName, 'signature_image_height_px')) {
                    $table->unsignedSmallInteger('signature_image_height_px')->default(55)->after('signature_image_drive_id');
                }
                if (! Schema::hasColumn($tableName, 'doc_title_text')) {
                    $table->string('doc_title_text', 128)->nullable()->after('signature_image_height_px');
                }
                if (! Schema::hasColumn($tableName, 'doc_title_font_size')) {
                    $table->unsignedTinyInteger('doc_title_font_size')->default(9)->after('doc_title_text');
                }
                if (! Schema::hasColumn($tableName, 'doc_title_bold')) {
                    $table->boolean('doc_title_bold')->default(true)->after('doc_title_font_size');
                }
            });
        }

        if (Schema::hasTable('quotes') && ! Schema::hasColumn('quotes', 'show_pdf_signature')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->boolean('show_pdf_signature')->default(true)->after('show_pdf_footer');
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
                foreach ([
                    'signature_image_path',
                    'signature_image_drive_id',
                    'signature_image_height_px',
                    'doc_title_text',
                    'doc_title_font_size',
                    'doc_title_bold',
                ] as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $cols[] = $col;
                    }
                }
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('quotes') && Schema::hasColumn('quotes', 'show_pdf_signature')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->dropColumn('show_pdf_signature');
            });
        }
    }
};
