@php
    $customerCols = (int) ($customerCols ?? 14);
    $jobCols = (int) ($jobCols ?? 4);
@endphp
<tr class="trf-banner-row">
    <td colspan="{{ $customerCols }}" class="trf-banner-cell">CUSTOMER DETAILS</td>
    <td rowspan="4" colspan="{{ $jobCols }}" class="trf-job-cell">
        <span class="trf-job-number-label">JOB NUMBER:</span>
        <span class="trf-job-number-value">{{ $customer['job_number'] ?: '' }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ $customerCols }}" class="trf-customer-field">
        <span class="trf-field-label">Name:</span>
        <span class="trf-field-value">{{ $customer['customer_name'] ?: '' }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ (int) floor($customerCols / 2) }}" class="trf-customer-field">
        <span class="trf-field-label">Address:</span>
        <span class="trf-field-value">{{ $customer['customer_address'] ?: '' }}</span>
    </td>
    <td colspan="{{ $customerCols - (int) floor($customerCols / 2) }}" class="trf-customer-field">
        <span class="trf-field-label">Tel/ Fax No.:</span>
        <span class="trf-field-value">{{ $customer['customer_phone'] ?: '' }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ (int) floor($customerCols / 2) }}" class="trf-customer-field">
        <span class="trf-field-label">Contact Person:</span>
        <span class="trf-field-value">{{ $customer['contact_person'] ?: '' }}</span>
    </td>
    <td colspan="{{ $customerCols - (int) floor($customerCols / 2) }}" class="trf-customer-field">
        <span class="trf-field-label">Mobile Number:</span>
        <span class="trf-field-value">{{ $customer['mobile_number'] ?: '' }}</span>
    </td>
</tr>
