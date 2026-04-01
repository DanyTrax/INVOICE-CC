@extends('layouts.admin-flowbite')

@section('title', 'Catálogo de conceptos - RAMS')

@section('page-title', 'Catálogo de conceptos')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Catálogo de conceptos</span>
        </div>
    </li>
@endsection

@section('content')
    @php
        $permissionService = app(App\Services\PermissionService::class);
    @endphp
    <div class="mb-6 flex justify-between items-center">
        <p class="text-sm text-gray-600 max-w-2xl">Lista opcional para rellenar por defecto concepto, alcance y honorarios al crear una <a href="{{ route('admin.proposals.index') }}" class="text-teal-600 hover:underline">propuesta</a>.</p>
        <a href="{{ route('admin.concept-catalogs.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
            <i class="fas fa-plus mr-2"></i> Nuevo concepto
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Alcance (resumen)</th>
                        <th class="px-4 py-3">Honorario sugerido</th>
                        <th class="px-4 py-3">Activo</th>
                        <th class="px-4 py-3 w-40">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($concepts as $c)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $c->name }}</td>
                            <td class="px-4 py-3">{{ Str::limit($c->scope, 80) ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $c->default_fee !== null ? number_format($c->default_fee, 2) : '—' }}</td>
                            <td class="px-4 py-3">
                                @if($c->is_active)
                                    <span class="text-green-700">Sí</span>
                                @else
                                    <span class="text-gray-400">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($permissionService->userHasPermission('concept_catalogs', 'edit'))
                                    <a href="{{ route('admin.concept-catalogs.edit', $c) }}" class="text-teal-600 hover:text-teal-800 px-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if($permissionService->userHasPermission('concept_catalogs', 'delete'))
                                    <form action="{{ route('admin.concept-catalogs.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este concepto del catálogo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 px-2" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay conceptos. Cree uno o agregue ítems manualmente en cada propuesta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($concepts->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $concepts->links() }}</div>
        @endif
    </div>
@endsection
