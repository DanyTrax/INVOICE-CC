<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $quote->consecutive }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { margin-bottom: 20px; padding-bottom: 14px; border-bottom: 2px solid #0d9488; overflow: hidden; }
        .header-left { float: left; width: 28%; }
        .header-right { float: right; width: 70%; text-align: right; }
        .header-logo { max-height: 56px; max-width: 180px; }
        .header-company { font-size: 16px; font-weight: bold; color: #0d9488; margin-bottom: 4px; }
        .header-subtitle { font-size: 9px; color: #6b7280; margin-bottom: 6px; }
        .header-details { font-size: 9px; color: #374151; line-height: 1.4; }
        h1 { font-size: 15px; margin: 20px 0 14px 0; color: #111827; clear: both; text-align: center; }
        .context-body { margin-bottom: 18px; line-height: 1.5; }
        .meta { margin-bottom: 18px; }
        .meta p { margin: 3px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        table.items th { background: #f3f4f6; text-align: left; padding: 8px 6px; font-size: 9px; text-transform: uppercase; border: 1px solid #e5e7eb; }
        table.items td { padding: 6px; border: 1px solid #e5e7eb; }
        table.items tr.alt { background: #f9fafb; }
        .totals { margin-top: 18px; width: 280px; margin-left: auto; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        .totals .label { background: #f3f4f6; font-weight: bold; width: 55%; }
        .totals .grand { background: #ccfbf1; font-weight: bold; font-size: 12px; }
        .signature { margin-top: 36px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        .signature-line { width: 240px; border-bottom: 1px solid #1f2937; margin-top: 32px; margin-bottom: 4px; }
        .signature-label { font-size: 9px; color: #6b7280; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #6b7280; text-align: center; }
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
            $fechaTexto = $quote->date ? \Carbon\Carbon::parse($quote->date)->locale('es')->translatedFormat('d \d\e F \d\e Y') : '';
            $ciudad = 'Bogotá D. C.';
            $cliente = $quote->client->name ?? '';
            $consecutivo = $quote->consecutive;
            $destinatario = $quote->client->name ?? '';
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
    @endphp

    @if($useTemplate)
        {{-- Cabecera desde plantilla --}}
        <div class="header">
            <div class="header-left">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="" class="header-logo">
                @endif
                @if(!empty(trim($template->header_company_name ?? '')))
                    <div class="header-company" style="font-size: 12px;">{{ $template->header_company_name }}</div>
                @endif
                @if(!empty(trim($template->header_subtitle ?? '')))
                    <div class="header-subtitle">{{ $template->header_subtitle }}</div>
                @endif
            </div>
            <div class="header-right">
                @if(!empty(trim($template->header_company_name ?? '')))
                    <div class="header-company">{{ $template->header_company_name }}</div>
                @endif
                @if(!empty(trim($template->header_nit ?? '')))
                    <div class="header-details"><strong>NIT.</strong> {{ $template->header_nit }}</div>
                @endif
            </div>
        </div>

        <h1>COTIZACIÓN No. {{ $quote->consecutive }}</h1>

        @if(!empty(trim($bodyHtml)))
            <div class="context-body">
                {!! $bodyHtml !!}
            </div>
        @endif
    @else
        {{-- Cabecera desde configuración general (sin plantilla) --}}
        <div class="header">
            <div class="header-left">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="" class="header-logo">
                @else
                    <span class="header-company">{{ $settings->agency_name ?? 'RAMS' }}</span>
                @endif
            </div>
            <div class="header-right">
                @if($logoPath)
                    <div class="header-company">{{ $settings->agency_name ?? 'RAMS' }}</div>
                @endif
                @if(!empty(trim($settings->quote_pdf_header_subtitle ?? '')))
                    <div class="header-subtitle">{{ $settings->quote_pdf_header_subtitle }}</div>
                @endif
                @if(!empty(trim($settings->agency_address ?? '')) || !empty(trim($settings->agency_phone ?? '')) || !empty(trim($settings->agency_email ?? '')))
                    <div class="header-details">
                        @if(!empty(trim($settings->agency_address ?? ''))){{ $settings->agency_address }}<br>@endif
                        @if(!empty(trim($settings->agency_phone ?? '')))Tel: {{ $settings->agency_phone }}@if(!empty(trim($settings->agency_email ?? '')))<br>@endif @endif
                        @if(!empty(trim($settings->agency_email ?? ''))){{ $settings->agency_email }}@endif
                    </div>
                @endif
            </div>
        </div>

        <h1>COTIZACIÓN {{ $quote->consecutive }}</h1>

        <div class="meta">
            <p><strong>Cliente:</strong> {{ $quote->client->name ?? '-' }}</p>
            <p><strong>Fecha:</strong> {{ $quote->date?->format('d/m/Y') ?? '-' }}</p>
            <p><strong>Moneda:</strong> {{ $quote->currency ?? 'COP' }}</p>
            @if($quote->exchange_rate)
                <p><strong>Tasa de cambio:</strong> {{ number_format($quote->exchange_rate, 4) }}</p>
            @endif
        </div>
    @endif

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 22%;">Tipo de trámite</th>
                <th style="width: 22%;">Producto / Descripción</th>
                @if($quote->show_prev_license_column)
                    <th style="width: 12%;">Expediente / INVIMA</th>
                @endif
                @if($quote->show_raa_column)
                    <th style="width: 8%;">RAA</th>
                @endif
                <th style="width: 18%;">Alcance</th>
                <th style="width: 8%;">Tipo</th>
                <th style="width: 5%; text-align: right;">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->quoteItems as $item)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'alt' : '' }}">
                    <td>{{ $item->item_position }}</td>
                    <td>{{ $item->serviceType->name ?? '-' }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
                    @if($quote->show_prev_license_column)
                        <td>{{ $item->previous_license ?? '-' }}</td>
                    @endif
                    @if($quote->show_raa_column)
                        <td>{{ $item->raa_code ?? '-' }}</td>
                    @endif
                    <td>{{ Str::limit($item->scope, 50) }}</td>
                    <td>{{ $item->is_loan ? 'Préstamo' : 'Honorario' }}</td>
                    <td style="text-align: right;">{{ number_format($item->fee_value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Total honorarios</td>
                <td style="text-align: right;">{{ $quote->currency }} {{ number_format($quote->total_professional_fees, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total suplidos / Préstamos</td>
                <td style="text-align: right;">{{ $quote->currency }} {{ number_format($quote->total_loans, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Tasas INVIMA</td>
                <td style="text-align: right;">{{ $quote->currency }} {{ number_format($quote->total_invima_fees, 2) }}</td>
            </tr>
            @if($quote->apply_tax && $quote->tax_percentage !== null)
                <tr>
                    <td class="label">Sub-total</td>
                    <td style="text-align: right;">{{ $quote->currency }} {{ number_format($quote->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">IVA ({{ number_format($quote->tax_percentage, 2) }}%)</td>
                    <td style="text-align: right;">{{ $quote->currency }} {{ number_format($quote->tax_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label grand">Total</td>
                    <td class="grand" style="text-align: right;">{{ $quote->currency }} {{ number_format($quote->total_with_tax, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td class="label grand">Total</td>
                    <td class="grand" style="text-align: right;">{{ $quote->currency }} {{ number_format($quote->subtotal, 2) }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="signature-label">Firma del Gerente</div>
    </div>

    <div class="footer">
        {{ $footerText }}
    </div>
</body>
</html>
