<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index(Request $request, $section = 'agency')
    {
        // Validar que la sección sea válida
        $validSections = ['agency', 'drive', 'mail', 'templates', 'history'];
        if (!in_array($section, $validSections)) {
            $section = 'agency';
        }
        
        $emailTemplates = EmailTemplate::all();
        
        // Verificar si la tabla email_logs existe antes de consultarla
        try {
            $emailLogs = EmailLog::orderBy('created_at', 'desc')
                ->with('user')
                ->paginate(20);
        } catch (\Illuminate\Database\QueryException $e) {
            // Si la tabla no existe, crear una colección vacía
            $emailLogs = new \Illuminate\Pagination\LengthAwarePaginator(
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
            $settings = app(GeneralSettings::class);
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            // Si aún falla, crear settings con valores por defecto
            $settings = new GeneralSettings();
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }
        
        return view('admin.settings.index', [
            'settings' => $settings,
            'emailTemplates' => $emailTemplates,
            'emailLogs' => $emailLogs,
            'activeSection' => $section,
        ]);
    }

    public function update(Request $request)
    {
        $section = $request->input('section');
        
        // Asegurar que los settings existan en la BD antes de intentar cargarlos
        $this->ensureSettingsInDatabase();
        
        // Obtener settings completos
        try {
            $settings = app(GeneralSettings::class);
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            // Si aún falla después de asegurar settings, crear un nuevo objeto con valores por defecto
            $settings = new GeneralSettings();
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        switch ($section) {
            case 'agency':
                $this->updateAgencySettings($request, $settings);
                break;
            case 'drive':
                $this->updateDriveSettings($request, $settings);
                break;
            case 'mail':
                $this->updateMailSettings($request, $settings);
                break;
            case 'email_template':
                $this->updateEmailTemplate($request);
                break;
            case 'test_email':
                return $this->sendTestEmail($request);
                break;
        }

        $redirect = redirect()
            ->route('admin.settings.index')
            ->with('success', 'Configuración actualizada exitosamente.');
        
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
            
            if (!in_array($extension, $allowedExtensions)) {
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
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
            
            // Crear directorio dentro del repositorio (public/uploads/logos)
            $logoDir = public_path('uploads/logos');
            if (!is_dir($logoDir)) {
                mkdir($logoDir, 0755, true);
            }
            
            // Ruta completa del archivo destino
            $destinationPath = $logoDir . '/' . $filename;
            
            // Mover archivo usando move_uploaded_file (no requiere php_fileinfo)
            move_uploaded_file($file->getRealPath(), $destinationPath);
            
            // Guardar ruta relativa para acceso web (uploads/logos/filename)
            $settings->agency_logo = 'uploads/logos/' . $filename;
        }
        // Si no se envía nada, mantener el logo actual (ya está cargado en $settings)
        
        // Asegurar que todas las propiedades estén establecidas antes de guardar
        $this->ensureAllPropertiesSet($settings);
        $settings->save();
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
        ];
        
        $existingSettings = DB::table('settings')
            ->where('group', 'general')
            ->pluck('name')
            ->toArray();
        
        $missingSettings = array_diff_key($requiredSettings, array_flip($existingSettings));
        
        if (!empty($missingSettings)) {
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
        ];
        
        // Establecer todas las propiedades, usando valores existentes si están disponibles
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
        ]);

        // Asegurar que todas las propiedades estén establecidas
        $this->ensureAllPropertiesSet($settings);
        
        // Actualizar campo
        if (isset($validated['drive_service_account_json'])) {
            $settings->drive_service_account_json = $validated['drive_service_account_json'] ?? '';
        }
        
        $settings->save();
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
        $template->subject = $validated['subject'];
        $template->body = $validated['body'];
        $template->save();
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
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            $settings = new GeneralSettings();
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        // Validar que Client ID y Client Secret estén configurados
        if (empty($settings->zoho_client_id) || empty($settings->zoho_client_secret)) {
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Por favor, configura primero el Client ID y Client Secret de Zoho antes de autorizar.');
        }

        // Construir URL de autorización
        $redirectUri = route('admin.settings.zoho.callback');
        $scope = 'ZohoMail.messages.CREATE,ZohoMail.accounts.READ';
        $clientId = $settings->zoho_client_id;
        
        $authUrl = sprintf(
            'https://accounts.zoho.com/oauth/v2/auth?scope=%s&client_id=%s&response_type=code&access_type=offline&redirect_uri=%s',
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
                    ->route('admin.settings.index')
                    ->with('error', 'Error: URI de redireccionamiento no válido. Asegúrate de haber configurado EXACTAMENTE esta URL en Zoho API Console: ' . $redirectUri)
                    ->with('tab', 'mail');
            }
            
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Error en la autorización de Zoho: ' . $errorDescription)
                ->with('tab', 'mail');
        }

        if (!$code) {
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'No se recibió el código de autorización de Zoho.')
                ->with('tab', 'mail');
        }

        // Asegurar que los settings existan
        $this->ensureSettingsInDatabase();
        
        try {
            $settings = app(GeneralSettings::class);
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            $settings = new GeneralSettings();
            $this->ensureAllPropertiesSet($settings);
            $settings->save();
        }

        // Validar que Client ID y Client Secret estén configurados
        if (empty($settings->zoho_client_id) || empty($settings->zoho_client_secret)) {
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Client ID y Client Secret no están configurados.');
        }

        // Intercambiar código por Refresh Token
        $redirectUri = route('admin.settings.zoho.callback');
        
        try {
            $response = \Illuminate\Support\Facades\Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
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
                        ->route('admin.settings.index')
                        ->with('success', '¡Autorización exitosa! El Refresh Token ha sido guardado automáticamente.')
                        ->with('tab', 'mail'); // Para abrir el tab de correo
                } else {
                    return redirect()
                        ->route('admin.settings.index')
                        ->with('error', 'No se recibió el Refresh Token en la respuesta de Zoho.')
                        ->with('tab', 'mail');
                }
            } else {
                $errorMessage = $response->json()['error_description'] ?? $response->body();
                return redirect()
                    ->route('admin.settings.index')
                    ->with('error', 'Error al obtener Refresh Token: ' . $errorMessage)
                    ->with('tab', 'mail');
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Error al procesar la autorización: ' . $e->getMessage())
                ->with('tab', 'mail');
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
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            $settings = new GeneralSettings();
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
            
            if (!empty($missingConfig)) {
                return redirect()
                    ->route('admin.settings.section', 'mail')
                    ->with('error', 'Configuración incompleta de Zoho. Faltan: ' . implode(', ', $missingConfig) . '. Por favor, completa la configuración antes de enviar.')
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
            
            if (!empty($missingConfig)) {
                return redirect()
                    ->route('admin.settings.section', 'mail')
                    ->with('error', 'Configuración incompleta de SMTP. Faltan: ' . implode(', ', $missingConfig) . '. Por favor, completa la configuración antes de enviar.')
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
                    ->with('success', 'Correo de prueba enviado exitosamente a ' . $to);
            } else {
                return redirect()
                    ->route('admin.settings.section', 'history')
                    ->with('error', 'Error al enviar correo de prueba. Revisa el historial para ver el error detallado.');
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.settings.section', 'history')
                ->with('error', 'Error al enviar correo de prueba: ' . $e->getMessage());
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
                'message' => 'Error al eliminar el correo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
