<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cuenta de cobro {{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 24px; }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .brand-name { font-size: 16px; font-weight: bold; }
        .muted { color: #555; }
        .section-title { font-size: 12px; font-weight: bold; margin: 18px 0 8px; text-transform: uppercase; }
        .data-table th, .data-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .data-table th { background: #f3f4f6; }
        .amount { font-size: 14px; font-weight: bold; }
        .footer { margin-top: 36px; }
        .signature { text-align: center; margin-top: 24px; }
        .signature img { max-height: 70px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 30%;">
                @if($brand->logoBase64())
                    <img src="{{ $brand->logoBase64() }}" alt="Logo" style="max-height: 70px;">
                @endif
            </td>
            <td>
                <div class="brand-name">{{ $brand->company_name }}</div>
                @if($brand->nit)<div class="muted">NIT: {{ $brand->nit }}</div>@endif
                @if($brand->address)<div class="muted">{{ $brand->address }}</div>@endif
                @if($brand->city)<div class="muted">{{ $brand->city }}</div>@endif
                @if($brand->phone)<div class="muted">Tel: {{ $brand->phone }}</div>@endif
                @if($brand->email)<div class="muted">{{ $brand->email }}</div>@endif
            </td>
            <td style="width: 28%; text-align: right;">
                <div class="brand-name">CUENTA DE COBRO</div>
                <div><strong>No.</strong> {{ $invoice->number }}</div>
                <div><strong>Elaboración:</strong> {{ $invoice->issue_date->format('d/m/Y') }}</div>
                <div><strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Datos del asociado</div>
    <table class="data-table">
        <tr>
            <th>Nombre</th>
            <th>ID / NIT</th>
            <th>Categoría</th>
            <th>Teléfono</th>
            <th>Correo</th>
        </tr>
        <tr>
            <td>{{ $invoice->associate->full_name }}</td>
            <td>{{ $invoice->associate->document_id }}</td>
            <td>{{ $invoice->associate->category }}</td>
            <td>{{ $invoice->associate->phone ?: '—' }}</td>
            <td>{{ $invoice->associate->email ?: '—' }}</td>
        </tr>
    </table>

    <div class="section-title">Concepto de cobro</div>
    <table class="data-table">
        <tr>
            <th>Concepto</th>
            <th style="width: 25%;">Valor</th>
        </tr>
        <tr>
            <td>{{ $invoice->concept->name }}</td>
            <td class="amount">$ {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($brand->bank_name || $brand->bank_account_number)
        <div class="section-title">Datos bancarios</div>
        <table class="data-table">
            <tr>
                <th>Banco</th>
                <th>Tipo de cuenta</th>
                <th>Número</th>
            </tr>
            <tr>
                <td>{{ $brand->bank_name ?: '—' }}</td>
                <td>{{ $brand->bank_account_type ?: '—' }}</td>
                <td>{{ $brand->bank_account_number ?: '—' }}</td>
            </tr>
        </table>
    @endif

    <div class="footer signature">
        @if($brand->treasurerSignatureBase64())
            <img src="{{ $brand->treasurerSignatureBase64() }}" alt="Firma">
        @endif
        @if($brand->treasurer_signature_title)
            <div style="margin-top: 8px; font-weight: bold;">{{ $brand->treasurer_signature_title }}</div>
        @endif
    </div>
</body>
</html>
