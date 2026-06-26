<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Complaints Investigation Form</title>
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
            border: 1px solid #000; /* Outer border for the report */
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
            border-bottom: none !important; /* No line under sections 2-5 */
            padding: 8px 10px !important;
        }
        .sub-header {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            border-top: 0.5px solid #000 !important;
            border-bottom: 0.5px solid #000 !important;
        }
        .label {
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            width: 20%;
        }
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
        
        $mode = $complaint->mode_of_delivery ?: 'N/A';
        $receivedFromType = $complaint->received_from_type ?? 'Customer';
        
        $capaRecord = \App\Models\CRM\CapaRecord::where('complaint_id', $complaint->id)->first();
        $whys = $capaRecord ? (is_string($capaRecord->why_why_analysis) ? json_decode($capaRecord->why_why_analysis, true) : $capaRecord->why_why_analysis) : [];
        $capa_corrective_action = $whys['corrective_action'] ?? '';
        
        $resolution = $resolution ?? new \App\Models\CRM\Complaintsresolutions();
        
        // Final Corrective Action Selection
        $corrective_action = !empty($capa_corrective_action) ? $capa_corrective_action : ($resolution->corrective_action_taken ?? '');

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
                    <div style="font-size: 12pt; font-weight: bold;">{{ strtoupper($company->name) }}</div>
                    <div style="font-size: 10pt; font-weight: bold; margin-top: 2px;">LABORATORY COMPLAINTS INVESTIGATION FORM</div>
                </td>
                <td width="25%" style="padding: 0;">
                    <table style="border: none; width: 100%;">
                        <tr><td style="border: none; border-bottom: 0.5px solid #000; border-left: 0.5px solid #000; font-size: 7.5pt;">Doc Ref:</td><td style="border: none; border-bottom: 0.5px solid #000; border-left: 0.5px solid #000; font-weight: bold;">{{ $reportNumber ?? 'LR-03' }}</td></tr>
                        <tr><td style="border: none; border-bottom: 0.5px solid #000; border-left: 0.5px solid #000; font-size: 7.5pt;">Version:</td><td style="border: none; border-bottom: 0.5px solid #000; border-left: 0.5px solid #000; font-weight: bold;">{{ str_pad($version ?? '1', 2, '0', STR_PAD_LEFT) }}</td></tr>
                        <tr><td style="border: none; border-left: 0.5px solid #000; font-size: 7.5pt;">Page:</td><td style="border: none; border-left: 0.5px solid #000; font-weight: bold;"><span class="pagenum"></span></td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div style="float: left;">{{ $company->name }} - Laboratory Management System</div>
        <div style="float: right;">System Generated Report | Investigation Form LR-03</div>
        <div style="clear: both;"></div>
    </footer>

    <table class="main-report-table">
        <!-- 01. COMPLAINT INFORMATION -->
        <tr class="section-row">
            <td colspan="4" class="section-header" style="border-top: none !important;">01. COMPLAINT INFORMATION</td>
        </tr>
        <tr class="section-row">
            <td class="label">ORGANIZATION:</td>
            <td colspan="3">{{ na($complaint->organization_name ?? $complaint->received_from) }}</td>
        </tr>
        <tr class="section-row">
            <td class="label">CONTACT NAME:</td>
            <td width="30%">{{ na($complaint->contact_name) }}</td>
            <td class="label">TITLE/POSITION:</td>
            <td>{{ na($complaint->title_position) }}</td>
        </tr>
        <tr class="section-row">
            <td class="label">SOURCE:</td>
            <td>{{ na($receivedFromType) }}</td>
            <td class="label">LAB RELATED:</td>
            <td>{{ $complaint->is_lab_related ? 'YES' : 'NO' }}</td>
        </tr>
        <tr class="section-row">
            <td class="label">DATE RECEIVED:</td>
            <td>{{ $complaint->date ? $complaint->date->format('d/m/Y') : $complaint->created_at->format('d/m/Y') }}</td>
            <td class="label">SERIAL NO:</td>
            <td class="font-bold">{{ $complaint->complaint_id }}</td>
        </tr>

        <!-- 02. NATURE OF COMPLAINT -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">02. NATURE OF COMPLAINT</td>
        </tr>
        <tr class="section-row">
            <td class="label">TEST ITEM:</td>
            <td width="30%">{{ na($complaint->test_item) }}</td>
            <td class="label">REPORT NO:</td>
            <td>{{ na($complaint->report_serial_no) }}</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" class="sub-header">COMPLAINT DETAILS:</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" style="min-height: 80px;">
                {!! $complaint->description !!}
            </td>
        </tr>

        <!-- 03. ROOT CAUSE ANALYSIS -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">03. ROOT CAUSE ANALYSIS</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" style="padding: 0;">
                <table class="no-border" style="width: 100%;">
                    <tr>
                        <td class="label" style="width: 25%; padding: 8px 10px;">CAUSE OF COMPLAINT:</td>
                        <td style="padding: 8px 10px;">{!! na($resolution->cause_of_complaint) !!}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="section-row border-top-row">
            <td colspan="4" style="padding: 0;">
                <table class="no-border" style="width: 100%;">
                    <tr>
                        <td width="15%" class="label" style="padding: 8px 10px;">INVESTIGATED BY:</td>
                        <td width="30%" style="padding: 8px 10px;"><strong>{{ na($resolution->root_cause_by) }}</strong></td>
                        <td width="35%" style="padding: 0;">
                            @php $sigRc = getSig($resolution->root_cause_by); @endphp
                            @if($sigRc)
                                <img src="{{ $sigRc }}" class="signature-img">
                            @endif
                        </td>
                        <td width="20%" class="text-right" style="padding: 8px 10px;">
                            DATE: <strong>{{ $resolution->root_cause_date ? \Carbon\Carbon::parse($resolution->root_cause_date)->format('d/m/Y') : '........' }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 04. RESOLUTION & CORRECTIVE ACTIONS -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">04. RESOLUTION & CORRECTIVE ACTIONS</td>
        </tr>
        @if($resolution && $resolution->car_required)
            <tr class="section-row">
                <td colspan="4" style="background-color: #fffbeb; font-weight: bold; border-bottom: 0.5px solid #000; padding: 6px 10px;">
                    CAR ISSUED: YES | CAR NO: {{ na($resolution->car_no) }}
                </td>
            </tr>
        @endif
        
        <tr class="section-row">
            <td colspan="4" class="sub-header">Immediate Action (Containment):</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" style="min-height: 40px;">
                {!! na($resolution->action_taken) !!}
            </td>
        </tr>
        <tr class="section-row border-top-row">
            <td colspan="4" style="padding: 0;">
                <table class="no-border" style="width: 100%;">
                    <tr>
                        <td width="15%" class="label" style="padding: 8px 10px;">ACTION BY:</td>
                        <td width="30%" style="padding: 8px 10px;"><strong>{{ na($resolution->action_taken_by) }}</strong></td>
                        <td width="35%" style="padding: 0;">
                            @php $sigAt = getSig($resolution->action_taken_by); @endphp
                            @if($sigAt)
                                <img src="{{ $sigAt }}" class="signature-img">
                            @endif
                        </td>
                        <td width="20%" class="text-right" style="padding: 8px 10px;">
                            DATE: <strong>{{ $resolution->action_taken_date ? \Carbon\Carbon::parse($resolution->action_taken_date)->format('d/m/Y') : '........' }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="section-row">
            <td colspan="4" class="sub-header">Corrective Action (Long-term):</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" style="min-height: 40px;">
                {!! na($corrective_action) !!}
            </td>
        </tr>
        <tr class="section-row border-top-row">
            <td colspan="4" style="padding: 0;">
                <table class="no-border" style="width: 100%;">
                    <tr>
                        <td width="15%" class="label" style="padding: 8px 10px;">ACTION BY:</td>
                        <td width="30%" style="padding: 8px 10px;"><strong>{{ na($resolution->corrective_action_by) }}</strong></td>
                        <td width="35%" style="padding: 0;">
                            @php $sigCa = getSig($resolution->corrective_action_by); @endphp
                            @if($sigCa)
                                <img src="{{ $sigCa }}" class="signature-img">
                            @endif
                        </td>
                        <td width="20%" class="text-right" style="padding: 8px 10px;">
                            DATE: <strong>{{ $resolution->corrective_action_date ? \Carbon\Carbon::parse($resolution->corrective_action_date)->format('d/m/Y') : '........' }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 05. REVIEW & CLOSURE -->
        <tr class="section-row">
            <td colspan="4" class="section-header-no-line">05. REVIEW & CLOSURE</td>
        </tr>
        <tr class="section-row">
            <td colspan="4" style="padding: 0;">
                <table class="no-border" style="width: 100%; border-bottom: 0.5px solid #000;">
                    <tr>
                        <td width="25%" class="label" style="padding: 8px 10px; background-color: #fcfcfc;">CLIENT REMARKS:</td>
                        <td style="padding: 8px 10px;">{!! na($resolution->client_remarks) !!}</td>
                    </tr>
                    <tr>
                        <td width="25%" class="label" style="padding: 8px 10px; background-color: #fcfcfc;">CLOSURE REMARKS:</td>
                        <td style="padding: 8px 10px;">
                            @if($complaint->is_approved_for_closure || $complaint->is_closed)
                                {!! na($resolution->internal_remarks ?: 'Resolution approved and verified.') !!}
                            @else
                                <em style="color: #666;">Complaint in progress. Pending final closure review.</em>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="section-row">
            <td colspan="4" style="padding: 0;">
                <table class="no-border" style="width: 100%;">
                    <tr>
                        @php
                            $closer = $complaint->closedBy;
                            $closureDate = $complaint->date_closed ?? ($complaint->chainOfCustody()->where('action', 'like', '%Approval Recorded%')->latest()->first()->created_at ?? null);
                            $closerPosition = $closer ? $closer->position_name() : null;
                        @endphp
                        <td width="15%" class="label" style="padding: 12px 10px;">REVIEWED BY:</td>
                        <td width="30%" style="padding: 12px 10px;"><strong>{{ $closer->name ?? '........' }}</strong></td>
                        <td width="35%" style="padding: 0;">
                            @php $sigCl = getSig($closer->name ?? null); @endphp
                            @if($sigCl)
                                <img src="{{ $sigCl }}" class="signature-img">
                            @endif
                        </td>
                        <td width="20%" class="text-right" style="padding: 12px 10px;">
                            DATE: <strong>{{ $closureDate ? \Carbon\Carbon::parse($closureDate)->format('d/m/Y') : '........' }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 20px;">
        <table style="border: 1px solid #000;">
            <tr class="no-border">
                <td width="33%" style="padding: 10px;">
                    Authorized by:<br>
                    <strong>{{ $closerPosition && $closerPosition !== 'n/a' ? $closerPosition : ($closer->name ?? 'Pending') }}</strong>
                </td>
                <td width="33%" class="text-center" style="padding: 10px;">
                    Distribution:<br>
                    <strong>Quality File / CRM</strong>
                </td>
                <td width="33%" class="text-right" style="padding: 10px;">
                    Date of Issue:<br>
                    <strong>{{ $complaint->intake_approved_at ? $complaint->intake_approved_at->format('d/m/Y') : now()->format('d/m/Y') }}</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
