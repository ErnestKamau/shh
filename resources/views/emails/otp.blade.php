<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $app_name ?? config('app.name', 'Application') }} OTP Verification</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);padding:28px 32px;">
                            <p style="margin:0;color:#bfdbfe;font-size:12px;letter-spacing:1.2px;text-transform:uppercase;">Security Verification</p>
                            <h1 style="margin:8px 0 0 0;color:#ffffff;font-size:24px;line-height:1.3;font-weight:700;">One-Time Passcode</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px 14px 32px;">
                            <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;">Hello {{ $first_name ?? 'User' }},</p>
                            <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#4b5563;">
                                Use the verification code below to complete your sign-in to
                                <strong style="color:#111827;">{{ $app_name ?? config('app.name', 'Application') }}</strong>.
                            </p>
                            <div style="margin:0 0 18px 0;padding:18px;background:#f8fafc;border:1px dashed #93c5fd;border-radius:10px;text-align:center;">
                                <p style="margin:0 0 8px 0;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#64748b;">Your Verification Code</p>
                                <p style="margin:0;font-size:34px;line-height:1;font-weight:700;letter-spacing:6px;color:#0f172a;">{{ $otp_code ?? '------' }}</p>
                            </div>
                            <p style="margin:0 0 8px 0;font-size:14px;line-height:1.7;color:#4b5563;">
                                This code expires in <strong>{{ (int) ($expires_in_minutes ?? 30) }} minutes</strong>.
                            </p>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#4b5563;">
                                If you did not request this code, please ignore this email and contact your administrator.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px 28px 32px;">
                            <p style="margin:0;padding-top:14px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:12px;line-height:1.6;">
                                This is an automated security email from {{ $app_name ?? config('app.name', 'Application') }}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
