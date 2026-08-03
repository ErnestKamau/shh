@php
    $companyDetails = function_exists('getCompanyDetails') ? getCompanyDetails() : [];
    if (!isset($company) || ! $company) {
        $company = function_exists('getActiveCompany') ? getActiveCompany() : null;
    }
    $brandName = $company->name ?? ($companyDetails['name'] ?? 'IMARA LIMS');
@endphp
<x-emails.layout
    eyebrow="Feedback Request"
    heading="We value your partnership"
    :brand-name="$brandName"
    page-title="Feedback Request"
>
    <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;color:#1f2937;">
        Dear {{ $recipientName ?? 'Valued Customer' }},
    </p>
    <p style="margin:0 0 14px 0;">
        We value our partnership and we are committed to maintaining the highest standards of technical competence,
        accuracy, and reliability. As part of our commitment to continuous improvement, we kindly request your
        feedback on our services.
    </p>

    @if(!empty($body))
        <div style="margin:0 0 14px 0;">{!! $body !!}</div>
    @endif

    <p style="margin:0 0 8px 0;">Kindly click the link below to provide your feedback:</p>

    @include('emails.partials.cta-button', [
        'url' => $feedbackLink,
        'label' => 'Provide Feedback',
    ])

    <p style="margin:8px 0 0 0;">
        Best regards,<br>
        <strong style="color:#111827;">{{ $brandName }} Team</strong>
    </p>

    <x-slot:footer>
        <p style="margin:0;">
            <strong style="color:#374151;">{{ $brandName }}</strong>
            <br>{{ $company->address ?? ($companyDetails['address'] ?? '') }}
            @if(!empty($company->email) || !empty($companyDetails['email']))
                <br>Email: {{ $company->email ?? ($companyDetails['email'] ?? '') }}
            @endif
            @if(!empty($company->cell_phone) || !empty($companyDetails['phone']))
                <br>Phone: {{ $company->cell_phone ?? ($companyDetails['phone'] ?? '') }}
            @endif
            @if(!empty($company->website) || !empty($companyDetails['website']))
                <br>Website:
                <a href="http://{{ $company->website ?? ($companyDetails['website'] ?? '') }}" style="color:#1d4ed8;text-decoration:none;">
                    {{ $company->website ?? ($companyDetails['website'] ?? '') }}
                </a>
            @endif
        </p>
    </x-slot:footer>
</x-emails.layout>
