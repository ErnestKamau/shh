<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sample Receiving Disclaimer Form</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #111827; line-height: 1.45; }
        .title { font-size: 15px; font-weight: bold; text-align: center; margin-bottom: 4px; text-transform: uppercase; }
        .subtitle { text-align: center; margin-bottom: 14px; font-size: 10px; color: #475569; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .grid th, .grid td { border: 1px solid #9ca3af; padding: 5px 6px; vertical-align: top; }
        .label { width: 32%; font-weight: bold; background: #f3f4f6; }
        .legal { border: 1px solid #cbd5e1; padding: 10px; margin: 12px 0; background: #f8fafc; text-align: justify; }
        .sign-grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .sign-grid th, .sign-grid td { border: 1px solid #9ca3af; padding: 6px; }
        .sign-grid th { background: #f3f4f6; font-weight: bold; }
        .signature { max-width: 180px; max-height: 60px; }
        .note { margin-top: 14px; font-size: 10px; font-style: italic; color: #475569; }
    </style>
</head>
<body>
    <div class="title">Sample Receiving Disclaimer Form</div>
    <div class="subtitle">Batch {{ $batch->batch_code ?? '' }}</div>

    <table class="grid">
        <tr>
            <td class="label">Name of client</td>
            <td>{{ $form['client_name'] ?? '' }}</td>
            <td class="label">LAB NO / Sample ID</td>
            <td>{{ $form['lab_no'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td>{{ $form['date'] ?? '' }}</td>
            <td class="label">Time</td>
            <td>{{ $form['time'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Types of sample</td>
            <td colspan="3">{{ $form['sample_types'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Number of sample</td>
            <td colspan="3">{{ $form['number_of_samples'] ?? '' }}</td>
        </tr>
    </table>

    <div class="legal">
        {{ \App\Services\Sampleworkflow\SampleReceivingDisclaimerService::INTEGRITY_STATEMENT_PREFIX }}
        <strong>{{ $form['disclaimant_name'] ?? '' }}</strong>
        {{ \App\Services\Sampleworkflow\SampleReceivingDisclaimerService::INTEGRITY_STATEMENT_SUFFIX }}
    </div>

    <table class="sign-grid">
        <thead>
            <tr>
                <th style="width: 40%;">Name</th>
                <th style="width: 40%;">Signature</th>
                <th style="width: 20%;">Date</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $form['claimant_name'] ?? '' }}</td>
                <td>
                    @if(!empty($form['claimant_signature']))
                        <img src="{{ $form['claimant_signature'] }}" class="signature" alt="Claimant signature">
                    @endif
                </td>
                <td>{{ $form['claimant_signed_at'] ?? '' }}</td>
            </tr>
            <tr>
                <td>{{ $form['analyst_name'] ?? '' }}<br><small>Laboratory analyst</small></td>
                <td>
                    @if(!empty($form['analyst_signature']))
                        <img src="{{ $form['analyst_signature'] }}" class="signature" alt="Analyst signature">
                    @endif
                </td>
                <td>{{ $form['analyst_signed_at'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    <p class="note">{{ \App\Services\Sampleworkflow\SampleReceivingDisclaimerService::COA_FOOTER_NOTE }}</p>
</body>
</html>
