@php
    $isChild = $submission->parent_id !== null;
@endphp
<li class="relative pl-12 pb-6">
    {{-- Radicación / Sometimiento --}}
    <div class="absolute left-0 w-8 h-8 rounded-full {{ $submission->status === 'Rechazado/Negado' ? 'bg-red-500' : 'bg-blue-500' }} flex items-center justify-center text-white text-xs">
        <i class="fas fa-paper-plane"></i>
    </div>
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-2">
        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">
            {{ $submission->submission_type }} · {{ $submission->filing_number ?? 'Sin radicado' }}
        </p>
        <p class="font-semibold text-gray-900">
            @if($submission->filing_date)
                Radicado {{ $submission->filing_date->format('d/m/Y') }}
            @else
                Sometimiento (pendiente radicación)
            @endif
        </p>
        <p class="text-sm text-gray-600 mt-1">
            @if($submission->tracking_id) Seguimiento: {{ $submission->tracking_id }} · @endif
            <span class="px-2 py-0.5 rounded text-xs font-medium
                @if($submission->status === 'Aprobado') bg-green-100 text-green-800
                @elseif($submission->status === 'Rechazado/Negado') bg-red-100 text-red-800
                @elseif($submission->status === 'Requerido') bg-yellow-100 text-yellow-800
                @else bg-blue-100 text-blue-800
                @endif
            ">{{ $submission->status }}</span>
        </p>
    </div>

    <ul class="space-y-0 mt-0">
    {{-- Eventos regulatorios de este sometimiento --}}
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

    {{-- Sometimientos hijos (ej. nuevo intento por rechazo) --}}
    @foreach($submission->children->sortBy('filing_date') as $child)
        @include('admin.processes.partials.timeline-submission', ['submission' => $child])
    @endforeach
    </ul>
</li>
