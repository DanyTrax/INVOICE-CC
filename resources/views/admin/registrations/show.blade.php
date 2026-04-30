@extends('layouts.admin-flowbite')

@section('title', 'Ver solicitud - RAMS')

@section('page-title', $registration->product_name)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.registrations.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Solicitudes</a>
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
                    <h2 class="text-xl font-bold text-gray-900">Información de la solicitud</h2>
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
                            @if($registration->company)
                                <a href="{{ route('admin.companies.show', $registration->company) }}" class="text-teal-600 hover:text-teal-700">
                                    {{ $registration->company->name }}
                                </a>
                            @else
                                <span class="text-gray-500">Sin asignar</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            @php
                                $statusColors = [
                                    'tramite' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'En Trámite'],
                                    'vigente' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Vigente'],
                                    'requerimiento' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Requerimiento'],
                                    'vencido' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Vencido'],
                                ];
                                $statusInfo = $statusColors[$registration->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($registration->status)];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }} rounded-full">
                                {{ $statusInfo['label'] }}
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

            <!-- Documentos -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Documentos</h3>
                @if($registration->documents && $registration->documents->count() > 0)
                    <ul class="space-y-3">
                        @foreach($registration->documents as $document)
                            @php
                                // Verificar si el documento está en Google Drive (prioridad) o localmente
                                $hasDrive = !empty($document->drive_id);
                                $hasLocal = !$hasDrive && $document->file_path && 
                                            !str_starts_with($document->file_path, 'temp/') &&
                                            str_starts_with($document->file_path, 'registration-documents/');
                            @endphp
                            <li class="flex items-center justify-between gap-3 py-3 px-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 rounded-lg">
                                <div class="flex items-center min-w-0 flex-1">
                                    <i class="fas fa-file text-teal-500 mr-3 flex-shrink-0 text-lg"></i>
                                    <span class="text-sm font-medium text-gray-900 truncate" title="{{ $document->file_name }}">{{ $document->file_name }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($hasDrive || $hasLocal)
                                        <a href="{{ route('admin.registrations.documents.view', [$registration, $document]) }}" 
                                           target="_blank"
                                           class="p-2.5 text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors shadow-sm"
                                           title="Ver documento">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.registrations.documents.download', [$registration, $document]) }}" 
                                           class="p-2.5 text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors shadow-sm"
                                           title="Descargar documento">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                    <button type="button" 
                                            onclick="confirmDeleteDocument({{ $document->id }}, '{{ addslashes($document->file_name) }}')"
                                            class="p-2.5 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm"
                                            title="Eliminar documento">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-document-form-{{ $document->id }}" 
                                          action="{{ route('admin.registrations.documents.destroy', [$registration, $document]) }}" 
                                          method="POST" 
                                          class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($registration->drive_folder_url)
                        <p class="mt-4 pt-3 border-t border-gray-100">
                            <a href="{{ $registration->drive_folder_url }}" 
                               target="_blank"
                               class="text-sm text-teal-600 hover:text-teal-700">
                                <i class="fas fa-folder-open mr-1"></i> Abrir carpeta en Drive (legacy)
                            </a>
                        </p>
                    @endif
                @else
                    <p class="text-sm text-gray-500">No hay documentos cargados en esta solicitud.</p>
                    @if($registration->drive_folder_url)
                        <a href="{{ $registration->drive_folder_url }}" 
                           target="_blank"
                           class="inline-flex items-center text-sm text-teal-600 hover:text-teal-700 mt-2">
                            <i class="fas fa-folder-open mr-1"></i> Abrir carpeta en Drive (legacy)
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteDocument(documentId, fileName) {
            Swal.fire({
                title: '¿Eliminar documento?',
                html: `¿Estás seguro de eliminar el documento <strong>${fileName}</strong>?<br><br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: false,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                buttonsStyling: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-document-form-' + documentId).submit();
                }
            });
        }
    </script>
@endsection
