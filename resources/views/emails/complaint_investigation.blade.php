<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0 " />
    <style type="text/css">
        body {
            margin: 0 auto;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #ffffff;
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #dc2626;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
            font-size: 16px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #dc2626;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    @php
        $company = getActiveCompany();
        $companyDetails = getCompanyDetails();
        $logoDiskPath = $companyDetails['logo_path'] ?? null;
    @endphp

    <div class="container">
        <!-- Header with Logo -->
        <div class="header">
            @if($logoDiskPath)
                <img src="{{ $message->embed($logoDiskPath) }}" alt="{{ $companyDetails['name'] ?? 'IMARA LIMS' }}" style="max-width: 180px; height: auto;">
            @else

                <h1 style="margin: 0; color: #111827;">{{ $companyDetails['name'] ?? 'IMARA LIMS' }}</h1>
            @endif
        </div>

        <!-- Content -->
        <div class="content">
            <h2 style="color: #111827; margin-top: 0;">Investigation Report Update</h2>
            <p>Dear {{ $complaint->client->name ?? 'Customer' }},</p>
            
            <p>Please find attached the official <strong>Investigation Report</strong> regarding your complaint (ID: <strong>{{ $complaint->complaint_id }}</strong>).</p>
            
            <p>Our team has completed the investigation into the issues raised, and the attached document outlines the findings, root cause analysis, and the actions taken to resolve the matter and prevent recurrence.</p>
            
            <p>We take all feedback seriously and appreciate your patience while we conducted this thorough review.</p>
            
            <p>If you have any further questions or require additional clarification, please do not hesitate to contact us.</p>
            
            <p>Best Regards,<br>
            <strong>{{ $companyDetails['name'] ?? 'IMARA LIMS' }} Team</strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $companyDetails['name'] ?? 'IMARA LIMS' }}</strong></p>
            <p>{{ $companyDetails['street'] ?? '' }}{{ !empty($companyDetails['street']) && !empty($companyDetails['location']) ? ', ' : '' }}{{ $companyDetails['location'] ?? '' }}<br>
            @if(!empty($companyDetails['phone'])) Phone: {{ $companyDetails['phone'] }} | @endif
            @if(!empty($companyDetails['email'])) Email: {{ $companyDetails['email'] }} @endif</p>
            <p style="font-size: 12px; margin-top: 15px;">&copy; {{ date('Y') }} {{ $companyDetails['name'] ?? 'IMARA LIMS' }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
