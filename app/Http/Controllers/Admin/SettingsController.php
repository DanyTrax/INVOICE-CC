<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(GeneralSettings $settings)
    {
        $emailTemplates = EmailTemplate::all();
        
        return view('admin.settings.index', [
            'settings' => $settings,
            'emailTemplates' => $emailTemplates,
        ]);
    }

    public function update(Request $request, GeneralSettings $settings)
    {
        $section = $request->input('section');

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
        $validated = $request->validate([
            'agency_name' => 'required|string|max:255',
            'agency_nit' => 'nullable|string|max:50',
            'agency_address' => 'nullable|string|max:500',
            'agency_phone' => 'nullable|string|max:50',
            'agency_email' => 'nullable|email|max:255',
            'agency_website' => 'nullable|url|max:255',
            'agency_logo' => 'nullable|string|max:500',
        ]);

        $settings->agency_name = $validated['agency_name'];
        $settings->agency_nit = $validated['agency_nit'] ?? '';
        $settings->agency_address = $validated['agency_address'] ?? '';
        $settings->agency_phone = $validated['agency_phone'] ?? '';
        $settings->agency_email = $validated['agency_email'] ?? '';
        $settings->agency_website = $validated['agency_website'] ?? '';
        $settings->agency_logo = $validated['agency_logo'] ?? '';
        
        $settings->save();
    }

    private function updateDriveSettings(Request $request, GeneralSettings $settings)
    {
        $validated = $request->validate([
            'drive_service_account_json' => 'nullable|string',
        ]);

        $settings->drive_service_account_json = $validated['drive_service_account_json'] ?? '';
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

        $settings->mail_mailer = $validated['mail_mailer'];
        $settings->mail_host = $validated['mail_host'];
        $settings->mail_port = $validated['mail_port'];
        $settings->mail_username = $validated['mail_username'] ?? '';
        $settings->mail_password = $validated['mail_password'] ?? '';
        $settings->mail_encryption = $validated['mail_encryption'] ?? '';
        $settings->mail_from_address = $validated['mail_from_address'];
        $settings->mail_from_name = $validated['mail_from_name'];
        
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
