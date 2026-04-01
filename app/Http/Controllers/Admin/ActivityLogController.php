<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Misma consulta que el listado: jerarquía + filtros GET (usuario, acción, fechas).
     */
    protected function activityLogsQueryForRequest(Request $request, PermissionService $permissionService): Builder
    {
        $query = ActivityLog::query();

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

        return $query;
    }

    public function index(Request $request, PermissionService $permissionService): View
    {
        $query = $this->activityLogsQueryForRequest($request, $permissionService)
            ->with('user')
            ->orderByDesc('created_at');

        $visibleIds = $permissionService->visibleUserIdsForHierarchy();

        $usersQuery = User::query()->orderBy('name');
        if ($visibleIds !== null) {
            if ($visibleIds === []) {
                $usersQuery->whereRaw('1 = 0');
            } else {
                $usersQuery->whereIn('id', $visibleIds);
            }
        }
        $users = $usersQuery->get(['id', 'name', 'email']);

        $logs = $query->paginate(25)->withQueryString();

        $actionLabels = ActivityLogService::actionLabels();
        $canDeleteAll = $permissionService->userHasPermission('activity_logs', 'delete');
        $hasActivityFilters = $request->filled('user_id')
            || $request->filled('action')
            || $request->filled('date_from')
            || $request->filled('date_to');

        return view('admin.activity-logs.index', compact(
            'logs',
            'users',
            'actionLabels',
            'canDeleteAll',
            'hasActivityFilters'
        ));
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
     * Elimina los registros que coinciden con el listado actual (misma consulta que index: jerarquía + filtros).
     */
    public function destroyAll(Request $request, PermissionService $permissionService): RedirectResponse
    {
        if (! $permissionService->userHasPermission('activity_logs', 'delete')) {
            abort(403);
        }

        $deleted = $this->activityLogsQueryForRequest($request, $permissionService)->delete();

        $filterNote = $this->describeActivityFiltersForLog($request);
        app(ActivityLogService::class)->log(
            'deleted',
            'Eliminó en bloque registros de actividad ('.(int) $deleted.' filas). '.$filterNote
        );

        $redirectQuery = array_filter([
            'user_id' => $request->input('user_id'),
            'action' => $request->input('action'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ], fn ($v) => $v !== null && $v !== '');

        return redirect()
            ->route('admin.activity-logs.index', $redirectQuery)
            ->with('success', 'Se eliminaron '.(int) $deleted.' registro(s) (mismo alcance que el listado mostrado).');
    }

    protected function describeActivityFiltersForLog(Request $request): string
    {
        $parts = [];
        if ($request->filled('user_id')) {
            $u = User::find((int) $request->user_id);
            $parts[] = 'usuario: '.($u ? $u->email : '#'.$request->user_id);
        }
        if ($request->filled('action')) {
            $parts[] = 'acción: '.$request->action;
        }
        if ($request->filled('date_from')) {
            $parts[] = 'desde: '.$request->date_from;
        }
        if ($request->filled('date_to')) {
            $parts[] = 'hasta: '.$request->date_to;
        }

        return $parts === [] ? 'Sin filtros (todo lo visible por jerarquía).' : 'Filtros: '.implode('; ', $parts).'.';
    }
}
