@php
    $actionUrl = $correctiveActionUrl ?: (Route::has('feedback-home') ? route('feedback-home') : '#');
    $actionLabel = $correctiveActionUrl ? 'Open corrective action in CRM' : 'View Feedback in CRM';
@endphp
<x-emails.layout
    eyebrow="Action Required"
    heading="Customer feedback alert"
    page-title="Customer Feedback Alert"
    :footer-note="'This is an automated system alert from '.config('app.name').'.'"
>
    <p style="margin:0 0 14px 0;">
        <strong style="color:#111827;">Action required:</strong>
        A customer has submitted feedback that requires attention.
    </p>

    @include('emails.partials.detail-card', [
        'rows' => [
            ['label' => 'Customer', 'value' => e($feedback->contact->customer->name ?? 'N/A')],
            ['label' => 'Contact', 'value' => e($feedback->contact->name ?? 'N/A')],
            ['label' => 'Service Ref', 'value' => e($feedback->service_reference_no)],
            ['label' => 'Date', 'value' => e($feedback->created_at->format('d M Y, h:i A'))],
        ],
    ])

    @if($feedback->has_issues)
        <div style="margin:0 0 14px 0;padding:14px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;">
            <p style="margin:0 0 6px 0;font-size:12px;letter-spacing:0.8px;text-transform:uppercase;color:#b91c1c;font-weight:700;">
                Issue reported
            </p>
            <p style="margin:0;color:#7f1d1d;font-style:italic;">"{{ $feedback->issue_description }}"</p>
        </div>
    @endif

    @if(!empty($lowRatings))
        <div style="margin:0 0 14px 0;padding:14px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;">
            <p style="margin:0 0 8px 0;font-size:12px;letter-spacing:0.8px;text-transform:uppercase;color:#b45309;font-weight:700;">
                Low rating signals detected
            </p>
            <p style="margin:0 0 8px 0;color:#92400e;">Metrics at or below the corrective-action threshold:</p>
            <ul style="margin:0;padding-left:18px;color:#92400e;">
                @foreach($lowRatings as $lowRating)
                    <li style="margin-bottom:4px;">
                        {{ $lowRating['metric_name'] }}:
                        <strong>{{ $lowRating['rating'] }}/{{ $lowRating['max_rating'] }}</strong>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($feedback->iso_impartiality === 'No' || $feedback->iso_confidentiality === 'No')
        <div style="margin:0 0 14px 0;padding:14px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;">
            <p style="margin:0 0 6px 0;font-size:12px;letter-spacing:0.8px;text-transform:uppercase;color:#b91c1c;font-weight:700;">
                ISO compliance concern
            </p>
            <p style="margin:0 0 6px 0;color:#7f1d1d;">Customer flagged Impartiality or Confidentiality issues.</p>
            <p style="margin:0;color:#7f1d1d;font-style:italic;">Note: "{{ $feedback->iso_concerns_description }}"</p>
        </div>
    @endif

    <p style="margin:0 0 8px 0;">
        Please review this feedback in the CRM immediately and initiate a Non-Conforming Work investigation if necessary.
    </p>

    @include('emails.partials.cta-button', [
        'url' => $actionUrl,
        'label' => $actionLabel,
    ])
</x-emails.layout>
