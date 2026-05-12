<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sample Rejection Form</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1f2937; margin: 12px; }
        .header-table, .grid { width: 100%; border-collapse: collapse; }
        .header-table td, .grid th, .grid td { border: 1px solid #9ca3af; }
        .header-table td { padding: 6px; vertical-align: top; }
        .grid th, .grid td { padding: 4px 6px; vertical-align: top; }
        .center { text-align: center; }
        .logo { max-height: 56px; max-width: 95px; }
        .small { font-size: 9px; color: #4b5563; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 14%;" class="center">
                @if(!empty($logos['tanzania']))
                    <img src="{{ $logos['tanzania'] }}" class="logo" alt="Tanzania Logo">
                @endif
            </td>
            <td style="width: 72%;" class="center">
                <div style="font-weight: bold;">GOVERNMENT CHEMIST LABORATORY AUTHORITY</div>
                <div style="font-weight: bold;">SAMPLE REJECTION FORM</div>
                <div>QARM/F/01</div>
            </td>
            <td style="width: 14%;" class="center">
                @if(!empty($logos['gcla']))
                    <img src="{{ $logos['gcla'] }}" class="logo" alt="GCLA Logo">
                @endif
            </td>
        </tr>
    </table>

    <table class="grid" style="margin-top: 6px;">
        <tr><td style="width: 40%;">Sample ID:</td><td>{{ $payload['sample_id'] ?? '' }}</td></tr>
        <tr><td>Name client:</td><td>{{ $payload['name_of_client'] ?? '' }}</td></tr>
        <tr><td>Date sample(s) received/collected:</td><td>{{ $payload['date_sample_received'] ?? '' }}</td></tr>
        <tr><td>Date of sample(s):</td><td>{{ $payload['date_of_sample_collection'] ?? '' }}</td></tr>
        <tr><td>Number of samples received:</td><td>{{ $payload['number_of_samples_received'] ?? '' }}</td></tr>
    </table>

    <table class="grid" style="margin-top: 6px;">
        <thead>
            <tr>
                <th style="width: 43%;">Reason</th>
                <th style="width: 57%;">Explanation</th>
            </tr>
        </thead>
        <tbody>
            @php
                $selectedReasons = collect($payload['reasons'] ?? []);
                $explanation = $payload['explanation'] ?? '';
                $reasonRows = [
                    'Sample was collected in improper container',
                    'Sample not properly sealed was leaking',
                    'Sample was stored in appropriate storage conditions',
                    'Sample was inappropriate treated after sampling prior analysis',
                    'Sample was improperly labeled and date of collection was not clear',
                    'Sample material was inappropriate for the test(s) requested',
                    'Sample volume /weight was inappropriate for the test(s) requested',
                    'Sample was not accompanied by a request form/sample could not be related to a request form',
                    'Sample name/date of collection on request form did not match the same details on the sample label',
                    'Other',
                ];
            @endphp
            @foreach($reasonRows as $reasonRow)
                <tr>
                    <td>{{ $selectedReasons->contains($reasonRow) ? '☑' : '☐' }} {{ $reasonRow }}</td>
                    <td>{{ $selectedReasons->contains($reasonRow) ? $explanation : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 10px;">You are advised to collect another sample from source (if still possible) and resend this to us. We apologize for any inconvenience this has caused.</div>

    <table class="grid" style="margin-top: 16px;">
        <tr>
            <td style="width: 33%;" class="center"><strong>LABORATORY STAFF</strong><br>{{ $payload['laboratory_staff'] ?? '' }}</td>
            <td style="width: 33%;" class="center"><strong>SIGNATURE</strong><br>{{ $payload['signature_name'] ?? '' }}</td>
            <td style="width: 33%;" class="center"><strong>DATE</strong><br>{{ $payload['date'] ?? '' }}</td>
        </tr>
    </table>

    <div class="small" style="margin-top: 10px;">Generated from workflow request form records.</div>
</body>
</html>
