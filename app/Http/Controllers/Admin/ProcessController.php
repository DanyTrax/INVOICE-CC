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
use App\Models\ProcessDocument;
use App\Models\ServiceType;
use App\Exports\GeneralProcessExport;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\UploadedFile;

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
            'processDocuments',
            'submissions.regulatoryEvents',
            'submissions.children.regulatoryEvents',
        ]);

        // Asegurar carpeta en Drive para este expediente (se crea al visitar la página si está configurado Drive)
        try {
            app(GoogleDriveService::class)->getOrCreateProcessFolder($process);
            $process->refresh();
        } catch (\Exception $e) {
            Log::debug('No se pudo crear/obtener carpeta Drive del proceso', ['process_id' => $process->id, 'error' => $e->getMessage()]);
        }

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
            $validated = $request->validate([
                'rejection_observation' => 'required|string|max:2000',
            ]);
            $submission->update([
                'status' => Submission::STATUS_RECHAZADO,
                'rejection_observation' => $validated['rejection_observation'],
            ]);
            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('success', 'Rechazo registrado. Puede crear un nuevo intento desde "Crear Nuevo Intento".');
        }

        if ($type === 'auto') {
            $validated = $request->validate([
                'document_number' => 'required|string|max:64',
                'notification_date' => 'required|date',
                'file' => 'nullable|file|mimes:pdf|max:10240',
            ]);
            $filePath = null;
            if ($request->hasFile('file')) {
                try {
                    $name = 'Auto ' . ($validated['document_number'] ?? '') . '.pdf';
                    $result = $this->uploadProcessFileToDrive($submission->process, $request->file('file'), $name);
                    $filePath = 'drive://' . $result['drive_id'];
                } catch (\Exception $e) {
                    Log::error('Error al subir PDF de Auto a Drive', ['submission_id' => $submission->id, 'error' => $e->getMessage()]);
                    $msg = $e->getMessage();
                    if (str_contains($msg, 'OAuth') || str_contains($msg, 'token') || str_contains($msg, 'Reautoriza')) {
                        $msg = 'Google Drive no está conectado o el acceso ha expirado. Ve a Configuración > Google Drive y reautoriza.';
                    } else {
                        $msg = 'No se pudo subir el PDF a Drive: ' . $msg;
                    }
                    return redirect()->route('admin.processes.show', $submission->process)->with('error', $msg);
                }
            }
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
            $filePath = null;
            if ($request->hasFile('file')) {
                try {
                    $name = 'Resolución ' . ($validated['resolution_number'] ?? '') . '.pdf';
                    $result = $this->uploadProcessFileToDrive($submission->process, $request->file('file'), $name);
                    $filePath = 'drive://' . $result['drive_id'];
                } catch (\Exception $e) {
                    Log::error('Error al subir PDF de Resolución a Drive', ['submission_id' => $submission->id, 'error' => $e->getMessage()]);
                    $msg = $e->getMessage();
                    if (str_contains($msg, 'OAuth') || str_contains($msg, 'token') || str_contains($msg, 'Reautoriza')) {
                        $msg = 'Google Drive no está conectado o el acceso ha expirado. Ve a Configuración > Google Drive y reautoriza.';
                    } else {
                        $msg = 'No se pudo subir el PDF a Drive: ' . $msg;
                    }
                    return redirect()->route('admin.processes.show', $submission->process)->with('error', $msg);
                }
            }

            RegulatoryEvent::create([
                'submission_id' => $submission->id,
                'event_type' => RegulatoryEvent::EVENT_TYPE_RESOLUCION,
                'document_number' => $validated['resolution_number'],
                'event_date' => $validated['resolution_date'],
                'resolution_key' => $validated['resolution_key'],
                'file_path' => $filePath,
            ]);
            $submission->process->update(['status' => Process::STATUS_FINALIZADO]);
            $submission->update([
                'status' => Submission::STATUS_APROBADO,
                'radicado_invima' => $validated['resolution_number'],
                'tracking_id' => $validated['resolution_key'],
                'fecha_radicacion' => $validated['resolution_date'],
            ]);

            return redirect()
                ->route('admin.processes.show', $submission->process)
                ->with('success', 'Resolución aprobatoria registrada. Expediente finalizado.');
        }

        return redirect()->back()->with('error', 'Tipo de respuesta no válido.');
    }

    /**
     * Actualizar datos de un intento (sometimiento).
     */
    public function updateSubmission(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'submission_date' => 'nullable|date',
            'submission_code' => 'nullable|string|max:64',
            'radicado_invima' => 'nullable|string|max:64',
            'tracking_id' => 'nullable|string|max:64',
            'fecha_radicacion' => 'nullable|date',
            'status' => 'required|string|in:' . implode(',', Submission::statuses()),
            'rejection_observation' => 'nullable|string|max:2000',
        ]);
        $submission->update($validated);
        return redirect()
            ->route('admin.processes.show', $submission->process)
            ->with('success', 'Intento actualizado.');
    }

    /**
     * Eliminar un sometimiento (intento) y toda su rama: eventos regulatorios e intentos hijos.
     * Recalcula el estado del proceso según los sometimientos restantes.
     */
    public function destroySubmission(Submission $submission): RedirectResponse
    {
        $process = $submission->process;

        $this->deleteSubmissionBranch($submission);

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
     * Recalcula el estado del proceso según el último sometimiento restante.
     */
    private function recalculateProcessStatus(Process $process): void
    {
        $last = $process->submissions()->orderByDesc('id')->first();
        if (!$last) {
            $process->update(['status' => Process::STATUS_RECOLECCION]);
            return;
        }
        $status = match ($last->status) {
            Submission::STATUS_APROBADO => Process::STATUS_FINALIZADO,
            Submission::STATUS_EN_REQUERIMIENTO => Process::STATUS_EN_REQUERIMIENTO,
            default => Process::STATUS_RADICADO,
        };
        $process->update(['status' => $status]);
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

    /**
     * Subir un archivo del expediente a Google Drive y registrarlo en Documentos en Drive.
     * Respeta la estructura de carpetas (proceso/cliente/país). Retorna ['drive_id' => id] o lanza.
     */
    private function uploadProcessFileToDrive(Process $process, UploadedFile $file, ?string $fileName = null): array
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $originalName = $fileName ?? $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $extension = $file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION) : pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\pL]/u', '_', $baseName) ?: 'file';
        $uniqueName = Str::uuid() . '_' . $safeName . ($extension ? '.' . $extension : '');
        $fullPath = $tempDir . '/' . $uniqueName;

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
                'file_path' => 'drive://' . $driveFile['id'],
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
                $message = 'No se pudo subir el documento: ' . $message;
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
    public function viewDocument(Process $process, ProcessDocument $processDocument): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        if ($processDocument->process_id !== $process->id) {
            abort(404);
        }
        if (!$processDocument->drive_id) {
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
            $disposition = 'inline; filename="' . addcslashes($processDocument->file_name, '"\\') . '"';
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
    public function downloadDocument(Process $process, ProcessDocument $processDocument): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        if ($processDocument->process_id !== $process->id) {
            abort(404);
        }
        if (!$processDocument->drive_id) {
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
            $disposition = 'attachment; filename="' . addcslashes($processDocument->file_name, '"\\') . '"';
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
     * Eliminar documento del expediente (y del archivo en Google Drive si existe).
     */
    public function destroyDocument(Process $process, ProcessDocument $processDocument): RedirectResponse
    {
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
}
