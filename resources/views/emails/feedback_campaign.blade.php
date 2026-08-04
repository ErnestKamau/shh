@php
    $brandName = $companyName ?? config('app.name', 'Application');
@endphp
<x-emails.layout
    eyebrow="Feedback Request"
    heading="We value your feedback"
    :brand-name="$brandName"
    :page-title="$subject ?? ('Feedback — '.$brandName)"
    :footer-note="'© '.date('Y').' '.$brandName.'. All rights reserved.'"
>
    <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;color:#1f2937;">
        Dear {{ $recipientName }},
    </p>
    <div style="margin:0 0 8px 0;font-size:15px;line-height:1.7;color:#4b5563;">
        {!! nl2br(e($body)) !!}
    </div>

    @include('emails.partials.cta-button', [
        'url' => $feedbackLink,
        'label' => 'Share Your Feedback',
    ])

    <p style="margin:8px 0 0 0;font-size:14px;line-height:1.7;color:#4b5563;">
        Best regards,<br>
        <strong style="color:#111827;">{{ $brandName }} Team</strong>
    </p>

    <x-slot:footer>
        @if(!empty($companyAddress))
            <p style="margin:0 0 4px 0;">{{ $companyAddress }}</p>
        @endif
        @if(!empty($companyWebsite))
            <p style="margin:0;">
                <a href="{{ $companyWebsite }}" style="color:#1d4ed8;text-decoration:none;" target="_blank">{{ $companyWebsite }}</a>
            </p>
        @endif
    </x-slot:footer>
</x-emails.layout>
