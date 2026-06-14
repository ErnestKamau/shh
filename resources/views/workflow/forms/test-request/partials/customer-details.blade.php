<table class="trf-table">
    <tr class="trf-banner-row">
        <td style="width: 50%;">CUSTOMER DETAILS</td>
        <td style="width: 50%;" class="trf-right">
            <span class="trf-field-label">JOB NUMBER:</span>
            <span class="trf-field-value">{{ $customer['job_number'] ?: '' }}</span>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="trf-field-label">Name:</span>
            <span class="trf-field-value">{{ $customer['customer_name'] ?: '' }}</span>
        </td>
    </tr>
    <tr>
        <td style="width: 50%;">
            <span class="trf-field-label">Address:</span>
            <span class="trf-field-value">{{ $customer['customer_address'] ?: '' }}</span>
        </td>
        <td style="width: 50%;">
            <span class="trf-field-label">Tel/ Fax No.:</span>
            <span class="trf-field-value">{{ $customer['customer_phone'] ?: '' }}</span>
        </td>
    </tr>
    <tr>
        <td>
            <span class="trf-field-label">Contact Person:</span>
            <span class="trf-field-value">{{ $customer['contact_person'] ?: '' }}</span>
        </td>
        <td>
            <span class="trf-field-label">Mobile Number:</span>
            <span class="trf-field-value">{{ $customer['mobile_number'] ?: '' }}</span>
        </td>
    </tr>
</table>
