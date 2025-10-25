<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>Water Analysis Report</title>
    <style>
        @page {
            margin-top: 160px;
            margin-bottom: 100px;
            margin-left: 20px;
            margin-right: 20px;
            @bottom-center {
                content: element(footer);
            }
            @top-center {
                content: element(header);
            }
        }

        * {
            font-family: "Arial", "Times New Roman", sans-serif;
            font-size: 10px;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .header {
            position: fixed;
            top: -140px;
            left: 0;
            right: 0;
            height: 140px;
            z-index: 1000;
            background-color: white;
        }

        .footer {
            position: fixed;
            bottom: -80px;
            left: 0;
            right: 0;
            height: 80px;
            z-index: 1000;
            background-color: white;
            font-size: 8px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-section img {
            height: 40px;
            margin: 0 10px;
        }

        .form-version {
            text-align: center;
            font-weight: bold;
            margin: 5px 0;
        }

        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            text-decoration: underline;
        }

        .info-section {
            margin: 10px 0;
        }

        .info-row {
            display: table;
            width: 100%;
            margin: 3px 0;
        }

        .info-label {
            display: table-cell;
            width: 25%;
            font-weight: bold;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            width: 75%;
            border-bottom: 1px dotted #000;
            padding-bottom: 1px;
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
            padding: 4px;
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
            color: red;
            font-weight: bold;
        }

        .standards-section {
            margin: 15px 0;
            font-size: 8px;
        }

        .standards-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
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
            height: 40px;
            margin: 20px 0 5px 0;
        }

        .signature-title {
            font-weight: bold;
            margin: 5px 0;
        }

        .disclaimer {
            font-size: 7px;
            text-align: justify;
            margin: 10px 0;
        }

        .qr-section {
            text-align: center;
            margin: 10px 0;
        }

        .page-break {
            page-break-before: always;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }

        .stamp-area {
            position: fixed;
            bottom: 100px;
            right: 20px;
            z-index: 1100;
        }

        .stamp-area img {
            height: 60px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="{{ $sadc_logo }}" alt="SADCAS Logo">
            <img src="{{ $report_logo }}" alt="Company Logo">
            <img src="{{ $ilac_logo }}" alt="ILAC Logo">
        </div>
        <div class="form-version">FM/QA/051 Revision 5</div>
        <div class="report-title">CERTIFICATE OF ANALYSIS - WATER</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="disclaimer">
            {{ $disclaimer }}
        </div>
        <div class="qr-section">
            <img src="data:image/svg+xml;base64,{{ $qrcode }}" style="height: 30px;">
            <br>
            <small>Scan to Verify</small>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Customer and Sample Information -->
        <div class="info-section">
            <div class="two-column">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Client:</div>
                        <div class="info-value">{{ $customer->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Address:</div>
                        <div class="info-value">{{ $customer->physical_address }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value">{{ $customer->email }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Phone:</div>
                        <div class="info-value">{{ $customer->telephone1 }}</div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Certificate No:</div>
                        <div class="info-value">{{ $batch->batch_code }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date Received:</div>
                        <div class="info-value">{{ date('d/m/Y', strtotime($batch->receipt_date)) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date Analyzed:</div>
                        <div class="info-value">{{ $analysis_date ? date('d/m/Y', strtotime($analysis_date->start_analysis_date)) : 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date of Report:</div>
                        <div class="info-value">{{ $date }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sample Description Section -->
        <div class="info-section" style="border: 1px solid #000; padding: 10px; margin: 15px 0; font-size: 9px;">
            <div class="two-column">
                <div class="column">
                    <p><strong>Sample Description:</strong> {{ isset($samples[0]) ? ($samples[0]['sample']->comments ?? 'Water') : 'Water' }} x {{ count($samples) }}*</p>
                    <p><strong>Date of Sampling:</strong> {{ date('d/m/Y', strtotime($batch->date_collected)) }}</p>
                    <p><strong>Sample Lab Number:</strong> {{ $batch->batch_code }}</p>
                    <p><strong>Sample Receiving Date:</strong> {{ date('d/m/y', strtotime($batch->receipt_date)) }}</p>
                    <p><strong>Customers Reference / Section:</strong> {{ $batch->reference_number ?? $customer->name }} – Water*</p>
                </div>
                <div class="column">
                    <p><strong>Date Tested:</strong> {{ $analysis_date ? date('d', strtotime($analysis_date->start_analysis_date)) . ' – ' . date('d F Y', strtotime($analysis_date->start_analysis_date)) : 'N/A' }}</p>
                    <p><strong>Test Required:</strong> {{ $batch->description ?? 'Water Microbiological Analysis' }}</p>
                    <p><strong>Method Used:</strong> SOP MB 04 & MB 06</p>
                    <p><strong>Deviations from method?</strong></p>
                    <p><strong>Reason for Deviation:</strong> N/A</p>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <table class="results-table">
            <thead>
                <tr>
                    <th rowspan="2">Sample ID</th>
                    <th rowspan="2">Sample Description</th>
                    @foreach($parameters as $parameter)
                        <th>{{ $parameter->analyte_code }}</th>
                    @endforeach
                    <th rowspan="2">Statement of Conformity</th>
                </tr>
                <tr>
                    @foreach($parameters as $parameter)
                        <th>(mg/l)</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($samples as $sampleData)
                    <tr>
                        <td class="sample-id">{{ $sampleData['sample']->sample_code }}</td>
                        <td class="sample-id">{{ $sampleData['sample']->comments ?? 'Water Sample' }}</td>
                        @foreach($parameters as $parameter)
                            <td>
                                @if(isset($sampleData['results'][$parameter->analyte_code]))
                                    {{ $sampleData['results'][$parameter->analyte_code]['value'] }}
                                @else
                                    N/A
                                @endif
                            </td>
                        @endforeach
                        <td class="{{ strtolower($sampleData['conformity']) == 'pass' ? 'conformity-pass' : 'conformity-fail' }}">
                            {{ $sampleData['conformity'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Acceptable Water Standards -->
        <div class="standards-section">
            <div class="standards-title">Acceptable Water Standards (Kenya)</div>
            @if(!empty($standards))
                @foreach($standards as $parameter => $standardValue)
                    <p><strong>{{ $parameter }}:</strong> {{ $standardValue }} (Kenya Standard KS 459-1:2019)</p>
                @endforeach
            @else
                <p><strong>Total Viable Count (TVC):</strong> ≤ 100 CFU/ml (WHO Guidelines for Drinking Water Quality, 4th Edition)</p>
                <p><strong>Total Coliforms:</strong> 0 CFU/100ml (Kenya Standard KS 459-1:2019)</p>
                <p><strong>E. coli:</strong> 0 CFU/100ml (Kenya Standard KS 459-1:2019)</p>
            @endif
            
            <div style="margin-top: 10px;">
                <p><strong>Decision Rule:</strong></p>
                <p>• PASS - Sample meets the acceptable standards for drinking water</p>
                <p>• FAIL - Sample does not meet one or more acceptable standards for drinking water</p>
            </div>
        </div>

        <!-- Method Information -->
        <div class="info-section">
            <p><strong>Method:</strong> Pour Plate Method (ISO 6222:1999) for TVC; Membrane Filtration Method (ISO 9308-1:2014) for Coliforms and E. coli</p>
            <p><strong>Incubation:</strong> TVC - 48±3 hours at 22±2°C; Coliforms - 24±2 hours at 36±2°C</p>
        </div>

        <!-- Signatures Section -->
        @if($batch_approvers->count() > 0)
        <div class="signatures-section">
            @foreach($batch_approvers as $approver)
                <div class="signature-block">
                    <div class="signature-line">
                        @if($approver->getApproverDetails() && $approver->getApproverDetails()->electronic_sig)
                            <img src="{{ $approver->getApproverDetails()->electronic_sig }}" style="height: 30px; margin-top: 5px;" alt="Signature">
                        @endif
                    </div>
                    <div class="signature-title">{{ $approver->title ?? 'Analyst' }}</div>
                    <div>{{ $approver->approvershortname ?? $approver->approvername }}</div>
                    <div>{{ date('d/m/Y') }}</div>
                </div>
            @endforeach
        </div>
        @endif

        <!-- Stamp if required -->
        @if($is_stamp)
        <div class="stamp-area">
            <img src="{{ $stamp }}" alt="Official Stamp">
            <div style="color: red; font-weight: bold; margin-top: 5px;">{{ date('d M Y') }}</div>
        </div>
        @endif

        <!-- Additional Notes -->
        <div class="info-section" style="margin-top: 30px; font-size: 8px;">
            <p><strong>Notes:</strong></p>
            <ul style="margin: 5px 0; padding-left: 15px;">
                <li>This certificate relates only to the samples tested</li>
                <li>Results are valid at the time of testing</li>
                <li>Samples were analyzed under controlled laboratory conditions</li>
                <li>Certificate not valid without laboratory seal and authorized signatures</li>
                @if($report_type)
                    <li>Report Type: {{ $report_type }}</li>
                @endif
                @if($ammendment)
                    <li>Amendment Reason: {{ $ammendment->reason }}</li>
                @endif
            </ul>
        </div>

        <!-- Laboratory Information -->
        <div class="info-section" style="margin-top: 20px; font-size: 8px; text-align: center;">
            <p><strong>{{ $company->name ?? 'Laboratory Name' }}</strong></p>
            <p>{{ $company->address ?? 'Laboratory Address' }}</p>
            <p>Tel: {{ $company->telephone ?? 'N/A' }} | Email: {{ $company->email ?? 'N/A' }}</p>
            <p>Website: {{ $company->website ?? 'N/A' }}</p>
        </div>
    </div>
</body>
</html>
