{{-- Letterhead: company logo + name left, System Settings address right, TEST REPORT title --}}
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
            @foreach($letterhead['lines'] ?? [] as $line)
                <div>{{ $line }}</div>
            @endforeach
        </td>
    </tr>
</table>

<div class="report-title-bar">{{ $labels['report_title'] }}</div>
