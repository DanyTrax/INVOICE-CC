<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajustar valores de status al spec:
     * processes: Recolección, Radicado, En Requerimiento, Finalizado
     * checklist_items: Pendiente, Recibido, Traducción, Aprobado
     */
    public function up(): void
    {
        DB::table('processes')->where('status', 'Recolección Documentos')->update(['status' => 'Recolección']);
        DB::table('checklist_items')->where('status', 'En Traducción')->update(['status' => 'Traducción']);
    }

    public function down(): void
    {
        DB::table('processes')->where('status', 'Recolección')->update(['status' => 'Recolección Documentos']);
        DB::table('checklist_items')->where('status', 'Traducción')->update(['status' => 'En Traducción']);
    }
};
