<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Report — {{ $reportNumber }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background:#f5f5f5; margin:0; padding:20px; color:#333; }
        .wrap { max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.1); }
        .header { background:#8B1A1A; color:#fff; padding:28px 32px; }
        .header h1 { margin:0; font-size:20px; letter-spacing:.5px; }
        .header p { margin:6px 0 0; font-size:13px; opacity:.85; }
        .body { padding:28px 32px; }
        .body p { line-height:1.6; font-size:14px; margin:0 0 16px; }
        .report-card { background:#fdf4f4; border:1px solid #e8c0c0; border-radius:6px; padding:16px 20px; margin:20px 0; }
        .report-card .label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#8B1A1A; font-weight:700; margin-bottom:4px; }
        .report-card .value { font-size:16px; font-weight:700; color:#111; }
        .cta { display:inline-block; margin:20px 0 4px; background:#8B1A1A; color:#fff !important; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:700; }
        .footer { background:#f9f9f9; border-top:1px solid #eee; padding:18px 32px; font-size:11px; color:#999; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <p>Test Report — Ready for download</p>
    </div>
    <div class="body">
        <p>Dear {{ $contactName }},</p>
        <p>Your Test Report has been processed and is ready. Please find the details below.</p>

        <div class="report-card">
            <div class="label">Report Number</div>
            <div class="value">{{ $reportNumber }}</div>
        </div>

        @if($notes)
        <p><strong>Revision Notes:</strong> {{ $notes }}</p>
        @endif

        @if($downloadUrl)
        <a href="{{ $downloadUrl }}" class="cta" target="_blank">View / Download Report</a>
        @endif

        <p style="font-size:12px;color:#999;margin-top:24px;">
            If you are unable to click the button, copy this link into your browser:<br>
            <span style="color:#8B1A1A;">{{ $downloadUrl ?? 'Report link not available' }}</span>
        </p>
    </div>
    <div class="footer">
        This is an automated message from {{ $companyName }}. Please do not reply directly to this email.
    </div>
</div>
</body>
</html>
