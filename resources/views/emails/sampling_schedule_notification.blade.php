<x-emails.layout
    eyebrow="Sampling Schedule"
    heading="Sampling schedule confirmation"
    :brand-name="$companyName"
    page-title="Sampling Schedule Notification"
>
    <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;color:#1f2937;">
        Dear {{ $contactName }},
    </p>
    <p style="margin:0 0 8px 0;">
        This is to confirm that a sampling schedule has been created for you. Please find the details below:
    </p>

    @include('emails.partials.detail-card', [
        'rows' => array_values(array_filter([
            ['label' => 'Schedule Title', 'value' => e($schedule->title)],
            ['label' => 'Client', 'value' => e($schedule->client->name ?? 'N/A')],
            [
                'label' => 'Sampling Date & Time',
                'value' => e($schedule->sampling_datetime
                    ? $schedule->sampling_datetime->format('l, F j, Y \a\t g:i A')
                    : 'N/A'),
            ],
            ['label' => 'Location', 'value' => e($schedule->locationDisplayName())],
            ['label' => 'Number of Samples', 'value' => e((string) ($schedule->number_of_samples ?? 1))],
            ['label' => 'Frequency', 'value' => e($schedule->frequency ?? 'One-time')],
            [
                'label' => 'Personnel',
                'value' => e($personnelNames ?? ($schedule->personnelNames() ?? ($schedule->personnel->name ?? 'N/A'))),
            ],
            !empty($schedule->description)
                ? ['label' => 'Description / Special Instructions', 'value' => e($schedule->description)]
                : null,
        ])),
    ])

    <p style="margin:18px 0 14px 0;">
        If you have any questions or need to make changes to this schedule, please contact us.
    </p>
    <p style="margin:0;">
        Best regards,<br>
        <strong style="color:#111827;">{{ $companyName }}</strong>
    </p>
</x-emails.layout>
