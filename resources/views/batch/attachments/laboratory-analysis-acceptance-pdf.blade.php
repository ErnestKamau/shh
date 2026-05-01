<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laboratory Analysis Acceptance Form (GCLA/F/03)</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #111827; }
        .title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 6px; }
        .subtitle { text-align: center; margin-bottom: 14px; }
        .part-title { font-weight: bold; background: #e5e7eb; padding: 6px; margin-top: 12px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .grid th, .grid td { border: 1px solid #9ca3af; padding: 6px; vertical-align: top; }
        .label { width: 30%; font-weight: bold; background: #f3f4f6; }
        .value { width: 70%; }
        .tiny { font-size: 10px; color: #4b5563; }
        .check { font-weight: bold; }
        .signature { max-width: 200px; max-height: 70px; }
        .mt { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="title">Laboratory Analysis Acceptance Form</div>
    <div class="subtitle">GCLA/F/03 &middot; Batch {{ $batch->batch_code }}</div>

    <div class="part-title">Part A: Sample Details</div>
    <table class="grid">
        <tr><td class="label">Name of Customer</td><td class="value">{{ $form['customer_name'] ?? '' }}</td></tr>
        <tr><td class="label">Address</td><td class="value">{{ $form['customer_address'] ?? '' }}</td></tr>
        <tr><td class="label">Email</td><td class="value">{{ $form['customer_email'] ?? '' }}</td></tr>
        <tr><td class="label">Number of Samples</td><td class="value">{{ $form['number_of_samples'] ?? '' }}</td></tr>
        <tr><td class="label">Type of Samples</td><td class="value">{{ $form['type_of_samples'] ?? '' }}</td></tr>
        <tr><td class="label">Date of Sampling (if applicable)</td><td class="value">{{ $form['date_of_sampling'] ?? '' }}</td></tr>
        <tr><td class="label">Date</td><td class="value">{{ $form['date'] ?? '' }}</td></tr>
        <tr><td class="label">Tel</td><td class="value">{{ $form['tel'] ?? '' }}</td></tr>
        <tr><td class="label">Mode of Work</td><td class="value">{{ $form['mode_of_work'] ?? '' }}</td></tr>
    </table>

    <table class="grid mt">
        <thead>
            <tr>
                <th style="width: 45%;">Parameter(s) Requested</th>
                <th style="width: 27.5%;">Accepted</th>
                <th style="width: 27.5%;">Rejected</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requestedParameters as $row)
                <tr>
                    <td>{{ $row['label'] ?? '' }}</td>
                    <td>
                        @if(!empty($row['selected']))
                            TZS {{ number_format((float) ($row['price'] ?? 0), 2) }}
                        @endif
                    </td>
                    <td>
                        @if(empty($row['selected']))
                            TZS {{ number_format((float) ($row['price'] ?? 0), 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr>
                <td style="text-align:right;"><strong>Total Accepted</strong></td>
                <td><strong>TZS {{ number_format((float) $acceptedTotal, 2) }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="grid mt">
        <tr>
            <td class="label">Any deviation from specified conditions?</td>
            <td class="value">{{ $form['deviation_answer'] ?? '' }}</td>
        </tr>
    </table>

    <div class="part-title">Part B: Customer Certified</div>
    <table class="grid">
        <tr><td class="label">Statement</td><td class="value">{{ $form['customer_certification_text'] ?? '' }}</td></tr>
        <tr><td class="label">Customer Name</td><td class="value">{{ $form['customer_name_certified'] ?? '' }}</td></tr>
        <tr><td class="label">Signature</td><td class="value">@if(!empty($form['customer_signature']))<img src="{{ $form['customer_signature'] }}" class="signature" alt="Customer signature">@endif</td></tr>
        <tr><td class="label">Date</td><td class="value">{{ $form['customer_date'] ?? '' }}</td></tr>
    </table>

    <div class="part-title">Part C: Conformity Assessment</div>
    <table class="grid">
        <tr>
            <td class="label">Customer requests statement of conformity</td>
            <td class="value">{{ $form['conformity_request'] === 'requested' ? 'Requested' : 'Not Requested' }}</td>
        </tr>
    </table>

    <div class="part-title">Part D: Laboratory Manager</div>
    <table class="grid">
        <tr><td class="label">Capability and resources</td><td class="value">{{ $form['manager_capability'] === 'has' ? 'Laboratory has capability and resources' : 'Laboratory has not capability and resources' }}</td></tr>
        <tr><td class="label">Laboratory</td><td class="value">{{ $form['laboratory_name'] ?? '' }}</td></tr>
        <tr><td class="label">Laboratory Manager Name</td><td class="value">{{ $form['laboratory_manager_name'] ?? '' }}</td></tr>
        <tr><td class="label">Manager Signature</td><td class="value">@if(!empty($form['manager_signature']))<img src="{{ $form['manager_signature'] }}" class="signature" alt="Manager signature">@endif</td></tr>
        <tr><td class="label">Date</td><td class="value">{{ $form['manager_date'] ?? '' }}</td></tr>
    </table>
</body>
</html>
