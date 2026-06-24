@php
    $grid = $collectionGrid ?? [];
    $foodColspan = 18;
    $typeKeys = ['Raw', 'Cooked', 'Ready To Eat'];
    $conditionKeys = ['Acceptable', 'Chilled', 'Frozen', 'Ambient'];
@endphp
<table class="trf-table trf-no-gap trf-food-table">
    <colgroup>
        <col class="trf-col-serial">
        <col class="trf-col-sample-no">
        <col class="trf-col-desc">
        <col class="trf-col-location">
        <col class="trf-col-qty">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-temp">
        <col class="trf-col-date">
        <col class="trf-col-date">
        <col class="trf-col-batch">
        <col class="trf-col-params">
        <col class="trf-col-state">
    </colgroup>
    @include('workflow.forms.test-request.partials.customer-detail-rows', ['customerCols' => 14, 'jobCols' => 4])
    <tr class="trf-banner-row">
        <td colspan="{{ $foodColspan }}">SAMPLE COLLECTION DATA</td>
    </tr>
    @include('workflow.forms.test-request.partials.collection-grid-section', [
        'detailsColspan' => 2,
        'apparatusColspan' => 4,
        'methodColspan' => 6,
        'reasonColspan' => 3,
        'transportColspan' => 3,
    ])
    <tr>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['S.', 'No.']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLE', 'NO.']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLE', 'DESCRIPTION']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLING', 'POINT/', 'LOCATION']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['QTY.']])</th>
        <th colspan="3" class="trf-tick-col-header">SAMPLE TYPE</th>
        <th colspan="5" class="trf-tick-col-header">SAMPLE CONDITION</th>
        <th rowspan="2" class="trf-vtext-wrap trf-col-date">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Production', 'Date']])</th>
        <th rowspan="2" class="trf-vtext-wrap trf-col-date">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Expiration', 'Date']])</th>
        <th rowspan="2" class="trf-vtext-wrap trf-col-batch">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Batch', 'Number']])</th>
        <th rowspan="2" class="trf-vtext-wrap trf-col-params">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Micro/Chem', 'Parameters']])</th>
        <th rowspan="2" class="trf-state-header">
            @include('workflow.forms.test-request.partials.vtext', ['parts' => ['State of', 'Sample', '(L- Liquid, SS-', 'Semi Solid, S-', 'Solid)']])
        </th>
    </tr>
    <tr>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Raw'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Cooked'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Ready', 'To Eat']])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Acceptable'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Chilled'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Frozen'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Ambient'], 'rotate' => true])</th>
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
            <td class="trf-text-cell">{!! $row['sample_description'] ?? '' !!}</td>
            <td class="trf-text-cell">{!! $row['sampling_point'] ?? '' !!}</td>
            <td class="trf-center">{{ $row['qty'] ?? '' }}</td>
            @foreach($typeKeys as $key)
                <td class="trf-tick-cell">
                    @if($typeChecks[$key] ?? false)
                        <span class="trf-tick">&#10003;</span>
                    @endif
                </td>
            @endforeach
            @foreach($conditionKeys as $key)
                <td class="trf-tick-cell">
                    @if($conditionChecks[$key] ?? false)
                        <span class="trf-tick">&#10003;</span>
                    @endif
                </td>
            @endforeach
            <td class="trf-col-temp-cell">{{ $row['sample_temp'] ?? '' }}</td>
            <td class="trf-col-date-cell">{{ $row['production_date'] ?? '' }}</td>
            <td class="trf-col-date-cell">{{ $row['expiration_date'] ?? '' }}</td>
            <td class="trf-col-batch-cell">{{ $row['batch_number'] ?? '' }}</td>
            <td class="trf-col-params-cell">{{ $row['parameters'] ?? '' }}</td>
            <td class="trf-state-cell">
                @include('workflow.forms.test-request.partials.state-of-sample-cell', ['state' => $state])
            </td>
        </tr>
    @endforeach
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $foodColspan, 'variant' => 'food'])
</table>
