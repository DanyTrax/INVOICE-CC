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

        $registrations = $query->orderBy('created_at', 'desc')
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

        // Solo crear carpeta si hay documentos para subir
        $driveFolderId = null;
        $driveFolderUrl = null;

        if ($request->hasFile('documents')) {
            try {
                $driveService = app(GoogleDriveService::class);
                
                // Nombre de la carpeta del expediente
                $folderName = $validated['product_name'] . ' - ' . ($validated['registration_number'] ?? 'Sin Número');
                
                // Determinar carpeta padre según si tiene cliente o no
                $parentFolderId = null;
                if (!empty($validated['company_id'])) {
                    $company = Company::find($validated['company_id']);
                    if ($company && $company->drive_folder_id) {
                        // Si tiene cliente con carpeta, crear dentro de ella
                        $parentFolderId = $company->drive_folder_id;
                    } else {
                        // Si tiene cliente pero no tiene carpeta, usar carpeta base de clientes
                        $parentFolderId = $driveService->getOrCreateClientsFolder();
                    }
                } else {
                    // Si no tiene cliente, usar carpeta base de expedientes sin cliente
                    $parentFolderId = $driveService->getOrCreateNoClientFolder();
                }
                
                $folder = $driveService->createFolder($folderName, $parentFolderId, $registration->id, $validated['company_id'] ?? null);
                $driveFolderId = $folder['id'];
                $driveFolderUrl = $folder['webViewLink'];
                
                // Actualizar el registro con la carpeta creada
                $registration->update([
                    'drive_folder_id' => $driveFolderId,
                    'drive_folder_url' => $driveFolderUrl,
                ]);
                
                // Refrescar el modelo para asegurar que tiene los valores actualizados
                $registration->refresh();
                
                Log::info('Carpeta de Google Drive creada para expediente', [
                    'registration_id' => $registration->id,
                    'folder_id' => $driveFolderId,
                    'company_id' => $validated['company_id'] ?? null,
                ]);
                
                // Procesar documentos subidos inmediatamente después de crear la carpeta
                if ($request->hasFile('documents')) {
                    try {
                        Log::info('Iniciando subida de documentos', [
                            'registration_id' => $registration->id,
                            'folder_id' => $driveFolderId,
                            'files_count' => count($request->file('documents')),
                        ]);
                        
                        $this->uploadDocuments($registration, $request->file('documents'), $driveFolderId);
                        
                        Log::info('Documentos subidos exitosamente', [
                            'registration_id' => $registration->id,
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
            } catch (\Exception $e) {
                Log::error('Error al crear carpeta en Google Drive para expediente', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Si hay documentos pero falló la carpeta, informar al usuario
                if ($request->hasFile('documents')) {
                    return redirect()
                        ->route('admin.registrations.edit', $registration)
                        ->with('error', 'El expediente se creó, pero no se pudo crear la carpeta en Google Drive. Error: ' . $e->getMessage() . ' Los documentos no se pudieron subir.')
                        ->withInput();
                } else {
                    // Si no hay documentos, solo informar sobre la carpeta
                    return redirect()
                        ->route('admin.registrations.edit', $registration)
                        ->with('error', 'El expediente se creó, pero no se pudo crear la carpeta en Google Drive. Error: ' . $e->getMessage())
                        ->withInput();
                }
            }
        } else {
            // Si no hay documentos, no crear carpeta (comportamiento esperado)
            Log::info('Expediente creado sin documentos, no se crea carpeta en Drive', [
                'registration_id' => $registration->id,
            ]);
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

        $oldCompanyId = $registration->company_id;
        $oldDriveFolderId = $registration->drive_folder_id;

        // Si cambió el cliente (de null a cliente o de un cliente a otro), transferir documentos
        if ($validated['company_id'] != $oldCompanyId) {
            try {
                $driveService = app(GoogleDriveService::class);
                
                // Si ahora tiene cliente y antes no tenía (o tenía otro)
                if (!empty($validated['company_id']) && $oldDriveFolderId) {
                    $newCompany = Company::find($validated['company_id']);
                    
                    if ($newCompany && $newCompany->drive_folder_id) {
                        // Mover todos los archivos de la carpeta temporal/antigua a la carpeta del nuevo cliente
                        $movedCount = $driveService->moveFolderContents($oldDriveFolderId, $newCompany->drive_folder_id);
                        
                        // Crear nueva carpeta del expediente dentro de la carpeta del cliente
                        $folderName = $validated['product_name'] . ' - ' . ($validated['registration_number'] ?? 'Sin Número');
                        $folder = $driveService->createFolder($folderName, $newCompany->drive_folder_id, $registration->id, $validated['company_id']);
                        
                        // Mover archivos a la nueva carpeta del expediente
                        if ($movedCount > 0) {
                            $driveService->moveFolderContents($newCompany->drive_folder_id, $folder['id']);
                        }
                        
                        $validated['drive_folder_id'] = $folder['id'];
                        $validated['drive_folder_url'] = $folder['webViewLink'];
                        
                        Log::info('Documentos transferidos a nuevo cliente', [
                            'registration_id' => $registration->id,
                            'old_company_id' => $oldCompanyId,
                            'new_company_id' => $validated['company_id'],
                            'files_moved' => $movedCount,
                        ]);
                    }
                } elseif (empty($validated['company_id']) && $oldCompanyId) {
                    // Si se removió el cliente, mantener la carpeta pero marcar como temporal
                    // (No hacemos nada, la carpeta queda donde está)
                }
            } catch (\Exception $e) {
                Log::error('Error al transferir documentos a nuevo cliente', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
                // Continuar sin transferir si hay error
            }
        }

        $registration->update($validated);
        
        // Refrescar el modelo
        $registration->refresh();

        // Solo crear carpeta si hay documentos para subir y no tiene carpeta
        if ($request->hasFile('documents') && !$registration->drive_folder_id) {
            try {
                $driveService = app(GoogleDriveService::class);
                
                $folderName = $validated['product_name'] . ' - ' . ($validated['registration_number'] ?? 'Sin Número');
                
                // Determinar carpeta padre según si tiene cliente o no
                $parentFolderId = null;
                if (!empty($validated['company_id'])) {
                    $company = Company::find($validated['company_id']);
                    if ($company && $company->drive_folder_id) {
                        // Si tiene cliente con carpeta, crear dentro de ella
                        $parentFolderId = $company->drive_folder_id;
                    } else {
                        // Si tiene cliente pero no tiene carpeta, usar carpeta base de clientes
                        $parentFolderId = $driveService->getOrCreateClientsFolder();
                    }
                } else {
                    // Si no tiene cliente, usar carpeta base de expedientes sin cliente
                    $parentFolderId = $driveService->getOrCreateNoClientFolder();
                }
                
                $folder = $driveService->createFolder($folderName, $parentFolderId, $registration->id, $validated['company_id'] ?? null);
                
                $registration->update([
                    'drive_folder_id' => $folder['id'],
                    'drive_folder_url' => $folder['webViewLink'],
                ]);
                
                Log::info('Carpeta creada para expediente actualizado', [
                    'registration_id' => $registration->id,
                    'folder_id' => $folder['id'],
                    'company_id' => $validated['company_id'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error al crear carpeta para expediente actualizado', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
                
                return redirect()
                    ->route('admin.registrations.edit', $registration)
                    ->with('error', 'No se pudo crear la carpeta en Google Drive. Error: ' . $e->getMessage() . ' Por favor, verifica la configuración de Google Drive en Configuración.')
                    ->withInput();
            }
        }

        // Procesar documentos subidos (solo si hay carpeta)
        if ($request->hasFile('documents')) {
            $driveFolderId = $registration->drive_folder_id;
            
            Log::info('Intentando subir documentos', [
                'registration_id' => $registration->id,
                'drive_folder_id' => $driveFolderId,
                'files_count' => count($request->file('documents')),
            ]);
            
            if ($driveFolderId) {
                try {
                    $this->uploadDocuments($registration, $request->file('documents'), $driveFolderId);
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
            } else {
                Log::warning('No se pueden subir documentos: el expediente no tiene carpeta en Drive', [
                    'registration_id' => $registration->id,
                ]);
                
                return redirect()
                    ->route('admin.registrations.edit', $registration)
                    ->with('error', 'No se puede subir documentos porque el expediente no tiene carpeta en Google Drive. Por favor, verifica la configuración de Google Drive.')
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
    protected function uploadDocuments(Registration $registration, array $files, $driveFolderId = null)
    {
        if (!$driveFolderId) {
            $driveFolderId = $registration->drive_folder_id;
        }

        if (!$driveFolderId) {
            $errorMessage = 'No se puede subir documentos: el expediente no tiene carpeta en Drive';
            Log::warning($errorMessage, [
                'registration_id' => $registration->id,
            ]);
            throw new \Exception($errorMessage);
        }

        $driveService = app(GoogleDriveService::class);
        $uploadedCount = 0;
        $errors = [];
        
        foreach ($files as $file) {
            $tempPath = null;
            $fullPath = null;
            
            try {
                // Asegurar que el directorio temporal existe
                $tempDir = storage_path('app/temp');
                if (!is_dir($tempDir)) {
                    if (!mkdir($tempDir, 0755, true)) {
                        throw new \Exception("No se pudo crear el directorio temporal: {$tempDir}");
                    }
                }
                
                // Guardar archivo temporalmente
                // Usar un nombre único para evitar conflictos
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
                $uniqueName = $safeName . '_' . time() . '_' . uniqid() . '.' . $extension;
                
                // Intentar guardar el archivo
                $tempPath = $file->storeAs('temp', $uniqueName, 'local');
                
                if (!$tempPath) {
                    throw new \Exception("No se pudo guardar el archivo temporal: {$originalName}");
                }
                
                $fullPath = storage_path('app/' . $tempPath);
                
                if (!file_exists($fullPath)) {
                    throw new \Exception("El archivo temporal no existe después de guardarlo: {$originalName}");
                }
                
                // Verificar que el archivo tiene contenido
                if (filesize($fullPath) === 0) {
                    throw new \Exception("El archivo temporal está vacío: {$originalName}");
                }
                
                Log::info('Subiendo archivo a Google Drive', [
                    'registration_id' => $registration->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'folder_id' => $driveFolderId,
                ]);
                
                // Subir a Google Drive
                $driveFile = $driveService->uploadFile(
                    $fullPath,
                    $file->getClientOriginalName(),
                    $driveFolderId,
                    $file->getMimeType(),
                    $registration->id,
                    $registration->company_id
                );
                
                // Guardar en BD
                Document::create([
                    'registration_id' => $registration->id,
                    'uploaded_by_id' => auth()->id(),
                    'file_path' => $tempPath, // Mantener referencia local
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'drive_id' => $driveFile['id'],
                    'drive_url' => $driveFile['webViewLink'] ?? null,
                ]);
                
                $uploadedCount++;
                
                Log::info('Documento subido exitosamente a Google Drive', [
                    'registration_id' => $registration->id,
                    'file_name' => $file->getClientOriginalName(),
                    'drive_id' => $driveFile['id'],
                ]);
            } catch (\Exception $e) {
                $errorMsg = "Error al subir {$file->getClientOriginalName()}: " . $e->getMessage();
                $errors[] = $errorMsg;
                
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
        
        // Si hubo errores, lanzar excepción con todos los errores
        if (!empty($errors)) {
            $errorMessage = "Se subieron {$uploadedCount} de " . count($files) . " documentos. Errores: " . implode('; ', $errors);
            throw new \Exception($errorMessage);
        }
        
        Log::info('Todos los documentos subidos exitosamente', [
            'registration_id' => $registration->id,
            'uploaded_count' => $uploadedCount,
        ]);
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Expediente eliminado exitosamente.');
    }
}
