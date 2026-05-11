<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laboratory Analysis Acceptance Form</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1f2937; margin: 12px; }
        .header-table, .grid { width: 100%; border-collapse: collapse; }
        .header-table td, .grid th, .grid td { border: 1px solid #9ca3af; }
        .header-table td { padding: 6px; vertical-align: top; }
        .grid th, .grid td { padding: 4px 6px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .small { font-size: 9px; color: #4b5563; }
        .logo { max-height: 54px; max-width: 92px; }
        .section-title { font-weight: bold; text-transform: uppercase; margin: 8px 0 4px; font-size: 10px; }
        .line { border-bottom: 1px dotted #6b7280; min-height: 14px; display: inline-block; width: 100%; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 16%;" class="center">
                @if(!empty($logos['gcla']))
                    <img src="{{ $logos['gcla'] }}" class="logo" alt="GCLA Logo">
                @endif
            </td>
            <td style="width: 54%;" class="center">
                <div style="font-weight: bold;">Laboratory Analysis Acceptance Form</div>
                <div class="small">GCLA/F/03</div>
            </td>
            <td style="width: 30%;">
                <div class="small"><strong>Official Use only</strong></div>
                <div>Lab. No. <span class="line">{{ $payload['lab_no'] ?? '' }}</span></div>
            </td>
        </tr>
    </table>

    <div class="section-title">Part A: Sample Details</div>
    <table class="grid">
        <tr>
            <td style="width: 72%;">1. Name of customer: {{ $payload['customer_name'] ?? '' }}</td>
            <td style="width: 28%;">Date: {{ $payload['date'] ?? '' }}</td>
        </tr>
        <tr>
            <td>2. Address: {{ $payload['address'] ?? '' }}</td>
            <td>Tel: {{ $payload['tel'] ?? '' }}</td>
        </tr>
        <tr>
            <td>3. Email: {{ $payload['email'] ?? '' }}</td>
            <td></td>
        </tr>
        <tr>
            <td>4. Number of samples: {{ $payload['number_of_samples'] ?? '' }}</td>
            <td>Mode of work: {{ $payload['mode_of_work'] ?? 'Normal' }}</td>
        </tr>
        <tr>
            <td>5. Type of sample: {{ $payload['type_of_sample'] ?? '' }}</td>
            <td></td>
        </tr>
        <tr>
            <td>6. Date of sampling (if applicable): {{ $payload['date_of_sampling'] ?? '' }}</td>
            <td>Amount $ usd: {{ $payload['amount_usd'] ?? '' }}</td>
        </tr>
    </table>

    <table class="grid" style="margin-top: 5px;">
        <thead>
            <tr>
                <th style="width: 7%;">S/No</th>
                <th style="width: 43%;">Parameter(s) Requested</th>
                <th style="width: 20%; text-align: right;">Amount (USD)</th>
                <th style="width: 15%;">Accept (✓)</th>
                <th style="width: 15%;">Reject (x)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $rows = $payload['parameters'] ?? []; 
                $totalAmount = 0;
            @endphp
            @forelse($rows as $i => $row)
                @php $totalAmount += (float)($row['price'] ?? 0); @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $row['name'] ?? '' }}</td>
                    <td class="right">{{ isset($row['price']) ? number_format((float)$row['price'], 2) : '' }}</td>
                    <td class="center">{{ !empty($row['accepted']) ? '✓' : '' }}</td>
                    <td class="center">{{ !empty($row['rejected']) ? 'x' : '' }}</td>
                </tr>
            @empty
                @for($i = 0; $i < 3; $i++)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td></td>
                        <td class="right"></td>
                        <td class="center"></td>
                        <td class="center"></td>
                    </tr>
                @endfor
            @endforelse
            <tr style="font-weight: bold; background-color: #f3f4f6;">
                <td colspan="2" class="right"><strong>TOTAL</strong></td>
                <td class="right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 6px;">Any deviation from specified conditions: {{ $payload['deviation_answer'] ?? '' }}</div>

    <div class="section-title">Part B: Customer Certified</div>
    <div>I certified that the above request is correct.</div>
    <div style="margin-top: 8px;">Customer's Name: {{ $payload['customer_name_certified'] ?? '' }} &nbsp;&nbsp; Signature: {{ $payload['customer_signature_name'] ?? '' }} &nbsp;&nbsp; Date: {{ $payload['customer_date'] ?? '' }}</div>

    <div class="section-title">Part C: Conformity Assessment</div>
    <div>Customer requests / not requested a statement of conformity to a specification or standard: {{ $payload['conformity_request'] ?? '' }}</div>

    <div class="section-title">Part D: Laboratory Manager</div>
    <div>I certify that the laboratory has / has not capability and resources to meet customer requirements</div>
    <div style="margin-top: 8px;">Laboratory: {{ $payload['laboratory_name'] ?? '' }} &nbsp;&nbsp; Laboratory Manager name: {{ $payload['laboratory_manager_name'] ?? '' }}</div>
    <div style="margin-top: 8px;">Manager's Signature: {{ $payload['manager_signature_name'] ?? '' }} &nbsp;&nbsp; Date: {{ $payload['manager_date'] ?? '' }}</div>

    <div class="small" style="margin-top: 10px;">
        Rev: 03 &nbsp;&nbsp;&nbsp; Issued: 20-09-2024 &nbsp;&nbsp;&nbsp; Page 1 of 1
    </div>
</body>
</html>
