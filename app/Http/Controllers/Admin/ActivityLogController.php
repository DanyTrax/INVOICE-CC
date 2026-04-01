<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request, PermissionService $permissionService): View
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        $visibleIds = $permissionService->visibleUserIdsForHierarchy();
        if ($visibleIds !== null) {
            if ($visibleIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('user_id', $visibleIds);
            }
        }

        if ($request->filled('user_id')) {
            $filterUser = User::with('roles')->find((int) $request->user_id);
            if ($filterUser && ! $permissionService->canViewUserInHierarchy($request->user(), $filterUser)) {
                abort(403, 'No puedes filtrar por ese usuario.');
            }
            if ($filterUser) {
                $query->where('user_id', $filterUser->id);
            }
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->withQueryString();

        $usersQuery = User::query()->orderBy('name');
        if ($visibleIds !== null) {
            if ($visibleIds === []) {
                $usersQuery->whereRaw('1 = 0');
            } else {
                $usersQuery->whereIn('id', $visibleIds);
            }
        }
        $users = $usersQuery->get(['id', 'name', 'email']);

        $actionLabels = ActivityLogService::actionLabels();
        $canDeleteAll = $permissionService->userHasPermission('activity_logs', 'delete');

        return view('admin.activity-logs.index', compact('logs', 'users', 'actionLabels', 'canDeleteAll'));
    }

    /**
     * Línea de tiempo de actividad por usuario.
     */
    public function show(Request $request, User $user, PermissionService $permissionService): View
    {
        if (! $permissionService->canViewUserInHierarchy($request->user(), $user)) {
            abort(403, 'No tienes permiso para ver la actividad de este usuario.');
        }

        $logs = ActivityLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(50);

        $actionLabels = ActivityLogService::actionLabels();

        return view('admin.activity-logs.show', compact('user', 'logs', 'actionLabels'));
    }

    /**
     * Elimina todos los registros de actividad visibles para el usuario actual (jerarquía + permiso eliminar).
     */
    public function destroyAll(Request $request, PermissionService $permissionService): RedirectResponse
    {
        if (! $permissionService->userHasPermission('activity_logs', 'delete')) {
            abort(403);
        }

        $query = ActivityLog::query();
        $visibleIds = $permissionService->visibleUserIdsForHierarchy();
        if ($visibleIds !== null) {
            if ($visibleIds === []) {
                $deleted = 0;
            } else {
                $deleted = (clone $query)->whereIn('user_id', $visibleIds)->delete();
            }
        } else {
            $deleted = $query->delete();
        }

        app(ActivityLogService::class)->log(
            'deleted',
            'Eliminó en bloque los registros de actividad visibles ('.(int) $deleted.' filas).'
        );

        return redirect()
            ->route('admin.activity-logs.index')
            ->with('success', 'Se eliminaron '.(int) $deleted.' registro(s) de actividad.');
    }
}
