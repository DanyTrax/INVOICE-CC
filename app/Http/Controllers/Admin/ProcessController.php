<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Submission;
use App\Models\RegulatoryEvent;
use App\Models\Company;
use App\Models\ChecklistItem;
use App\Models\ServiceType;
use App\Exports\GeneralProcessExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProcessController extends Controller
{
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
            Process::create([
                'quote_item_id' => $item->id,
                'quote_id' => $quote->id,
                'client_id' => $quote->client_id,
                'status' => Process::STATUS_RECOLECCION,
                'expediente_invima' => null,
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * Formulario para crear un proceso directo (sin cotización).
     */
    public function create(): View
    {
        $companies = Company::orderBy('name')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('name')->get();
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
        ]);

        $serviceType = ServiceType::where('name', $validated['service_type_name'])->first();
        if (!$serviceType) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['service_type_name' => 'Seleccione un tipo de trámite de la lista.']);
        }

        $process = Process::create([
            'quote_item_id' => null,
            'client_id' => $validated['client_id'],
            'service_type_id' => $serviceType->id,
            'product_reference' => $validated['product_reference'] ?? null,
            'email_name' => $validated['email_name'] ?? null,
            'status' => Process::STATUS_RECOLECCION,
        ]);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Expediente creado. Complete la checklist en la vista del expediente.');
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

        return view('admin.processes.index', compact('grouped_quotes', 'orphan_processes', 'quotes_for_assign', 'companies'));
    }

    /**
     * Monitor de Operaciones (Master List): listado plano con filtros. Soporta respuesta AJAX (solo filas).
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
        ]);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        return view('admin.processes.monitor', compact('processes', 'companies'));
    }

    /**
     * Exportar procesos del Monitor a Excel con los filtros actuales.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = [
            'client_id' => $request->filled('client_id') ? $request->client_id : null,
            'status' => $request->filled('status') ? $request->status : null,
            'date_from' => $request->filled('date_from') ? $request->date_from : null,
            'date_to' => $request->filled('date_to') ? $request->date_to : null,
            'search' => $request->filled('search') ? $request->search : null,
        ];

        $export = new GeneralProcessExport($filters);
        $filename = 'monitor-procesos-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Vincular un proceso a una cotización (organización en acordeones). Actualiza quote_id y client_id.
     */
    public function linkToQuote(Request $request, Process $process): RedirectResponse
    {
        $validated = $request->validate([
            'quote_id' => 'required|exists:quotes,id',
        ]);

        $quote = Quote::findOrFail($validated['quote_id']);

        $process->update([
            'quote_id' => $quote->id,
            'client_id' => $quote->client_id,
        ]);

        return redirect()
            ->route('admin.processes.index', ['open_quote' => $quote->id])
            ->with('success', 'Expediente asignado a la cotización.');
    }

    /**
     * Vista detalle del expediente (para timeline y acciones).
     */
    public function show(Process $process)
    {
        $process->load([
            'client',
            'quoteItem.quote',
            'quoteItem.serviceType',
            'serviceType',
            'checklistItems',
            'submissions.regulatoryEvents',
            'submissions.children.regulatoryEvents',
        ]);

        return view('admin.processes.show', compact('process'));
    }

    /**
     * Crear un sometimiento (inicio del flujo). Solo guarda datos de sometimiento y radicado; estado inicial Pendiente.
     */
    public function storeSubmission(Request $request, Process $process): RedirectResponse
    {
        $checklistItems = $process->checklistItems;

        if ($checklistItems->isEmpty()) {
            return redirect()
                ->route('admin.processes.show', $process)
                ->with('error', 'Debe existir al menos un ítem en la checklist del expediente.');
        }

        $notApproved = $checklistItems->where('status', '!=', \App\Models\ChecklistItem::STATUS_APROBADO);
        if ($notApproved->isNotEmpty()) {
            $names = $notApproved->pluck('document_name')->implode(', ');
            return redirect()
                ->route('admin.processes.show', $process)
                ->with('error', "No se puede crear el sometimiento: todos los ítems de la checklist deben estar en estado Aprobado. Pendientes: {$names}");
        }

        $validated = $request->validate([
            'submission_date' => 'required|date',
            'submission_code' => 'required|string|max:64',
            'filing_date' => 'nullable|date',
            'filing_number' => 'nullable|string|max:64',
            'parent_id' => 'nullable|exists:submissions,id',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        Submission::create([
            'process_id' => $process->id,
            'parent_id' => $parentId,
            'submission_date' => $validated['submission_date'],
            'submission_code' => $validated['submission_code'],
            'fecha_radicacion' => $validated['filing_date'] ?? null,
            'radicado_invima' => $validated['filing_number'] ?? null,
            'status' => Submission::STATUS_PENDIENTE,
            'submission_type' => $parentId ? 'Subsanación / Nuevo intento' : 'Inicial',
        ]);

        $process->update(['status' => Process::STATUS_RADICADO]);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Sometimiento registrado correctamente.');
    }

    /**
     * Registrar respuesta del INVIMA: Auto/Requerimiento, Resolución Aprobatoria o Rechazo.
     */
    public function registerResponse(Request $request, Submission $submission): RedirectResponse
    {
        $type = $request->input('response_type');

        if ($type === 'rechazo') {
            $submission->update(['status' => Submission::STATUS_RECHAZADO]);
            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('success', 'Respuesta registrada: Rechazo. Puede crear un nuevo intento desde el expediente.');
        }

        if ($type === 'auto') {
            $validated = $request->validate([
                'document_number' => 'required|string|max:64',
                'notification_date' => 'required|date',
                'file' => 'nullable|file|mimes:pdf|max:10240',
            ]);
            $filePath = $request->hasFile('file') ? $request->file('file')->store('regulatory_events', 'public') : null;
            $notificationDate = \Carbon\Carbon::parse($validated['notification_date']);
            $dueDate = $notificationDate->copy()->addWeekdays(90);

            RegulatoryEvent::create([
                'submission_id' => $submission->id,
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
                ->with('success', 'Auto registrado. Fecha límite: ' . $dueDate->format('d/m/Y') . ' (90 días hábiles).');
        }

        if ($type === 'aprobado') {
            $validated = $request->validate([
                'resolution_number' => 'required|string|max:64',
                'resolution_date' => 'required|date',
                'resolution_key' => 'required|string|max:64',
                'file' => 'nullable|file|mimes:pdf|max:10240',
            ]);
            $filePath = $request->hasFile('file') ? $request->file('file')->store('regulatory_events', 'public') : null;

            RegulatoryEvent::create([
                'submission_id' => $submission->id,
                'event_type' => RegulatoryEvent::EVENT_TYPE_RESOLUCION,
                'document_number' => $validated['resolution_number'],
                'event_date' => $validated['resolution_date'],
                'resolution_key' => $validated['resolution_key'],
                'file_path' => $filePath,
            ]);
            $submission->process->update(['status' => Process::STATUS_FINALIZADO]);
            $submission->update(['status' => Submission::STATUS_APROBADO]);

            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('success', 'Resolución aprobatoria registrada. Expediente finalizado.');
        }

        return redirect()->back()->with('error', 'Tipo de respuesta no válido.');
    }

    /**
     * Agregar un documento (ítem) a la checklist del expediente.
     */
    public function storeChecklistItem(Request $request, Process $process): RedirectResponse
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
        ]);
        ChecklistItem::create([
            'process_id' => $process->id,
            'document_name' => $validated['document_name'],
            'status' => ChecklistItem::STATUS_PENDIENTE,
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
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', ChecklistItem::statuses()),
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
}
