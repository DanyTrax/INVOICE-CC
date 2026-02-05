<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\Submission;
use App\Models\RegulatoryEvent;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class ProcessController extends Controller
{
    /**
     * Listado de expedientes (processes).
     */
    public function index(Request $request)
    {
        $query = Process::with(['client', 'quoteItem.serviceType', 'submissions']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expediente_invima', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $processes = $query->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();
        $companies = Company::orderBy('name')->get();

        return view('admin.processes.index', compact('processes', 'companies'));
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
            'checklistItems',
            'submissions.regulatoryEvents',
            'submissions.children.regulatoryEvents',
        ]);

        return view('admin.processes.show', compact('process'));
    }

    /**
     * Registrar un Auto. Crea el evento y transiciona el proceso a 'En Requerimiento'.
     * due_date = 90 días hábiles desde notification_date.
     */
    public function storeAuto(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => 'nullable|string|max:64',
            'event_date' => 'nullable|date',
            'notification_date' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $dueDate = $this->addBusinessDays(
            Carbon::parse($validated['notification_date']),
            90
        );

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('regulatory_events', 'public');
        }

        RegulatoryEvent::create([
            'submission_id' => $submission->id,
            'event_type' => 'AUTO',
            'document_number' => $validated['document_number'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
            'notification_date' => $validated['notification_date'],
            'due_date' => $dueDate,
            'resolution_key' => null,
            'file_path' => $filePath,
        ]);

        $process = $submission->process;
        $process->update(['status' => 'En Requerimiento']);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Auto registrado. El expediente pasó a estado "En Requerimiento".');
    }

    /**
     * Registrar una Resolución. Crea el evento y transiciona el proceso a 'Finalizado'.
     */
    public function storeResolution(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => 'nullable|string|max:64',
            'event_date' => 'nullable|date',
            'notification_date' => 'nullable|date',
            'resolution_key' => 'nullable|string|max:64',
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('regulatory_events', 'public');
        }

        RegulatoryEvent::create([
            'submission_id' => $submission->id,
            'event_type' => 'RESOLUCION',
            'document_number' => $validated['document_number'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
            'notification_date' => $validated['notification_date'] ?? null,
            'due_date' => null,
            'resolution_key' => $validated['resolution_key'] ?? null,
            'file_path' => $filePath,
        ]);

        $process = $submission->process;
        $process->update(['status' => 'Finalizado']);

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Resolución registrada. El expediente pasó a estado "Finalizado".');
    }

    /**
     * Sumar N días hábiles (excluye sábado y domingo).
     */
    protected function addBusinessDays(Carbon $date, int $days): Carbon
    {
        return $date->copy()->addWeekdays($days);
    }
}
