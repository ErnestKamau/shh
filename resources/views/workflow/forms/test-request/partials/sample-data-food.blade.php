@php
    $grid = $collectionGrid ?? [];
    $foodColspan = 18;
    $typeKeys = ['Raw', 'Cooked', 'Ready To Eat'];
    $conditionKeys = ['Acceptable', 'Chilled', 'Frozen', 'Ambient'];
@endphp
<table class="trf-table trf-no-gap">
    <tr class="trf-banner-row">
        <td colspan="{{ $foodColspan }}">SAMPLE COLLECTION DATA</td>
    </tr>
    <tr>
        <th colspan="2">SAMPLE DETAILS</th>
        <th colspan="4">SAMPLING APPARATUS</th>
        <th colspan="4">METHOD OF SAMPLING</th>
        <th colspan="3">REASON OF COLLECTION</th>
        <th colspan="5">TRANSPORT CONDITION</th>
    </tr>
    @foreach($grid['rows'] ?? [] as $row)
        <tr class="trf-collection-row">
            <td colspan="2" class="trf-meta-cell">{!! $row['meta'] ?? '&nbsp;' !!}</td>
            <td colspan="4">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $row['apparatus'] ?? [],
                    'onlyKeys' => $row['apparatus_keys'] ?? [],
                ])
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
            <td colspan="5">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $row['transport'] ?? [],
                    'onlyKeys' => $row['transport_keys'] ?? [],
                ])
            </td>
        </tr>
    @endforeach
    <tr>
        <th rowspan="2" class="trf-vtext-wrap"><span class="trf-vtext">S. No.</span></th>
        <th rowspan="2">SAMPLE NO.</th>
        <th rowspan="2">SAMPLE DESCRIPTION</th>
        <th rowspan="2" class="trf-vtext-wrap"><span class="trf-vtext">SAMPLING POINT/ LOCATION</span></th>
        <th rowspan="2" class="trf-vtext-wrap"><span class="trf-vtext">QTY.</span></th>
        <th colspan="3">SAMPLE TYPE</th>
        <th colspan="5">SAMPLE CONDITION</th>
        <th rowspan="2" class="trf-vtext-wrap"><span class="trf-vtext">Production Date</span></th>
        <th rowspan="2" class="trf-vtext-wrap"><span class="trf-vtext">Expiration Date</span></th>
        <th rowspan="2" class="trf-vtext-wrap"><span class="trf-vtext">Batch Number</span></th>
        <th rowspan="2" class="trf-vtext-wrap"><span class="trf-vtext">Micro/Chem Parameters</span></th>
        <th rowspan="2" class="trf-vtext-wrap"><span class="trf-vtext">State of Sample</span></th>
    </tr>
    <tr>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Raw</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Cooked</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Ready To Eat</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Acceptable</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Chilled</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Frozen</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Ambient</span></th>
        <th class="trf-subheader trf-vtext-wrap"><span class="trf-vtext">Sample Temp(°C)</span></th>
    </tr>
    @foreach($sampleRows as $row)
        @php
            $typeChecks = $row['sample_type_checks'] ?? [];
            $conditionChecks = $row['sample_condition_checks'] ?? [];
            $state = $row['state_of_sample'] ?? [];
        @endphp
        <tr>
            <td class="trf-center">{{ $row['serial'] ?? '' }}</td>
            <td>{{ $row['sample_no'] ?? '' }}</td>
            <td>{{ $row['sample_description'] ?? '' }}</td>
            <td>{{ $row['sampling_point'] ?? '' }}</td>
            <td class="trf-center">{{ $row['qty'] ?? '' }}</td>
            @foreach($typeKeys as $key)
                <td class="trf-center">
                    <span class="{{ ($typeChecks[$key] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                </td>
            @endforeach
            @foreach($conditionKeys as $key)
                <td class="trf-center">
                    <span class="{{ ($conditionChecks[$key] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                </td>
            @endforeach
            <td class="trf-center">{{ $row['sample_temp'] ?? '' }}</td>
            <td class="trf-center">{{ $row['production_date'] ?? '' }}</td>
            <td class="trf-center">{{ $row['expiration_date'] ?? '' }}</td>
            <td>{{ $row['batch_number'] ?? '' }}</td>
            <td>{{ $row['parameters'] ?? '' }}</td>
            <td class="trf-center">
                <span class="{{ ($state['L'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                <span class="{{ ($state['SS'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                <span class="{{ ($state['S'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                <span class="trf-small">(L- Liquid, SS- Semi Solid, S- Solid)</span>
            </td>
        </tr>
    @endforeach
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $foodColspan, 'variant' => 'food'])
</table>
