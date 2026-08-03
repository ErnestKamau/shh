@php
    $activeCompany = $company ?? (function_exists('getActiveCompany') ? getActiveCompany() : null);
    $brandName = $app_name ?? ($activeCompany->name ?? config('app.name', 'Application'));
@endphp
<x-emails.layout
    :eyebrow="$eyebrow ?? 'Sample Report'"
    :heading="$heading ?? 'Your analysis report is ready'"
    :brand-name="$brandName"
    :page-title="$pageTitle ?? ('Report — '.$brandName)"
    :footer-note="$footerNote ?? null"
    :active-company="$activeCompany"
>
    <div style="font-size:15px;line-height:1.7;color:#4b5563;">
        {!! $body !!}
    </div>

    <x-slot:footer>
        @if($activeCompany)
            <p style="margin:0;">
                <strong style="color:#374151;">{{ $activeCompany->name }}</strong>
                @if(!empty($activeCompany->street) || !empty($activeCompany->location))
                    <br>{{ trim(($activeCompany->street ?? '').(!empty($activeCompany->street) && !empty($activeCompany->location) ? ', ' : '').($activeCompany->location ?? '')) }}
                @endif
                @if(!empty($activeCompany->address))
                    <br>P.O. BOX {{ $activeCompany->address }}
                @endif
                @if(!empty($activeCompany->email) || !empty($activeCompany->website))
                    <br>
                    @if(!empty($activeCompany->email))
                        Email: {{ $activeCompany->email }}
                    @endif
                    @if(!empty($activeCompany->email) && !empty($activeCompany->website))
                        |
                    @endif
                    @if(!empty($activeCompany->website))
                        Website: <a href="{{ $activeCompany->website }}" style="color:#1d4ed8;text-decoration:none;">{{ $activeCompany->website }}</a>
                    @endif
                @endif
            </p>
        @endif
    </x-slot:footer>
</x-emails.layout>
