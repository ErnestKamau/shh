<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            border-top: 5px solid #0056b3;
        }
        .header {
            padding: 30px;
            text-align: center;
            background: #ffffff;
            border-bottom: 1px solid #f0f0f0;
        }
        .header img {
            max-height: 50px;
            width: auto;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #222222;
        }
        .body-text {
            font-size: 15px;
            color: #555555;
            margin-bottom: 30px;
        }
        .btn-container {
            text-align: center;
            margin: 35px 0;
        }
        .btn {
            background-color: #0056b3;
            color: #ffffff !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 15px;
            display: inline-block;
            transition: background-color 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,86,179,0.3);
        }
        .btn:hover {
            background-color: #004094;
        }
        .footer {
            background-color: #fcfcfc;
            padding: 30px;
            text-align: center;
            font-size: 12px;
            color: #777777;
            border-top: 1px solid #f0f0f0;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #0056b3;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(isset($logoUrl) && $logoUrl)
            <div class="header">
                <img src="{{ $logoUrl }}" alt="Logo">
            </div>
        @endif
        
        <div class="content">
            <div class="greeting">Dear {{ $recipientName }},</div>
            <div class="body-text">
                {!! nl2br(e($body)) !!}
            </div>
            
            <div class="btn-container">
                <a href="{{ $feedbackLink }}" class="btn" target="_blank">Share Your Feedback</a>
            </div>
            
            <div style="font-size: 14px; color: #555555; margin-top: 25px;">
                Best regards,<br>
                <strong>{{ $companyName }} Team</strong>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
            @if(isset($companyAddress) && $companyAddress)
                <p>{{ $companyAddress }}</p>
            @endif
            @if(isset($companyWebsite) && $companyWebsite)
                <p><a href="{{ $companyWebsite }}" target="_blank">{{ $companyWebsite }}</a></p>
            @endif
        </div>
    </div>
</body>
</html>
