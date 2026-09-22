@php
    $grid = $collectionGrid ?? [];
    $waterColspan = 13;
@endphp
<table class="trf-table trf-no-gap trf-water-table">
    <colgroup>
        <col class="trf-col-serial">
        <col class="trf-col-sample-no">
        <col class="trf-col-desc">
        <col class="trf-col-qty">
        <col class="trf-col-location">
        <col class="trf-col-field">
        <col class="trf-col-field">
        <col class="trf-col-field">
        <col class="trf-col-field">
        <col class="trf-col-field">
        <col class="trf-col-test">
        <col class="trf-col-test">
        <col class="trf-col-test">
    </colgroup>
    @include('workflow.forms.test-request.partials.customer-detail-rows', ['customerCols' => 9, 'jobCols' => 4])
    <tr class="trf-banner-row">
        <td colspan="{{ $waterColspan }}">SAMPLE COLLECTION DATA</td>
    </tr>
    @include('workflow.forms.test-request.partials.collection-grid-section-water', [
        'detailsColspan' => 3,
        'apparatusColspan' => 5,
        'methodColspan' => 5,
    ])
    <tr class="trf-sample-header-row">
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['S.', 'NO.']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLE', 'NO.']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLE', 'DESCRIPTION']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['QTY.']])</th>
        <th rowspan="2" class="trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['SAMPLING', 'POINT']])</th>
        <th colspan="5">FIELD DATA</th>
        <th colspan="3" class="trf-tick-col-header">TEST REQUIREMENTS</th>
    </tr>
    <tr class="trf-sample-subheader-row">
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['pH']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Appear-', 'ance']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Residual', 'Chlorine']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Odor']])</th>
        <th class="trf-subheader trf-vtext-wrap">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Sample', 'Temp(°C)']])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Micro-', 'biology']])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Legion-', 'ella']])</th>
        <th class="trf-subheader trf-vtext-wrap trf-tick-col-header">@include('workflow.forms.test-request.partials.vtext', ['parts' => ['Chemical', 'Analysis']])</th>
    </tr>
    @foreach($sampleRows as $row)
        <tr class="trf-data-row">
            <td class="trf-center">{{ $row['serial'] ?? '' }}</td>
            <td>{{ $row['sample_no'] ?? '' }}</td>
            <td class="trf-text-cell">{!! $row['sample_description'] ?? '' !!}</td>
            <td class="trf-center">{{ $row['qty'] ?? '' }}</td>
            <td class="trf-text-cell">{!! $row['sampling_point'] ?? '' !!}</td>
            <td class="trf-center trf-col-field">{{ $row['ph'] ?? '' }}</td>
            <td class="trf-center trf-col-field">{{ $row['appearance'] ?? '' }}</td>
            <td class="trf-center trf-col-field">{{ $row['residual_chlorine'] ?? '' }}</td>
            <td class="trf-center trf-col-field">{{ $row['odor'] ?? '' }}</td>
            <td class="trf-center trf-col-temp-cell">{{ $row['sample_temp'] ?? '' }}</td>
            <td class="trf-tick-cell">
                @include('workflow.forms.test-request.partials.tick-icon', ['checked' => $row['microbiology'] ?? false])
            </td>
            <td class="trf-tick-cell">
                @include('workflow.forms.test-request.partials.tick-icon', ['checked' => $row['legionella'] ?? false])
            </td>
            <td class="trf-tick-cell">
                @include('workflow.forms.test-request.partials.tick-icon', ['checked' => $row['chemistry'] ?? $row['chemical_analysis'] ?? false])
            </td>
        </tr>
    @endforeach
    @include('workflow.forms.test-request.partials.additional-details-rows', [
        'sampleRows' => $sampleRows,
        'colspan' => $waterColspan,
    ])
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $waterColspan, 'variant' => 'water'])
</table>
