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
        <tr>
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
        <th rowspan="2" style="width: 3%;">S. No.</th>
        <th rowspan="2" style="width: 5%;">SAMPLE NO.</th>
        <th rowspan="2" style="width: 10%;">SAMPLE DESCRIPTION</th>
        <th rowspan="2" style="width: 8%;">SAMPLING POINT/ LOCATION</th>
        <th rowspan="2" style="width: 3%;">QTY.</th>
        <th colspan="3">SAMPLE TYPE</th>
        <th colspan="5">SAMPLE CONDITION</th>
        <th rowspan="2" style="width: 5%;">Production Date</th>
        <th rowspan="2" style="width: 5%;">Expiration Date</th>
        <th rowspan="2" style="width: 5%;">Batch Number</th>
        <th rowspan="2" style="width: 6%;">Micro/Chem Parameters</th>
        <th rowspan="2" style="width: 6%;">State of Sample</th>
    </tr>
    <tr>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Raw']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Cooked']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Ready', 'To Eat']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Accept-', 'able']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Chilled']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Frozen']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Ambient']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Sample', 'Temp(°C)']])</th>
    </tr>
    @foreach($sampleRows as $row)
        @php
            $typeChecks = $row['sample_type_checks'] ?? [];
            $conditionChecks = $row['sample_condition_checks'] ?? [];
            $state = $row['state_of_sample'] ?? [];
        @endphp
        <tr class="trf-data-row">
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
            <td class="trf-state-cell">
                <span class="{{ ($state['L'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                <span class="{{ ($state['SS'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                <span class="{{ ($state['S'] ?? false) ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>
                <span class="trf-small">(L- Liquid, SS- Semi Solid, S- Solid)</span>
            </td>
        </tr>
    @endforeach
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $foodColspan, 'variant' => 'food'])
</table>
