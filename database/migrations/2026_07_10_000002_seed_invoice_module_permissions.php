<?php

use App\Models\RolePermission;
use App\Models\Setting;
use App\Services\PermissionService;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Setting::current();

        $modules = ['settings_brand', 'associates', 'concepts', 'invoices'];
        $roles = Role::whereIn('name', ['admin', 'agent'])->get();

        foreach ($roles as $role) {
            foreach ($modules as $module) {
                foreach (PermissionService::getActionsForModule($module) as $action => $_label) {
                    RolePermission::updateOrCreate(
                        [
                            'role_id' => $role->id,
                            'module' => $module,
                            'action' => $action,
                        ],
                        ['enabled' => true]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};
