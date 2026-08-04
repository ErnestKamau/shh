@php
    $activeCompany = function_exists('getActiveCompany') ? getActiveCompany() : null;
    $brandName = $app_name ?? ($activeCompany->name ?? config('app.name', 'Application'));
@endphp
<x-emails.layout
    :eyebrow="$eyebrow ?? 'Notification'"
    :heading="$heading ?? $brandName"
    :brand-name="$brandName"
    :page-title="$pageTitle ?? ($heading ?? $brandName)"
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
                @if(!empty($activeCompany->cell_phone))
                    <br>Office Cell: {{ $activeCompany->cell_phone }}
                @endif
            </p>
            @if(function_exists('getReportEmailFooter'))
                <div style="margin-top:8px;">{!! getReportEmailFooter() !!}</div>
            @endif
        @endif
    </x-slot:footer>
</x-emails.layout>
