<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Renombrar el rol panel_user a admin en todo el sistema.
     */
    public function up(): void
    {
        // Tabla roles (Spatie Permission)
        DB::table('roles')->where('name', 'panel_user')->update(['name' => 'admin']);

        // Tabla role_hierarchy: referencias por nombre de rol
        DB::table('role_hierarchy')->where('can_create_role', 'panel_user')->update(['can_create_role' => 'admin']);
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'admin')->update(['name' => 'panel_user']);
        DB::table('role_hierarchy')->where('can_create_role', 'admin')->update(['can_create_role' => 'panel_user']);
    }
};
