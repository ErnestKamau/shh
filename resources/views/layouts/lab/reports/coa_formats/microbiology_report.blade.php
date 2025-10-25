<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>Microbiology Laboratory Report</title>
    <style>
        @page {
            margin-top: 200px;
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
            top: -180px;
            left: 0;
            right: 0;
            height: 160px;
            z-index: 1000;
            background-color: white;
           
            padding-bottom: 10px;
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

        .logo-section {
            text-align: center;
            margin: 10px 0;
        }

        .logo-section img {
            height: 30px;
            margin: 0 20px;
        }

        .header-info {
            display: table;
            width: 100%;
            margin: 10px 0;
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
            text-align: left;
            padding-left: 10px;
        }

        .company-logo {
            height: 120px;
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
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
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

        .conformity-fail {
            color: red !important;
            font-weight: bold !important;
        }

        .failed-result {
            color: red !important;
            font-weight: bold !important;
        }

        .sample-area-header {
            background-color: #e6e6e6;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            padding: 8px;
        }

        .standards-row {
            background-color: #f8f8f8;
            font-weight: bold;
            font-style: italic;
        }

        .standards-row .standard-label {
            text-align: left;
            font-weight: bold;
        }

        .standards-section {
            margin: 20px 0;
            font-size: 9px;
            border: 2px solid #4682B4;
            padding: 10px;
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
        }

        .signature-block {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 50px;
            margin: 20px 0 10px 0;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .signature-line img {
            height: 40px;
            max-width: 100%;
        }

        .signature-title {
            font-weight: bold;
            margin: 5px 0;
            font-size: 9px;
        }

        .signature-name {
            font-size: 9px;
            margin: 2px 0;
        }

        .signature-date {
            font-size: 8px;
            margin: 2px 0;
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

        .page-number {
            text-align: center;
            font-size: 8px;
            margin: 5px 0;
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

        .decision-rule {
            margin: 15px 0;
            font-size: 9px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .page-break {
            page-break-before: always;
        }

        .main-content {
            position: relative;
            z-index: 1;
        }

        /* Ensure consistent spacing on all pages */
        .info-section:first-of-type {
            margin-top: 0;
        }
        
        /* Force consistent page layout */
        .main-content {
            position: relative;
            z-index: 1;
            margin-top: 0 !important;
            /* padding-top: 25px !important; */
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
            <div class="header-right" style="text-align: right;line-height: 1.2;">
                <span>FM/QA/100</span><br>
                <span>Revision 2</span><br>
                <span>Issue Date: 16/03/2023</span><br><br>
                <span>Report No: </span> {{ $batch->batch_code }}<br>
                <span>Customer: </span> {{ $customer->name }}<br>
                <span>Address: </span> {{ $customer->address ?? 'N/A' }}<br>
                <span>P.O BOX: </span> {{ $customer->postal_address ?? 'N/A' }}<br>
                <span>Cell : </span> {{ $customer->telephone1 ?? 'N/A' }}<br>
                <span>Email: </span> {{ $customer->email ?? 'N/A' }}<br>
            </div>
        </div>
        
        <div class="report-title">MICROBIOLOGY LABORATORY REPORT</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div style="display: table; width: 100%; height: 100%;">
            <!-- QR Code Section - Left aligned at page start -->
            <div style="display: table-cell; width: 25%; text-align: left; vertical-align: bottom; padding-left: 0;">
                <div class="qr-section" style="text-align: left; margin: 0;">
                    <img src="data:image/svg+xml;base64,{{ $qrcode }}" alt="QR Code">
                    <br>
                    <small>Scan to Verify Report</small>
                </div>
            </div>
            
            <!-- SADCAS Logo with Caption - Centered -->
            <div style="display: table-cell; width: 25%; text-align: center; vertical-align: bottom;">
                <div style="text-align: center;">
                    <img src="{{ $sadc_logo }}" alt="SADCAS Logo" style="height: 55px; margin: 0;">
                    <div style="margin-top: 2px; font-size: 10px; font-weight: bold;">
                        TEST-1 0028<br>
                        VET 009
                    </div>
                </div>
            </div>
            
            <!-- ILAC Logo - Right aligned -->
            <div style="display: table-cell; width: 25%; text-align: center; vertical-align: bottom;">
                <div style="text-align: center;">
                    <img src="{{ $ilac_logo }}" alt="ILAC Logo" style="height: 80px; margin: 0;">
                </div>
            </div>
            
            <!-- Page Number Section - Far right -->
            <div style="display: table-cell; width: 25%; text-align: center; vertical-align: bottom;">
                {{-- Page numbers handled by script --}}
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" style="margin-top: 0;border-top: 1px solid #ddd; min-height: calc(100vh - 320px);">
        
        <!-- Sample Information Section -->
        <div class="info-section">
            <div class="three-column">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Sample Description:</div>
                        <div class="info-value">
                            {{ $sample_type_name ?? 'Water Sample' }} x {{ array_sum(array_map('count', $grouped_samples)) + count($ungrouped_samples) }}*
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
                        <div class="info-value">{{ $analysis_date ? date('d', strtotime($analysis_date->start_analysis_date)) . ' – ' . date('d F Y', strtotime($analysis_date->start_analysis_date)) : 'N/A' }}</div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Customer Reference:</div>
                        <div class="info-value">{{ $batch->reference_number ?? $customer->name }} – Water*</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date of Sampling:</div>
                        <div class="info-value">{{ date('d/m/Y', strtotime($batch->date_collected)) }}</div>
                    </div>
                </div>
            </div>
            <div class="three-column">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Date of Report:</div>
                        <div class="info-value">{{ $date }}</div>
                    </div>
                </div>
                <div class="column">
                    <!-- Empty column for spacing -->
                </div>
                <div class="column">
                    <!-- Empty column for spacing -->
                </div>
            </div>
        </div>

        <!-- Test Method Section -->
        <div class="method-section">
            <div class="section-title">Test Method:</div>
            <div class="three-column">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Test Required:</div>
                        <div class="info-value">{{ $batch->description ?? 'Water Microbiological Analysis' }}</div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Method Used:</div>
                        <div class="info-value">
                            @php
                                $methods = [];
                                foreach($parameters as $parameter) {
                                    // Get method information from the selected fields
                                    $method = $parameter->method_name ?? $parameter->method_code ?? null;
                                    if ($method) {
                                        $methods[] = $method;
                                    }
                                }
                                $uniqueMethods = array_unique($methods);
                                $methodText = !empty($uniqueMethods) ? implode(', ', $uniqueMethods) : 'SOP MB 04 & MB 06';
                            @endphp
                            {{ $methodText }}
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Deviations from method?</div>
                        <div class="info-value">No</div>
                    </div>
                </div>
            </div>
            <div class="three-column">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Reason for Deviation:</div>
                        <div class="info-value">N/A</div>
                    </div>
                </div>
                <div class="column">
                    <!-- Empty column for spacing -->
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
                    <th rowspan="2">Sample Point</th>
                    @foreach($parameters as $parameter)
                        <th>{{ $parameter->analyte_code }}</th>
                    @endforeach
                    <th rowspan="2">Statement of Conformity. Pass/Fail</th>
                </tr>
                <tr>
                    @foreach($parameters as $parameter)
                        <th>({{ $parameter->reporting_unit_id }})</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{-- Display grouped samples by area --}}
                @foreach($grouped_samples as $areaName => $areaSamples)
                    {{-- Area header row --}}
                    <tr>
                        <td class="sample-area-header" colspan="{{ 3 + count($parameters) }}">
                            {{ $areaName }}
                        </td>
                    </tr>
                    
                    {{-- Samples in this area --}}
                    @foreach($areaSamples as $sampleData)
                        <tr>
                            <td class="sample-id">{{ $sampleData['sample']->sample_code }}</td>
                            <td class="sample-id">{!! $sampleData['sample']->sample_point ?? 'Water Sample' !!}</td>
                            @foreach($parameters as $parameter)
                                @php
                                    $resultData = $sampleData['results'][$parameter->analyte_code] ?? null;
                                    $value = $resultData['value'] ?? 'N/A';
                                    $remark = strtolower($resultData['remark'] ?? 'pass');
                                    $isFailed = $remark === 'fail' || $remark === 'failed' || $remark === 'failure';
                                @endphp
                                <td class="{{ $isFailed ? 'failed-result' : '' }}">
                                    {{ $value }}
                                </td>
                            @endforeach
                            <td class="{{ strtolower($sampleData['conformity']) == 'pass' ? 'conformity-pass' : 'conformity-fail' }}">
                                {{ $sampleData['conformity'] }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach

                {{-- Display ungrouped samples (no area) --}}
                @foreach($ungrouped_samples as $sampleData)
                    <tr>
                        <td class="sample-id">{{ $sampleData['sample']->sample_code }}</td>
                        <td class="sample-id">{!! $sampleData['sample']->sample_point ?? 'Water Sample' !!}</td>
                        @foreach($parameters as $parameter)
                            @php
                                $resultData = $sampleData['results'][$parameter->analyte_code] ?? null;
                                $value = $resultData['value'] ?? 'N/A';
                                $remark = strtolower($resultData['remark'] ?? 'pass');
                                $isFailed = $remark === 'fail' || $remark === 'failed' || $remark === 'failure';
                            @endphp
                            <td class="{{ $isFailed ? 'failed-result' : '' }}">
                                {{ $value }}
                            </td>
                        @endforeach
                        <td class="{{ strtolower($sampleData['conformity']) == 'pass' ? 'conformity-pass' : 'conformity-fail' }}">
                            {{ $sampleData['conformity'] }}
                        </td>
                    </tr>
                @endforeach

                {{-- Standards row --}}
                <tr class="standards-row">
                    <td class="standard-label" colspan="2">
                        {{ $standard_codes ?? 'Kenya Standards' }}
                    </td>
                    @foreach($parameters as $parameter)
                        <td>
                            {{ $standards[$parameter->analyte_code] ?? 'NS' }}
                        </td>
                    @endforeach
                    <td>-</td>
                </tr>
            </tbody>
        </table>

        {{-- Decision Rule --}}
        <div class="decision-rule">
            <p><strong>Decision Rule:</strong></p>
            <p>• <strong>PASS</strong> - Sample meets the acceptable standards for drinking water according to Kenya Standards</p>
            <p>• <strong>FAIL</strong> - Sample does not meet one or more acceptable standards for drinking water according to Kenya Standards</p>
        </div>
        </div>

        

        <!-- Interpretation and Comments Section -->
        <div class="section-title">Interpretation and Comments</div>
        <div class="interpretation-section">
            @if($report_type)
                <p><strong>Report Type:</strong> {{ $report_type }}</p>
            @endif
            @if($ammendment)
                <p><strong>Amendment Reason:</strong> {{ $ammendment->reason }}</p>
            @endif
            <p>This certificate relates only to the samples tested. Results are valid at the time of testing. Samples were analyzed under controlled laboratory conditions.</p>
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
            @foreach($batch_approvers as $approver)
                <div class="signature-block">
                    <div class="signature-line">
                        @if($approver->getApproverDetails() && $approver->getApproverDetails()->electronic_sig)
                            <center>
                                <img src="{{ getCoaApproverSignature($approver->getApproverDetails()->electronic_sig) }}" style="height:48px;z-index:-10;position:relative;" alt="signature">
                            </center>
                        @endif
                    </div>
                    <div class="signature-title">{{ $approver->title ?? 'Analyst' }}</div>
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