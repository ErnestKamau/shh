{{-- Letterhead: logo left; Companies name first, then address / T / W; TEST REPORT title --}}
@php
    $letterhead = $companyLetterhead ?? ['name' => ($company->name ?? 'AmSpec'), 'lines' => []];
    $letterheadLogo = $companyLogo ?: ($reportLogo ?? '');
@endphp
<table class="pg-header">
    <tr>
        <td class="pg-header-logo">
            @if($letterheadLogo !== '')
                <img src="{{ $letterheadLogo }}" alt="{{ $letterhead['name'] }}">
            @else
                <span class="logo-text">AmSpec</span>
            @endif
        </td>
        <td class="pg-header-address">
            @if(($letterhead['name'] ?? '') !== '')
                <div class="pg-header-company">{{ $letterhead['name'] }}</div>
            @endif
            @foreach($letterhead['lines'] ?? [] as $line)
                @continue(strcasecmp(trim((string) $line), trim((string) ($letterhead['name'] ?? ''))) === 0)
                <div>{{ $line }}</div>
            @endforeach
        </td>
    </tr>
</table>

<div class="report-title-bar">
    @if(!empty($isPreviewMode) && !empty($labels['draft_report_title']))
        {{ $labels['draft_report_title'] }}
    @else
        {{ $labels['report_title'] ?? 'TEST REPORT' }}
    @endif
</div>
