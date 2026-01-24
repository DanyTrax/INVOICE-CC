@extends('layouts.admin-flowbite')

@section('title', 'Ver Empresa - RAMS')

@section('page-title', $company->name)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.companies.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Empresas</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Ver</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Información de la Empresa</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.companies.edit', $company) }}" 
                           class="px-3 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                        <a href="{{ route('admin.registrations.index', ['company' => $company->id]) }}" 
                           class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            <i class="fas fa-clipboard-list mr-1"></i> Ver Expedientes
                        </a>
                    </div>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre de la Empresa</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">NIT/RUT</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->nit_rut }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->address ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Contacto</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->contact_person_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($company->contact_person_email)
                                <a href="mailto:{{ $company->contact_person_email }}" class="text-teal-600 hover:text-teal-700">
                                    {{ $company->contact_person_email }}
                                </a>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    @if($company->drive_folder_id)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Carpeta Google Drive</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <code class="px-2 py-1 bg-gray-100 rounded">{{ $company->drive_folder_id }}</code>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Estadísticas -->
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Total de Expedientes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $company->registrations_count }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expedientes del Cliente -->
    @if($company->registrations->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Expedientes</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3">Producto</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Fecha Vencimiento</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($company->registrations as $registration)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $registration->product_name }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($registration->status === 'en_tramite') bg-blue-100 text-blue-800
                                        @elseif($registration->status === 'aprobado') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $registration->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($registration->expiration_date)
                                        {{ $registration->expiration_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.registrations.edit', $registration) }}" 
                                       class="text-teal-600 hover:text-teal-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
