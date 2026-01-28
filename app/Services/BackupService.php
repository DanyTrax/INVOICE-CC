<?php

namespace App\Services;

use App\Models\SystemBackup;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'companies',
            'company_user',
            'registrations',
            'documents',
            'email_templates',
            'settings',
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
        $folderId = $drive->getOrCreateFolder('Backups RAMS', null);
        $driveFileId = $drive->uploadFileFromPath($localPath, $fileName, $folderId);

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

