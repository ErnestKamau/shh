<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>{{ $procedure->name }} – Procedure Worksheet</title>
    <style>
        @page {
            size: A4 landscape;
            margin-top: 130px;
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
            top: -110px;
            left: 0;
            right: 0;
            height: 110px;
            z-index: 1000;
            background-color: white;
            padding-bottom: 5px;
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
            margin: 12px 0;
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
            width: 40%;
            font-weight: bold;
            vertical-align: top;
            padding-right: 6px;
            white-space: nowrap;
        }

        .info-value {
            display: table-cell;
            width: 60%;
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
            padding-right: 10px;
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
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
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
                <span class="signature-line"></span>
            </div>
            <div class="signature-col">
                <span class="signature-label">Date:</span>
                <span class="signature-line"></span>
            </div>
        </div>
        <div class="page-number">
            {{-- Populated by Dompdf page_text --}}
        </div>
    </div>

    {{-- Main content --}}
    <div class="main-content">
        <div class="report-title">
            {{ strtoupper($procedure->name) }} WORKSHEET
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
                        @endphp
                        <div class="info-row">
                            <div class="info-label">{{ $field->label }}:</div>
                            <div class="info-value">{{ $val }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Steps table (Step, Measurand, Equipment ID, Analyst) --}}
        <table class="results-table">
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Measurand</th>
                    <th>Equipment ID</th>
                    <th>Analyst</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $firstRow = $sampleRows[0] ?? null;
                @endphp
                @foreach($steps as $step)
                    <tr>
                        <td>{{ $step->step }}</td>
                        <td>
                            @if($step->measurands && $step->measurands->isNotEmpty())
                                {{ $step->measurands->pluck('name')->implode(', ') }}
                            @else
                                &nbsp;
                            @endif
                        </td>
                        <td>
                            @php
                                $equipmentNames = $step->equipment?->pluck('name')->implode(', ') ?? '';
                            @endphp
                            {{ $equipmentNames }}
                        </td>
                        <td>{{ $worksheetHeader['analyst'] ?? '' }}</td>
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
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>
