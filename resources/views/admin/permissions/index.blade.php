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
    @if (session('success'))
        <div class="mb-4 p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Permisos por Módulo -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-shield-alt mr-2 text-teal-600"></i>
                Permisos por Módulo y Acción
            </h2>
            <p class="text-sm text-gray-600 mb-4">
                Configura qué acciones puede realizar cada rol en cada módulo del sistema.
            </p>

            <form id="permissionsForm" method="POST" action="{{ route('admin.permissions.update') }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Rol</th>
                                @foreach($actions as $actionKey => $actionLabel)
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-700">{{ $actionLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($roles as $role)
                                @if($role->name === 'super_admin')
                                    <tr class="bg-gray-50">
                                        <td class="px-3 py-2 font-medium text-gray-900">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-purple-100 text-purple-800">
                                                {{ $role->name }}
                                            </span>
                                        </td>
                                        @foreach($actions as $actionKey => $actionLabel)
                                            <td class="px-2 py-2 text-center">
                                                <span class="text-green-600 text-xs">✓ Todos</span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @else
                                    @foreach($modules as $moduleKey => $moduleLabel)
                                        <tr>
                                            @if($loop->first)
                                                <td class="px-3 py-2 font-medium text-gray-900" rowspan="{{ count($modules) }}">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                                        {{ $role->name }}
                                                    </span>
                                                </td>
                                            @endif
                                            <td class="px-2 py-1 text-xs text-gray-600 border-r border-gray-200">
                                                {{ $moduleLabel }}
                                            </td>
                                            @foreach($actions as $actionKey => $actionLabel)
                                                @php
                                                    // Colección de permisos para este rol
                                                    $rolePerms = $permissions->get($role->id) ?? collect();
                                                    // Buscar permiso específico por módulo + acción
                                                    $existing = $rolePerms->first(function ($perm) use ($moduleKey, $actionKey) {
                                                        return $perm->module === $moduleKey && $perm->action === $actionKey;
                                                    });
                                                    $enabled = $existing ? (bool) $existing->enabled : false;
                                                @endphp
                                                <td class="px-2 py-1 text-center">
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
                        <i class="fas fa-save mr-2"></i> Guardar Permisos
                    </button>
                </div>
            </form>
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
                                            @endphp
                                            <div class="flex items-center justify-between text-sm">
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="hierarchy[{{ $role->id }}_{{ $targetRole->name }}][can_create]"
                                                           value="1"
                                                           {{ $canCreate ? 'checked' : '' }}
                                                           onchange="toggleViewCheckbox(this, '{{ $role->id }}_{{ $targetRole->name }}')"
                                                           class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 mr-2">
                                                    <span class="text-gray-700">{{ $targetRole->name }}</span>
                                                    @if($targetRole->name === 'client')
                                                        <span class="text-xs text-gray-400 ml-1">(portal)</span>
                                                    @endif
                                                </label>
                                                <label class="flex items-center text-xs text-gray-500">
                                                    <input type="checkbox" 
                                                           name="hierarchy[{{ $role->id }}_{{ $targetRole->name }}][can_view]"
                                                           value="1"
                                                           id="view_{{ $role->id }}_{{ $targetRole->name }}"
                                                           {{ $canView ? 'checked' : '' }}
                                                           {{ !$canCreate ? 'disabled' : '' }}
                                                           class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 mr-1">
                                                    Ver
                                                </label>
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
