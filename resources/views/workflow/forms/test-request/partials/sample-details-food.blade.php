@php
    $stateLabel = function (array $checks, string $key): string {
        return ($checks[$key] ?? false) ? '☑' : '☐';
    };
@endphp

<div class="trf-section-title">Sample Details</div>
<table class="trf-table">
    <thead>
        <tr>
            <th style="width:4%;">S. No.</th>
            <th style="width:8%;">Sample No.</th>
            <th style="width:14%;">Sample Description</th>
            <th style="width:12%;">Sampling Point/ Location</th>
            <th style="width:5%;">Qty.</th>
            <th style="width:8%;">Sample Type</th>
            <th style="width:10%;">Sample Condition</th>
            <th style="width:6%;">Temp (°C)</th>
            <th style="width:10%;">Production / Expiry / Batch</th>
            <th style="width:8%;">State (L/SS/S)</th>
            <th style="width:15%;">Micro/Chem Parameters</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sampleRows as $row)
            <tr>
                <td class="trf-center">{{ $row['serial'] }}</td>
                <td>{{ $row['sample_no'] }}</td>
                <td>{{ $row['sample_description'] }}</td>
                <td>{{ $row['sampling_point'] }}</td>
                <td class="trf-center">{{ $row['qty'] }}</td>
                <td class="trf-center">{{ $row['sample_type'] }}</td>
                <td class="trf-center">{{ $row['sample_condition'] }}</td>
                <td class="trf-center">{{ $row['sample_temp'] }}</td>
                <td class="trf-small">
                    @if($row['production_date'])Prod: {{ $row['production_date'] }}<br>@endif
                    @if($row['expiration_date'])Exp: {{ $row['expiration_date'] }}<br>@endif
                    @if($row['batch_number'])Batch: {{ $row['batch_number'] }}@endif
                </td>
                <td class="trf-center trf-state-group">
                    {{ $stateLabel($row['state_of_sample'], 'L') }}L
                    {{ $stateLabel($row['state_of_sample'], 'SS') }}SS
                    {{ $stateLabel($row['state_of_sample'], 'S') }}S
                </td>
                <td>{{ $row['parameters'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="trf-center trf-small">No sample rows recorded.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<p class="trf-small">State of Sample (L - Liquid, SS - Semi Solid, S - Solid)</p>
