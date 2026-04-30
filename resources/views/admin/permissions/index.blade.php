@extends('layouts.admin-flowbite')

@section('title', 'Gestión de Permisos - RAMS')

@section('page-title', 'Gestión de Permisos')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Permisos</span>
        </div>
    </li>
@endsection

@section('content')
    @php
        $actionColumns = [
            'view' => 'Ver',
            \App\Services\PermissionService::ACTION_TIMELINE_FEED => 'Alimentar línea de tiempo',
            'edit' => 'Editar',
            'delete' => 'Eliminar',
        ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Crear rol + Permisos por Módulo -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">
                    <i class="fas fa-plus-circle mr-2 text-teal-600"></i>
                    Crear rol personalizado
                </h2>
                <p class="text-sm text-gray-600 mb-3">
                    Ej.: <code class="bg-gray-100 px-1 rounded">contabilidad</code>, <code class="bg-gray-100 px-1 rounded">recursos_humanos</code>. Solo letras minúsculas, números y guión bajo; debe empezar con letra.
                </p>
                <form method="POST" action="{{ route('admin.roles.store') }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div class="flex-1 min-w-[12rem]">
                        <label for="new_role_name" class="block text-xs font-medium text-gray-700 mb-1">Nombre del rol</label>
                        <input type="text" name="name" id="new_role_name" value="{{ old('name') }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500"
                               placeholder="contabilidad" maxlength="64" required pattern="[a-z][a-z0-9_]*" title="Solo minúsculas, números y _">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 text-sm">
                        Crear rol
                    </button>
                </form>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @php
                    $protectedRoleNames = ['super_admin', 'client', 'admin', 'agent'];
                    $deletableRoles = $roles->filter(fn ($r) => !in_array($r->name, $protectedRoleNames, true));
                @endphp
                @if($deletableRoles->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-700 mb-2">Roles personalizados (eliminar)</p>
                        <ul class="space-y-1">
                            @foreach($deletableRoles as $dr)
                                <li class="flex items-center justify-between gap-2 text-sm">
                                    <code class="text-gray-800">{{ $dr->name }}</code>
                                    <form action="{{ route('admin.roles.destroy', $dr) }}" method="post" class="inline"
                                          onsubmit="return confirm('¿Eliminar el rol «{{ $dr->name }}»? Se borrarán sus permisos; los usuarios con este rol quedarán sin él.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Eliminar</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-shield-alt mr-2 text-teal-600"></i>
                    Permisos por módulo
                </h2>
                <p class="text-sm text-gray-600 mb-4">
                    <strong>super_admin</strong> tiene acceso total (no se lista). En <strong>Solicitudes</strong>, «Alimentar línea de tiempo» permite registrar sometimientos, radicado y eventos nuevos sin editar ni borrar lo ya guardado; «Editar» / «Eliminar» aplican a cambios y borrados sobre datos existentes.
                </p>

            <form id="permissionsForm" method="POST" action="{{ route('admin.permissions.update') }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700">Rol</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700">Módulo</th>
                                @foreach($actionColumns as $actionLabel)
                                    <th class="px-1 py-2 text-center text-[10px] font-medium text-gray-700 max-w-[5rem] leading-tight">{{ $actionLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($roles as $role)
                                @if($role->name === 'super_admin')
                                    <tr class="bg-gray-50">
                                        <td class="px-2 py-2 font-medium text-gray-900" colspan="2">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-purple-100 text-purple-800">
                                                {{ $role->name }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-2 text-center text-xs text-green-700" colspan="{{ count($actionColumns) }}">Acceso completo a todos los módulos y acciones</td>
                                    </tr>
                                @else
                                    @foreach($modules as $moduleKey => $moduleLabel)
                                        <tr>
                                            @if($loop->first)
                                                <td class="px-2 py-1 font-medium text-gray-900 align-top" rowspan="{{ count($modules) }}">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                                        {{ $role->name }}
                                                    </span>
                                                </td>
                                            @endif
                                            <td class="px-2 py-1 text-xs text-gray-600 border-r border-gray-100">
                                                {{ $moduleLabel }}
                                            </td>
                                            @foreach($actionColumns as $actionKey => $actionLabel)
                                                @php
                                                    $moduleActions = \App\Services\PermissionService::getActionsForModule($moduleKey);
                                                    $rolePerms = $permissions->get($role->id) ?? collect();
                                                    $existing = $rolePerms->first(function ($perm) use ($moduleKey, $actionKey) {
                                                        return $perm->module === $moduleKey && $perm->action === $actionKey;
                                                    });
                                                    $enabled = $existing ? (bool) $existing->enabled : false;
                                                    $applies = array_key_exists($actionKey, $moduleActions);
                                                @endphp
                                                <td class="px-1 py-1 text-center align-middle">
                                                    @if($applies)
                                                        <input type="hidden"
                                                               name="permissions[{{ $role->id }}_{{ $moduleKey }}_{{ $actionKey }}][role_id]"
                                                               value="{{ $role->id }}">
                                                        <input type="hidden"
                                                               name="permissions[{{ $role->id }}_{{ $moduleKey }}_{{ $actionKey }}][module]"
                                                               value="{{ $moduleKey }}">
                                                        <input type="hidden"
                                                               name="permissions[{{ $role->id }}_{{ $moduleKey }}_{{ $actionKey }}][action]"
                                                               value="{{ $actionKey }}">
                                                        <input type="checkbox"
                                                               name="permissions[{{ $role->id }}_{{ $moduleKey }}_{{ $actionKey }}][enabled]"
                                                               value="1"
                                                               {{ $enabled ? 'checked' : '' }}
                                                               class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500">
                                                    @else
                                                        <span class="text-gray-300">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                        <i class="fas fa-save mr-2"></i> Guardar permisos
                    </button>
                </div>
            </form>
            </div>
        </div>

        <!-- Jerarquía de Roles -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-sitemap mr-2 text-teal-600"></i>
                Jerarquía de Roles
            </h2>
            <p class="text-sm text-gray-600 mb-4">
                Define qué roles puede crear, ver y editar cada rol. Por cada rol destino: <strong>Crear</strong>, <strong>Ver</strong> y <strong>Editar</strong> son opciones independientes. El rol <strong>client</strong> es de portal.
            </p>

            <form id="hierarchyForm" method="POST" action="{{ route('admin.permissions.hierarchy') }}">
                @csrf
                <div class="space-y-4">
                    @foreach($roles as $role)
                        @if($role->name === 'super_admin')
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-purple-100 text-purple-800 mr-2">
                                            {{ $role->name }}
                                        </span>
                                    </span>
                                    <span class="text-xs text-green-600">Puede crear y ver todos los roles</span>
                                </div>
                            </div>
                        @else
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <div class="font-medium text-gray-900 mb-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                        {{ $role->name }}
                                    </span>
                                </div>
                                <div class="space-y-2">
                                    {{-- Opción "Sin roles" para permitir crear/ver/editar usuarios sin rol --}}
                                    @php
                                        $noRole = \App\Services\PermissionService::NO_ROLE;
                                        $existingNoRole = $hierarchy->get($role->id)?->firstWhere('can_create_role', $noRole);
                                        $canCreateNoRole = $existingNoRole !== null;
                                        $canViewNoRole = $existingNoRole ? $existingNoRole->can_view : false;
                                        $canEditNoRole = $existingNoRole ? ($existingNoRole->can_edit ?? false) : false;
                                    @endphp
                                    <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="hierarchy[{{ $role->id }}_{{ $noRole }}][can_create]"
                                                   value="1"
                                                   {{ $canCreateNoRole ? 'checked' : '' }}
                                                   onchange="toggleVerEditarCheckboxes(this, '{{ $role->id }}_{{ $noRole }}')"
                                                   class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 mr-2">
                                            <span class="text-gray-700 font-medium">Sin roles</span>
                                        </label>
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center text-xs text-gray-600 cursor-pointer">
                                                <input type="checkbox" 
                                                       name="hierarchy[{{ $role->id }}_{{ $noRole }}][can_view]"
                                                       value="1"
                                                       id="view_{{ $role->id }}_{{ $noRole }}"
                                                       {{ $canViewNoRole ? 'checked' : '' }}
                                                       {{ !$canCreateNoRole ? 'disabled' : '' }}
                                                       class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 mr-1">
                                                Ver
                                            </label>
                                            <label class="flex items-center text-xs text-gray-600 cursor-pointer">
                                                <input type="checkbox" 
                                                       name="hierarchy[{{ $role->id }}_{{ $noRole }}][can_edit]"
                                                       value="1"
                                                       id="edit_{{ $role->id }}_{{ $noRole }}"
                                                       {{ $canEditNoRole ? 'checked' : '' }}
                                                       {{ !$canCreateNoRole ? 'disabled' : '' }}
                                                       class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 mr-1">
                                                Editar
                                            </label>
                                        </div>
                                        <input type="hidden" 
                                               name="hierarchy[{{ $role->id }}_{{ $noRole }}][role_id]" 
                                               value="{{ $role->id }}">
                                        <input type="hidden" 
                                               name="hierarchy[{{ $role->id }}_{{ $noRole }}][can_create_role]" 
                                               value="{{ $noRole }}">
                                    </div>
                                    @foreach($targetRolesForHierarchy ?? $roles as $targetRole)
                                        @if($targetRole->id !== $role->id)
                                            @php
                                                $existing = $hierarchy->get($role->id)?->firstWhere('can_create_role', $targetRole->name);
                                                $canCreate = $existing !== null;
                                                $canView = $existing ? $existing->can_view : false;
                                                $canEdit = $existing ? ($existing->can_edit ?? false) : false;
                                            @endphp
                                            <div class="flex items-center justify-between text-sm">
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="hierarchy[{{ $role->id }}_{{ $targetRole->name }}][can_create]"
                                                           value="1"
                                                           {{ $canCreate ? 'checked' : '' }}
                                                           onchange="toggleVerEditarCheckboxes(this, '{{ $role->id }}_{{ $targetRole->name }}')"
                                                           class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 mr-2">
                                                    <span class="text-gray-700">{{ $targetRole->name }}</span>
                                                    @if($targetRole->name === 'client')
                                                        <span class="text-xs text-gray-400 ml-1">(portal)</span>
                                                    @endif
                                                </label>
                                                <div class="flex items-center gap-4">
                                                    <label class="flex items-center text-xs text-gray-600 cursor-pointer">
                                                        <input type="checkbox" 
                                                               name="hierarchy[{{ $role->id }}_{{ $targetRole->name }}][can_view]"
                                                               value="1"
                                                               id="view_{{ $role->id }}_{{ $targetRole->name }}"
                                                               {{ $canView ? 'checked' : '' }}
                                                               {{ !$canCreate ? 'disabled' : '' }}
                                                               class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 mr-1">
                                                        Ver
                                                    </label>
                                                    <label class="flex items-center text-xs text-gray-600 cursor-pointer">
                                                        <input type="checkbox" 
                                                               name="hierarchy[{{ $role->id }}_{{ $targetRole->name }}][can_edit]"
                                                               value="1"
                                                               id="edit_{{ $role->id }}_{{ $targetRole->name }}"
                                                               {{ $canEdit ? 'checked' : '' }}
                                                               {{ !$canCreate ? 'disabled' : '' }}
                                                               class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 mr-1">
                                                        Editar
                                                    </label>
                                                </div>
                                                <input type="hidden" 
                                                       name="hierarchy[{{ $role->id }}_{{ $targetRole->name }}][role_id]" 
                                                       value="{{ $role->id }}">
                                                <input type="hidden" 
                                                       name="hierarchy[{{ $role->id }}_{{ $targetRole->name }}][can_create_role]" 
                                                       value="{{ $targetRole->name }}">
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                        <i class="fas fa-save mr-2"></i> Guardar Jerarquía
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleVerEditarCheckboxes(createCheckbox, prefix) {
            const viewCheckbox = document.getElementById('view_' + prefix);
            const editCheckbox = document.getElementById('edit_' + prefix);
            const enabled = !!createCheckbox.checked;
            if (viewCheckbox) {
                viewCheckbox.disabled = !enabled;
                if (!enabled) viewCheckbox.checked = false;
            }
            if (editCheckbox) {
                editCheckbox.disabled = !enabled;
                if (!enabled) editCheckbox.checked = false;
            }
        }
    </script>
    @endpush
@endsection
