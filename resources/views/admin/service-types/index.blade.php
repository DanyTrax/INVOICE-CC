@extends('layouts.admin-flowbite')

@section('title', 'Trámite - RAMS')

@section('page-title', 'Trámite')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Trámite</span>
        </div>
    </li>
@endsection

@section('content')
    @php
        $permissionService = app(App\Services\PermissionService::class);
    @endphp

    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.service-types.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i> Nuevo Trámite
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3 w-24">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceTypes as $type)
                        <tr class="bg-white border-b last:border-b-0">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $type->name }}</td>
                            <td class="px-4 py-3 text-gray-600 max-w-md truncate" title="{{ $type->description }}">{{ $type->description ? \Illuminate\Support\Str::limit(strip_tags($type->description), 80) : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($permissionService->userHasPermission('service_types', 'edit'))
                                    <a href="{{ route('admin.service-types.edit', $type) }}" class="text-teal-600 hover:text-teal-800 px-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if($permissionService->userHasPermission('service_types', 'delete'))
                                    <form action="{{ route('admin.service-types.destroy', $type) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este trámite?');">
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
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No hay tipos de trámite configurados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($serviceTypes->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $serviceTypes->links() }}
            </div>
        @endif
    </div>
@endsection
