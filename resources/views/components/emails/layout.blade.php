@props([
    'eyebrow' => 'Notification',
    'heading' => null,
    'brandName' => null,
    'pageTitle' => null,
    'footerNote' => null,
    'activeCompany' => null,
])

@php
    $resolvedCompany = $activeCompany ?? (function_exists('getActiveCompany') ? getActiveCompany() : null);
    $resolvedBrand = $brandName ?? ($resolvedCompany->name ?? config('app.name', 'Application'));
    $resolvedHeading = $heading ?? $resolvedBrand;
    $resolvedPageTitle = $pageTitle ?? $resolvedHeading;
    $resolvedFooter = $footerNote
        ?? ('This is an automated message from '.$resolvedBrand.'. Please do not reply directly to this email.');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $resolvedPageTitle }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);padding:28px 32px;">
                            <p style="margin:0;color:#bfdbfe;font-size:12px;letter-spacing:1.2px;text-transform:uppercase;">{{ $eyebrow }}</p>
                            <h1 style="margin:8px 0 0 0;color:#ffffff;font-size:24px;line-height:1.3;font-weight:700;">{{ $resolvedHeading }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px 14px 32px;font-size:15px;line-height:1.7;color:#4b5563;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px 28px 32px;">
                            <p style="margin:0;padding-top:14px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:12px;line-height:1.6;">
                                {{ $resolvedFooter }}
                            </p>
                            @isset($footer)
                                <div style="margin-top:12px;color:#6b7280;font-size:12px;line-height:1.6;">
                                    {{ $footer }}
                                </div>
                            @endisset
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
