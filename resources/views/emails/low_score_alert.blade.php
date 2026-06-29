<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .header {
            background: #d9534f;
            color: white;
            padding: 10px 15px;
            border-radius: 5px 5px 0 0;
        }

        .content {
            padding: 15px;
        }

        .footer {
            font-size: 12px;
            color: #777;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .rating-box {
            background: #f9f9f9;
            padding: 10px;
            border: 1px solid #eee;
            margin-bottom: 15px;
        }

        .alert-item {
            color: #d9534f;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>⚠️ Customer Feedback Alert</h2>
        </div>
        <div class="content">
            <p><strong>Action Required:</strong> A customer has submitted feedback that requires attention.</p>

            <div class="rating-box">
                <p><strong>Customer:</strong> {{ $feedback->contact->customer->name ?? 'N/A' }}</p>
                <p><strong>Contact:</strong> {{ $feedback->contact->name ?? 'N/A' }}</p>
                <p><strong>Service Ref:</strong> {{ $feedback->service_reference_no }}</p>
                <p><strong>Date:</strong> {{ $feedback->created_at->format('d M Y, h:i A') }}</p>
            </div>

            @if($feedback->has_issues)
                <div class="rating-box" style="border-left: 4px solid #d9534f;">
                    <p class="alert-item">ISSUE REPORTED:</p>
                    <p><em>"{{ $feedback->issue_description }}"</em></p>
                </div>
            @endif

            @if(!empty($lowRatings))
                <div class="rating-box" style="border-left: 4px solid #f0ad4e;">
                    <p class="alert-item">LOW RATING SIGNALS DETECTED:</p>
                    <p>Metrics at or below the corrective-action threshold:</p>
                    <ul>
                        @foreach($lowRatings as $lowRating)
                            <li>{{ $lowRating['metric_name'] }}: <strong>{{ $lowRating['rating'] }}/{{ $lowRating['max_rating'] }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($feedback->iso_impartiality === 'No' || $feedback->iso_confidentiality === 'No')
                <div class="rating-box" style="border-left: 4px solid #d9534f;">
                    <p class="alert-item">ISO COMPLIANCE CONCERN:</p>
                    <p>Customer flagged Impartiality or Confidentiality issues.</p>
                    <p><em>Note: "{{ $feedback->iso_concerns_description }}"</em></p>
                </div>
            @endif

            <p>Please review this feedback in the CRM immediately and initiate a Non-Conforming Work investigation if
                necessary.</p>

            @if($correctiveActionUrl)
                <p><a href="{{ $correctiveActionUrl }}">Open corrective action in CRM</a></p>
            @else
                <p><a href="{{ route('feedback-home') }}">View Feedback in CRM</a></p>
            @endif
        </div>
        <div class="footer">
            This is an automated system alert from {{ config('app.name') }}.
        </div>
    </div>
</body>

</html>
