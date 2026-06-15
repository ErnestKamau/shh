<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #7a1f2b; padding: 4px; vertical-align: top; }
        .title { text-align: center; font-weight: bold; font-size: 14px; margin: 8px 0; }
        .section-head { background: #7a1f2b; color: #fff; font-weight: bold; padding: 4px; }
        .no-border td { border: none; }
        .lab-only { border: 2px solid #7a1f2b; padding: 8px; min-height: 80px; }
    </style>
</head>
<body>
    @include('layouts.lab.invoice.partials.amspec-quotation-header', [
        'company' => $company,
        'logo_path' => $logo_path ?? null,
        'logo_secondary_path' => $logo_secondary_path ?? null,
    ])

    <div class="title">{{ $document_title }}</div>
    <table class="no-border" style="margin-bottom: 6px;">
        <tr>
            <td><strong>S. No.</strong> {{ $form_number }}</td>
            <td style="text-align: right;"><strong>JOB NUMBER:</strong> {{ $job_number }}</td>
        </tr>
    </table>

    <div class="section-head">CUSTOMER DETAILS</div>
    <table>
        <tr>
            <td width="50%"><strong>Name:</strong> {{ $customer_name }}</td>
            <td><strong>Address:</strong> {{ $customer_address }}</td>
        </tr>
        <tr>
            <td><strong>Tel/Fax No.:</strong> {{ $customer_tel_fax }}</td>
            <td><strong>Mobile Number:</strong> {{ $customer_mobile }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Contact Person:</strong> {{ $contact_person }}</td>
        </tr>
    </table>

    <div class="section-head" style="margin-top: 8px;">SAMPLE COLLECTION DATA</div>
    <table>
        <tr>
            <td width="25%"><strong>Sampling Date:</strong> {{ $collection['sampling_date'] }}</td>
            <td width="25%"><strong>Sampling Time:</strong> {{ $collection['sampling_time'] }}</td>
            <td colspan="2"><strong>Sampling Location:</strong> {{ $collection['sampling_location'] }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Sampling Apparatus:</strong> {{ $collection['sampling_apparatus'] }}</td>
            <td colspan="2"><strong>Method:</strong> {{ $collection['method_of_sampling'] }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Reason:</strong> {{ $collection['reason_of_collection'] }}</td>
            <td colspan="2"><strong>Transport:</strong> {{ $collection['transport_condition'] }}</td>
        </tr>
    </table>

    <table style="margin-top: 8px;">
        <thead>
            <tr>
                <th>S.No.</th>
                <th>Sample No.</th>
                <th>Sample Description</th>
                <th>Sampling Point</th>
                <th>Qty</th>
                <th>Sample Type</th>
                <th>Parameters</th>
                <th>Temp (°C)</th>
                <th>Prod. Date</th>
                <th>Exp. Date</th>
                <th>Batch</th>
                <th>State</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sample_lines as $index => $line)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $line['customer_sample_id'] ?? '' }}</td>
                    <td>{{ $line['sample_description'] ?? '' }}</td>
                    <td>{{ $line['sampling_point'] ?? '' }}</td>
                    <td>{{ $line['number_of_samples'] ?? '' }}</td>
                    <td>{{ $line['analysis_type_name'] ?? '' }}</td>
                    <td>{{ $line['parameter_label'] ?? '' }}</td>
                    <td>{{ $line['attributes']['field_sample_temp'] ?? '' }}</td>
                    <td>{{ $line['production_date'] ?? '' }}</td>
                    <td>{{ $line['expiration_date'] ?? '' }}</td>
                    <td>{{ $line['batch_number'] ?? '' }}</td>
                    <td>{{ $line['state_of_sample'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="12">&nbsp;</td></tr>
            @endforelse
        </tbody>
    </table>

    <table style="margin-top: 8px;">
        <tr>
            <td width="50%"><strong>Statement of Conformity:</strong> {{ $sign['statement_of_conformity'] }}</td>
            <td><strong>Sampled By:</strong> {{ $sign['sampled_by'] }}</td>
        </tr>
        <tr>
            <td><strong>Customer Representative:</strong> {{ $sign['customer_representative_name'] }}</td>
            <td><strong>Contact:</strong> {{ $sign['customer_representative_contact'] }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Remarks:</strong> {{ $sign['remarks'] }}</td>
        </tr>
    </table>

    <table style="margin-top: 8px;">
        <tr>
            <td width="70%">&nbsp;</td>
            <td class="lab-only">
                <strong>FOR LAB USE ONLY</strong><br>
                Received Date &amp; Time:<br><br>
                Received by:<br><br>
                Sample Condition:
            </td>
        </tr>
    </table>

    <p style="margin-top: 12px; font-size: 8px;">{{ $document_code }} - Test Request Form - Food - V0</p>
</body>
</html>
