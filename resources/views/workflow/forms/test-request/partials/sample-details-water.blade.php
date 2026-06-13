@php
    $renderSamplingPoint = function (array $checks) {
        $html = '';
        foreach ($checks as $label => $checked) {
            $class = $checked ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
            $html .= '<span class="' . $class . '"></span> ' . e($label) . ' ';
        }
        return $html;
    };
    $renderTestReq = function (array $row) {
        $items = [
            'Microbiology' => $row['microbiology'] ?? false,
            'Legionella' => $row['legionella'] ?? false,
            'Chemical Analysis' => $row['chemical_analysis'] ?? false,
        ];
        $html = '';
        foreach ($items as $label => $checked) {
            $class = $checked ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
            $html .= '<span class="' . $class . '"></span> ' . e($label) . ' ';
        }
        return $html;
    };
@endphp
<div class="trf-section-title">Sample Details</div>
<table class="trf-table">
    <tr>
        <th rowspan="2" style="width:3%;">S. NO.</th>
        <th rowspan="2" style="width:7%;">SAMPLE NO.</th>
        <th rowspan="2" style="width:10%;">SAMPLE DESCRIPTION</th>
        <th rowspan="2" style="width:8%;">LOCATION</th>
        <th rowspan="2" style="width:4%;">QTY.</th>
        <th colspan="5">SAMPLING POINT</th>
        <th colspan="5">FIELD DATA</th>
        <th colspan="3">TEST REQUIREMENTS</th>
    </tr>
    <tr>
        <th class="trf-subheader">Tap</th>
        <th class="trf-subheader">Tank</th>
        <th class="trf-subheader">Pool</th>
        <th class="trf-subheader">Shower</th>
        <th class="trf-subheader">Others</th>
        <th class="trf-subheader">pH</th>
        <th class="trf-subheader">Appearance</th>
        <th class="trf-subheader">Residual Chlorine</th>
        <th class="trf-subheader">Odor</th>
        <th class="trf-subheader">Temp</th>
        <th class="trf-subheader">Microbiology</th>
        <th class="trf-subheader">Legionella</th>
        <th class="trf-subheader">Chemical Analysis</th>
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
            <td colspan="18" class="trf-center">&nbsp;</td>
        </tr>
    @endforelse
</table>
