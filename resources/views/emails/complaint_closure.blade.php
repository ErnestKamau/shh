@php
    $company = $company ?? (function_exists('getActiveCompany') ? getActiveCompany() : null);
    $brandName = $company->name ?? 'IMARA LIMS';
@endphp
<x-emails.layout
    eyebrow="Complaint Closure"
    heading="Complaint closure report"
    :brand-name="$brandName"
    page-title="Complaint Closure Report"
>
    <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;color:#1f2937;">
        Dear {{ $complaint->client->name ?? 'Customer' }},
    </p>
    <p style="margin:0 0 14px 0;">
        Please find attached the official closure report for your complaint
        (ID: <strong style="color:#111827;">{{ $complaint->complaint_id }}</strong>).
    </p>
    <p style="margin:0 0 14px 0;">
        We verify that this complaint has been fully resolved and documented.
    </p>
    <p style="margin:0;">
        Best regards,<br>
        <strong style="color:#111827;">{{ $brandName }} Team</strong>
    </p>

    <x-slot:footer>
        @if($company)
            <p style="margin:0;">
                <strong style="color:#374151;">{{ $brandName }}</strong>
                @if(!empty($company->street) || !empty($company->location))
                    <br>{{ trim(($company->street ?? '').(!empty($company->street) && !empty($company->location) ? ', ' : '').($company->location ?? '')) }}
                @endif
                @if(!empty($company->address))
                    <br>P.O. BOX {{ $company->address }}
                @endif
                @if(!empty($company->email) || !empty($company->website))
                    <br>
                    @if(!empty($company->email)) Email: {{ $company->email }} @endif
                    @if(!empty($company->email) && !empty($company->website)) | @endif
                    @if(!empty($company->website))
                        Website: <a href="{{ $company->website }}" style="color:#1d4ed8;text-decoration:none;">{{ $company->website }}</a>
                    @endif
                @endif
                @if(!empty($company->cell_phone))
                    <br>Office Cell: {{ $company->cell_phone }}
                @endif
            </p>
        @endif
    </x-slot:footer>
</x-emails.layout>
