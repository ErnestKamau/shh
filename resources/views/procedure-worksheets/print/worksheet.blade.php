<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>Procedure Worksheet for {{ $analysisTypeName ?? $procedure->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin-top: 100px;
            margin-bottom: 80px;
            margin-left: 20px;
            margin-right: 20px;
        }

        * {
            font-family: "Times New Roman", "Arial Unicode MS", Times, serif;
            font-size: 11px;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 100px;
            z-index: 1000;
            background-color: white;
            padding-bottom: 2px;
        }

        .footer {
            position: fixed;
            bottom: -70px;
            left: 0;
            right: 0;
            height: 65px;
            z-index: 1000;
            background-color: white;
            font-size: 8px;
        }

        .header-info {
            display: table;
            width: 100%;
            margin: 5px 0;
            font-size: 10px;
        }

        .header-left {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            padding-right: 10px;
        }

        .header-center {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: center;
        }

        .header-right {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            text-align: right;
            padding-left: 10px;
        }

        .company-logo {
            height: 80px;
            max-width: 100%;
        }

        .report-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin: 4px 0 20px 0;
            text-decoration: underline;
        }

        .info-section {
            font-size: 11px;
            border: none;
            padding: 5px 0;
            margin-bottom: 10px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin: 4px 0;
        }

        .info-label {
            display: table-cell;
            width: 22%;
            font-weight: bold;
            vertical-align: top;
            padding-right: 2px;
            white-space: nowrap;
        }

        .info-value {
            display: table-cell;
            width: 78%;
            border-bottom: 1px dotted #000;
            padding-bottom: 1px;
        }

        .three-column {
            display: table;
            width: 100%;
            margin: 6px 0;
        }

        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0 6px 0;
            font-size: 11px;
        }

        table.results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 0 0;
            font-size: 10px;
        }

        table.results-table th,
        table.results-table td {
            border: 1px solid #000;
            padding: 6px 6px;
            text-align: left;
            vertical-align: middle;
        }

        table.results-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .page-number {
            text-align: center;
            font-size: 8px;
            margin-top: 4px;
        }

        .signature-row {
            margin-top: 20px;
            display: table;
            width: 100%;
        }

        .signature-col {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding-right: 10px;
        }

        .signature-label {
            display: inline-block;
            min-width: 70px;
            font-weight: bold;
        }

        .signature-line {
            display: inline-block;
            width: 70%;
            border-bottom: 1px solid #000;
            height: 14px;
        }

        .main-content {
            position: relative;
            z-index: 1;
            margin-top: 0;
            min-height: calc(100vh - 270px);
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    {{-- Fixed Header (company, logo, document control, general info) --}}
    <div class="header">
        <div class="header-info">
            <div class="header-left">
                @if($company ?? false)
                    <strong>{{ $company->name }}</strong><br>
                    {{ $company->street }}<br>
                    P.O BOX {{ $company->address }}<br>
                    {{ $company->location }}<br>
                    {{ optional($company->country)->name }}
                @else
                    <strong>{{ config('app.name') }}</strong>
                @endif
            </div>
            <div class="header-center">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Company Logo" class="company-logo">
                @else
                    <strong>{{ config('app.name') }}</strong>
                @endif
            </div>
            <div class="header-right">
                @php
                    $docNo = $procedure->document_control_no ?? 'FM/SER/001';
                    $rev = $procedure->revision ?? '1';
                    $issue = $procedure->issue_date ? $procedure->issue_date->format('d/m/Y') : $printedAt;
                @endphp
                <span>{{ $docNo }}</span><br>
                <span>Revision {{ $rev }}</span><br>
                <span>Issue Date: {{ $issue }}</span>
            </div>
        </div>
    </div>

    {{-- Fixed footer with Checked By / Date. Page numbers via script below. --}}
    <div class="footer">
        <div class="signature-row">
            <div class="signature-col">
                <span class="signature-label">Checked By:</span>
                @if(!empty($checker_name))
                    <br>
                    <span style="font-size: 9px;">{{ $checker_name }}</span>
                @else
                    <br>
                    <span class="signature-line"></span>
                @endif
            </div>
            <div class="signature-col">
                <span class="signature-label">Signature:</span>
                <br>
                @if(!empty($checker_signature))
                    <span>
                        <img src="{{ signatureToDataUri($checker_signature) }}" alt="Checker signature" style="height:40px; position:relative; top:8px;">
                    </span>
                @else
                    <span class="signature-line"></span>
                @endif
            </div>
            <div class="signature-col" style="padding-right: 0;">
                <span class="signature-label">Date:</span>
                @if(!empty($checker_signed_at))
                    <br>
                    <span style="border-bottom: 1px solid #000; padding: 0 4px; min-width: 60px; display: inline-block;">
                        {{ $checker_signed_at }}
                    </span>
                @else
                    <br>
                    <span class="signature-line"></span>
                @endif
            </div>
        </div>
        <div class="page-number">
            {{-- Populated by Dompdf page_text --}}
        </div>
    </div>

    {{-- Main content --}}
    <div class="main-content">
        <div class="report-title">
            PROCEDURE WORKSHEET FOR {{ strtoupper($analysisTypeName ?? $procedure->name) }}
        </div>

        {{-- Header information block (configurable fields mapped here) --}}
        <div class="info-section">
            <div class="three-column">
                @php
                    $half = ceil($configFields->count() / 2);
                    $col1 = $configFields->slice(0, $half);
                    $col2 = $configFields->slice($half);
                @endphp
                <div class="column">
                    @foreach($col1 as $field)
                        @php
                            $key = $field->field_value_name ?: \Illuminate\Support\Str::slug($field->label, '_');
                            $val = $headerConfig[$key] ?? '';
                            $labelLower = \Illuminate\Support\Str::lower((string) ($field->label ?? ''));
                            $keyLower = \Illuminate\Support\Str::lower((string) $key);
                            $isTemperature = str_contains($labelLower, 'temperature') || str_contains($labelLower, 'temp')
                                || str_contains($keyLower, 'temperature') || str_contains($keyLower, 'temp');
                            if ($isTemperature && is_string($val)) {
                                $trimmed = trim($val);
                                $alreadyHasUnit = str_contains($trimmed, '°') || preg_match('/\b(c|degc|celsius)\b/i', $trimmed) === 1;
                                if ($trimmed !== '' && ! $alreadyHasUnit) {
                                    $val = $trimmed . ' °C';
                                }
                            }
                        @endphp
                        <div class="info-row">
                            <div class="info-label">{{ $field->label }}:</div>
                            <div class="info-value">{{ $val }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="column">
                    @foreach($col2 as $field)
                        @php
                            $key = $field->field_value_name ?: \Illuminate\Support\Str::slug($field->label, '_');
                            $val = $headerConfig[$key] ?? '';
                            $labelLower = \Illuminate\Support\Str::lower((string) ($field->label ?? ''));
                            $keyLower = \Illuminate\Support\Str::lower((string) $key);
                            $isTemperature = str_contains($labelLower, 'temperature') || str_contains($labelLower, 'temp')
                                || str_contains($keyLower, 'temperature') || str_contains($keyLower, 'temp');
                            if ($isTemperature && is_string($val)) {
                                $trimmed = trim($val);
                                $alreadyHasUnit = str_contains($trimmed, '°') || preg_match('/\b(c|degc|celsius)\b/i', $trimmed) === 1;
                                if ($trimmed !== '' && ! $alreadyHasUnit) {
                                    $val = $trimmed . ' °C';
                                }
                            }
                        @endphp
                        <div class="info-row">
                            <div class="info-label">{{ $field->label }}:</div>
                            <div class="info-value">{{ $val }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if(!empty($metaRows))
            <div class="section-title">Sample metadata</div>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Sample code</th>
                        <th>Test name</th>
                        <th>Analyst</th>
                        <th>Method</th>
                        <th>Unit</th>
                        <th>Standard</th>
                        <th>Standard limit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($metaRows as $row)
                        <tr>
                            <td>{{ $row['sample_code'] ?? '—' }}</td>
                            <td>{{ $row['test_name'] ?? '—' }}</td>
                            <td>{{ $row['analyst'] ?? '—' }}</td>
                            <td>{{ $row['method'] ?? '—' }}</td>
                            <td>{{ $row['unit'] ?? '—' }}</td>
                            <td>{{ $row['standard'] ?? '—' }}</td>
                            <td>{{ $row['standard_limit'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Steps table (Step, Measurand, Equipment ID, Analyst) --}}
        <table class="results-table">
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Measurand / Value</th>
                    <th>Equipment ID</th>
                    <th>Analyst</th>
                </tr>
            </thead>
            <tbody>
                @foreach($steps as $step)
                    @php
                        $measurandNames = $step->measurands?->pluck('name', 'id') ?? collect();
                        $valueType = $step->value_type ?: 'text';

                        $formatStepValue = function ($raw) use ($valueType): string {
                            if ($raw === null) {
                                return '';
                            }
                            $value = trim((string) $raw);
                            if ($value === '') {
                                return '';
                            }

                            return match ($valueType) {
                                'date' => (function () use ($value): string {
                                    try {
                                        return \Carbon\Carbon::parse($value)->format('d/m/Y');
                                    } catch (\Throwable) {
                                        return $value;
                                    }
                                })(),
                                'time' => (function () use ($value): string {
                                    try {
                                        if (\preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
                                            return $value;
                                        }
                                        return \Carbon\Carbon::parse($value)->format('H:i');
                                    } catch (\Throwable) {
                                        return $value;
                                    }
                                })(),
                                'datetime' => (function () use ($value): string {
                                    try {
                                        return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
                                    } catch (\Throwable) {
                                        return $value;
                                    }
                                })(),
                                'method_select' => (function () use ($value): string {
                                    $method = \App\AnalysisMethod::find($value);

                                    return $method
                                        ? trim($method->name . ($method->code ? ' (' . $method->code . ')' : ''))
                                        : $value;
                                })(),
                                'equipment_select' => (function () use ($value): string {
                                    $equipment = \App\Models\Equipments\Equipment::find($value);

                                    return $equipment
                                        ? trim($equipment->name . ($equipment->equipment_number ? ' (' . $equipment->equipment_number . ')' : ''))
                                        : $value;
                                })(),
                                'custom_select' => $value,
                                default => $value,
                            };
                        };

                        // Build structured entries so we can underline only the value part.
                        // When multiple samples are selected, aggregate values across them.
                        $entries = [];
                        $hasAnyValues = false;

                        foreach ($sampleRows as $sampleRow) {
                            $detail = $sampleRow['steps'][$step->id] ?? null;

                            if ($detail && is_array($detail['measurand_map'] ?? null)) {
                                foreach ($detail['measurand_map'] as $mid => $val) {
                                    $label = $measurandNames[$mid] ?? $mid;
                                    $entries[] = [
                                        'value' => $formatStepValue($val),
                                        'label' => (string) $label,
                                        'mid' => (string) $mid,
                                    ];
                                    $hasAnyValues = true;
                                }
                            } elseif ($detail && !empty($detail['raw_value'])) {
                                // Fallback when we only have a single raw value
                                $label = $step->measurands && $step->measurands->isNotEmpty()
                                    ? $step->measurands->pluck('name')->implode(', ')
                                    : '';
                                $entries[] = [
                                    'value' => $formatStepValue($detail['raw_value']),
                                    'label' => (string) $label,
                                ];
                                $hasAnyValues = true;
                            }
                        }

                        // If nothing has values yet, show just the measurand labels.
                        if (! $hasAnyValues && $step->measurands && $step->measurands->isNotEmpty()) {
                            $entries[] = [
                                'value' => '',
                                'label' => (string) $step->measurands->pluck('name')->implode(', '),
                            ];
                        }

                        // De-duplicate identical value+label pairs while preserving first appearance order.
                        if (!empty($entries)) {
                            $entries = collect($entries)
                                ->unique(fn ($e) => (string) ($e['mid'] ?? '') . '|' . ($e['value'] ?? '') . '|' . ($e['label'] ?? ''))
                                ->values()
                                ->all();
                        }

                        // If we have measurand-specific value entries (measurand_map) but the
                        // map is partial, ensure we still render labels for measurands that
                        // have no recorded value (so blank inputs show up on the PDF).
                        if (! empty($entries) && $step->measurands && $step->measurands->isNotEmpty()) {
                            $hasMidEntries = collect($entries)->pluck('mid')->filter()->isNotEmpty();
                            if ($hasMidEntries) {
                                $expectedMids = $step->measurands->pluck('id')->map(fn ($id) => (string) $id)->values()->all();
                                $existingMids = collect($entries)
                                    ->pluck('mid')
                                    ->filter(fn ($m) => $m !== null && $m !== '')
                                    ->map(fn ($m) => (string) $m)
                                    ->unique()
                                    ->values()
                                    ->all();

                                foreach ($expectedMids as $expectedMid) {
                                    if (in_array($expectedMid, $existingMids, true)) {
                                        continue;
                                    }

                                    $label = $measurandNames[$expectedMid] ?? $measurandNames[(int) $expectedMid] ?? $expectedMid;
                                    $entries[] = [
                                        'value' => '',
                                        'label' => (string) $label,
                                        'mid' => (string) $expectedMid,
                                    ];
                                }
                            }
                        }

                        // Resolve per-step analysts when available from precomputed map, otherwise fall back to header analyst.
                        $analystNames = $stepAnalystMap[$step->id] ?? ($worksheetHeader['analyst'] ?? '');
                    @endphp
                    @php
                        $equipmentDisplay = '';
                        $eqCollection = $step->equipment ?? collect();
                        if ($eqCollection instanceof \Illuminate\Support\Collection && $eqCollection->isNotEmpty()) {
                            $equipmentDisplay = $eqCollection->map(function ($e) {
                                $number = $e->equipment_number ?? null;
                                return $number
                                    ? $e->name . ' (' . $number . ')'
                                    : $e->name;
                            })->implode(', ');
                        }
                    @endphp
                    <tr>
                        <td>{{ $step->step }}</td>
                        <td>
                            @if(!empty($entries))
                                @foreach($entries as $idx => $entry)
                                    @php
                                        $hasValue = trim($entry['value']) !== '';
                                    @endphp
                                    @if($hasValue)
                                        <span style="border-bottom: 1px dashed #000;">
                                            {{ $entry['value'] }}
                                        </span>
                                        @if(trim($entry['label']) !== '')
                                            {{ ' ' . $entry['label'] }}
                                        @endif
                                    @else
                                        {{ $entry['label'] }}
                                    @endif
                                    @if($idx < count($entries) - 1)
                                        , 
                                    @endif
                                @endforeach
                            @else
                                &nbsp;
                            @endif
                        </td>
                        <td>{{ $equipmentDisplay !== '' ? $equipmentDisplay : ' ' }}</td>
                        <td>{{ $analystNames !== '' ? $analystNames : ' ' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Page break before Test Kit Information if there is kit data --}}
        @if($testKitColumns->isNotEmpty())
            <br>
            <div class="section-title">Test Kit Information</div>
            <table class="results-table">
                <thead>
                    <tr>
                        @foreach($testKitColumns as $col)
                            <th>{{ $col->label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($testKitRows as $row)
                        <tr>
                            @foreach($testKitColumns as $col)
                                <td>{{ $row['values'][$col->id] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $testKitColumns->count() }}" style="text-align:center;">No test kit data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Verdana");
            $textWidth = $fontMetrics->get_text_width($text, $font, $size);
            $rightMargin = 20;
            $x = $pdf->get_width() - $textWidth - $rightMargin;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>
