{{-- Per-sample detail grid: two columns of "Label : Value" cells --}}
@php
    $context = $sampleDetailContexts[$sampleIndex] ?? null;
    $rows = is_array($context['rows'] ?? null) ? $context['rows'] : [];
@endphp
@if($rows !== [])
@if(!empty($isBrazilExportationReport))
<div style="font-weight:bold;text-transform:uppercase;font-size:11px;margin:8px 0 4px;border-bottom:1px solid #000;display:inline-block;padding-bottom:2px;">
    {{ $labels['sample_information'] ?? 'Sample Information' }}
</div>
@endif
<table class="detail-grid sample-detail-grid">
    @include('layouts.lab.sample-workflow.report-formats.partials.trr-detail-grid-cols')
    @foreach($rows as $pair)
    <tr>
        <td>
            <strong>{{ $labels[$pair['left']['label']] ?? $pair['left']['label'] }}</strong> :
            <span @class(['val-emphasize' => !empty($pair['left']['emphasize'])])>{{ $pair['left']['value'] }}</span>
        </td>
        <td>
            <strong>{{ $labels[$pair['right']['label']] ?? $pair['right']['label'] }}</strong> :
            <span @class(['val-emphasize' => !empty($pair['right']['emphasize'])])>{{ $pair['right']['value'] }}</span>
        </td>
    </tr>
    @endforeach
</table>
@endif

@php $labSection = trim((string) ($context['lab_section'] ?? '')); @endphp
@if($labSection !== '')
<table class="detail-grid lab-section-banner">
    @include('layouts.lab.sample-workflow.report-formats.partials.trr-detail-grid-cols')
    <tr>
        <td colspan="2">
            <strong>{{ $labels['lab_section'] ?? 'Lab Section' }}</strong> : {{ $labSection }}
        </td>
    </tr>
</table>
@endif
