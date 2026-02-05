<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_pdf_templates', function (Blueprint $table) {
            $table->string('signature_name', 128)->nullable()->after('footer_text');
            $table->string('signature_position', 128)->nullable()->after('signature_name');
        });
    }

    public function down(): void
    {
        Schema::table('quote_pdf_templates', function (Blueprint $table) {
            $table->dropColumn(['signature_name', 'signature_position']);
        });
    }
};
