@php
    $grid = $collectionGrid ?? [];
    $foodSampleTypeColumns = $foodSampleTypeColumns ?? [];
    $sampleTypeColumnCount = count($foodSampleTypeColumns);
    // Fixed cols: serial, sample_no, desc, point, qty, condition(5), dates(2), batch, micro, chem, state = 16
    $foodColspan = 16 + $sampleTypeColumnCount;
    $jobCols = min(4, max(3, (int) floor($foodColspan * 0.22)));
    $customerCols = $foodColspan - $jobCols;
    $detailsColspan = 3;
    $apparatusColspan = 4;
    $reasonColspan = 3;
    $transportColspan = 3;
    $methodColspan = max(3, $foodColspan - $detailsColspan - $apparatusColspan - $reasonColspan - $transportColspan);
    $conditionKeys = ['Acceptable', 'Chilled', 'Frozen', 'Ambient'];
    $microChemKeys = ['Micro', 'Chem'];
@endphp
<table class="trf-table trf-no-gap trf-food-table">
    <colgroup>
        <col class="trf-col-serial">
        <col class="trf-col-sample-no">
        <col class="trf-col-desc">
        <col class="trf-col-location">
        <col class="trf-col-qty">
        @foreach($foodSampleTypeColumns as $sampleTypeColumn)
            <col class="trf-col-sample-type">
        @endforeach
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-temp">
        <col class="trf-col-date">
        <col class="trf-col-date">
        <col class="trf-col-batch">
        <col class="trf-col-tick">
        <col class="trf-col-tick">
        <col class="trf-col-state">
    </colgroup>
    @include('workflow.forms.test-request.partials.customer-detail-rows', [
        'customerCols' => $customerCols,
        'jobCols' => $jobCols,
    ])
    <tr class="trf-banner-row">
        <td colspan="{{ $foodColspan }}">SAMPLE COLLECTION DATA</td>
    </tr>
    @include('workflow.forms.test-request.partials.collection-grid-section', [
        'detailsColspan' => $detailsColspan,
        'apparatusColspan' => $apparatusColspan,
        'methodColspan' => $methodColspan,
        'reasonColspan' => $reasonColspan,
        'transportColspan' => $transportColspan,
    ])
    <tr>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['S.', 'No.']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLE', 'NO.']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLE', 'DESCRIPTION']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLING', 'POINT']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['QTY.']])</th>
        @if($sampleTypeColumnCount > 0)
            <th colspan="{{ $sampleTypeColumnCount }}" class="trf-tick-col-header">SAMPLE TYPE</th>
        @endif
        <th colspan="5" class="trf-tick-col-header">SAMPLE CONDITION</th>
        <th rowspan="2" class="trf-vtext-wrap trf-col-date">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Production', 'Date']])</th>
        <th rowspan="2" class="trf-vtext-wrap trf-col-date">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Expiration', 'Date']])</th>
        <th rowspan="2" class="trf-vtext-wrap trf-col-batch">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Batch', 'Number']])</th>
        <th colspan="2" class="trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Micro/Chem', 'Parameters']])</th>
        <th rowspan="2" class="trf-state-header">
            @include('workflow.forms.test-request.partials.vtext', ['parts' => ['State of', 'Sample', '(L / SS / S)']])
        </th>
    </tr>
    <tr>
        @foreach($foodSampleTypeColumns as $sampleTypeColumn)
            @php
                $typeName = trim((string) ($sampleTypeColumn['name'] ?? ''));
                $typeParts = $typeName !== '' ? preg_split('/\s+/', $typeName) : ['—'];
                if (! is_array($typeParts) || $typeParts === []) {
                    $typeParts = ['—'];
                }
            @endphp
            <th class="trf-subheader trf-vtext-wrap trf-tick-col-header trf-col-sample-type-header">
                @include('workflow.forms.test-request.partials.vtext', [
                    'parts' => $typeParts,
                    'rotate' => true,
                ])
            </th>
        @endforeach
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Acceptable'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Chilled'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Frozen'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Ambient'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Sample', 'Temp(°C)']])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Micro'], 'rotate' => true])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Chem'], 'rotate' => true])</th>
    </tr>
    @foreach($sampleRows as $row)
        @php
            $conditionChecks = $row['sample_condition_checks'] ?? [];
            $microChemChecks = $row['micro_chem_checks'] ?? [];
            $sampleTypeTicks = $row['sample_type_ticks'] ?? [];
            $state = $row['state_of_sample'] ?? [];
        @endphp
        <tr class="trf-data-row">
            <td class="trf-center">{{ $row['serial'] ?? '' }}</td>
            <td>{{ $row['sample_no'] ?? '' }}</td>
            <td class="trf-text-cell">{{ $row['sample_description'] ?? '' }}</td>
            <td class="trf-text-cell">{{ $row['sampling_point'] ?? '' }}</td>
            <td class="trf-center">{{ $row['qty'] ?? '' }}</td>
            @foreach($foodSampleTypeColumns as $sampleTypeColumn)
                <td class="trf-tick-cell trf-col-sample-type-cell">
                    @if($sampleTypeTicks[$sampleTypeColumn['id']] ?? false)
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
            @foreach($microChemKeys as $key)
                <td class="trf-tick-cell">
                    @if($microChemChecks[$key] ?? false)
                        <span class="trf-tick">&#10003;</span>
                    @endif
                </td>
            @endforeach
            <td class="trf-state-cell">
                @include('workflow.forms.test-request.partials.state-of-sample-cell', ['state' => $state])
            </td>
        </tr>
    @endforeach
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $foodColspan, 'variant' => 'food'])
</table>
