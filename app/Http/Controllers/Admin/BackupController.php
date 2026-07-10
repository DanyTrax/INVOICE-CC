<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemBackup;
use App\Services\BackupService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

        $backupRestoreScopes = BackupService::selectiveRestoreScopeDefinitions();

        return view('admin.backups.index', compact('backups', 'backupRestoreScopes'));
    }

    public function store(BackupService $service): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        $service->createBackup();

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Backup creado correctamente.');
    }

    public function download(SystemBackup $backup): StreamedResponse
    {
        $this->ensureCanAccessBackups();

        $path = $backup->drive_file_id;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'Archivo de backup no encontrado.');
        }

        return Storage::disk('local')->download($path, $backup->name);
    }

    public function destroy(SystemBackup $backup): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        if ($backup->drive_file_id && Storage::disk('local')->exists($backup->drive_file_id)) {
            Storage::disk('local')->delete($backup->drive_file_id);
        }

        $backup->delete();

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Backup eliminado correctamente.');
    }

    public function import(Request $request, BackupService $service): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        $scopeKeys = array_keys(BackupService::selectiveRestoreScopeDefinitions());

        $validated = $request->validate([
            'backup_file' => 'required|file|mimes:json,txt',
            'confirm_restore' => 'accepted',
            'restore_mode' => ['required', Rule::in(['full', 'selective'])],
            'scopes' => ['required_if:restore_mode,selective', 'array', 'min:1'],
            'scopes.*' => [Rule::in($scopeKeys)],
        ], [
            'confirm_restore.accepted' => 'Debes confirmar que entiendes las consecuencias de la importación.',
            'scopes.required_if' => 'En modo parcial, marca al menos un bloque a restaurar.',
            'scopes.min' => 'En modo parcial, marca al menos un bloque a restaurar.',
        ]);

        $selective = $validated['restore_mode'] === 'selective'
            ? array_values(array_unique($validated['scopes']))
            : null;

        try {
            $service->restoreBackupFromFile($request->file('backup_file'), $selective);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Error al importar el backup: '.$e->getMessage());
        }

        $msg = $selective === null
            ? 'Backup importado: restauración completa aplicada.'
            : 'Backup importado: se restauraron solo los bloques seleccionados.';

        return redirect()
            ->route('admin.backups.index')
            ->with('success', $msg);
    }

    public function restore(Request $request, SystemBackup $backup, BackupService $service): RedirectResponse
    {
        $this->ensureCanAccessBackups();

        $scopeKeys = array_keys(BackupService::selectiveRestoreScopeDefinitions());

        $validated = $request->validate([
            'restore_mode' => ['required', Rule::in(['full', 'selective'])],
            'scopes' => ['required_if:restore_mode,selective', 'array', 'min:1'],
            'scopes.*' => [Rule::in($scopeKeys)],
        ], [
            'scopes.required_if' => 'En modo parcial, marca al menos un bloque a restaurar.',
            'scopes.min' => 'En modo parcial, marca al menos un bloque a restaurar.',
        ]);

        $selective = $validated['restore_mode'] === 'selective'
            ? array_values(array_unique($validated['scopes']))
            : null;

        try {
            $path = $backup->drive_file_id;
            if (! $path || ! Storage::disk('local')->exists($path)) {
                throw new \RuntimeException('Archivo de backup no encontrado en el servidor.');
            }
            $content = Storage::disk('local')->get($path);
            $service->restoreBackupFromJson($content, $selective);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Error al restaurar backup: '.$e->getMessage());
        }

        $msg = $selective === null
            ? 'Backup restaurado (restauración completa).'
            : 'Backup restaurado solo en los bloques seleccionados.';

        return redirect()
            ->route('admin.backups.index')
            ->with('success', $msg);
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
