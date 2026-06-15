@php
    $labelColspan = 2 + ($showLoqColumn ? 1 : 0) + ($showMuColumn ? 1 : 0) + 1;
@endphp
<tr class="amspec-totals">
    <td colspan="{{ $labelColspan }}" class="text-right" style="font-weight: 700; border-top: 2px solid #999;">Net Amount</td>
    <td class="text-right" style="font-weight: 700; border-top: 2px solid #999;">{{ number_format($totals['net'], 2) }}</td>
</tr>
<tr class="amspec-totals">
    <td colspan="{{ $labelColspan }}" class="text-right" style="font-weight: 700;">
        Vat Amount @if($totals['vat_rate'])({{ number_format($totals['vat_rate'], 0) }}%)@endif
    </td>
    <td class="text-right" style="font-weight: 700;">{{ number_format($totals['vat'], 2) }}</td>
</tr>
<tr class="amspec-totals">
    <td colspan="{{ $labelColspan }}" class="text-right" style="font-weight: 700;">Total Amount</td>
    <td class="text-right" style="font-weight: 700;">{{ number_format($totals['total'], 2) }}</td>
</tr>
