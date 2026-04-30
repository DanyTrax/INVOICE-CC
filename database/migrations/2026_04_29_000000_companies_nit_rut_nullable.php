<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite varias empresas "sin NIT" (NULL). El valor "0" como placeholder dejaba de ser único.
     */
    public function up(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        // Pasar valores equivalentes a "sin NIT" a NULL antes del unique nullable
        DB::table('companies')->select(['id', 'nit_rut'])->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $nit = trim((string) ($row->nit_rut ?? ''));
                if ($nit === '' || preg_match('/^0+$/', $nit)) {
                    DB::table('companies')->where('id', $row->id)->update(['nit_rut' => null]);
                }
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('nit_rut')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        DB::table('companies')->whereNull('nit_rut')->update(['nit_rut' => '']);

        Schema::table('companies', function (Blueprint $table) {
            $table->string('nit_rut')->nullable(false)->change();
        });
    }
};
