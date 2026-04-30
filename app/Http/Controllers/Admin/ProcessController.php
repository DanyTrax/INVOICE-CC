<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GeneralProcessExport;
use App\Http\Controllers\Concerns\AuthorizesProcessAccess;
use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use App\Models\Company;
use App\Models\Process;
use App\Models\ProcessDocument;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\RegulatoryEvent;
use App\Models\ServiceType;
use App\Models\Submission;
use App\Services\GoogleDriveService;
use App\Services\ProcessAccessService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProcessController extends Controller
{
    use AuthorizesProcessAccess;

    /**
     * Crear procesos automáticamente cuando se aprueba una cotización.
     * Un Process por cada QuoteItem que aún no tenga proceso.
     * (También se dispara desde QuoteObserver al cambiar status a Aprobada.)
     */
    public static function createFromApprovedQuote(Quote $quote): int
    {
        $quote->loadMissing('quoteItems');
        $created = 0;

        foreach ($quote->quoteItems as $item) {
            if ($item->process()->exists()) {
                continue;
            }
            try {
                Process::createWithSolicitudCode([
                    'quote_item_id' => $item->id,
                    'quote_id' => $quote->id,
                    'client_id' => $quote->client_id,
                    'service_type_id' => $item->service_type_id,
                    'status' => Process::STATUS_RECOLECCION,
                    'expediente_invima' => null,
                ]);
                $created++;
            } catch (\RuntimeException $e) {
                Log::warning('No se creó solicitud al aprobar cotización: '.$e->getMessage(), [
                    'quote_id' => $quote->id,
                    'client_id' => $quote->client_id,
                    'quote_item_id' => $item->id,
                ]);
            }
        }

        // No forzar show_service_type_column; el usuario puede habilitarlo manualmente.

        return $created;
    }

    /**
     * Formulario para crear un proceso directo (sin cotización).
     */
    public function create(): View
    {
        $companies = Company::orderBy('name')->get();
        $serviceTypes = ServiceType::orderBy('name')->get();

        return view('admin.processes.create', compact('companies', 'serviceTypes'));
    }

    /**
     * Guardar proceso directo (sin cotización). Checklist queda vacía para que el agente la complete.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:companies,id',
            'service_type_name' => 'required|string|max:255',
            'product_reference' => 'nullable|string|max:500',
            'email_name' => 'nullable|string|max:255',
            'expediente_invima' => 'nullable|string|max:64',
        ]);

        $serviceType = ServiceType::where('name', $validated['service_type_name'])->first();
        if (! $serviceType) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['service_type_name' => 'Seleccione un tipo de trámite de la lista.']);
        }

        $rawOp = $request->input('expediente_invima_opcional', '0');
        $expedienteOpcionalOn = (is_array($rawOp) ? (string) end($rawOp) : (string) $rawOp) === '1';
        $expedienteInvima = $expedienteOpcionalOn
            ? (trim((string) ($validated['expediente_invima'] ?? '')) ?: null)
            : null;

        try {
            $process = Process::createWithSolicitudCode([
                'quote_item_id' => null,
                'client_id' => $validated['client_id'],
                'service_type_id' => $serviceType->id,
                'product_reference' => $validated['product_reference'] ?? null,
                'email_name' => $validated['email_name'] ?? null,
                'expediente_invima' => $expedienteInvima,
                'status' => Process::STATUS_RECOLECCION,
            ]);
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['client_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Solicitud creada. Complete la checklist en la vista de la solicitud.');
    }

    /**
     * Listado de expedientes: acordeones por cotización + procesos huérfanos (sin cotización).
     */
    public function index(Request $request)
    {
        $companies = Company::orderBy('name')->get();
        $clientId = $request->integer('client_id') ?: null;

        $quoteQuery = Quote::whereHas('quoteItems', fn ($q) => $q->whereHas('process'))
            ->orWhereHas('processes');
        if ($clientId) {
            $quoteQuery->where(function ($q) use ($clientId) {
                $q->whereHas('quoteItems', fn ($q2) => $q2->whereHas('process', fn ($q3) => $q3->where('client_id', $clientId)))
                    ->orWhereHas('processes', fn ($q2) => $q2->where('client_id', $clientId));
            });
        }
        $grouped_quotes = $quoteQuery
            ->with([
                'client',
                'quoteItems' => fn ($q) => $q->whereHas('process')->with([
                    'process.client',
                    'process.serviceType',
                    'process.submissions',
                    'serviceType',
                ]),
                'processes.client',
                'processes.serviceType',
            ])
            ->orderBy('id', 'desc')
            ->get();

        $orphanQuery = Process::whereNull('quote_item_id')->whereNull('quote_id');
        if ($clientId) {
            $orphanQuery->where('client_id', $clientId);
        }
        $orphan_processes = $orphanQuery
            ->with(['client', 'serviceType'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Todas las cotizaciones para el modal "Asignar a Cotización" (sin restricción de estado).
        $quotes_for_assign = Quote::with('client')
            ->orderBy('id', 'desc')
            ->get();

        $access = app(ProcessAccessService::class);
        if (! $access->isSupervisor(auth()->user())) {
            $user = auth()->user();
            $grouped_quotes = $grouped_quotes->map(function ($quote) use ($user, $access) {
                $filteredItems = $quote->quoteItems->filter(function ($item) use ($user, $access) {
                    $process = $item->process;

                    return $process && $access->canViewProcess($user, $process);
                });
                $quote->setRelation('quoteItems', $filteredItems->values());

                $filteredProcesses = $quote->processes->filter(fn ($p) => $access->canViewProcess($user, $p));
                $quote->setRelation('processes', $filteredProcesses->values());

                return $quote;
            })->filter(function ($quote) {
                return $quote->quoteItems->isNotEmpty() || $quote->processes->isNotEmpty();
            })->values();

            $orphan_processes = $orphan_processes->filter(fn ($p) => $access->canViewProcess($user, $p))->values();
        }

        return view('admin.processes.index', compact('grouped_quotes', 'orphan_processes', 'quotes_for_assign', 'companies'));
    }

    /**
     * Expedientes (Master List): listado plano con filtros. Soporta respuesta AJAX (solo filas).
     */
    public function masterList(Request $request)
    {
        $query = Process::with([
            'client',
            'quoteItem.quote',
            'quoteItem.serviceType',
            'quote',
            'serviceType',
            'submissions.regulatoryEvents',
            'assignedUsers',
        ]);

        app(ProcessAccessService::class)->scopeProcessesForUser($query, auth()->user());

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $stepFilter = $request->filled('step') ? (int) $request->step : null;
        if ($stepFilter !== null && $stepFilter >= 1 && $stepFilter <= 5) {
            $query->whereStep($stepFilter);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        } elseif (! $request->filled('quote_id')) {
            // Por defecto, el Monitor solo muestra expedientes activos (excluye Finalizados).
            $query->where('status', '!=', Process::STATUS_FINALIZADO);
        }

        $filterQuote = null;
        if ($request->filled('quote_id')) {
            $quoteId = (int) $request->quote_id;
            $filterQuote = Quote::find($quoteId);
            if ($filterQuote) {
                $query->whereLinkedToQuote($quoteId);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_reference', 'like', "%{$search}%")
                    ->orWhere('expediente_invima', 'like', "%{$search}%")
                    ->orWhere('solicitud_code', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('quote', fn ($q2) => $q2->where('consecutive', 'like', "%{$search}%"))
                    ->orWhereHas('quoteItem.quote', fn ($q2) => $q2->where('consecutive', 'like', "%{$search}%"));
            });
        }

        $processes = $query->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();

        if ($request->ajax()) {
            $rows = view('admin.processes.partials.process-rows', compact('processes'))->render();
            $pagination = $processes->hasPages() ? $processes->withQueryString()->links()->render() : '';

            return response()->json(['rows' => $rows, 'pagination' => $pagination]);
        }

        $companies = Company::orderBy('name')->get();

        return view('admin.processes.monitor', compact('processes', 'companies', 'filterQuote'));
    }

    /**
     * Historial de Expedientes: solo expedientes Finalizados.
     */
    public function history(Request $request)
    {
        $query = Process::with([
            'client',
            'quoteItem.quote',
            'quoteItem.serviceType',
            'quote',
            'serviceType',
            'assignedUsers',
        ])->where('status', Process::STATUS_FINALIZADO);

        app(ProcessAccessService::class)->scopeProcessesForUser($query, auth()->user());

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_reference', 'like', "%{$search}%")
                    ->orWhere('expediente_invima', 'like', "%{$search}%")
                    ->orWhere('solicitud_code', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('quote', fn ($q2) => $q2->where('consecutive', 'like', "%{$search}%"))
                    ->orWhereHas('quoteItem.quote', fn ($q2) => $q2->where('consecutive', 'like', "%{$search}%"));
            });
        }

        $processes = $query->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();
        $companies = Company::orderBy('name')->get();

        return view('admin.processes.history', compact('processes', 'companies'));
    }

    /**
     * Exportar procesos del Monitor a Excel con los filtros actuales.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = [
            'client_id' => $request->filled('client_id') ? $request->client_id : null,
            'step' => $request->filled('step') ? (int) $request->step : null,
            'status' => $request->filled('status') ? $request->status : null,
            'date_from' => $request->filled('date_from') ? $request->date_from : null,
            'date_to' => $request->filled('date_to') ? $request->date_to : null,
            'search' => $request->filled('search') ? $request->search : null,
            'quote_id' => $request->filled('quote_id') ? (int) $request->quote_id : null,
        ];

        $export = new GeneralProcessExport($filters, auth()->user());
        $filename = 'monitor-procesos-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Vincular un proceso (expediente) a una cotización y opcionalmente a un ítem.
     * Si se envía quote_item_id, el expediente queda vinculado a ese ítem y la cotización muestra la columna Trámite.
     */
    public function linkToQuote(Request $request, Process $process): RedirectResponse
    {
        $this->authorizeProcessView($process);
        $this->authorizeProcessDocuments($process);

        if ($request->boolean('unlink')) {
            $unlinkedQuoteId = $process->quote_id;
            $process->update([
                'quote_id' => null,
                'quote_item_id' => null,
            ]);
            if ($unlinkedQuoteId) {
                $this->syncQuoteTramiteColumnIfNoLinks((int) $unlinkedQuoteId);
            }

            return redirect()
                ->route('admin.processes.show', $process)
                ->with('success', 'Cotización desvinculada de la solicitud.');
        }

        // Permite "quitar ítem" enviando vacío desde el formulario (value="").
        $request->merge([
            'quote_item_id' => $request->input('quote_item_id') ?: null,
        ]);
        $validated = $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'quote_item_id' => 'nullable|exists:quote_items,id',
        ]);

        $quote = Quote::findOrFail($validated['quote_id']);
        if ($quote->client_id !== $process->client_id) {
            return redirect()
                ->route('admin.processes.show', $process)
                ->with('error', 'La cotización debe ser del mismo cliente de la solicitud.');
        }

        $data = [
            'quote_id' => $quote->id,
            'client_id' => $quote->client_id,
        ];
        if (! empty($validated['quote_item_id'])) {
            $quoteItem = QuoteItem::with('quote')->findOrFail($validated['quote_item_id']);
            if (! $quoteItem->quote || $quoteItem->quote_id != $quote->id) {
                return redirect()->route('admin.processes.show', $process)
                    ->with('error', 'El ítem no pertenece a la cotización seleccionada.');
            }
            $data['quote_item_id'] = $quoteItem->id;
            if ($process->service_type_id) {
                $quoteItem->update(['service_type_id' => $process->service_type_id]);
            }
            if (! $quote->show_service_type_column) {
                $quote->update(['show_service_type_column' => true]);
            }
        } else {
            $data['quote_item_id'] = null;
        }

        $process->update($data);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', empty($validated['quote_item_id']) ? 'Solicitud asignada a la cotización.' : 'Solicitud vinculada a la cotización e ítem seleccionados.');
    }

    /**
     * Vista detalle del expediente (para timeline y acciones).
     */
    public function show(Process $process)
    {
        $this->authorizeProcessView($process);

        $process->load([
            'client',
            'quote',
            'quoteItem.quote',
            'quoteItem.service',
            'quoteItem.serviceType',
            'serviceType',
            'checklistItems',
            'processDocuments',
            'submissions.regulatoryEvents',
            'submissions.children.regulatoryEvents',
            'submissions.quote',
            'submissions.quoteItem.quote',
            'submissions.quoteItem.service',
            'submissions.quoteItem.serviceType',
            'assignedUsers',
        ]);

        // Asegurar carpeta en Drive para este expediente (se crea al visitar la página si está configurado Drive)
        try {
            app(GoogleDriveService::class)->getOrCreateProcessFolder($process);
            $process->refresh();
        } catch (\Exception $e) {
            Log::debug('No se pudo crear/obtener carpeta Drive del proceso', ['process_id' => $process->id, 'error' => $e->getMessage()]);
        }

        // Cotizaciones del mismo cliente para poder vincular ciclos a una cotización/ítem
        $quotesForClient = Quote::with(['quoteItems.service', 'quoteItems.serviceType'])
            ->where('client_id', $process->client_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $process->submissions->loadMissing([
            'createdByUser',
            'radicadoSavedByUser',
            'regulatoryEvents.savedByUser',
        ]);

        return view('admin.processes.show', compact('process', 'quotesForClient'));
    }

    /**
     * Crear un sometimiento (inicio del flujo). Solo guarda datos de sometimiento y radicado; estado inicial Pendiente.
     */
    public function storeSubmission(Request $request, Process $process): RedirectResponse
    {
        $this->authorizeProcessView($process);
        $this->authorizeProcessFeed($process);

        $checklistItems = $process->checklistItems;

        if ($checklistItems->isEmpty()) {
            return redirect()
                ->route('admin.processes.show', $process)
                ->with('error', 'Debe existir al menos un ítem en la checklist de la solicitud.');
        }

        $notApproved = $checklistItems->where('status', '!=', ChecklistItem::STATUS_APROBADO);
        if ($notApproved->isNotEmpty()) {
            $names = $notApproved->pluck('document_name')->implode(', ');

            return redirect()
                ->route('admin.processes.show', $process)
                ->with('error', "No se puede crear el sometimiento: todos los ítems de la checklist deben estar en estado Aprobado. Pendientes: {$names}");
        }

        $validated = $request->validate([
            'submission_date' => 'required|date',
            'submission_code' => 'required|string|max:64',
            'parent_id' => 'nullable|exists:submissions,id',
        ]);

        $parentId = $validated['parent_id'] ?? null;
        $isFirstSubmission = $process->submissions()->count() === 0;

        Submission::create([
            'process_id' => $process->id,
            'parent_id' => $parentId,
            'submission_date' => $validated['submission_date'],
            'submission_code' => $validated['submission_code'],
            'status' => Submission::STATUS_PENDIENTE,
            'submission_type' => $parentId ? 'Subsanación / Nuevo intento' : 'Inicial',
            'quote_id' => $isFirstSubmission && $process->quote_id ? $process->quote_id : null,
            'quote_item_id' => $isFirstSubmission && $process->quote_item_id ? $process->quote_item_id : null,
            'created_by_user_id' => auth()->id(),
        ]);

        // El proceso sigue en Recolección hasta que se registre la respuesta "Radicado" del INVIMA.
        // Flujo: Recolección → Someter (Pendiente) → registrar Radicado → Radicado → AUTO o Resolución → En Requerimiento / Finalizado.
        $process->update(['status' => Process::STATUS_RECOLECCION]);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Sometimiento registrado correctamente. Cuando INVIMA responda, registre Radicado, AUTO o Resolución según corresponda.');
    }

    /**
     * Registrar respuesta del INVIMA: Auto/Requerimiento, Resolución Aprobatoria o Rechazo.
     */
    public function registerResponse(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorizeProcessView($submission->process);
        $this->authorizeProcessFeed($submission->process);

        $type = $request->input('response_type');

        if ($type === 'radicado') {
            if ($submission->status !== Submission::STATUS_PENDIENTE) {
                return redirect()->route('admin.processes.show', $submission->process)
                    ->with('error', 'Solo se puede registrar radicado en un sometimiento Pendiente.');
            }
            $validated = $request->validate([
                'radicado_invima' => 'required|string|max:64',
                'fecha_radicacion' => 'required|date',
                // Llave / campo de registro OBLIGATORIO para el radicado.
                'tracking_id' => 'required|string|max:64',
            ]);
            $submission->update([
                'status' => Submission::STATUS_RADICADO,
                'radicado_invima' => $validated['radicado_invima'],
                'fecha_radicacion' => $validated['fecha_radicacion'],
                'tracking_id' => $validated['tracking_id'] ?? null,
                'radicado_saved_by_user_id' => auth()->id(),
            ]);
            $submission->process->update(['status' => Process::STATUS_RADICADO]);
            $msg = $submission->isAutoFollowUpCycle()
                ? 'Radicado registrado. En este ciclo de subsanación solo puede registrar RESOLUCIÓN para cerrar la solicitud.'
                : 'Radicado registrado. En la línea de tiempo use REQUERIMIENTO AUTO o RESOLUCIÓN según corresponda.';

            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('success', $msg);
        }

        if ($type === 'rechazo') {
            $validated = $request->validate([
                'rejection_observation' => 'required|string|max:2000',
            ]);
            $submission->update([
                'status' => Submission::STATUS_RECHAZADO,
                'rejection_observation' => $validated['rejection_observation'],
            ]);
            $this->recalculateProcessStatus($submission->process);

            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('success', 'Rechazo registrado. Observación guardada. Puede volver a intentar el proceso de sometimiento desde "Crear Nuevo Intento" (vinculando a este intento).');
        }

        if ($type === 'auto') {
            if ($submission->isAutoFollowUpCycle()) {
                return redirect()->route('admin.processes.show', $submission->process)
                    ->with('error', 'En el ciclo de subsanación (Ciclo 2 o posterior) solo puede registrar Resolución desde Radicado para cerrar la solicitud, no un nuevo AUTO.');
            }
            if ($submission->status !== Submission::STATUS_RADICADO) {
                return redirect()->route('admin.processes.show', $submission->process)
                    ->with('error', 'Solo se puede registrar Requerimiento AUTO cuando el sometimiento está en estado Radicado.');
            }
            $validated = $request->validate([
                'document_number' => 'required|string|max:64',
                'notification_date' => 'required|date',
                'due_date' => 'required|date',
                'file' => 'nullable|file|mimes:pdf|max:10240',
            ]);
            $filePath = null;
            if ($request->hasFile('file')) {
                try {
                    $name = 'Auto '.($validated['document_number'] ?? '').'.pdf';
                    $result = $this->uploadProcessFileToDrive($submission->process, $request->file('file'), $name);
                    $filePath = 'drive://'.$result['drive_id'];
                } catch (\Exception $e) {
                    Log::error('Error al subir PDF de Auto a Drive', ['submission_id' => $submission->id, 'error' => $e->getMessage()]);
                    $msg = $e->getMessage();
                    if (str_contains($msg, 'OAuth') || str_contains($msg, 'token') || str_contains($msg, 'Reautoriza')) {
                        $msg = 'Google Drive no está conectado o el acceso ha expirado. Ve a Configuración > Google Drive y reautoriza.';
                    } else {
                        $msg = 'No se pudo subir el PDF a Drive: '.$msg;
                    }

                    return redirect()->route('admin.processes.show', $submission->process)->with('error', $msg);
                }
            }
            $dueDate = Carbon::parse($validated['due_date']);

            RegulatoryEvent::create([
                'submission_id' => $submission->id,
                'saved_by_user_id' => auth()->id(),
                'event_type' => RegulatoryEvent::EVENT_TYPE_AUTO,
                'document_number' => $validated['document_number'],
                'notification_date' => $validated['notification_date'],
                'due_date' => $dueDate,
                'file_path' => $filePath,
            ]);
            $submission->process->update(['status' => Process::STATUS_EN_REQUERIMIENTO]);
            $submission->update(['status' => Submission::STATUS_EN_REQUERIMIENTO]);

            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('success', 'Requerimiento AUTO registrado. Ciclo cerrado. Fecha de vencimiento: '.$dueDate->format('d/m/Y').'. Puede crear un nuevo ciclo.');
        }

        if ($type === 'aprobado') {
            if ($submission->status !== Submission::STATUS_RADICADO) {
                return redirect()->route('admin.processes.show', $submission->process)
                    ->with('error', 'Solo se puede registrar RESOLUCIÓN cuando el sometimiento está en estado Radicado.');
            }
            $validated = $request->validate([
                'resolution_number' => 'required|string|max:64',
                'resolution_date' => 'required|date',
                // Antes \"campo de registro\" obligatorio; ahora detalle/observación opcional.
                'resolution_key' => 'nullable|string|max:64',
                'file' => 'nullable|file|mimes:pdf|max:10240',
            ]);
            $filePath = null;
            if ($request->hasFile('file')) {
                try {
                    $name = 'Resolución '.($validated['resolution_number'] ?? '').'.pdf';
                    $result = $this->uploadProcessFileToDrive($submission->process, $request->file('file'), $name);
                    $filePath = 'drive://'.$result['drive_id'];
                } catch (\Exception $e) {
                    Log::error('Error al subir PDF de Resolución a Drive', ['submission_id' => $submission->id, 'error' => $e->getMessage()]);
                    $msg = $e->getMessage();
                    if (str_contains($msg, 'OAuth') || str_contains($msg, 'token') || str_contains($msg, 'Reautoriza')) {
                        $msg = 'Google Drive no está conectado o el acceso ha expirado. Ve a Configuración > Google Drive y reautoriza.';
                    } else {
                        $msg = 'No se pudo subir el PDF a Drive: '.$msg;
                    }

                    return redirect()->route('admin.processes.show', $submission->process)->with('error', $msg);
                }
            }

            RegulatoryEvent::create([
                'submission_id' => $submission->id,
                'saved_by_user_id' => auth()->id(),
                'event_type' => RegulatoryEvent::EVENT_TYPE_RESOLUCION,
                'document_number' => $validated['resolution_number'],
                'event_date' => $validated['resolution_date'],
                'resolution_key' => $validated['resolution_key'] ?? null,
                'file_path' => $filePath,
            ]);
            $submission->process->update(['status' => Process::STATUS_FINALIZADO]);
            // Solo cambiamos el estado del intento; los datos de Radicado permanecen intactos.
            $submission->update([
                'status' => Submission::STATUS_APROBADO,
            ]);

            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('success', 'Resolución aprobatoria registrada. Solicitud finalizada.');
        }

        return redirect()->back()->with('error', 'Tipo de respuesta no válido.');
    }

    /**
     * Actualizar datos de un intento (sometimiento).
     */
    public function updateSubmission(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorizeProcessView($submission->process);
        $this->authorizeProcessFeed($submission->process);

        $validated = $request->validate([
            'submission_date' => 'nullable|date',
            'submission_code' => 'nullable|string|max:64',
            'radicado_invima' => 'nullable|string|max:64',
            'tracking_id' => 'nullable|string|max:64',
            'fecha_radicacion' => 'nullable|date',
            // Si no se envía, se mantiene el estado actual del intento.
            'status' => 'sometimes|string|in:'.implode(',', Submission::statuses()),
            'rejection_observation' => 'nullable|string|max:2000',
        ]);
        // Solo actualizamos los campos presentes en la petición.
        $radicadoKeys = ['radicado_invima', 'fecha_radicacion', 'tracking_id'];
        if (array_intersect_key($validated, array_flip($radicadoKeys))) {
            $validated['radicado_saved_by_user_id'] = auth()->id();
        }
        $submission->update($validated);

        return redirect()
            ->route('admin.processes.show', $submission->process)
            ->with('success', 'Intento actualizado.');
    }

    /**
     * Actualizar únicamente los datos del Radicado (número, fecha y llave/campo de registro).
     */
    public function updateRadicado(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorizeProcessView($submission->process);
        $this->authorizeProcessFeed($submission->process);

        $validated = $request->validate([
            'radicado_invima' => 'required|string|max:64',
            'fecha_radicacion' => 'required|date',
            'tracking_id' => 'required|string|max:64',
        ]);

        $validated['radicado_saved_by_user_id'] = auth()->id();
        $submission->update($validated);

        // Aseguramos que el intento y el proceso sigan marcados como Radicado.
        $submission->update(['status' => Submission::STATUS_RADICADO]);
        $submission->process->update(['status' => Process::STATUS_RADICADO]);

        return redirect()
            ->route('admin.processes.show', $submission->process)
            ->with('success', 'Radicado actualizado.');
    }

    /**
     * Vincular un ciclo (sometimiento) a una cotización y un ítem. La cotización y el ítem deben ser del cliente del expediente.
     */
    public function linkSubmissionQuote(Request $request, Submission $submission): RedirectResponse
    {
        $process = $submission->process;
        $this->authorizeProcessView($process);
        $this->authorizeProcessDocuments($process);

        if ($request->boolean('unlink')) {
            $unlinkedQuoteId = $submission->quote_id;
            $submission->update([
                'quote_id' => null,
                'quote_item_id' => null,
            ]);
            if ($unlinkedQuoteId) {
                $this->syncQuoteTramiteColumnIfNoLinks((int) $unlinkedQuoteId);
            }

            return redirect()
                ->route('admin.processes.show', $process)
                ->with('success', 'Ciclo desvinculado de la cotización.');
        }

        $request->merge([
            'quote_item_id' => $request->input('quote_item_id') ?: null,
        ]);
        $validated = $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'quote_item_id' => 'nullable|exists:quote_items,id',
        ]);
        $quote = Quote::findOrFail($validated['quote_id']);
        if ($quote->client_id !== $process->client_id) {
            return redirect()->route('admin.processes.show', $process)
                ->with('error', 'La cotización debe ser del mismo cliente de la solicitud.');
        }

        $quoteItemId = $validated['quote_item_id'] ?? null;
        if ($quoteItemId) {
            $quoteItem = QuoteItem::with('quote')->findOrFail($quoteItemId);
            if (! $quoteItem->quote || $quoteItem->quote_id != $quote->id) {
                return redirect()->route('admin.processes.show', $process)
                    ->with('error', 'El ítem no pertenece a la cotización seleccionada.');
            }
            $submission->update([
                'quote_id' => $quote->id,
                'quote_item_id' => $quoteItem->id,
            ]);
            // Trámite del expediente → ítem de cotización (nombre de tipo de trámite en PDF/vista).
            if ($process->service_type_id) {
                $quoteItem->update(['service_type_id' => $process->service_type_id]);
            }
            // Mostrar columna Trámite en cotización y PDF (el usuario puede desactivarla al editar).
            if (! $quote->show_service_type_column) {
                $quote->update(['show_service_type_column' => true]);
            }
        } else {
            // Quitar el ítem del ciclo, manteniendo la cotización seleccionada.
            $submission->update([
                'quote_id' => $quote->id,
                'quote_item_id' => null,
            ]);
        }

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Ciclo vinculado a la cotización/ítem seleccionado.');
    }

    /**
     * Eliminar un sometimiento (intento) y toda su rama: eventos regulatorios e intentos hijos.
     * Recalcula el estado del proceso según los sometimientos restantes.
     */
    public function destroySubmission(Submission $submission): RedirectResponse
    {
        $process = $submission->process;
        $this->authorizeProcessView($process);
        $this->authorizeProcessFeed($process);
        $quoteIdsToSync = $this->collectQuoteIdsFromSubmissionTree($submission);

        // Si se elimina un sometimiento (por ejemplo el del Ciclo 1 que ya tenía AUTO),
        // también limpiamos la Gestión Documental AUTO asociada al expediente, ya que
        // deja de aplicar ese requerimiento.
        ChecklistItem::where('process_id', $process->id)
            ->where('is_for_auto', true)
            ->delete();

        $this->deleteSubmissionBranch($submission);

        foreach ($quoteIdsToSync as $qid) {
            $this->syncQuoteTramiteColumnIfNoLinks($qid);
        }

        $this->recalculateProcessStatus($process);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Intento y línea de tiempo asociada eliminados.');
    }

    /**
     * Elimina un sometimiento, sus eventos y recursivamente sus hijos.
     */
    private function deleteSubmissionBranch(Submission $submission): void
    {
        foreach ($submission->children as $child) {
            $this->deleteSubmissionBranch($child);
        }
        $submission->regulatoryEvents()->delete();
        $submission->delete();
    }

    /**
     * @return list<int>
     */
    private function collectQuoteIdsFromSubmissionTree(Submission $submission): array
    {
        $ids = $submission->quote_id ? [(int) $submission->quote_id] : [];
        foreach ($submission->children as $child) {
            $ids = array_merge($ids, $this->collectQuoteIdsFromSubmissionTree($child));
        }

        return array_values(array_unique($ids));
    }

    /**
     * Quitar los datos de Radicado de un sometimiento y eliminar todo lo que cuelga debajo (eventos e intentos hijos).
     * El intento vuelve a estado Pendiente y el proceso regresa a su estado previo (Recolección / Sometimiento).
     */
    public function destroyRadicado(Submission $submission): RedirectResponse
    {
        $process = $submission->process;
        $this->authorizeProcessView($process);
        $this->authorizeProcessFeed($process);

        // Eliminar intentos hijos y eventos asociados (ciclos posteriores, AUTO, Resolución, etc.)
        foreach ($submission->children as $child) {
            $this->deleteSubmissionBranch($child);
        }
        // Eliminar eventos regulatorios ligados a este intento (AUTO, Resolución, etc.)
        $submission->regulatoryEvents()->delete();

        // Limpiar campos de radicado y volver a Pendiente
        $submission->update([
            'radicado_invima' => null,
            'tracking_id' => null,
            'fecha_radicacion' => null,
            'radicado_saved_by_user_id' => null,
            'status' => Submission::STATUS_PENDIENTE,
        ]);

        $this->recalculateProcessStatus($process);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Radicado y eventos posteriores eliminados. El sometimiento volvió a estado Pendiente.');
    }

    /**
     * Recalcula el estado del proceso según el último sometimiento restante.
     */
    private function recalculateProcessStatus(Process $process): void
    {
        $last = $process->submissions()->orderByDesc('id')->first();
        if (! $last) {
            $process->update(['status' => Process::STATUS_RECOLECCION]);

            return;
        }
        $status = match ($last->status) {
            Submission::STATUS_APROBADO => Process::STATUS_FINALIZADO,
            Submission::STATUS_EN_REQUERIMIENTO => Process::STATUS_EN_REQUERIMIENTO,
            Submission::STATUS_RADICADO => Process::STATUS_RADICADO,
            default => Process::STATUS_RECOLECCION, // Pendiente o Rechazado: proceso sigue en Recolección
        };
        $process->update(['status' => $status]);
    }

    /**
     * Agregar un documento (ítem) a la checklist del expediente.
     */
    public function storeChecklistItem(Request $request, Process $process): RedirectResponse
    {
        $this->authorizeProcessView($process);
        $this->authorizeProcessFeed($process);

        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
        ]);
        ChecklistItem::create([
            'process_id' => $process->id,
            'document_name' => $validated['document_name'],
            'status' => ChecklistItem::STATUS_PENDIENTE,
            'is_for_auto' => $request->boolean('is_for_auto', false),
        ]);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Documento agregado a la checklist.');
    }

    /**
     * Actualizar estado y/o observación de un ítem de la checklist.
     */
    public function updateChecklistItem(Request $request, ChecklistItem $checklistItem): RedirectResponse
    {
        $this->authorizeProcessView($checklistItem->process);
        $this->authorizeProcessDocuments($checklistItem->process);

        $validated = $request->validate([
            'status' => 'required|string|in:'.implode(',', ChecklistItem::statuses()),
            'observation_agent' => 'nullable|string|max:1000',
        ]);
        $checklistItem->update([
            'status' => $validated['status'],
            'observation_agent' => $validated['observation_agent'] ?? null,
        ]);

        return redirect()
            ->route('admin.processes.show', $checklistItem->process)
            ->with('success', 'Documento actualizado.');
    }

    /**
     * Subir un archivo del expediente a Google Drive y registrarlo en Documentos en Drive.
     * Respeta la estructura de carpetas (proceso/cliente/país). Retorna ['drive_id' => id] o lanza.
     */
    private function uploadProcessFileToDrive(Process $process, UploadedFile $file, ?string $fileName = null): array
    {
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $originalName = $fileName ?? $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $extension = $file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\pL]/u', '_', $baseName) ?: 'file';
        $uniqueName = Str::uuid().'_'.$safeName.($extension ? '.'.$extension : '');
        $fullPath = $tempDir.'/'.$uniqueName;

        $file->move($tempDir, $uniqueName);
        try {
            $driveService = app(GoogleDriveService::class);
            $folderId = $driveService->getOrCreateProcessFolder($process);
            $driveFile = $driveService->uploadFile(
                $fullPath,
                $originalName,
                $folderId,
                $mimeType,
                null,
                $process->client_id
            );
            ProcessDocument::create([
                'process_id' => $process->id,
                'uploaded_by_id' => auth()->id(),
                'file_path' => 'drive://'.$driveFile['id'],
                'file_name' => $originalName,
                'file_type' => $mimeType,
                'drive_id' => $driveFile['id'],
            ]);

            return ['drive_id' => $driveFile['id']];
        } finally {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    /**
     * Subir documento del expediente a Google Drive.
     */
    public function uploadDocument(Request $request, Process $process): RedirectResponse
    {
        $this->authorizeProcessView($process);
        $this->authorizeProcessDocumentUpload($process);

        $request->validate([
            'document' => 'required|file|max:10240', // 10MB
        ]);

        $file = $request->file('document');
        try {
            $this->uploadProcessFileToDrive($process, $file);
        } catch (\Exception $e) {
            Log::error('Error al subir documento del proceso', ['process_id' => $process->id, 'error' => $e->getMessage()]);
            $message = $e->getMessage();
            if (str_contains($message, 'OAuth') || str_contains($message, 'token') || str_contains($message, 'Reautoriza')) {
                $message = 'Google Drive no está conectado o el acceso ha expirado. Ve a Configuración > Google Drive y haz clic en «Conectar con Google» para reautorizar.';
            } else {
                $message = 'No se pudo subir el documento: '.$message;
            }

            return redirect()->route('admin.processes.show', $process)
                ->with('error', $message);
        }

        return redirect()->route('admin.processes.show', $process)
            ->with('success', 'Documento subido correctamente.');
    }

    /**
     * Ver documento del expediente en el navegador (desde Drive).
     */
    public function viewDocument(Process $process, ProcessDocument $processDocument): Response|RedirectResponse
    {
        $this->authorizeProcessView($process);

        if ($processDocument->process_id !== $process->id) {
            abort(404);
        }
        if (! $processDocument->drive_id) {
            abort(404, 'Documento sin enlace a Drive.');
        }
        $driveService = app(GoogleDriveService::class);
        try {
            $fileInfo = $driveService->getFileInfo($processDocument->drive_id);
        } catch (\Exception $e) {
            Log::warning('Archivo no encontrado en Drive', ['document_id' => $processDocument->id, 'error' => $e->getMessage()]);
            abort(404, 'El documento no existe en Google Drive.');
        }
        try {
            $fileContent = $driveService->downloadFile($processDocument->drive_id);
            $mime = $processDocument->file_type ?: ($fileInfo['mimeType'] ?? 'application/octet-stream');
            $disposition = 'inline; filename="'.addcslashes($processDocument->file_name, '"\\').'"';

            return response($fileContent, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => $disposition,
                'Content-Length' => strlen($fileContent),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo de Drive para ver', ['document_id' => $processDocument->id, 'error' => $e->getMessage()]);
            abort(404, 'No se pudo obtener el documento.');
        }
    }

    /**
     * Descargar documento del expediente (desde Drive).
     */
    public function downloadDocument(Process $process, ProcessDocument $processDocument): Response|RedirectResponse
    {
        $this->authorizeProcessView($process);

        if ($processDocument->process_id !== $process->id) {
            abort(404);
        }
        if (! $processDocument->drive_id) {
            abort(404, 'Documento sin enlace a Drive.');
        }
        $driveService = app(GoogleDriveService::class);
        try {
            $fileInfo = $driveService->getFileInfo($processDocument->drive_id);
        } catch (\Exception $e) {
            abort(404, 'El documento no existe en Google Drive.');
        }
        try {
            $fileContent = $driveService->downloadFile($processDocument->drive_id);
            $mime = $processDocument->file_type ?: ($fileInfo['mimeType'] ?? 'application/octet-stream');
            $disposition = 'attachment; filename="'.addcslashes($processDocument->file_name, '"\\').'"';

            return response($fileContent, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => $disposition,
                'Content-Length' => strlen($fileContent),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al descargar documento del proceso', ['document_id' => $processDocument->id, 'error' => $e->getMessage()]);
            abort(404, 'No se pudo descargar el documento.');
        }
    }

    /**
     * Eliminar expediente (en cualquier estado). Se eliminan sometimientos, eventos, checklist, documentos y el proceso.
     * Los archivos en Drive de los documentos se intentan eliminar.
     */
    public function destroy(Process $process): RedirectResponse
    {
        $this->authorizeProcessView($process);
        $this->authorizeProcessDelete($process);

        $process->load('processDocuments');
        foreach ($process->processDocuments as $doc) {
            if ($doc->drive_id) {
                try {
                    app(GoogleDriveService::class)->deleteFile($doc->drive_id);
                } catch (\Exception $e) {
                    Log::warning('No se pudo eliminar archivo de Drive al eliminar solicitud', [
                        'process_id' => $process->id,
                        'drive_id' => $doc->drive_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        $process->delete();

        return redirect()
            ->route('admin.processes.monitor')
            ->with('success', 'Solicitud eliminada.');
    }

    /**
     * Eliminar documento del expediente (y del archivo en Google Drive si existe).
     */
    public function destroyDocument(Process $process, ProcessDocument $processDocument): RedirectResponse
    {
        $this->authorizeProcessView($process);
        $this->authorizeProcessDelete($process);

        if ($processDocument->process_id !== $process->id) {
            abort(404);
        }
        if ($processDocument->drive_id) {
            try {
                app(GoogleDriveService::class)->deleteFile($processDocument->drive_id);
            } catch (\Exception $e) {
                Log::warning('No se pudo eliminar archivo de Drive al borrar documento del proceso', [
                    'process_document_id' => $processDocument->id,
                    'drive_id' => $processDocument->drive_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        $processDocument->delete();

        return redirect()->route('admin.processes.show', $process)
            ->with('success', 'Documento eliminado.');
    }

    /**
     * Si ya no queda ningún expediente ni ciclo (sometimiento) con esa cotización vinculada,
     * desactiva la columna "Trámite" (show_service_type_column) en la cotización.
     * Si otros expedientes o ciclos siguen usando la misma cotización, la casilla se mantiene.
     */
    private function syncQuoteTramiteColumnIfNoLinks(int $quoteId): void
    {
        $quote = Quote::find($quoteId);
        if (! $quote || ! $quote->show_service_type_column) {
            return;
        }

        $stillLinked = Submission::where('quote_id', $quoteId)->exists()
            || Process::where('quote_id', $quoteId)->exists();

        if (! $stillLinked) {
            $quote->update(['show_service_type_column' => false]);
        }
    }
}
