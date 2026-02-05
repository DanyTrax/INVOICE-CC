<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\RegulatoryEvent;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class RegulatoryEventController extends Controller
{
    /**
     * Registrar un Auto. Crea el evento; el Observer calcula due_date (90 días hábiles) y marca proceso En Requerimiento.
     */
    public function storeAuto(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => 'nullable|string|max:64',
            'event_date' => 'nullable|date',
            'notification_date' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('regulatory_events', 'public');
        }

        RegulatoryEvent::create([
            'submission_id' => $submission->id,
            'event_type' => RegulatoryEvent::EVENT_TYPE_AUTO,
            'document_number' => $validated['document_number'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
            'notification_date' => $validated['notification_date'],
            'due_date' => null, // lo calcula el Observer
            'resolution_key' => null,
            'file_path' => $filePath,
        ]);

        $process = $submission->process;

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Auto registrado. El expediente pasó a estado "En Requerimiento".');
    }

    /**
     * Registrar una Resolución. Crea el evento; el Observer marca el proceso como Finalizado.
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
            'event_type' => RegulatoryEvent::EVENT_TYPE_RESOLUCION,
            'document_number' => $validated['document_number'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
            'notification_date' => $validated['notification_date'] ?? null,
            'due_date' => null,
            'resolution_key' => $validated['resolution_key'] ?? null,
            'file_path' => $filePath,
        ]);

        $process = $submission->process;

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Resolución registrada. El expediente pasó a estado "Finalizado".');
    }
}
