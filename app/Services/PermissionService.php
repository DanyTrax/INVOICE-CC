<?php

namespace App\Services;

use App\Models\RoleHierarchy;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class PermissionService
{
    private const MEMO_ATTR = '_permission_service_memo';

    /** Valor especial en jerarquía: usuarios sin rol asignado (crear/ver/editar). */
    public const NO_ROLE = 'no_role';

    /**
     * Acción solo para solicitudes (processes): registrar en línea de tiempo sin editar/borrar registros existentes.
     */
    public const ACTION_TIMELINE_FEED = 'timeline_feed';

    /**
     * Módulos disponibles en el sistema (panel admin).
     *
     * @return array<string, string>
     */
    public static function getModules(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'associates' => 'Asociados',
            'concepts' => 'Conceptos de cobro',
            'invoices' => 'Cuentas de cobro',
            'users' => 'Usuarios y especialistas',
            'settings_brand' => 'Config: marca blanca',
            'settings_mail' => 'Config: correo',
            'settings_templates' => 'Config: plantillas',
            'settings_history' => 'Config: históricos',
            'settings_system' => 'Config: sistema',
            'backups' => 'Backups',
            'permissions' => 'Gestión de permisos y roles',
            'activity_logs' => 'Registros de actividad',
        ];
    }

    /**
     * Acciones estándar (todos los módulos excepto variaciones en solicitudes).
     *
     * @return array<string, string>
     */
    public static function getStandardActions(): array
    {
        return [
            'view' => 'Ver',
            'edit' => 'Editar',
            'delete' => 'Eliminar',
        ];
    }

    /**
     * Acciones por módulo (solicitudes incluye alimentar línea de tiempo).
     *
     * @return array<string, string>
     */
    public static function getActionsForModule(string $moduleKey): array
    {
        return self::getStandardActions();
    }

    /**
     * @deprecated Usar getStandardActions / getActionsForModule
     */
    public static function getActions(): array
    {
        return array_merge(self::getStandardActions(), [
            self::ACTION_TIMELINE_FEED => 'Alimentar línea de tiempo',
        ]);
    }

    /**
     * Verificar si un rol tiene permiso para una acción en un módulo.
     */
    public function hasPermission(string $roleName, string $module, string $action): bool
    {
        if ($roleName === 'super_admin') {
            return true;
        }

        $memoKey = 'hp:'.$roleName.'|'.$module.'|'.$action;
        $cached = $this->memoGet($memoKey);
        if ($cached !== null) {
            return $cached;
        }

        $role = Role::where('name', $roleName)->first();

        if (! $role) {
            $this->memoSet($memoKey, false);

            return false;
        }

        $permission = RolePermission::where('role_id', $role->id)
            ->where('module', $module)
            ->where('action', $action)
            ->first();

        $allowed = $permission && $permission->enabled;
        $this->memoSet($memoKey, $allowed);

        return $allowed;
    }

    /**
     * Permisos de solicitud con jerarquía: edit/delete implican capacidades inferiores para rutas.
     */
    public function userHasProcessAction(string $needed): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($needed === 'view') {
            return $this->userHasPermission('processes', 'view');
        }

        if ($needed === self::ACTION_TIMELINE_FEED) {
            return $this->userHasPermission('processes', self::ACTION_TIMELINE_FEED)
                || $this->userHasPermission('processes', 'edit')
                || $this->userHasPermission('processes', 'delete');
        }

        if ($needed === 'edit') {
            return $this->userHasPermission('processes', 'edit')
                || $this->userHasPermission('processes', 'delete');
        }

        if ($needed === 'delete') {
            return $this->userHasPermission('processes', 'delete');
        }

        return false;
    }

    /**
     * Cotizaciones: view | edit (incluye delete a nivel de rol) | delete.
     */
    public function userHasQuoteAction(string $needed): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($needed === 'view') {
            return $this->userHasPermission('quotes', 'view');
        }

        if ($needed === 'edit') {
            return $this->userHasPermission('quotes', 'edit')
                || $this->userHasPermission('quotes', 'delete');
        }

        if ($needed === 'delete') {
            return $this->userHasPermission('quotes', 'delete');
        }

        return false;
    }

    /**
     * Descargar PDF de cotización: ver cotizaciones o ver propuestas.
     */
    public function userCanDownloadQuotePdf(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $this->userHasPermission('quotes', 'view')
            || $this->userHasPermission('proposals', 'view');
    }

    /**
     * Verificar si el usuario autenticado tiene permiso.
     */
    public function userHasPermission(string $module, string $action): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        $memoKey = 'uhp:'.$user->getKey().'|'.$module.'|'.$action;
        $cached = $this->memoGet($memoKey);
        if ($cached !== null) {
            return $cached;
        }

        $user->loadMissing('roles');
        $roleIds = $user->roles->pluck('id')->all();

        if ($roleIds === []) {
            $this->memoSet($memoKey, false);

            return false;
        }

        $allowed = RolePermission::query()
            ->whereIn('role_id', $roleIds)
            ->where('module', $module)
            ->where('action', $action)
            ->where('enabled', true)
            ->exists();

        $this->memoSet($memoKey, $allowed);

        return $allowed;
    }

    private function memoGet(string $key): ?bool
    {
        if (! app()->bound('request')) {
            return null;
        }

        $memo = request()->attributes->get(self::MEMO_ATTR);
        if (! is_array($memo) || ! array_key_exists($key, $memo)) {
            return null;
        }

        return $memo[$key];
    }

    private function memoSet(string $key, bool $value): void
    {
        if (! app()->bound('request')) {
            return;
        }

        $memo = request()->attributes->get(self::MEMO_ATTR, []);
        if (! is_array($memo)) {
            $memo = [];
        }
        $memo[$key] = $value;
        request()->attributes->set(self::MEMO_ATTR, $memo);
    }

    /**
     * Obtener roles que un rol puede crear.
     */
    public function getRolesCanCreate(string $roleName): array
    {
        $role = Role::where('name', $roleName)->first();

        if (! $role) {
            return [];
        }

        if ($roleName === 'super_admin') {
            return Role::pluck('name')->toArray();
        }

        $fromHierarchy = RoleHierarchy::where('role_id', $role->id)
            ->where('can_create_role', '!=', null)
            ->pluck('can_create_role')
            ->toArray();
        if (! in_array($roleName, $fromHierarchy, true)) {
            $fromHierarchy[] = $roleName;
        }

        return $fromHierarchy;
    }

    /**
     * Obtener roles que un rol puede ver.
     */
    public function getRolesCanView(string $roleName): array
    {
        $role = Role::where('name', $roleName)->first();

        if (! $role) {
            return [];
        }

        if ($roleName === 'super_admin') {
            return Role::pluck('name')->toArray();
        }

        $fromHierarchy = RoleHierarchy::where('role_id', $role->id)
            ->where('can_view', true)
            ->pluck('can_create_role')
            ->toArray();
        if (! in_array($roleName, $fromHierarchy, true)) {
            $fromHierarchy[] = $roleName;
        }

        return $fromHierarchy;
    }

    /**
     * Obtener roles que un rol puede editar.
     */
    public function getRolesCanEdit(string $roleName): array
    {
        $role = Role::where('name', $roleName)->first();

        if (! $role) {
            return [];
        }

        if ($roleName === 'super_admin') {
            return array_merge(Role::pluck('name')->toArray(), [self::NO_ROLE]);
        }

        $fromHierarchy = RoleHierarchy::where('role_id', $role->id)
            ->where('can_edit', true)
            ->pluck('can_create_role')
            ->toArray();
        if (! in_array($roleName, $fromHierarchy, true)) {
            $fromHierarchy[] = $roleName;
        }

        return $fromHierarchy;
    }

    public function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Roles que el usuario puede ver en directorio / jerarquía (misma lógica que UserController).
     *
     * @return list<string>
     */
    public function getAllowedRolesToViewForUser(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return [];
        }

        try {
            foreach ($user->roles as $role) {
                $canView = $this->getRolesCanView($role->name);
                if (! empty($canView)) {
                    return $canView;
                }
            }
        } catch (\Exception $e) {
        }

        if ($user->hasRole('super_admin')) {
            return ['super_admin', 'admin', 'agent', 'client'];
        }

        if ($user->hasRole('admin')) {
            return ['admin', 'agent', 'client'];
        }

        if ($user->hasRole('agent')) {
            return ['agent', 'client'];
        }

        return [];
    }

    /**
     * ¿Puede el observador ver al usuario objetivo según jerarquía de roles?
     */
    public function canViewUserInHierarchy(User $viewer, User $target): bool
    {
        if ($viewer->hasRole('super_admin')) {
            return true;
        }

        $allowedRoles = $this->getAllowedRolesToViewForUser($viewer);

        if ($target->roles->isEmpty()) {
            return in_array(self::NO_ROLE, $allowedRoles, true);
        }

        foreach ($target->roles as $role) {
            if (in_array($role->name, $allowedRoles, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * IDs de usuarios visibles por jerarquía para listados (p. ej. registros de actividad).
     * null = sin filtro (super_admin ve todos).
     *
     * @return list<int>|null
     */
    public function visibleUserIdsForHierarchy(?User $viewer = null): ?array
    {
        $viewer = $viewer ?? auth()->user();

        if (! $viewer) {
            return [];
        }

        if ($viewer->hasRole('super_admin')) {
            return null;
        }

        $allowed = $this->getAllowedRolesToViewForUser($viewer);

        if (empty($allowed)) {
            return [];
        }

        $roleNames = array_values(array_filter($allowed, fn ($r) => $r !== self::NO_ROLE));
        $hasNoRole = in_array(self::NO_ROLE, $allowed, true);

        return User::query()
            ->where(function ($q) use ($hasNoRole, $roleNames) {
                if ($hasNoRole && empty($roleNames)) {
                    $q->whereDoesntHave('roles');
                } elseif ($hasNoRole && ! empty($roleNames)) {
                    $q->where(function ($q2) use ($roleNames) {
                        $q2->whereDoesntHave('roles')
                            ->orWhereHas('roles', fn ($r) => $r->whereIn('name', $roleNames));
                    });
                } elseif (! empty($roleNames)) {
                    $q->whereHas('roles', fn ($r) => $r->whereIn('name', $roleNames));
                }
            })
            ->pluck('id')
            ->all();
    }
}
