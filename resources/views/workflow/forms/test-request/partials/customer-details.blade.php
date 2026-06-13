<div class="trf-section-title">Customer Details</div>
<table class="trf-table">
    <tr>
        <td style="width: 18%;"><span class="trf-field-label">JOB NUMBER:</span></td>
        <td style="width: 82%;" colspan="3"><span class="trf-field-value">{{ $customer['job_number'] ?: '&nbsp;' }}</span></td>
    </tr>
    <tr>
        <td><span class="trf-field-label">Name:</span></td>
        <td style="width: 32%;"><span class="trf-field-value">{{ $customer['customer_name'] ?: '&nbsp;' }}</span></td>
        <td style="width: 18%;"><span class="trf-field-label">Address:</span></td>
        <td style="width: 32%;"><span class="trf-field-value">{{ $customer['customer_address'] ?: '&nbsp;' }}</span></td>
    </tr>
    <tr>
        <td><span class="trf-field-label">Tel/ Fax No.:</span></td>
        <td><span class="trf-field-value">{{ $customer['customer_phone'] ?: '&nbsp;' }}</span></td>
        <td><span class="trf-field-label">Contact Person:</span></td>
        <td><span class="trf-field-value">{{ $customer['contact_person'] ?: '&nbsp;' }}</span></td>
    </tr>
    <tr>
        <td><span class="trf-field-label">Mobile Number:</span></td>
        <td colspan="3"><span class="trf-field-value">{{ $customer['mobile_number'] ?: '&nbsp;' }}</span></td>
    </tr>
</table>
