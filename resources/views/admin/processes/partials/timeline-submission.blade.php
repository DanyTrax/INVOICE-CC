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
        <p class="font-semibold text-gray-900">
            @if($sometidoAt)
                Sometido: {{ $sometidoAt }}
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
            · <span class="px-2 py-0.5 rounded text-xs font-medium
                @if($submission->status === 'Aprobado') bg-green-100 text-green-800
                @elseif($submission->status === 'Rechazado') bg-red-100 text-red-800
                @elseif($submission->status === 'En Requerimiento') bg-yellow-100 text-yellow-800
                @else bg-blue-100 text-blue-800
                @endif
            ">{{ $submission->status }}</span>
        </p>
        @if($submission->status === \App\Models\Submission::STATUS_PENDIENTE && isset($lastSubmission) && $lastSubmission && $submission->id === $lastSubmission->id)
            <p class="mt-2 flex flex-wrap gap-2">
                <button type="button" onclick="document.getElementById('modal-response-invima').classList.remove('hidden'); var t=document.getElementById('tab-aprobado'); if(t) t.click();"
                        class="text-sm px-3 py-1.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-check mr-1"></i> Aprobar
                </button>
                <button type="button" onclick="document.getElementById('modal-response-invima').classList.remove('hidden'); var t=document.getElementById('tab-rechazo'); if(t) t.click();"
                        class="text-sm px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-times mr-1"></i> Rechazar
                </button>
            </p>
            <p class="text-xs text-gray-500 mt-1">Aprobar: Radicado y Llave / Resolución o Auto. Rechazar: indicar observación y podrá crear nuevo intento.</p>
        @endif
        <p class="mt-2 pt-2 border-t border-blue-100 flex flex-wrap gap-2 items-center">
            <form action="{{ route('admin.submissions.destroy', $submission) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar este intento y toda la línea de tiempo hacia abajo (eventos e intentos hijos)? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg border border-red-200">
                    <i class="fas fa-trash-alt mr-1"></i> Eliminar intento y línea hacia abajo
                </button>
            </form>
        </p>
    </div>

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
                    @if($event->notification_date) Notificación: {{ $event->notification_date->format('d/m/Y') }} @endif
                    @if($event->due_date) · Vence: {{ $event->due_date->format('d/m/Y') }} @endif
                    @if($event->resolution_key) · Llave: {{ $event->resolution_key }} @endif
                </p>
            </div>
        </li>
    @endforeach

    @foreach($submission->children->sortBy('fecha_radicacion') as $child)
        @include('admin.processes.partials.timeline-submission', ['submission' => $child, 'attemptNum' => $attemptNum + $loop->iteration, 'lastSubmission' => $lastSubmission ?? null])
    @endforeach
    </ul>
</li>
