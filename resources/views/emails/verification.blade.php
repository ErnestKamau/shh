<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code - {{ $branding_name ?? config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #ffffff;
            color: #333333;
            padding: 28px 24px 24px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        .header-logo {
            display: block;
            margin: 0 auto;
            max-width: 280px;
            width: 100%;
            height: auto;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #333333;
        }
        .header-tagline {
            margin: 12px 0 0;
            font-size: 14px;
            color: #6c757d;
            font-weight: 400;
        }
        .content {
            padding: 16px 30px 40px;
        }
        .content h2 {
            margin: 0 0 16px 0;
            font-size: 20px;
            font-weight: 600;
        }
        .verification-code {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #495057;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    @php
        $activeCompany = getActiveCompany();
        $displayName = $branding_name ?? config('app.name');
        $isPasswordReset = ($purpose ?? 'login') === 'password_reset';
        $rawLogo = $activeCompany?->logo ?? null;
        $baseUrl = rtrim((string) config('app.url'), '/');
        if ($rawLogo && filter_var($rawLogo, FILTER_VALIDATE_URL)) {
            $logoUrl = $rawLogo;
        } elseif ($rawLogo) {
            $logoUrl = $baseUrl . '/' . ltrim($rawLogo, '/');
        } else {
            $logoUrl = null;
        }
    @endphp
    <div class="container">
        <div class="header">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $displayName }}" class="header-logo" width="280" border="0" />
                <p class="header-tagline">{{ $isPasswordReset ? 'Password Reset' : 'Two-Factor Authentication' }}</p>
            @else
                <h1>{{ $displayName }}</h1>
                <p class="header-tagline">{{ $isPasswordReset ? 'Password Reset' : 'Two-Factor Authentication' }}</p>
            @endif
        </div>
        
        <div class="content">
            <h2>Hello {{ $user->first_name ?? $user->name }},</h2>
            
            <p>Your verification code has been generated successfully. Please use the code below to {{ $isPasswordReset ? 'reset your password' : 'complete your login' }}:</p>
            
            <div class="verification-code">
                <p style="margin: 0 0 10px 0; color: #6c757d;">Your verification code is:</p>
                <div class="code">{{ $user->verify_code }}</div>
            </div>
            
            <div class="warning">
                <strong>Important:</strong> This code will expire in 30 minutes. Do not share this code with anyone.
            </div>
            
            <p>If you didn't request this verification code, please ignore this email or contact your system administrator.</p>
            
            <p>Best regards,<br>
            {{ $branding_name ?? config('app.name') }} Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ $branding_name ?? config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
