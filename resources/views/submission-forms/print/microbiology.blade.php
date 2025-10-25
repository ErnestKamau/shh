<!DOCTYPE html>
<html lang="en">
<?php
$active = getActiveCompany();

$headerDetails = $existingValues['sample_header'];
$sampleDetails = $existingValues['sample_details'];

$companyDetails = \App\Models\CRM\CRMCustomer::find($headerDetails['crm_customer_id']);
$companyUnitDetails = \App\Models\CRM\CRMCompanyUnit::find($headerDetails['crm_unit_id']);
$sampleTypeDetails = \App\SampleType::find($headerDetails['sample_type_id']);

$samplePoints = [];

foreach ($sampleDetails as $sample) {
    $samplePoints = array_merge($samplePoints, explode(",", $sample['sample_point_id']));
}

// dd($samplePoints);

$samplePointNames = \App\Models\CRM\SamplePoint::whereIn('id', $samplePoints)->select('name')->pluck('name')->toArray();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serology Laboratory Report</title>
    <style>
        @page {
            margin: 5mm 5mm 5mm 5mm;
            size: A4;

            @bottom-right {
                content: "Page " counter(page);
                font-family: Arial, sans-serif;
                font-size: 11px;
                color: #2c5aa0;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0 auto;
            padding: 0;
        }

        .header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2c5aa0;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c5aa0;
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
        }

        .header-left,
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }

        .report-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .report-single-col {
            width: 100%;
            margin-bottom: 20px;
        }

        .report-left,
        .report-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            color: #2c5aa0;
            text-transform: capitalize;
            text-decoration: underline;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #2c5aa0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .results-table th,
        .results-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .results-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
        }

        .results-table td {
            font-size: 10px;
        }

        .sensitivity-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .sensitivity-table th,
        .sensitivity-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }

        .sensitivity-table th {
            background-color: #e8f0fe;
            font-weight: bold;
            font-size: 10px;
        }

        .sensitivity-table td {
            font-size: 10px;
        }

        .isolate-title {
            font-weight: bold;
            margin: 15px 0 5px 0;
            color: #2c5aa0;
            font-size: 12px;
        }

        .sensitive {
            color: #28a745;
            font-weight: bold;
        }

        .resistant {
            color: #dc3545;
            font-weight: bold;
        }

        .intermediate {
            color: #ffc107;
            font-weight: bold;
        }

        .interpretations {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #2c5aa0;
        }

        .interpretations h3 {
            margin-top: 0;
            color: #2c5aa0;
            font-size: 12px;
        }

        .disclaimers {
            margin-top: 20px;
            font-size: 9px;
            color: #666;
        }

        .disclaimers h4 {
            font-size: 10px;
            margin-bottom: 5px;
            color: #333;
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
            padding: 0 20px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        /* Header for page 2 */
        .header-page2 {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2c5aa0;
        }

        /* Print optimization */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                counter-reset: page;
            }

            .page-number {
                position: fixed;
                right: 15mm;
                bottom: 5mm;
                font-size: 11px;
                color: #2c5aa0;
            }

            .page-number:after {
                content: "Page " counter(page);
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
            border-bottom: 5px solid #2c5aa0;
            margin-bottom: 18px;
            position: relative;
        }

        .header-underline:after {
            content: "";
            display: block;
            width: 100%;
            border-bottom: 1.5px solid #b3c6e0;
            margin-top: 3px;
        }

        .bordered-section {
            border: 4px solid #2c5aa0;
            /* border-radius: 8px; */
            padding: 28px 20px 12px 20px;
            margin-bottom: 24px;
            background: #fafdff;
            box-shadow: 0 2px 8px 0 rgba(44, 90, 160, 0.04);
            position: relative;
            overflow: hidden;
        }

        .bordered-section::before {
            content: "";
            display: block;
            width: calc(100% - 16px);
            height: 0;
            border-bottom: 1.5px solid #b3c6e0;
            position: absolute;
            top: 4px;
            left: 8px;
            right: 8px;
            z-index: 1;
            border-radius: 4px;
            pointer-events: none;
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

        .bordered-section {
            page-break-inside: auto;
        }

        .isolate-block {
            page-break-inside: avoid;
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
                <img src="{{$active->logo}}" alt="Fivet Logo"
                    style="height:80px; object-fit:contain; margin:0 auto 10px; display:block;">
            </td>
            <td class="document-info">
                <div><strong>FM/QA/072</strong></div>
                <div><strong>Revision 04</strong></div>
                <div><strong>Issue Date:</strong> 11/09/2023</div>
            </td>
        </tr>
    </table>
    <div class="header-underline"></div>

    <div class="report-title">MICROBIOLOGY LABORATORY SUBMISSION FORM</div>

    <strong>CLIENT DETAILS</strong><br>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
        <tr>
            <th style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">OWNER/COMPANY NAME</th>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">{{ $companyDetails->name }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">ADDRESS</th>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">{{ $companyDetails->physical_address }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">TELEPHONE</th>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">{{ $companyDetails->telephone1 }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;">EMAIL</th>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;">{{ $companyDetails->email }}</td>
        </tr>
    </table>
    <br>
    <strong>SAMPLING AND SUBMISSION DETAILS</strong><br>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
        <tr>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;" colspan="50%">
                <div style="display: flex; width: 100%;">
                    <div style="width: 50%; padding-right: 10px;">
                        Specimen Type:<span style="font-weight: 400; border-bottom: 1px dotted #000; display: inline-block; width: 80px; margin-left: 5px;">{{$headerDetails['description']}}</span>
                    </div>
                </div>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;" colspan="50%">
                Sample Collection Point/Site: <span style="border-bottom: 1px dotted #000; display: inline-block; min-width: 200px; margin-left: 5px;">{{$companyUnitDetails->name}}</span>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;" colspan="33%">
                Date of Sampling
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['date_collected'])->format("d/m/Y") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;" colspan="33%">
                Time of Sampling
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['date_time_sampling'])->format("H:i") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;" colspan="33%">
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
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;" colspan="33%">
                Date of Submission
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['submission_date'])->format("d/m/Y") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;" colspan="33%">
                Time of Submission
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['submission_date'])->format("H:i") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;" colspan="33%">
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
        <tr>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;" colspan="33%">
                Date of Reception
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['receipt_date'])->format("d/m/Y") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000;" colspan="33%">
                Time of Reception
                <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px">
                    {{ \Carbon\Carbon::parse($headerDetails['receipt_date'])->format("H:i") }}
                </span>
            </td>
            <td style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold;" colspan="33%">
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
        <tr>
            <td colspan="100%" style="padding: 4px 10px">
                NB: By signing and submitting this submission form, the client acknowledges and accepts the test methods to be used. turnaround and fces for the analysis requested. By receiving the samples, the laboratory confirms that it has understood the customer requirenients and has the capability and resources to meet these requirements.
            </td>
        </tr>
        <tr>
            <td colspan="100%" style="padding: 4px 10px">
               <strong>LABORATORY NUMBER:</strong> 
               <span style="font-weight: 400; border-bottom: 1px dotted #000; margin-left: 5px; margin-right:20px"></span>
            </td>
        </tr>
    </table>
    <br>
    <br>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
        <tr>
            <th colspan="66.67" style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold; font-weight: 600; text-decoration: underline; padding: 3px 4px">Tests Required</th>
            <th colspan="33.33"style="text-align: center; padding: 10px; border: 1px solid #000; font-weight: bold;; font-weight: 600; text-decoration: underline;  padding: 3px 4px">Tick Appropriate</th>
        </tr>
        @foreach($sampleDetails as $detail)
        @php($analysisType = \App\AnalysisType::find($detail['analysis_type_id']))
        <tr>
            <th colspan="66.67" style="text-align: left; padding: 10px; border: 1px solid #000; font-weight: bold; padding: 5px">{{$analysisType->name}}</th>
            <th colspan="33.33"style="text-align: center; padding: 10px; border: 1px solid #000; font-weight: bold; padding: 5px">✔</th>
        </tr>
        @endforeach
    </table>
</body>

</html>