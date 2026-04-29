<?php

namespace App\Services;

use App\Models\SystemBackup;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class BackupService
{
    public const SCOPE_EMAIL_TEMPLATES = 'email_templates';

    public const SCOPE_EMPRESA = 'empresa';

    public const SCOPE_MAIL = 'mail_connection';

    public const SCOPE_DRIVE = 'drive_connection';

    /**
     * Bloques disponibles para restauración selectiva (sin tocar el resto de tablas).
     *
     * @return array<string, array{label: string, description: string, tables: list<string>, settings_keys: list<string>}>
     */
    public static function selectiveRestoreScopeDefinitions(): array
    {
        return [
            self::SCOPE_EMAIL_TEMPLATES => [
                'label' => 'Plantillas de correo electrónico',
                'description' => 'Todas las plantillas de email del sistema (asunto, cuerpo, tipo).',
                'tables' => ['email_templates'],
                'settings_keys' => [],
            ],
            self::SCOPE_EMPRESA => [
                'label' => 'Empresa, legales y plantillas PDF',
                'description' => 'Marca blanca (nombre, NIT, contacto, logo), pie y nombre del sistema, textos legales públicos, ajustes de PDF en configuración y registros de plantillas PDF de cotización y propuesta.',
                'tables' => ['quote_pdf_templates', 'proposal_pdf_templates'],
                'settings_keys' => [
                    'agency_name',
                    'agency_nit',
                    'agency_address',
                    'agency_phone',
                    'agency_email',
                    'agency_website',
                    'agency_logo',
                    'footer_text',
                    'system_name',
                    'quote_pdf_header_subtitle',
                    'quote_pdf_footer_text',
                    'legal_privacy_title',
                    'legal_terms_title',
                    'legal_show_privacy_on_login',
                    'legal_show_terms_on_login',
                    'legal_privacy_html',
                    'legal_terms_html',
                ],
            ],
            self::SCOPE_MAIL => [
                'label' => 'Conexión de correo (SMTP y Zoho)',
                'description' => 'Proveedor de envío, servidor SMTP, credenciales, remitente y tokens Zoho Mail según estén guardados en el backup.',
                'tables' => [],
                'settings_keys' => [
                    'mail_provider',
                    'mail_mailer',
                    'mail_host',
                    'mail_port',
                    'mail_username',
                    'mail_password',
                    'mail_encryption',
                    'mail_from_address',
                    'mail_from_name',
                    'zoho_client_id',
                    'zoho_client_secret',
                    'zoho_refresh_token',
                    'zoho_access_token',
                    'zoho_from_email',
                ],
            ],
            self::SCOPE_DRIVE => [
                'label' => 'Conexión Google Drive',
                'description' => 'Modo (Service Account / OAuth), JSON de cuenta de servicio, carpeta raíz, nombres de carpetas y credenciales OAuth según el backup.',
                'tables' => [],
                'settings_keys' => [
                    'drive_mode',
                    'drive_service_account_json',
                    'drive_folder_id',
                    'drive_folder_name_no_client',
                    'drive_folder_name_with_client',
                    'drive_default_country_no_client',
                    'drive_oauth_client_id',
                    'drive_oauth_client_secret',
                    'drive_oauth_refresh_token',
                    'drive_oauth_access_token',
                ],
            ],
        ];
    }

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
            'role_permissions',
            'role_hierarchy',
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
            'quote_pdf_templates',
            'proposal_pdf_templates',
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
     * @param  list<string>|null  $selectiveScopes  Claves de {@see selectiveRestoreScopeDefinitions()}; null = restauración completa.
     *
     * @throws \Exception
     */
    public function restoreBackupFromJson(string $content, ?array $selectiveScopes = null): void
    {
        $payload = json_decode($content, true);

        if (! is_array($payload) || empty($payload['tables']) || ! is_array($payload['tables'])) {
            throw new \Exception('Archivo de backup inválido.');
        }

        if ($selectiveScopes === null) {
            $this->restoreFullPayload($payload);

            return;
        }

        $allowed = array_keys(self::selectiveRestoreScopeDefinitions());
        $selectiveScopes = array_values(array_unique(array_intersect($selectiveScopes, $allowed)));

        if ($selectiveScopes === []) {
            throw new \InvalidArgumentException('Selecciona al menos un bloque válido para restauración parcial.');
        }

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            foreach ($selectiveScopes as $scopeKey) {
                $def = self::selectiveRestoreScopeDefinitions()[$scopeKey];
                foreach ($def['tables'] as $table) {
                    $this->replaceTableFromPayload($payload, $table);
                }
                if ($def['settings_keys'] !== []) {
                    $this->mergeGeneralSettingsKeysFromPayload($payload, $def['settings_keys']);
                }
            }
        } finally {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }

        try {
            Artisan::call('settings:clear-cache');
        } catch (\Throwable) {
            // Ignorar si el comando no está disponible en el entorno
        }
    }

    /**
     * @param  list<string>|null  $selectiveScopes
     */
    public function restoreBackupFromFile(UploadedFile $file, ?array $selectiveScopes = null): void
    {
        $content = file_get_contents($file->getRealPath());
        $this->restoreBackupFromJson($content, $selectiveScopes);
    }

    /**
     * Restauración completa: todas las tablas conocidas del backup.
     */
    protected function restoreFullPayload(array $payload): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            $restoreOrder = $this->fullRestoreTableOrder();

            foreach ($restoreOrder as $table) {
                $this->replaceTableFromPayload($payload, $table);
            }

            $remainingTables = array_diff(array_keys($payload['tables']), $restoreOrder);
            foreach ($remainingTables as $table) {
                $this->replaceTableFromPayload($payload, $table);
            }
        } finally {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }

        try {
            Artisan::call('settings:clear-cache');
        } catch (\Throwable) {
        }
    }

    /**
     * @return list<string>
     */
    protected function fullRestoreTableOrder(): array
    {
        return [
            'users',
            'roles',
            'permissions',
            'role_has_permissions',
            'role_permissions',
            'role_hierarchy',
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
            'quote_pdf_templates',
            'proposal_pdf_templates',
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
    }

    protected function replaceTableFromPayload(array $payload, string $table): void
    {
        if (! isset($payload['tables'][$table]) || ! Schema::hasTable($table)) {
            return;
        }

        $rows = $payload['tables'][$table];
        DB::table($table)->truncate();

        $tableColumns = Schema::getColumnListing($table);

        if (! is_array($rows) || count($rows) === 0) {
            return;
        }

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

    /**
     * @param  list<string>  $settingsKeys  Nombres de filas en group `general`.
     */
    protected function mergeGeneralSettingsKeysFromPayload(array $payload, array $settingsKeys): void
    {
        if (! Schema::hasTable('settings') || ! isset($payload['tables']['settings'])) {
            return;
        }

        $nameSet = array_flip($settingsKeys);
        $tableColumns = Schema::getColumnListing('settings');
        $rows = $payload['tables']['settings'];

        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $rowArray = (array) $row;
            if (($rowArray['group'] ?? '') !== 'general') {
                continue;
            }
            $name = $rowArray['name'] ?? '';
            if ($name === '' || ! isset($nameSet[$name])) {
                continue;
            }

            $clean = array_filter(
                array_intersect_key($rowArray, array_flip($tableColumns)),
                fn ($value, $key) => in_array($key, $tableColumns, true),
                ARRAY_FILTER_USE_BOTH
            );

            $payloadVal = $clean['payload'] ?? null;
            if ($payloadVal !== null && ! is_string($payloadVal)) {
                $payloadVal = json_encode($payloadVal);
            }
            $payloadJson = is_string($payloadVal) ? $payloadVal : 'null';

            $group = $clean['group'];
            $locked = (bool) ($clean['locked'] ?? false);

            $exists = DB::table('settings')
                ->where('group', $group)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                DB::table('settings')
                    ->where('group', $group)
                    ->where('name', $name)
                    ->update([
                        'locked' => $locked,
                        'payload' => $payloadJson,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('settings')->insert([
                    'group' => $group,
                    'name' => $name,
                    'locked' => $locked,
                    'payload' => $payloadJson,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Vaciar datos de negocio dejando solo el super_admin.
     */
    public function wipeDataExceptSuperAdmin(bool $preserveCurrentUser = true, bool $preserveRolesAndPermissions = true): void
    {
        // MySQL: TRUNCATE hace COMMIT implícito; DB::transaction() rompe al finalizar ("no active transaction").
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            $superAdminIds = User::role('super_admin')->pluck('id')->toArray();

            if ($preserveCurrentUser && Auth::check()) {
                $currentUserId = Auth::id();
                if ($currentUserId && ! in_array($currentUserId, $superAdminIds, true)) {
                    $superAdminIds[] = $currentUserId;
                }
            }

            // Orden: hijos de expediente → processes → empresas/cotizaciones (FK relajadas; orden legible).
            $truncateTables = [
                'company_user',
                'documents',
                'registrations',
                'process_documents',
                'process_user',
                'regulatory_events',
                'submissions',
                'checklist_items',
                'processes',
                'quote_items',
                'quotes',
                'proposal_items',
                'proposals',
                'quote_pdf_templates',
                'proposal_pdf_templates',
                'companies',
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
                foreach ([
                    'role_hierarchy',
                    'role_permissions',
                    'role_has_permissions',
                    'model_has_roles',
                    'permissions',
                    'roles',
                ] as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->truncate();
                    }
                }
                $this->bootstrapSuperAdminRoleOnly($superAdminIds);
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
        } finally {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    /**
     * Tras vaciar roles/permisos, deja solo el rol Spatie super_admin y lo asigna a los usuarios conservados.
     */
    protected function bootstrapSuperAdminRoleOnly(array $superAdminUserIds): void
    {
        if ($superAdminUserIds === []) {
            return;
        }

        $role = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        foreach ($superAdminUserIds as $userId) {
            $user = User::find($userId);
            if ($user !== null) {
                $user->syncRoles([$role]);
            }
        }

        app(PermissionService::class)->clearCache();

        if (app()->bound(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
