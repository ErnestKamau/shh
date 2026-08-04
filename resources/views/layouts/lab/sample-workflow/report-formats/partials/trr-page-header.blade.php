{{-- Logos + certificate no + report title (repeated on every PDF page) --}}
<table class="pg-header">
    <tr>
        <td class="pg-header-logo">
            @if(!empty($reportLogos['top_left']))
                <img src="{{ $reportLogos['top_left']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}">
            @elseif($reportLogo && empty($reportLogos))
                <img src="{{ $reportLogo }}" alt="{{ $company->name ?? 'AmSpec' }}">
            @else
                <span class="logo-text">{{ $company->name ?? 'AmSpec' }}</span>
            @endif
        </td>
        <td class="pg-header-center">
            <div class="cert-no">{{ $labels['certificate_no'] }}: {{ $reportNumber }}</div>
        </td>
        <td class="pg-header-right">
            @if(!empty($reportLogos['top_right']))
                <img src="{{ $reportLogos['top_right']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}">
            @endif
        </td>
    </tr>
</table>

<div class="report-title-bar">{{ $labels['report_title'] }}</div>

@if($showClientInfo ?? true)
    @include('layouts.lab.sample-workflow.report-formats.partials.trr-page-client-info')
@endif
