@extends('layouts.admin-flowbite')

@section('title', 'Directorio Empresas - RAMS')

@section('page-title', 'Directorio Empresas')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Empresas</span>
        </div>
    </li>
@endsection

@section('content')
    <!-- Barra de búsqueda y acciones -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex-1 w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.companies.index') }}" class="flex gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Buscar por nombre, NIT o email..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-search mr-2"></i> Buscar
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>
        <a href="{{ route('admin.companies.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
            <i class="fas fa-plus mr-2"></i> Nueva Empresa
        </a>
    </div>

    <!-- Tabla de clientes -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nombre</th>
                        <th scope="col" class="px-6 py-3">NIT/RUT</th>
                        <th scope="col" class="px-6 py-3">País</th>
                        <th scope="col" class="px-6 py-3">Contacto</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Clientes</th>
                        <th scope="col" class="px-6 py-3">Expedientes</th>
                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $company->name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $company->nit_rut }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $company->country ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $company->contact_person_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $company->contact_person_email ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-800" title="Usuarios rol Cliente vinculados">
                                    {{ (int) ($company->clients_assigned_count ?? 0) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ $company->processes_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    @php $contactUser = $company->contactRegisteredUser(); @endphp
                                    @if($contactUser)
                                        @php $loginEmail = $company->contact_person_email ?? $contactUser->email; @endphp
                                        <a href="{{ route('login') }}?email={{ urlencode($loginEmail) }}"
                                           class="text-green-600 hover:text-green-800"
                                           title="Acceder (ya registrado)">
                                            <i class="fas fa-sign-in-alt"></i>
                                        </a>
                                    @else
                                        <button type="button"
                                                onclick="openCompanyInviteModal({{ $company->id }})"
                                                class="text-amber-600 hover:text-amber-800"
                                                title="Enviar invitación para registro">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.companies.show', $company) }}" 
                                       class="text-blue-600 hover:text-blue-800" 
                                       title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.companies.edit', $company) }}" 
                                       class="text-teal-600 hover:text-teal-800" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.companies.destroy', $company) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('¿Estás seguro de eliminar esta empresa?');">
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
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                <p>No se encontraron empresas</p>
                                @if(request('search'))
                                    <a href="{{ route('admin.companies.index') }}" class="text-teal-600 hover:text-teal-700 mt-2 inline-block">
                                        Ver todas las empresas
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($companies->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $companies->links() }}
            </div>
        @endif
    </div>

    @include('admin.companies.partials.invite-modal')
@endsection
