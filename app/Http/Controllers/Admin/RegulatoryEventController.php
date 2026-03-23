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
        if ($submission->isAutoFollowUpCycle()) {
            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('error', 'En el ciclo de subsanación solo puede cerrar con Resolución; no se registra un nuevo AUTO desde aquí.');
        }

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
            'saved_by_user_id' => auth()->id(),
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
            'saved_by_user_id' => auth()->id(),
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

    /**
     * Actualizar un evento regulatorio (Auto o Resolución).
     */
    public function update(Request $request, RegulatoryEvent $regulatoryEvent): RedirectResponse
    {
        $submission = $regulatoryEvent->submission;
        $process = $submission->process;

        if ($regulatoryEvent->event_type === RegulatoryEvent::EVENT_TYPE_AUTO) {
            $validated = $request->validate([
                'document_number' => 'nullable|string|max:64',
                'notification_date' => 'required|date',
                'due_date' => 'required|date',
            ]);
            $regulatoryEvent->update([
                'document_number' => $validated['document_number'] ?? $regulatoryEvent->document_number,
                'notification_date' => $validated['notification_date'],
                'due_date' => $validated['due_date'],
                'saved_by_user_id' => auth()->id(),
            ]);
        } else {
            $validated = $request->validate([
                'document_number' => 'nullable|string|max:64',
                'event_date' => 'nullable|date',
                'resolution_key' => 'nullable|string|max:64',
            ]);
            $regulatoryEvent->update([
                'document_number' => $validated['document_number'] ?? $regulatoryEvent->document_number,
                'event_date' => $validated['event_date'] ?? $regulatoryEvent->event_date,
                'resolution_key' => $validated['resolution_key'] ?? $regulatoryEvent->resolution_key,
                'saved_by_user_id' => auth()->id(),
            ]);
        }

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Evento actualizado.');
    }

    /**
     * Eliminar un evento regulatorio.
     * Si es una Resolución, el expediente deja de estar Finalizado y vuelve a Radicado.
     */
    public function destroy(RegulatoryEvent $regulatoryEvent): RedirectResponse
    {
        $submission = $regulatoryEvent->submission;
        $process = $submission->process;

        if ($regulatoryEvent->event_type === RegulatoryEvent::EVENT_TYPE_RESOLUCION) {
            // Borrar la resolución y devolver el intento / proceso a estado Radicado.
            $regulatoryEvent->delete();
            $submission->update([
                'status' => Submission::STATUS_RADICADO,
            ]);
            $process->update([
                'status' => Process::STATUS_RADICADO,
            ]);

            return redirect()
                ->route('admin.processes.show', $process)
                ->with('success', 'Resolución eliminada. El expediente volvió a estado Radicado.');
        }

        // Otros eventos (por ahora solo AUTO) simplemente se eliminan.
        $regulatoryEvent->delete();

        // Recalcular estado del proceso por si era el único evento que marcaba En Requerimiento.
        $last = $process->submissions()->orderByDesc('id')->first();
        if ($last) {
            $status = match ($last->status) {
                Submission::STATUS_APROBADO => Process::STATUS_FINALIZADO,
                Submission::STATUS_EN_REQUERIMIENTO => Process::STATUS_EN_REQUERIMIENTO,
                Submission::STATUS_RADICADO => Process::STATUS_RADICADO,
                default => Process::STATUS_RECOLECCION,
            };
            $process->update(['status' => $status]);
        } else {
            $process->update(['status' => Process::STATUS_RECOLECCION]);
        }

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Evento eliminado.');
    }
}
