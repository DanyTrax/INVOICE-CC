<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Submission;
use App\Models\Company;
use App\Models\ChecklistItem;
use App\Models\ServiceType;
use Illuminate\Http\Request;
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

        // Cotizaciones que tienen al menos un proceso (vía quote_items o vía quote_id).
        $grouped_quotes = Quote::whereHas('quoteItems', fn ($q) => $q->whereHas('process'))
            ->orWhereHas('processes')
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

        // Procesos sin asignar a ninguna cotización (huérfanos: sin quote_item_id ni quote_id).
        $orphan_processes = Process::whereNull('quote_item_id')
            ->whereNull('quote_id')
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
     * Crear un sometimiento. Solo permite si toda la checklist está en estado Aprobado.
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
            'radicado_invima' => 'nullable|string|max:64',
            'tracking_id' => 'nullable|string|max:64',
            'fecha_radicacion' => 'nullable|date',
            'status' => 'required|string|in:' . implode(',', Submission::statuses()),
            'parent_id' => 'nullable|exists:submissions,id',
        ]);

        $validated['process_id'] = $process->id;
        if (empty($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }

        Submission::create($validated);

        $process->update(['status' => Process::STATUS_RADICADO]);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Sometimiento registrado correctamente.');
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
