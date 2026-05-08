<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to {{ $companyName }} Portal</title>
    <style type="text/css">
        body { margin: 0; padding: 0; background-color: #f0f4f8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        table { border-collapse: collapse; mso-table-lspace: 0; mso-table-rspace: 0; }
        img { border: 0; outline: none; display: block; }
        a { color: #059669; text-decoration: none; }
        @media only screen and (max-width: 620px) {
            .em_main { width: 100% !important; }
            .em_pad { padding: 0 16px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;">

<!-- Wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;">
    <tr>
        <td align="center" style="padding:40px 16px;">

            <!-- Card -->
            <table class="em_main" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(2,6,23,0.10);">

                <!-- Header bar -->
                <tr>
                    <td style="background:linear-gradient(135deg,#10b981 0%,#059669 55%,#047857 100%);padding:32px 40px;text-align:center;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $companyName }}" height="52"
                                 style="max-height:52px;max-width:220px;object-fit:contain;margin-bottom:12px;" />
                            <br/>
                        @endif
                        <span style="font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">
                            {{ $companyName }}
                        </span>
                        <br/>
                        <span style="font-size:12px;color:#a7f3d0;font-weight:500;letter-spacing:1px;text-transform:uppercase;margin-top:4px;display:inline-block;">
                            Client Portal
                        </span>
                    </td>
                </tr>

                <!-- Hero icon + title -->
                <tr>
                    <td style="padding:36px 40px 0;text-align:center;">
                        <div style="display:inline-block;width:68px;height:68px;background:#ecfdf5;border-radius:50%;line-height:68px;font-size:30px;margin-bottom:20px;border:2px solid #a7f3d0;">
                            &#128273;
                        </div>
                        <h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#0f172a;line-height:1.3;">
                            Welcome, {{ $fullName }}!
                        </h1>
                        <p style="margin:0;font-size:15px;color:#64748b;line-height:1.7;">
                            Your client portal account has been created and is ready.<br/>
                            Use the credentials below to sign in and get started.
                        </p>
                    </td>
                </tr>

                <!-- Credentials box -->
                <tr>
                    <td style="padding:28px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color:#f8fafc;border:1px solid #d1fae5;border-radius:12px;overflow:hidden;">
                            <!-- Box header -->
                            <tr>
                                <td style="background:linear-gradient(180deg,#ecfdf5 0%,#f8fafc 100%);padding:12px 24px;border-bottom:1px solid #d1fae5;">
                                    <p style="margin:0;font-size:11px;font-weight:700;color:#059669;letter-spacing:0.8px;text-transform:uppercase;">
                                        &#128273;&nbsp; Your Login Credentials
                                    </p>
                                </td>
                            </tr>
                            <!-- Credentials -->
                            <tr>
                                <td style="padding:20px 24px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td width="50%" style="padding-bottom:18px;">
                                                <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:0.8px;text-transform:uppercase;">
                                                    Username / Email
                                                </p>
                                                <p style="margin:0;font-size:15px;font-weight:600;color:#0f172a;word-break:break-all;">
                                                    {{ $emailAddress }}
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:0.8px;text-transform:uppercase;">
                                                    Temporary Password
                                                </p>
                                                <p style="margin:0;display:inline-block;background:#0f172a;color:#34d399;font-size:19px;font-weight:700;letter-spacing:2px;font-family:Courier New, Courier, monospace;padding:8px 16px;border-radius:8px;">
                                                    {{ $plainPassword }}
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- CTA button -->
                <tr>
                    <td style="padding:28px 40px 0;text-align:center;">
                        <a href="{{ $loginUrl }}"
                           style="display:inline-block;background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#ffffff;font-size:15px;font-weight:700;padding:15px 40px;border-radius:10px;text-decoration:none;letter-spacing:0.2px;box-shadow:0 4px 14px rgba(5,150,105,0.30);">
                            Access the Portal &rarr;
                        </a>
                    </td>
                </tr>

                <!-- What you can do -->
                <tr>
                    <td style="padding:28px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <tr>
                                <td style="padding:18px 24px;">
                                    <p style="margin:0 0 12px;font-size:12px;font-weight:700;color:#059669;letter-spacing:0.6px;text-transform:uppercase;">
                                        What you can do on the portal
                                    </p>
                                    <!-- Feature row -->
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td width="50%" valign="top" style="padding-bottom:10px;padding-right:12px;">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td width="22" valign="top" style="padding-top:1px;">
                                                            <span style="display:inline-block;width:18px;height:18px;background:#d1fae5;border-radius:4px;text-align:center;line-height:18px;font-size:11px;">&#10003;</span>
                                                        </td>
                                                        <td style="padding-left:8px;font-size:13px;color:#334155;line-height:1.5;">
                                                            View and track your sample submissions
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td width="50%" valign="top" style="padding-bottom:10px;">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td width="22" valign="top" style="padding-top:1px;">
                                                            <span style="display:inline-block;width:18px;height:18px;background:#d1fae5;border-radius:4px;text-align:center;line-height:18px;font-size:11px;">&#10003;</span>
                                                        </td>
                                                        <td style="padding-left:8px;font-size:13px;color:#334155;line-height:1.5;">
                                                            Download reports and certificates
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%" valign="top" style="padding-right:12px;">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td width="22" valign="top" style="padding-top:1px;">
                                                            <span style="display:inline-block;width:18px;height:18px;background:#d1fae5;border-radius:4px;text-align:center;line-height:18px;font-size:11px;">&#10003;</span>
                                                        </td>
                                                        <td style="padding-left:8px;font-size:13px;color:#334155;line-height:1.5;">
                                                            View invoices and payment status
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td width="50%" valign="top">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td width="22" valign="top" style="padding-top:1px;">
                                                            <span style="display:inline-block;width:18px;height:18px;background:#d1fae5;border-radius:4px;text-align:center;line-height:18px;font-size:11px;">&#10003;</span>
                                                        </td>
                                                        <td style="padding-left:8px;font-size:13px;color:#334155;line-height:1.5;">
                                                            Communicate directly with our team
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td style="padding:28px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="border-top:1px solid #e2e8f0;font-size:1px;line-height:1px;">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Important notices -->
                <tr>
                    <td style="padding:24px 40px 0;">
                        <p style="margin:0 0 14px;font-size:11px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.6px;">
                            Important — Please Read
                        </p>

                        <!-- Notice 1: Change password immediately -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:12px;">
                            <tr>
                                <td width="40" valign="top" style="padding-top:2px;">
                                    <div style="width:32px;height:32px;background:#fef3c7;border-radius:8px;text-align:center;line-height:32px;font-size:16px;">
                                        &#128274;
                                    </div>
                                </td>
                                <td style="padding-left:12px;">
                                    <p style="margin:0;font-size:14px;color:#334155;line-height:1.6;">
                                        <strong style="color:#0f172a;">Change your password immediately after first login.</strong><br/>
                                        The temporary password above was set by an administrator. For your security, please update it to a strong, personal password as soon as you sign in.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Notice 2: 90-day expiry policy -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="40" valign="top" style="padding-top:2px;">
                                    <div style="width:32px;height:32px;background:#dbeafe;border-radius:8px;text-align:center;line-height:32px;font-size:16px;">
                                        &#128197;
                                    </div>
                                </td>
                                <td style="padding-left:12px;">
                                    <p style="margin:0;font-size:14px;color:#334155;line-height:1.6;">
                                        <strong style="color:#0f172a;">90-Day Password Expiry Policy.</strong><br/>
                                        {{ $companyName }} enforces a 90-day password rotation policy for all portal users. You will be prompted to change your password when it is due to expire — please act promptly to maintain uninterrupted access.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Security warning -->
                <tr>
                    <td style="padding:20px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color:#fff7ed;border:1px solid #fed7aa;border-radius:10px;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                                        <strong>&#9888;&#65039; Security reminder:</strong>
                                        Never share your login credentials with anyone, including {{ $companyName }} staff. We will never ask for your password via email or phone.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Support -->
                <tr>
                    <td style="padding:24px 40px 0;text-align:center;">
                        <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.6;">
                            Having trouble logging in? Reach out to your {{ $companyName }} account representative for assistance.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:28px 40px 32px;text-align:center;background-color:#f8fafc;border-top:1px solid #e2e8f0;margin-top:24px;">
                        <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#0f172a;">
                            {{ $companyName }}
                        </p>
                        <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
                            This is an automated message — please do not reply directly to this email.
                        </p>
                    </td>
                </tr>

            </table>
            <!-- /Card -->

        </td>
    </tr>
</table>
<!-- /Wrapper -->

</body>
</html>
