<?php

namespace App\Services;

use App\Models\Process;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProcessAccessService
{
    public function __construct(
        protected PermissionService $permissions
    ) {}

    public function isSupervisor(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    /**
     * En el vínculo usuario–empresa, ¿puede ver todos los expedientes de esa empresa sin asignación por expediente?
     */
    public function userSeesAllProcessesForProcessCompany(User $user, Process $process): bool
    {
        if (! $process->client_id) {
            return false;
        }

        $company = $user->companies()->where('companies.id', $process->client_id)->first();

        if (! $company) {
            return false;
        }

        return (bool) ($company->pivot->sees_all_processes ?? false);
    }

    /**
     * Debe filtrar por process_user para este expediente (no admin y sin "ver todos" en esa empresa).
     */
    public function userMustUsePerProcessAssignment(User $user, Process $process): bool
    {
        if ($this->isSupervisor($user)) {
            return false;
        }

        if (! $process->client_id) {
            return true;
        }

        return ! $this->userSeesAllProcessesForProcessCompany($user, $process);
    }

    /**
     * El usuario puede ver la empresa (cliente) del expediente.
     */
    public function userHasCompanyAccess(User $user, Process $process): bool
    {
        if ($this->isSupervisor($user)) {
            return true;
        }

        if (! $process->client_id) {
            return false;
        }

        return $user->companies()->where('companies.id', $process->client_id)->exists();
    }

    /**
     * Está asignado explícitamente al expediente.
     */
    public function userIsAssigned(User $user, Process $process): bool
    {
        if ($process->relationLoaded('assignedUsers')) {
            return $process->assignedUsers->contains('id', $user->id);
        }

        return $process->assignedUsers()->where('users.id', $user->id)->exists();
    }

    /**
     * Puede ver el expediente: supervisores ven todo; con "ver todos los expedientes de la empresa" solo requiere empresa;
     * si no, debe estar asignado en process_user.
     */
    public function canViewProcess(User $user, Process $process): bool
    {
        if ($this->isSupervisor($user)) {
            return true;
        }

        if (! $this->permissions->userHasProcessAction('view')) {
            return false;
        }

        if (! $this->userHasCompanyAccess($user, $process)) {
            return false;
        }

        if (! $this->userMustUsePerProcessAssignment($user, $process)) {
            return true;
        }

        return $this->userIsAssigned($user, $process);
    }

    /**
     * Puede alimentar línea de tiempo en este expediente (permiso global + pivote + asignación si aplica).
     */
    public function canFeedTimelineOnProcess(User $user, Process $process): bool
    {
        if (! $this->canViewProcess($user, $process)) {
            return false;
        }

        if (! $this->permissions->userHasProcessAction(PermissionService::ACTION_TIMELINE_FEED)) {
            return false;
        }

        if ($this->isSupervisor($user)) {
            return true;
        }

        if (! $this->userMustUsePerProcessAssignment($user, $process)) {
            return true;
        }

        $pivot = $this->getPivot($user, $process);

        return $pivot && $pivot->pivot->can_feed_timeline;
    }

    /**
     * Puede subir archivos a Drive en este expediente.
     * Quien puede alimentar la línea de tiempo también puede subir documentos; además quien tenga gestión documental explícita.
     */
    public function canUploadDocumentsOnProcess(User $user, Process $process): bool
    {
        return $this->canFeedTimelineOnProcess($user, $process)
            || $this->canManageDocumentsOnProcess($user, $process);
    }

    /**
     * Puede gestionar documentos / Drive / checklist editable en este expediente.
     * Quien solo tiene permiso de línea de tiempo en el rol puede editar checklist si el pivote tiene can_feed_timeline.
     */
    public function canManageDocumentsOnProcess(User $user, Process $process): bool
    {
        if (! $this->canViewProcess($user, $process)) {
            return false;
        }

        $canEditGlobal = $this->permissions->userHasProcessAction('edit');
        $canFeedGlobal = $this->permissions->userHasProcessAction(PermissionService::ACTION_TIMELINE_FEED);

        if (! $canEditGlobal && ! $canFeedGlobal) {
            return false;
        }

        if ($this->isSupervisor($user)) {
            return true;
        }

        // Sin asignación por expediente ("ver todos" en la empresa): mismo criterio que línea de tiempo / feed.
        // Antes solo se permitía con permiso global `edit`, y quien tenía solo `timeline_feed` veía el expediente
        // pero no podía cambiar estados en Gestión Documental (botón "Cambiar estado" oculto).
        if (! $this->userMustUsePerProcessAssignment($user, $process)) {
            return $canEditGlobal || $canFeedGlobal;
        }

        $pivot = $this->getPivot($user, $process);

        if (! $pivot) {
            return false;
        }

        $hasDocPivot = $pivot->pivot->can_manage_documents || $pivot->pivot->can_feed_timeline;

        if (! $hasDocPivot) {
            return false;
        }

        if ($canEditGlobal) {
            return true;
        }

        return $canFeedGlobal
            && ($pivot->pivot->can_feed_timeline || $pivot->pivot->can_manage_documents);
    }

    /**
     * Puede eliminar el expediente (solo permiso global; asignación no restringe a supervisores).
     */
    public function canDeleteProcess(User $user, Process $process): bool
    {
        if (! $this->canViewProcess($user, $process)) {
            return false;
        }

        return $this->permissions->userHasProcessAction('delete');
    }

    protected function getPivot(User $user, Process $process): ?User
    {
        $u = $process->assignedUsers()->where('users.id', $user->id)->first();

        return $u;
    }

    /**
     * Restringe listados/export de expedientes.
     * Incluye expedientes donde el usuario está en process_user O tiene "ver todos" en company_user para esa empresa.
     */
    public function scopeProcessesForUser(Builder $query, User $user): Builder
    {
        if ($this->isSupervisor($user)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where(function (Builder $q2) use ($user) {
                $q2->whereHas('assignedUsers', fn (Builder $q3) => $q3->where('users.id', $user->id))
                    ->whereHas('client.users', fn (Builder $q3) => $q3->where('users.id', $user->id));
            })->orWhere(function (Builder $q2) use ($user) {
                $q2->whereHas('client', function ($q3) use ($user) {
                    $q3->whereHas('users', function ($q4) use ($user) {
                        $q4->where('users.id', $user->id)
                            ->where('company_user.sees_all_processes', true);
                    });
                });
            });
        });
    }

    /**
     * Usuarios que pueden asignarse al expediente (activos, no clientes, con permiso ver expedientes).
     *
     * @return Collection<int, User>
     */
    public function assignableUsers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->with('roles')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'client'))
            ->orderBy('name')
            ->get()
            ->filter(function (User $user) {
                if ($user->hasRole('super_admin')) {
                    return true;
                }
                foreach ($user->roles as $role) {
                    if ($this->permissions->hasPermission($role->name, 'processes', 'view')) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }

    /**
     * Permisos de módulo para marcar checkboxes en el modal (por usuario).
     *
     * @return array{can_offer_timeline: bool, can_offer_documents: bool}
     */
    public function permissionFlagsForAssignableUser(User $target): array
    {
        $canTimeline = false;
        $canDocs = false;

        if ($target->hasRole('super_admin')) {
            return ['can_offer_timeline' => true, 'can_offer_documents' => true];
        }

        foreach ($target->roles as $role) {
            if ($this->permissions->hasPermission($role->name, 'processes', PermissionService::ACTION_TIMELINE_FEED)
                || $this->permissions->hasPermission($role->name, 'processes', 'edit')
                || $this->permissions->hasPermission($role->name, 'processes', 'delete')) {
                $canTimeline = true;
            }
            if ($this->permissions->hasPermission($role->name, 'processes', 'edit')
                || $this->permissions->hasPermission($role->name, 'processes', 'delete')) {
                $canDocs = true;
            }
        }

        return [
            'can_offer_timeline' => $canTimeline,
            'can_offer_documents' => $canDocs,
        ];
    }
}
