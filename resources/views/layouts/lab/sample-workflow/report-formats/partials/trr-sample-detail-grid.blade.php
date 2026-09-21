{{-- Per-sample Test info grid: two columns of "Label : Value" (empty values omitted upstream). --}}
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
    @php
        $left = is_array($pair['left'] ?? null) ? $pair['left'] : null;
        $right = is_array($pair['right'] ?? null) ? $pair['right'] : null;
    @endphp
    @if($left !== null)
    <tr>
        <td @if($right === null) colspan="2" @endif>
            <strong>{{ $labels[$left['label']] ?? $left['label'] }}</strong> :
            <span @class(['val-emphasize' => !empty($left['emphasize'])])>{{ $left['value'] }}</span>
        </td>
        @if($right !== null)
        <td>
            <strong>{{ $labels[$right['label']] ?? $right['label'] }}</strong> :
            <span @class(['val-emphasize' => !empty($right['emphasize'])])>{{ $right['value'] }}</span>
        </td>
        @endif
    </tr>
    @endif
    @endforeach
</table>
@endif
