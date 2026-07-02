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
    $letterheadSrc = PdfDocumentHelper::resolveLetterheadSrcForPdf($letterheadPath);
    $bodyHtml = PdfDocumentHelper::resolveBodyHtml($useTemplate ? $template : null, $proposal);
    $sideNoteHtml = PdfDocumentHelper::resolveSideNoteHtml($useTemplate ? $template : null, $proposal);
    $closingFooterHtml = PdfDocumentHelper::resolveClosingFooterHtml($useTemplate ? $template : null, $proposal);
    $sigNameSize = (int) ($useTemplate ? ($template->signature_name_font_size ?? 11) : 11);
    $sigPosSize = (int) ($useTemplate ? ($template->signature_position_font_size ?? 9) : 9);
    $signatureImageSrc = $useTemplate
        ? PdfDocumentHelper::resolveLetterheadSrcForPdf(PdfDocumentHelper::resolveSignatureImagePath($template))
        : null;
    $sigImgHeight = (int) ($useTemplate ? ($template->signature_image_height_px ?? 55) : 55);
    $docTitleText = trim((string) ($useTemplate ? ($template->doc_title_text ?? '') : '')) ?: 'PROPUESTA No.';
    $docTitleSize = (int) ($useTemplate ? ($template->doc_title_font_size ?? 9) : 9);
    $docTitleBold = $useTemplate ? (bool) ($template->doc_title_bold ?? true) : true;
    $pdfSignatureSpacerPx = $useTemplate ? (int) ($template->signature_margin_top_px ?? 130) : 130;
    $pdfFooterReserveMm = $letterheadSrc
        ? ($useTemplate ? (int) ($template->letterhead_footer_reserve_mm ?? 42) : 42)
        : 14;
    if (! $letterheadSrc) {
        $pdfSignatureSpacerPx = 20;
    }
    $fmt = fn ($n) => PdfDocumentHelper::formatMoney((float) $n, $proposal->currency ?? 'COP');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propuesta {{ $proposal->consecutive }}</title>
    @include('admin.partials.pdf-document-styles')
    @if($letterheadSrc)
        <style>body { padding-bottom: {{ $pdfFooterReserveMm }}mm !important; }</style>
    @endif
</head>
<body>

    @include('admin.partials.pdf-letterhead-img', ['letterheadSrc' => $letterheadSrc])

    <div class="pdf-body-content">
        <h1 class="doc-title" style="font-size: {{ max(4, min(40, $docTitleSize)) }}px; font-weight: {{ $docTitleBold ? 'bold' : 'normal' }};">{{ $docTitleText }} {{ $proposal->consecutive }}</h1>

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

        @include('admin.partials.pdf-signature-section', [
            'template' => $template,
            'useTemplate' => $useTemplate,
            'sigNameSize' => $sigNameSize,
            'sigPosSize' => $sigPosSize,
            'signatureImageSrc' => $signatureImageSrc,
            'sigImgHeight' => $sigImgHeight,
            'signatureSpacerPx' => $pdfSignatureSpacerPx,
            'defaultSigLabel' => 'Firma autorizada',
        ])
    </div>
</body>
</html>
