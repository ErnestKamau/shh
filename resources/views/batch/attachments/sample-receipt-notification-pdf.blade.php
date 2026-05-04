<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sample Receipt Notification (GCLA 01)</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #111827; }
        .title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 6px; }
        .subtitle { text-align: center; margin-bottom: 14px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .grid th, .grid td { border: 1px solid #9ca3af; padding: 6px; vertical-align: top; }
        .label { width: 36%; font-weight: bold; background: #f3f4f6; }
        .value { width: 64%; }
        .signature { max-width: 200px; max-height: 70px; }
    </style>
</head>
<body>
    <div class="title">Sample Receipt Notification</div>
    <div class="subtitle">GCLA 01 &middot; Batch {{ $batch->batch_code }}</div>

    <table class="grid">
        <tr>
            <td class="label">1. Name of the client or submitting authority</td>
            <td class="value">{{ $form['client_or_authority_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">2. Description of sample(s)</td>
            <td class="value">{{ $form['sample_description'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">3. Name of person submitting sample or exhibit</td>
            <td class="value">{{ $form['submitter_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">3. Designation</td>
            <td class="value">{{ $form['submitter_designation'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">3. Signature</td>
            <td class="value">
                @if(!empty($form['submitter_signature']))
                    <img src="{{ $form['submitter_signature'] }}" class="signature" alt="Submitter signature">
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">4. Laboratory Identification Number / Lab. No. (Batch No)</td>
            <td class="value">{{ $form['laboratory_identification_number'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">5. Number of Samples</td>
            <td class="value">{{ $form['number_of_samples'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">6. Name of receiving person</td>
            <td class="value">{{ $form['receiver_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">6. Designation</td>
            <td class="value">{{ $form['receiver_designation'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">6. Signature</td>
            <td class="value">
                @if(!empty($form['receiver_signature']))
                    <img src="{{ $form['receiver_signature'] }}" class="signature" alt="Receiver signature">
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">7. Sample receiving date</td>
            <td class="value">{{ $form['sample_receiving_date'] ?? '' }}</td>
        </tr>
    </table>
</body>
</html>
