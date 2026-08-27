{{-- Analysis meta → notes → amendment → signature → end of text (once per sample) --}}
@php
    $sampleNotesBody = $sample->notes_body ?? null;
    $conductedBy = trim((string) ($sampleDetailContexts[$sampleIndex]['conducted_by'] ?? ''));
@endphp

<table class="meta-box">
    <tr>
        <td>
            {{ $labels['analysis_conducted'] }}: {{ $labels['employee_id'] ?? 'Employee ID' }} - {{ $conductedBy }}
        </td>
        <td>
            {{ $labels['test_method_dev'] }}
        </td>
    </tr>
</table>

@if(filled(trim(strip_tags((string) $sampleNotesBody))))
<div class="sample-notes">
    <strong>{{ $labels['notes'] ?? 'Notes' }}:</strong>
    {!! $sampleNotesBody !!}
</div>
@endif

@if(!empty($ammendment?->id))
<div class="sample-amendment">
    <div><strong>{{ $supersedesText }}</strong></div>
    @if(!empty($amendmentRevisionLabel))
    <div style="margin-top:4px;">
        <strong>{{ $revisionLabel }}:</strong>
        {{ $amendmentRevisionLabel }}
        @if(!empty($reportNumber))
            <span style="color:#555;">({{ $reportNumber }})</span>
        @endif
    </div>
    @endif
    <div style="margin-top:4px;">
        <strong>{{ $reasonLabel }}:</strong>
        {{ $ammendment->reason }}
    </div>
</div>
@endif

<div class="sig-section">
    <div class="sig-intro">
        {{ $labels['signed_behalf'] }} {{ $labels['signed_org'] ?? 'AMSPEC' }}
    </div>

    <div class="sig-name">{{ $approverUser->name ?? '&nbsp;' }}</div>
    <div class="sig-title-line">{{ $approverRole ?? '&nbsp;' }}</div>
    <div class="sig-company-line">{{ $company->name ?? '&nbsp;' }}</div>
    <div class="sig-image-box">
        @if(!empty($signatureSrc))
            <img src="{{ $signatureSrc }}" alt="Signature">
        @else
            <span class="sig-missing">{{ $labels['no_signature'] }}</span>
        @endif
    </div>
</div>

<div class="end-text">{{ $labels['end_of_text'] }}</div>

@if(!empty($isBrazilExportationReport))
<div class="report-footer-text">{{ $labels['lab_address_closing'] ?? 'The analyses were performed at the laboratory address identified in the header.' }}</div>
@endif
