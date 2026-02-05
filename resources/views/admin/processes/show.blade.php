@extends('layouts.admin-flowbite')

@section('title', 'Expediente - RAMS')

@section('page-title', 'Expediente ' . ($process->expediente_invima ?? 'N/A'))

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.processes.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Expedientes</a>
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
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Resumen del expediente -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Cliente</dt>
                        <dd class="font-medium text-gray-900">{{ $process->client->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tipo de servicio</dt>
                        <dd class="font-medium text-gray-900">{{ $process->quoteItem->serviceType->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Estado</dt>
                        <dd>
                            @php
                                $statusStyles = [
                                    'Recolección' => 'bg-gray-100 text-gray-800',
                                    'Radicado' => 'bg-blue-100 text-blue-800',
                                    'En Requerimiento' => 'bg-yellow-100 text-yellow-800',
                                    'Finalizado' => 'bg-green-100 text-green-800',
                                ];
                                $style = $statusStyles[$process->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $process->status }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Expediente INVIMA</dt>
                        <dd class="font-medium text-gray-900">{{ $process->expediente_invima ?? '-' }}</dd>
                    </div>
                    @if($process->quoteItem->quote ?? null)
                        <div>
                            <dt class="text-gray-500">Cotización</dt>
                            <dd class="font-medium text-gray-900">{{ $process->quoteItem->quote->consecutive ?? '-' }} ({{ $process->quoteItem->quote->date?->format('d/m/Y') }})</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Timeline vertical -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Línea de tiempo</h3>

                <div class="relative">
                    <!-- Línea vertical -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    <ul class="space-y-0">
                        {{-- 1. Cotización --}}
                        @if($process->quoteItem->quote ?? null)
                            @php $quote = $process->quoteItem->quote; @endphp
                            <li class="relative pl-12 pb-8">
                                <div class="absolute left-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Cotización</p>
                                    <p class="font-semibold text-gray-900">{{ $quote->consecutive }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $quote->date->format('d/m/Y') }} · {{ $quote->status }}</p>
                                </div>
                            </li>
                        @endif

                        {{-- 2. Checklist documental --}}
                        @if($process->checklistItems->isNotEmpty())
                            <li class="relative pl-12 pb-8">
                                <div class="absolute left-0 w-8 h-8 rounded-full bg-gray-500 flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Checklist documental</p>
                                    <ul class="mt-2 space-y-1 text-sm">
                                        @foreach($process->checklistItems as $item)
                                            @php
                                                $itemStyle = match($item->status) {
                                                    'Aprobado' => 'text-green-700',
                                                    'Traducción' => 'text-yellow-700',
                                                    'Recibido' => 'text-blue-700',
                                                    default => 'text-gray-700',
                                                };
                                            @endphp
                                            <li class="flex items-center gap-2 {{ $itemStyle }}">
                                                <i class="fas fa-{{ $item->status === 'Aprobado' ? 'check-circle' : 'circle' }} text-xs"></i>
                                                {{ $item->document_name }}
                                                <span class="text-xs">({{ $item->status }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- 3. Sometimientos y eventos (raíz primero, ordenados por fecha) --}}
                        @php
                            $rootSubmissions = $process->submissions->where('parent_id', null)->sortBy('fecha_radicacion');
                        @endphp
                        @foreach($rootSubmissions as $submission)
                            @include('admin.processes.partials.timeline-submission', ['submission' => $submission])
                        @endforeach

                        @if($rootSubmissions->isEmpty() && $process->checklistItems->isEmpty() && !($process->quoteItem->quote ?? null))
                            <li class="relative pl-12 pb-4 text-sm text-gray-500">
                                Sin eventos aún en la línea de tiempo.
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Acciones: Registrar Sometimiento, Auto, Resolución --}}
    @php
        $lastSubmission = $process->submissions->sortByDesc('id')->first();
        $rejectedSubmissions = $process->submissions->where('status', \App\Models\Submission::STATUS_RECHAZADO);
    @endphp
    <div class="mt-6 flex flex-wrap gap-3">
        <button type="button" onclick="document.getElementById('modal-submission').classList.remove('hidden')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-paper-plane mr-2"></i> Registrar Sometimiento
        </button>
        @if($lastSubmission)
            <button type="button" onclick="document.getElementById('modal-auto').classList.remove('hidden')"
                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                <i class="fas fa-gavel mr-2"></i> Registrar Auto
            </button>
            <button type="button" onclick="document.getElementById('modal-resolution').classList.remove('hidden')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-file-signature mr-2"></i> Registrar Resolución
            </button>
        @endif
    </div>

    @include('admin.processes.partials.modal-submission', ['process' => $process, 'rejectedSubmissions' => $rejectedSubmissions])
    @if($lastSubmission)
        @include('admin.processes.partials.modal-auto', ['submission' => $lastSubmission])
        @include('admin.processes.partials.modal-resolution', ['submission' => $lastSubmission])
    @endif
@endsection
