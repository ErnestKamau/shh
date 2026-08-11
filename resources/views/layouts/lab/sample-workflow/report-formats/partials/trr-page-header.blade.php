{{-- Letterhead: logo + company name left, address right, TEST REPORT title (repeats every PDF page) --}}
@php
    $letterhead = $companyLetterhead ?? ['name' => ($company->name ?? 'AmSpec'), 'lines' => []];
@endphp
<table class="pg-header">
    <tr>
        <td class="pg-header-logo">
            @if(!empty($reportLogos['top_left']))
                <img src="{{ $reportLogos['top_left']['src'] }}" alt="{{ $letterhead['name'] }}">
            @elseif($reportLogo && empty($reportLogos))
                <img src="{{ $reportLogo }}" alt="{{ $letterhead['name'] }}">
            @else
                <span class="logo-text">AmSpec</span>
            @endif
            <div class="pg-header-company">{{ $letterhead['name'] }}</div>
        </td>
        <td class="pg-header-address">
            @foreach($letterhead['lines'] ?? [] as $line)
                <div>{{ $line }}</div>
            @endforeach
        </td>
    </tr>
</table>

<div class="report-title-bar">{{ $labels['report_title'] }}</div>
