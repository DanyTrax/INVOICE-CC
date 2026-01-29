<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemBackup;
use App\Services\BackupService;
use App\Services\GoogleDriveService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    /** Solo quien tiene permiso "Backups" puede acceder (controlado desde Gestión de Permisos). */
    protected function ensureCanAccessBackups(): void
    {
        if (!app(PermissionService::class)->userHasPermission('backups', 'view')) {
            abort(403, 'No tienes permiso para acceder a Backups.');
        }
    }

    public function index()
    {
        $this->ensureCanAccessBackups();

        $backups = SystemBackup::with('user')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.backups.index', compact('backups'));
    }

    public function store(BackupService $service): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        $service->createBackup();

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Backup creado y enviado a Google Drive correctamente.');
    }

    public function download(SystemBackup $backup, GoogleDriveService $drive): StreamedResponse
    {
        $this->ensureCanAccessBackups();

        $content = $drive->downloadFile($backup->drive_file_id);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $backup->name, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function destroy(SystemBackup $backup, GoogleDriveService $drive): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        if ($backup->drive_file_id) {
            try {
                $drive->deleteFile($backup->drive_file_id);
            } catch (\Throwable $e) {
                // Ignorar errores al borrar en Drive
            }
        }

        $backup->delete();

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Backup eliminado correctamente.');
    }

    public function wipe(BackupService $service): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        $service->wipeDataExceptSuperAdmin();

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Datos de negocio eliminados. Solo se conservan los usuarios super_admin.');
    }
}

