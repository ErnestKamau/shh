<x-emails.layout
    eyebrow="Test Report"
    heading="Ready for download"
    :brand-name="$companyName"
    :page-title="'Test Report — '.$reportNumber"
>
    <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;color:#1f2937;">
        Dear {{ $contactName }},
    </p>
    <p style="margin:0 0 8px 0;">
        Your Test Report has been processed and is ready. Please find the details below.
    </p>

    @include('emails.partials.detail-card', [
        'rows' => [
            ['label' => 'Report Number', 'value' => e($reportNumber)],
        ],
    ])

    @if($notes)
        <p style="margin:0 0 8px 0;">
            <strong style="color:#111827;">Revision Notes:</strong> {{ $notes }}
        </p>
    @endif

    @if($downloadUrl)
        @include('emails.partials.cta-button', [
            'url' => $downloadUrl,
            'label' => 'View / Download Report',
        ])
        <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
            If you are unable to click the button, copy this link into your browser:<br>
            <a href="{{ $downloadUrl }}" style="color:#1d4ed8;text-decoration:none;word-break:break-all;">{{ $downloadUrl }}</a>
        </p>
    @endif
</x-emails.layout>
