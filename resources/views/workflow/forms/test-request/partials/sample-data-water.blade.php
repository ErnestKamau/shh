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
    $grid = $collectionGrid ?? [];
    $waterColspan = 18;
@endphp
<table class="trf-table trf-no-gap">
    <tr class="trf-banner-row">
        <td colspan="{{ $waterColspan }}">SAMPLE COLLECTION DATA</td>
    </tr>
    <tr>
        <th colspan="3">SAMPLE DETAILS</th>
        <th colspan="4">SAMPLING APPARATUS</th>
        <th colspan="4">METHOD OF SAMPLING</th>
        <th colspan="3">REASON OF COLLECTION</th>
        <th colspan="4">TRANSPORT CONDITION</th>
    </tr>
    @foreach($grid['rows'] ?? [] as $row)
        <tr>
            <td colspan="3" class="trf-meta-label">{!! $row['meta'] ?? '&nbsp;' !!}</td>
            <td colspan="4">{!! $renderChecks($row['apparatus'] ?? [], $row['apparatus_keys'] ?? []) !!}{!! $row['apparatus_extra'] ?? '' !!}</td>
            <td colspan="4">{!! $renderChecks($row['method'] ?? [], $row['method_keys'] ?? []) !!}</td>
            <td colspan="3">{!! $renderChecks($row['reason'] ?? [], $row['reason_keys'] ?? []) !!}</td>
            <td colspan="4">{!! $renderChecks($row['transport'] ?? [], $row['transport_keys'] ?? []) !!}</td>
        </tr>
    @endforeach
    <tr>
        <th rowspan="2">S. NO.</th>
        <th rowspan="2">SAMPLE NO.</th>
        <th rowspan="2">SAMPLE DESCRIPTION</th>
        <th rowspan="2">LOCATION</th>
        <th rowspan="2">QTY.</th>
        <th colspan="5">SAMPLING POINT</th>
        <th colspan="5">FIELD DATA</th>
        <th colspan="3">TEST REQUIRMENTS</th>
    </tr>
    <tr>
        <th class="trf-subheader">Tap</th>
        <th class="trf-subheader">Tank</th>
        <th class="trf-subheader">Pool</th>
        <th class="trf-subheader">Shower<br>Head</th>
        <th class="trf-subheader">Others</th>
        <th class="trf-subheader">pH</th>
        <th class="trf-subheader">Appearance</th>
        <th class="trf-subheader">Residual<br>Chlorine</th>
        <th class="trf-subheader">Odor</th>
        <th class="trf-subheader">Sample<br>Temp(°C)</th>
        <th class="trf-subheader">Microbiology</th>
        <th class="trf-subheader">Legionella</th>
        <th class="trf-subheader">Chemical<br>Analysis</th>
    </tr>
    @forelse($sampleRows as $row)
        @php $sp = $row['sampling_point_checks'] ?? []; @endphp
        <tr>
            <td class="trf-center">{{ $row['serial'] }}</td>
            <td>{{ $row['sample_no'] }}</td>
            <td>{{ $row['sample_description'] }}</td>
            <td>{{ $row['location'] }}</td>
            <td class="trf-center">{{ $row['qty'] }}</td>
            <td class="trf-center"><span class="{{ ($sp['Tap'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span></td>
            <td class="trf-center"><span class="{{ ($sp['Tank'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span></td>
            <td class="trf-center"><span class="{{ ($sp['Pool'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span></td>
            <td class="trf-center"><span class="{{ ($sp['Shower Head'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span></td>
            <td class="trf-center"><span class="{{ ($sp['Others'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span></td>
            <td class="trf-center">{{ $row['ph'] }}</td>
            <td class="trf-center">{{ $row['appearance'] }}</td>
            <td class="trf-center">{{ $row['residual_chlorine'] }}</td>
            <td class="trf-center">{{ $row['odor'] }}</td>
            <td class="trf-center">{{ $row['sample_temp'] }}</td>
            <td class="trf-center"><span class="{{ ($row['microbiology'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span></td>
            <td class="trf-center"><span class="{{ ($row['legionella'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span></td>
            <td class="trf-center"><span class="{{ ($row['chemical_analysis'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span></td>
        </tr>
    @empty
        <tr>
            <td colspan="{{ $waterColspan }}" class="trf-center">&nbsp;</td>
        </tr>
    @endforelse
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $waterColspan, 'variant' => 'water'])
</table>
