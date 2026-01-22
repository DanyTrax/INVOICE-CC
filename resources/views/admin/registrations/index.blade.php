@extends('layouts.admin-flowbite')

@section('title', 'Registros (Expedientes) - RAMS')

@section('page-title', 'Registros (Expedientes)')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Expedientes</span>
        </div>
    </li>
@endsection

@section('content')
    <!-- Barra de búsqueda y filtros -->
    <div class="mb-6 space-y-4">
        <!-- Búsqueda -->
        <form method="GET" action="{{ route('admin.registrations.index') }}" class="flex flex-wrap gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Buscar por producto, número, cliente..." 
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500">
            </div>
            
            <select name="company" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 px-3 py-2">
                <option value="">Todas las empresas</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            
            <select name="status" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 px-3 py-2">
                <option value="">Todos los estados</option>
                <option value="en_tramite" {{ request('status') === 'en_tramite' ? 'selected' : '' }}>En Trámite</option>
                <option value="aprobado" {{ request('status') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                <option value="rechazado" {{ request('status') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                <option value="pendiente" {{ request('status') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            </select>
            
            <select name="specialist" class="border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 px-3 py-2">
                <option value="">Todos los especialistas</option>
                @foreach($specialists as $specialist)
                    <option value="{{ $specialist->id }}" {{ request('specialist') == $specialist->id ? 'selected' : '' }}>
                        {{ $specialist->name }}
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                <i class="fas fa-search mr-2"></i> Buscar
            </button>
            
            @if(request()->anyFilled(['search', 'company', 'status', 'specialist']))
                <a href="{{ route('admin.registrations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-times mr-2"></i> Limpiar
                </a>
            @endif
        </form>
        
        <!-- Botón nuevo -->
        <div class="flex justify-end">
            <a href="{{ route('admin.registrations.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                <i class="fas fa-plus mr-2"></i> Nuevo Expediente
            </a>
        </div>
    </div>

    <!-- Tabla de expedientes -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Producto</th>
                        <th scope="col" class="px-6 py-3">Cliente</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3">N° Registro</th>
                        <th scope="col" class="px-6 py-3">Vencimiento</th>
                        <th scope="col" class="px-6 py-3">Especialista</th>
                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $registration)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $registration->product_name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $registration->company->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'en_tramite' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                        'aprobado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                        'rechazado' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                        'pendiente' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                    ];
                                    $colors = $statusColors[$registration->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium {{ $colors['bg'] }} {{ $colors['text'] }} rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $registration->registration_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($registration->expiration_date)
                                    @php
                                        $daysUntilExpiration = now()->diffInDays($registration->expiration_date, false);
                                    @endphp
                                    <div class="flex items-center">
                                        <span class="{{ $daysUntilExpiration < 30 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                            {{ $registration->expiration_date->format('d/m/Y') }}
                                        </span>
                                        @if($daysUntilExpiration < 30 && $daysUntilExpiration >= 0)
                                            <span class="ml-2 text-xs text-red-600">
                                                ({{ $daysUntilExpiration }} días)
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $registration->assignedSpecialist->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.registrations.show', $registration) }}" 
                                       class="text-blue-600 hover:text-blue-800" 
                                       title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.registrations.edit', $registration) }}" 
                                       class="text-teal-600 hover:text-teal-800" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.registrations.destroy', $registration) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('¿Estás seguro de eliminar este expediente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800" 
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                <p>No se encontraron expedientes</p>
                                @if(request()->anyFilled(['search', 'company', 'status', 'specialist']))
                                    <a href="{{ route('admin.registrations.index') }}" class="text-teal-600 hover:text-teal-700 mt-2 inline-block">
                                        Ver todos los expedientes
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($registrations->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
@endsection
