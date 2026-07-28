<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>Microbiology Laboratory Report - Aspergillus</title>
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

        .header-left {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            padding-right: 10px;
        }

        .header-center {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: center;
            padding: 0 10px;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
            padding-left: 10px;
        }

        .logo-section {
            text-align: center;
            margin: 10px 0;
        }

        .logo-section img {
            height: 30px;
            margin: 0 20px;
        }

        .company-logo {
            height: 100px;
            max-width: 100%;
        }

        .form-version {
            text-align: center;
            font-weight: bold;
            margin: 5px 0;
            font-size: 9px;
        }

        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0;
            text-decoration: underline;
        }

        .info-section {

            font-size: 10px;
            border: 2px solid #4682B4;
            padding: 10px;
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

        .three-column {
            display: table;
            width: 100%;
            margin: 10px 0;
        }

        .column {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding-right: 15px;
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
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .results-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .results-table .area-cell {
            text-align: left;
            font-weight: bold;
        }

        .intensity-positive {
            font-weight: bold;
            color: #000;
        }

        .key-section {
            margin: 20px 0;
            font-size: 10px;
            padding: 10px;
        }

        .key-item {
            display: table;
            width: 100%;
            margin: 8px 0;
            line-height: 1.3;
        }

        .key-symbol {
            display: table-cell;
            width: 4%;
            font-weight: bold;
            vertical-align: top;
            padding-right: 8px;
            /* border: 1px solid #4682B4; */
        }

        .key-description {
            display: table-cell;
            width: 85%;
            vertical-align: top;
        }

        .interpretation-section {
            margin: 20px 0;
            font-size: 10px;
            border: 2px solid #4682B4;
            padding: 10px;
            min-height: 60px;
        }

        .signatures-section {
            margin-top: 30px;
            display: table;
            width: 100%;
            text-align: center;
        }

        .signature-block {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-line {
            border-bottom: 1px solid #4682B4;
            height: 50px;
            margin: 20px 0 10px 0;
            position: relative;
        }

        .signature-line img {
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            height: 48px;
            z-index: 1;
        }

        .signature-title {
            font-weight: bold;
            margin: 5px 0;
            font-size: 9px;
            text-align: center;
        }

        .signature-name {
            font-size: 9px;
            margin: 2px 0;
            text-align: center;
        }

        .signature-date {
            font-size: 8px;
            margin: 2px 0;
            text-align: center;
        }

        .disclaimer {
            font-size: 8px;
            text-align: justify;
            margin: 15px 0;
            line-height: 1.2;
            border: 2px solid #4682B4;
            padding: 10px;
        }

        .qr-section {
            text-align: left;
            margin: 0;
        }

        .qr-section img {
            height: 60px;
        }

        .sample-area-header {
            background-color: #e6e6e6;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            padding: 8px;
        }

        .stamp-area {
            position: fixed;
            bottom: 120px;
            right: 30px;
            z-index: 1100;
        }

        .stamp-area img {
            height: 80px;
        }

        .stamp-date {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 5px;
            font-size: 10px;
        }

        .method-section {
            margin: 15px 0;
            font-size: 10px;
            border: 2px solid #4682B4;
            padding: 10px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-info">
            <div class="header-left" style="line-height: 1.5;">
                <strong>{{ $company->name ?? 'Laboratory Name' }}</strong><br>
                {{ $company->street ?? 'Laboratory Street' }}<br>
                P.O BOX {{ $company->address ?? 'Laboratory Address' }}<br>
                {{ $company->location ?? 'Laboratory Location' }}<br>
                {{ $company->country ->name?? 'Laboratory Country' }}<br>
                Tel: {{ $company->telephone ?? 'N/A' }}<br>
                Fax: {{ $company->fax ?? 'N/A' }}<br>
                Email: {{ $company->email ?? 'N/A' }}
            </div>
            <div class="header-center">
                <img src="{{ $report_logo }}" alt="Company Logo" class="company-logo">
            </div>
            <div class="header-right" style="line-height: 1.4;">
                <span>{{ $document_code ?? 'FM/QA/051' }}</span><br>
                <span>Revision {{ $revision_number ?? '5' }}</span><br>
                <span>Issue Date: {{ $issue_date ? date('d/m/Y', strtotime($issue_date)) : '16/03/2023' }}</span><br>
                <span>Report No: </span> {{ $batch->batch_code }}<br>
                <span>Customer: </span> {{ $customer->name }}<br>
                <span>Address: </span> {{ $customer->address ?? 'N/A' }}<br>
                <span>P.O BOX: </span> {{ $customer->postal_address ?? 'N/A' }}<br>
                <span>Cell : </span> {{ $customer->telephone1 ?? 'N/A' }}<br>
                <span>Email: </span> {{ $customer->email ?? 'N/A' }}
            </div>
        </div>

        <div class="report-title">MICROBIOLOGY LABORATORY REPORT - ASPERGILLUS</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div style="display: table; width: 100%; height: 100%;">
            <!-- QR Code Section - Left aligned at page start -->
            <div style="display: table-cell; width: 100%; text-align: left; vertical-align: bottom; padding-left: 0;">
                <div class="qr-section" style="text-align: center; margin: 0;">
                    <center>
                        <img src="data:image/svg+xml;base64,{{ $qrcode }}" alt="QR Code">
                        <br>
                        <small>Scan to Verify Report</small>

                    </center>
                </div>
            </div>


        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" style="margin-top: 0;border-top: 1px solid #4682B4; min-height: calc(100vh - 320px);">

        <!-- Sample Information Section -->
        <div class="info-section">
            <div class="three-column">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Sample Description:</div>
                        <div class="info-value">
                            @php
                            $sampleTypeName = 'Feed Sample';
                            if (isset($samples[0]['sample'])) {
                            $firstSample = $samples[0]['sample'];
                            if (isset($firstSample->sample_type_name)) {
                            $sampleTypeName = $firstSample->sample_type_name;
                            } elseif (method_exists($firstSample, 'sampleType') && $firstSample->sampleType) {
                            $sampleTypeName = $firstSample->sampleType->name;
                            }
                            }
                            @endphp
                            {{ $sampleTypeName }} x {{ count($samples) }}*
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Sample Receiving Date:</div>
                        <div class="info-value">{{ date('d/m/y', strtotime($batch->receipt_date)) }}</div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Lab Number:</div>
                        <div class="info-value">{{ $batch->batch_code }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date Tested:</div>
                        <div class="info-value">
                            @php
                                $analysisStart = $analysis_date?->start_analysis_date;
                                $analysisEnd = $analysis_date?->end_analysis_date;
                            @endphp
                            @if($analysisStart && $analysisEnd)
                                {{ date('d', strtotime($analysisStart)) . ' – ' . date('d F Y', strtotime($analysisEnd)) }}
                            @elseif($analysisStart)
                                {{ date('d', strtotime($analysisStart)) . ' – ' . date('d F Y', strtotime($analysisStart)) }}
                            @elseif($analysisEnd)
                                {{ date('d', strtotime($analysisEnd)) . ' – ' . date('d F Y', strtotime($analysisEnd)) }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Customer Reference:</div>
                        <div class="info-value">{{ $batch->reference_number ?? $customer->name }} – Feed*</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date of Reporting:</div>
                        <div class="info-value">{{ $date }}</div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Test Method Section -->
        <div class="method-section">
            <div class="section-title">Test Method:</div>
            <div class="three-column">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Testing Section:</div>
                        <div class="info-value">{{ $batch->lab_section ?? 'Microbiology' }}</div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Test Required:</div>
                        <div class="info-value">{{ $analyte_names }}</div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Method Used:</div>
                        <div class="info-value">{{ $method_names }}</div>
                    </div>
                </div>
            </div>
            <div class="three-column">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Deviations from method:</div>
                        <div class="info-value">
                            {{ ($batch->has_method_deviation ?? false) ? 'Yes' : 'No' }}
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Reason for Deviation:</div>
                        <div class="info-value">
                            @if($batch->has_method_deviation ?? false)
                                {{ $batch->method_deviation_reason ?: 'N/A' }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>
                <div class="column">
                    <!-- Empty column for spacing -->
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="section-title">Results :</div>
            <table class="results-table">
                <thead>
                    <tr>
                        <th rowspan="2">Sample ID</th>
                        <th rowspan="2">Areas</th>
                        @foreach($parameters as $parameter)
                        <th>{{ $parameter->analyte_name ?? $parameter->analyte_code }}</th>
                        @endforeach
                        {{-- <th rowspan="2">Reporting Unit</th> --}}
                        <th rowspan="2">Intensity</th>
                    </tr>
                    <tr>
                        @foreach($parameters as $parameter)
                        <th>({{ $parameter->reporting_unit_id ?? 'CFU/g' }})</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- Display grouped samples by area --}}
                    @foreach($grouped_samples as $areaName => $areaSamples)
                    {{-- Area header row --}}
                    <tr>
                        <td class="sample-area-header" colspan="{{ 4 + count($parameters) }}">
                            {{ $areaName }}
                        </td>
                    </tr>

                    {{-- Samples in this area --}}
                    @foreach($areaSamples as $sampleData)
                    <tr>
                        <td class="sample-id">{{ $sampleData['sample']->sample_code }}</td>
                        <td class="sample-id">{{ $sampleData['sample']->sample_point ?? 'Sample Point' }}</td>
                        @foreach($parameters as $parameter)
                        @php
                        $resultData = $sampleData['results'][$parameter->analyte_code] ?? null;
                        $value = $resultData['value'] ?? 'N/A';
                        $isPositive = stripos($value, 'positive') !== false ||
                        stripos($value, '+') !== false ||
                        (is_numeric($value) && floatval($value) > 0);
                        @endphp
                        <td class="{{ $isPositive ? 'failed-result' : '' }}">{{ $value }}</td>
                        @endforeach
                        {{-- <td>{{ $parameters->first()->reporting_unit_id ?? 'CFU/g' }}</td> --}}
                        <td>{{ $sampleData['intensity'] }}</td>
                    </tr>
                    @endforeach
                    @endforeach

                    {{-- Display ungrouped samples (no area) --}}
                    @foreach($ungrouped_samples as $sampleData)
                    <tr>
                        <td class="sample-id">{{ $sampleData['sample']->sample_code }}</td>
                        <td class="sample-id">{{ $sampleData['sample']->sample_point_name ?? 'Sample Point' }}</td>
                        @foreach($parameters as $parameter)
                        @php
                        $resultData = $sampleData['results'][$parameter->analyte_code] ?? null;
                        $value = $resultData['value'] ?? 'N/A';
                        $isPositive = stripos($value, 'positive') !== false ||
                        stripos($value, '+') !== false ||
                        (is_numeric($value) && floatval($value) > 0);
                        @endphp
                        <td class="{{ $isPositive ? 'failed-result' : '' }}">{{ $value }}</td>
                        @endforeach
                        {{-- <td>{{ $parameters->first()->reporting_unit_id ?? 'CFU/g' }}</td> --}}
                        <td>{{ $sampleData['intensity'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Key Section -->
        <div class="key-section">
            <div class="section-title">Key :</div>
            <div class="key-item">
                <div class="key-symbol">+ : </div>
                <div class="key-description">Low intensity growth</div>
            </div>
            <div class="key-item">
                <div class="key-symbol">++ : </div>
                <div class="key-description">Moderate intensity growth</div>
            </div>
            <div class="key-item">
                <div class="key-symbol">+++ : </div>
                <div class="key-description">High intensity growth</div>
            </div>
            <div class="key-item">
                <div class="key-symbol">- : </div>
                <div class="key-description">No growth detected</div>
            </div>
        </div>

        <!-- Interpretation and Comments Section -->
        <div class="section-title">Interpretation and Comments</div>
        <div class="interpretation-section">
            @if($report_type)
            <p><strong>Report Type:</strong> {{ $report_type }}</p>
            @endif
            @if($ammendment)
            <p><strong>Revision No.:</strong> R{{ str_pad((string) ($ammendment->version_number ?? ($batch->is_amendment ?? 1)), 2, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Amendment Reason:</strong> {{ $ammendment->reason }}</p>
            @endif
            <p>This certificate relates only to the samples tested. Results are valid at the time of testing. Samples were analyzed under controlled laboratory conditions.</p>
            <br>
            <p><strong>Note:</strong> The presence of Aspergillus species in feed samples may indicate potential mycotoxin contamination. Further testing may be recommended based on these results.</p>
        </div>

        <!-- Disclaimers Section -->
        <div class="section-title">Disclaimers</div>
        <div class="disclaimer">
            {{ $disclaimer }}
        </div>
        <div class="disclaimer-" style="margin-top: 10px;">
            Information marked * has been provided by the customer.
        </div>

        <!-- Signatures Section -->
        @if($batch_approvers->count() > 0)
        <div class="signatures-section">
            @php
            $approversCount = $batch_approvers->count();
            $signatureWidth = $approversCount == 1 ? '100%' : '50%';
            @endphp
            @foreach($batch_approvers as $index => $approver)
            <div class="signature-block" style="width: {{ $signatureWidth }};">
                <div class="signature-line">
                    @if($approver->getApproverDetails() && $approver->getApproverDetails()->electronic_sig)
                    <img src="{{ signatureToDataUri($approver->getApproverDetails()->electronic_sig) }}" alt="signature">
                    @endif
                </div>
                <div class="signature-title">
                    @if($index == 0)
                    Authorised By
                    @else
                    Veterinarian
                    @endif
                </div>
                @if($approver->getApproverDetails() && $approver->getApproverDetails()->electronic_sig)
                <div class="signature-name">{{ $approver->approvershortname ?? $approver->approvername }}</div>
                @else
                <div class="signature-name">{{ $approver->approvershortname ?? $approver->approvername }} – Signature</div>
                @endif
                <div class="signature-date">{{ date('d/m/Y') }}</div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Stamp if required -->
        @if($is_stamp)
        <div class="stamp-area">
            <img src="{{ $stamp }}" alt="Official Stamp">
            <div class="stamp-date">{{ date('d M Y') }}</div>
        </div>
        @endif

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

</html>