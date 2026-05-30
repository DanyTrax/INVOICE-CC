@php
    $itemStyle = match($item->status) {
        'Aprobado' => 'text-green-700',
        'Traducción' => 'text-yellow-700',
        'Recibido' => 'text-blue-700',
        default => 'text-gray-700',
    };
@endphp
<li class="flex items-start gap-2 {{ $itemStyle }}">
    <i class="fas fa-{{ $item->status === 'Aprobado' ? 'check-circle' : 'circle' }} text-xs mt-1 shrink-0"></i>
    <div class="min-w-0">
        <span>{{ $item->document_name }} <span class="text-xs">({{ $item->status }})</span></span>
        @if($item->updated_at)
            <p class="text-[11px] text-gray-500 mt-0.5">Modificado: {{ $item->updated_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>
</li>
