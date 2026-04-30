@extends('layouts.portal')

@section('title', 'Solicitud ' . ($registration->registration_number ?: $registration->product_name))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('portal.registrations.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Solicitud: {{ $registration->registration_number ?? $registration->product_name }}</h3>
                @php
                    $statusBadge = match($registration->status) {
                        'vigente' => 'text-green-600',
                        'tramite' => 'text-yellow-600',
                        'requerimiento' => 'text-amber-600',
                        'vencido' => 'text-red-600',
                        default => 'text-gray-600',
                    };
                @endphp
                <p class="text-xs font-bold {{ $statusBadge }}">● Estado: {{ ucfirst($registration->status) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Información del Registro</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nombre del Producto</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm text-gray-700">{{ $registration->product_name }}</div>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Número de Registro</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm text-gray-700">{{ $registration->registration_number ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Fecha de Vencimiento</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm {{ $registration->expiration_date ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                        {{ $registration->expiration_date?->format('d F, Y') ?? '-' }}
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tipo de Trámite</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm text-gray-700">{{ $registration->transaction_type ?? '-' }}</div>
                </div>
            </div>

            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2 mt-6">Cronograma</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Fecha Radicación</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm text-gray-700">{{ $registration->radication_date?->format('d M Y') ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">No. Radicado</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm text-gray-700">{{ $registration->radication_number ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Resolución</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm text-gray-700">{{ $registration->resolution_number ?? '-' }}</div>
                </div>
            </div>

            @if($registration->observations || $registration->client_requirement || $registration->invima_requirement || $registration->pending_docs)
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2 mt-6">Observaciones y Requerimientos</h4>
            <div class="space-y-2 text-sm text-gray-700">
                @if($registration->observations)<p><span class="font-medium text-gray-500">Observaciones:</span> {{ $registration->observations }}</p>@endif
                @if($registration->client_requirement)<p><span class="font-medium text-gray-500">Requerimiento del Cliente:</span> {{ $registration->client_requirement }}</p>@endif
                @if($registration->invima_requirement)<p><span class="font-medium text-gray-500">Requerimiento INVIMA:</span> {{ $registration->invima_requirement }}</p>@endif
                @if($registration->pending_docs)<p><span class="font-medium text-gray-500">Documentos Pendientes:</span> {{ $registration->pending_docs }}</p>@endif
            </div>
            @endif
        </div>

        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Documentos Disponibles</h4>
                @if($registration->documents->isEmpty())
                    <p class="text-sm text-gray-500 py-4">No hay documentos disponibles.</p>
                @else
                    <div class="space-y-3">
                        @foreach($registration->documents as $doc)
                        <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 group transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="text-red-500 flex-shrink-0"><i class="fas fa-file-pdf text-xl"></i></div>
                                <div class="overflow-hidden">
                                    <p class="text-sm font-bold text-gray-700 truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $doc->file_type ?? 'Documento' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                <a href="{{ route('portal.documents.view', [$registration, $doc]) }}" target="_blank"
                                   class="p-2.5 text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors shadow-sm" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('portal.documents.download', [$registration, $doc]) }}"
                                   class="p-2.5 text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors shadow-sm" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
