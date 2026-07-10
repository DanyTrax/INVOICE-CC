<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los clientes en "pendiente" deben poder iniciar sesión (pantalla de cuenta pendiente).
     * Antes is_active solo era true para "activo"; se alinea con la nueva regla.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'client_status')) {
            return;
        }

        DB::table('users')
            ->where('client_status', 'pendiente')
            ->where('is_active', false)
            ->update(['is_active' => true]);
    }

    public function down(): void
    {
        // No revertir: no sabemos cuáles eran pendiente antes de la migración de datos.
    }
};
