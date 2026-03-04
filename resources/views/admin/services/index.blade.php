@extends('layouts.admin-flowbite')

@section('title', 'Servicios - RAMS')

@section('page-title', 'Servicios')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Servicios</span>
        </div>
    </li>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.services.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i> Nuevo Servicio
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Servicio</th>
                        <th class="px-4 py-3">Alcance (resumen)</th>
                        <th class="px-4 py-3">Activo</th>
                        <th class="px-4 py-3 w-24">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $svc)
                        <tr class="bg-white border-b last:border-b-0">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $svc->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $svc->default_scope ? mb_substr($svc->default_scope, 0, 80) . (mb_strlen($svc->default_scope) > 80 ? '…' : '') : '-' }}</td>
                            <td class="px-4 py-3">
                                @if($svc->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Sí</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.services.edit', $svc) }}" class="text-teal-600 hover:text-teal-800 text-sm font-medium mr-2">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>
                                <form action="{{ route('admin.services.destroy', $svc) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este servicio?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        <i class="fas fa-trash-alt mr-1"></i>Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No hay servicios configurados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($services->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $services->links() }}
            </div>
        @endif
    </div>
@endsection
