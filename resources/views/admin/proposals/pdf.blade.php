<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propuesta {{ $proposal->consecutive }}</title>
    <style>
        @page { size: letter; margin: 28mm 12mm 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 0; }
        .pdf-page-header { position: fixed; top: -28mm; left: 0; right: 0; z-index: 1; background: #fff; padding: 10px 0 0 0; }
        .pdf-page-footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; box-sizing: border-box; z-index: 1; background: #fff; padding: 8px 12mm 10px 12mm; border-top: 1px solid #e5e7eb; font-size: 9px; color: #6b7280; text-align: left; min-height: 28mm; }
        .pdf-page-footer-content { width: 100%; white-space: pre-line; word-wrap: break-word; line-height: 1.4; }
        .pdf-body-content { padding-top: 0; padding-bottom: 80px; }
        .header { overflow: visible; }
        .header-left { float: left; width: 28%; }
        .header-logo { max-height: 62px; max-width: 208px; display: block; object-fit: contain; margin-bottom: 2px; }
        .header-company-below-logo { font-size: 10px; font-weight: bold; color: #0d9488; margin-bottom: 1px; line-height: 1.15; }
        .header-nit { font-size: 9px; color: #374151; line-height: 1.2; margin-bottom: 0; }
        h1 { font-size: 15px; margin: 4px 0 6px 0; color: #111827; clear: both; text-align: center; }
        .context-body { margin-bottom: 8px; line-height: 1.5; font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .context-body * { font-family: DejaVu Sans, sans-serif !important; font-size: 11px !important; color: #1f2937; }
        .meta { margin-bottom: 8px; }
        .meta p { margin: 3px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        table.items th { background: #f3f4f6; text-align: left; padding: 8px 6px; font-size: 9px; text-transform: uppercase; border: 1px solid #e5e7eb; }
        table.items td { padding: 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        table.items tr.alt { background: #f9fafb; }
        .totals { margin-top: 18px; width: 280px; margin-left: auto; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        .totals .label { background: #f3f4f6; font-weight: bold; width: 55%; }
        .totals .grand { background: #ccfbf1; font-weight: bold; font-size: 12px; }
        .signature { margin-top: 36px; padding-top: 0; }
        .signature-line { width: 240px; border-bottom: 1px solid #1f2937; margin-top: 32px; margin-bottom: 4px; }
        .signature-label { font-size: 9px; color: #6b7280; }
        .signature-name { font-size: 12px; font-weight: bold; color: #1f2937; }
    </style>
</head>
<body>
    @php
        $template = $template ?? null;
        $useTemplate = $template && $template->id;

        if ($useTemplate) {
            $logoPath = ($template->logo_path && file_exists(public_path($template->logo_path)))
                ? public_path($template->logo_path)
                : null;
            $footerText = trim($template->footer_text ?? '') ?: 'RAMS - Regulatory Affairs Management System';
            $fechaTexto = $proposal->date ? \Carbon\Carbon::parse($proposal->date)->locale('es')->translatedFormat('d \d\e F \d\e Y') : '';
            $ciudad = 'Bogotá D. C.';
            $cliente = $proposal->client->name ?? '';
            $consecutivo = $proposal->consecutive;
            $destinatario = $proposal->client->name ?? '';
            $bodyHtml = $template->body_html ?? '';
            $bodyHtml = str_replace(['{{fecha}}', '{{ciudad}}', '{{cliente}}', '{{consecutivo}}', '{{destinatario}}'], [$fechaTexto, $ciudad, $cliente, $consecutivo, $destinatario], $bodyHtml);
        } else {
            $settings = $settings ?? app(\App\Settings\GeneralSettings::class);
            $logoPath = (!empty($settings->agency_logo) && file_exists(public_path($settings->agency_logo)))
                ? public_path($settings->agency_logo)
                : null;
            $footerText = !empty(trim($settings->quote_pdf_footer_text ?? ''))
                ? $settings->quote_pdf_footer_text
                : ($settings->footer_text ?? 'RAMS - Regulatory Affairs Management System');
        }
        if (!empty(trim($proposal->pdf_footer ?? ''))) {
            $footerText = trim($proposal->pdf_footer);
        }
    @endphp

    <div class="pdf-page-header">
        <div class="header">
            <div class="header-left">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="" class="header-logo">
                @endif
                @if($useTemplate)
                    @if(!empty(trim($template->header_company_name ?? '')))
                        <div class="header-company-below-logo">{{ $template->header_company_name }}</div>
                    @endif
                    @if(!empty(trim($template->header_nit ?? '')))
                        <div class="header-nit"><strong>NIT.</strong> {{ $template->header_nit }}</div>
                    @endif
                @else
                    @if(!$logoPath)
                        <span style="font-size: 16px; font-weight: bold; color: #0d9488;">{{ $settings->agency_name ?? 'RAMS' }}</span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="pdf-page-footer"><div class="pdf-page-footer-content">{{ $footerText }}</div></div>

    <div class="pdf-body-content">
        <h1>PROPUESTA No. {{ $proposal->consecutive }}</h1>

        @if($useTemplate)
            @if(!empty(trim($bodyHtml)))
                <div class="context-body">
                    {!! $bodyHtml !!}
                </div>
            @endif
        @else
            <div class="meta">
                <p><strong>Cliente:</strong> {{ $proposal->client->name ?? '-' }}</p>
                <p><strong>Fecha:</strong> {{ $proposal->date?->format('d/m/Y') ?? '-' }}</p>
                <p><strong>Moneda:</strong> {{ $proposal->currency ?? 'COP' }}</p>
                @if($proposal->exchange_rate)
                    <p><strong>Tasa de cambio:</strong> {{ number_format($proposal->exchange_rate, 4) }}</p>
                @endif
            </div>
        @endif

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 28%;">Concepto</th>
                <th style="width: 47%;">Alcance</th>
                <th style="width: 20%; text-align: right;">Honorarios</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proposal->proposalItems as $item)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'alt' : '' }}">
                    <td>{{ $item->item_position }}</td>
                    <td>{{ $item->concept }}</td>
                    <td>{{ $item->scope ? Str::limit(strip_tags($item->scope), 400) : '—' }}</td>
                    <td style="text-align: right;">{{ $proposal->currency }} {{ number_format($item->fee_value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal honorarios</td>
                <td style="text-align: right;">{{ $proposal->currency }} {{ number_format($proposal->subtotal, 2) }}</td>
            </tr>
            @if($proposal->apply_tax && $proposal->tax_percentage !== null)
                <tr>
                    <td class="label">IVA ({{ number_format($proposal->tax_percentage, 2) }}%)</td>
                    <td style="text-align: right;">{{ $proposal->currency }} {{ number_format($proposal->tax_amount, 2) }}</td>
                </tr>
            @endif
            @if($proposal->apply_bank_fee && $proposal->bank_fee_value !== null)
                <tr>
                    <td class="label">Gasto bancario</td>
                    <td style="text-align: right;">{{ $proposal->currency }} {{ number_format($proposal->bank_fee_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="label grand">Total</td>
                <td class="grand" style="text-align: right;">{{ $proposal->currency }} {{ number_format($proposal->total_with_tax, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="signature">
        <div class="signature-line"></div>
        @if($useTemplate && (trim($template->signature_name ?? '') !== '' || trim($template->signature_position ?? '') !== ''))
            <div class="signature-name">{{ trim($template->signature_name ?? '') }}</div>
            @if(trim($template->signature_position ?? '') !== '')
                <div class="signature-label" style="margin-top: 1px;">{{ trim($template->signature_position) }}</div>
            @endif
        @else
            <div class="signature-label">Firma autorizada</div>
        @endif
    </div>

    </div>
</body>
</html>
