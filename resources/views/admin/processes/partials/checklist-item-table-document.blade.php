<td class="px-3 py-2 font-medium text-gray-900">
    <div class="flex items-start gap-2">
        <i class="fas {{ $item->status === 'Aprobado' ? 'fa-check-circle text-green-600' : 'fa-circle text-gray-400' }} mt-0.5 shrink-0"></i>
        <div class="min-w-0">
            <div>{{ $item->document_name }}</div>
            @if($item->updated_at)
                <p class="text-[11px] text-gray-500 font-normal mt-0.5">Modificado: {{ $item->updated_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    </div>
</td>
