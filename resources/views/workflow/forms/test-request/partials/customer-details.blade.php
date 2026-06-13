<div class="trf-section-title">Customer Details</div>
<table class="trf-table">
    <tr>
        <td style="width: 20%;"><span class="trf-field-label">JOB NUMBER:</span></td>
        <td style="width: 80%;" colspan="3">{{ $customer['job_number'] }}</td>
    </tr>
    <tr>
        <td><span class="trf-field-label">Name:</span></td>
        <td colspan="3">{{ $customer['customer_name'] }}</td>
    </tr>
    <tr>
        <td><span class="trf-field-label">Address:</span></td>
        <td colspan="3">{{ $customer['customer_address'] }}</td>
    </tr>
    <tr>
        <td><span class="trf-field-label">Tel/ Fax No.:</span></td>
        <td style="width: 30%;">{{ $customer['customer_phone'] }}</td>
        <td style="width: 20%;"><span class="trf-field-label">Contact Person:</span></td>
        <td style="width: 30%;">{{ $customer['contact_person'] }}</td>
    </tr>
    <tr>
        <td><span class="trf-field-label">Mobile Number:</span></td>
        <td colspan="3">{{ $customer['mobile_number'] }}</td>
    </tr>
</table>
