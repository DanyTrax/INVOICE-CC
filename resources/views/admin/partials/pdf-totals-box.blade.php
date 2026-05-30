@php
    $subtotalLabel = $subtotalLabel ?? 'Subtotal';
@endphp
<table class="totals-box" width="230" cellspacing="0" cellpadding="0" style="width:230px;">
    <tr>
        <td class="label">{{ $subtotalLabel }}</td>
        <td style="text-align: right;">{{ $doc->currency }} {{ $fmt($doc->subtotal) }}</td>
    </tr>
    @if($doc->apply_tax && $doc->tax_percentage !== null)
        <tr>
            <td class="label">IVA ({{ number_format($doc->tax_percentage, 0) }}%)</td>
            <td style="text-align: right;">{{ $doc->currency }} {{ $fmt($doc->tax_amount) }}</td>
        </tr>
    @endif
    @if($doc->apply_bank_fee && $doc->bank_fee_value !== null)
        <tr>
            <td class="label">Gasto bancario</td>
            <td style="text-align: right;">{{ $doc->currency }} {{ $fmt($doc->bank_fee_amount) }}</td>
        </tr>
    @endif
    <tr>
        <td class="label grand">Total</td>
        <td class="grand" style="text-align: right;">{{ $doc->currency }} {{ $fmt($doc->total_with_tax) }}</td>
    </tr>
</table>
