@php
    $isChild = $submission->parent_id !== null;
    $attemptNum = $attemptNum ?? ($isChild ? 2 : 1);
    $sometidoAt = $submission->submission_date ? $submission->submission_date->format('d/M') : null;
    $radicadoAt = $submission->fecha_radicacion ? $submission->fecha_radicacion->format('d/M') : null;
@endphp
<li class="relative pl-12 pb-6">
    <div class="absolute left-0 w-8 h-8 rounded-full {{ $submission->status === \App\Models\Submission::STATUS_RECHAZADO ? 'bg-red-500' : 'bg-blue-500' }} flex items-center justify-center text-white text-xs">
        <i class="fas fa-paper-plane"></i>
    </div>
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-2">
        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">
            Intento {{ $attemptNum }}
            @if($submission->status === \App\Models\Submission::STATUS_RECHAZADO)
                (Rechazado)
            @else
                (En curso)
            @endif
        </p>
        <div class="flex items-start justify-between gap-2">
            <p class="font-semibold text-gray-900">
                Sometimiento:
                @if($sometidoAt)
                    {{ $sometidoAt }}
                @else
                    sin fecha
                @endif
                @if($radicadoAt)
                    → Radicado: {{ $radicadoAt }}
                @else
                    → Pendiente de radicación
                @endif
            </p>
            <p class="text-[11px] text-gray-500 whitespace-nowrap mt-1">
                Guardado:
                {{ optional($submission->created_at)->format('d/m/Y H:i') }}
            </p>
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
                <i class="fas fa-stamp mr-1"></i> Fase INVIMA: <strong>Radicado</strong> (debajo verá la tarjeta Radicado y podrá registrar AUTO o Resolución).
            </p>
        @elseif($submission->status === \App\Models\Submission::STATUS_EN_REQUERIMIENTO)
            <p class="text-xs text-amber-800 mt-1">
                <i class="fas fa-gavel mr-1"></i> Fase INVIMA: <strong>Requerimiento (AUTO)</strong> — el sometimiento sigue aquí; el detalle del AUTO está en la tarjeta <strong>AUTO</strong> debajo.
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
                @if($submission->status === \App\Models\Submission::STATUS_PENDIENTE && isset($lastSubmission) && $lastSubmission && $submission->id === $lastSubmission->id)
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
                <button type="button" class="js-edit-submission flex-shrink-0 text-sm px-2.5 py-1.5 text-teal-600 hover:bg-teal-50 rounded-lg border border-teal-200"
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
                <form action="{{ route('admin.submissions.destroy', $submission) }}" method="post" class="inline-flex flex-shrink-0" onsubmit="return confirm('¿Eliminar este intento y toda la línea de tiempo hacia abajo (eventos e intentos hijos)? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="whitespace-nowrap text-sm px-2.5 py-1.5 text-red-600 hover:bg-red-50 rounded-lg border border-red-200" title="Eliminar intento y línea hacia abajo">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
        </div>
        @if($submission->status === \App\Models\Submission::STATUS_PENDIENTE && isset($lastSubmission) && $lastSubmission && $submission->id === $lastSubmission->id)
            <p class="text-xs text-gray-500 mt-1">
                Aprobar: registre los datos del radicado; se creará una línea <strong>Radicado</strong> debajo con los botones AUTO y RESOLUCIÓN.
                Rechazar: indique la observación; podrá crear más intentos en el mismo ciclo.
            </p>
        @endif
    </div>

    @if($submission->status === \App\Models\Submission::STATUS_RADICADO)
        <div class="flex gap-3 items-start mb-2" style="margin-left: 0;">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-teal-500 flex items-center justify-center text-white text-xs" style="margin-left: 4px;">
                <i class="fas fa-stamp"></i>
            </div>
            <div class="flex-1 border border-teal-200 bg-teal-50 rounded-lg p-3 text-sm">
                <p class="text-xs font-medium text-teal-700 uppercase">Radicado</p>
                <p class="font-medium text-gray-900">Radicado: {{ $submission->radicado_invima ?? '—' }}</p>
                <p class="text-gray-600 mt-1">
                    @if($submission->fecha_radicacion) Fecha: {{ $submission->fecha_radicacion->format('d/m/Y') }} @endif
                    @if($submission->tracking_id) · Llave: {{ $submission->tracking_id }} @endif
                </p>
                <p class="mt-2 flex flex-wrap gap-2">
                    <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('auto')"
                            class="text-xs px-3 py-1.5 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                        <i class="fas fa-gavel mr-1"></i> REQUERIMIENTO AUTO
                    </button>
                    <button type="button" onclick="typeof openResponseModal === 'function' && openResponseModal('aprobado')"
                            class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-file-signature mr-1"></i> RESOLUCIÓN
                    </button>
                </p>
            </div>
        </div>
    @endif

    <ul class="space-y-0 mt-0">
    @foreach($submission->regulatoryEvents->sortBy('notification_date') as $event)
        <li class="relative pl-12 pb-4">
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
            <div class="absolute left-0 w-6 h-6 rounded-full {{ $eventDot }} flex items-center justify-center text-white text-xs" style="margin-left: 4px;">
                <i class="fas {{ $eventIcon }}"></i>
            </div>
            <div class="border {{ $eventBg }} rounded-lg p-3 text-sm">
                <p class="text-xs font-medium text-gray-600 uppercase">{{ $event->event_type }}</p>
                <p class="font-medium text-gray-900">{{ $event->document_number ?? 'Sin número' }}</p>
                <p class="text-gray-600 mt-1">
                    @if($event->notification_date) Fecha: {{ $event->notification_date->format('d/m/Y') }} @endif
                    @if($event->due_date) · Vence: {{ $event->due_date->format('d/m/Y') }} @endif
                    @if($event->resolution_key) · Llave: {{ $event->resolution_key }} @endif
                </p>
                <p class="mt-2">
                    <button type="button" class="js-edit-event text-xs px-2 py-1 text-teal-600 hover:bg-teal-50 rounded border border-teal-200"
                            data-url="{{ route('admin.regulatory-events.update', $event) }}"
                            data-event-type="{{ $event->event_type }}"
                            data-document-number="{{ $event->document_number ?? '' }}"
                            data-notification-date="{{ $event->notification_date?->format('Y-m-d') }}"
                            data-event-date="{{ $event->event_date?->format('Y-m-d') }}"
                            data-due-date="{{ $event->due_date?->format('Y-m-d') }}"
                            data-resolution-key="{{ $event->resolution_key ?? '' }}">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </button>
                </p>
            </div>
        </li>
    @endforeach

    @foreach($submission->children->sortBy('fecha_radicacion') as $child)
        @include('admin.processes.partials.timeline-submission', ['submission' => $child, 'attemptNum' => $attemptNum + $loop->iteration, 'lastSubmission' => $lastSubmission ?? null])
    @endforeach
    </ul>
</li>
