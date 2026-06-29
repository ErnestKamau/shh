<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Corrective Action and Preventive Action Report</title>
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
            border: 1px solid #000;
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
        .no-border td, .no-border th { border: none !important; }
        .header-grid-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000 !important;
        }
        .header-grid-table td {
            border: 1px solid #000 !important;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .header-grid-table .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .header-grid-table .meta-table td {
            border: none !important;
            padding: 3px 6px;
            font-size: 7.5pt;
        }
        .header-grid-table .meta-table tr:not(:last-child) td {
            border-bottom: 0.5px solid #000 !important;
        }
        .header-grid-table .meta-table td:first-child {
            border-right: 0.5px solid #000 !important;
            width: 45%;
        }
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
        <table class="header-grid-table">
            <tr>
                <td width="20%" style="text-align: center;">
                    @if($base64Logo)
                        <img src="{{ $base64Logo }}" style="max-height: 55px; max-width: 150px; display: block; margin: 0 auto;">
                    @endif
                </td>
                <td width="55%" class="text-center">
                    <div style="font-size: 11pt; font-weight: bold;">{{ strtoupper($company->name) }}</div>
                    <div style="font-size: 9.5pt; font-weight: bold; margin-top: 2px;">CORRECTIVE & PREVENTIVE ACTION REPORT (CAPA)</div>
                </td>
                <td width="25%" style="padding: 0;">
                    <table class="meta-table">
                        <tr>
                            <td>Doc Ref:</td>
                            <td class="font-bold">{{ $reportNumber ?? 'LR-04' }}</td>
                        </tr>
                        <tr>
                            <td>Version:</td>
                            <td class="font-bold">{{ str_pad($version ?? '1', 2, '0', STR_PAD_LEFT) }}</td>
                        </tr>
                        <tr>
                            <td>Page:</td>
                            <td class="font-bold"><span class="pagenum"></span></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div style="float: left;">{{ $company->name }} - Quality Management System</div>
        <div style="float: right;">System Generated Report | CAPA Form LR-04</div>
        <div style="clear: both;"></div>
    </footer>

    <table class="main-report-table">
        <!-- SECTION 1: ASSIGNMENT DETAILS -->
        <tr class="section-row">
            <td colspan="4" class="section-header" style="border-top: none !important;">SECTION 1: ASSIGNMENT DETAILS</td>
        </tr>
        <tr class="section-row">
            <td class="label">CAR NO:</td>
            <td width="25%" class="font-bold" style="font-size: 10pt;">{{ $resolution->car_no }}</td>
            <td class="label">DATE ISSUED:</td>
            <td>{{ $resolution->date_issued ? \Carbon\Carbon::parse($resolution->date_issued)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr class="section-row">
            <td class="label">ASSIGNED TO:</td>
            <td class="font-bold">{{ na($resolution->issued_to) }}</td>
            <td class="label">PROPOSED CLOSE OUT:</td>
            <td>{{ $resolution->proposed_close_out_date ? \Carbon\Carbon::parse($resolution->proposed_close_out_date)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr class="section-row">
            <td class="label">ISSUED BY:</td>
            <td colspan="3">{{ na($resolution->issued_by) }}</td>
        </tr>

        <!-- SECTION 2: RISK & CLASSIFICATION -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">SECTION 2: RISK & CLASSIFICATION</td>
        </tr>
        <tr class="section-row">
            <td class="label">REF. CLAUSE:</td>
            <td colspan="3">{{ na($resolution->ref_clause) }}</td>
        </tr>
        <tr class="section-row">
            <td class="label">CLASSIFICATION:</td>
            <td colspan="3" class="font-bold">{{ na(strtoupper($resolution->car_type)) }}</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" class="sub-header">DETAILS OF NON-CONFORMANCE:</td>
        </tr>
        <tr class="section-row">
            <td colspan="4">
               {!! $capaRecord->details_of_non_conformance ?? ($resolution->findings ?: 'N/A') !!}
            </td>
        </tr>
        <tr class="section-row">
            <td class="label">IDENTIFIED BY:</td>
            <td>
                <strong>{{ na($resolution->capa_identified_by) }}</strong>
                @php $sigCap = getSig($resolution->capa_identified_by); @endphp
                @if($sigCap)
                    <img src="{{ $sigCap }}" class="signature-img" style="margin-top: 5px;">
                @endif
            </td>
            <td class="label">DATE:</td>
            <td><strong>{{ $resolution->capa_identified_date ? \Carbon\Carbon::parse($resolution->capa_identified_date)->format('d/m/Y') : 'N/A' }}</strong></td>
        </tr>

        <!-- SECTION 3: ROOT CAUSE & ACTION PLAN -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">SECTION 3: ROOT CAUSE & ACTION PLAN</td>
        </tr>
        <tr class="section-row">
            <td class="label">RISK LEVEL:</td>
            <td colspan="3" class="font-bold">{{ na($resolution->risk_level) }}</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" class="sub-header">IMMEDIATE ACTION TAKEN (CONTAINMENT):</td>
        </tr>
        <tr class="section-row">
            <td colspan="4">
                {!! na($resolution->action_taken) !!}
            </td>
        </tr>
        <tr class="section-row">
            <td class="label">IDENTIFIED BY:</td>
            <td>
                <strong>{{ na($resolution->corrective_action_by) }}</strong>
                @php $sigRel = getSig($resolution->corrective_action_by); @endphp
                @if($sigRel)
                    <img src="{{ $sigRel }}" class="signature-img" style="margin-top: 5px;">
                @endif
            </td>
            <td class="label">DATE:</td>
            <td><strong>{{ $resolution->corrective_action_date ? \Carbon\Carbon::parse($resolution->corrective_action_date)->format('d/m/Y') : 'N/A' }}</strong></td>
        </tr>

        <tr class="section-row border-top-row">
            <td class="label">ROOT CAUSE SUMMARY:</td>
            <td colspan="3" style="font-weight: bold;">
                {!! na($resolution->root_cause_analysis) !!}
            </td>
        </tr>
        <tr class="section-row">
            <td class="label">IDENTIFIED BY:</td>
            <td>
                <strong>{{ na($resolution->corrective_action_by) }}</strong>
                @if($sigRel)
                    <img src="{{ $sigRel }}" class="signature-img" style="margin-top: 5px;">
                @endif
            </td>
            <td class="label">DATE:</td>
            <td><strong>{{ $resolution->corrective_action_date ? \Carbon\Carbon::parse($resolution->corrective_action_date)->format('d/m/Y') : 'N/A' }}</strong></td>
        </tr>

        <tr class="section-row border-top-row">
            <td colspan="4" class="sub-header">CORRECTIVE ACTION (CA) PLAN:</td>
        </tr>
        <tr class="section-row">
            <td colspan="4">
                @php
                    $whys = $capaRecord->why_why_analysis ?? [];
                    if (is_string($whys)) $whys = json_decode($whys, true);
                    $ca = $whys['corrective_action'] ?? ($resolution->corrective_action_taken ?: 'N/A');
                @endphp
                {!! $ca !!}
            </td>
        </tr>
        <tr class="section-row">
            <td class="label">IDENTIFIED BY:</td>
            <td>
                <strong>{{ na($resolution->corrective_action_by) }}</strong>
                @if($sigRel)
                    <img src="{{ $sigRel }}" class="signature-img" style="margin-top: 5px;">
                @endif
            </td>
            <td class="label">DATE:</td>
            <td><strong>{{ $resolution->corrective_action_date ? \Carbon\Carbon::parse($resolution->corrective_action_date)->format('d/m/Y') : 'N/A' }}</strong></td>
        </tr>

        <!-- SECTION 4: VERIFICATION OF EFFECTIVENESS -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">SECTION 4: VERIFICATION OF EFFECTIVENESS</td>
        </tr>
        <tr class="section-row">
            <td colspan="4">
                <div class="font-bold" style="font-size: 7.5pt; margin-bottom: 5px; color: #555;">VERIFICATION FINDINGS:</div>
                {!! na($resolution->findings) !!}
            </td>
        </tr>
        <tr class="section-row">
            <td class="label">VERIFIED BY:</td>
            <td>
                <strong>{{ na($capaRecord->effectiveness_verified_by) }}</strong>
                @php $sigV = getSig($capaRecord->effectiveness_verified_by); @endphp
                @if($sigV)
                    <img src="{{ $sigV }}" class="signature-img" style="margin-top: 5px;">
                @endif
            </td>
            <td class="label">DATE:</td>
            <td><strong>{{ $capaRecord->effectiveness_date ? \Carbon\Carbon::parse($capaRecord->effectiveness_date)->format('d/m/Y') : 'N/A' }}</strong></td>
        </tr>

        <!-- SECTION 5: FINAL CLOSURE & SIGN-OFF -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">SECTION 5: FINAL CLOSURE & SIGN-OFF</td>
        </tr>
        <tr class="section-row">
            @php
                $closer = $complaint->closedBy;
                $sigCl = getSig($closer->name ?? null);
            @endphp
            <td class="label">CLOSED BY:</td>
            <td>
                <strong>{{ $closer->name ?? '........' }}</strong>
                @if($sigCl)
                    <img src="{{ $sigCl }}" class="signature-img" style="margin-top: 5px;">
                @endif
            </td>
            <td class="label">DATE:</td>
            <td><strong>{{ $complaint->date_closed ? \Carbon\Carbon::parse($complaint->date_closed)->format('d/m/Y') : '........' }}</strong></td>
        </tr>
    </table>

    <div style="margin-top: 20px;">
        <table style="border: 1px solid #000;">
            <tr class="no-border">
                <td width="33%" style="padding: 10px;">
                    Authorized by:<br>
                    <strong>Quality Manager</strong>
                </td>
                <td width="33%" class="text-center" style="padding: 10px;">
                    Issued to:<br>
                    <strong>Responsible Officer / File</strong>
                </td>
                <td width="33%" class="text-right" style="padding: 10px;">
                    Date of Issue:<br>
                    <strong>{{ now()->format('d/m/Y') }}</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
