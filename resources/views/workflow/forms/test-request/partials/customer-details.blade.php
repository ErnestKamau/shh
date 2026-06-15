<table class="trf-table trf-no-gap trf-customer-table">
    @include('workflow.forms.test-request.partials.customer-detail-rows', [
        'customerCols' => $customerCols ?? 3,
        'jobCols' => $jobCols ?? 1,
    ])
</table>
