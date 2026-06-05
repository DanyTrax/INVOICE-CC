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
                if (! Schema::hasColumn($tableName, 'letterhead_drive_id')) {
                    $table->string('letterhead_drive_id', 255)->nullable()->after('letterhead_path');
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
                if (Schema::hasColumn($tableName, 'letterhead_drive_id')) {
                    $table->dropColumn('letterhead_drive_id');
                }
            });
        }
    }
};
