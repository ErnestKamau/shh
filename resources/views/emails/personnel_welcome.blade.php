<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to {{ $companyName }}</title>
    <style type="text/css">
        body { margin: 0; padding: 0; background-color: #f0f4f8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        table { border-collapse: collapse; mso-table-lspace: 0; mso-table-rspace: 0; }
        img { border: 0; outline: none; display: block; }
        a { color: #0284c7; text-decoration: none; }
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
                    <td style="background:linear-gradient(135deg,#0ea5e9 0%,#0284c7 100%);padding:32px 40px;text-align:center;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $companyName }}" height="52"
                                 style="max-height:52px;max-width:220px;object-fit:contain;margin-bottom:12px;" />
                            <br/>
                        @endif
                        <span style="font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">
                            {{ $companyName }}
                        </span>
                    </td>
                </tr>

                <!-- Hero icon + title -->
                <tr>
                    <td style="padding:36px 40px 0;text-align:center;">
                        <div style="display:inline-block;width:64px;height:64px;background:#eff6ff;border-radius:50%;line-height:64px;font-size:28px;margin-bottom:20px;">
                            &#128075;
                        </div>
                        <h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#0f172a;line-height:1.3;">
                            Welcome aboard, {{ $fullName }}!
                        </h1>
                        <p style="margin:0;font-size:15px;color:#64748b;line-height:1.6;">
                            Your account has been created and is ready to use.<br/>
                            Below are your login credentials — please keep them secure.
                        </p>
                    </td>
                </tr>

                <!-- Credentials box -->
                <tr>
                    <td style="padding:28px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <tr>
                                <td style="padding:20px 24px;">
                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:0.8px;text-transform:uppercase;">
                                        Username / Email
                                    </p>
                                    <p style="margin:0 0 18px;font-size:15px;font-weight:600;color:#0f172a;word-break:break-all;">
                                        {{ $emailAddress }}
                                    </p>

                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:0.8px;text-transform:uppercase;">
                                        Temporary Password
                                    </p>
                                    <p style="margin:0;font-size:18px;font-weight:700;color:#0284c7;letter-spacing:1px;font-family:monospace;">
                                        {{ $plainPassword }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- CTA button -->
                <tr>
                    <td style="padding:28px 40px 0;text-align:center;">
                        <a href="{{ $loginUrl }}"
                           style="display:inline-block;background:linear-gradient(135deg,#0ea5e9 0%,#0284c7 100%);color:#ffffff;font-size:15px;font-weight:700;padding:14px 36px;border-radius:10px;text-decoration:none;letter-spacing:0.2px;">
                            Sign In to Your Account &rarr;
                        </a>
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
                        <p style="margin:0 0 14px;font-size:13px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;">
                            Important — Please Read
                        </p>

                        <!-- Notice 1: Change password -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:12px;">
                            <tr>
                                <td width="36" valign="top" style="padding-top:2px;">
                                    <div style="width:28px;height:28px;background:#fef3c7;border-radius:50%;text-align:center;line-height:28px;font-size:14px;">
                                        &#128274;
                                    </div>
                                </td>
                                <td style="padding-left:10px;">
                                    <p style="margin:0;font-size:14px;color:#334155;line-height:1.6;">
                                        <strong style="color:#0f172a;">Change your password immediately after first login.</strong><br/>
                                        The temporary password above was auto-generated. For your security, please update it to a strong, personal password as soon as you sign in.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Notice 2: Password policy -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="36" valign="top" style="padding-top:2px;">
                                    <div style="width:28px;height:28px;background:#dbeafe;border-radius:50%;text-align:center;line-height:28px;font-size:14px;">
                                        &#128197;
                                    </div>
                                </td>
                                <td style="padding-left:10px;">
                                    <p style="margin:0;font-size:14px;color:#334155;line-height:1.6;">
                                        <strong style="color:#0f172a;">90-Day Password Policy is in effect.</strong><br/>
                                        {{ $companyName }} requires all users to update their password every 90 days. You will be prompted to change your password when it is due to expire.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Security tip -->
                <tr>
                    <td style="padding:20px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color:#fff7ed;border:1px solid #fed7aa;border-radius:10px;">
                            <tr>
                                <td style="padding:14px 16px;">
                                    <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                                        <strong>&#9888; Security reminder:</strong>
                                        Never share your login credentials with anyone. {{ $companyName }} staff will never ask for your password.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Support note -->
                <tr>
                    <td style="padding:24px 40px 0;text-align:center;">
                        <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.6;">
                            Having trouble logging in? Contact your system administrator for assistance.
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
