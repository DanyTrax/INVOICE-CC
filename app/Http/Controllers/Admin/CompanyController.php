<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyInvite;
use App\Models\EmailLog;
use App\Models\Process;
use App\Models\RegulatoryEvent;
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
            'allows_loans' => 'nullable|boolean',
        ]);
        $validated['allows_loans'] = $request->boolean('allows_loans');

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
        app(ActivityLogService::class)->log('created', 'Creó la empresa "' . $company->name . '"', $company);

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
        $company->loadCount('registrations');

        // Centro de trazabilidad: expedientes (processes) del cliente
        $total_processes = Process::where('client_id', $company->id)->count();

        $stats_by_status = Process::where('client_id', $company->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $processes_timeline = Process::where('client_id', $company->id)
            ->with([
                'quote',
                'quoteItem.quote',
                'quoteItem.serviceType',
                'serviceType',
                'submissions.regulatoryEvents',
                'submissions.children.regulatoryEvents',
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Semáforo: procesos en radicación y con autos vencidos
        $count_radicado = (int) ($stats_by_status[Process::STATUS_RADICADO] ?? 0);
        $count_autos_vencidos = Process::where('client_id', $company->id)
            ->whereHas('submissions.regulatoryEvents', function ($q) {
                $q->where('event_type', RegulatoryEvent::EVENT_TYPE_AUTO)
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', today());
            })
            ->count();

        return view('admin.companies.show', compact(
            'company',
            'total_processes',
            'stats_by_status',
            'processes_timeline',
            'count_radicado',
            'count_autos_vencidos'
        ));
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
            'allows_loans' => 'nullable|boolean',
        ]);
        $validated['allows_loans'] = $request->boolean('allows_loans');
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
