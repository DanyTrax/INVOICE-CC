<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapacitacionCompletion;
use App\Models\CapacitacionVideo;
use App\Models\User;
use App\Services\GoogleDriveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CapacitacionController extends Controller
{
    protected function requireActiveUser(): void
    {
        $user = auth()->user();
        if (!$user || !$user->is_active) {
            abort(403, 'Solo los especialistas activos pueden acceder a Capacitaciones.');
        }
    }

    protected function canManage(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || !empty($user->manage_capacitaciones));
    }

    public function index(): View|RedirectResponse
    {
        $this->requireActiveUser();

        try {
            $videos = CapacitacionVideo::with(['completions.user', 'createdByUser'])
                ->orderBy('orden')
                ->orderBy('created_at', 'desc')
                ->get();

            $especialistas = User::where('is_active', true)
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'client'))
                ->orderBy('name')
                ->get();
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'no existe')) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'El módulo Capacitaciones requiere ejecutar las migraciones en el servidor. Ejecuta: php artisan migrate --force');
            }
            throw $e;
        }

        return view('admin.capacitaciones.index', [
            'videos' => $videos,
            'especialistas' => $especialistas,
            'canManage' => $this->canManage(),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $this->requireActiveUser();
        if (!$this->canManage()) {
            abort(403, 'No tienes permiso para subir videos de capacitación.');
        }
        return view('admin.capacitaciones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireActiveUser();
        if (!$this->canManage()) {
            abort(403, 'No tienes permiso para subir videos de capacitación.');
        }
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'video' => 'required|file|mimes:mp4|max:512000', // max 512MB
        ]);

        $file = $request->file('video');
        $fechaSubida = now()->format('Y-m-d');
        try {
            $drive = app(GoogleDriveService::class);
            // Carpeta específica por capacitación: Base → Capacitaciones → {titulo} - {fecha}
            $folderId = $drive->getOrCreateCapacitacionVideoFolder($validated['titulo'], $fechaSubida);
            $tempPath = $file->getRealPath();
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            if (!str_ends_with(strtolower($fileName), '.mp4')) {
                $fileName .= '.mp4';
            }
            $result = $drive->uploadFile($tempPath, $fileName, $folderId, 'video/mp4');

            $orden = (int) CapacitacionVideo::max('orden') + 1;
            CapacitacionVideo::create([
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'drive_file_id' => $result['id'],
                'drive_folder_id' => $folderId,
                'nombre_archivo' => $fileName,
                'orden' => $orden,
                'created_by' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.capacitaciones.create')
                ->withInput()
                ->with('error', 'Error al subir a Drive: ' . $e->getMessage());
        }

        return redirect()->route('admin.capacitaciones.index')
            ->with('success', 'Video de capacitación subido correctamente.');
    }

    public function edit(CapacitacionVideo $capacitacionVideo): View|RedirectResponse
    {
        $this->requireActiveUser();
        if (!$this->canManage()) {
            abort(403, 'No tienes permiso para editar videos de capacitación.');
        }
        return view('admin.capacitaciones.edit', ['video' => $capacitacionVideo]);
    }

    public function update(Request $request, CapacitacionVideo $capacitacionVideo): RedirectResponse
    {
        $this->requireActiveUser();
        if (!$this->canManage()) {
            abort(403, 'No tienes permiso para editar videos de capacitación.');
        }
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'video' => 'nullable|file|mimes:mp4|max:512000',
        ]);

        $capacitacionVideo->titulo = $validated['titulo'];
        $capacitacionVideo->descripcion = $validated['descripcion'] ?? null;

        $drive = app(GoogleDriveService::class);
        $fechaOriginal = optional($capacitacionVideo->created_at)->format('Y-m-d') ?? now()->format('Y-m-d');

        // Renombrar carpeta en Drive cuando cambie el título
        if ($capacitacionVideo->drive_folder_id) {
            $nuevoNombreCarpeta = ($capacitacionVideo->titulo ?: 'Capacitacion') . ' - ' . $fechaOriginal;
            $drive->renameFileOrFolder($capacitacionVideo->drive_folder_id, $nuevoNombreCarpeta);
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            try {
                // Usar la misma carpeta si ya existe; si no, crearla
                $folderId = $capacitacionVideo->drive_folder_id
                    ?: $drive->getOrCreateCapacitacionVideoFolder($capacitacionVideo->titulo, $fechaOriginal);
                $tempPath = $file->getRealPath();
                $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                if (!str_ends_with(strtolower($fileName), '.mp4')) {
                    $fileName .= '.mp4';
                }
                $result = $drive->uploadFile($tempPath, $fileName, $folderId, 'video/mp4');
                $capacitacionVideo->drive_file_id = $result['id'];
                $capacitacionVideo->drive_folder_id = $folderId;
                $capacitacionVideo->nombre_archivo = $fileName;
            } catch (\Exception $e) {
                return redirect()->route('admin.capacitaciones.edit', $capacitacionVideo)
                    ->withInput()
                    ->with('error', 'Error al subir el nuevo video a Drive: ' . $e->getMessage());
            }
        }
        $capacitacionVideo->save();

        return redirect()->route('admin.capacitaciones.index')
            ->with('success', 'Video actualizado correctamente.');
    }

    public function destroy(CapacitacionVideo $capacitacionVideo): RedirectResponse
    {
        $this->requireActiveUser();
        if (!$this->canManage()) {
            abort(403, 'No tienes permiso para eliminar videos de capacitación.');
        }
        // Eliminar archivo y carpeta en Drive (si existen)
        try {
            $drive = app(GoogleDriveService::class);
            if ($capacitacionVideo->drive_file_id) {
                $drive->deleteFileOrFolder($capacitacionVideo->drive_file_id);
            }
            if ($capacitacionVideo->drive_folder_id) {
                $drive->deleteFileOrFolder($capacitacionVideo->drive_folder_id);
            }
        } catch (\Exception $e) {
            // No interrumpir el flujo si falla el borrado en Drive
        }

        $capacitacionVideo->delete();
        return redirect()->route('admin.capacitaciones.index')
            ->with('success', 'Video eliminado.');
    }

    public function ver(CapacitacionVideo $capacitacionVideo): View|RedirectResponse
    {
        $this->requireActiveUser();
        if (!$capacitacionVideo->drive_file_id) {
            return redirect()->route('admin.capacitaciones.index')
                ->with('error', 'Este video no tiene archivo asociado.');
        }
        $completion = CapacitacionCompletion::where('capacitacion_video_id', $capacitacionVideo->id)
            ->where('user_id', auth()->id())
            ->first();

        return view('admin.capacitaciones.ver', [
            'video' => $capacitacionVideo,
            'yaCompleto' => (bool) $completion,
        ]);
    }

    /**
     * Stream del video desde Drive (para reproductor HTML5).
     */
    public function stream(CapacitacionVideo $capacitacionVideo): StreamedResponse
    {
        $this->requireActiveUser();
        if (!$capacitacionVideo->drive_file_id) {
            abort(404);
        }
        $drive = app(GoogleDriveService::class);
        $token = $drive->getAccessToken();
        $url = 'https://www.googleapis.com/drive/v3/files/' . $capacitacionVideo->drive_file_id . '?alt=media';
        // Usar streaming por chunks para no agotar memoria en archivos grandes
        return response()->stream(function () use ($url, $token) {
            $response = Http::withToken($token)->withOptions(['stream' => true])->get($url);
            $stream = $response->toPsrResponse()->getBody();
            while (!$stream->eof()) {
                echo $stream->read(1024 * 1024); // 1 MB por chunk
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'video/mp4',
        ]);
    }

    public function completar(Request $request, CapacitacionVideo $capacitacionVideo): RedirectResponse
    {
        $this->requireActiveUser();
        $user = auth()->user();
        CapacitacionCompletion::updateOrCreate(
            [
                'capacitacion_video_id' => $capacitacionVideo->id,
                'user_id' => $user->id,
            ],
            ['completed_at' => now()]
        );
        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('admin.capacitaciones.index')
            ->with('success', 'Visualización registrada.');
    }

    public function reportePdf(): \Illuminate\Http\Response|RedirectResponse
    {
        $this->requireActiveUser();
        if (!$this->canManage()) {
            abort(403, 'No tienes permiso para descargar el reporte.');
        }
        $videos = CapacitacionVideo::with(['completions.user'])->orderBy('orden')->get();
        $especialistas = User::where('is_active', true)
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'client'))
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('admin.capacitaciones.reporte-pdf', [
            'videos' => $videos,
            'especialistas' => $especialistas,
            'tituloReporte' => 'Reporte de capacitaciones - Todos los videos',
        ]);
        return $pdf->download('reporte-capacitaciones-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function reporteVideoPdf(CapacitacionVideo $capacitacionVideo): \Illuminate\Http\Response|RedirectResponse
    {
        $this->requireActiveUser();
        if (!$this->canManage()) {
            abort(403, 'No tienes permiso para descargar el reporte.');
        }
        $capacitacionVideo->load(['completions.user']);
        $especialistas = $capacitacionVideo->completions->map(fn ($c) => $c->user)->filter()->sortBy('name')->values();

        $pdf = Pdf::loadView('admin.capacitaciones.reporte-video-pdf', [
            'video' => $capacitacionVideo,
            'completions' => $capacitacionVideo->completions,
            'tituloReporte' => 'Reporte - ' . $capacitacionVideo->titulo,
        ]);
        $slug = preg_replace('/[^a-z0-9-]/', '-', strtolower($capacitacionVideo->titulo));
        return $pdf->download('reporte-capacitacion-' . $slug . '-' . now()->format('Y-m-d-His') . '.pdf');
    }
}
