@php
    $letterheadPath = $template->letterhead_path ?? $template->logo_path ?? null;
@endphp
@if($letterheadPath && file_exists(public_path($letterheadPath)))
    <img src="{{ asset($letterheadPath) }}" alt="Membrete {{ $template->name }}"
         class="h-16 w-auto max-w-[140px] object-contain border border-gray-200 rounded shadow-sm bg-white">
@else
    <span class="text-gray-400 text-xs">Sin membrete</span>
@endif
