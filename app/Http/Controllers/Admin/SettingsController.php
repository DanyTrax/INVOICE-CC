<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Controller;
use App\Models\AppErrorLog;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\LoginIpLockout;
use App\Models\User;
use App\Services\GitWorkingCopyService;
use App\Services\LoginLockoutService;
use App\Services\MailService;
use App\Services\PermissionService;
use App\Settings\GeneralSettings;
use App\Support\LegalPageDefaults;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelSettings\Exceptions\MissingSettings;

class SettingsController extends Controller
{
    public function index(Request $request, $section = 'mail')
    {
        // Validar que la sección sea válida
        $validSections = ['mail', 'templates', 'history', 'system', 'legal-policies', 'login-lockouts'];
        if (! in_array($section, $validSections)) {
            $section = 'mail';
        }

        $permissionService = app(PermissionService::class);

        $sectionModuleMap = [
            'mail' => ['settings_mail'],
            'templates' => ['settings_templates'],
            'history' => ['settings_history'],
            'system' => ['settings_system'],
            'legal-policies' => ['settings_system'],
            'login-lockouts' => ['settings_system'],
        ];

        $modules = $sectionModuleMap[$section] ?? null;

        // Verificar permisos de vista por sección (drive: basta con uno de los dos)
        if ($modules) {
            $hasAny = false;
            foreach ((array) $modules as $module) {
                if ($permissionService->userHasPermission($module, 'view')) {
                    $hasAny = true;
                    break;
                }
            }
            if (! $hasAny) {
                $firstAllowed = $this->getFirstAllowedSection($permissionService);

                return redirect()->route('admin.settings.section', $firstAllowed)
                    ->with('error', 'No tienes permisos para acceder a esta sección.');
            }
        }

        // Sección system: controlada por permiso Config: Sistema (Gestión de Permisos)
        if ($section === 'system' && ! $permissionService->userHasPermission('settings_system', 'view')) {
            $firstAllowed = $this->getFirstAllowedSection($permissionService);

            return redirect()->route('admin.settings.section', $firstAllowed)
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        $emailTemplates = EmailTemplate::all();

        // Verificar si la tabla email_logs existe antes de consultarla
        try {
            $emailLogs = EmailLog::orderBy('created_at', 'desc')
                ->with('user')
                ->paginate(20);
        } catch (QueryException $e) {
            // Si la tabla no existe, crear una colección vacía
            $emailLogs = new LengthAwarePaginator(
                collect([]),
                0,
                20,
                1
            );
        }

        // Asegurar que los settings existan en la BD
        $this->ensureSettingsInDatabase();

        // Inicializar settings con valores por defecto si no existen
        try {
            $settings = $this->resolveGeneralSettingsAfterEnsuringDatabase();
        } catch (MissingSettings $e) {
            // Si aún falla, crear settings con valores por defecto
            $settings = new GeneralSettings;
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        $userToDelete = null;
        if ($section === 'system') {
            if ($request->query('cancel_delete_user')) {
                session()->forget('user_to_delete_id');
            } elseif (session('user_to_delete_id')) {
                $userToDelete = User::with('roles', 'companies')->find(session('user_to_delete_id'));
                if (! $userToDelete) {
                    session()->forget('user_to_delete_id');
                }
            }
        }

        $gitInfo = [
            'available' => false,
            'short_hash' => null,
            'full_hash' => null,
            'commit_at' => null,
            'branch' => null,
            'subject' => null,
            'error' => null,
        ];
        if ($section === 'system') {
            $gitInfo = app(GitWorkingCopyService::class)->getInfo();
        }

        $timezoneIdentifiers = [];
        if ($section === 'system') {
            $timezoneIdentifiers = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        }

        $allowedSystemSubs = ['git', 'delete-user', 'customization', 'errors'];
        $systemSub = 'git';
        if ($section === 'system') {
            if (! $request->has('system_sub') && session('user_to_delete_id')) {
                $systemSub = 'delete-user';
            } else {
                $q = (string) $request->query('system_sub', 'git');
                $systemSub = in_array($q, $allowedSystemSubs, true) ? $q : 'git';
            }
        }

        $errorLogs = null;
        $errorLogsUnresolvedCount = 0;
        if ($section === 'system') {
            try {
                $errorLogs = AppErrorLog::with('user')
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*'], 'errors_page')
                    ->withQueryString();
                $errorLogsUnresolvedCount = AppErrorLog::whereNull('resolved_at')->count();
            } catch (QueryException $e) {
                $errorLogs = new LengthAwarePaginator(collect([]), 0, 20, 1);
            }
        }

        $legalDefaults = [
            'privacy' => LegalPageDefaults::privacyHtml(),
            'terms' => LegalPageDefaults::termsHtml(),
        ];

        $loginIpLockouts = null;
        if ($section === 'login-lockouts') {
            try {
                $loginIpLockouts = LoginIpLockout::query()
                    ->orderByRaw('(locked_until IS NOT NULL AND locked_until > ?) DESC', [now()])
                    ->orderByDesc('last_attempt_at')
                    ->orderByDesc('id')
                    ->paginate(25);
            } catch (\Throwable $e) {
                $loginIpLockouts = new LengthAwarePaginator(collect([]), 0, 25, 1);
            }
        }

        return view('admin.settings.index', [
            'settings' => $settings,
            'emailTemplates' => $emailTemplates,
            'emailLogs' => $emailLogs,
            'activeSection' => $section,
            'userToDelete' => $userToDelete,
            'gitInfo' => $gitInfo,
            'systemSub' => $systemSub,
            'timezoneIdentifiers' => $timezoneIdentifiers,
            'legalDefaults' => $legalDefaults,
            'loginIpLockouts' => $loginIpLockouts,
            'errorLogs' => $errorLogs,
            'errorLogsUnresolvedCount' => $errorLogsUnresolvedCount,
        ]);
    }

    /**
     * Desbloquear una IP desde Configuración → Bloqueos de acceso.
     */
    public function unlockLoginLockout(LoginIpLockout $loginIpLockout): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('settings_system', 'edit')) {
            abort(403, 'No tienes permiso para esta acción.');
        }

        app(LoginLockoutService::class)->unlock($loginIpLockout);

        return redirect()
            ->route('admin.settings.section', 'login-lockouts')
            ->with('success', 'La IP '.$loginIpLockout->ip_address.' ha sido desbloqueada.');
    }

    /**
     * Buscar usuario por correo para eliminar, o confirmar eliminación (Configuración → Sistema).
     */
    public function deleteUserByEmail(Request $request): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('settings_system', 'view')) {
            abort(403, 'No tienes permiso para esta acción.');
        }

        $redirectSettings = redirect()->to(route('admin.settings.section', 'system').'?system_sub=delete-user');

        // Confirmar eliminación (segundo paso)
        if ($request->filled('user_id')) {
            $user = User::with('roles')->find($request->user_id);
            if (! $user) {
                session()->forget('user_to_delete_id');

                return $redirectSettings->with('error', 'Usuario no encontrado.');
            }
            if ($user->id === auth()->id()) {
                session()->forget('user_to_delete_id');

                return $redirectSettings->with('error', 'No puedes eliminar tu propio usuario.');
            }
            $userController = app(AdminUserController::class);
            if (! $userController->canEditUserPublic($user)) {
                session()->forget('user_to_delete_id');

                return $redirectSettings->with('error', 'No tienes permiso para eliminar a este usuario.');
            }
            if ((int) session('user_to_delete_id') !== (int) $user->id) {
                session()->forget('user_to_delete_id');

                return $redirectSettings->with('error', 'Confirma la eliminación desde el mismo flujo (busca de nuevo por correo).');
            }

            $name = $user->name;
            $email = $user->email;
            $rolesText = $user->roles->pluck('name')->join(', ') ?: 'Sin rol';
            $userController->performUserDeletion($user);
            session()->forget('user_to_delete_id');

            return $redirectSettings->with('success', 'Usuario eliminado: '.$name.' ('.$email.'). Rol: '.$rolesText.'.');
        }

        // Buscar por correo (primer paso)
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');
        $user = User::with('roles')->where('email', $email)->first();

        if (! $user) {
            return $redirectSettings->with('error', 'No hay ningún usuario con ese correo en el sistema. No está en la lista.');
        }
        if ($user->id === auth()->id()) {
            return $redirectSettings->with('error', 'No puedes eliminar tu propio usuario.');
        }
        if ($user->assignedRegistrations()->count() > 0) {
            return $redirectSettings->with('error', 'No se puede eliminar: tiene solicitudes asignadas.');
        }
        $userController = app(AdminUserController::class);
        if (! $userController->canEditUserPublic($user)) {
            return $redirectSettings->with('error', 'No tienes permiso para eliminar a este usuario.');
        }

        session()->put('user_to_delete_id', $user->id);

        return $redirectSettings->with('info', 'Usuario encontrado. Revisa los datos abajo y confirma la eliminación.');
    }

    /**
     * Primera sección de configuración a la que el usuario tiene permiso (unificación sidebar/tabs).
     */
    protected function getFirstAllowedSection(PermissionService $permissionService): string
    {
        if (auth()->user()->hasRole('super_admin')) {
            return 'mail';
        }
        $order = [
            'mail' => ['settings_mail'],
            'templates' => ['settings_templates'],
            'history' => ['settings_history'],
            'login-lockouts' => ['settings_system'],
            'system' => ['settings_system'],
            'legal-policies' => ['settings_system'],
        ];
        foreach ($order as $section => $modules) {
            if ($section === 'system') {
                if (auth()->user()->hasRole('super_admin')) {
                    return $section;
                }

                continue;
            }
            foreach ($modules as $module) {
                if ($permissionService->userHasPermission($module, 'view')) {
                    return $section;
                }
            }
        }

        return 'mail';
    }

    /**
     * Redirigir a la primera sección de configuración permitida (unificación con sidebar/tabs).
     */
    public function redirectToFirstSection()
    {
        $permissionService = app(PermissionService::class);
        $section = $this->getFirstAllowedSection($permissionService);

        return redirect()->route('admin.settings.section', $section);
    }

    public function update(Request $request)
    {
        $section = $request->input('section');

        // Asegurar que los settings existan en la BD antes de intentar cargarlos
        $this->ensureSettingsInDatabase();

        // Obtener settings completos (recargar: boot() pudo resolver GeneralSettings antes de existir timezone en BD)
        try {
            $settings = $this->resolveGeneralSettingsAfterEnsuringDatabase();
        } catch (MissingSettings $e) {
            // Si aún falla después de asegurar settings, crear un nuevo objeto con valores por defecto
            $settings = new GeneralSettings;
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        switch ($section) {
            case 'mail':
                $this->updateMailSettings($request, $settings);
                break;
            case 'system':
                // Solo super_admin puede actualizar configuración del sistema
                if (! auth()->user()->hasRole('super_admin')) {
                    return redirect()->route('admin.settings.section', 'mail')
                        ->with('error', 'No tienes permisos para realizar esta acción.');
                }
                $this->updateSystemSettings($request, $settings);
                break;
            case 'login-lockouts':
                if (! app(PermissionService::class)->userHasPermission('settings_system', 'edit')) {
                    return redirect()->route('admin.settings.section', 'login-lockouts')
                        ->with('error', 'No tienes permisos para realizar esta acción.');
                }
                $this->updateLoginLockoutSettings($request, $settings);
                break;
            case 'legal-policies':
                $this->updateLegalPoliciesSettings($request, $settings);
                break;
            case 'email_template':
                $this->updateEmailTemplate($request);
                break;
            case 'test_email':
                return $this->sendTestEmail($request);
                break;
        }

        // Redirigir a la misma sección donde se guardó
        $redirectSection = $section;

        // Para casos especiales, mantener la sección actual
        if ($section === 'email_template' || $section === 'test_email') {
            // Estos casos ya manejan su propia redirección
            $redirectSection = $request->input('current_section', 'mail');
        }

        if ($redirectSection === 'system') {
            $redirect = redirect()->to(route('admin.settings.section', 'system').'?system_sub=customization')
                ->with('success', 'Configuración actualizada exitosamente.');
        } else {
            $redirect = redirect()
                ->route('admin.settings.section', $redirectSection)
                ->with('success', 'Configuración actualizada exitosamente.');
        }

        // Si hay un tab específico, agregarlo a la URL
        if ($request->has('tab')) {
            $redirect->with('tab', $request->input('tab'));
        }

        return $redirect;
    }

    private function updateAgencySettings(Request $request, GeneralSettings $settings)
    {
        $rules = [
            'agency_name' => 'required|string|max:255',
            'agency_nit' => 'nullable|string|max:50',
            'agency_address' => 'nullable|string|max:500',
            'agency_phone' => 'nullable|string|max:50',
            'agency_email' => 'nullable|email|max:255',
            'agency_website' => 'nullable|url|max:255',
            'remove_logo' => 'nullable|boolean',
            'quote_pdf_header_subtitle' => 'nullable|string|max:500',
            'quote_pdf_footer_text' => 'nullable|string|max:1000',
        ];

        // Validación del logo sin depender de php_fileinfo
        if ($request->hasFile('agency_logo')) {
            $rules['agency_logo'] = 'file|max:2048'; // 2MB
        }

        $validated = $request->validate($rules);

        // Validación del logo sin depender de php_fileinfo
        if ($request->hasFile('agency_logo')) {
            $rules['agency_logo'] = 'file|max:2048'; // 2MB
        }

        $validated = $request->validate($rules);

        // Validación manual de extensión del logo (sin depender de php_fileinfo)
        if ($request->hasFile('agency_logo')) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
            $extension = strtolower($request->file('agency_logo')->getClientOriginalExtension());

            if (! in_array($extension, $allowedExtensions)) {
                return redirect()
                    ->route('admin.settings.index')
                    ->withInput()
                    ->withErrors(['agency_logo' => 'El archivo debe ser una imagen válida (JPG, PNG, GIF, SVG, WEBP).']);
            }
        }

        // Asegurar que todas las propiedades estén establecidas antes de actualizar
        $this->ensureAllPropertiesSet($settings);

        // Actualizar campos del request
        $settings->agency_name = $validated['agency_name'];
        if (isset($validated['agency_nit'])) {
            $settings->agency_nit = $validated['agency_nit'] ?? '';
        }
        if (isset($validated['agency_address'])) {
            $settings->agency_address = $validated['agency_address'] ?? '';
        }
        if (isset($validated['agency_phone'])) {
            $settings->agency_phone = $validated['agency_phone'] ?? '';
        }
        if (isset($validated['agency_email'])) {
            $settings->agency_email = $validated['agency_email'] ?? '';
        }
        if (isset($validated['agency_website'])) {
            $settings->agency_website = $validated['agency_website'] ?? '';
        }
        if (array_key_exists('quote_pdf_header_subtitle', $validated)) {
            $settings->quote_pdf_header_subtitle = $validated['quote_pdf_header_subtitle'] ?? '';
        }
        if (array_key_exists('quote_pdf_footer_text', $validated)) {
            $settings->quote_pdf_footer_text = $validated['quote_pdf_footer_text'] ?? '';
        }

        // Manejar logo
        if ($request->has('remove_logo') && $request->remove_logo) {
            // Eliminar logo existente
            if ($settings->agency_logo) {
                $oldLogoPath = public_path($settings->agency_logo);
                if (file_exists($oldLogoPath)) {
                    unlink($oldLogoPath);
                }
            }
            $settings->agency_logo = '';
        } elseif ($request->hasFile('agency_logo')) {
            // Eliminar logo anterior si existe
            if ($settings->agency_logo) {
                $oldLogoPath = public_path($settings->agency_logo);
                if (file_exists($oldLogoPath)) {
                    unlink($oldLogoPath);
                }
            }

            // Guardar nuevo logo usando funciones PHP nativas (evita php_fileinfo y finfo)
            $file = $request->file('agency_logo');
            $extension = $file->getClientOriginalExtension();
            $filename = 'logo_'.time().'_'.uniqid().'.'.$extension;

            // Crear directorio dentro del repositorio (public/uploads/logos)
            $logoDir = public_path('uploads/logos');
            if (! is_dir($logoDir)) {
                mkdir($logoDir, 0755, true);
            }

            // Ruta completa del archivo destino
            $destinationPath = $logoDir.'/'.$filename;

            // Mover archivo usando move_uploaded_file (no requiere php_fileinfo)
            move_uploaded_file($file->getRealPath(), $destinationPath);

            // Guardar ruta relativa para acceso web (uploads/logos/filename)
            $settings->agency_logo = 'uploads/logos/'.$filename;
        }
        // Si no se envía nada, mantener el logo actual (ya está cargado en $settings)

        // Asegurar que todas las propiedades estén establecidas antes de guardar
        $this->ensureAllPropertiesSet($settings);
        $settings->save();
    }

    /**
     * Tras sincronizar filas en `settings`, forzar una nueva resolución de GeneralSettings.
     * Sin esto, una instancia cargada antes (p. ej. en AppServiceProvider::boot) puede quedar sin
     * claves recién insertadas (p. ej. timezone) y Spatie lanza MissingSettings al guardar.
     */
    private function resolveGeneralSettingsAfterEnsuringDatabase(): GeneralSettings
    {
        try {
            Artisan::call('settings:clear-cache');
        } catch (\Throwable) {
        }

        $app = app();
        if ($app->resolved(GeneralSettings::class)) {
            $app->forgetInstance(GeneralSettings::class);
        }

        return $app->make(GeneralSettings::class);
    }

    /**
     * Asegurar que todos los settings estén en la base de datos
     */
    private function ensureSettingsInDatabase()
    {
        $requiredSettings = [
            'agency_name' => 'RAMS',
            'agency_nit' => '',
            'agency_address' => '',
            'agency_phone' => '',
            'agency_email' => '',
            'agency_website' => '',
            'agency_logo' => '',
            'drive_service_account_json' => '',
            'drive_folder_id' => '',
            'drive_default_country_no_client' => '',
            'drive_folder_name_no_client' => 'Solicitudes sin cliente',
            'drive_folder_name_with_client' => 'Clientes',
            'drive_mode' => 'service_account',
            'drive_oauth_client_id' => '',
            'drive_oauth_client_secret' => '',
            'drive_oauth_refresh_token' => '',
            'drive_oauth_access_token' => '',
            'mail_provider' => 'smtp',
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@rams.com',
            'mail_from_name' => 'RAMS Sistema',
            'zoho_client_id' => '',
            'zoho_client_secret' => '',
            'zoho_refresh_token' => '',
            'zoho_access_token' => '',
            'zoho_from_email' => '',
            'footer_text' => 'RAMS - Regulatory Affairs Management System',
            'system_name' => 'Sistema de Gestión Regulatoria',
            'timezone' => 'America/Bogota',
            'quote_pdf_header_subtitle' => 'RAMS - Regulatory Affairs Management System',
            'quote_pdf_footer_text' => '',
            'legal_privacy_title' => 'Política de Privacidad',
            'legal_terms_title' => 'Términos y Condiciones del Servicio',
            'legal_show_privacy_on_login' => true,
            'legal_show_terms_on_login' => true,
            'legal_privacy_html' => '',
            'legal_terms_html' => '',
            'admin_sidebar_expanded_default' => false,
            'login_lockout_enabled' => true,
            'login_max_failed_attempts' => 5,
            'login_lockout_duration_minutes' => 30,
        ];

        $existingSettings = DB::table('settings')
            ->where('group', 'general')
            ->pluck('name')
            ->toArray();

        $missingSettings = array_diff_key($requiredSettings, array_flip($existingSettings));

        if (! empty($missingSettings)) {
            // Insertar settings faltantes directamente en la BD
            $now = now();
            foreach ($missingSettings as $name => $defaultValue) {
                DB::table('settings')->insert([
                    'group' => 'general',
                    'name' => $name,
                    'locked' => false,
                    'payload' => json_encode($defaultValue),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Asegurar que todas las propiedades de GeneralSettings estén establecidas
     * Spatie Settings requiere que todas las propiedades estén definidas antes de guardar
     */
    private function ensureAllPropertiesSet(GeneralSettings $settings)
    {
        // Establecer todas las propiedades directamente
        // Spatie Settings necesita que todas estén definidas antes de guardar

        $defaults = [
            'agency_name' => 'RAMS',
            'agency_nit' => '',
            'agency_address' => '',
            'agency_phone' => '',
            'agency_email' => '',
            'agency_website' => '',
            'agency_logo' => '',
            'drive_service_account_json' => '',
            'drive_folder_id' => '',
            'drive_default_country_no_client' => '',
            'mail_provider' => 'smtp',
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@rams.com',
            'mail_from_name' => 'RAMS Sistema',
            'zoho_client_id' => '',
            'zoho_client_secret' => '',
            'zoho_refresh_token' => '',
            'zoho_access_token' => '',
            'zoho_from_email' => '',
            'footer_text' => 'RAMS - Regulatory Affairs Management System',
            'system_name' => 'Sistema de Gestión Regulatoria',
            'timezone' => 'America/Bogota',
            'quote_pdf_header_subtitle' => 'RAMS - Regulatory Affairs Management System',
            'quote_pdf_footer_text' => '',
            'drive_folder_name_no_client' => 'Solicitudes sin cliente',
            'drive_folder_name_with_client' => 'Clientes',
            'drive_mode' => 'service_account',
            'drive_oauth_client_id' => '',
            'drive_oauth_client_secret' => '',
            'drive_oauth_refresh_token' => '',
            'drive_oauth_access_token' => '',
            'legal_privacy_title' => 'Política de Privacidad',
            'legal_terms_title' => 'Términos y Condiciones del Servicio',
            'legal_show_privacy_on_login' => true,
            'legal_show_terms_on_login' => true,
            'legal_privacy_html' => '',
            'legal_terms_html' => '',
            'admin_sidebar_expanded_default' => false,
            'login_lockout_enabled' => true,
            'login_max_failed_attempts' => 5,
            'login_lockout_duration_minutes' => 30,
        ];
        foreach ($defaults as $property => $defaultValue) {
            try {
                $currentValue = $settings->$property ?? null;
                if ($currentValue === null) {
                    $settings->$property = $defaultValue;
                }
            } catch (\Exception $e) {
                // Si la propiedad no existe o no se puede acceder, establecer valor por defecto
                $settings->$property = $defaultValue;
            }
        }
    }

    private function updateDriveSettings(Request $request, GeneralSettings $settings)
    {
        $validated = $request->validate([
            'drive_service_account_json' => 'nullable|string',
            'drive_folder_id' => 'nullable|string|max:255',
            'drive_folder_name_no_client' => 'nullable|string|max:255',
            'drive_folder_name_with_client' => 'nullable|string|max:255',
            'drive_default_country_no_client' => 'nullable|string|max:100',
            'drive_mode' => 'nullable|in:service_account,oauth_user',
            'drive_oauth_client_id' => 'nullable|string|max:255',
            'drive_oauth_client_secret' => 'nullable|string|max:255',
        ]);

        $driveMode = $validated['drive_mode'] ?? $settings->drive_mode ?? 'service_account';
        $settings->drive_mode = $driveMode;

        if (isset($validated['drive_oauth_client_id'])) {
            $settings->drive_oauth_client_id = $validated['drive_oauth_client_id'] ?? '';
        }
        if (isset($validated['drive_oauth_client_secret'])) {
            $settings->drive_oauth_client_secret = $validated['drive_oauth_client_secret'] ?? '';
        }

        // Validar que el JSON sea válido si se proporciona (solo en modo Service Account)
        if ($driveMode === 'service_account' && ! empty($validated['drive_service_account_json'])) {
            $jsonData = json_decode($validated['drive_service_account_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()
                    ->route('admin.settings.section', 'drive')
                    ->withInput()
                    ->with('error', 'El JSON proporcionado no es válido. Por favor, verifica el formato.');
            }

            // Verificar campos requeridos en el JSON
            $requiredFields = ['type', 'project_id', 'private_key', 'client_email'];
            foreach ($requiredFields as $field) {
                if (! isset($jsonData[$field])) {
                    return redirect()
                        ->route('admin.settings.section', 'drive')
                        ->withInput()
                        ->with('error', "El JSON no contiene el campo requerido: {$field}");
                }
            }

            // Verificar que sea una Service Account
            if ($jsonData['type'] !== 'service_account') {
                return redirect()
                    ->route('admin.settings.section', 'drive')
                    ->withInput()
                    ->with('error', 'El JSON no corresponde a una Service Account de Google Cloud.');
            }
        }

        // Asegurar que todos los settings estén en la base de datos primero
        $this->ensureSettingsInDatabase();

        // Asegurar que todas las propiedades estén establecidas ANTES de actualizar
        $this->ensureAllPropertiesSet($settings);

        // Actualizar campos
        if (isset($validated['drive_service_account_json'])) {
            $settings->drive_service_account_json = $validated['drive_service_account_json'] ?? '';
        }

        if (isset($validated['drive_folder_id'])) {
            $settings->drive_folder_id = $validated['drive_folder_id'] ?? '';
        }

        // Establecer valores por defecto si no están en la base de datos
        if (! isset($validated['drive_folder_name_no_client'])) {
            // Si no viene en el request, usar el valor existente o el por defecto
            try {
                $currentValue = $settings->drive_folder_name_no_client ?? null;
                if ($currentValue === null) {
                    $settings->drive_folder_name_no_client = 'Solicitudes sin cliente';
                }
            } catch (\Exception $e) {
                $settings->drive_folder_name_no_client = 'Solicitudes sin cliente';
            }
        } else {
            $settings->drive_folder_name_no_client = $validated['drive_folder_name_no_client'] ?: 'Solicitudes sin cliente';
        }

        if (! isset($validated['drive_folder_name_with_client'])) {
            // Si no viene en el request, usar el valor existente o el por defecto
            try {
                $currentValue = $settings->drive_folder_name_with_client ?? null;
                if ($currentValue === null) {
                    $settings->drive_folder_name_with_client = 'Clientes';
                }
            } catch (\Exception $e) {
                $settings->drive_folder_name_with_client = 'Clientes';
            }
        } else {
            $settings->drive_folder_name_with_client = $validated['drive_folder_name_with_client'] ?: 'Clientes';
        }

        if (array_key_exists('drive_default_country_no_client', $validated)) {
            $settings->drive_default_country_no_client = $validated['drive_default_country_no_client'] ?? '';
        }

        $settings->save();
    }

    /**
     * Redirigir a Google OAuth para autorizar Drive (modo usuario / Mi unidad)
     */
    public function driveOauthAuthorize(Request $request)
    {
        $this->ensureSettingsInDatabase();
        try {
            $settings = app(GeneralSettings::class);
        } catch (\Exception $e) {
            $settings = new GeneralSettings;
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        if (empty($settings->drive_oauth_client_id) || empty($settings->drive_oauth_client_secret)) {
            return redirect()
                ->route('admin.settings.section', 'drive')
                ->with('error', 'Configura Client ID y Client Secret de Google OAuth (modo OAuth) y guarda antes de autorizar.');
        }

        $redirectUri = route('admin.settings.drive-oauth.callback');
        $scope = 'https://www.googleapis.com/auth/drive';
        $authUrl = sprintf(
            'https://accounts.google.com/o/oauth2/v2/auth?client_id=%s&redirect_uri=%s&response_type=code&scope=%s&access_type=offline&prompt=consent',
            urlencode($settings->drive_oauth_client_id),
            urlencode($redirectUri),
            urlencode($scope)
        );

        return redirect($authUrl);
    }

    /**
     * Callback de OAuth de Google Drive – recibe código y obtiene refresh_token
     */
    public function driveOauthCallback(Request $request)
    {
        $code = $request->input('code');
        $error = $request->input('error');

        if ($error) {
            $desc = $request->input('error_description', $error);

            return redirect()
                ->route('admin.settings.section', 'drive')
                ->with('error', 'Error en autorización Google: '.$desc);
        }

        if (! $code) {
            return redirect()
                ->route('admin.settings.section', 'drive')
                ->with('error', 'No se recibió el código de autorización de Google.');
        }

        $this->ensureSettingsInDatabase();
        try {
            $settings = app(GeneralSettings::class);
        } catch (\Exception $e) {
            $settings = new GeneralSettings;
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        if (empty($settings->drive_oauth_client_id) || empty($settings->drive_oauth_client_secret)) {
            return redirect()
                ->route('admin.settings.section', 'drive')
                ->with('error', 'Client ID y Client Secret de Google OAuth no configurados.');
        }

        $redirectUri = route('admin.settings.drive-oauth.callback');
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $settings->drive_oauth_client_id,
            'client_secret' => $settings->drive_oauth_client_secret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (! $response->successful()) {
            $body = $response->json();
            $msg = $body['error_description'] ?? $body['error'] ?? $response->body();

            return redirect()
                ->route('admin.settings.section', 'drive')
                ->with('error', 'Error al obtener tokens: '.$msg);
        }

        $data = $response->json();
        $refreshToken = $data['refresh_token'] ?? null;
        $accessToken = $data['access_token'] ?? null;

        if (! $refreshToken) {
            return redirect()
                ->route('admin.settings.section', 'drive')
                ->with('error', 'Google no devolvió refresh_token. Asegúrate de usar prompt=consent y autorizar de nuevo.');
        }

        $this->ensureAllPropertiesSet($settings);
        $settings->drive_oauth_refresh_token = $refreshToken;
        if ($accessToken) {
            $settings->drive_oauth_access_token = $accessToken;
        }
        $settings->save();

        return redirect()
            ->route('admin.settings.section', 'drive')
            ->with('success', 'Google Drive OAuth conectado. Ya puedes usar "Mi unidad" y subir documentos.');
    }

    private function updateMailSettings(Request $request, GeneralSettings $settings)
    {
        $rules = [
            'mail_provider' => 'required|in:smtp,zoho',
            'mail_mailer' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string|max:10',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'zoho_client_id' => 'nullable|string|max:255',
            'zoho_client_secret' => 'nullable|string|max:255',
            'zoho_refresh_token' => 'nullable|string',
            'zoho_from_email' => 'nullable|email|max:255',
        ];

        $validated = $request->validate($rules);

        // Asegurar que todas las propiedades estén establecidas
        $this->ensureAllPropertiesSet($settings);

        // Actualizar proveedor
        if (isset($validated['mail_provider'])) {
            $settings->mail_provider = $validated['mail_provider'];
        }

        // Actualizar campos SMTP
        if (isset($validated['mail_mailer'])) {
            $settings->mail_mailer = $validated['mail_mailer'];
        }
        if (isset($validated['mail_host'])) {
            $settings->mail_host = $validated['mail_host'];
        }
        if (isset($validated['mail_port'])) {
            $settings->mail_port = $validated['mail_port'];
        }
        if (isset($validated['mail_username'])) {
            $settings->mail_username = $validated['mail_username'] ?? '';
        }
        if (isset($validated['mail_password'])) {
            $settings->mail_password = $validated['mail_password'] ?? '';
        }
        if (isset($validated['mail_encryption'])) {
            $settings->mail_encryption = $validated['mail_encryption'] ?? '';
        }
        if (isset($validated['mail_from_address'])) {
            $settings->mail_from_address = $validated['mail_from_address'];
        }
        if (isset($validated['mail_from_name'])) {
            $settings->mail_from_name = $validated['mail_from_name'];
        }

        // Actualizar campos Zoho
        if (isset($validated['zoho_client_id'])) {
            $settings->zoho_client_id = $validated['zoho_client_id'] ?? '';
        }
        if (isset($validated['zoho_client_secret'])) {
            $settings->zoho_client_secret = $validated['zoho_client_secret'] ?? '';
        }
        if (isset($validated['zoho_refresh_token'])) {
            $settings->zoho_refresh_token = $validated['zoho_refresh_token'] ?? '';
        }
        if (isset($validated['zoho_from_email'])) {
            $settings->zoho_from_email = $validated['zoho_from_email'] ?? '';
        }

        $settings->save();
    }

    private function updateEmailTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template = EmailTemplate::findOrFail($validated['template_id']);

        // Verificar que el body no esté vacío
        if (empty(trim($validated['body']))) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cuerpo de la plantilla no puede estar vacío',
                ], 422);
            }

            return redirect()
                ->route('admin.settings.section', 'templates')
                ->with('error', 'El cuerpo de la plantilla no puede estar vacío');
        }

        $template->subject = $validated['subject'];
        $template->body = $validated['body'];
        $template->save();

        // Si es una petición AJAX, devolver JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Plantilla guardada exitosamente',
            ]);
        }

        return redirect()
            ->route('admin.settings.section', 'templates')
            ->with('success', 'Plantilla guardada exitosamente');
    }

    /**
     * Obtener datos de una plantilla (para modal)
     */
    public function getTemplate($id)
    {
        try {
            $template = EmailTemplate::findOrFail($id);

            // Obtener el body directamente de la BD
            $body = $template->body ?? '';

            // Logging detallado para debugging
            \Log::info("Obteniendo plantilla {$id}", [
                'id' => $template->id,
                'name' => $template->name,
                'type' => $template->type,
                'body_length' => strlen($body),
                'body_preview' => substr($body, 0, 200),
                'body_is_empty' => empty(trim($body)),
            ]);

            // Si está vacío, loguear warning
            if (empty(trim($body))) {
                \Log::warning("Plantilla {$id} ({$template->type}) tiene body vacío en la BD");
            }

            return response()->json([
                'success' => true,
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name ?? '',
                    'type' => $template->type ?? '',
                    'subject' => $template->subject ?? '',
                    'body' => $body, // Asegurar que siempre se devuelva, aunque esté vacío
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error obteniendo plantilla: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Plantilla no encontrada: '.$e->getMessage(),
            ], 404);
        }
    }

    /**
     * Iniciar proceso de autorización OAuth con Zoho
     */
    public function zohoAuthorize(Request $request)
    {
        // Asegurar que los settings existan
        $this->ensureSettingsInDatabase();

        try {
            $settings = app(GeneralSettings::class);
        } catch (MissingSettings $e) {
            $settings = new GeneralSettings;
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        // Validar que Client ID, Client Secret y Email Remitente estén configurados
        if (empty($settings->zoho_client_id) || empty($settings->zoho_client_secret)) {
            return redirect()
                ->route('admin.settings.section', 'mail')
                ->with('error', 'Configura Client ID y Client Secret de Zoho antes de autorizar.');
        }
        if (empty($settings->zoho_from_email)) {
            return redirect()
                ->route('admin.settings.section', 'mail')
                ->with('error', 'Configura el Email Remitente (Zoho) y guarda los cambios antes de autorizar. Debes autorizar con esa misma cuenta en Zoho.');
        }

        // Construir URL de autorización (prompt=consent para asegurar consentimiento explícito)
        $redirectUri = route('admin.settings.zoho.callback');
        $scope = 'ZohoMail.messages.CREATE,ZohoMail.accounts.READ';
        $clientId = $settings->zoho_client_id;

        $authUrl = sprintf(
            'https://accounts.zoho.com/oauth/v2/auth?scope=%s&client_id=%s&response_type=code&access_type=offline&redirect_uri=%s&prompt=consent',
            urlencode($scope),
            urlencode($clientId),
            urlencode($redirectUri)
        );

        // Redirigir a Zoho para autorización
        return redirect($authUrl);
    }

    /**
     * Callback de OAuth de Zoho - recibe el código de confirmación de Zoho y obtiene Refresh Token
     * Este método se ejecuta después de que Zoho confirma la autorización y redirige de vuelta
     */
    public function zohoCallback(Request $request)
    {
        $code = $request->input('code');
        $error = $request->input('error');

        if ($error) {
            $errorDescription = $request->input('error_description', $error);

            // Mensaje más específico para error de Redirect URI
            if (str_contains(strtolower($errorDescription), 'redirect') || str_contains(strtolower($errorDescription), 'uri')) {
                $redirectUri = route('admin.settings.zoho.callback');

                return redirect()
                    ->route('admin.settings.section', 'mail')
                    ->with('error', 'Error: URI de redireccionamiento no válido. Asegúrate de haber configurado EXACTAMENTE esta URL en Zoho API Console: '.$redirectUri);
            }

            return redirect()
                ->route('admin.settings.section', 'mail')
                ->with('error', 'Error en la autorización de Zoho: '.$errorDescription);
        }

        if (! $code) {
            return redirect()
                ->route('admin.settings.section', 'mail')
                ->with('error', 'No se recibió el código de autorización de Zoho.');
        }

        // Asegurar que los settings existan
        $this->ensureSettingsInDatabase();

        try {
            $settings = app(GeneralSettings::class);
        } catch (MissingSettings $e) {
            $settings = new GeneralSettings;
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        // Validar que Client ID y Client Secret estén configurados
        if (empty($settings->zoho_client_id) || empty($settings->zoho_client_secret)) {
            return redirect()
                ->route('admin.settings.section', 'mail')
                ->with('error', 'Client ID y Client Secret no están configurados.');
        }

        // Intercambiar código por Refresh Token
        $redirectUri = route('admin.settings.zoho.callback');

        try {
            $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $settings->zoho_client_id,
                'client_secret' => $settings->zoho_client_secret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $refreshToken = $data['refresh_token'] ?? null;
                $accessToken = $data['access_token'] ?? null;

                if ($refreshToken) {
                    // Guardar Refresh Token y Access Token en settings
                    $this->ensureAllPropertiesSet($settings);
                    $settings->zoho_refresh_token = $refreshToken;
                    if ($accessToken) {
                        $settings->zoho_access_token = $accessToken;
                    }
                    $settings->save();

                    return redirect()
                        ->route('admin.settings.section', 'mail')
                        ->with('success', '¡Autorización exitosa! El Refresh Token ha sido guardado automáticamente.');
                } else {
                    return redirect()
                        ->route('admin.settings.section', 'mail')
                        ->with('error', 'No se recibió el Refresh Token en la respuesta de Zoho.');
                }
            } else {
                $errorMessage = $response->json()['error_description'] ?? $response->body();

                return redirect()
                    ->route('admin.settings.section', 'mail')
                    ->with('error', 'Error al obtener Refresh Token: '.$errorMessage);
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.settings.section', 'mail')
                ->with('error', 'Error al procesar la autorización: '.$e->getMessage());
        }
    }

    /**
     * Enviar correo de prueba
     */
    public function sendTestEmail(Request $request)
    {
        $validated = $request->validate([
            'test_email_to' => 'required|email',
            'test_email_subject' => 'nullable|string|max:255',
            'test_email_body' => 'nullable|string',
        ]);

        // Asegurar que los settings existan
        $this->ensureSettingsInDatabase();

        try {
            $settings = app(GeneralSettings::class);
        } catch (MissingSettings $e) {
            $settings = new GeneralSettings;
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        // Validar configuración antes de intentar enviar
        if ($settings->mail_provider === 'zoho') {
            $missingConfig = [];

            if (empty($settings->zoho_client_id)) {
                $missingConfig[] = 'Client ID de Zoho';
            }
            if (empty($settings->zoho_client_secret)) {
                $missingConfig[] = 'Client Secret de Zoho';
            }
            if (empty($settings->zoho_refresh_token)) {
                $missingConfig[] = 'Refresh Token de Zoho (necesitas autorizar la aplicación)';
            }
            if (empty($settings->zoho_from_email)) {
                $missingConfig[] = 'Email de origen de Zoho';
            }

            if (! empty($missingConfig)) {
                return redirect()
                    ->route('admin.settings.section', 'mail')
                    ->with('error', 'Configuración incompleta de Zoho. Faltan: '.implode(', ', $missingConfig).'. Por favor, completa la configuración antes de enviar.')
                    ->with('tab', 'history');
            }
        } else {
            // Validar SMTP
            $missingConfig = [];

            if (empty($settings->mail_host)) {
                $missingConfig[] = 'Host SMTP';
            }
            if (empty($settings->mail_port)) {
                $missingConfig[] = 'Puerto SMTP';
            }
            if (empty($settings->mail_username)) {
                $missingConfig[] = 'Usuario SMTP';
            }
            if (empty($settings->mail_password)) {
                $missingConfig[] = 'Contraseña SMTP';
            }
            if (empty($settings->mail_from_address)) {
                $missingConfig[] = 'Email de origen';
            }

            if (! empty($missingConfig)) {
                return redirect()
                    ->route('admin.settings.section', 'mail')
                    ->with('error', 'Configuración incompleta de SMTP. Faltan: '.implode(', ', $missingConfig).'. Por favor, completa la configuración antes de enviar.')
                    ->with('tab', 'history');
            }
        }

        try {
            $mailService = app(MailService::class);

            $to = $validated['test_email_to'];
            $subject = $validated['test_email_subject'] ?? 'Correo de Prueba - RAMS';
            $body = $validated['test_email_body'] ?? '<h1>Correo de Prueba</h1><p>Este es un correo de prueba enviado desde el sistema RAMS.</p><p>Si recibes este correo, la configuración de correo está funcionando correctamente.</p>';

            $result = $mailService->send($to, $subject, $body, null, null, true);

            if ($result) {
                return redirect()
                    ->route('admin.settings.section', 'history')
                    ->with('success', 'Correo de prueba enviado exitosamente a '.$to);
            } else {
                return redirect()
                    ->route('admin.settings.section', 'history')
                    ->with('error', 'Error al enviar correo de prueba. Revisa el historial para ver el error detallado.');
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.settings.section', 'history')
                ->with('error', 'Error al enviar correo de prueba: '.$e->getMessage());
        }
    }

    /**
     * Obtener detalles de un log de correo
     */
    public function getEmailLog(EmailLog $log)
    {
        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'to' => $log->to,
                'from_email' => $log->from_email,
                'from_name' => $log->from_name,
                'subject' => $log->subject,
                'body' => $log->body,
                'provider' => $log->provider,
                'status' => $log->status,
                'error_message' => $log->error_message,
                'is_test' => $log->is_test,
                'created_at' => $log->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Eliminar un log de correo
     */
    public function deleteEmailLog(EmailLog $log)
    {
        try {
            $log->delete();

            return response()->json([
                'success' => true,
                'message' => 'Correo eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el correo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar un registro de error de la aplicación (marcar como solucionado y borrar).
     */
    public function deleteErrorLog(AppErrorLog $errorLog): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('settings_system', 'view')) {
            abort(403);
        }

        $errorLog->delete();

        return redirect()
            ->to(route('admin.settings.section', 'system').'?system_sub=errors')
            ->with('success', 'Registro de error eliminado.');
    }

    /**
     * Vaciar el historial de errores de la aplicación.
     */
    public function clearErrorLogs(Request $request): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('settings_system', 'view')) {
            abort(403);
        }

        if ($request->boolean('only_resolved')) {
            $deleted = AppErrorLog::whereNotNull('resolved_at')->delete();
            $msg = "Se eliminaron {$deleted} error(es) marcados como solucionados.";
        } else {
            $deleted = AppErrorLog::query()->delete();
            $msg = "Se eliminó todo el historial de errores ({$deleted}).";
        }

        return redirect()
            ->to(route('admin.settings.section', 'system').'?system_sub=errors')
            ->with('success', $msg);
    }

    /**
     * Marcar un error como solucionado (sin borrarlo).
     */
    public function resolveErrorLog(AppErrorLog $errorLog): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('settings_system', 'view')) {
            abort(403);
        }

        $errorLog->update([
            'resolved_at' => $errorLog->resolved_at ? null : now(),
        ]);

        return redirect()
            ->to(route('admin.settings.section', 'system').'?system_sub=errors')
            ->with('success', $errorLog->resolved_at ? 'Error marcado como solucionado.' : 'Error reabierto.');
    }

    /**
     * Actualizar configuración del sistema
     */
    private function updateSystemSettings(Request $request, GeneralSettings $settings)
    {
        $request->validate([
            'footer_text' => 'nullable|string|max:255',
            'system_name' => 'nullable|string|max:255',
            'timezone' => ['nullable', 'string', 'timezone:all'],
        ]);

        if ($request->has('footer_text')) {
            $settings->footer_text = $request->input('footer_text');
        }

        if ($request->has('system_name')) {
            $settings->system_name = $request->input('system_name');
        }

        if ($request->has('timezone')) {
            $tz = trim((string) $request->input('timezone'));
            if ($tz !== '') {
                $settings->timezone = $tz;
            }
        }

        $settings->admin_sidebar_expanded_default = $request->boolean('admin_sidebar_expanded_default');

        $this->ensureAllPropertiesSet($settings);
        $settings->save();
    }

    private function updateLoginLockoutSettings(Request $request, GeneralSettings $settings): void
    {
        $validated = $request->validate([
            'login_max_failed_attempts' => 'required|integer|min:1|max:100',
            'login_lockout_duration_minutes' => 'required|integer|min:1|max:10080',
        ]);

        $settings->login_lockout_enabled = $request->boolean('login_lockout_enabled');
        $settings->login_max_failed_attempts = (int) $validated['login_max_failed_attempts'];
        $settings->login_lockout_duration_minutes = (int) $validated['login_lockout_duration_minutes'];

        $this->ensureAllPropertiesSet($settings);
        $settings->save();
    }

    /**
     * Política de privacidad y términos (HTML público).
     */
    private function updateLegalPoliciesSettings(Request $request, GeneralSettings $settings): void
    {
        $validated = $request->validate([
            'legal_privacy_title' => 'nullable|string|max:255',
            'legal_terms_title' => 'nullable|string|max:255',
            'legal_privacy_html' => 'nullable|string|max:500000',
            'legal_terms_html' => 'nullable|string|max:500000',
        ]);

        $settings->legal_privacy_title = trim((string) ($validated['legal_privacy_title'] ?? '')) ?: 'Política de Privacidad';
        $settings->legal_terms_title = trim((string) ($validated['legal_terms_title'] ?? '')) ?: 'Términos y Condiciones del Servicio';
        $settings->legal_show_privacy_on_login = $request->boolean('legal_show_privacy_on_login');
        $settings->legal_show_terms_on_login = $request->boolean('legal_show_terms_on_login');
        $settings->legal_privacy_html = $validated['legal_privacy_html'] ?? '';
        $settings->legal_terms_html = $validated['legal_terms_html'] ?? '';

        $this->ensureAllPropertiesSet($settings);
        $settings->save();
    }

    /**
     * Ejecuta un comando en la raíz del proyecto (sin incluir redirección stderr).
     */
    protected function runShellInProjectRoot(string $innerCommand): array
    {
        $projectPathEscaped = \escapeshellarg(base_path());
        $fullCommand = "cd {$projectPathEscaped} && {$innerCommand} 2>&1";

        $output = '';
        $returnCode = 0;

        $disabled = array_filter(array_map('trim', explode(',', (string) ini_get('disable_functions'))));

        if (function_exists('exec') && ! in_array('exec', $disabled, true)) {
            $outputArray = [];
            \exec($fullCommand, $outputArray, $returnCode);
            $output = implode("\n", $outputArray);
        } elseif (function_exists('shell_exec') && ! in_array('shell_exec', $disabled, true)) {
            $shellOut = \shell_exec($fullCommand);
            $output = $shellOut ?? '';
            $returnCode = $shellOut !== null ? 0 : 1;
        } elseif (function_exists('passthru') && ! in_array('passthru', $disabled, true)) {
            \ob_start();
            \passthru($fullCommand, $returnCode);
            $output = (string) \ob_get_clean();
        } elseif (function_exists('proc_open')) {
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            $process = \proc_open($fullCommand, $descriptorspec, $pipes);

            if (is_resource($process)) {
                \fclose($pipes[0]);
                $output = (string) \stream_get_contents($pipes[1]);
                \fclose($pipes[1]);
                \fclose($pipes[2]);
                $returnCode = \proc_close($process);
            } else {
                throw new \Exception('No se pudo ejecutar el comando. Las funciones de ejecución están deshabilitadas.');
            }
        } else {
            throw new \Exception('Las funciones de ejecución de comandos están deshabilitadas en este servidor. Contacta al administrador del servidor.');
        }

        return ['output' => $output, 'exit_code' => $returnCode];
    }

    /**
     * Consola de mantenimiento: solo líneas permitidas (composer / php artisan), sin shell metacharacters.
     */
    public function maintenanceCli(Request $request)
    {
        if (! auth()->user()->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ], 403);
        }

        $command = trim((string) $request->input('command', ''));
        if ($command === '') {
            return response()->json([
                'success' => false,
                'message' => 'Indica un comando.',
            ], 400);
        }

        if (strlen($command) > 2000) {
            return response()->json([
                'success' => false,
                'message' => 'Comando demasiado largo.',
            ], 400);
        }

        if (! $this->isMaintenanceCliLineAllowed($command)) {
            return response()->json([
                'success' => false,
                'message' => 'Comando no permitido o formato inválido. Usa «composer …» (require, install, update, remove, etc.) o «php artisan …» con un comando de la lista permitida, sin pipes ni redirecciones.',
            ], 400);
        }

        try {
            $result = $this->runShellInProjectRoot($command);
            $ok = $result['exit_code'] === 0;

            return response()->json([
                'success' => $ok,
                'message' => $ok ? 'Comando ejecutado' : 'El comando terminó con código '.$result['exit_code'],
                'output' => $result['output'] !== '' ? $result['output'] : 'Comando ejecutado (sin salida)',
            ], $ok ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
                'output' => 'Las funciones de ejecución pueden estar deshabilitadas. Verifica la configuración de PHP (disable_functions).',
            ], 500);
        }
    }

    protected function isMaintenanceCliLineAllowed(string $command): bool
    {
        if (preg_match('/[;|&$`(){}\n\r<>\\\\\'\"]/', $command)) {
            return false;
        }

        if (str_starts_with(strtolower($command), 'composer')) {
            return $this->isAllowedComposerMaintenanceLine($command);
        }

        if (preg_match('/^php\s+artisan\s+/i', $command)) {
            return $this->isAllowedArtisanMaintenanceLine($command);
        }

        return false;
    }

    protected function isAllowedComposerMaintenanceLine(string $command): bool
    {
        $allowedSub = ['require', 'install', 'update', 'remove', 'dump-autoload', 'dumpautoload', 'clear-cache', 'validate', 'show'];
        $tokens = preg_split('/\s+/', trim($command));
        if (count($tokens) < 2 || strtolower($tokens[0]) !== 'composer') {
            return false;
        }

        $i = 1;
        while ($i < count($tokens) && str_starts_with($tokens[$i], '-')) {
            $t = $tokens[$i];
            if (preg_match('/^--working-dir/', $t) || preg_match('/^--file/', $t) || $t === '-d' || str_starts_with($t, '-d=')) {
                return false;
            }
            $i++;
        }

        $sub = strtolower($tokens[$i] ?? '');

        return in_array($sub, $allowedSub, true);
    }

    protected function isAllowedArtisanMaintenanceLine(string $command): bool
    {
        if (! preg_match('/^php\s+artisan\s+(.+)$/i', $command, $m)) {
            return false;
        }

        $rest = trim(preg_replace('/\s+/', ' ', $m[1]));

        $allowed = [
            'view:clear',
            'cache:clear',
            'config:clear',
            'route:clear',
            'optimize:clear',
            'migrate --force',
            'migrate',
            'storage:link',
            'about',
            'db:show',
            'queue:restart',
            'config:cache',
            'route:cache',
            'view:cache',
        ];

        return in_array($rest, $allowed, true);
    }

    /**
     * Ejecutar git pull
     */
    public function gitPull(Request $request)
    {
        // Solo super_admin puede ejecutar git pull
        if (! auth()->user()->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ], 403);
        }

        $branch = $request->input('branch', 'main');

        if ($branch === 'origin main') {
            $inner = 'git pull origin main';
        } else {
            if (! is_string($branch) || preg_match('/^[a-zA-Z0-9._\/ -]+$/', $branch) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rama inválida.',
                ], 400);
            }
            $inner = "git pull {$branch}";
        }

        try {
            $result = $this->runShellInProjectRoot($inner);
            $output = $result['output'];
            $returnCode = $result['exit_code'];

            if ($returnCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Git pull ejecutado exitosamente',
                    'output' => $output !== '' ? $output : 'Comando ejecutado (sin salida)',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar git pull',
                'output' => $output !== '' ? $output : 'No se pudo obtener la salida del comando',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
                'output' => 'Las funciones de ejecución pueden estar deshabilitadas. Verifica la configuración de PHP (disable_functions).',
            ], 500);
        }
    }

    /**
     * Ejecutar comando artisan
     */
    public function artisanCommand(Request $request)
    {
        // Solo super_admin puede ejecutar comandos artisan
        if (! auth()->user()->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ], 403);
        }

        $command = $request->input('command');

        // Validar que el comando esté permitido (seguridad). Permite comando con opciones (ej. migrate --force)
        $allowedCommands = [
            'view:clear',
            'cache:clear',
            'config:clear',
            'route:clear',
            'optimize:clear',
            'migrate --force',
        ];

        // Si el comando contiene &&, dividirlo en múltiples comandos
        $commands = [];
        $regex = '/php\s+artisan\s+([\w:]+(?:\s+--[\w-]+)*)/';
        if (strpos($command, '&&') !== false) {
            $commandParts = explode('&&', $command);
            foreach ($commandParts as $part) {
                $part = trim($part);
                if (preg_match($regex, $part, $matches)) {
                    $cmd = trim($matches[1]);
                    if (in_array($cmd, $allowedCommands)) {
                        $commands[] = $cmd;
                    }
                }
            }
        } else {
            // Comando simple
            if (preg_match($regex, $command, $matches)) {
                $cmd = trim($matches[1]);
                if (in_array($cmd, $allowedCommands)) {
                    $commands[] = $cmd;
                }
            }
        }

        if (empty($commands)) {
            return response()->json([
                'success' => false,
                'message' => 'Comando no permitido o formato inválido.',
            ], 400);
        }

        try {
            $allOutput = [];
            $hasError = false;

            foreach ($commands as $cmd) {
                $result = $this->runShellInProjectRoot('php artisan '.$cmd);
                $output = $result['output'];
                $returnCode = $result['exit_code'];

                $allOutput[] = "=== php artisan {$cmd} ===";
                $allOutput[] = $output !== '' ? $output : 'Comando ejecutado (sin salida)';

                if ($returnCode !== 0) {
                    $hasError = true;
                }
            }

            $outputText = implode("\n\n", $allOutput);

            if (! $hasError) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comando(s) ejecutado(s) exitosamente',
                    'output' => $outputText,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar comando(s)',
                'output' => $outputText,
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
                'output' => 'Las funciones de ejecución pueden estar deshabilitadas. Verifica la configuración de PHP (disable_functions).',
            ], 500);
        }
    }

    /**
     * Probar conexión con Google Drive
     */
    public function testDriveConnection(Request $request)
    {
        try {
            $driveService = app(GoogleDriveService::class);
            $result = $driveService->testConnection();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al probar conexión: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener historial de operaciones de Google Drive con filtros (requiere permiso settings_drive_operations_log)
     */
    public function getDriveOperationsLog(Request $request)
    {
        $permissionService = app(PermissionService::class);
        if (! $permissionService->userHasPermission('settings_drive_operations_log', 'view')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver el historial de operaciones.'], 403);
        }
        try {
            $query = DriveOperationLog::with(['user', 'registration', 'company']);

            // Filtro por tipo de operación
            if ($request->has('operation_type') && $request->operation_type !== '' && $request->operation_type !== 'all') {
                $query->where('operation_type', $request->operation_type);
            }

            // Filtro por fecha desde
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            // Filtro por fecha hasta
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Buscador (por nombre de recurso, usuario, solicitud)
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('resource_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('registration', function ($regQuery) use ($search) {
                            $regQuery->where('product_name', 'like', "%{$search}%")
                                ->orWhere('registration_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('company', function ($compQuery) use ($search) {
                            $compQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $operations = $query->orderBy('created_at', 'desc')->paginate(20);

            return response()->json([
                'success' => true,
                'operations' => $operations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar registros del historial de operaciones de Google Drive (requiere permiso settings_drive_operations_log)
     */
    public function deleteDriveOperationsLog(Request $request)
    {
        $permissionService = app(PermissionService::class);
        if (! $permissionService->userHasPermission('settings_drive_operations_log', 'view')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso.'], 403);
        }
        try {
            $ids = $request->input('ids', []);

            if (empty($ids) || ! is_array($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionaron IDs para eliminar',
                ], 400);
            }

            $deleted = DriveOperationLog::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deleted} registro(s) del historial",
                'deleted_count' => $deleted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar registros: '.$e->getMessage(),
            ], 500);
        }
    }
}
