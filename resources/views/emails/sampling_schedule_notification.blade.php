<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sampling Schedule Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #0f5132, #198754);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .detail-row {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-label {
            font-weight: bold;
            color: #0f5132;
            display: block;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #333;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sampling Schedule Confirmation</h2>
    </div>
    
    <div class="content">
        <p>Dear {{ $contactName }},</p>
        
        <p>This is to confirm that a sampling schedule has been created for you. Please find the details below:</p>
        
        <div class="detail-row">
            <span class="detail-label">Schedule Title:</span>
            <span class="detail-value">{{ $schedule->title }}</span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Client:</span>
            <span class="detail-value">{{ $schedule->client->name ?? 'N/A' }}</span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Sampling Date & Time:</span>
            <span class="detail-value">{{ $schedule->sampling_datetime ? $schedule->sampling_datetime->format('l, F j, Y \a\t g:i A') : 'N/A' }}</span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Location:</span>
            <span class="detail-value">{{ $schedule->location ?? 'N/A' }}</span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Number of Samples:</span>
            <span class="detail-value">{{ $schedule->number_of_samples ?? 1 }}</span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Frequency:</span>
            <span class="detail-value">{{ $schedule->frequency ?? 'One-time' }}</span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Personnel:</span>
            <span class="detail-value">{{ $schedule->personnel->name ?? 'N/A' }}</span>
        </div>
        
        @if($schedule->description)
        <div class="detail-row">
            <span class="detail-label">Description / Special Instructions:</span>
            <span class="detail-value">{{ $schedule->description }}</span>
        </div>
        @endif
        
        <p style="margin-top: 30px;">
            If you have any questions or need to make changes to this schedule, please contact us.
        </p>
        
        <p>Best regards,<br>
        <strong>{{ $companyName }}</strong></p>
    </div>
    
    <div class="footer">
        <p>This is an automated message from {{ $companyName }}. Please do not reply to this email.</p>
    </div>
</body>
</html>
