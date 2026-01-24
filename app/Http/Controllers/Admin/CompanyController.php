<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyInvite;
use App\Services\GoogleDriveService;
use App\Services\MailService;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nit_rut', 'like', "%{$search}%")
                  ->orWhere('contact_person_email', 'like', "%{$search}%");
            });
        }

        $companies = $query->withCount('registrations')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nit_rut' => 'required|string|max:50|unique:companies,nit_rut',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'logo_path' => 'nullable|string|max:500',
            'drive_folder_id' => 'nullable|string|max:255',
        ]);

        // Crear carpeta en Google Drive si está configurado
        if (empty($validated['drive_folder_id'])) {
            try {
                $driveService = app(GoogleDriveService::class);
                $folderName = $validated['name'] . ' - ' . ($validated['nit_rut'] ?? 'Sin NIT');
                
                // Crear dentro de la carpeta base de clientes
                $clientsFolderId = $driveService->getOrCreateClientsFolder();
                $folder = $driveService->createFolder($folderName, $clientsFolderId);
                $validated['drive_folder_id'] = $folder['id'];
                
                Log::info('Carpeta de Google Drive creada para cliente', [
                    'company_name' => $validated['name'],
                    'folder_id' => $folder['id'],
                ]);
            } catch (\Exception $e) {
                Log::error('Error al crear carpeta en Google Drive para cliente', [
                    'company_name' => $validated['name'],
                    'error' => $e->getMessage(),
                ]);
                // Continuar sin carpeta si hay error
            }
        }

        $company = Company::create($validated);

        if ($sendInvite && !empty($validated['contact_person_email'])) {
            $sent = $this->sendCompanyInviteEmail($company, $validated['contact_person_email'], $validated['contact_person_name'] ?? $validated['name']);
            if ($sent) {
                return redirect()
                    ->route('admin.companies.index')
                    ->with('success', 'Cliente creado exitosamente. Se envió correo de invitación para registro.');
            }
        }

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Company $company)
    {
        $company->loadCount('registrations');
        $company->load('registrations');
        
        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nit_rut' => 'required|string|max:50|unique:companies,nit_rut,' . $company->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'logo_path' => 'nullable|string|max:500',
            'drive_folder_id' => 'nullable|string|max:255',
        ]);

        $company->update($validated);

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $companies = Company::where('name', 'like', "%{$query}%")
            ->orWhere('contact_person_email', 'like', "%{$query}%")
            ->orWhere('nit_rut', 'like', "%{$query}%")
            ->select('id', 'name', 'contact_person_email', 'nit_rut')
            ->limit(20)
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'text' => $company->name . ' - ' . ($company->nit_rut ?: 'Sin NIT') . ($company->contact_person_email ? ' (' . $company->contact_person_email . ')' : ''),
                    'name' => $company->name,
                    'email' => $company->contact_person_email,
                    'nit_rut' => $company->nit_rut,
                ];
            });
        
        return response()->json($companies);
    }

    public function destroy(Company $company)
    {
        // Verificar si tiene registros
        if ($company->registrations()->count() > 0) {
            return redirect()
                ->route('admin.companies.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene expedientes asociados.');
        }

        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Enviar correo de invitación para registro (link de unico uso).
     */
    public function sendInvite(Company $company)
    {
        if (!$company->contact_person_email) {
            return redirect()
                ->route('admin.companies.index')
                ->with('error', 'El cliente no tiene email de contacto. Edítalo y añade uno.');
        }

        $sent = $this->sendCompanyInviteEmail(
            $company,
            $company->contact_person_email,
            $company->contact_person_name ?? $company->name
        );

        if ($sent) {
            return redirect()
                ->route('admin.companies.index')
                ->with('success', 'Correo de invitación enviado a ' . $company->contact_person_email);
        }

        return redirect()
            ->route('admin.companies.index')
            ->with('error', 'No se pudo enviar el correo. Revisa la configuración de correo en Configuración.');
    }

    /**
     * Crear invitación, procesar plantilla y enviar correo.
     */
    protected function sendCompanyInviteEmail(Company $company, string $email, string $name): bool
    {
        try {
            $invite = CompanyInvite::createForCompany($company, $email);
            $link = url('/registrarse?token=' . $invite->token);

            $templateService = app(EmailTemplateService::class);
            $result = $templateService->processTemplate('client_invitation', [
                'name' => $name,
                'email' => $email,
                'company_name' => $company->name,
                'link' => $link,
            ]);

            if (!$result) {
                Log::warning('Plantilla client_invitation no encontrada');
                return false;
            }

            $mailService = app(MailService::class);
            return $mailService->send($email, $result['subject'], $result['body']);
        } catch (\Exception $e) {
            Log::error('Error al enviar invitación de cliente', [
                'company_id' => $company->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
