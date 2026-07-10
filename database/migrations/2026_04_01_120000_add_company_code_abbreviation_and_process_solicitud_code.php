<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('companies') && ! Schema::hasColumn('companies', 'code_abbreviation')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('code_abbreviation', 10)->nullable()->after('name');
            });
        }

        if (! Schema::hasTable('processes')) {
            return;
        }

        if (! Schema::hasColumn('processes', 'request_sequence')) {
            Schema::table('processes', function (Blueprint $table) {
                $table->unsignedInteger('request_sequence')->nullable()->after('client_id');
                $table->string('solicitud_code', 24)->nullable()->after('request_sequence');
            });
        }

        if (! $this->indexExists('processes', 'processes_client_solicitud_code_unique')) {
            Schema::table('processes', function (Blueprint $table) {
                $table->unique(['client_id', 'solicitud_code'], 'processes_client_solicitud_code_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('processes')) {
            if ($this->indexExists('processes', 'processes_client_solicitud_code_unique')) {
                Schema::table('processes', function (Blueprint $table) {
                    $table->dropUnique('processes_client_solicitud_code_unique');
                });
            }

            if (Schema::hasColumn('processes', 'request_sequence')) {
                Schema::table('processes', function (Blueprint $table) {
                    $table->dropColumn(['request_sequence', 'solicitud_code']);
                });
            }
        }

        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'code_abbreviation')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('code_abbreviation');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $rows = $connection->select("PRAGMA index_list('{$table}')");

            return collect($rows)->contains(fn ($row) => ($row->name ?? null) === $index);
        }

        $database = $connection->getDatabaseName();
        $result = $connection->select(
            'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index]
        );

        return (int) ($result[0]->c ?? 0) > 0;
    }
};
