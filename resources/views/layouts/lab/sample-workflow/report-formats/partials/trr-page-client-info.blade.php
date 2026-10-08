{{-- Attention / Client / Address — label, colon and value in aligned columns --}}
<table class="info-table">
    <colgroup>
        <col class="info-label-col">
        <col class="info-colon-col">
        <col>
    </colgroup>
    <tr>
        <td class="info-label">{{ $labels['attention'] }}</td>
        <td class="info-colon">:</td>
        <td class="info-value">{{ $attention ?? $batch->getContactPersonDetail() }}</td>
    </tr>
    <tr>
        <td class="info-label">{{ $labels['client'] }}</td>
        <td class="info-colon">:</td>
        <td class="info-value">{{ $customer->name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="info-label">{{ $labels['address'] }}</td>
        <td class="info-colon">:</td>
        <td class="info-value">{{ $customer->physical_address ?? ($customer->postal_address ?? '-') }}</td>
    </tr>
</table>
