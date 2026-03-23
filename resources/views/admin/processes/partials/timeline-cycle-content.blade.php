{{-- Contenido de un solo ciclo: card del sometimiento + eventos regulatorios (sin hijos). --}}
@php
    $cycleNum = $cycleNum ?? 1;
    /** Ciclos 2+ = respuesta al requerimiento AUTO (subsanación): mismo trámite AUTO, pasos Sometimiento/Radicado. */
    $isAutoLinkedCycle = $cycleNum >= 2;
    $sometidoAt = $submission->submission_date ? $submission->submission_date->format('d/M') : null;
    $radicadoAt = $submission->fecha_radicacion ? $submission->fecha_radicacion->format('d/M') : null;
@endphp
<div class="space-y-4">
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
        <div class="flex items-start justify-between gap-2">
            <p class="font-semibold text-gray-900">
                @if($isAutoLinkedCycle)
                    <span class="text-amber-900">AUTO ·</span>
                @endif
                Sometimiento:
                @if($sometidoAt)
                    {{ $sometidoAt }}
                @else
                    sin fecha
                @endif
                @if($radicadoAt)
                    → @if($isAutoLinkedCycle)<span class="text-amber-900">AUTO ·</span> @endif Radicado: {{ $radicadoAt }}
                @else
                    → @if($isAutoLinkedCycle)<span class="text-amber-900">AUTO ·</span> @endif Pendiente de radicación
                @endif
            </p>
            <div class="text-right flex-shrink-0">
                <p class="text-[11px] text-gray-500 whitespace-nowrap mt-1">
                    Guardado:
                    {{ optional($submission->created_at)->format('d/m/Y H:i') }}
                </p>
                <p class="text-[11px] text-gray-500 mt-0.5">
                    Especialista: {{ $submission->createdByUser?->name ?? '—' }}
                </p>
            </div>
        </div>

        <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium text-gray-800">Código de sometimiento:</span>
            <span class="ml-1">{{ $submission->submission_code ?? '—' }}</span>
        </p>

        <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium text-gray-800">Fecha de sometimiento:</span>
            <span class="ml-1">
                @if($submission->submission_date)
                    {{ $submission->submission_date->format('d/m/Y H:i') }}
                @else
                    —
                @endif
            </span>
            · <span class="font-medium text-gray-800">Estado:</span>
            @php
                $statusPillClass = match($submission->status) {
                    \App\Models\Submission::STATUS_RECHAZADO => 'bg-red-100 text-red-700',
                    \App\Models\Submission::STATUS_RADICADO => 'bg-green-100 text-green-800',
                    \App\Models\Submission::STATUS_EN_REQUERIMIENTO => 'bg-amber-100 text-amber-900',
                    \App\Models\Submission::STATUS_APROBADO => 'bg-emerald-100 text-emerald-900',
                    \App\Models\Submission::STATUS_PENDIENTE => 'bg-gray-100 text-gray-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp
            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $statusPillClass }}">
                {{ $submission->status }}
            </span>
        </p>
        @if($submission->status === \App\Models\Submission::STATUS_RADICADO)
            <p class="text-xs text-teal-700 mt-1">
                <i class="fas fa-stamp mr-1"></i>
                @if($isAutoLinkedCycle)
                    Trámite <strong>AUTO</strong>: fase <strong>AUTO · Radicado</strong> (subsanación tras requerimiento).
                @else
                    Fase INVIMA: <strong>Radicado</strong>.
                @endif
            </p>
        @elseif($submission->status === \App\Models\Submission::STATUS_EN_REQUERIMIENTO)
            <p class="text-xs text-amber-800 mt-1">
                <i class="fas fa-gavel mr-1"></i> Fase INVIMA: <strong>Requerimiento (AUTO)</strong> — detalle en la tarjeta AUTO debajo.
            </p>
        @endif

        @if($submission->status === \App\Models\Submission::STATUS_RECHAZADO && filled($submission->rejection_observation))
            <p class="text-sm text-gray-700 mt-1">
                <span class="font-medium text-gray-800">Observación (motivo del rechazo):</span>
                <span class="ml-1">{{ $submission->rejection_observation }}</span>
            </p>
        @endif

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                @if(isset($lastSubmission) && $lastSubmission && $submission->id === $lastSubmission->id && $submission->status === \App\Models\Submission::STATUS_PENDIENTE)
                    <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('radicado')"
                            class="text-sm px-3 py-1.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                        Aprobar
                    </button>
                    <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('rechazo')"
                            class="text-sm px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Rechazar
                    </button>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="js-edit-submission text-sm px-2.5 py-1.5 text-teal-600 hover:bg-teal-50 rounded-lg border border-teal-200"
                        data-url="{{ route('admin.submissions.update', $submission) }}"
                        data-submission-date="{{ $submission->submission_date?->format('Y-m-d\TH:i') }}"
                        data-submission-code="{{ $submission->submission_code ?? '' }}"
                        data-radicado-invima="{{ $submission->radicado_invima ?? '' }}"
                        data-tracking-id="{{ $submission->tracking_id ?? '' }}"
                        data-fecha-radicacion="{{ $submission->fecha_radicacion?->format('Y-m-d') }}"
                        data-status="{{ $submission->status }}"
                        data-rejection-observation="{{ $submission->rejection_observation ?? '' }}"
                        title="Editar sometimiento">
                    <i class="fas fa-edit"></i>
                </button>
                <form action="{{ route('admin.submissions.destroy', $submission) }}" method="post" class="inline-flex" onsubmit="return confirm('¿Eliminar este ciclo y toda la línea hacia abajo (eventos e intentos hijos)? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="whitespace-nowrap text-sm px-2.5 py-1.5 text-red-600 hover:bg-red-50 rounded-lg border border-red-200" title="Eliminar ciclo y línea hacia abajo">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
        </div>

        @if(isset($lastSubmission) && $lastSubmission && $submission->id === $lastSubmission->id && $submission->status === \App\Models\Submission::STATUS_PENDIENTE)
            <p class="text-xs text-gray-500 mt-1">
                @if($isAutoLinkedCycle)
                    <strong>AUTO · Sometimiento:</strong> al aprobar, registre el radicado de esta subsanación; debajo quedará <strong>AUTO · Radicado</strong> con la opción <strong>Resolución</strong> para cerrar el expediente.
                @else
                    Aprobar: registre los datos del radicado; se creará una línea <strong>Radicado</strong> debajo con los botones AUTO y RESOLUCIÓN.
                @endif
                Rechazar: indique la observación; podrá crear más intentos en el mismo ciclo.
            </p>
        @endif
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
                        <p class="text-xs font-medium text-teal-700 uppercase">@if($isAutoLinkedCycle) AUTO · @endif Radicado</p>
                        <p class="text-sm text-gray-700 mt-1">
                            <span class="font-medium text-gray-800">Número de radicado:</span>
                            <span class="ml-1">{{ $submission->radicado_invima ?? '—' }}</span>
                        </p>
                        <p class="text-sm text-gray-700 mt-1">
                            <span class="font-medium text-gray-800">Fecha de radicado:</span>
                            <span class="ml-1">{{ $submission->fecha_radicacion ? $submission->fecha_radicacion->format('d/m/Y') : '—' }}</span>
                        </p>
                        <p class="text-sm text-gray-700 mt-1">
                            <span class="font-medium text-gray-800">Llave / campo de registro:</span>
                            <span class="ml-1 break-words">{{ $submission->tracking_id ?? '—' }}</span>
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-[11px] text-gray-500 mt-1 whitespace-nowrap">
                            Guardado:
                            {{ optional($submission->updated_at ?? $submission->created_at)->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Especialista: {{ $submission->radicadoSavedByUser?->name ?? '—' }}
                        </p>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-2">
                        @if($submission->status === \App\Models\Submission::STATUS_RADICADO)
                            @if(!$isAutoLinkedCycle)
                                <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('auto')"
                                        class="text-xs px-3 py-1.5 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                    <i class="fas fa-gavel mr-1"></i> AUTO
                                </button>
                            @endif
                            <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('aprobado')"
                                    class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-file-signature mr-1"></i> RESOLUCIÓN
                            </button>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                onclick="typeof openEditRadicado === 'function' && openEditRadicado({{ $submission->id }}, '{{ addslashes($submission->radicado_invima ?? '') }}', '{{ $submission->fecha_radicacion?->format('Y-m-d') }}', '{{ addslashes($submission->tracking_id ?? '') }}')"
                                class="text-xs px-2.5 py-1.5 text-teal-600 border border-teal-200 rounded-lg hover:bg-teal-50"
                                title="Editar Radicado">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.submissions.destroy-radicado', $submission) }}" method="post" class="inline-flex"
                              onsubmit="return confirm('¿Quitar Radicado y eliminar AUTO / Resolución y ciclos posteriores? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs px-2.5 py-1.5 text-red-600 border border-red-200 rounded-lg hover:bg-red-50" title="Quitar Radicado">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
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
                        <p class="text-xs font-medium text-gray-600 uppercase">
                            @if($event->event_type === \App\Models\RegulatoryEvent::EVENT_TYPE_AUTO)
                                AUTO
                            @elseif($event->event_type === \App\Models\RegulatoryEvent::EVENT_TYPE_RESOLUCION)
                                RESOLUCIÓN
                            @else
                                {{ $event->event_type }}
                            @endif
                        </p>
                        @if($event->event_type === \App\Models\RegulatoryEvent::EVENT_TYPE_AUTO)
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium text-gray-800">Número de AUTO:</span>
                                <span class="ml-1">{{ $event->document_number ?? '—' }}</span>
                            </p>
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium text-gray-800">Fecha de AUTO:</span>
                                <span class="ml-1">{{ $event->notification_date ? $event->notification_date->format('d/m/Y') : '—' }}</span>
                            </p>
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium text-gray-800">Fecha de vencimiento:</span>
                                <span class="ml-1">{{ $event->due_date ? $event->due_date->format('d/m/Y') : '—' }}</span>
                            </p>
                        @elseif($event->event_type === \App\Models\RegulatoryEvent::EVENT_TYPE_RESOLUCION)
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium text-gray-800">Número de Resolución:</span>
                                <span class="ml-1">{{ $event->document_number ?? '—' }}</span>
                            </p>
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium text-gray-800">Fecha de Resolución:</span>
                                <span class="ml-1">{{ $event->event_date ? $event->event_date->format('d/m/Y') : '—' }}</span>
                            </p>
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium text-gray-800">Detalle / observación:</span>
                                <span class="ml-1 break-words">{{ $event->resolution_key ?? '—' }}</span>
                            </p>
                        @else
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium text-gray-800">Número:</span>
                                <span class="ml-1">{{ $event->document_number ?? '—' }}</span>
                            </p>
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium text-gray-800">Fecha:</span>
                                <span class="ml-1">{{ $event->notification_date ? $event->notification_date->format('d/m/Y') : '—' }}</span>
                            </p>
                            @if($event->due_date)
                                <p class="text-sm text-gray-700 mt-1">
                                    <span class="font-medium text-gray-800">Vence:</span>
                                    <span class="ml-1">{{ $event->due_date->format('d/m/Y') }}</span>
                                </p>
                            @endif
                        @endif
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-[11px] text-gray-500 mt-1 whitespace-nowrap">
                            Guardado:
                            {{ optional($event->updated_at ?? $event->created_at)->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Especialista: {{ $event->savedByUser?->name ?? '—' }}
                        </p>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" class="js-edit-event text-xs px-2.5 py-1 text-teal-600 hover:bg-teal-50 rounded border border-teal-200"
                            data-url="{{ route('admin.regulatory-events.update', $event) }}"
                            data-event-type="{{ $event->event_type }}"
                            data-document-number="{{ $event->document_number ?? '' }}"
                            data-notification-date="{{ $event->notification_date?->format('Y-m-d') }}"
                            data-event-date="{{ $event->event_date?->format('Y-m-d') }}"
                            data-due-date="{{ $event->due_date?->format('Y-m-d') }}"
                            data-resolution-key="{{ $event->resolution_key ?? '' }}"
                            title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.regulatory-events.destroy', $event) }}" method="post" class="inline-flex"
                          onsubmit="return confirm('¿Eliminar este evento y devolver el proceso a su estado anterior si aplica? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs px-2.5 py-1 text-red-600 border border-red-200 rounded-lg hover:bg-red-50" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
