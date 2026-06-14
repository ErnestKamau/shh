@php
    $grid = $collectionGrid ?? [];
    $waterColspan = 18;
    $samplingPointKeys = ['Tap', 'Tank', 'Pool', 'Shower Head', 'Others'];
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
        <tr class="trf-collection-row">
            <td colspan="3" class="trf-meta-cell">{!! $row['meta'] ?? '&nbsp;' !!}</td>
            <td colspan="4">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $row['apparatus'] ?? [],
                    'onlyKeys' => $row['apparatus_keys'] ?? [],
                ])
                {!! $row['apparatus_extra'] ?? '' !!}
            </td>
            <td colspan="4">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $row['method'] ?? [],
                    'onlyKeys' => $row['method_keys'] ?? [],
                ])
            </td>
            <td colspan="3">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $row['reason'] ?? [],
                    'onlyKeys' => $row['reason_keys'] ?? [],
                ])
            </td>
            <td colspan="4">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $row['transport'] ?? [],
                    'onlyKeys' => $row['transport_keys'] ?? [],
                ])
            </td>
        </tr>
    @endforeach
    <tr>
        <th rowspan="2" class="trf-vtext-wrap" style="width: 3%;"><span class="trf-vtext">S. NO.</span></th>
        <th rowspan="2" style="width: 5%;">SAMPLE NO.</th>
        <th rowspan="2" style="width: 10%;">SAMPLE DESCRIPTION</th>
        <th rowspan="2" style="width: 7%;">LOCATION</th>
        <th rowspan="2" class="trf-vtext-wrap" style="width: 3%;"><span class="trf-vtext">QTY.</span></th>
        <th colspan="5">SAMPLING POINT</th>
        <th colspan="5">FIELD DATA</th>
        <th colspan="3">TEST REQUIRMENTS</th>
    </tr>
    <tr>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Tap</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Tank</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Pool</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Shower Head</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Others</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">pH</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Appearance</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Residual Chlorine</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Odor</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Sample Temp(°C)</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Microbiology</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Legionella</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Chemical Analysis</span></th>
    </tr>
    @foreach($sampleRows as $row)
        @php $sp = $row['sampling_point_checks'] ?? []; @endphp
        <tr>
            <td class="trf-center">{{ $row['serial'] ?? '' }}</td>
            <td>{{ $row['sample_no'] ?? '' }}</td>
            <td>{{ $row['sample_description'] ?? '' }}</td>
            <td>{{ $row['location'] ?? '' }}</td>
            <td class="trf-center">{{ $row['qty'] ?? '' }}</td>
            @foreach($samplingPointKeys as $key)
                <td class="trf-center">
                    <span class="{{ ($sp[$key] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                </td>
            @endforeach
            <td class="trf-center">{{ $row['ph'] ?? '' }}</td>
            <td class="trf-center">{{ $row['appearance'] ?? '' }}</td>
            <td class="trf-center">{{ $row['residual_chlorine'] ?? '' }}</td>
            <td class="trf-center">{{ $row['odor'] ?? '' }}</td>
            <td class="trf-center">{{ $row['sample_temp'] ?? '' }}</td>
            <td class="trf-center">
                <span class="{{ ($row['microbiology'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
            </td>
            <td class="trf-center">
                <span class="{{ ($row['legionella'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
            </td>
            <td class="trf-center">
                <span class="{{ ($row['chemical_analysis'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
            </td>
        </tr>
    @endforeach
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $waterColspan, 'variant' => 'water'])
</table>
