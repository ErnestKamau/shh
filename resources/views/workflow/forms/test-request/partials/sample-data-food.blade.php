@php
    $renderChecks = function (array $checks, array $onlyKeys = []) {
        $html = '';
        foreach ($checks as $label => $checked) {
            if ($onlyKeys !== [] && ! in_array($label, $onlyKeys, true)) {
                continue;
            }
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
        $html .= '<span class="trf-small">(L- Liquid, SS- Semi Solid, S- Solid)</span>';
        return $html;
    };
    $grid = $collectionGrid ?? [];
    $foodColspan = 13;
@endphp
<table class="trf-table trf-no-gap">
    <tr class="trf-banner-row">
        <td colspan="{{ $foodColspan }}">SAMPLE COLLECTION DATA</td>
    </tr>
    <tr>
        <th colspan="2">SAMPLE DETAILS</th>
        <th colspan="3">SAMPLING APPARATUS</th>
        <th colspan="3">METHOD OF SAMPLING</th>
        <th colspan="2">REASON OF COLLECTION</th>
        <th colspan="3">TRANSPORT CONDITION</th>
    </tr>
    @foreach($grid['rows'] ?? [] as $row)
        <tr>
            <td colspan="2" class="trf-meta-label">{!! $row['meta'] ?? '&nbsp;' !!}</td>
            <td colspan="3">{!! $renderChecks($row['apparatus'] ?? [], $row['apparatus_keys'] ?? []) !!}</td>
            <td colspan="3">{!! $renderChecks($row['method'] ?? [], $row['method_keys'] ?? []) !!}</td>
            <td colspan="2">{!! $renderChecks($row['reason'] ?? [], $row['reason_keys'] ?? []) !!}</td>
            <td colspan="3">{!! $renderChecks($row['transport'] ?? [], $row['transport_keys'] ?? []) !!}</td>
        </tr>
    @endforeach
    <tr>
        <th>S. No.</th>
        <th>SAMPLE NO.</th>
        <th>SAMPLE DESCRIPTION</th>
        <th>SAMPLING POINT/<br>LOCATION</th>
        <th>QTY.</th>
        <th>SAMPLE TYPE</th>
        <th>SAMPLE CONDITION</th>
        <th>Production<br>Date</th>
        <th>Expiration<br>Date</th>
        <th>Batch<br>Number</th>
        <th>Sample<br>Temp(°C)</th>
        <th>Micro/Chem<br>Parameters</th>
        <th>State of Sample</th>
    </tr>
    @forelse($sampleRows as $row)
        <tr>
            <td class="trf-center">{{ $row['serial'] }}</td>
            <td>{{ $row['sample_no'] }}</td>
            <td>{{ $row['sample_description'] }}</td>
            <td>{{ $row['sampling_point'] }}</td>
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
            <td colspan="{{ $foodColspan }}" class="trf-center">&nbsp;</td>
        </tr>
    @endforelse
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $foodColspan, 'variant' => 'food'])
</table>
