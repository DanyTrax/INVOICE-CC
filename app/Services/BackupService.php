<?php

namespace App\Services;

use App\Models\SystemBackup;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BackupService
{
    public function createBackup(): SystemBackup
    {
        /** @var User $user */
        $user = Auth::user();

        $now = now();
        $fileName = 'backup-rams-'.$now->format('Ymd-His').'.json';
        $localPath = storage_path('app/tmp/'.$fileName);

        if (! is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0775, true);
        }

        $payload = [
            'meta' => [
                'generated_at' => $now->toIso8601String(),
                'app_env' => config('app.env'),
                'app_url' => config('app.url'),
                'version' => config('app.version', null),
            ],
            'tables' => [],
        ];

        $tables = [
            'users',
            'roles',
            'permissions',
            'role_has_permissions',
            'model_has_roles',
            'companies',
            'company_user',
            'registrations',
            'services',
            'service_types',
            'concept_catalogs',
            'quotes',
            'quote_items',
            'proposals',
            'proposal_items',
            'processes',
            'process_documents',
            'process_user',
            'submissions',
            'regulatory_events',
            'documents',
            'checklist_items',
            'capacitacion_videos',
            'capacitacion_completions',
            'email_templates',
            'settings',
            'email_logs',
            'drive_operations_log',
            'company_invites',
            'activity_logs',
        ];

        foreach ($tables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $payload['tables'][$table] = DB::table($table)->get()->toArray();
                } else {
                    $payload['tables'][$table] = [];
                }
            } catch (Throwable $e) {
                Log::error('Error exportando tabla para backup', [
                    'table' => $table,
                    'error' => $e->getMessage(),
                ]);
                $payload['tables'][$table] = [];
            }
        }

        // Verificación rápida: se guardaron todas las tablas definidas (si existen)
        $missing = array_filter($tables, fn ($table) => ! array_key_exists($table, $payload['tables']));
        if (! empty($missing)) {
            Log::warning('Backup incompleto: algunas tablas definidas faltan en payload', ['missing' => $missing]);
        }

        file_put_contents($localPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $size = filesize($localPath) ?: null;

        /** @var GoogleDriveService $drive */
        $drive = app(GoogleDriveService::class);

        // Crear/cargar carpeta "Backups RAMS" en el Drive raíz configurado
        $folderId = $drive->getOrCreateBackupsFolder();
        $uploadResult = $drive->uploadFile($localPath, $fileName, $folderId, 'application/json');
        $driveFileId = $uploadResult['id'];

        @unlink($localPath);

        return SystemBackup::create([
            'name' => $fileName,
            'drive_file_id' => $driveFileId,
            'size_bytes' => $size,
            'type' => 'manual',
            'created_by_id' => $user->id,
        ]);
    }

    /**
     * Vaciar datos de negocio dejando solo el super_admin.
     */
    public function restoreBackupFromJson(string $content): void
    {
        $payload = json_decode($content, true);

        if (! is_array($payload) || empty($payload['tables']) || ! is_array($payload['tables'])) {
            throw new \Exception('Archivo de backup inválido.');
        }

        DB::transaction(function () use ($payload) {
            // Deshabilitar temporalmente FK para SQLite y MySQL
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            }
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            $restoreOrder = [
                'users',
                'roles',
                'permissions',
                'role_has_permissions',
                'model_has_roles',
                'companies',
                'company_user',
                'services',
                'service_types',
                'concept_catalogs',
                'quotes',
                'quote_items',
                'proposals',
                'proposal_items',
                'registrations',
                'processes',
                'process_documents',
                'process_user',
                'submissions',
                'regulatory_events',
                'documents',
                'checklist_items',
                'capacitacion_videos',
                'capacitacion_completions',
                'email_templates',
                'settings',
                'email_logs',
                'drive_operations_log',
                'company_invites',
                'activity_logs',
            ];

            foreach ($restoreOrder as $table) {
                if (! isset($payload['tables'][$table]) || ! Schema::hasTable($table)) {
                    continue;
                }

                $rows = $payload['tables'][$table];
                DB::table($table)->truncate();

                $tableColumns = Schema::getColumnListing($table);

                if (is_array($rows) && count($rows) > 0) {
                    $cleanRows = array_map(function ($row) use ($tableColumns) {
                        $rowArray = (array) $row;

                        return array_filter(
                            array_intersect_key($rowArray, array_flip($tableColumns)),
                            fn ($value, $key) => in_array($key, $tableColumns, true),
                            ARRAY_FILTER_USE_BOTH
                        );
                    }, $rows);

                    foreach (array_chunk($cleanRows, 1000) as $chunk) {
                        if (count($chunk) > 0) {
                            DB::table($table)->insert($chunk);
                        }
                    }
                }
            }

            // Cargar tablas extra que estén en el payload y no en la lista principal
            $remainingTables = array_diff(array_keys($payload['tables']), $restoreOrder);
            foreach ($remainingTables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $rows = $payload['tables'][$table];
                DB::table($table)->truncate();

                $tableColumns = Schema::getColumnListing($table);

                if (is_array($rows) && count($rows) > 0) {
                    $cleanRows = array_map(function ($row) use ($tableColumns) {
                        $rowArray = (array) $row;

                        return array_filter(
                            array_intersect_key($rowArray, array_flip($tableColumns)),
                            fn ($value, $key) => in_array($key, $tableColumns, true),
                            ARRAY_FILTER_USE_BOTH
                        );
                    }, $rows);

                    foreach (array_chunk($cleanRows, 1000) as $chunk) {
                        if (count($chunk) > 0) {
                            DB::table($table)->insert($chunk);
                        }
                    }
                }
            }

            // Reactivar llaves foráneas
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        });
    }

    public function restoreBackupFromFile(UploadedFile $file): void
    {
        $content = file_get_contents($file->getRealPath());
        $this->restoreBackupFromJson($content);
    }

    public function wipeDataExceptSuperAdmin(bool $preserveCurrentUser = true, bool $preserveRolesAndPermissions = true): void
    {
        DB::transaction(function () use ($preserveCurrentUser, $preserveRolesAndPermissions) {
            // MySQL: TRUNCATE falla en tablas padre si hay FK desde hijas (aunque estén vacías).
            // Misma idea que restoreBackupFromJson.
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            }
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            $superAdminIds = User::role('super_admin')->pluck('id')->toArray();

            if ($preserveCurrentUser && Auth::check()) {
                $currentUserId = Auth::id();
                if ($currentUserId && ! in_array($currentUserId, $superAdminIds, true)) {
                    $superAdminIds[] = $currentUserId;
                }
            }

            $truncateTables = [
                'company_user',
                'documents',
                'registrations',
                'companies',
                'process_documents',
                'process_user',
                'submissions',
                'regulatory_events',
                'checklist_items',
                'quotes',
                'quote_items',
                'proposals',
                'proposal_items',
                'services',
                'service_types',
                'concept_catalogs',
                'email_templates',
                'settings',
                'email_logs',
                'drive_operations_log',
                'company_invites',
                'activity_logs',
                'capacitacion_videos',
                'capacitacion_completions',
            ];

            foreach ($truncateTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            if (! $preserveRolesAndPermissions) {
                foreach (['model_has_roles', 'role_has_permissions', 'roles', 'permissions'] as $table) {
                    if (Schema::hasTable($table)) {
                        if ($table === 'model_has_roles') {
                            DB::table($table)->where('model_type', User::class)->truncate();
                        } else {
                            DB::table($table)->truncate();
                        }
                    }
                }
            } else {
                if (Schema::hasTable('model_has_roles')) {
                    DB::table('model_has_roles')
                        ->where('model_type', User::class)
                        ->whereNotIn('model_id', $superAdminIds)
                        ->delete();
                }
            }

            if (Schema::hasTable('users')) {
                DB::table('users')
                    ->whereNotIn('id', $superAdminIds)
                    ->delete();
            }

            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        });
    }
}
