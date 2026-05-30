<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propuesta {{ $proposal->consecutive }}</title>
    @include('admin.partials.pdf-document-styles')
</head>
<body>
    @php
        use App\Support\PdfDocumentHelper;
        $template = $template ?? null;
        $useTemplate = $template && $template->id;
        $letterheadPath = PdfDocumentHelper::resolveLetterheadPath($useTemplate ? $template : null);
        if (! $letterheadPath) {
            $settings = $settings ?? app(\App\Settings\GeneralSettings::class);
            if (! empty($settings->agency_logo) && file_exists(public_path($settings->agency_logo))) {
                $letterheadPath = public_path($settings->agency_logo);
            }
        }
        $letterheadDataUri = PdfDocumentHelper::resolveLetterheadDataUri($letterheadPath);
        $bodyHtml = PdfDocumentHelper::resolveBodyHtml($useTemplate ? $template : null, $proposal);
        $sideNoteHtml = PdfDocumentHelper::resolveSideNoteHtml($useTemplate ? $template : null, $proposal);
        $closingFooterHtml = PdfDocumentHelper::resolveClosingFooterHtml($useTemplate ? $template : null, $proposal);
        $sigNameSize = (int) ($useTemplate ? ($template->signature_name_font_size ?? 11) : 11);
        $sigPosSize = (int) ($useTemplate ? ($template->signature_position_font_size ?? 11) : 11);
        $fmt = fn ($n) => PdfDocumentHelper::formatMoney((float) $n, $proposal->currency ?? 'COP');
    @endphp

    @if($letterheadDataUri)
        <div class="pdf-letterhead" style="background-image: url('{{ $letterheadDataUri }}');">
            <img src="{{ $letterheadDataUri }}" alt="">
        </div>
    @endif

    <div class="pdf-body-content">
        <h1 class="doc-title">PROPUESTA No. {{ $proposal->consecutive }}</h1>

        @if($bodyHtml !== '')
            <div class="context-body">{!! $bodyHtml !!}</div>
        @elseif(! $useTemplate)
            @php $settings = $settings ?? app(\App\Settings\GeneralSettings::class); @endphp
            <div class="meta">
                <p><strong>Cliente:</strong> {{ $proposal->client->name ?? '-' }}</p>
                <p><strong>Fecha:</strong> {{ $proposal->date?->format('d/m/Y') ?? '-' }}</p>
                <p><strong>Moneda:</strong> {{ $proposal->currency ?? 'COP' }}</p>
            </div>
        @endif

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 28%;">Concepto</th>
                    <th>Alcance</th>
                    <th style="width: 18%; text-align: right;">Honorarios</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proposal->proposalItems as $item)
                    <tr class="{{ $loop->iteration % 2 === 0 ? 'alt' : '' }}">
                        <td>{{ $item->item_position }}</td>
                        <td>{{ $item->concept }}</td>
                        <td>{!! $item->scope ? nl2br(e(strip_tags($item->scope))) : '—' !!}</td>
                        <td style="text-align: right;">{{ $proposal->currency }} {{ $fmt($item->fee_value) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @include('admin.partials.pdf-totals-section', [
            'doc' => $proposal,
            'fmt' => $fmt,
            'sideNoteHtml' => $sideNoteHtml,
            'subtotalLabel' => 'Subtotal honorarios',
        ])

        @if($closingFooterHtml !== '')
            <div class="closing-footer">{!! $closingFooterHtml !!}</div>
        @endif

        <div class="signature">
            <div class="signature-line"></div>
            @if($useTemplate && (trim($template->signature_name ?? '') !== '' || trim($template->signature_position ?? '') !== ''))
                @if(trim($template->signature_name ?? '') !== '')
                    <div style="font-size: {{ max(8, (int) $sigNameSize - 2) }}px; font-weight: bold; color: #1f2937;">{{ trim($template->signature_name) }}</div>
                @endif
                @if(trim($template->signature_position ?? '') !== '')
                    <div style="font-size: {{ max(8, (int) $sigPosSize - 2) }}px; color: #6b7280; margin-top: 1px;">{{ trim($template->signature_position) }}</div>
                @endif
            @else
                <div style="font-size: 9px; color: #6b7280;">Firma autorizada</div>
            @endif
        </div>
    </div>
</body>
</html>
