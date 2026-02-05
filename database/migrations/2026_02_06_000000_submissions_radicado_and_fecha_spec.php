<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajustar submissions al spec: radicado_invima, fecha_radicacion, status (En Estudio, Requerido, Aprobado, Rechazado).
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('radicado_invima', 64)->nullable()->after('parent_id');
            $table->date('fecha_radicacion')->nullable()->after('tracking_id');
        });

        foreach (DB::table('submissions')->get() as $row) {
            DB::table('submissions')->where('id', $row->id)->update([
                'radicado_invima' => $row->filing_number,
                'fecha_radicacion' => $row->filing_date,
            ]);
        }

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['filing_number', 'filing_date']);
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->index('radicado_invima');
        });

        DB::table('submissions')->where('status', 'Rechazado/Negado')->update(['status' => 'Rechazado']);
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('filing_number', 64)->nullable()->after('parent_id');
            $table->date('filing_date')->nullable()->after('tracking_id');
        });

        foreach (DB::table('submissions')->get() as $row) {
            DB::table('submissions')->where('id', $row->id)->update([
                'filing_number' => $row->radicado_invima,
                'filing_date' => $row->fecha_radicacion,
            ]);
        }

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['radicado_invima']);
            $table->dropColumn(['radicado_invima', 'fecha_radicacion']);
        });
    }
};
