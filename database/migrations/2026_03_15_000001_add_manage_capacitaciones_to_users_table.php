<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'manage_capacitaciones')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('manage_capacitaciones')->default(false)->after('client_status');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'manage_capacitaciones')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('manage_capacitaciones');
        });
    }
};
