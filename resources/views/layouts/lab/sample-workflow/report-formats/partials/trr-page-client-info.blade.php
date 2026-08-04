{{-- Attention / Client / Address — flows with batch info on page 1 --}}
<table class="info-table">
    <tr>
        <td class="lbl">{{ $labels['attention'] }}</td>
        <td class="colon">:</td>
        <td>{{ $attention ?? $batch->getContactPersonDetail() }}</td>
    </tr>
    <tr>
        <td class="lbl">{{ $labels['client'] }}</td>
        <td class="colon">:</td>
        <td>{{ $customer->name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">{{ $labels['address'] }}</td>
        <td class="colon">:</td>
        <td>{{ $customer->physical_address ?? ($customer->postal_address ?? '-') }}</td>
    </tr>
</table>
