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
        if (! app(PermissionService::class)->userHasPermission('backups', 'view')) {
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

    public function import(Request $request, BackupService $service): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        $request->validate([
            'backup_file' => 'required|file|mimes:json,txt',
            'confirm_restore' => 'accepted',
        ], [
            'confirm_restore.accepted' => 'Debes confirmar que entiendes que la importación restablecerá todos los datos.',
        ]);

        try {
            $service->restoreBackupFromFile($request->file('backup_file'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Error al importar el backup: '.$e->getMessage());
        }

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Backup importado correctamente y datos restaurados.');
    }

    public function restore(SystemBackup $backup, BackupService $service, GoogleDriveService $drive): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        try {
            $content = $drive->downloadFile($backup->drive_file_id);
            $service->restoreBackupFromJson($content);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Error al restaurar backup desde Drive: '.$e->getMessage());
        }

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Backup restaurado desde Drive correctamente.');
    }

    public function wipe(Request $request, BackupService $service): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        // Checkboxes no enviados = desmarcados; no usar input(..., true) o queda siempre en true.
        $preserveCurrentUser = $request->boolean('preserve_current_user');
        $preserveRolesAndPermissions = $request->boolean('preserve_roles_permissions');

        $service->wipeDataExceptSuperAdmin($preserveCurrentUser, $preserveRolesAndPermissions);

        $message = 'Datos de negocio eliminados.';
        if ($preserveCurrentUser) {
            $message .= ' Se conserva el usuario actual (si aplica).';
        }
        if ($preserveRolesAndPermissions) {
            $message .= ' Se conservan roles y permisos.';
        } else {
            $message .= ' Roles y matriz de permisos eliminados; solo queda el rol super_admin para los super_admin conservados.';
        }

        return redirect()
            ->route('admin.backups.index')
            ->with('success', $message);
    }
}
