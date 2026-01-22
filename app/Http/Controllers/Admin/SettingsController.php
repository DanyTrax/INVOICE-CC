<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        
        // Obtener settings completos para asegurar que todas las propiedades estén cargadas
        try {
            $settings = app(GeneralSettings::class);
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            // Ejecutar migración de settings si no existen
            \Artisan::call('settings:migrate');
            try {
                $settings = app(GeneralSettings::class);
            } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e2) {
                // Si aún falla, inicializar todas las propiedades manualmente
                $settings = new GeneralSettings();
                // Establecer todas las propiedades con valores por defecto
                $settings->agency_name = 'RAMS';
                $settings->agency_nit = '';
                $settings->agency_address = '';
                $settings->agency_phone = '';
                $settings->agency_email = '';
                $settings->agency_website = '';
                $settings->agency_logo = '';
                $settings->drive_service_account_json = '';
                $settings->mail_mailer = 'smtp';
                $settings->mail_host = 'smtp.gmail.com';
                $settings->mail_port = 587;
                $settings->mail_username = '';
                $settings->mail_password = '';
                $settings->mail_encryption = 'tls';
                $settings->mail_from_address = 'noreply@rams.com';
                $settings->mail_from_name = 'RAMS Sistema';
                // Guardar inicial
                $settings->save();
            }
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

        // Asegurar que todas las propiedades estén establecidas antes de guardar
        // Usar valores del request o mantener los existentes
        $settings->agency_name = $validated['agency_name'] ?? $settings->agency_name ?? 'RAMS';
        $settings->agency_nit = $validated['agency_nit'] ?? $settings->agency_nit ?? '';
        $settings->agency_address = $validated['agency_address'] ?? $settings->agency_address ?? '';
        $settings->agency_phone = $validated['agency_phone'] ?? $settings->agency_phone ?? '';
        $settings->agency_email = $validated['agency_email'] ?? $settings->agency_email ?? '';
        $settings->agency_website = $validated['agency_website'] ?? $settings->agency_website ?? '';
        
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
        
        // Asegurar que todas las demás propiedades estén establecidas (mantener valores existentes)
        if (!isset($settings->drive_service_account_json)) $settings->drive_service_account_json = '';
        if (!isset($settings->mail_mailer)) $settings->mail_mailer = 'smtp';
        if (!isset($settings->mail_host)) $settings->mail_host = 'smtp.gmail.com';
        if (!isset($settings->mail_port)) $settings->mail_port = 587;
        if (!isset($settings->mail_username)) $settings->mail_username = '';
        if (!isset($settings->mail_password)) $settings->mail_password = '';
        if (!isset($settings->mail_encryption)) $settings->mail_encryption = 'tls';
        if (!isset($settings->mail_from_address)) $settings->mail_from_address = 'noreply@rams.com';
        if (!isset($settings->mail_from_name)) $settings->mail_from_name = 'RAMS Sistema';
        if (!isset($settings->agency_logo)) $settings->agency_logo = '';
        
        $settings->save();
    }

    private function updateDriveSettings(Request $request, GeneralSettings $settings)
    {
        $validated = $request->validate([
            'drive_service_account_json' => 'nullable|string',
        ]);

        // Asegurar que todas las propiedades estén establecidas
        $settings->drive_service_account_json = $validated['drive_service_account_json'] ?? '';
        $settings->agency_name = $settings->agency_name ?? 'RAMS';
        $settings->agency_nit = $settings->agency_nit ?? '';
        $settings->agency_address = $settings->agency_address ?? '';
        $settings->agency_phone = $settings->agency_phone ?? '';
        $settings->agency_email = $settings->agency_email ?? '';
        $settings->agency_website = $settings->agency_website ?? '';
        $settings->agency_logo = $settings->agency_logo ?? '';
        $settings->mail_mailer = $settings->mail_mailer ?? 'smtp';
        $settings->mail_host = $settings->mail_host ?? 'smtp.gmail.com';
        $settings->mail_port = $settings->mail_port ?? 587;
        $settings->mail_username = $settings->mail_username ?? '';
        $settings->mail_password = $settings->mail_password ?? '';
        $settings->mail_encryption = $settings->mail_encryption ?? 'tls';
        $settings->mail_from_address = $settings->mail_from_address ?? 'noreply@rams.com';
        $settings->mail_from_name = $settings->mail_from_name ?? 'RAMS Sistema';
        
        $settings->save();
    }

    private function updateMailSettings(Request $request, GeneralSettings $settings)
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|string|max:50',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string|max:10',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        // Asegurar que todas las propiedades estén establecidas
        $settings->mail_mailer = $validated['mail_mailer'];
        $settings->mail_host = $validated['mail_host'];
        $settings->mail_port = $validated['mail_port'];
        $settings->mail_username = $validated['mail_username'] ?? '';
        $settings->mail_password = $validated['mail_password'] ?? '';
        $settings->mail_encryption = $validated['mail_encryption'] ?? '';
        $settings->mail_from_address = $validated['mail_from_address'];
        $settings->mail_from_name = $validated['mail_from_name'];
        $settings->agency_name = $settings->agency_name ?? 'RAMS';
        $settings->agency_nit = $settings->agency_nit ?? '';
        $settings->agency_address = $settings->agency_address ?? '';
        $settings->agency_phone = $settings->agency_phone ?? '';
        $settings->agency_email = $settings->agency_email ?? '';
        $settings->agency_website = $settings->agency_website ?? '';
        $settings->agency_logo = $settings->agency_logo ?? '';
        $settings->drive_service_account_json = $settings->drive_service_account_json ?? '';
        
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
