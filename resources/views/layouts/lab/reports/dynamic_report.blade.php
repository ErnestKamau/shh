<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>Report</title>
    <style>
        @page {
            margin-top: 240px;
            margin-bottom: 90px;
            margin-left: 20px;
            margin-right: 20px;
        }

        * {
            font-family: "Times New Roman", "Arial Unicode MS", Times, serif;
            font-size: 10px;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .header {
            position: fixed;
            top: -220px;
            left: 0;
            right: 0;
            height: 215px;
            z-index: 1000;
            background-color: white;
            padding-bottom: 5px;
        }

        .footer {
            position: fixed;
            bottom: -105px;
            left: 0;
            right: 0;
            height: 100px;
            z-index: 1000;
            background-color: white;
            font-size: 8px;
        }

        .header-info {
            display: table;
            width: 100%;
            margin: 5px 0;
            font-size: 9px;
        }

        .header-left,
        .header-right {
            width: 30%;
            display: table-cell;
            vertical-align: top;
        }

        .header-center {
            width: 40%;
            display: table-cell;
            vertical-align: top;
            text-align: center;
        }

        .company-logo {
            height: 100px;
            max-width: 100%;
        }

        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .info-section,
        .method-section,
        .standards-section,
        .interpretation-section {
            font-size: 10px;
            border: 2px solid #4682B4;
            padding: 10px;
            margin: 15px 0;
        }

        .info-row {
            display: table;
            width: 100%;
            margin: 5px 0;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: bold;
        }

        .info-value {
            display: table-cell;
            width: 65%;
            border-bottom: 1px dotted #000;
        }

        .three-column {
            display: table;
            width: 100%;
            margin: 10px 0;
        }

        .column {
            display: table-cell;
            width: 33.33%;
            padding-right: 15px;
            vertical-align: top;
        }

        .column:last-child {
            padding-right: 0;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin: 15px 0 10px 0;
            font-size: 11px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9px;
        }

        .results-section {
            margin: 15px 0;
            border: 2px solid #4682B4;
            padding: 10px;
        }

        .results-table th,
        .results-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .results-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .results-table .sample-id {
            text-align: left;
            font-weight: bold;
        }

        .conformity-pass {
            color: green;
            font-weight: bold;
        }

        .conformity-fail,
        .failed-result {
            color: red !important;
            font-weight: bold !important;
        }

        .sample-area-header {
            background-color: #e6e6e6;
            font-weight: bold;
            text-align: center;
        }

        .standards-row {
            background-color: #f8f8f8;
            font-weight: bold;
            font-style: italic;
        }

        .signatures-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .signature-block {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 50px;
            margin: 20px 0 10px 0;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .signature-line img {
            height: 40px;
        }

        .disclaimer {
            font-size: 8px;
            text-align: justify;
            margin: 15px 0;
            line-height: 1.2;
            border: 2px solid #4682B4;
            padding: 10px;
        }

        .main-content {
            position: relative;
            z-index: 1;
            margin-top: 0 !important;
        }
    </style>
</head>

<body>

    @if($reportFormat->isSectionVisible('Header') ?? true)
    @include('layouts.lab.reports.partials.header')
    @endif

    @if($reportFormat->isSectionVisible('Footer') ?? true)
    @include('layouts.lab.reports.partials.footer')
    @endif

    <div class="main-content" style="border-top: 1px solid #ddd; min-height: calc(100vh - 320px);">
        @php
        // Fallback sections if none configured
        $activeSections = $reportFormat->sections->where('is_visible', true)->sortBy('section_order');
        if ($activeSections->isEmpty()) {
        $defaultSections = [
        'ReportTitle', 'SampleInfo', 'TestMethods', 'Results', 'Methodology', 'Signatures'
        ];
        } else {
        $defaultSections = $activeSections->pluck('section_name')->toArray();
        }
        @endphp

        @foreach($defaultSections as $sectionName)
        @php
        $section = $activeSections->firstWhere('section_name', $sectionName);
        $customTitle = $section ? $section->custom_title : null;
        @endphp

        @switch($sectionName)
        @case('ReportTitle')
        @include('layouts.lab.reports.partials.title', ['custom_title' => $customTitle])
        @break
        @case('SampleInfo')
        @include('layouts.lab.reports.partials.sample_info')
        @break
        @case('TestMethods')
        @include('layouts.lab.reports.partials.test_methods')
        @break
        @case('Results')
        @if(($reportFormat->results_display_type ?? 'grid') == 'grid')
        @include('layouts.lab.reports.partials.results_grid', ['custom_title' => $customTitle])
        @elseif(($reportFormat->results_display_type ?? 'grid') == 'list')
        @include('layouts.lab.reports.partials.results_list', ['custom_title' => $customTitle])
        @endif
        @break
        @case('Methodology')
        @include('layouts.lab.reports.partials.methodology', ['custom_title' => $customTitle])
        @break
        @case('Signatures')
        @include('layouts.lab.reports.partials.signatures')
        @break
        @endswitch
        @endforeach
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Verdana");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 1;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>