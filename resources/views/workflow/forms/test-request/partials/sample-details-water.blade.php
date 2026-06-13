@php
    $check = fn (bool $on): string => $on ? '☑' : '☐';
@endphp

<div class="trf-section-title">Sample Details</div>
<table class="trf-table">
    <thead>
        <tr>
            <th style="width:4%;">S. No.</th>
            <th style="width:8%;">Sample No.</th>
            <th style="width:14%;">Sample Description</th>
            <th style="width:12%;">Location</th>
            <th style="width:5%;">Qty.</th>
            <th style="width:10%;">Sampling Point</th>
            <th style="width:5%;">pH</th>
            <th style="width:8%;">Appearance</th>
            <th style="width:8%;">Residual Chlorine</th>
            <th style="width:6%;">Odor</th>
            <th style="width:6%;">Temp (°C)</th>
            <th style="width:14%;">Test Requirements</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sampleRows as $row)
            <tr>
                <td class="trf-center">{{ $row['serial'] }}</td>
                <td>{{ $row['sample_no'] }}</td>
                <td>{{ $row['sample_description'] }}</td>
                <td>{{ $row['location'] }}</td>
                <td class="trf-center">{{ $row['qty'] }}</td>
                <td class="trf-center">{{ $row['sampling_point'] }}</td>
                <td class="trf-center">{{ $row['ph'] }}</td>
                <td>{{ $row['appearance'] }}</td>
                <td class="trf-center">{{ $row['residual_chlorine'] }}</td>
                <td>{{ $row['odor'] }}</td>
                <td class="trf-center">{{ $row['sample_temp'] }}</td>
                <td class="trf-small">
                    {{ $check($row['microbiology']) }} Microbiology<br>
                    {{ $check($row['legionella']) }} Legionella<br>
                    {{ $check($row['chemical_analysis']) }} Chemical Analysis
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="trf-center trf-small">No sample rows recorded.</td>
            </tr>
        @endforelse
    </tbody>
</table>
