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
    $bodyHtml = PdfDocumentHelper::resolveBodyHtml($useTemplate ? $template : null, $quote);
    $showPdfSideNote = $quote->show_pdf_side_note ?? true;
    $showPdfFooter = $quote->show_pdf_footer ?? true;
    $sideNoteHtml = $showPdfSideNote
        ? PdfDocumentHelper::resolveSideNoteHtml($useTemplate ? $template : null, $quote)
        : '';
    $closingFooterHtml = $showPdfFooter
        ? PdfDocumentHelper::resolveClosingFooterHtml($useTemplate ? $template : null, $quote)
        : '';
    $sigNameSize = (int) ($useTemplate ? ($template->signature_name_font_size ?? 11) : 11);
    $sigPosSize = (int) ($useTemplate ? ($template->signature_position_font_size ?? 9) : 9);
    $pdfSignatureSpacerPx = $useTemplate ? (int) ($template->signature_margin_top_px ?? 130) : 130;
    $pdfFooterReserveMm = $letterheadSrc
        ? ($useTemplate ? (int) ($template->letterhead_footer_reserve_mm ?? 42) : 42)
        : 14;
    if (! $letterheadSrc) {
        $pdfSignatureSpacerPx = 20;
    }
    $fmt = fn ($n) => PdfDocumentHelper::formatMoney((float) $n, $quote->currency ?? 'COP');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $quote->consecutive }}</title>
    @include('admin.partials.pdf-document-styles')
    @if($letterheadSrc)
        <style>body { padding-bottom: {{ $pdfFooterReserveMm }}mm !important; }</style>
    @endif
</head>
<body>

    @include('admin.partials.pdf-letterhead-img', ['letterheadSrc' => $letterheadSrc])

    <div class="pdf-body-content">
        <h1 class="doc-title">COTIZACIÓN No. {{ $quote->consecutive }}</h1>

        @if($bodyHtml !== '')
            <div class="context-body">{!! $bodyHtml !!}</div>
        @elseif(! $useTemplate)
            @php $settings = $settings ?? app(\App\Settings\GeneralSettings::class); @endphp
            <div class="meta">
                <p><strong>Cliente:</strong> {{ $quote->client->name ?? '-' }}</p>
                <p><strong>Fecha:</strong> {{ $quote->date?->format('d/m/Y') ?? '-' }}</p>
                <p><strong>Moneda:</strong> {{ $quote->currency ?? 'COP' }}</p>
            </div>
        @endif

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    @if($quote->show_row_id_column ?? false)
                        <th style="width: 8%;">ROW ID</th>
                    @endif
                    <th>Servicio</th>
                    @if($quote->show_service_type_column)
                        <th>Trámite</th>
                    @endif
                    @if($quote->show_description_column ?? true)
                        <th>Producto / Descripción</th>
                    @endif
                    @if($quote->show_prev_license_column)
                        <th>Solicitud / INVIMA</th>
                    @endif
                    @if($quote->show_raa_column)
                        <th>RAA</th>
                    @endif
                    @if($quote->show_franquicia_column ?? false)
                        <th>Franquicia</th>
                    @endif
                    @if($quote->show_centro_costos_column ?? false)
                        <th>Centro de costos</th>
                    @endif
                    @if($quote->show_contacto_column ?? false)
                        <th>Contacto</th>
                    @endif
                    <th>Alcance</th>
                    <th style="width: 12%; text-align: right;">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->quoteItems as $item)
                    <tr class="{{ $loop->iteration % 2 === 0 ? 'alt' : '' }}">
                        <td>{{ $item->item_position }}</td>
                        @if($quote->show_row_id_column ?? false)
                            <td>{{ $item->row_id ?: '–' }}</td>
                        @endif
                        <td>{{ $item->service_label ?: ($item->service?->name ?? '-') }}</td>
                        @if($quote->show_service_type_column)
                            <td>{{ $item->serviceType?->name ?? '–' }}</td>
                        @endif
                        @if($quote->show_description_column ?? true)
                            <td>{{ $item->description ?? '-' }}</td>
                        @endif
                        @if($quote->show_prev_license_column)
                            <td>{{ $item->previous_license ?? '-' }}</td>
                        @endif
                        @if($quote->show_raa_column)
                            <td>{{ $item->raa_code ?? '-' }}</td>
                        @endif
                        @if($quote->show_franquicia_column ?? false)
                            <td>{{ $item->franquicia ?: '–' }}</td>
                        @endif
                        @if($quote->show_centro_costos_column ?? false)
                            <td>{{ $item->centro_costos ?: '–' }}</td>
                        @endif
                        @if($quote->show_contacto_column ?? false)
                            <td>{{ $item->contacto ?: '–' }}</td>
                        @endif
                        <td>{{ $item->scope ?? '-' }}</td>
                        <td style="text-align: right;">{{ $fmt($item->fee_value) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @include('admin.partials.pdf-totals-section', [
            'doc' => $quote,
            'fmt' => $fmt,
            'sideNoteHtml' => $sideNoteHtml,
            'subtotalLabel' => 'Subtotal',
        ])

        @if($closingFooterHtml !== '')
            <div class="closing-footer">{!! $closingFooterHtml !!}</div>
        @endif

        @include('admin.partials.pdf-signature-section', [
            'template' => $template,
            'useTemplate' => $useTemplate,
            'sigNameSize' => $sigNameSize,
            'sigPosSize' => $sigPosSize,
            'signatureSpacerPx' => $pdfSignatureSpacerPx,
            'defaultSigLabel' => 'Firma del Gerente',
        ])
    </div>
</body>
</html>
