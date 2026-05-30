{{-- Bloque de firma: espaciador + línea/nombre/cargo (evita solaparse con el pie del membrete). --}}
@php
    $signatureSpacerPx = (int) ($signatureSpacerPx ?? 130);
    $defaultSigLabel = $defaultSigLabel ?? 'Firma del Gerente';
    $sigNamePt = max(4, min(24, (int) ($sigNameSize ?? 11)));
    $sigPosPt = max(4, min(24, (int) ($sigPosSize ?? 9)));
@endphp
<div class="pdf-signature-area" style="page-break-inside: avoid;">
    <div class="pdf-signature-spacer" style="height: {{ max(0, $signatureSpacerPx) }}px; line-height: 0; font-size: 0;">&nbsp;</div>
    <div class="signature">
        <div class="signature-line"></div>
        @if($useTemplate && $template && (trim($template->signature_name ?? '') !== '' || trim($template->signature_position ?? '') !== ''))
            @if(trim($template->signature_name ?? '') !== '')
                <div style="font-size: {{ $sigNamePt }}pt; font-weight: bold; color: #1f2937;">{{ trim($template->signature_name) }}</div>
            @endif
            @if(trim($template->signature_position ?? '') !== '')
                <div style="font-size: {{ $sigPosPt }}pt; color: #6b7280; margin-top: 1px;">{{ trim($template->signature_position) }}</div>
            @endif
        @else
            <div style="font-size: 9pt; color: #6b7280;">{{ $defaultSigLabel }}</div>
        @endif
    </div>
</div>
