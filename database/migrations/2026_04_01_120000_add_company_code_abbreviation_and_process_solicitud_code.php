<?php

use App\Models\Company;
use App\Models\Process;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('code_abbreviation', 10)->nullable()->after('name');
        });

        Schema::table('processes', function (Blueprint $table) {
            $table->unsignedInteger('request_sequence')->nullable()->after('client_id');
            $table->string('solicitud_code', 24)->nullable()->after('request_sequence');
        });

        foreach (Company::orderBy('id')->cursor() as $company) {
            if (blank($company->code_abbreviation)) {
                $s = Company::suggestCodeAbbreviationFromName((string) $company->name);
                if (mb_strlen($s) < 2) {
                    $s = 'C'.$company->id;
                }
                $company->forceFill([
                    'code_abbreviation' => Str::upper(Str::limit($s, 10, '')),
                ])->saveQuietly();
            }
        }

        $byClient = Process::orderBy('id')->get()->groupBy('client_id');
        foreach ($byClient as $clientId => $rows) {
            $company = Company::find($clientId);
            $prefix = $company ? Str::upper(trim((string) $company->code_abbreviation)) : 'C'.$clientId;
            if ($prefix === '') {
                $prefix = 'C'.$clientId;
            }
            $n = 0;
            foreach ($rows as $process) {
                $n++;
                $width = max(3, strlen((string) $n));
                $code = $prefix.'-'.str_pad((string) $n, $width, '0', STR_PAD_LEFT);
                $process->forceFill([
                    'request_sequence' => $n,
                    'solicitud_code' => $code,
                ])->saveQuietly();
            }
        }

        Schema::table('processes', function (Blueprint $table) {
            $table->unique(['client_id', 'solicitud_code'], 'processes_client_solicitud_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropUnique('processes_client_solicitud_code_unique');
        });

        Schema::table('processes', function (Blueprint $table) {
            $table->dropColumn(['request_sequence', 'solicitud_code']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('code_abbreviation');
        });
    }
};
