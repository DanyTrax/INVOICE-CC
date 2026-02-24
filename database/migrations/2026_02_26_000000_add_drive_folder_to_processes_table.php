<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->string('drive_folder_id', 255)->nullable()->after('expediente_invima');
            $table->string('drive_folder_url', 500)->nullable()->after('drive_folder_id');
        });
    }

    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropColumn(['drive_folder_id', 'drive_folder_url']);
        });
    }
};
