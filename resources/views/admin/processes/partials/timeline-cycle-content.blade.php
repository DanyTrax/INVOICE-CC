{{-- Contenido de un solo ciclo: card del sometimiento + eventos regulatorios (sin hijos). --}}
@php
    $sometidoAt = $submission->submission_date ? $submission->submission_date->format('d/M') : null;
    $radicadoAt = $submission->fecha_radicacion ? $submission->fecha_radicacion->format('d/M') : null;
@endphp
<div class="space-y-4">
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
        <p class="font-semibold text-gray-900">
            @if($sometidoAt)
                Sometimiento: {{ $sometidoAt }}
            @else
                Sometimiento sin fecha
            @endif
            @if($radicadoAt)
                → Radicado: {{ $radicadoAt }}
            @else
                → Pendiente de radicación
            @endif
            @if($submission->status === \App\Models\Submission::STATUS_PENDIENTE && !$submission->regulatoryEvents->isEmpty())
                → Esperando respuesta...
            @elseif($submission->status === \App\Models\Submission::STATUS_PENDIENTE)
                → Esperando respuesta INVIMA
            @endif
        </p>
        <p class="text-sm text-gray-600 mt-1">
            {{ $submission->submission_code ?? $submission->radicado_invima ?? 'Sin código' }}
            @if($submission->tracking_id) · Seguimiento: {{ $submission->tracking_id }} @endif
            @if($submission->quote)
                · <a href="{{ route('admin.quotes.show', $submission->quote) }}" class="text-teal-600 hover:underline">Cotización: {{ $submission->quote->consecutive ?? $submission->quote->id }}</a>
            @endif
            @if($submission->quoteItem)
                · Ítem: #{{ $submission->quoteItem->item_position }} ({{ $submission->quoteItem->serviceType->name ?? 'Servicio' }})
            @endif
            · <span class="px-2 py-0.5 rounded text-xs font-medium
                @if($submission->status === 'Aprobado') bg-green-100 text-green-800
                @elseif($submission->status === 'Rechazado') bg-red-100 text-red-800
                @elseif($submission->status === \App\Models\Submission::STATUS_RADICADO) bg-teal-100 text-teal-800
                @elseif($submission->status === 'En Requerimiento') bg-yellow-100 text-yellow-800
                @else bg-blue-100 text-blue-800
                @endif
            ">{{ $submission->status }}</span>
        </p>
        @if(isset($lastSubmission) && $lastSubmission && $submission->id === $lastSubmission->id && $submission->status === \App\Models\Submission::STATUS_PENDIENTE)
            <p class="mt-2 flex flex-wrap gap-2">
                <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('radicado')"
                        class="text-sm px-3 py-1.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-check mr-1"></i> Aprobar
                </button>
                <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('rechazo')"
                        class="text-sm px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-times mr-1"></i> Rechazar
                </button>
            </p>
            <p class="text-xs text-gray-500 mt-1">Aprobar: registre los datos del radicado; se creará una línea <strong>Radicado</strong> debajo con los botones REQUERIMIENTO AUTO y RESOLUCIÓN. Rechazar: indicar observación; puede crear más intentos en el mismo ciclo.</p>
        @endif
        <p class="mt-2 pt-2 border-t border-blue-100 flex flex-wrap gap-2 items-center">
            <button type="button" class="js-edit-submission text-sm px-2.5 py-1.5 text-teal-600 hover:bg-teal-50 rounded-lg border border-teal-200"
                    data-url="{{ route('admin.submissions.update', $submission) }}"
                    data-submission-date="{{ $submission->submission_date?->format('Y-m-d\TH:i') }}"
                    data-submission-code="{{ $submission->submission_code ?? '' }}"
                    data-radicado-invima="{{ $submission->radicado_invima ?? '' }}"
                    data-tracking-id="{{ $submission->tracking_id ?? '' }}"
                    data-fecha-radicacion="{{ $submission->fecha_radicacion?->format('Y-m-d') }}"
                    data-status="{{ $submission->status }}"
                    data-rejection-observation="{{ $submission->rejection_observation ?? '' }}">
                <i class="fas fa-edit"></i>
            </button>
            <form action="{{ route('admin.submissions.destroy', $submission) }}" method="post" class="inline-flex" onsubmit="return confirm('¿Eliminar este ciclo y toda la línea hacia abajo (eventos e intentos hijos)? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="whitespace-nowrap text-sm px-2.5 py-1.5 text-red-600 hover:bg-red-50 rounded-lg border border-red-200" title="Eliminar ciclo y línea hacia abajo">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>
        </p>
    </div>

    @php
        $hasRadicadoData = $submission->radicado_invima || $submission->fecha_radicacion || $submission->tracking_id;
    @endphp

    @if($hasRadicadoData)
        <div class="flex gap-3 items-start">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-teal-500 flex items-center justify-center text-white text-xs">
                <i class="fas fa-stamp"></i>
            </div>
            <div class="flex-1 border border-teal-200 bg-teal-50 rounded-lg p-3 text-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-medium text-teal-700 uppercase">Radicado</p>
                        <p class="font-medium text-gray-900">Radicado: {{ $submission->radicado_invima ?? '—' }}</p>
                        <p class="text-gray-600 mt-1">
                            @if($submission->fecha_radicacion) Fecha: {{ $submission->fecha_radicacion->format('d/m/Y') }} @endif
                            @if($submission->tracking_id) · Detalle: {{ $submission->tracking_id }} @endif
                        </p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1 whitespace-nowrap">
                        Guardado:
                        {{ optional($submission->updated_at ?? $submission->created_at)->format('d/m/Y H:i') }}
                    </p>
                </div>
                @if($submission->status === \App\Models\Submission::STATUS_RADICADO)
                    <p class="mt-2 flex flex-wrap gap-2">
                        <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('auto')"
                                class="text-xs px-3 py-1.5 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            <i class="fas fa-gavel mr-1"></i> AUTO
                        </button>
                        <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('aprobado')"
                                class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-file-signature mr-1"></i> RESOLUCIÓN
                        </button>
                        <form action="{{ route('admin.submissions.destroy-radicado', $submission) }}" method="post" class="inline-flex ml-auto"
                              onsubmit="return confirm('¿Quitar Radicado y eliminar AUTO / Resolución y ciclos posteriores? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                        <button type="submit" class="text-xs px-2.5 py-1.5 text-red-600 border border-red-200 rounded-lg hover:bg-red-50" title="Quitar Radicado">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </p>
                @endif
            </div>
        </div>
    @endif

    @foreach($submission->regulatoryEvents->sortBy('notification_date') as $event)
        @php
            $eventBg = match($event->event_type) {
                'AUTO' => 'bg-yellow-50 border-yellow-200',
                'RESOLUCION' => 'bg-green-50 border-green-200',
                'OFICIO' => 'bg-gray-50 border-gray-200',
                default => 'bg-gray-50 border-gray-200',
            };
            $eventDot = match($event->event_type) {
                'AUTO' => 'bg-yellow-500',
                'RESOLUCION' => 'bg-green-500',
                'OFICIO' => 'bg-gray-500',
                default => 'bg-gray-500',
            };
            $eventIcon = match($event->event_type) {
                'AUTO' => 'fa-gavel',
                'RESOLUCION' => 'fa-file-signature',
                'OFICIO' => 'fa-envelope',
                default => 'fa-file',
            };
        @endphp
        <div class="flex gap-3 items-start">
            <div class="flex-shrink-0 w-6 h-6 rounded-full {{ $eventDot }} flex items-center justify-center text-white text-xs">
                <i class="fas {{ $eventIcon }}"></i>
            </div>
            <div class="flex-1 border {{ $eventBg }} rounded-lg p-3 text-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">{{ $event->event_type }}</p>
                        <p class="font-medium text-gray-900">{{ $event->document_number ?? 'Sin número' }}</p>
                        <p class="text-gray-600 mt-1">
                            @if($event->notification_date) Notificación: {{ $event->notification_date->format('d/m/Y') }} @endif
                            @if($event->due_date) · Vence: {{ $event->due_date->format('d/m/Y') }} @endif
                            @if($event->resolution_key) · Detalle: {{ $event->resolution_key }} @endif
                        </p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1 whitespace-nowrap">
                        Guardado:
                        {{ optional($event->updated_at ?? $event->created_at)->format('d/m/Y H:i') }}
                    </p>
                </div>
                <p class="mt-2 flex flex-wrap gap-2">
                    <button type="button" class="js-edit-event text-xs px-2.5 py-1 text-teal-600 hover:bg-teal-50 rounded border border-teal-200"
                            data-url="{{ route('admin.regulatory-events.update', $event) }}"
                            data-event-type="{{ $event->event_type }}"
                            data-document-number="{{ $event->document_number ?? '' }}"
                            data-notification-date="{{ $event->notification_date?->format('Y-m-d') }}"
                            data-event-date="{{ $event->event_date?->format('Y-m-d') }}"
                            data-resolution-key="{{ $event->resolution_key ?? '' }}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.regulatory-events.destroy', $event) }}" method="post" class="inline-flex"
                          onsubmit="return confirm('¿Eliminar este evento y devolver el proceso a su estado anterior si aplica? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs px-2.5 py-1 text-red-600 border border-red-200 rounded-lg hover:bg-red-50" title="Eliminar evento">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </p>
            </div>
        </div>
    @endforeach
</div>
