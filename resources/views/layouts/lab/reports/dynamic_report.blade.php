<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>{{ ($customer->name ?? 'Customer') }} Report</title>
    <style>
        @page {
            margin: 15px 20px 37px 20px;
            size: A4;
        }

        * {
            font-family: "Times New Roman", "Arial Unicode MS", Times, serif;
            font-size: 10px;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .footer {
            position: fixed;
            bottom: 35px;
            left: 0;
            right: 0;
            height: 100px;
            z-index: -1000;
            background-color: white;
            font-size: 8px;
        }

        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 18px 0;
            /* slightly larger gap below title */
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
            margin: 15px 0 18px 0;
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
@include('partials.report-watermark', ['company' => $company ?? null, 'forPdf' => true])

    @if($reportFormat->isSectionVisible('Header') ?? true)
    <div style="position: fixed; left: 0; right: 0;  background-color: #fff; z-index: 1000;">
        <table width="100%" style="border-collapse: collapse; margin-top: 15px;">
            <tr>
                {{-- Left: Company Info --}}
                <td style="width: 33%;">
                    <div>{{ $company->name ?? 'Fivet Laboratory Services' }}</div>
                    <div>{{ $company->street ?? '2 Telford Road' }}</div>
                    <div>P.O. Box {{ $company->address ?? '3450' }}</div>
                    <div>{{ $company->location ?? 'Graniteside, Harare' }}</div>
                    <div>{{ $company->country->name ?? 'Zimbabwe' }}</div>
                </td>

                {{-- Center: Logo --}}
                <td style="width: 34%; text-align: center;">
                    @if(!empty($report_logo))
                    <img src="{{ $report_logo }}" alt="Company Logo" height="80">
                    @endif
                </td>

                {{-- Right: Document Control & Client Info --}}
                <td style="width: 33%; vertical-align: top; text-align: right; font-size: 11px; color: #333;">
                    <div style="font-weight: bold; line-height: 1.4;">
                        <div>{{ $document_code ?? 'FM/QA/047' }}</div>
                        <div>Revision Number {{ $revision_number ?? '5.0 TRIAL' }}</div>
                        <div>Issue Date:
                            @if(isset($issue_date) && $issue_date)
                            {{ date('d/m/Y', strtotime($issue_date)) }}
                            @elseif(isset($date) && $date)
                            {{ date('d/m/Y', strtotime($date)) }}
                            @else
                            03/11/2023
                            @endif
                        </div>
                    </div>

                    <div style="line-height: 1.4; margin-top: 6px;">
                        <div><strong>Report Number:</strong> {{ $batch->batch_code ?? 'N/A' }}</div>
                    </div>

                    <div style="line-height: 1.4; margin-top: 8px;">
                        <div><strong>Client:</strong> {{ $customer->name ?? 'N/A' }}</div>
                        <div><strong>Address:</strong> {{ $customer->address ?? 'N/A' }}</div>
                        <div><strong>P.O BOX:</strong> {{ $customer->postal_address ?? 'N/A' }}</div>
                        <div><strong>Cell:</strong> {{ $customer->telephone1 ?? 'N/A' }}</div>
                        <div><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Blue underline rule --}}
        <div style="width: 100%; border-bottom: 2px solid #4682B4; margin-top: 15px;"></div>
    </div>
    @endif

    @if($reportFormat->isSectionVisible('Footer') ?? true)
    @include('layouts.lab.reports.partials.footer')
    @endif

    <div class="main-content" style="border-top: 1px solid #ddd; padding-top: 185px; min-height: calc(100vh - 320px);">
        @php
        // Fallback sections if none configured
        $activeSections = $reportFormat->sections->where('is_visible', true)->sortBy('section_order');
        if ($activeSections->isEmpty()) {
        $defaultSections = [
        'ReportTitle', 'SampleInfo', 'TestMethods', 'Results', 'Methodology', 'Signatures'
        ];
        } else {
        $defaultSections = $activeSections->pluck('section_name')->toArray();
        // Always ensure the configurable ReportTitle section is present first
        if (!in_array('ReportTitle', $defaultSections, true)) {
        array_unshift($defaultSections, 'ReportTitle');
        }
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
        <div class="section-title">{{ $custom_title ?? 'Interpretation and Comments' }}</div>
        @php
        $displayType = $reportFormat->results_display_type ?? 'grid';
        @endphp
        @if($displayType === 'grid')
        @include('layouts.lab.reports.partials.results_grid', ['custom_title' => $customTitle])
        @elseif($displayType === 'list')
        @include('layouts.lab.reports.partials.results_list', ['custom_title' => $customTitle])
        @elseif($displayType === 'attachment_summary')
        @include('layouts.lab.reports.partials.results_attachment_summary', ['custom_title' => $customTitle])
        @endif
        @include('layouts.lab.reports.partials.remarks')
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