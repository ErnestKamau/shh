@php
    $footerLabelColspan = $labelColspan ?? 5;
    $hideVatAmount = (bool) ($hideVatAmount ?? false);
@endphp
<tr class="amspec-totals">
    <td colspan="{{ $footerLabelColspan }}" class="text-center" style="font-weight: 700; border-top: 2px solid #999; text-align: center;">Net Amount</td>
    <td class="text-right" style="font-weight: 700; border-top: 2px solid #999;">{{ number_format($totals['net'], 2) }}</td>
</tr>
@unless($hideVatAmount)
<tr class="amspec-totals">
    <td colspan="{{ $footerLabelColspan }}" class="text-center" style="font-weight: 700; text-align: center;">
        Vat Amount @if($totals['vat_rate'])({{ number_format($totals['vat_rate'], 0) }}%)@endif
    </td>
    <td class="text-right" style="font-weight: 700;">{{ number_format($totals['vat'], 2) }}</td>
</tr>
@endunless
<tr class="amspec-totals">
    <td colspan="{{ $footerLabelColspan }}" class="text-center" style="font-weight: 700; text-align: center;">Total Amount</td>
    <td class="text-right" style="font-weight: 700;">{{ number_format($totals['total'], 2) }}</td>
</tr>
