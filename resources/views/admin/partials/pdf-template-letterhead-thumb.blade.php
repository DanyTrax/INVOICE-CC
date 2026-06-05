@php
    use App\Support\PdfDocumentHelper;
    $letterheadPath = PdfDocumentHelper::resolveLetterheadRelativePath($template);
@endphp
@if($letterheadPath)
    <img src="{{ asset($letterheadPath) }}" alt="Membrete {{ $template->name }}"
         class="h-16 w-auto max-w-[140px] object-contain border border-gray-200 rounded shadow-sm bg-white">
    @if($template->letterhead_drive_id ?? null)
        <span class="block text-[10px] text-teal-700 mt-0.5" title="Respaldo en Google Drive"><i class="fab fa-google-drive"></i> Drive</span>
    @endif
@elseif($template->letterhead_drive_id ?? null)
    <span class="text-amber-700 text-xs"><i class="fab fa-google-drive mr-1"></i> En Drive</span>
@else
    <span class="text-gray-400 text-xs">Sin membrete</span>
@endif
