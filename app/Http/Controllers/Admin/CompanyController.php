<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyInvite;
use App\Models\EmailLog;
use App\Models\Process;
use App\Models\User;
use App\Services\EmailTemplateService;
use App\Services\GoogleDriveService;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nit_rut', 'like', "%{$search}%")
                    ->orWhere('contact_person_email', 'like', "%{$search}%")
                    ->orWhereHas('users', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $companies = $query->withCount([
            'processes',
            'users as clients_assigned_count' => function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'client'));
            },
        ])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        $countries = config('countries', []);
        sort($countries);
        $clientUsers = $this->availableClientUsers();

        return view('admin.companies.create', compact('countries', 'clientUsers'));
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
                'in:'.implode(',', $countriesList),
            ],
            'phone' => 'nullable|string|max:50',
            'invite_email' => 'nullable|email|max:255',
            'logo_path' => 'nullable|string|max:500',
            'drive_folder_id' => 'nullable|string|max:255',
            'allows_loans' => 'nullable|boolean',
        ], [
            'country.in' => 'Debe seleccionar un país de la lista (escriba para buscar y elija una opción).',
        ], [
            'country' => 'país',
        ]);
        $validated['allows_loans'] = $request->boolean('allows_loans');
        $inviteEmail = $validated['invite_email'] ?? null;
        unset($validated['invite_email']);

        $this->validateClientAssignments($request);

        // Crear carpeta en Google Drive: Base → País → Empresa (sin carpeta "Clientes" intermedia)
        if (empty($validated['drive_folder_id'])) {
            try {
                $driveService = app(GoogleDriveService::class);
                $folderName = $validated['name'].' - '.($validated['nit_rut'] ?? 'Sin NIT');
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

        $company->syncClientAssignments($this->parseClientAssignments($request));

        $sendInvite = $request->boolean('send_invite_email');
        if ($sendInvite && ! empty($inviteEmail)) {
            $lastError = null;
            $sent = $this->sendCompanyInviteEmail($company, $inviteEmail, $validated['name'], $lastError);
            if ($sent) {
                return redirect()
                    ->route('admin.companies.index')
                    ->with('success', 'Empresa creada exitosamente. Se envió correo de invitación para registro.');
            }

            return redirect()
                ->route('admin.companies.index')
                ->with('success', 'Cliente creado exitosamente.')
                ->with('error', 'No se pudo enviar el correo de invitación.'.($lastError ? ' '.$lastError : ''));
        }

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Empresa creada exitosamente.');
    }

    public function show(Request $request, Company $company)
    {
        // 0. Cargar usuarios asignados (clientes y especialistas) con sus roles
        $company->load(['users' => function ($q) {
            $q->with('roles')->orderBy('name');
        }]);

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

        // 2. Filtro por búsqueda (origen, producto, nº expediente, tipo de trámite, etc.)
        $search = $request->filled('search') ? trim($request->search) : null;
        if ($search !== null && $search !== '') {
            $searchLower = mb_strtolower($search);
            $processes = $processes->filter(function ($p) use ($searchLower) {
                $origen = $p->quote?->consecutive ?? $p->quoteItem?->quote?->consecutive ?? '';
                $producto = $p->product_reference ?: ($p->expediente_invima ?? '') ?: ($p->quoteItem?->serviceType?->name ?? $p->serviceType?->name ?? '');
                $expediente = $p->expediente_invima ?? '';
                $tipoTramite = $p->quoteItem?->serviceType?->name ?? $p->serviceType?->name ?? '';

                return str_contains(mb_strtolower($origen), $searchLower)
                    || str_contains(mb_strtolower($producto), $searchLower)
                    || str_contains(mb_strtolower((string) $expediente), $searchLower)
                    || str_contains(mb_strtolower($tipoTramite), $searchLower);
            })->values();
        }

        // 3. Filtro por paso del flujo (Recolección, Sometimiento, Radicado, AUTO, Finalizado)
        $stepFilter = $request->filled('step_filter') ? (int) $request->step_filter : null;
        if ($stepFilter !== null && $stepFilter >= 1 && $stepFilter <= 5) {
            $processes = $processes->filter(function ($p) use ($stepFilter) {
                return $p->getCurrentStep() === $stepFilter;
            })->values();
        }

        // 4. Calcular estadísticas por paso (sin filtrar) para los KPIs
        $total_processes = $company->processes->count();
        $stats = [
            'recoleccion' => $company->processes->filter(fn ($p) => $p->getCurrentStep() === Process::STEP_RECOLECCION)->count(),
            'sometimiento' => $company->processes->filter(fn ($p) => $p->getCurrentStep() === Process::STEP_SOMETIMIENTO)->count(),
            'radicado' => $company->processes->filter(fn ($p) => $p->getCurrentStep() === Process::STEP_RADICADO)->count(),
            'requerimiento' => $company->processes->filter(fn ($p) => $p->isInAutoPipeline())->count(),
            'finalizado' => $company->processes->filter(fn ($p) => $p->getCurrentStep() === Process::STEP_FINALIZADO)->count(),
        ];
        $alerts = $company->processes->filter(fn ($p) => $p->isInAutoPipeline())->count();

        $availableSteps = Process::stepLabels();

        return view('admin.companies.show', compact('company', 'processes', 'total_processes', 'stats', 'alerts', 'availableSteps'));
    }

    public function edit(Company $company)
    {
        $countries = config('countries', []);
        sort($countries);
        $company->load(['users' => fn ($q) => $q->with('roles')->orderBy('users.name')]);
        $clientUsers = $this->availableClientUsers();

        return view('admin.companies.edit', compact('company', 'countries', 'clientUsers'));
    }

    public function update(Request $request, Company $company)
    {
        $countriesList = config('countries', []);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nit_rut' => 'required|string|max:50|unique:companies,nit_rut,'.$company->id,
            'address' => 'nullable|string|max:500',
            'country' => [
                'nullable',
                'string',
                'max:100',
                'in:'.implode(',', $countriesList),
            ],
            'phone' => 'nullable|string|max:50',
            'logo_path' => 'nullable|string|max:500',
            'drive_folder_id' => 'nullable|string|max:255',
            'allows_loans' => 'nullable|boolean',
        ], [
            'country.in' => 'Debe seleccionar un país de la lista (escriba para buscar y elija una opción).',
        ], [
            'country' => 'país',
        ]);
        $validated['allows_loans'] = $request->boolean('allows_loans');

        $this->validateClientAssignments($request);

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
                    ->with('error', 'No se pudo mover la carpeta en Google Drive al nuevo país. '.$e->getMessage())
                    ->withInput();
            }
        }

        $company->update($validated);

        $company->syncClientAssignments($this->parseClientAssignments($request));

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
            ->orWhereHas('users', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->with(['users' => fn ($q) => $q->orderBy('users.name')->limit(1)])
            ->select('id', 'name', 'contact_person_email', 'nit_rut')
            ->limit(20)
            ->get()
            ->map(function ($company) {
                $firstEmail = $company->contact_person_email ?? $company->users->first()?->email;

                return [
                    'id' => $company->id,
                    'text' => $company->name.' - '.($company->nit_rut ?: 'Sin NIT').($firstEmail ? ' ('.$firstEmail.')' : ''),
                    'name' => $company->name,
                    'email' => $firstEmail,
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

        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Datos JSON para el modal de invitación (clientes asignados con rol cliente).
     */
    public function inviteData(Company $company)
    {
        $company->load(['users' => fn ($q) => $q->with('roles')->orderBy('users.name')]);
        $clients = $company->users
            ->filter(fn ($u) => $u->hasRole('client'))
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])
            ->values();

        return response()->json([
            'company_id' => $company->id,
            'company_name' => $company->name,
            'clients' => $clients,
        ]);
    }

    /**
     * Enviar correo(s) de invitación para registro (uno o más clientes asignados y/o un correo nuevo).
     */
    public function sendInvite(Request $request, Company $company)
    {
        $validated = $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'invite_email' => 'nullable|email|max:255',
            'invite_name' => 'nullable|string|max:255',
        ]);

        $userIds = array_values(array_unique(array_filter($validated['user_ids'] ?? [])));
        $inviteEmail = isset($validated['invite_email']) ? trim((string) $validated['invite_email']) : '';
        $inviteEmail = $inviteEmail !== '' ? $inviteEmail : null;
        $inviteName = isset($validated['invite_name']) ? trim((string) $validated['invite_name']) : '';
        $inviteName = $inviteName !== '' ? $inviteName : null;

        if ($userIds === [] && $inviteEmail === null) {
            return redirect()
                ->back()
                ->with('error', 'Seleccione al menos un cliente de la empresa o indique un correo para invitar.');
        }

        $emailsSent = [];
        $errors = [];

        foreach ($userIds as $uid) {
            $user = $company->users()
                ->where('users.id', $uid)
                ->whereHas('roles', fn ($r) => $r->where('name', 'client'))
                ->first();
            if (! $user) {
                $errors[] = "El usuario #{$uid} no está asignado a esta empresa como cliente.";

                continue;
            }
            $lastError = null;
            $ok = $this->sendCompanyInviteEmail($company, $user->email, $user->name, $lastError);
            if ($ok) {
                $emailsSent[] = $user->email;
            } else {
                $errors[] = $user->email.': '.($lastError ?? 'Error al enviar.');
            }
        }

        if ($inviteEmail !== null) {
            $already = false;
            foreach ($emailsSent as $sent) {
                if (strcasecmp($sent, $inviteEmail) === 0) {
                    $already = true;
                    break;
                }
            }
            if (! $already) {
                $displayName = $inviteName !== null && $inviteName !== ''
                    ? $inviteName
                    : (explode('@', $inviteEmail)[0] ?: 'Invitado/a');
                $lastError = null;
                $ok = $this->sendCompanyInviteEmail($company, $inviteEmail, $displayName, $lastError);
                if ($ok) {
                    $emailsSent[] = $inviteEmail;
                } else {
                    $errors[] = $inviteEmail.': '.($lastError ?? 'Error al enviar.');
                }
            }
        }

        if ($emailsSent !== []) {
            $successMsg = count($emailsSent) === 1
                ? 'Invitación enviada a '.$emailsSent[0].'.'
                : 'Invitaciones enviadas a: '.implode(', ', $emailsSent).'.';
        }

        if ($errors !== []) {
            $errMsg = implode(' ', $errors);
            if ($emailsSent !== []) {
                return redirect()
                    ->back()
                    ->with('success', $successMsg ?? 'Algunas invitaciones se enviaron.')
                    ->with('error', 'Algunos envíos fallaron: '.$errMsg);
            }

            return redirect()
                ->back()
                ->with('error', $errMsg);
        }

        return redirect()
            ->back()
            ->with('success', $successMsg ?? 'Invitación enviada.');
    }

    /**
     * Reenviar invitación pendiente (mismo correo, nuevo token).
     */
    public function resendInvite(CompanyInvite $invite)
    {
        if ($invite->used_at !== null) {
            return redirect()
                ->route('admin.clients.index')
                ->with('error', 'Esta invitación ya fue utilizada.');
        }

        $company = $invite->company;
        $name = explode('@', $invite->email)[0] ?: 'Invitado/a';
        $lastError = null;
        $ok = $this->sendCompanyInviteEmail($company, $invite->email, $name, $lastError);

        if ($ok) {
            return redirect()
                ->route('admin.clients.index')
                ->with('success', 'Invitación reenviada a '.$invite->email.'.');
        }

        $msg = 'No se pudo reenviar la invitación.';
        if ($lastError) {
            $msg .= ' '.$lastError;
        }

        return redirect()
            ->route('admin.clients.index')
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
            $link = url('/registrarse?token='.$invite->token);

            $templateService = app(EmailTemplateService::class);
            $result = $templateService->processTemplate('client_invitation', [
                'name' => $name,
                'email' => $email,
                'company_name' => $company->name,
                'link' => $link,
            ]);

            if (! $result) {
                Log::warning('Plantilla client_invitation no encontrada');
                $lastError = 'No existe la plantilla de invitación. Ejecuta: php artisan db:seed --class=EmailTemplateSeeder';

                return false;
            }

            $mailService = app(MailService::class);
            $sent = $mailService->send($email, $result['subject'], $result['body']);

            if (! $sent) {
                Log::warning('MailService::send devolvió false para invitación', [
                    'company_id' => $company->id,
                    'email' => $email,
                ]);
                $lastError = $this->getLastEmailError($email);
                if (! $lastError) {
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
            $lastError = strlen($e->getMessage()) > 250 ? substr($e->getMessage(), 0, 247).'…' : $e->getMessage();

            return false;
        }
    }

    /**
     * Obtener el último error de EmailLog para un destinatario (para mostrar al usuario).
     */
    /**
     * Usuarios con rol client para asignar a la empresa.
     */
    protected function availableClientUsers()
    {
        return User::role('client')->orderBy('name')->get(['id', 'name', 'email']);
    }

    /**
     * @return array<int, array{user_id: int, description: ?string}>
     */
    protected function parseClientAssignments(Request $request): array
    {
        $rows = $request->input('client_assignments', []);
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        $seen = [];
        foreach ($rows as $row) {
            if (empty($row['user_id'])) {
                continue;
            }
            $uid = (int) $row['user_id'];
            if (isset($seen[$uid])) {
                continue;
            }
            $seen[$uid] = true;
            $desc = isset($row['description']) ? trim((string) $row['description']) : '';
            $out[] = [
                'user_id' => $uid,
                'description' => $desc === '' ? null : $desc,
            ];
        }

        return $out;
    }

    protected function validateClientAssignments(Request $request): void
    {
        $assignments = $this->parseClientAssignments($request);
        foreach ($assignments as $index => $row) {
            Validator::make($row, [
                'user_id' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id')->where(function ($q) {
                        $q->whereHas('roles', fn ($r) => $r->where('name', 'client'));
                    }),
                ],
                'description' => 'nullable|string|max:500',
            ], [], [
                'user_id' => 'cliente #'.($index + 1),
            ])->validate();
        }
    }

    protected function getLastEmailError(string $to): ?string
    {
        $log = EmailLog::where('to', $to)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->orderByDesc('created_at')
            ->first();

        if ($log && $log->error_message) {
            $msg = $log->error_message;

            return strlen($msg) > 200 ? substr($msg, 0, 197).'…' : $msg;
        }

        $fallback = EmailLog::where('status', 'failed')
            ->where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subMinutes(10))
            ->orderByDesc('created_at')
            ->first();

        return $fallback && $fallback->error_message
            ? (strlen($fallback->error_message) > 200 ? substr($fallback->error_message, 0, 197).'…' : $fallback->error_message)
            : null;
    }
}
