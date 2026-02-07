<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flujo reactivo INVIMA: submission_date (sometimiento), submission_code;
     * filing_date/filing_number ya existen como fecha_radicacion/radicado_invima.
     * Status: Pendiente, En Requerimiento, Aprobado, Rechazado.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dateTime('submission_date')->nullable()->after('parent_id');
            $table->string('submission_code', 64)->nullable()->after('submission_date');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['submission_date', 'submission_code']);
        });
    }
};
