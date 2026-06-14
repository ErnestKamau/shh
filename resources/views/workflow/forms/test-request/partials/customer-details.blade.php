<table class="trf-table trf-no-gap">
    <tr class="trf-banner-row">
        <td colspan="2">CUSTOMER DETAILS</td>
    </tr>
    <tr>
        <td class="trf-customer-left">
            <span class="trf-field-label">Name:</span>
            <span class="trf-field-value">{{ $customer['customer_name'] ?: '' }}</span>
        </td>
        <td class="trf-customer-right">
            <span class="trf-job-label">JOB NUMBER:</span>
            <span class="trf-field-value">{{ $customer['job_number'] ?: '' }}</span>
        </td>
    </tr>
    <tr>
        <td class="trf-customer-left">
            <span class="trf-field-label">Address:</span>
            <span class="trf-field-value">{{ $customer['customer_address'] ?: '' }}</span>
        </td>
        <td class="trf-customer-right">
            <span class="trf-field-label">Tel/ Fax No.:</span>
            <span class="trf-field-value">{{ $customer['customer_phone'] ?: '' }}</span>
        </td>
    </tr>
    <tr>
        <td class="trf-customer-left">
            <span class="trf-field-label">Contact Person:</span>
            <span class="trf-field-value">{{ $customer['contact_person'] ?: '' }}</span>
        </td>
        <td class="trf-customer-right">
            <span class="trf-field-label">Mobile Number:</span>
            <span class="trf-field-value">{{ $customer['mobile_number'] ?: '' }}</span>
        </td>
    </tr>
</table>
