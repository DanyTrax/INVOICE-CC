<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Logo embebido en BD (base64 + mime) para respaldos JSON sin depender de archivos en disco.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->longText('logo_base64')->nullable()->after('logo_path');
            $table->string('logo_mime', 128)->nullable()->after('logo_base64');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['logo_base64', 'logo_mime']);
        });
    }
};
