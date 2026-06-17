@php
    $customerCols = (int) ($customerCols ?? 9);
    $jobCols = (int) ($jobCols ?? 3);
    $leftSplitA = (int) ($leftSplitA ?? 4);
    $leftSplitB = $customerCols - $leftSplitA;
    $sampleNumber = (string) ($wasteWaterFields['sample_number'] ?? '');
@endphp
<tr class="trf-banner-row trf-ww-customer-banner">
    <td colspan="12" class="trf-banner-cell">CUSTOMER DETAILS</td>
</tr>
<tr class="trf-ww-customer-row">
    <td colspan="{{ $customerCols }}" class="trf-customer-field trf-ww-anchor-cell">
        <span class="trf-field-label">Name:</span>
        <span class="trf-field-value">{{ $customer['customer_name'] ?: '' }}</span>
    </td>
    <td rowspan="3" colspan="{{ $jobCols }}" class="trf-job-cell trf-ww-job-sample-cell">
        <div class="trf-ww-job-half">
            <span class="trf-job-number-label">JOB NUMBER:</span>
            <span class="trf-job-number-value">{{ $customer['job_number'] ?: '' }}</span>
        </div>
        <div class="trf-ww-sample-half">
            <span class="trf-job-number-label">SAMPLE NUMBER:</span>
            <span class="trf-job-number-value">{{ $sampleNumber }}</span>
        </div>
    </td>
</tr>
<tr class="trf-ww-customer-row">
    <td colspan="{{ $leftSplitA }}" class="trf-customer-field trf-ww-anchor-cell">
        <span class="trf-field-label">Address:</span>
        <span class="trf-field-value">{{ $customer['customer_address'] ?: '' }}</span>
    </td>
    <td colspan="{{ $leftSplitB }}" class="trf-customer-field trf-ww-anchor-cell">
        <span class="trf-field-label">Tel/ Fax No.:</span>
        <span class="trf-field-value">{{ $customer['customer_phone'] ?: '' }}</span>
    </td>
</tr>
<tr class="trf-ww-customer-row">
    <td colspan="{{ $leftSplitA }}" class="trf-customer-field trf-ww-anchor-cell">
        <span class="trf-field-label">Contact Person:</span>
        <span class="trf-field-value">{{ $customer['contact_person'] ?: '' }}</span>
    </td>
    <td colspan="{{ $leftSplitB }}" class="trf-customer-field trf-ww-anchor-cell">
        <span class="trf-field-label">Mobile Number:</span>
        <span class="trf-field-value">{{ $customer['mobile_number'] ?: '' }}</span>
    </td>
</tr>
