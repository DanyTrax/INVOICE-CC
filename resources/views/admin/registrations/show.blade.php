@extends('layouts.admin-flowbite')

@section('title', 'Ver Expediente - RAMS')

@section('page-title', $registration->product_name)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.registrations.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Expedientes</a>
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
        <div class="lg:col-span-2 space-y-6">
            <!-- Información Básica -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Información del Expediente</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.registrations.edit', $registration) }}" 
                           class="px-3 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                    </div>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Producto</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $registration->product_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('admin.companies.show', $registration->company) }}" class="text-teal-600 hover:text-teal-700">
                                {{ $registration->company->name ?? '-' }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
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
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Especialista Asignado</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->assignedSpecialist->name ?? 'Sin asignar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tipo de Trámite</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->transaction_type ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">N° Registro</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->registration_number ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Cronograma -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cronograma</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha Solicitud Cliente</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->client_request_date?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha Radicación</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->radication_date?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha Presentación</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->submission_date?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha Vencimiento</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($registration->expiration_date)
                                <span class="{{ now()->diffInDays($registration->expiration_date, false) < 30 ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $registration->expiration_date->format('d/m/Y') }}
                                </span>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha Límite Respuesta</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->response_limit_date?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha Auto INVIMA</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->invima_auto_date?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Observaciones -->
            @if($registration->client_requirement || $registration->invima_requirement || $registration->pending_docs || $registration->observations)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalles y Observaciones</h3>
                    <div class="space-y-4">
                        @if($registration->client_requirement)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Requerimiento del Cliente</dt>
                                <dd class="text-sm text-gray-900 whitespace-pre-wrap">{{ $registration->client_requirement }}</dd>
                            </div>
                        @endif
                        @if($registration->invima_requirement)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Requerimiento INVIMA</dt>
                                <dd class="text-sm text-gray-900 whitespace-pre-wrap">{{ $registration->invima_requirement }}</dd>
                            </div>
                        @endif
                        @if($registration->pending_docs)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Documentos Pendientes</dt>
                                <dd class="text-sm text-gray-900 whitespace-pre-wrap">{{ $registration->pending_docs }}</dd>
                            </div>
                        @endif
                        @if($registration->observations)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Observaciones</dt>
                                <dd class="text-sm text-gray-900 whitespace-pre-wrap">{{ $registration->observations }}</dd>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Información Adicional -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Adicional</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">N° Cotización</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->quotation_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">N° Radicación</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->radication_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Código Clave</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->key_code ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">N° Resolución</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->resolution_number ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Google Drive -->
            @if($registration->drive_folder_url)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Google Drive</h3>
                    <a href="{{ $registration->drive_folder_url }}" 
                       target="_blank"
                       class="inline-flex items-center text-teal-600 hover:text-teal-700">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Abrir carpeta en Drive
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
