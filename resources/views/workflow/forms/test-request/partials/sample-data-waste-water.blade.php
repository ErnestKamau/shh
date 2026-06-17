@php
    $wwColspan = 12;
@endphp
<table class="trf-table trf-no-gap trf-waste-water-table">
    <colgroup>
        <col span="4" class="trf-ww-col-details">
        <col span="4" class="trf-ww-col-apparatus">
        <col span="4" class="trf-ww-col-method">
    </colgroup>
    <tbody class="trf-ww-customer-tbody">
        @include('workflow.forms.test-request.partials.customer-detail-rows-waste-water', [
            'customerCols' => 9,
            'jobCols' => 3,
            'leftSplitA' => 4,
        ])
    </tbody>
    <tbody class="trf-ww-body-tbody">
    <tr class="trf-banner-row">
        <td colspan="{{ $wwColspan }}">SAMPLE COLLECTION DATA</td>
    </tr>
    @include('workflow.forms.test-request.partials.waste-water-collection-grid', [
        'detailsColspan' => 4,
        'apparatusColspan' => 4,
        'methodColspan' => 4,
    ])
    @include('workflow.forms.test-request.partials.waste-water-field-grid', [
        'fieldColspan' => 3,
    ])
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $wwColspan, 'variant' => 'waste_water'])
    </tbody>
</table>
