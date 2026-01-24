<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Company;
use App\Models\User;
use App\Models\Document;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['company', 'assignedSpecialist']);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('quotation_number', 'like', "%{$search}%")
                  ->orWhere('radication_number', 'like', "%{$search}%")
                  ->orWhereHas('company', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por empresa
        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por vencimientos este mes
        if ($request->filled('filter') && $request->filter === 'expiring') {
            $query->whereMonth('expiration_date', now()->month)
                  ->whereYear('expiration_date', now()->year);
        }

        // Filtro por especialista
        if ($request->filled('specialist')) {
            $query->where('assigned_specialist_id', $request->specialist);
        }

        $registrations = $query->withCount('documents')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $companies = Company::orderBy('name')->get();
        $specialists = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.registrations.index', compact('registrations', 'companies', 'specialists'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $specialists = User::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.registrations.create', compact('companies', 'specialists'));
    }

    public function store(Request $request)
    {
        $request->merge(['company_id' => $request->input('company_id') ?: null]);

        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'assigned_specialist_id' => 'nullable|exists:users,id',
            'product_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'status' => 'required|string|max:50',
            'transaction_type' => 'nullable|string|max:100',
            'quotation_number' => 'nullable|string|max:100',
            'client_request_date' => 'nullable|date',
            'radication_date' => 'nullable|date',
            'submission_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'invima_auto_date' => 'nullable|date',
            'response_limit_date' => 'nullable|date',
            'response_radication_date' => 'nullable|date',
            'client_requirement' => 'nullable|string',
            'invima_requirement' => 'nullable|string',
            'pending_docs' => 'nullable|string',
            'observations' => 'nullable|string',
            'radication_number' => 'nullable|string|max:100',
            'key_code' => 'nullable|string|max:100',
            'resolution_number' => 'nullable|string|max:100',
            'drive_folder_url' => 'nullable|string|max:500',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:10240', // 10MB máximo
        ]);

        // Crear el registro primero
        $registration = Registration::create($validated);

        if ($request->hasFile('documents')) {
            try {
                $this->uploadDocuments($registration, $request->file('documents'));
                Log::info('Documentos subidos a Google Drive', [
                    'registration_id' => $registration->id,
                    'files_count' => count($request->file('documents')),
                ]);
            } catch (\Exception $e) {
                Log::error('Error al subir documentos al crear expediente', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()
                    ->route('admin.registrations.edit', $registration)
                    ->with('error', 'El expediente se creó, pero hubo un error al subir los documentos: ' . $e->getMessage())
                    ->withInput();
            }
        }

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Expediente creado exitosamente.');
    }

    public function show(Registration $registration)
    {
        $registration->load(['company', 'assignedSpecialist', 'documents']);
        
        return view('admin.registrations.show', compact('registration'));
    }

    public function edit(Registration $registration)
    {
        $companies = Company::orderBy('name')->get();
        $specialists = User::where('is_active', true)->orderBy('name')->get();
        $registration->load(['company', 'assignedSpecialist', 'documents']);
        
        return view('admin.registrations.edit', compact('registration', 'companies', 'specialists'));
    }

    public function update(Request $request, Registration $registration)
    {
        $request->merge(['company_id' => $request->input('company_id') ?: null]);

        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'assigned_specialist_id' => 'nullable|exists:users,id',
            'product_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'status' => 'required|in:vigente,tramite,requerimiento,vencido',
            'transaction_type' => 'nullable|string|max:100',
            'quotation_number' => 'nullable|string|max:100',
            'client_request_date' => 'nullable|date',
            'radication_date' => 'nullable|date',
            'submission_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'invima_auto_date' => 'nullable|date',
            'response_limit_date' => 'nullable|date',
            'response_radication_date' => 'nullable|date',
            'client_requirement' => 'nullable|string',
            'invima_requirement' => 'nullable|string',
            'pending_docs' => 'nullable|string',
            'observations' => 'nullable|string',
            'radication_number' => 'nullable|string|max:100',
            'key_code' => 'nullable|string|max:100',
            'resolution_number' => 'nullable|string|max:100',
            'drive_folder_url' => 'nullable|string|max:500',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:10240', // 10MB máximo
        ]);

        $registration->update($validated);
        $registration->refresh();

        if ($request->hasFile('documents')) {
            try {
                $this->uploadDocuments($registration, $request->file('documents'));
                Log::info('Documentos subidos a Google Drive', [
                    'registration_id' => $registration->id,
                    'files_count' => count($request->file('documents')),
                ]);
            } catch (\Exception $e) {
                Log::error('Error al subir documentos', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()
                    ->route('admin.registrations.edit', $registration)
                    ->with('error', 'El expediente se actualizó, pero hubo un error al subir los documentos: ' . $e->getMessage())
                    ->withInput();
            }
        }

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Expediente actualizado exitosamente.');
    }

    /**
     * Subir documentos a Google Drive y guardar en BD
     */
    protected function uploadDocuments(Registration $registration, array $files): void
    {
        $driveService = app(GoogleDriveService::class);
        
        // Obtener o crear carpeta del expediente en Drive
        $driveFolderId = $driveService->getOrCreateRegistrationFolder($registration);
        
        $uploadedCount = 0;
        $errors = [];
        $tempDir = storage_path('app/temp');

        foreach ($files as $file) {
            $tempPath = null;
            $fullPath = null;
            
            try {
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType() ?: 'application/octet-stream';
                
                // Crear directorio temporal si no existe
                if (!is_dir($tempDir)) {
                    if (!mkdir($tempDir, 0755, true)) {
                        throw new \Exception("No se pudo crear el directorio temporal: {$tempDir}");
                    }
                }
                
                // Guardar archivo temporalmente
                $extension = $file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION);
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9_\-\pL]/u', '_', $baseName) ?: 'file';
                $uniqueName = Str::uuid() . '_' . $safeName . ($extension ? '.' . $extension : '');
                $fullPath = $tempDir . '/' . $uniqueName;
                
                if (!$file->move($tempDir, $uniqueName)) {
                    throw new \Exception("No se pudo guardar el archivo temporal: {$originalName}");
                }
                
                if (!file_exists($fullPath) || filesize($fullPath) === 0) {
                    throw new \Exception("El archivo temporal está vacío o no existe: {$originalName}");
                }
                
                // Subir a Google Drive
                $driveFile = $driveService->uploadFile(
                    $fullPath,
                    $originalName,
                    $driveFolderId,
                    $mimeType,
                    $registration->id,
                    $registration->company_id
                );
                
                // Guardar en BD con drive_id
                Document::create([
                    'registration_id' => $registration->id,
                    'uploaded_by_id' => auth()->id(),
                    'file_path' => 'drive://' . $driveFile['id'], // Marcar como archivo de Drive
                    'file_name' => $originalName,
                    'file_type' => $mimeType,
                    'drive_id' => $driveFile['id'],
                ]);

                $uploadedCount++;
                
                Log::info('Documento subido a Google Drive', [
                    'registration_id' => $registration->id,
                    'file_name' => $originalName,
                    'drive_id' => $driveFile['id'],
                ]);
            } catch (\Exception $e) {
                $errors[] = "Error al subir {$file->getClientOriginalName()}: " . $e->getMessage();
                Log::error('Error al subir documento a Google Drive', [
                    'registration_id' => $registration->id,
                    'file_name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } finally {
                // Eliminar archivo temporal
                if ($fullPath && file_exists($fullPath)) {
                    try {
                        unlink($fullPath);
                    } catch (\Exception $e) {
                        Log::warning('No se pudo eliminar archivo temporal', [
                            'path' => $fullPath,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Se subieron ' . $uploadedCount . ' de ' . count($files) . ' documentos. Errores: ' . implode('; ', $errors));
        }
    }

    /**
     * Ver documento en el navegador (inline) desde Google Drive.
     */
    public function viewDocument(Registration $registration, Document $document): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        if ($document->registration_id !== $registration->id) {
            abort(404);
        }

        if ($document->drive_id) {
            try {
                $driveService = app(GoogleDriveService::class);
                $fileContent = $driveService->downloadFile($document->drive_id);
                $mime = $document->file_type ?: 'application/octet-stream';
                $filename = $document->file_name;
                $disposition = 'inline; filename="' . addcslashes($filename, '"\\') . '"';

                return response($fileContent, 200, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => $disposition,
                    'Content-Length' => strlen($fileContent),
                ]);
            } catch (\Exception $e) {
                Log::error('Error al obtener archivo de Drive para ver', [
                    'document_id' => $document->id,
                    'drive_id' => $document->drive_id,
                    'error' => $e->getMessage(),
                ]);
                // Fallback: redirigir a Drive
                return redirect('https://drive.google.com/file/d/' . $document->drive_id . '/view');
            }
        }

        // Legacy: archivo local
        if ($document->file_path && str_starts_with($document->file_path, 'registration-documents/') && Storage::disk('local')->exists($document->file_path)) {
            $path = Storage::disk('local')->path($document->file_path);
            $mime = $document->file_type ?: 'application/octet-stream';
            $filename = $document->file_name;
            $disposition = 'inline; filename="' . addcslashes($filename, '"\\') . '"';

            return response()->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => $disposition,
            ]);
        }

        abort(404, 'Documento no encontrado.');
    }

    /**
     * Descargar documento desde Google Drive.
     */
    public function downloadDocument(Registration $registration, Document $document): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        if ($document->registration_id !== $registration->id) {
            abort(404);
        }

        if ($document->drive_id) {
            try {
                $driveService = app(GoogleDriveService::class);
                $fileContent = $driveService->downloadFile($document->drive_id);
                $mime = $document->file_type ?: 'application/octet-stream';
                $filename = $document->file_name;
                $disposition = 'attachment; filename="' . addcslashes($filename, '"\\') . '"';

                return response($fileContent, 200, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => $disposition,
                    'Content-Length' => strlen($fileContent),
                ]);
            } catch (\Exception $e) {
                Log::error('Error al obtener archivo de Drive para descargar', [
                    'document_id' => $document->id,
                    'drive_id' => $document->drive_id,
                    'error' => $e->getMessage(),
                ]);
                // Fallback: redirigir a Drive
                return redirect('https://drive.google.com/uc?export=download&id=' . $document->drive_id);
            }
        }

        // Legacy: archivo local
        if ($document->file_path && str_starts_with($document->file_path, 'registration-documents/') && Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download(
                $document->file_path,
                $document->file_name,
                ['Content-Type' => $document->file_type ?: 'application/octet-stream']
            );
        }

        abort(404, 'Documento no encontrado.');
    }

    public function destroy(Registration $registration)
    {
        $driveService = app(GoogleDriveService::class);
        
        // Eliminar documentos de Drive y local
        foreach ($registration->documents as $doc) {
            if ($doc->drive_id) {
                try {
                    $driveService->deleteFile($doc->drive_id);
                } catch (\Exception $e) {
                    Log::warning('No se pudo eliminar documento de Drive al eliminar expediente', [
                        'document_id' => $doc->id,
                        'drive_id' => $doc->drive_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            if ($doc->file_path && str_starts_with($doc->file_path, 'registration-documents/') && Storage::disk('local')->exists($doc->file_path)) {
                Storage::disk('local')->delete($doc->file_path);
            }
        }
        
        $registration->delete();

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Expediente eliminado exitosamente.');
    }

    /**
     * Eliminar un documento del expediente (almacenamiento local y/o Drive legacy).
     */
    public function destroyDocument(Registration $registration, Document $document)
    {
        if ($document->registration_id !== $registration->id) {
            abort(404);
        }

        try {
            if ($document->file_path && !str_starts_with($document->file_path, 'temp/') && Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }
            if ($document->drive_id) {
                try {
                    app(GoogleDriveService::class)->deleteFile($document->drive_id);
                } catch (\Exception $e) {
                    Log::warning('No se pudo eliminar de Drive (legacy)', ['document_id' => $document->id, 'error' => $e->getMessage()]);
                }
            }
            $document->delete();
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'document_id' => $document->id,
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()
                ->route('admin.registrations.show', $registration)
                ->with('error', 'No se pudo eliminar el documento: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Documento eliminado correctamente.');
    }
}
