<!DOCTYPE html>
<html lang="en">
<?php
$active = getActiveCompany();

$headerDetails = $existingValues['sample_header'] ?? [];
$sampleDetails = $existingValues['sample_details'] ?? [];

$companyDetails = isset($headerDetails['crm_customer_id']) ? \App\Models\CRM\CRMCustomer::find($headerDetails['crm_customer_id']) : null;
$companyUnitId = $headerDetails['crm_unit_name'] ?? $headerDetails['crm_unit_id'] ?? null;
$companyUnitDetails = $companyUnitId ? \App\Models\CRM\CRMCompanyUnit::find($companyUnitId) : null;
$sampleTypeDetails = isset($headerDetails['sample_type_id']) ? \App\SampleType::find($headerDetails['sample_type_id']) : null;

$samplePoints = [];

foreach ($sampleDetails as $sample) {
    $samplePointField = $sample['company_sub_unit_id'] ?? $sample['sample_point_id'] ?? null;
    if ($samplePointField) {
        $samplePoints = array_merge($samplePoints, is_array($samplePointField) ? $samplePointField : explode(",", (string) $samplePointField));
    }
}

$samplePoints = array_unique(array_filter($samplePoints));
$samplePointNames = !empty($samplePoints) ? \App\Models\CRM\SamplePoint::whereIn('id', $samplePoints)->get()->pluck('name')->toArray() : [];

// Defaults for optional form fields so the template does not break when keys are missing
$headerDetails['description'] = $headerDetails['description'] ?? '';
$headerDetails['date_time_sampling'] = $headerDetails['date_time_sampling'] ?? $headerDetails['date_collected'] ?? null;
$headerDetails['sampled_by'] = $headerDetails['sampled_by'] ?? $headerDetails['sampling_officer_name'] ?? '';
$headerDetails['submission_date'] = $headerDetails['submission_date'] ?? $headerDetails['receipt_date'] ?? $headerDetails['date_collected'] ?? null;
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Microbiology Laboratory Submission Form</title>
    <style>
        @page {
            margin: 20px;
            margin-bottom: 54px;
            size: A4;
        }

        * {
            font-family: "Times New Roman", "Arial Unicode MS", Times, serif;
            font-size: 10px;
        }

        body {
            margin: 0;
            padding: 0;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4682B4;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            color: #4682B4;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 10px;
            line-height: 1.3;
            color: #666;
            margin-bottom: 15px;
        }

        .header-info {
            display: table;
            width: 100%;
            margin-top: 15px;
            font-size: 9px;
        }

        .header-left,
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }

        .info-section {
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
            vertical-align: top;
            padding-right: 10px;
            white-space: nowrap;
        }

        .info-value {
            display: table-cell;
            width: 65%;
            border-bottom: 1px dotted #000;
            padding-bottom: 2px;
        }

        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 20px 0;
            text-decoration: underline;
            color: #333;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin: 15px 0 10px 0;
            font-size: 11px;
            color: #333;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9px;
        }

        .results-table th,
        .results-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            vertical-align: middle;
        }

        .results-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .sensitivity-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }

        .sensitivity-table th,
        .sensitivity-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .sensitivity-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .isolate-title {
            font-weight: bold;
            margin: 15px 0 5px 0;
            font-size: 11px;
            text-decoration: underline;
        }

        .sensitive {
            color: green;
            font-weight: bold;
        }

        .resistant {
            color: red !important;
            font-weight: bold !important;
        }

        .intermediate {
            color: #666;
            font-weight: bold;
        }

        .interpretations {
            margin-top: 20px;
            font-size: 10px;
            border: 2px solid #4682B4;
            padding: 10px;
            min-height: 40px;
        }

        .interpretations h3 {
            margin-top: 0;
            font-size: 11px;
            font-weight: bold;
            text-decoration: underline;
        }

        .disclaimers {
            margin-top: 20px;
            font-size: 8px;
            text-align: justify;
            line-height: 1.2;
            border: 2px solid #4682B4;
            padding: 10px;
        }

        .disclaimers h4 {
            font-size: 9px;
            margin-bottom: 5px;
        }

        .disclaimers ul {
            margin: 0;
            padding-left: 15px;
        }

        .disclaimers li {
            margin-bottom: 3px;
        }

        .signatures {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .signature-left,
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 50px;
            margin: 20px 0 10px 0;
            font-size: 9px;
        }

        .page-break {
            page-break-before: always;
        }

        .header-page2 {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4682B4;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .page-number {
                font-size: 8px;
                color: #4682B4;
            }
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0 10px;
            width: 33.33%;
        }

        .company-info {
            font-size: 10px;
            line-height: 1.2;
            text-align: left;
        }

        .logo-section {
            text-align: center;
            position: relative;
        }

        .document-info {
            text-align: right;
            font-size: 10px;
            line-height: 1.2;
        }

        .classy-header-underline {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .header-underline {
            width: 100%;
            border-bottom: 2px solid #4682B4;
            margin-bottom: 18px;
            position: relative;
        }

        .bordered-section {
            border: 2px solid #4682B4;
            padding: 10px;
            margin-bottom: 24px;
            position: relative;
        }

        table {
            page-break-inside: avoid;
        }

        .bordered-section,
        .interpretations,
        .disclaimers,
        .signatures {
            page-break-inside: avoid;
        }

        .results-table thead,
        .sensitivity-table thead {
            display: table-header-group;
        }

        .isolate-block {
            page-break-inside: avoid;
        }

        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            font-size: 9px;
            color: #4682B4;
            background: #fff;
            width: 100%;
        }
        .print-footer-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            border-spacing: 0;
            margin: 0;
            padding: 0;
            table-layout: fixed;
        }
        .print-footer-table tr {
            height: 48px;
        }
        .print-footer-table td {
            vertical-align: middle;
            border: none;
            padding: 0 12px;
            line-height: 1;
        }
        .print-footer-table .footer-qr-cell {
            width: 90px;
            text-align: left;
        }
        .print-footer-table .footer-qr-cell img {
            height: 70px;
            width: 70px;
            vertical-align: middle;
        }
        .print-footer-table .scan-note {
            font-size: 8px;
            color: #4682B4;
            font-style: italic;
            vertical-align: middle;
        }
        .print-footer-table .footer-page-cell {
            text-align: right;
            width: auto;
        }
    </style>
</head>

<body>
    <!-- Consistent Header for All Pages -->
    <table class="header-table classy-header-underline">
        <tr>
            <td class="company-info">
                <div>Fivet Laboratory Services</div>
                <div>2 Telford Road</div>
                <div>P.O. Box 3450</div>
                <div>Graniteside, Harare</div>
                <div>Zimbabwe</div>
            </td>
            <td class="logo-section">
                @php $logoSrc = $logoSrc ?? $logoUrl ?? $active->logo ?? ''; @endphp
                @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Fivet Logo"
                    style="height:80px; object-fit:contain; margin:0 auto 10px; display:block;">
                @endif
            </td>
            <td class="document-info">
                <div><strong>{{ $submissionForm->document_code ?? 'FM/QA/072' }}</strong></div>
                <div><strong>Revision Number {{ $submissionForm->version }}</strong></div>
                <div><strong>Issue Date:</strong> {{ $submissionForm->issue_date ? $submissionForm->issue_date->format('d/m/Y') : '11/09/2023' }}</div>
            </td>
        </tr>
    </table>
    <div class="header-underline"></div>

    <div class="report-title">MICROBIOLOGY LABORATORY SUBMISSION FORM</div>

    <strong>CLIENT DETAILS</strong><br>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
        <tr>
            <th style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">OWNER/COMPANY NAME</th>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">{{ optional($companyDetails)->name }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">ADDRESS</th>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">{{ optional($companyDetails)->physical_address }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">TELEPHONE</th>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">{{ optional($companyDetails)->telephone1 }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">EMAIL</th>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">{{ optional($companyDetails)->email }}</td>
        </tr>
    </table>
    <br>
    <strong>SAMPLING AND SUBMISSION DETAILS</strong><br>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; table-layout: fixed;">
        <colgroup>
            <col style="width: 33.33%;">
            <col style="width: 33.33%;">
            <col style="width: 33.34%;">
        </colgroup>
        <tr>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">
                <div style="display: flex; width: 100%;">
                    <div style="width: 50%; padding-right: 10px;">
                        Specimen Type:<span style="font-weight: 400; border-bottom: 1px dotted #000; display: inline-block; width: 80px; margin-left: 5px;">{{$headerDetails['description']}}</span>
                    </div>
                </div>
            </td>
            <td colspan="2" style="text-align: left; padding: 10px; border: 1px solid #000;">
                Sample Collection Point/Site: <span style="border-bottom: 1px dotted #000; display: inline-block; min-width: 200px; margin-left: 5px;">{{ optional($companyUnitDetails)->name ?? '-' }}</span>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">
                Date of Sampling
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['date_collected'])->format("d/m/Y") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">
                Time of Sampling
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    @if(!empty($headerDetails['date_time_sampling']))
                        {{ \Carbon\Carbon::parse($headerDetails['date_time_sampling'])->format("H:i") }}
                    @else
                        -
                    @endif
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">
                Sampled By 
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ $headerDetails['sampled_by'] }}
                </span>
                @if(isset($headerDetails['signature']))
                <br>
                Signature 
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    <img src="{{ $headerDetails['signature'] }}" style="height: 30px; margin: 3px 5px" />
                </span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">
                Date of Submission
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    @if(!empty($headerDetails['submission_date']))
                        {{ \Carbon\Carbon::parse($headerDetails['submission_date'])->format("d/m/Y") }}
                    @else
                        -
                    @endif
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">
                Time of Submission
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    @if(!empty($headerDetails['submission_date']))
                        {{ \Carbon\Carbon::parse($headerDetails['submission_date'])->format("H:i") }}
                    @else
                        -
                    @endif
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">
                Submitted By 
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ $headerDetails['submit_by'] }}
                </span>
                @if(isset($headerDetails['signature_submission']))
                <br>
                Signature 
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    <img src="{{ $headerDetails['signature_submission'] }}" style="height: 30px; margin: 3px 5px" />
                </span>
                @endif
            </td>
        </tr>
        <tr style="background-color: #e6eef7;">
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold; background-color: #e6eef7;">
                Date of Reception
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['receipt_date'])->format("d/m/Y") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; background-color: #e6eef7;">
                Time of Reception
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['receipt_date'])->format("H:i") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold; background-color: #e6eef7;">
                Received By 
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ $headerDetails['submit_by'] }}
                </span>
                @if(isset($headerDetails['signature_reception']))
                <br>
                Signature 
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    <img src="{{ $headerDetails['signature_reception'] }}" style="height: 30px; margin: 3px 5px" />
                </span>
                @endif
            </td>
        </tr>
        <tr style="background-color: #e6eef7;">
            <td colspan="3" style="padding: 4px 10px; background-color: #e6eef7; border: 1px solid #000;">
                NB: By signing and submitting this submission form, the client acknowledges and accepts the test methods to be used. turnaround and fces for the analysis requested. By receiving the samples, the laboratory confirms that it has understood the customer requirenients and has the capability and resources to meet these requirements.
            </td>
        </tr>
    </table>
    <br>
    <br>
    @if(!empty($processedSampleData))
    <strong>Tests Required</strong><br>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 4px;">
        <thead>
            <tr style="background-color: #e6eef7;">
                <th style="text-align: left; padding: 8px; border: 1px solid #000; font-weight: bold; color: #4682B4; background-color: #e6eef7;">Test Required</th>
                <th style="text-align: center; padding: 8px; border: 1px solid #000; font-weight: bold; color: #4682B4; background-color: #e6eef7;">No of Samples</th>
                <th style="text-align: left; padding: 8px; border: 1px solid #000; font-weight: bold; color: #4682B4; background-color: #e6eef7;">Lab No</th>
                <th style="text-align: left; padding: 8px; border: 1px solid #000; font-weight: bold; color: #4682B4; background-color: #e6eef7;">Reported By &amp; Date</th>
                <th style="text-align: left; padding: 8px; border: 1px solid #000; font-weight: bold; color: #4682B4; background-color: #e6eef7;">Sent By &amp; Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($processedSampleData as $sampleTypeGroup)
                @foreach($sampleTypeGroup['analyses'] as $analysisData)
                <tr>
                    <td style="padding: 6px 8px; border: 1px solid #000;">{{ $analysisData['analysis_type_name'] }}</td>
                    <td style="text-align: center; padding: 6px 8px; border: 1px solid #000;">{{ $analysisData['sample_count'] }}</td>
                    <td style="padding: 6px 8px; border: 1px solid #000;">{{ $analysisData['code_range'] }}</td>
                    <td style="padding: 6px 8px; border: 1px solid #000;">{{ $analysisData['reported_info'] }}</td>
                    <td style="padding: 6px 8px; border: 1px solid #000;">Pending</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    @else
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
        <tr>
            <th colspan="66.67" style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold; font-weight: 600; text-decoration: underline; padding: 3px 4px">Tests Required</th>
            <th colspan="33.33"style="text-align: center; padding: 10px; border: 1px solid #000; font-weight: bold;; font-weight: 600; text-decoration: underline;  padding: 3px 4px">Tick Appropriate</th>
        </tr>
        @foreach($sampleDetails as $detail)
        @php($analysisType = \App\AnalysisType::find($detail['analysis_type_id'] ?? null))
        @if($analysisType)
        <tr>
            <th colspan="66.67" style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold; padding: 5px">{{ $analysisType->name }}</th>
            <th colspan="33.33"style="text-align: center; padding: 10px; border: 1px solid #000; font-weight: bold; padding: 5px">✔</th>
        </tr>
        @endif
        @endforeach
    </table>
    @endif

    {{-- Footer: one row, two cells (QR left, page number right) on same center line --}}
    <div class="print-footer">
        <table class="print-footer-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="footer-qr-cell">
                    @if(!empty($footerQrcode))
                    <img src="data:image/svg+xml;base64,{{ $footerQrcode }}" alt="QR Code">
                    @endif
                </td>
                <td class="footer-page-cell">&nbsp;</td>
            </tr>
        </table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Times New Roman");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = $pdf->get_width() - $width - 12;
            $y = $pdf->get_height() - 24;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>