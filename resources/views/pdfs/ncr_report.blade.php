<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Non-conformance report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #000;
            font-size: 8.5pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 100px 40px 60px 40px;
        }
        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 70px;
        }
        footer {
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px;
            height: 30px; 
            text-align: center;
            font-size: 7pt;
            color: #777;
            border-top: 0.5px solid #000;
            padding-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .main-report-table {
            border: 1px solid #000;
            width: 100%;
        }
        .main-report-table td, .main-report-table th {
            padding: 6px 10px;
            vertical-align: top;
            border: none;
        }
        .section-header {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5pt;
            border-top: 1px solid #000 !important;
            border-bottom: 1px solid #000 !important;
            padding: 8px 10px !important;
        }
        .section-header-no-line {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5pt;
            border-top: 1px solid #000 !important;
            border-bottom: none !important;
            padding: 8px 10px !important;
        }
        .sub-header {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            border-top: 0.5px solid #000 !important;
            border-bottom: 0.5px solid #000 !important;
        }
        .label {
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            width: 25%;
        }
        .why-box {
            border-left: 2px solid #000;
            padding-left: 10px;
            margin-bottom: 8px;
        }
        .why-label { font-weight: bold; font-size: 7.5pt; color: #555; text-transform: uppercase; }
        .no-border td, .no-border th { border: none !important; }
        .signature-img {
            max-height: 40px;
            max-width: 140px;
            display: block;
            margin: 2px 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        .pagenum:before { content: counter(page); }
        .section-row { page-break-inside: avoid; }
        .border-top-row { border-top: 0.5px solid #000 !important; }
    </style>
</head>
<body>
    @php
        $company = getActiveCompany();
        $logoPath = $company->report_logo ?: $company->logo;
        $base64Logo = $logoPath ? imageTobase64($logoPath, true) : null;

        $whys = $capaRecord->why_why_analysis ?? [];
        if (is_string($whys)) $whys = json_decode($whys, true);

        // Data Mapping Fixes
        $rootCause = !empty($capaRecord->root_cause) ? $capaRecord->root_cause : ($resolution->root_cause_analysis ?? '');
        $correctiveAction = !empty($whys['corrective_action']) ? $whys['corrective_action'] : ($resolution->corrective_action_taken ?? '');

        // Helper for N/A
        if (!function_exists('na')) {
            function na($val) { return empty($val) ? 'N/A' : (is_array($val) ? implode(', ', $val) : $val); }
        }

        // Helper to get signature
        if (!function_exists('getSig')) {
            function getSig($personnel) {
                if (empty($personnel)) return null;
                $user = \App\User::where('name', $personnel)->first();
                if (!$user && preg_match('/^[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}$/i', $personnel)) {
                    $user = \App\User::find($personnel);
                }
                $sigPath = $user?->getSignaturePath();
                return $sigPath ? imageTobase64($sigPath, true) : null;
            }
        }
    @endphp

    <header>
        <table class="no-border" style="border: 0.5px solid #000 !important;">
            <tr>
                <td width="20%" style="vertical-align: middle;">
                    @if($base64Logo)
                        <img src="{{ $base64Logo }}" style="max-height: 55px; max-width: 150px;">
                    @endif
                </td>
                <td width="55%" class="text-center" style="vertical-align: middle;">
                    <div style="font-size: 11pt; font-weight: bold;">{{ strtoupper($company->name) }}</div>
                    <div style="font-size: 10pt; font-weight: bold; margin-top: 2px;">NON-CONFORMANCE REPORT (NCR)</div>
                </td>
                <td width="25%" style="padding: 0;">
                    <table style="border: none; width: 100%;">
                        <tr><td style="border: none; border-bottom: 0.5px solid #000; border-left: 0.5px solid #000; font-size: 7pt;">Doc Ref:</td><td style="border: none; border-bottom: 0.5px solid #000; border-left: 0.5px solid #000; font-weight: bold;">{{ $reportNumber ?? 'LR-04A' }}</td></tr>
                        <tr><td style="border: none; border-bottom: 0.5px solid #000; border-left: 0.5px solid #000; font-size: 7pt;">Version:</td><td style="border: none; border-bottom: 0.5px solid #000; border-left: 0.5px solid #000; font-weight: bold;">{{ str_pad($version ?? '1', 2, '0', STR_PAD_LEFT) }}</td></tr>
                        <tr><td style="border: none; border-left: 0.5px solid #000; font-size: 7pt;">Page:</td><td style="border: none; border-left: 0.5px solid #000; font-weight: bold;"><span class="pagenum"></span></td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div style="float: left;">{{ $company->name }} - Quality Management System</div>
        <div style="float: right;">System Generated Report | NCR Form LR-04A</div>
        <div style="clear: both;"></div>
    </footer>

    <table class="main-report-table">
        <!-- 01. IDENTIFICATION -->
        <tr class="section-row">
            <td colspan="4" class="section-header" style="border-top: none !important;">01. IDENTIFICATION</td>
        </tr>
        <tr class="section-row">
            <td class="label" width="20%">NCR NO:</td>
            <td width="30%" class="font-bold">{{ $resolution->car_no }}</td>
            <td class="label" width="20%">DATE:</td>
            <td>{{ now()->format('d/m/Y') }}</td>
        </tr>
        <tr class="section-row">
            <td class="label">DEPARTMENT:</td>
            <td colspan="3">LABORATORY</td>
        </tr>

        <!-- 02. PROBLEM STATEMENT -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">02. PROBLEM STATEMENT</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" style="min-height: 50px;">
                {!! html_entity_decode($whys['problem_statement'] ?? 'N/A') !!}
            </td>
        </tr>

        <!-- 03. WHY-WHY ANALYSIS -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">03. WHY-WHY ANALYSIS</td>
        </tr>
        <tr class="section-row">
            <td colspan="4">
                @php 
                    $whySteps = $whys['whys'] ?? [];
                    $hasSteps = false;
                @endphp
                @if(is_array($whySteps))
                    @foreach($whySteps as $idx => $step)
                        @if(!empty(trim(strip_tags($step))))
                            @php $hasSteps = true; @endphp
                            <div class="why-box">
                                <div class="why-label">Step {{ $idx + 1 }}: Why?</div>
                                <div class="why-answer">{!! html_entity_decode($step) !!}</div>
                            </div>
                        @endif
                    @endforeach
                @endif
                
                @if(!$hasSteps) 
                    <span style="color: #666;">Analysis in progress.</span> 
                @endif
            </td>
        </tr>

        <!-- 04. ROOT CAUSE & CORRECTIVE ACTION -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">04. ROOT CAUSE & CORRECTIVE ACTION</td>
        </tr>
        <tr class="section-row">
            <td colspan="4">
                <div class="font-bold text-uppercase" style="font-size: 7.5pt; margin-bottom: 5px; color: #555;">Root Cause Identification:</div>
                {!! na($rootCause) !!}
            </td>
        </tr>
        <tr class="section-row">
            <td colspan="4" class="sub-header">Proposed Corrective Action:</td>
        </tr>
        <tr class="section-row">
            <td colspan="4">
                {!! na($correctiveAction) !!}
            </td>
        </tr>

        <!-- 05. CLOSURE & VERIFICATION -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">05. CLOSURE & VERIFICATION</td>
        </tr>
        <tr class="section-row border-top-row">
            <td colspan="4" style="padding: 0;">
                <table class="no-border" style="width: 100%;">
                    <tr>
                        <td width="20%" class="label" style="padding: 10px;">AUTHORIZED BY:</td>
                        <td width="25%" style="padding: 10px;"><strong>{{ $complaint->closedBy->name ?? '........' }}</strong></td>
                        <td width="35%" style="padding: 0;">
                             @php $sigCl = getSig($complaint->closedBy->name ?? null); @endphp
                            @if($sigCl)
                                <img src="{{ $sigCl }}" class="signature-img">
                            @endif
                        </td>
                        <td width="20%" class="text-right" style="padding: 10px;">
                            DATE: <strong>{{ $complaint->date_closed ? \Carbon\Carbon::parse($complaint->date_closed)->format('d/m/Y') : '........' }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 20px; font-size: 7pt; text-align: center; color: #777;">
        <em>This is an official system generated document. Laboratory Management System | CRM Module</em>
    </div>
</body>
</html>
