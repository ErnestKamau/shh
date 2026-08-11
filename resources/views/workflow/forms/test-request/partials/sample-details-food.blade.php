@php
    $renderChecks = function (array $checks) {
        $html = '';
        foreach ($checks as $label => $checked) {
            $class = $checked ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
            $html .= '<span class="' . $class . '"></span> ' . e($label) . ' ';
        }
        return $html;
    };
    $renderState = function (array $state) {
        $html = '';
        foreach (['L' => 'L', 'SS' => 'SS', 'S' => 'S'] as $key => $label) {
            $class = ($state[$key] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
            $html .= '<span class="' . $class . '"></span> ' . e($label) . ' ';
        }
        return $html;
    };
@endphp
<div class="trf-section-title">Sample Details</div>
<table class="trf-table">
    <tr>
        <th style="width:3%;">S. No.</th>
        <th style="width:7%;">SAMPLE NO.</th>
        <th style="width:10%;">SAMPLE DESCRIPTION</th>
        <th style="width:8%;">SAMPLING LOCATION</th>
        <th style="width:8%;">SAMPLING POINT</th>
        <th style="width:4%;">QTY.</th>
        <th style="width:8%;">SAMPLE TYPE</th>
        <th style="width:8%;">SAMPLE CONDITION</th>
        <th style="width:6%;">Production Date</th>
        <th style="width:6%;">Expiration Date</th>
        <th style="width:6%;">Batch Number</th>
        <th style="width:5%;">Sample Temp (°C)</th>
        <th style="width:9%;">Micro/Chem Parameters</th>
        <th style="width:6%;">State of Sample</th>
    </tr>
    @forelse($sampleRows as $row)
        <tr>
            <td class="trf-center">{{ $row['serial'] }}</td>
            <td>{{ $row['sample_no'] }}</td>
            <td>{{ $row['sample_description'] }}</td>
            <td>{{ $row['sampling_location'] ?? '' }}</td>
            <td>{{ $row['sampling_point'] ?? '' }}</td>
            <td class="trf-center">{{ $row['qty'] }}</td>
            <td>{!! $renderChecks($row['sample_type_checks'] ?? []) !!}</td>
            <td>{!! $renderChecks($row['sample_condition_checks'] ?? []) !!}</td>
            <td class="trf-center">{{ $row['production_date'] }}</td>
            <td class="trf-center">{{ $row['expiration_date'] }}</td>
            <td>{{ $row['batch_number'] }}</td>
            <td class="trf-center">{{ $row['sample_temp'] }}</td>
            <td>{{ $row['parameters'] }}</td>
            <td>{!! $renderState($row['state_of_sample'] ?? []) !!}</td>
        </tr>
    @empty
        <tr>
            <td colspan="14" class="trf-center">&nbsp;</td>
        </tr>
    @endforelse
</table>
