<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Procedure Worksheet – {{ $procedure->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; margin: 0; padding: 0; }
        .parameter { border: solid 1px rgba(0, 0, 0, 0.35) !important; padding: 4px 6px !important; }
        table { width: 100%; border-collapse: collapse; }
        th.parameter, td.parameter { font-size: 8px !important; vertical-align: middle !important; }
        .section-title { font-size: 10px !important; font-weight: 700; background-color: #fafafa; padding: 6px 8px; border: solid 1px rgba(0, 0, 0, 0.35); }
        .report-header-table { border: 0; border-bottom: 1px solid #014421; margin-bottom: 12px; }
        .report-header-table td { border: 0; padding: 4px 8px; vertical-align: top; }
        .footer-text { font-size: 8px; text-align: right; margin-top: 12px; }
    </style>
</head>
<body>

{{-- Header: same style as standard_report_2 (logo left, company right) --}}
<table class="report-header-table" style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:30%; border: 0;">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" style="height:70px;" alt="Logo">
            @else
                <strong>{{ config('app.name') }}</strong>
            @endif
        </td>
        <td style="width:70%; text-align:right; border: 0;">
            @if(isset($company) && $company)
                <span style="font-size: 8px;">{{ $company->name }}</span><br>
                @if($company->street) {{ $company->street }}<br> @endif
                @if($company->address) P.O. Box {{ $company->address }}@if($company->location), {{ $company->location }}@endif<br> @endif
                @if($company->telephone) Office: {{ $company->telephone }} @endif
                @if($company->fax) Fax: {{ $company->fax }} @endif<br>
                @if($company->cell_phone) Tel: {{ $company->cell_phone }}<br> @endif
                @if($company->email) Email: {{ $company->email }} @endif
                @if($company->website) Web: {{ $company->website }} @endif
            @else
                <strong>{{ config('app.name') }}</strong>
            @endif
        </td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
    <tr>
        <td class="parameter" colspan="2" style="font-size:10px;"><b>PROCEDURE WORKSHEET</b> – {{ $procedure->name }}</td>
    </tr>
    <tr>
        <td class="parameter" style="width:20%;">Batch</td>
        <td class="parameter">{{ $batch->batch_code }}</td>
    </tr>
    <tr>
        <td class="parameter">Printed</td>
        <td class="parameter">{{ $printedAt }}</td>
    </tr>
</table>

{{-- 1. Select Samples for the worksheet --}}
<table class="parameter" style="width:100%; margin-bottom:12px;">
    <tr>
        <td class="section-title" colspan="3">SELECT SAMPLES FOR THE WORKSHEET</td>
    </tr>
    <tr>
        <th class="parameter" style="width:33%;">Sample</th>
        <th class="parameter" style="width:33%;">Batch</th>
        <th class="parameter" style="width:34%;">Sample Type</th>
    </tr>
    @forelse($samplesForWorksheet ?? [] as $row)
    <tr>
        <td class="parameter">{{ $row['sample_code'] }}</td>
        <td class="parameter">{{ $row['batch_code'] }}</td>
        <td class="parameter">{{ $row['sample_type_name'] ?? '—' }}</td>
    </tr>
    @empty
    <tr>
        <td class="parameter" colspan="3" style="text-align:center;">No samples selected</td>
    </tr>
    @endforelse
</table>

{{-- 2. Configurable Fields --}}
@if($configFields->isNotEmpty())
<table class="parameter" style="width:100%; margin-bottom:12px;">
    <tr>
        <td class="section-title" colspan="6">CONFIGURABLE FIELDS</td>
    </tr>
    <tr>
        <th class="parameter" style="width:8%;">Order</th>
        <th class="parameter" style="width:22%;">Label</th>
        <th class="parameter" style="width:14%;">Type</th>
        <th class="parameter" style="width:20%;">Value name</th>
        <th class="parameter" style="width:10%;">Required</th>
        <th class="parameter">Help text</th>
    </tr>
    @foreach($configFields as $field)
    <tr>
        <td class="parameter">{{ $field->order }}</td>
        <td class="parameter">{{ $field->label }}</td>
        <td class="parameter">{{ \App\Models\Procedures\ProcedureConfigField::getFieldTypes()[$field->field_type] ?? $field->field_type }}</td>
        <td class="parameter">{{ $field->field_value_name ?? '—' }}</td>
        <td class="parameter">{{ $field->is_required ? 'Yes' : 'No' }}</td>
        <td class="parameter">{{ $field->help_text ?? '—' }}</td>
    </tr>
    @endforeach
</table>
@endif

{{-- 3. Test Kit Columns --}}
@if($testKitColumns->isNotEmpty())
<table class="parameter" style="width:100%; margin-bottom:12px;">
    <tr>
        <td class="section-title" colspan="6">TEST KIT COLUMNS</td>
    </tr>
    <tr>
        <th class="parameter" style="width:8%;">Order</th>
        <th class="parameter" style="width:22%;">Label</th>
        <th class="parameter" style="width:18%;">Key</th>
        <th class="parameter" style="width:12%;">Type</th>
        <th class="parameter" style="width:10%;">Required</th>
        <th class="parameter">Help text</th>
    </tr>
    @foreach($testKitColumns as $col)
    <tr>
        <td class="parameter">{{ $col->order }}</td>
        <td class="parameter">{{ $col->label }}</td>
        <td class="parameter">{{ $col->key }}</td>
        <td class="parameter">{{ $col->type }}</td>
        <td class="parameter">{{ $col->is_required ? 'Yes' : 'No' }}</td>
        <td class="parameter">{{ $col->help_text ?? '—' }}</td>
    </tr>
    @endforeach
</table>
@endif

{{-- 4. Worksheet Data: Batch, Sample, Sample Type, Analysis Type, Parameters (steps + config), Results --}}
<table class="parameter" style="width:100%;">
    <tr>
        <td class="section-title" colspan="{{ 4 + $steps->count() + $configFields->count() }}">WORKSHEET DATA</td>
    </tr>
    <tr>
        <th class="parameter" style="width:8%;">Batch</th>
        <th class="parameter" style="width:10%;">Sample</th>
        <th class="parameter" style="width:12%;">Sample Type</th>
        <th class="parameter" style="width:12%;">Analysis Type</th>
        @foreach($steps as $step)
        <th class="parameter" style="min-width:80px;">{{ $step->step }}@if($step->measurands && $step->measurands->isNotEmpty()) ({{ $step->measurands->pluck('name')->implode(', ') }})@endif</th>
        @endforeach
        @foreach($configFields as $field)
        <th class="parameter" style="min-width:70px;">{{ $field->label }}</th>
        @endforeach
    </tr>
    @forelse($sampleRows as $row)
    <tr>
        <td class="parameter">{{ $row['batch_code'] }}</td>
        <td class="parameter">{{ $row['sample_code'] }}</td>
        <td class="parameter">{{ $row['sample_type_name'] ?? '—' }}</td>
        <td class="parameter">{{ $row['analysis_type_name'] ?? '—' }}</td>
        @foreach($steps as $step)
        <td class="parameter">{{ $row['step_values'][$step->id] ?? '—' }}</td>
        @endforeach
        @foreach($configFields as $field)
        <td class="parameter">{{ $row['config_values'][$field->id] ?? '—' }}</td>
        @endforeach
    </tr>
    @empty
    <tr>
        <td class="parameter" colspan="{{ 4 + $steps->count() + $configFields->count() }}" style="text-align:center;">No sample data</td>
    </tr>
    @endforelse
</table>

<p class="footer-text">********** End of Procedure Worksheet **********</p>
<p class="footer-text">{{ config('app.name') }} – {{ $printedAt }}</p>

</body>
</html>
