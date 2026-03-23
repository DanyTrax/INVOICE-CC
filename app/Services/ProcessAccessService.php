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
     * Puede ver el expediente: supervisores; o agente con empresa + (sin asignaciones explícitas en el expediente o está en la lista).
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

        if (! $process->hasExplicitAssignments()) {
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

        if (! $process->hasExplicitAssignments()) {
            return true;
        }

        $pivot = $this->getPivot($user, $process);

        return $pivot && $pivot->pivot->can_feed_timeline;
    }

    /**
     * Puede gestionar documentos / Drive / checklist editable en este expediente.
     */
    public function canManageDocumentsOnProcess(User $user, Process $process): bool
    {
        if (! $this->canViewProcess($user, $process)) {
            return false;
        }

        if (! $this->permissions->userHasProcessAction('edit')) {
            return false;
        }

        if ($this->isSupervisor($user)) {
            return true;
        }

        if (! $process->hasExplicitAssignments()) {
            return true;
        }

        $pivot = $this->getPivot($user, $process);

        return $pivot && $pivot->pivot->can_manage_documents;
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
     * Restringe la consulta de expedientes para agentes (monitor, historial, export).
     */
    public function scopeProcessesForUser(Builder $query, User $user): Builder
    {
        if ($this->isSupervisor($user)) {
            return $query;
        }

        if (! $user->hasRole('agent')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where(function (Builder $q2) use ($user) {
                $q2->whereHas('assignedUsers', fn (Builder $q3) => $q3->where('users.id', $user->id))
                    ->whereHas('client.users', fn (Builder $q3) => $q3->where('users.id', $user->id));
            })->orWhere(function (Builder $q2) use ($user) {
                $q2->whereDoesntHave('assignedUsers')
                    ->whereHas('client.users', fn (Builder $q3) => $q3->where('users.id', $user->id));
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
