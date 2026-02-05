<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $quote->consecutive }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { margin-bottom: 24px; border-bottom: 2px solid #0d9488; padding-bottom: 12px; }
        .logo { font-size: 18px; font-weight: bold; color: #0d9488; }
        .subtitle { font-size: 9px; color: #6b7280; margin-top: 2px; }
        h1 { font-size: 16px; margin: 0 0 16px 0; color: #111827; }
        .meta { margin-bottom: 20px; }
        .meta p { margin: 4px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items th { background: #f3f4f6; text-align: left; padding: 8px 6px; font-size: 9px; text-transform: uppercase; border: 1px solid #e5e7eb; }
        table.items td { padding: 6px; border: 1px solid #e5e7eb; }
        table.items tr.alt { background: #f9fafb; }
        .totals { margin-top: 20px; width: 280px; margin-left: auto; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        .totals .label { background: #f3f4f6; font-weight: bold; width: 55%; }
        .totals .grand { background: #ccfbf1; font-weight: bold; font-size: 12px; }
        .signature { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .signature-line { width: 250px; border-bottom: 1px solid #1f2937; margin-top: 36px; margin-bottom: 4px; }
        .signature-label { font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">DobleVía</div>
        <div class="subtitle">RAMS - Regulatory Affairs Management System</div>
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
</body>
</html>
