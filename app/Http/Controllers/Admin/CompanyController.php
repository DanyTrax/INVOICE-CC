<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyInvite;
use App\Models\EmailLog;
use App\Services\GoogleDriveService;
use App\Services\MailService;
use App\Services\EmailTemplateService;
use App\Services\ActivityLogService;
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
        $countries = config('countries', []);
        sort($countries);
        return view('admin.companies.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $countriesList = config('countries', []);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nit_rut' => 'required|string|max:50|unique:companies,nit_rut',
            'address' => 'nullable|string|max:500',
            'country' => [
                'nullable',
                'string',
                'max:100',
                'in:' . implode(',', $countriesList),
            ],
            'phone' => 'nullable|string|max:50',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'logo_path' => 'nullable|string|max:500',
            'drive_folder_id' => 'nullable|string|max:255',
            'allows_loans' => 'nullable|boolean',
        ], [
            'country.in' => 'Debe seleccionar un país de la lista (escriba para buscar y elija una opción).',
        ], [
            'country' => 'país',
        ]);
        $validated['allows_loans'] = $request->boolean('allows_loans');

        // Crear carpeta en Google Drive: Base → País → Empresa (sin carpeta "Clientes" intermedia)
        if (empty($validated['drive_folder_id'])) {
            try {
                $driveService = app(GoogleDriveService::class);
                $folderName = $validated['name'] . ' - ' . ($validated['nit_rut'] ?? 'Sin NIT');
                $country = isset($validated['country']) && trim($validated['country']) !== '' ? trim($validated['country']) : null;
                $parentId = $country
                    ? $driveService->getOrCreateCountryFolder($country)
                    : $driveService->getOrCreateClientsFolder(null);
                $folder = $driveService->createFolder($folderName, $parentId);
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
        app(ActivityLogService::class)->log('created', 'Creó la empresa "' . $company->name . '"', $company);

        $sendInvite = $request->boolean('send_invite_email');
        if ($sendInvite && !empty($validated['contact_person_email'])) {
            $lastError = null;
            $sent = $this->sendCompanyInviteEmail($company, $validated['contact_person_email'], $validated['contact_person_name'] ?? $validated['name'], $lastError);
            if ($sent) {
                return redirect()
                    ->route('admin.companies.index')
                    ->with('success', 'Empresa creada exitosamente. Se envió correo de invitación para registro.');
            }
            return redirect()
                ->route('admin.companies.index')
                ->with('success', 'Cliente creado exitosamente.')
                ->with('error', 'No se pudo enviar el correo de invitación.' . ($lastError ? ' ' . $lastError : ''));
        }

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Empresa creada exitosamente.');
    }

    public function show(Company $company)
    {
        // 1. Cargar procesos con sus relaciones clave para la trazabilidad
        $company->load(['processes' => function ($q) {
            $q->with([
                'serviceType',
                'quote',
                'quoteItem.quote',
                'quoteItem.serviceType',
                'submissions.regulatoryEvents',
                'submissions.children.regulatoryEvents',
            ])
                ->orderBy('updated_at', 'desc');
        }]);

        $processes = $company->processes;

        // 2. Calcular Estadísticas (KPIs)
        $total_processes = $processes->count();

        $stats = [
            'recoleccion' => $processes->where('status', 'Recolección')->count(),
            'radicado' => $processes->where('status', 'Radicado')->count(),
            'requerimiento' => $processes->where('status', 'En Requerimiento')->count(),
            'finalizado' => $processes->where('status', 'Finalizado')->count(),
        ];

        // 3. Detectar Alertas (procesos en requerimiento que requieren atención)
        $alerts = $processes->filter(function ($p) {
            return $p->status === 'En Requerimiento';
        })->count();

        return view('admin.companies.show', compact('company', 'total_processes', 'stats', 'alerts'));
    }

    public function edit(Company $company)
    {
        $countries = config('countries', []);
        sort($countries);
        return view('admin.companies.edit', compact('company', 'countries'));
    }

    public function update(Request $request, Company $company)
    {
        $countriesList = config('countries', []);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nit_rut' => 'required|string|max:50|unique:companies,nit_rut,' . $company->id,
            'address' => 'nullable|string|max:500',
            'country' => [
                'nullable',
                'string',
                'max:100',
                'in:' . implode(',', $countriesList),
            ],
            'phone' => 'nullable|string|max:50',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'logo_path' => 'nullable|string|max:500',
            'drive_folder_id' => 'nullable|string|max:255',
            'allows_loans' => 'nullable|boolean',
        ], [
            'country.in' => 'Debe seleccionar un país de la lista (escriba para buscar y elija una opción).',
        ], [
            'country' => 'país',
        ]);
        $validated['allows_loans'] = $request->boolean('allows_loans');

        $oldCountry = trim($company->country ?? '');
        $newCountry = isset($validated['country']) ? trim($validated['country'] ?? '') : '';

        // Si cambió el país y la empresa tiene carpeta en Drive, mover la carpeta (y todo su contenido) al nuevo país
        if ($company->drive_folder_id && $oldCountry !== $newCountry) {
            try {
                $driveService = app(GoogleDriveService::class);
                $newParentId = $newCountry !== ''
                    ? $driveService->getOrCreateCountryFolder($newCountry)
                    : $driveService->getOrCreateClientsFolder(null);
                $driveService->moveFile($company->drive_folder_id, $newParentId);
                Log::info('Carpeta de empresa movida en Drive al cambiar país', [
                    'company_id' => $company->id,
                    'new_country' => $newCountry ?: '(sin país)',
                ]);
            } catch (\Exception $e) {
                Log::error('Error al mover carpeta de empresa en Drive al cambiar país', [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()
                    ->route('admin.companies.edit', $company)
                    ->with('error', 'No se pudo mover la carpeta en Google Drive al nuevo país. ' . $e->getMessage())
                    ->withInput();
            }
        }

        $company->update($validated);
        app(ActivityLogService::class)->log('updated', 'Actualizó la empresa "' . $company->name . '"', $company);

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
        // Verificar si tiene expedientes (processes) o registros antiguos
        if ($company->processes()->count() > 0 || $company->registrations()->count() > 0) {
            return redirect()
                ->route('admin.companies.index')
                ->with('error', 'No se puede eliminar la empresa porque tiene expedientes asociados.');
        }

        $name = $company->name;
        $company->delete();
        app(ActivityLogService::class)->log('deleted', 'Eliminó la empresa "' . $name . '"');

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
                ->with('error', 'La empresa no tiene email de contacto. Edítala y añade uno.');
        }

        $lastError = null;
        $sent = $this->sendCompanyInviteEmail(
            $company,
            $company->contact_person_email,
            $company->contact_person_name ?? $company->name,
            $lastError
        );

        if ($sent) {
            return redirect()
                ->route('admin.companies.index')
                ->with('success', 'Correo de invitación enviado a ' . $company->contact_person_email);
        }

        $msg = 'No se pudo enviar el correo de invitación.';
        if ($lastError) {
            $msg .= ' ' . $lastError;
        } else {
            $msg .= ' Revisa Configuración → Correo y el Historial de correos.';
        }
        return redirect()
            ->route('admin.companies.index')
            ->with('error', $msg);
    }

    /**
     * Crear invitación, procesar plantilla y enviar correo.
     * Si falla, $lastError se rellena con el mensaje a mostrar al usuario.
     */
    protected function sendCompanyInviteEmail(Company $company, string $email, string $name, ?string &$lastError = null): bool
    {
        $lastError = null;

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
                $lastError = 'No existe la plantilla de invitación. Ejecuta: php artisan db:seed --class=EmailTemplateSeeder';
                return false;
            }

            $mailService = app(MailService::class);
            $sent = $mailService->send($email, $result['subject'], $result['body']);

            if (!$sent) {
                Log::warning('MailService::send devolvió false para invitación', [
                    'company_id' => $company->id,
                    'email' => $email,
                ]);
                $lastError = $this->getLastEmailError($email);
                if (!$lastError) {
                    $lastError = 'Revisa Configuración → Historial de correos para el detalle del error.';
                }
            }

            return $sent;
        } catch (\Exception $e) {
            Log::error('Error al enviar invitación de cliente', [
                'company_id' => $company->id,
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $lastError = strlen($e->getMessage()) > 250 ? substr($e->getMessage(), 0, 247) . '…' : $e->getMessage();
            return false;
        }
    }

    /**
     * Obtener el último error de EmailLog para un destinatario (para mostrar al usuario).
     */
    protected function getLastEmailError(string $to): ?string
    {
        $log = EmailLog::where('to', $to)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->orderByDesc('created_at')
            ->first();

        if ($log && $log->error_message) {
            $msg = $log->error_message;
            return strlen($msg) > 200 ? substr($msg, 0, 197) . '…' : $msg;
        }

        $fallback = EmailLog::where('status', 'failed')
            ->where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subMinutes(10))
            ->orderByDesc('created_at')
            ->first();

        return $fallback && $fallback->error_message
            ? (strlen($fallback->error_message) > 200 ? substr($fallback->error_message, 0, 197) . '…' : $fallback->error_message)
            : null;
    }
}
