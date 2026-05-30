@php
    $link = isset($item) && $item instanceof \App\Models\QuoteItem ? $item->resolveLinkedSolicitudButton() : null;
@endphp
@if($link)
    <a href="{{ $link['url'] }}"
       title="{{ $link['title'] }}"
       class="text-sm text-teal-600 hover:bg-teal-50 rounded-lg border border-teal-200 inline-flex items-center px-3 py-1.5 whitespace-nowrap">
        <i class="fas fa-folder-open mr-1"></i>{{ $link['label'] }}
    </a>
@else
    <span class="text-gray-400 text-sm">—</span>
@endif
