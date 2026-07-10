<?php

namespace Database\Seeders;

use App\Models\RolePermission;
use App\Services\PermissionService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = PermissionService::getModules();
        $deniedForAdminAgent = ['backups', 'permissions'];

        foreach (Role::whereIn('name', ['admin', 'agent'])->get() as $role) {
            foreach ($modules as $moduleKey => $_label) {
                if (in_array($moduleKey, $deniedForAdminAgent, true)) {
                    continue;
                }

                foreach (PermissionService::getActionsForModule($moduleKey) as $actionKey => $_actionLabel) {
                    RolePermission::updateOrCreate(
                        [
                            'role_id' => $role->id,
                            'module' => $moduleKey,
                            'action' => $actionKey,
                        ],
                        ['enabled' => true]
                    );
                }
            }
        }
    }
}
