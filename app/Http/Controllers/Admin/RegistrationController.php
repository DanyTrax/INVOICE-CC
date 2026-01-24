<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
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

        // Crear carpeta en Google Drive para el expediente
        $company = Company::find($validated['company_id']);
        $driveFolderId = null;
        $driveFolderUrl = null;

        try {
            $driveService = app(GoogleDriveService::class);
            
            // Nombre de la carpeta del expediente
            $folderName = $validated['product_name'] . ' - ' . ($validated['registration_number'] ?? 'Sin Número');
            
            // Si el cliente tiene carpeta, crear dentro de ella, sino crear temporal
            $parentFolderId = $company->drive_folder_id;
            
            $folder = $driveService->createFolder($folderName, $parentFolderId);
            $driveFolderId = $folder['id'];
            $driveFolderUrl = $folder['webViewLink'];
            
            $validated['drive_folder_id'] = $driveFolderId;
            $validated['drive_folder_url'] = $driveFolderUrl;
            
            Log::info('Carpeta de Google Drive creada para expediente', [
                'registration_name' => $validated['product_name'],
                'folder_id' => $driveFolderId,
                'company_id' => $company->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear carpeta en Google Drive para expediente', [
                'registration_name' => $validated['product_name'],
                'error' => $e->getMessage(),
            ]);
            // Continuar sin carpeta si hay error
        }

        // Crear el registro
        $registration = Registration::create($validated);

        // Procesar documentos subidos
        if ($request->hasFile('documents')) {
            $this->uploadDocuments($registration, $request->file('documents'), $driveFolderId);
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
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
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

        // Si cambió el cliente, transferir documentos
        if ($validated['company_id'] != $oldCompanyId) {
            try {
                $newCompany = Company::find($validated['company_id']);
                $oldCompany = Company::find($oldCompanyId);
                
                if ($newCompany && $newCompany->drive_folder_id && $oldDriveFolderId) {
                    $driveService = app(GoogleDriveService::class);
                    
                    // Si el nuevo cliente tiene carpeta, mover el contenido
                    if ($newCompany->drive_folder_id) {
                        // Renombrar carpeta del expediente si es necesario
                        $folderName = $validated['product_name'] . ' - ' . ($validated['registration_number'] ?? 'Sin Número');
                        
                        // Mover todos los archivos de la carpeta temporal a la carpeta del cliente
                        $driveService->moveFolderContents($oldDriveFolderId, $newCompany->drive_folder_id);
                        
                        // Actualizar la carpeta del expediente para que esté dentro de la carpeta del cliente
                        // (Esto requiere actualizar el parent de la carpeta, lo cual es más complejo)
                        // Por ahora, los archivos se moverán a la carpeta del cliente directamente
                        
                        Log::info('Documentos transferidos a nuevo cliente', [
                            'registration_id' => $registration->id,
                            'old_company_id' => $oldCompanyId,
                            'new_company_id' => $validated['company_id'],
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error al transferir documentos a nuevo cliente', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
                // Continuar sin transferir si hay error
            }
        }

        // Si no tiene carpeta y ahora tiene cliente, crear carpeta
        if (!$registration->drive_folder_id && $validated['company_id']) {
            try {
                $company = Company::find($validated['company_id']);
                $driveService = app(GoogleDriveService::class);
                
                $folderName = $validated['product_name'] . ' - ' . ($validated['registration_number'] ?? 'Sin Número');
                $parentFolderId = $company->drive_folder_id;
                
                $folder = $driveService->createFolder($folderName, $parentFolderId);
                $validated['drive_folder_id'] = $folder['id'];
                $validated['drive_folder_url'] = $folder['webViewLink'];
            } catch (\Exception $e) {
                Log::error('Error al crear carpeta para expediente actualizado', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $registration->update($validated);

        // Procesar documentos subidos
        if ($request->hasFile('documents')) {
            $this->uploadDocuments($registration, $request->file('documents'), $registration->drive_folder_id);
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
            Log::warning('No se puede subir documentos: el expediente no tiene carpeta en Drive', [
                'registration_id' => $registration->id,
            ]);
            return;
        }

        try {
            $driveService = app(GoogleDriveService::class);
            
            foreach ($files as $file) {
                // Guardar archivo temporalmente
                $tempPath = $file->store('temp', 'local');
                $fullPath = storage_path('app/' . $tempPath);
                
                try {
                    // Subir a Google Drive
                    $driveFile = $driveService->uploadFile(
                        $fullPath,
                        $file->getClientOriginalName(),
                        $driveFolderId,
                        $file->getMimeType()
                    );
                    
                    // Guardar en BD
                    Document::create([
                        'registration_id' => $registration->id,
                        'uploaded_by_id' => auth()->id(),
                        'file_path' => $tempPath, // Mantener referencia local
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'drive_id' => $driveFile['id'],
                    ]);
                    
                    Log::info('Documento subido a Google Drive', [
                        'registration_id' => $registration->id,
                        'file_name' => $file->getClientOriginalName(),
                        'drive_id' => $driveFile['id'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al subir documento a Google Drive', [
                        'registration_id' => $registration->id,
                        'file_name' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ]);
                } finally {
                    // Eliminar archivo temporal
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en uploadDocuments', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Expediente eliminado exitosamente.');
    }
}
