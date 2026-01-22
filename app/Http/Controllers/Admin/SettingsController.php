<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $emailTemplates = EmailTemplate::all();
        
        // Inicializar settings con valores por defecto si no existen
        try {
            $settings = app(GeneralSettings::class);
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            // Ejecutar migración de settings si no existen
            \Artisan::call('settings:migrate');
            $settings = app(GeneralSettings::class);
        }
        
        return view('admin.settings.index', [
            'settings' => $settings,
            'emailTemplates' => $emailTemplates,
        ]);
    }

    public function update(Request $request)
    {
        $section = $request->input('section');
        
        // Asegurar que los settings existan en la BD antes de intentar cargarlos
        // Ejecutar migración si es necesario
        try {
            $settings = app(GeneralSettings::class);
            // Verificar que todas las propiedades estén en la BD
            $this->ensureSettingsInDatabase();
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            // Ejecutar migración de settings si no existen
            \Artisan::call('settings:migrate');
            $settings = app(GeneralSettings::class);
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
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Configuración actualizada exitosamente.');
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
            'agency_name', 'agency_nit', 'agency_address', 'agency_phone', 
            'agency_email', 'agency_website', 'agency_logo', 
            'drive_service_account_json', 'mail_mailer', 'mail_host', 
            'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 
            'mail_from_address', 'mail_from_name'
        ];
        
        $existingSettings = \DB::table('settings')
            ->where('group', 'general')
            ->pluck('name')
            ->toArray();
        
        $missingSettings = array_diff($requiredSettings, $existingSettings);
        
        if (!empty($missingSettings)) {
            // Ejecutar migración para crear los settings faltantes
            \Artisan::call('settings:migrate');
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
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@rams.com',
            'mail_from_name' => 'RAMS Sistema',
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
            'mail_mailer' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string|max:10',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ];

        $validated = $request->validate($rules);

        // Asegurar que todas las propiedades estén establecidas
        $this->ensureAllPropertiesSet($settings);
        
        // Actualizar campos del request
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
}
