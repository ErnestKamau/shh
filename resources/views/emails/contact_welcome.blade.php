<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to {{ $companyName }} Portal</title>
    <style type="text/css">
        body { margin: 0; padding: 0; background-color: #f5f2f3; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        table { border-collapse: collapse; mso-table-lspace: 0; mso-table-rspace: 0; }
        img { border: 0; outline: none; display: block; }
        a { color: #7a1f2b; text-decoration: none; }
        @media only screen and (max-width: 620px) {
            .em_main { width: 100% !important; }
            .em_pad { padding: 0 16px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f5f2f3;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f2f3;">
    <tr>
        <td align="center" style="padding:40px 16px;">

            <table class="em_main" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="background-color:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 28px rgba(74,21,32,0.10);">

                {{-- Header --}}
                <tr>
                    <td style="background:#7a1f2b;padding:28px 40px;text-align:center;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $companyName }}" height="48"
                                 style="max-height:48px;max-width:200px;object-fit:contain;margin:0 auto 12px auto;" />
                        @endif
                        <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.2px;">
                            {{ $companyName }}
                        </p>
                        <p style="margin:8px 0 0;font-size:11px;font-weight:600;color:#f0d5da;letter-spacing:1.2px;text-transform:uppercase;">
                            Client Portal
                        </p>
                    </td>
                </tr>

                {{-- Intro --}}
                <tr>
                    <td style="padding:36px 40px 0;text-align:center;">
                        <h1 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#2b2426;line-height:1.3;">
                            Welcome, {{ $fullName }}
                        </h1>
                        <p style="margin:0;font-size:15px;color:#6b6467;line-height:1.7;">
                            Your client portal account is ready.<br/>
                            Use the credentials below to sign in and get started.
                        </p>
                    </td>
                </tr>

                {{-- Credentials --}}
                <tr>
                    <td style="padding:28px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color:#fbf7f8;border:1px solid #e8d5d9;border-radius:12px;overflow:hidden;">
                            <tr>
                                <td style="background:#f7eef0;padding:12px 24px;border-bottom:1px solid #e8d5d9;">
                                    <p style="margin:0;font-size:11px;font-weight:700;color:#7a1f2b;letter-spacing:0.8px;text-transform:uppercase;">
                                        Your login credentials
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:20px 24px;">
                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:0.8px;text-transform:uppercase;">
                                        Username / Email
                                    </p>
                                    <p style="margin:0 0 18px;font-size:15px;font-weight:600;color:#2b2426;word-break:break-all;">
                                        {{ $emailAddress }}
                                    </p>

                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:0.8px;text-transform:uppercase;">
                                        Temporary Password
                                    </p>
                                    <p style="margin:0;display:inline-block;background:#2b2426;color:#f0d5da;font-size:16px;font-weight:700;letter-spacing:1px;font-family:Courier New, Courier, monospace;padding:10px 14px;border-radius:8px;">
                                        {{ $plainPassword }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- CTA --}}
                <tr>
                    <td style="padding:28px 40px 0;text-align:center;">
                        <a href="{{ $loginUrl }}"
                           style="display:inline-block;background:#7a1f2b;color:#ffffff;font-size:15px;font-weight:700;padding:14px 36px;border-radius:10px;text-decoration:none;letter-spacing:0.2px;">
                            Access the Portal &rarr;
                        </a>
                        <p style="margin:12px 0 0;font-size:12px;color:#94a3b8;word-break:break-all;">
                            {{ $loginUrl }}
                        </p>
                    </td>
                </tr>

                {{-- What you can do --}}
                <tr>
                    <td style="padding:28px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color:#fbf7f8;border:1px solid #ebe4e6;border-radius:12px;">
                            <tr>
                                <td style="padding:18px 24px;">
                                    <p style="margin:0 0 12px;font-size:11px;font-weight:700;color:#7a1f2b;letter-spacing:0.6px;text-transform:uppercase;">
                                        What you can do on the portal
                                    </p>
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td width="50%" valign="top" style="padding:0 10px 10px 0;font-size:13px;color:#334155;line-height:1.5;">
                                                &#10003;&nbsp; View and track sample submissions
                                            </td>
                                            <td width="50%" valign="top" style="padding:0 0 10px 0;font-size:13px;color:#334155;line-height:1.5;">
                                                &#10003;&nbsp; Download reports and certificates
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%" valign="top" style="padding:0 10px 0 0;font-size:13px;color:#334155;line-height:1.5;">
                                                &#10003;&nbsp; View invoices and payment status
                                            </td>
                                            <td width="50%" valign="top" style="font-size:13px;color:#334155;line-height:1.5;">
                                                &#10003;&nbsp; Communicate with our team
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Notices --}}
                <tr>
                    <td style="padding:28px 40px 0;">
                        <p style="margin:0 0 14px;font-size:11px;font-weight:700;color:#2b2426;text-transform:uppercase;letter-spacing:0.6px;">
                            Important
                        </p>
                        <p style="margin:0 0 12px;font-size:14px;color:#334155;line-height:1.6;">
                            <strong style="color:#2b2426;">Change your password after first login.</strong><br/>
                            The temporary password above was set by an administrator. Update it to a strong personal password as soon as you sign in.
                        </p>
                        <p style="margin:0;font-size:14px;color:#334155;line-height:1.6;">
                            <strong style="color:#2b2426;">90-day password policy.</strong><br/>
                            {{ $companyName }} requires portal users to rotate passwords every 90 days.
                        </p>
                    </td>
                </tr>

                {{-- Security --}}
                <tr>
                    <td style="padding:20px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color:#fff7ed;border:1px solid #fed7aa;border-radius:10px;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                                        <strong>Security reminder:</strong>
                                        Never share your login credentials. {{ $companyName }} staff will never ask for your password by email or phone.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Support --}}
                <tr>
                    <td style="padding:24px 40px 0;text-align:center;">
                        <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.6;">
                            Having trouble logging in? Contact your {{ $companyName }} account representative.
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:28px 40px 32px;text-align:center;background-color:#fbf7f8;border-top:1px solid #ebe4e6;">
                        <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#2b2426;">
                            {{ $companyName }}
                        </p>
                        <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
                            This is an automated message — please do not reply directly to this email.
                        </p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
