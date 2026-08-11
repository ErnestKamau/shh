{{-- Attention / Client / Address — one cell per row: "Label : Value" --}}
<table class="info-table">
    <tr>
        <td>
            <strong>{{ $labels['attention'] }}</strong> : {{ $attention ?? $batch->getContactPersonDetail() }}
        </td>
    </tr>
    <tr>
        <td>
            <strong>{{ $labels['client'] }}</strong> : {{ $customer->name ?? '-' }}
        </td>
    </tr>
    <tr>
        <td>
            <strong>{{ $labels['address'] }}</strong> : {{ $customer->physical_address ?? ($customer->postal_address ?? '-') }}
        </td>
    </tr>
</table>
