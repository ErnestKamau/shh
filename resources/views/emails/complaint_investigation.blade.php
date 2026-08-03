@php
    $company = getActiveCompany();
    $companyDetails = function_exists('getCompanyDetails') ? getCompanyDetails() : [];
    $brandName = $companyDetails['name'] ?? ($company->name ?? 'IMARA LIMS');
@endphp
<x-emails.layout
    eyebrow="Complaint Update"
    heading="Investigation Report Update"
    :brand-name="$brandName"
    page-title="Investigation Report Update"
>
    <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;color:#1f2937;">
        Dear {{ $complaint->client->name ?? 'Customer' }},
    </p>
    <p style="margin:0 0 14px 0;">
        Please find attached the official <strong style="color:#111827;">Investigation Report</strong>
        regarding your complaint (ID: <strong style="color:#111827;">{{ $complaint->complaint_id }}</strong>).
    </p>
    <p style="margin:0 0 14px 0;">
        Our team has completed the investigation into the issues raised. The attached document outlines the findings,
        root cause analysis, and the actions taken to resolve the matter and prevent recurrence.
    </p>
    <p style="margin:0 0 14px 0;">
        We take all feedback seriously and appreciate your patience while we conducted this thorough review.
    </p>
    <p style="margin:0 0 14px 0;">
        If you have any further questions or require additional clarification, please do not hesitate to contact us.
    </p>
    <p style="margin:0;">
        Best regards,<br>
        <strong style="color:#111827;">{{ $brandName }} Team</strong>
    </p>

    <x-slot:footer>
        <p style="margin:0;">
            <strong style="color:#374151;">{{ $brandName }}</strong>
            @if(!empty($companyDetails['street']) || !empty($companyDetails['location']))
                <br>{{ trim(($companyDetails['street'] ?? '').(!empty($companyDetails['street']) && !empty($companyDetails['location']) ? ', ' : '').($companyDetails['location'] ?? '')) }}
            @endif
            @if(!empty($companyDetails['phone']) || !empty($companyDetails['email']))
                <br>
                @if(!empty($companyDetails['phone'])) Phone: {{ $companyDetails['phone'] }} @endif
                @if(!empty($companyDetails['phone']) && !empty($companyDetails['email'])) | @endif
                @if(!empty($companyDetails['email'])) Email: {{ $companyDetails['email'] }} @endif
            @endif
        </p>
    </x-slot:footer>
</x-emails.layout>
