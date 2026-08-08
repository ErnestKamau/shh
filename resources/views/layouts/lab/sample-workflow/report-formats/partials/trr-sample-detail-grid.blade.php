{{-- Per-sample detail grid matching General Report Format field order --}}
@php
    $context = $sampleDetailContexts[$sampleIndex] ?? null;
    $rows = is_array($context['rows'] ?? null) ? $context['rows'] : [];
@endphp
@if($rows !== [])
<table class="detail-grid sample-detail-grid">
    @include('layouts.lab.sample-workflow.report-formats.partials.trr-detail-grid-cols')
    @foreach($rows as $pair)
    <tr>
        <td class="dlbl">{{ $labels[$pair['left']['label']] ?? $pair['left']['label'] }}</td>
        <td @class(['val-emphasize' => !empty($pair['left']['emphasize'])])>{{ $pair['left']['value'] }}</td>
        <td class="dlbl">{{ $labels[$pair['right']['label']] ?? $pair['right']['label'] }}</td>
        <td @class(['val-emphasize' => !empty($pair['right']['emphasize'])])>{{ $pair['right']['value'] }}</td>
    </tr>
    @endforeach
</table>
@endif

@php $labSection = trim((string) ($context['lab_section'] ?? '')); @endphp
@if($labSection !== '')
<table class="detail-grid lab-section-banner">
    @include('layouts.lab.sample-workflow.report-formats.partials.trr-detail-grid-cols')
    <tr>
        <td class="dlbl">{{ $labels['lab_section'] ?? 'Lab Section' }}</td>
        <td colspan="3">{{ $labSection }}</td>
    </tr>
</table>
@endif
