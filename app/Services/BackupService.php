<?php

namespace App\Services;

use App\Models\SystemBackup;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService
{
    public function createBackup(): SystemBackup
    {
        /** @var User $user */
        $user = Auth::user();

        $now = now();
        $fileName = 'backup-rams-' . $now->format('Ymd-His') . '.json';
        $localPath = storage_path('app/tmp/' . $fileName);

        if (!is_dir(dirname($localPath))) {
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
            'model_has_roles',
            'role_has_permissions',
            'companies',
            'company_user',
            'registrations',
            'processes',
            'process_documents',
            'submissions',
            'documents',
            'quotes',
            'quote_items',
            'service_types',
            'proposals',
            'email_templates',
            'settings',
            'email_logs',
            'drive_operations_log',
            'company_invites',
        ];

        foreach ($tables as $table) {
            try {
                $payload['tables'][$table] = DB::table($table)->get()->toArray();
            } catch (Throwable $e) {
                Log::error('Error exportando tabla para backup', [
                    'table' => $table,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        file_put_contents($localPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $size = filesize($localPath) ?: null;

        /** @var \App\Services\GoogleDriveService $drive */
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

        if (!is_array($payload) || empty($payload['tables']) || !is_array($payload['tables'])) {
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

            foreach ($payload['tables'] as $table => $rows) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

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

    public function restoreBackupFromFile(\Illuminate\Http\UploadedFile $file): void
    {
        $content = file_get_contents($file->getRealPath());
        $this->restoreBackupFromJson($content);
    }

    public function wipeDataExceptSuperAdmin(): void
    {
        DB::transaction(function () {
            // IDs de super admins
            $superAdminIds = User::role('super_admin')->pluck('id');

            // Tablas de negocio que se pueden vaciar
            $truncateTables = [
                'company_user',
                'documents',
                'registrations',
                'companies',
                'email_logs',
                'drive_operations_log',
                'company_invites',
            ];

            foreach ($truncateTables as $table) {
                DB::table($table)->truncate();
            }

            // Eliminar usuarios que no sean super_admin
            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->whereNotIn('model_id', $superAdminIds)
                ->delete();

            DB::table('users')
                ->whereNotIn('id', $superAdminIds)
                ->delete();
        });
    }
}

