{{-- Per-sample Test info grid: two columns of "Label : Value" (empty values omitted upstream). --}}
{{-- Brazil Exportation: bilingual label column + value column (SAMPLE INFORMATION table). --}}
@php
    $context = $sampleDetailContexts[$sampleIndex] ?? null;
    $rows = is_array($context['rows'] ?? null) ? $context['rows'] : [];
    $brazilExportationBilingual = [
        'sample' => ['en' => 'SAMPLE:', 'pt' => 'AMOSTRA'],
        'date_received' => ['en' => 'DATE RECEIVED:', 'pt' => 'DATA DE RECEBIMENTO'],
        'packaging' => ['en' => 'PACKAGING:', 'pt' => 'EMBALAGEM'],
        'sample_weight' => ['en' => 'SAMPLE WEIGHT:', 'pt' => 'PESO DA AMOSTRA'],
        'sample_information' => ['en' => 'SAMPLE INFORMATION:', 'pt' => 'INFORMAÇÃO DA AMOSTRA'],
        'ship_name' => ['en' => 'SHIP:', 'pt' => 'NAVIO'],
        'port_of_loading' => ['en' => 'PORT OF LOADING:', 'pt' => 'PORTO DE EMBARQUE'],
        'port_of_discharge' => ['en' => 'PORT OF DISCHARGE:', 'pt' => 'PORTO DE DESEMBARQUE'],
        'seal_number' => ['en' => 'SEAL:', 'pt' => 'LACRE'],
    ];
@endphp
@if($rows !== [])
@if(!empty($isBrazilExportationReport))
<div class="brazil-export-sample-info-header">
    <div class="brazil-export-sample-info-header__en">SAMPLE INFORMATION</div>
    <div class="brazil-export-sample-info-header__pt">INFORMAÇÃO DA AMOSTRA</div>
</div>
<table class="detail-grid sample-detail-grid brazil-export-sample-info-table">
    <colgroup>
        <col class="brazil-export-label-col">
        <col class="brazil-export-value-col">
    </colgroup>
    @foreach($rows as $pair)
    @php
        $left = is_array($pair['left'] ?? null) ? $pair['left'] : null;
        if ($left === null) {
            continue;
        }
        $key = (string) ($left['label'] ?? '');
        $bilingual = $brazilExportationBilingual[$key] ?? null;
        $value = mb_strtoupper(trim((string) ($left['value'] ?? '')));
    @endphp
    <tr>
        <td class="brazil-export-label-cell">
            @if($bilingual !== null)
                <div class="brazil-export-label-en">{{ $bilingual['en'] }}</div>
                <div class="brazil-export-label-pt">{{ $bilingual['pt'] }}</div>
            @else
                <strong>{{ $labels[$key] ?? $key }}</strong>
            @endif
        </td>
        <td class="brazil-export-value-cell">
            <strong>{{ $value }}</strong>
        </td>
    </tr>
    @endforeach
</table>
@else
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
@endif
