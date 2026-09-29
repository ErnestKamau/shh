@php
    $colspan = (int) ($colspan ?? 1);
    $hasAdditionalDetails = collect($sampleRows ?? [])->contains(
        static fn ($row): bool => is_array($row)
            && is_array($row['additional_details'] ?? null)
            && ($row['additional_details'] ?? []) !== []
    );
@endphp
@if($hasAdditionalDetails)
    <tr class="trf-banner-row">
        <td colspan="{{ $colspan }}">ADDITIONAL DETAILS</td>
    </tr>
    @foreach($sampleRows as $row)
        @php
            $details = is_array($row['additional_details'] ?? null) ? $row['additional_details'] : [];
        @endphp
        @foreach($details as $detail)
            <tr class="trf-data-row trf-additional-detail-row">
                <td class="trf-center">{{ $row['serial'] ?? '' }}</td>
                <td>{{ $row['sample_no'] ?? '' }}</td>
                <td class="trf-text-cell" colspan="{{ max(1, $colspan - 2) }}">
                    <strong>{{ $detail['label'] ?? 'Detail' }}:</strong>
                    {{ $detail['value'] ?? '' }}
                </td>
            </tr>
        @endforeach
    @endforeach
@endif
