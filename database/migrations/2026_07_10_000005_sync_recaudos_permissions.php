<?php

use App\Models\RolePermission;
use App\Services\PermissionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /** @var list<string> */
    private array $obsoleteModules = [
        'companies',
        'processes',
        'registrations',
        'quotes',
        'proposals',
        'drive',
        'clients',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('role_permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        RolePermission::query()
            ->whereIn('module', $this->obsoleteModules)
            ->delete();

        RolePermission::query()
            ->where('action', PermissionService::ACTION_TIMELINE_FEED)
            ->delete();

        $modules = PermissionService::getModules();
        $deniedForAdminAgent = ['backups', 'permissions'];

        foreach (Role::whereNotIn('name', ['client', 'super_admin'])->get() as $role) {
            if (in_array($role->name, ['admin', 'agent'], true)) {
                foreach ($modules as $moduleKey => $_label) {
                    if (in_array($moduleKey, $deniedForAdminAgent, true)) {
                        continue;
                    }
                    foreach (PermissionService::getActionsForModule($moduleKey) as $action => $_a) {
                        RolePermission::updateOrCreate(
                            [
                                'role_id' => $role->id,
                                'module' => $moduleKey,
                                'action' => $action,
                            ],
                            ['enabled' => true]
                        );
                    }
                }

                continue;
            }

            $enabledModules = RolePermission::query()
                ->where('role_id', $role->id)
                ->where('enabled', true)
                ->pluck('module')
                ->unique()
                ->all();

            $hadDashboard = in_array('dashboard', $enabledModules, true);

            foreach ($modules as $moduleKey => $_label) {
                $shouldEnable = $hadDashboard && in_array($moduleKey, [
                    'dashboard',
                    'associates',
                    'concepts',
                    'invoices',
                    'users',
                ], true);

                foreach (PermissionService::getActionsForModule($moduleKey) as $action => $_a) {
                    RolePermission::updateOrCreate(
                        [
                            'role_id' => $role->id,
                            'module' => $moduleKey,
                            'action' => $action,
                        ],
                        ['enabled' => $shouldEnable]
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
