<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Renombrar el rol panel_user a admin en instalaciones existentes.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        DB::table('roles')->where('name', 'panel_user')->update(['name' => 'admin']);

        if (Schema::hasTable('role_hierarchy')) {
            DB::table('role_hierarchy')->where('can_create_role', 'panel_user')->update(['can_create_role' => 'admin']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        DB::table('roles')->where('name', 'admin')->update(['name' => 'panel_user']);

        if (Schema::hasTable('role_hierarchy')) {
            DB::table('role_hierarchy')->where('can_create_role', 'admin')->update(['can_create_role' => 'panel_user']);
        }
    }
};
