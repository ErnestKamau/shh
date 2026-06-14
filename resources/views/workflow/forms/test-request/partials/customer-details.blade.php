<table class="trf-table">
    <tr>
        <td colspan="3" class="trf-section-title">CUSTOMER DETAILS</td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="trf-field-label">Name:</span>
            <span class="trf-field-value">{{ $customer['customer_name'] ?: '' }}</span>
        </td>
        <td rowspan="3" class="trf-job-cell" style="width: 22%;">
            <span class="trf-job-label">JOB NUMBER:</span><br>
            <span class="trf-field-value">{{ $customer['job_number'] ?: '' }}</span>
        </td>
    </tr>
    <tr>
        <td style="width: 39%;">
            <span class="trf-field-label">Address:</span>
            <span class="trf-field-value">{{ $customer['customer_address'] ?: '' }}</span>
        </td>
        <td style="width: 39%;">
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
