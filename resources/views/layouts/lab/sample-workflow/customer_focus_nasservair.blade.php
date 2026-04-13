@php
    $companyName = (isset($company) && $company) ? ($company->name ?? 'NAS AIRPORT SERVICES LTD') : 'NAS AIRPORT SERVICES LTD';
    $companyLogoPath = (isset($company) && $company && !empty($company->logo)) ? $company->logo : null;
    $companyLogoUrl = $companyLogoPath ? asset($companyLogoPath) : asset('images/company_logo.png');
    if ($isPdf ?? false) {
        $logoPath = ($companyLogoPath && file_exists(public_path($companyLogoPath)))
            ? public_path($companyLogoPath)
            : public_path('images/company_logo.png');
        $companyLogoUrl = file_exists($logoPath)
            ? 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath))
            : '';
    }
    $customerName = (isset($customer) && $customer) ? ($customer->name ?? '') : '';
    $customerAddress = (isset($customer) && $customer) ? ($customer->physical_address ?? $customer->postal_address ?? '') : '';
    $contactName = (isset($primary_contact) && $primary_contact)
        ? trim(($primary_contact->first_name ?? '') . ' ' . ($primary_contact->middle_name ?? '') . ' ' . ($primary_contact->last_name ?? ''))
        : ($batch->submit_by ?? '');
    $contactEmail = (isset($primary_contact) && $primary_contact) ? ($primary_contact->email ?? '') : (isset($customer) && $customer ? $customer->email ?? '' : '');
    $contactPhone = (isset($primary_contact) && $primary_contact)
        ? ($primary_contact->telephone ?? $primary_contact->mobile ?? '')
        : (isset($customer) && $customer ? $customer->telephone1 ?? '' : '');
    $testItems = $testItems ?? ((isset($batch->sample_type) && $batch->sample_type) ? $batch->sample_type->name : ($batch->description ?? ''));
    $sampleCount = $sampleCount ?? (isset($samples) ? $samples->count() : 0);
    $sampleMarks = $sampleMarks ?? (isset($samples) ? $samples->pluck('sample_code')->filter()->implode(', ') : '');
    $testsRequested = $testsRequested ?? (isset($samples) ? $samples->map(function ($s) {
        if (method_exists($s, 'analyteNames')) {
            return implode(', ', $s->analyteNames() ?? []);
        }
        if (method_exists($s, 'getAnalytesName')) {
            return $s->getAnalytesName();
        }
        if (method_exists($s, 'getAnalysisRelation')) {
            return $s->getAnalysisRelation();
        }
        return '-';
    })->unique()->filter()->implode(', ') : '');
    $submittedDate = $submittedDate ?? ($batch->receipt_date ? \Carbon\Carbon::parse($batch->receipt_date)->format('d/m/Y') : '');
    $expectedDate = $expectedDate ?? ((isset($target_date) && $target_date && isset($target_date->date)) ? \Carbon\Carbon::parse($target_date->date)->format('d/m/Y') : '');
    $receivedByName = (isset($review_staff) && $review_staff) ? ($review_staff->name ?? '') : (isset($submission) && $submission ? ($submission->received_by_name ?? '') : (isset($batch) && $batch ? ($batch->receiving_officer_name ?? '') : ''));
    $receivedDateTime = $batch->receipt_date ? \Carbon\Carbon::parse($batch->receipt_date)->format('d/m/Y H:i') : '';
    $docsSettings = $docs_settings ?? [];
    $docRef = $docsSettings['document_ref'] ?? 'LR-44';
    $docVersion = $docsSettings['revision'] ?? '03';
    $docPage = $docsSettings['issue'] ?? '1 of 1';
    $dateOfIssue = $docsSettings['date_issue'] ?? now()->format('d/m/Y');
    $testMethodsStr = $testMethods ?? '';
    $specs = $specCheckboxes ?? ['nas' => false, 'regulatory' => false, 'customer' => false, 'none' => false, 'others' => false];
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NASSERVAIR Sample Submission Form</title>
    @if($isPdf ?? false)
        <style>
            @page {
                margin: 7.5mm 9mm;
            }

            * {
                font-family: Arial, sans-serif;
            }

            body {
                background-color: #fff;
                margin: 0;
                padding: 0;
            }

            .nas-form-container {
                background: white;
            }

            .nas-header {
                position: relative;
                margin-bottom: 4px;
            }

            .nas-header-table {
                width: 100%;
                border-collapse: collapse;
                border: 2px solid #000;
            }

            .nas-header-table td {
                border: 2px solid #000;
                padding: 2px 6px;
                vertical-align: middle;
            }

            .nas-logo {
                width: auto;
                height: 50px;
                margin-top: 2px;
            }

            .nas-company-name {
                font-size: 20px;
                font-weight: bold;
                margin: 0;
                padding: 0;
                line-height: 1.1;
            }

            .nas-form-title {
                font-size: 13px;
                font-weight: bold;
                margin: 2px 0;
                line-height: 1.1;
            }

            .nas-note {
                font-size: 10px;
                margin: 5px 0;
                line-height: 1.4;
                font-style: italic;
            }

            .nas-doc-ref-box {
                border: none;
                padding: 4px 0;
                font-size: 11px;
                background: transparent;
            }

            .nas-doc-ref-box .ref-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
            }

            .nas-doc-ref-box .ref-label {
                font-weight: normal;
                text-align: left;
            }

            .nas-doc-ref-box .ref-value {
                text-align: left;
                margin-left: 5px;
            }

            .nas-section {
                margin: 15px 0;
            }

            .nas-section-header {
                background-color: #e0e0e0;
                border: 1px solid #000;
                padding: 5px;
                font-weight: bold;
                font-size: 13px;
                margin-bottom: 0;
            }

            .nas-section-content {
                border: 1px solid #000;
                border-top: none;
                padding: 10px;
            }

            .nas-label {
                font-weight: 500;
            }

            .nas-underline {
                border-bottom: 1px solid #000;
                display: inline-block;
            }

            .nas-test-table {
                width: 100%;
                border-collapse: collapse;
                margin: 10px 0;
            }

            .nas-table-header,
            .nas-table-cell {
                border: 1px solid #000;
                padding: 6px;
                font-size: 11px;
            }

            .nas-custom-table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
            }

            .nas-custom-grid {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
            }

            .nas-custom-grid td {
                border: 1px solid #000;
                padding: 4px 8px;
                font-size: 11px;
                vertical-align: top;
            }

            .nas-custom-grid-4col .nas-col-label {
                width: 20%;
                font-weight: 500;
            }

            .nas-custom-grid-4col .nas-col-value {
                width: 30%;
            }

            .nas-custom-grid .nas-label {
                font-weight: 500;
            }

            .nas-custom-section {
                margin-top: 80px;
            }

            .nas-footer-table {
                width: 100%;
                /* margin-top: 20px; */
                border-collapse: collapse;
                border: 1px solid #000;
            }

            .nas-footer-cell {
                /* padding: 8px; */
                border: 1px solid #000;
            }

            .text-center {
                text-align: center;
            }

            .text-left {
                text-align: left;
            }

            .text-right {
                text-align: right;
            }

            .align-middle {
                vertical-align: middle;
            }

            .align-top {
                vertical-align: top;
            }

            .d-inline-block {
                display: inline-block;
            }

            .mb-3 {
                margin-bottom: 1rem;
            }

            .nas-box {
                display: inline-block;
                width: 14px;
                height: 14px;
                border: 1px solid #000;
                margin-right: 4px;
                text-align: center;
                line-height: 12px;
                font-size: 10px;
            }

            .nas-box-ticked {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000000' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
                background-size: 12px 12px;
                background-repeat: no-repeat;
                background-position: center;
            }
        </style>
    @else
        <link rel="stylesheet" href="{{ asset('css/nasservair_form.css') }}">
    @endif
</head>

<body>
    <!-- Print Button (hidden on print and when generating PDF) -->
    @if(!($isPdf ?? false))
        <div class="header-print p-3">
            <span class="btn btn-md mb-3 btn-primary print_initiator float-right" onclick="window.print()">
                <i class="mdi mdi-print"></i> Print
            </span>
        </div>
    @endif

    <!-- Form Container -->
    <div class="nas-form-container m-4 card" style="clear:both">
        <div class="card-body">

            <!-- HEADER SECTION -->
            <div class="nas-header">
                <table class="w-100 nas-header-table">
                    <tr>
                        <td colspan="3" class="text-center">
                            <h1 class="nas-company-name">{{ strtoupper($companyName) }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle text-left" style="width: 20%;">
                            <img src="{{ $companyLogoUrl }}" class="nas-logo" alt="Company Logo"
                                onerror="this.onerror=null; this.src='{{ asset('images/company_logo.png') }}';">
                        </td>
                        <td class="align-middle text-center" style="width: 55%;">
                            <h2 class="nas-form-title">SAMPLE SUBMISSION FORM</h2>
                        </td>
                        <td class="align-top text-right" style="width: 25%;">
                            <!-- Document Reference Box -->
                            <div class="nas-doc-ref-box d-inline-block">
                                <div class="ref-row">
                                    <span class="ref-label">Document Ref</span>
                                    <span class="ref-value">: {{ $docRef }}</span>
                                </div>
                                <div class="ref-row">
                                    <span class="ref-label">Version</span>
                                    <span class="ref-value">: {{ $docVersion }}</span>
                                </div>
                                <div class="ref-row">
                                    <span class="ref-label">Page No.</span>
                                    <span class="ref-value">: {{ $docPage }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <p class="nas-note text-center mb-5">
                This form must be completed when requesting testing services of the Laboratory. The
                completed form shall be submitted to the laboratory with
                the corresponding testing sample(s) for review of the contract by the authorized laboratory
                staff.
            </p>

            <!-- CLIENT INFORMATION SECTION -->
            <div class="nas-section">
                <div
                    style="font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 2px; margin-bottom: 6px;">
                    CLIENT INFORMATION
                </div>
                <table style="width: 100%; border-collapse: collapse; margin-top: 4px; border: 1px solid #000;">
                    <tr>
                        <td style="font-size: 12px; padding: 4px; border-bottom: 1px solid #000;">
                            Name: {{ $customerName }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; padding: 4px; border-bottom: 1px solid #000;">
                            Address: {{ $customerAddress }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; padding: 4px; border-bottom: 1px solid #000;">
                            Contact person: {{ $contactName }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; padding: 4px; border-bottom: 1px solid #000;">
                            Email: {{ $contactEmail }} &nbsp;|&nbsp; Phone: {{ $contactPhone }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; padding: 4px;">
                            Comments/Additional information:
                            {!! strip_tags($batch->batch_instructions ?? '', '<strong><b><em><i>') !!}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- SAMPLE INFORMATION SECTION -->
            <div class="nas-section">
                <div
                    style="font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 2px; margin-bottom: 6px;">
                    SAMPLE INFORMATION
                </div>
                <div style="margin-top: 4px; font-size: 12px;">
                    <span style="font-weight: bold;">Test items:</span>
                    <span style="display: inline-block; border-bottom: 1px solid #000; min-width: 55%; padding: 0 4px;">
                        {{ $testItems }}
                    </span>
                    <span style="margin-left: 20px; font-weight: bold;">Number of samples:</span>
                    <span
                        style="display: inline-block; border-bottom: 1px solid #000; min-width: 60px; padding: 0 4px;">
                        {{ $sampleCount }}
                    </span>
                </div>
                <div style="margin-top: 10px; font-size: 12px;">
                    <span style="font-weight: bold;">Sample remarks</span>
                    <span style="font-style: italic; font-weight: bold;">
                        (List or attach list of test items)
                    </span>
                </div>
                <div class="nas-textarea-lines" style="margin-top: 4px;">
                    @if(trim($sampleMarks))
                        @php
                            $wrappedMarks = wordwrap($sampleMarks, 100, "\n");
                        @endphp
                        @foreach(explode("\n", $wrappedMarks) as $line)
                            <div class="nas-line" style="border-bottom: 1px dashed #000; min-height: 20px;">{{ $line }}</div>
                        @endforeach
                    @else
                        @for($i = 0; $i < 3; $i++)
                            <div class="nas-line" style="border-bottom: 1px dashed #000; min-height: 20px;"></div>
                        @endfor
                    @endif
                </div>
            </div>
        </div>

        <!-- TESTS REQUESTED TABLE -->
        <div class="nas-section">
            <table class="nas-test-table">
                <thead>
                    <tr>
                        <th class="nas-table-header">
                            TEST(S) REQUESTED<br>
                            <span class="nas-table-subheader">(List or attach list of test parameter(s))</span>
                        </th>
                        <th class="nas-table-header">
                            TEST<br>METHOD(S)
                        </th>
                        <th class="nas-table-header" style="width: 40px; border: 1px solid #000;">
                            <span style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>
                        </th>
                        <th class="nas-table-header">
                            SPECIFICATIONS REQUIRED<br>
                            <span class="nas-table-subheader">(please tick (<span
                                    style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>) where applicable
                                below)</span>
                        </th>
                    </tr>
                </thead>
                @php
                    $specRowsDef = [
                        ['key' => 'nas', 'label' => 'NAS specifications (Max Limits)'],
                        ['key' => 'regulatory', 'label' => 'Regulatory specifications KEBS/NEMA/EMC'],
                        ['key' => 'customer', 'label' => 'Customer specifications'],
                        ['key' => 'none', 'label' => 'None'],
                        ['key' => 'others', 'label' => 'Others (Specify):'],
                    ];
                    $typeRows = $testsByType ?? [['tests_requested' => $testsRequested ?? '', 'test_methods' => $testMethodsStr ?? '']];
                    $totalRows = max(count($typeRows), count($specRowsDef));
                @endphp
                <tbody>
                    @for ($i = 0; $i < $totalRows; $i++)
                        <tr>
                            <td class="nas-table-cell">
                                @if ($i < count($typeRows))
                                    {{ $typeRows[$i]['tests_requested'] ?: ' ' }}
                                @endif
                            </td>
                            <td class="nas-table-cell">
                                @if ($i < count($typeRows))
                                    {{ $typeRows[$i]['test_methods'] }}
                                @endif
                            </td>
                            <td class="nas-table-cell-checkbox" style="border: 1px solid #000; font-weight: bold;">
                                @if ($i < count($specRowsDef))
                                    {!! ($specs[$specRowsDef[$i]['key']] ?? false) ? '<span style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>' : '' !!}
                                @endif
                            </td>
                            <td class="nas-table-cell">
                                @if ($i < count($specRowsDef))
                                    {{ $specRowsDef[$i]['label'] }}
                                    @if ($specRowsDef[$i]['key'] === 'others')
                                        <span class="nas-underline-inline"></span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- SAMPLE SUBMISSION -->
        <div style="margin-top: 15px; font-size: 12px; white-space: nowrap;">
            Sample(s) submitted by:
            <span style="display: inline-block; border-bottom: 1px solid #000; min-width: 50%; padding: 0 4px;">
                {{ $contactName }}
            </span>
            Date:
            <span style="display: inline-block; border-bottom: 1px solid #000; min-width: 18%; padding: 0 4px;">
                {{ $submittedDate }}
            </span>
        </div>
        <div style="border-bottom: 2px dotted #000; margin-top: 10px;"></div>

        <!-- FOR OFFICIAL USE ONLY SECTION -->
        <div class="nas-section" style="margin-top: 10px;">
            <div style="font-weight: bold; text-transform: uppercase; margin-bottom: 4px; color: #ff6666;">
                FOR OFFICIAL USE ONLY
            </div>
            <div class="nas-section-content-official" style="padding-top: 0; font-size: 12px; line-height: 1.45;">
                <p class="nas-official-note" style="margin-top: 0; font-style: italic;">
                    (To be completed by the authorized laboratory staff reviewing the contract - tick in the box where
                    appropriate)
                </p>

                <div class="nas-official-row">
                    <span class="nas-official-label">Lab capability in performing all requested tests:</span>
                    <span style="margin-right: 10px;">
                        <span
                            class="nas-box">{!! (($batch->lab_capable ?? 0) == 1) ? '<span style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>' : '&nbsp;' !!}</span>
                        YES
                    </span>
                    <span>
                        <span
                            class="nas-box">{!! (($batch->lab_capable ?? 0) != 1) ? '<span style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>' : '&nbsp;' !!}</span>
                        NO
                    </span>
                </div>

                <div class="nas-official-row">
                    <span class="nas-official-label">Quantity of sample acceptable:</span>
                    <span style="margin-right: 10px;">
                        <span
                            class="nas-box">{!! (($batch->sample_quantity_acceptable ?? 0) == 1) ? '<span style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>' : '&nbsp;' !!}</span>
                        YES
                    </span>
                    <span>
                        <span
                            class="nas-box">{!! (($batch->sample_quantity_acceptable ?? 0) != 1) ? '<span style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>' : '&nbsp;' !!}</span>
                        NO
                    </span>
                </div>

                <div class="nas-official-row">
                    <span class="nas-official-label">Any need of subcontracting test(s) requested:</span>
                    <span style="margin-right: 10px;">
                        <span
                            class="nas-box">{!! (($batch->batch_subcontracted_client_approval ?? 0) == 1) ? '<span style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>' : '&nbsp;' !!}</span>
                        YES
                    </span>
                    <span>
                        <span
                            class="nas-box">{!! (($batch->batch_subcontracted_client_approval ?? 0) != 1) ? '<span style="font-family: DejaVu Sans, sans-serif;">&#x2713;</span>' : '&nbsp;' !!}</span>
                        NO
                    </span>
                    <span style="margin-left: 20px;">Subcontractor Lab: <span class="nas-underline"
                            style="width: 150px;">{{ $batch->subcontractor_lab_name ?? '' }}</span></span>
                </div>

                <div class="nas-official-row">
                    <label class="nas-official-label">Conditions of sample:</label>
                    <span class="nas-underline"
                        style="flex: 1; margin-left: 10px;">{{ $batch->condition_quality_sample ?? '' }}</span>
                </div>

                <div class="nas-official-row">
                    <label class="nas-official-label">Expected analysis completion:</label>
                    <span style="margin-left: 10px;">Date: <span class="nas-underline"
                            style="width: 150px;">{{ $expectedDate }}</span></span>
                </div>

                <div class="nas-official-row" style="white-space: nowrap; font-size: 11px;">
                    <label class="nas-official-label">Sample(s) received by:</label>
                    <span class="nas-underline" style="width: 180px; margin-left: 8px;">{{ $receivedByName }}</span>
                    <span style="margin-left: 18px;">Signature:
                        @php 
                            $sig = null;
                            if (isset($submission) && $submission && $submission->batches->first()) {
                                $sig = $submission->batches->first()->declaration_customer_signature;
                            } elseif (isset($batch) && $batch) {
                                $sig = $batch->declaration_customer_signature ?? null;
                            }

                            $sigSrc = null;
                            if (is_string($sig) && trim($sig) !== '') {
                                $sigStr = trim($sig);

                                // Some implementations store JSON like {"data":"data:image/png;base64,..."}.
                                if (str_starts_with($sigStr, '{') && str_ends_with($sigStr, '}')) {
                                    $decoded = json_decode($sigStr, true);
                                    if (is_array($decoded) && isset($decoded['data']) && is_string($decoded['data'])) {
                                        $sigStr = trim($decoded['data']);
                                    }
                                }

                                // Dompdf will try to parse "data:" URIs; ensure they are valid (must include comma + payload).
                                if (str_starts_with($sigStr, 'data:')) {
                                    if (str_contains($sigStr, ',') && preg_match('/^data:[^;,]+(?:;charset=[^;,]+)?;base64,/', $sigStr)) {
                                        $sigSrc = $sigStr;
                                    } else {
                                        $sigSrc = null;
                                    }
                                } else {
                                    // If PDF generation, prefer embedding local files as data URIs (remote is disabled).
                                    if (($isPdf ?? false) && (str_starts_with($sigStr, '/storage/') || str_starts_with($sigStr, 'storage/'))) {
                                        $relative = ltrim($sigStr, '/');
                                        $relative = preg_replace('#^storage/#', 'storage/app/public/', $relative);
                                        $path = base_path($relative);
                                        if (is_string($path) && file_exists($path)) {
                                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                            $mime = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? ('image/' . ($ext === 'jpg' ? 'jpeg' : $ext)) : null;
                                            if ($mime) {
                                                $sigSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                                            }
                                        }
                                    } else {
                                        $sigSrc = $sigStr;
                                    }
                                }
                            }
                        @endphp
                        @if($sigSrc)
                            <img src="{{ $sigSrc }}" style="max-height: 35px; vertical-align: middle; margin-top: -10px;">
                        @else
                            <span class="nas-underline" style="width: 130px;"></span>
                        @endif
                    </span>
                    <span style="margin-left: 15px;">Date/Time: <span class="nas-underline"
                            style="width: 100px;">{{ $receivedDateTime }}</span></span>
                </div>

                <div class="nas-official-row" style="margin-top: 15px;">
                    <label class="nas-official-label" style="font-weight: bold;">Report Serial Number(s):</label>
                    @php
                        $reportSerialNumbers = $reportSerialNumbers ?? (isset($samples) ? trim($samples->pluck('report_number')->filter()->unique()->values()->implode(', ')) : '');
                    @endphp
                    <span class="nas-underline"
                        style="flex: 1; margin-left: 10px;">{{ $reportSerialNumbers ?: ($batch->batch_code ?? '') }}</span>
                </div>
            </div>
        </div>

        <!-- FOOTER SECTION (end of page 1) -->
        <div class="nas-footer" style="page-break-after: always;">
            <table class="nas-footer-table">
                <tr>
                    <td class="nas-footer-cell">
                        <div class="nas-footer-label">Approved by</div>
                    </td>
                    <td class="nas-footer-cell">
                        <div class="nas-footer-label">Date of issue</div>
                    </td>
                    <td class="nas-footer-cell">
                        <div class="nas-footer-label">Issued to</div>
                    </td>
                </tr>
                <tr>
                    <td class="nas-footer-cell">
                        <div class="nas-footer-value">Laboratory Manager</div>
                    </td>
                    <td class="nas-footer-cell">
                        <div class="nas-footer-value">{{ $dateOfIssue }}</div>
                    </td>
                    <td class="nas-footer-cell">
                        <div class="nas-footer-value">
                            <span class="nas-box">&nbsp;</span> File
                            &nbsp;&nbsp;
                            <span class="nas-box">&nbsp;</span> Customer
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    </div>

    @if(!empty($customEntries) && collect($customEntries)->flatten(1)->isNotEmpty())
        <!-- PAGE 2: STORE: CRM customer name (Custom Fields) -->
        <div class="nas-form-container m-4 card" style="clear:both">
            <div class="card-body">
                <div
                    style="font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 2px; margin-bottom: 6px; font-size: 13px;">
                    STORE: {{ $customerName ?: '—' }}
                </div>

                @php
                    $groupedSamples = isset($batches) && $batches->count() > 1
                        ? $samples->groupBy('sample_header_id')
                        : collect([0 => $samples]);
                    $batchesById = isset($batches) ? $batches->keyBy('id') : collect();
                @endphp

                @foreach($groupedSamples as $batchId => $batchSamples)
                    @if(isset($batches) && $batches->count() > 1)
                        @php $groupBatch = $batchesById->get($batchId); @endphp
                        <div
                            style="margin-top: 15px; font-weight: bold; font-size: 12px; background: #e0e0e0; padding: 4px 8px; border: 1px solid #000;">
                            {{ $groupBatch?->sample_type?->name ?? '' }}
                        </div>
                    @endif

                    @foreach($batchSamples as $sample)
                        @php $entries = $customEntries[$sample->id] ?? []; @endphp
                        @if(!empty($entries))
                            <div style="margin-top: 10px; page-break-inside: avoid;">
                                <table class="nas-custom-grid nas-custom-grid-4col" style="margin-top: 0;">
                                    @foreach(array_chunk($entries, 2) as $row)
                                        <tr>
                                            @foreach($row as $entry)
                                                <td class="nas-col-label">{{ $entry['label'] }}</td>
                                                <td class="nas-col-value">{!! strip_tags($entry['value'], '<strong><b><em><i><br><p><u>') !!}</td>
                                            @endforeach
                                            @if(count($row) === 1)
                                                <td class="nas-col-label"></td>
                                                <td class="nas-col-value"></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    <script src="{{ asset('assets/js/libs/jquery/jquery-3.5.1.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.print_initiator').forEach(function (el) {
                el.addEventListener('click', function () {
                    window.print();
                });
            });
        });
    </script>
</body>

</html>