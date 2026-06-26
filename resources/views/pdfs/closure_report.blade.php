<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Complaint Closure Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #2d3748;
            font-size: 9pt;
            line-height: 1.5;
        }

        @page {
            margin: 180px 30px 60px 30px;
        }

        /* HEADER & FOOTER */
        header {
            position: fixed;
            top: -160px;
            left: 0px;
            right: 0px;
            height: 150px;
        }

        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 30px;
            font-size: 8pt;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        /* HEADER LAYOUT */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            padding-bottom: 15px;
        }

        /* LOGO & ADDRESS */
        .company-logo {
            font-size: 22pt;
            font-weight: bold;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-address {
            font-size: 8pt;
            color: #718096;
            margin-top: 5px;
            line-height: 1.3;
        }

        .iso-stamp {
            color: #48bb78;
            font-weight: bold;
            font-size: 9pt;
            margin-top: 8px;
            border: 1px solid #48bb78;
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
        }

        /* CLIENT & METADATA */
        .client-box {
            background-color: #f7fafc;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-size: 9pt;
            color: #4a5568;
        }

        .metadata-table {
            width: 100%;
            font-size: 9pt;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .metadata-table td {
            padding: 3px 0;
            color: #4a5568;
        }

        /* STATUS BADGE */
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
            color: white;
            display: inline-block;
            text-transform: uppercase;
        }

        .status-open {
            background-color: #ecc94b;
            color: #744210;
        }

        .status-closed {
            background-color: #48bb78;
        }

        .status-pending {
            background-color: #4299e1;
        }

        /* SECTIONS */
        .section-header {
            background-color: #4a5568;
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-radius: 4px;
            letter-spacing: 0.5px;
            page-break-after: avoid;
            break-after: avoid;
        }

        .chain-of-custody-section {
            page-break-before: auto;   /* Flow into remaining space after Attachments - no forced new page */
            page-break-inside: auto;
        }

        .chain-of-custody-section .section-header {
            margin-top: 0;
        }

        /* SUBSECTION HEADERS (Notes, Attachments) */
        .subsection-header {
            font-weight: bold;
            font-size: 9pt;
            color: #4a5568;
            margin-top: 16px;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            page-break-after: avoid;
        }

        .subsection-header:first-of-type {
            margin-top: 0;
        }

        /* ATTACHMENT & NOTE BLOCKS (grid-like layout) */
        .attachment-note-stack {
            display: block;
            width: 100%;
            page-break-inside: auto;
        }

        /* Strategy 1: Controlled breaking - blocks can break, but date+description stay together */
        .attachment-block,
        .note-block {
            display: block;
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 12px;
            padding: 12px;
            page-break-inside: auto;
            break-inside: auto;
        }

        .attachment-block:last-child,
        .note-block:last-child {
            margin-bottom: 0;
        }

        /* Date + description must stay together - never split */
        .block-header {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .block-date {
            font-size: 9pt;
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .block-description {
            margin-bottom: 10px;
            line-height: 1.5;
            color: #2d3748;
        }

        .block-meta {
            font-size: 8pt;
            color: #718096;
            margin-top: 4px;
        }

        /* Prevent image from orphaned at bottom of page - push to next page if insufficient space */
        .block-image {
            margin-top: 10px;
            page-break-before: avoid;
            break-before: avoid;
        }

        .block-image img {
            max-width: 100%;
            width: auto;
            height: auto;
            max-height: 280px;
            display: block;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        /* TABLES */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .content-table thead {
            display: table-header-group;
        }

        .content-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .content-table td,
        .content-table th {
            border: 1px solid #e2e8f0;
            padding: 10px;
            vertical-align: top;
        }

        .content-table th {
            background-color: #f7fafc;
            color: #2d3748;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            border-bottom: 2px solid #cbd5e0 !important;
        }

        .content-table tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .label-cell {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: bold;
            width: 20%;
        }

        /* RESOLUTION HIGHLIGHT */
        .resolution-section {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
            background-color: #fff;
        }

        .resolution-field {
            margin-bottom: 15px;
        }

        .resolution-label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 5px;
            padding-bottom: 2px;
        }

        /* APPROVALS */
        .approval-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-top: 10px;
        }

        .approval-table td {
            border: 1px solid #e2e8f0;
            background-color: #f9fafb;
            padding: 10px;
            width: 33%;
            vertical-align: top;
            height: 90px;
            border-radius: 6px;
        }

        .role-title {
            font-size: 8pt;
            font-weight: bold;
            color: #718096;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 25px;
            letter-spacing: 0.5px;
        }

        .signer-name {
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #cbd5e0;
            margin-top: 10px;
            color: #2d3748;
            padding-bottom: 2px;
        }

        .date-line {
            text-align: center;
            font-size: 8pt;
            margin-top: 4px;
            color: #718096;
        }

        /* UTILS */
        .page-number:before {
            content: counter(page);
        }

        .total-pages:before {
            content: counter(pages);
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>

    <header>
        <!-- FULL HEADER (REPEATS ON ALL PAGES) -->
        <div style="padding-bottom: 15px;">
            <table class="header-table">
                <tr>
                    <td width="55%" style="vertical-align: top;">
                        <!-- COMPANY IDENTITY (null-safe when no active company configured) -->
                        @php
                            $company = getActiveCompany();
                            $logoPath = $company->report_logo ?: $company->logo;
                            $base64Logo = $logoPath ? imageTobase64($logoPath, true) : null;
                        @endphp

                        @if($base64Logo)
                            <img src="{{ $base64Logo }}" style="height: 60px; margin-bottom: 10px;" alt="Company Logo">
                        @else
                            <div class="company-logo">{{ $company?->name ?? 'IMARA LIMS' }}</div>
                        @endif

                        <div class="company-address">
                            {{ $company?->address ?? 'P.O. Box 12345, Nairobi, Kenya' }}<br>
                            Tel: {{ $company?->cell_phone ?? '+254 700 000 000' }} | Email: {{ $company?->email ?? 'info@imaralims.com' }}<br>
                            Website: {{ $company?->website ?? 'www.imaralims.com' }}
                        </div>
                        
                    </td>
                    <td width="45%" style="vertical-align: top; text-align: right;">
                        <!-- CONTEXT -->
                        <div style="font-weight: bold; color: #4a5568; text-transform: uppercase; font-size: 9pt;">
                            CLIENT: {{ $complaint->client->name ?? 'N/A' }}</div>
                        <div style="color: #718096; font-size: 8pt; margin-bottom: 10px;">
                            {{ $complaint->client->email ?? $complaint->client->phone ?? '' }}<br>
                        </div>

                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="text-align: right; vertical-align: bottom;">
                                    <div style="font-size: 9pt;"><strong>Report Ref:</strong>
                                        {{ $complaint->complaint_id }}</div>
                                    <div style="font-size: 9pt;"><strong>Date:</strong> {{ now()->format('d M Y') }}
                                    </div>
                                </td>

                            </tr>
                        </table>
                        <div style="text-align: right; margin-top: 5px;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=50x50&data={{ $complaint->complaint_id }}"
                                alt="QR" width="40" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </header>

    <footer>
        <table width="100%">
            <tr>
                <td>
                    <em>Generated by ImaraLims System on {{ now()->format('d M Y H:i:s') }}</em>
                </td>
                <td class="text-right" style="text-align: right;">
                    {{-- Page numbers added via addClosureReportPageNumbers() in PHP after render --}}
                </td>
            </tr>
        </table>
    </footer>

    <!-- SECTION 1: COMPLAINT DETAILS -->
    <div class="section-header">COMPLAINT DETAILS</div>
    <table class="content-table">
        <tr>
            <td class="label-cell">Organization / Customer</td>
            <td colspan="3" style="font-weight: bold;">{{ $complaint->organization_name ?? $complaint->received_from }}</td>
        </tr>
        <tr>
            <td class="label-cell">Contact Person</td>
            <td>{{ $complaint->contact_name ?? 'N/A' }}</td>
            <td class="label-cell">Title / Position</td>
            <td>{{ $complaint->title_position ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Test Item</td>
            <td>{{ $complaint->test_item ?? $complaint->test_item_report_serial_no ?? 'N/A' }}</td>
            <td class="label-cell">Report Serial No</td>
            <td>{{ $complaint->report_serial_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Complaint Type</td>
            <td>{{ $complaint->type ?? 'General' }}</td>
            <td class="label-cell">Priority</td>
            <td>{{ $complaint->priority ?? 'Normal' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Date Logged</td>
            <td>{{ $complaint->date ? $complaint->date->format('d M Y') : $complaint->created_at->format('d M Y') }}</td>
            <td class="label-cell">Logged By</td>
            <td>{{ $complaint->registered_by }}</td>
        </tr>
        <tr>
            <td class="label-cell">Description</td>
            <td colspan="3">{!! $complaint->description !!}</td>
        </tr>
    </table>

    <!-- SECTION 2: TECHNICAL RESOLUTION -->
    @php
        $res = $complaint->resolutions->last();
    @endphp

    <div class="section-header"> RESOLUTION DETAILS</div>

    @if($res)
        <table class="content-table">
            <tr>
                <td class="label-cell">CAR Reference</td>
                <td>{{ $res->car_no }}</td>
                <td class="label-cell">Resolution Date</td>
                <td>{{ $res->created_at->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label-cell">Officer Responsible</td>
                <td colspan="3">{{ $res->resolvedBy->name ?? $res->officer_responsible ?? 'Pending' }}</td>
            </tr>
            <tr>
                <td class="label-cell">1. FINDINGS / INVESTIGATION</td>
                <td colspan="3">
                    {!! $res->findings ?: ($res->action_taken !== 'See detailed resolution fields.' ? $res->action_taken : '<span style="color: #718096; font-style: italic;">None required</span>') !!}
                </td>
            </tr>
            <tr>
                <td class="label-cell">2. ROOT CAUSE ANALYSIS (RCA)</td>
                <td colspan="3">
                    {!! $res->root_cause_analysis ?: '<span style="color: #718096; font-style: italic;">None required</span>' !!}
                </td>
            </tr>
            <tr>
                <td class="label-cell">3. CORRECTIVE ACTIONS</td>
                <td colspan="3">
                    {!! $res->corrective_action_taken ?: '<span style="color: #718096; font-style: italic;">None required</span>' !!}
                </td>
            </tr>
            <tr>
                <td class="label-cell">4. PREVENTIVE ACTIONS</td>
                <td colspan="3">
                    {!! $res->preventive_action ?: '<span style="color: #718096; font-style: italic;">Not applicable</span>' !!}
                </td>
            </tr>
            <tr>
                <td class="label-cell">5. CLOSURE REMARKS</td>
                <td colspan="3">
                    {!! $res->internal_remarks ?: '<span style="color: #718096; font-style: italic;">No closure remarks provided.</span>' !!}
                </td>
            </tr>
        </table>

        <div style="font-size: 8pt; color: #718096; text-align: right; margin-top: 5px; font-style: italic;">
            Resolution logged by <strong>{{ $res->registered_by }}</strong> on {{ $res->created_at->format('d M Y H:i') }}
        </div>
    @else
        <div style="border: 1px solid #e2e8f0; padding: 20px; text-align: center; color: #718096; font-style: italic;">
            No technical resolution has been recorded for this complaint yet.
        </div>
    @endif

    <!-- SECTION 3: ATTACHMENTS & NOTES -->
    <div class="section-header"> ATTACHMENTS & NOTES</div>
    @if(($publicNotes && $publicNotes->count() > 0) || ($publicAttachments && $publicAttachments->count() > 0))
        @php
            $notes = collect();
            foreach ($publicNotes as $note) {
                $notes->push([
                    'date' => $note->created_at,
                    'description' => $note->notes,
                    'meta' => 'By: ' . ($note->created_by ?? 'N/A'),
                ]);
            }
            $notes = $notes->sortBy('date');

            $attachments = collect();
            foreach ($publicAttachments as $att) {
                $imgPath = null;
                if (!empty($att->file_path)) {
                    $relativePath = ltrim(str_replace('/storage/', '', $att->file_path), '/');
                    $ext = strtolower(pathinfo($att->file_path, PATHINFO_EXTENSION));
                    $isImageExt = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                    if ($isImageExt) {
                        $absPath = storage_path('app/public/' . $relativePath);
                        if (!file_exists($absPath)) {
                            $absPath = public_path($att->file_path);
                        }
                        if (file_exists($absPath)) {
                            $imgPath = $absPath;
                        }
                    }
                }
                $desc = ($att->title ?? 'Attachment') . ' (' . ($att->type ?? '') . ')';
                if (!empty($att->description)) {
                    $desc .= ' - ' . $att->description;
                }
                $attachments->push([
                    'date' => $att->created_at,
                    'description' => $desc,
                    'meta' => 'Uploaded By: ' . ($att->posted_by ?? 'N/A'),
                    'imgPath' => $imgPath,
                ]);
            }
            $attachments = $attachments->sortBy('date');
        @endphp

        {{-- NOTES SUBSECTION --}}
        <div class="subsection-header"> Notes</div>
        @if($notes->count() > 0)
            <div class="attachment-note-stack">
                @foreach($notes as $item)
                    <div class="note-block">
                        <div class="block-header">
                            <div class="block-date">{{ $item['date']->format('d M Y H:i') }}</div>
                            <div class="block-description">
                                {{ $item['description'] }}
                                <div class="block-meta">{{ $item['meta'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="subsection-empty" style="padding: 10px 12px; margin-bottom: 12px; color: #718096; font-style: italic; font-size: 8pt; border: 1px solid #e2e8f0; border-radius: 4px;">
                No notes recorded for this complaint.
            </div>
        @endif

        {{-- ATTACHMENTS SUBSECTION --}}
        <div class="subsection-header"> Attachments</div>
        @if($attachments->count() > 0)
            <div class="attachment-note-stack">
                @foreach($attachments as $item)
                    <div class="attachment-block">
                        <div class="block-header">
                            <div class="block-date">{{ $item['date']->format('d M Y H:i') }}</div>
                            <div class="block-description">
                                {{ $item['description'] }}
                                <div class="block-meta">{{ $item['meta'] }}</div>
                            </div>
                        </div>
                        @if($item['imgPath'])
                            <div class="block-image">
                                <img src="{{ $item['imgPath'] }}" alt="Attachment">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="subsection-empty" style="padding: 10px 12px; margin-bottom: 12px; color: #718096; font-style: italic; font-size: 8pt; border: 1px solid #e2e8f0; border-radius: 4px;">
                No attachments uploaded for this complaint.
            </div>
        @endif
    @else
        <div class="attachments-empty-state" style="padding: 12px 16px; margin-bottom: 8px; text-align: center; color: #718096; font-style: italic; font-size: 8pt; border: 1px solid #e2e8f0; border-radius: 4px; page-break-inside: avoid;">
            No notes or attachments were recorded for this case.
        </div>
    @endif

    <!-- SECTION 4: CHAIN OF CUSTODY (flows into remaining space - no forced page break) -->
    <div class="chain-of-custody-section">
        <div class="section-header"> CHAIN OF CUSTODY</div>
        <table class="content-table">
        <thead>
            <tr>
                <th style="width: 25%; text-align: center;">Date/Time</th>
                <th style="width: 10%; text-align: center;">Stage</th>
                <th style="width: 30%;">Action</th>
                <th style="width: 35%;">Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($chainOfCustody as $log)
                <tr>
                    <td style="text-align: center;">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td style="text-align: center;">
                        {{ is_numeric($log->workflow_stage) ? (getComplaintWorkflow()[$log->workflow_stage] ?? $log->workflow_stage) : ($log->workflow_stage ?? 'N/A') }}
                    </td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->comments ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        </table>
    </div>

    <!-- SECTION 5: APPROVALS -->
    <div class="approval-table-container" style="page-break-inside: avoid;">
        <div class="section-header"> APPROVALS</div>
        <table class="approval-table"
            style="width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 5px 0;">
            <tr>
                <!-- INTAKE -->
                <td width="25%" style="width: 25%; vertical-align: top;">
                    <div class="role-title">Complaint Intake</div>
                    @php
                        // intake is the first log
                        $intakeLog = $chainOfCustody->sortBy('id')->first();
                        $intakeUser = $intakeLog ? $intakeLog->actionTaker : null;
                        if(!$intakeUser && $complaint->registered_by) {
                             $intakeUser = \App\User::where('name', $complaint->registered_by)->first();
                        }
                        $intakeSig = $intakeUser ? $intakeUser->getSignaturePath() : null;
                    @endphp

                    <div style="height: 60px; border-bottom: 1px solid #cbd5e0; margin-bottom: 5px; text-align: center;">
                        @if($intakeSig)
                            <img src="{{ $intakeSig }}" style="max-height: 50px; width: auto; margin-top: 5px;" alt="Signature">
                        @elseif($intakeUser)
                             <div style="line-height: 60px; color: #cbd5e0; font-style: italic; font-size: 8pt;">(Digitally Signed)</div>
                        @else
                             <div style="line-height: 60px;"></div>
                        @endif
                    </div>

                    <div class="signer-name" style="border: none;">
                        {{ $intakeUser->name ?? $complaint->registered_by }}
                    </div>
                    <div class="date-line">Date: {{ $complaint->created_at->format('d M Y') }}</div>
                </td>

                <!-- COMPLAINT APPROVAL -->
                <td width="25%" style="width: 25%; vertical-align: top;">
                    <div class="role-title">Complaint Approval</div>
                    @php
                        // Find the action where the complaint was approved (Stage 2 -> 3)
                        // The action is usually "Approve Compliant" (sic) or "Approved" based on helper
                        $complaintApproverLog = $chainOfCustody->filter(function ($log) {
                            return strpos($log->workflow_stage, 'Active Investigations') !== false
                                && (stripos($log->action, 'Approve') !== false);
                        })->sortByDesc('id')->first();

                        $complaintApprover = $complaintApproverLog ? $complaintApproverLog->actionTaker : null;
                        $approverSig = $complaintApprover ? $complaintApprover->getSignaturePath() : null;
                    @endphp

                    <div
                        style="height: 60px; border-bottom: 1px solid #cbd5e0; margin-bottom: 5px; text-align: center;">
                        @if($approverSig)
                            <img src="{{ $approverSig }}" style="max-height: 50px; width: auto; margin-top: 5px;"
                                alt="Signature">
                        @elseif($complaintApprover)
                            <div style="line-height: 60px; color: #cbd5e0; font-style: italic; font-size: 8pt;">(Digitally
                                Signed)</div>
                        @else
                            <div style="line-height: 60px;"></div>
                        @endif
                    </div>

                    <div class="signer-name" style="border: none;">
                        {{ $complaintApprover->name ?? 'Pending' }}
                    </div>
                    <div class="date-line">
                        @if($complaintApproverLog)
                            Date: {{ $complaintApproverLog->created_at->format('d M Y') }}
                        @endif
                    </div>
                </td>

                <!-- RESOLVED BY -->
                <td width="25%" style="width: 25%; vertical-align: top;">
                    <div class="role-title">Resolution Approver</div>

                    @php
                        $res = $complaint->resolutions->last();
                        $resolver = $res ? $res->resolvedBy : null;
                        $sigPath = $resolver ? $resolver->getSignaturePath() : null;
                    @endphp

                    <div
                        style="height: 60px; border-bottom: 1px solid #cbd5e0; margin-bottom: 5px; text-align: center;">
                        @if($sigPath)
                            <img src="{{ $sigPath }}" style="max-height: 50px; width: auto; margin-top: 5px;"
                                alt="Signature">
                        @elseif($resolver)
                            <div style="line-height: 60px; color: #cbd5e0; font-style: italic; font-size: 8pt;">(Digitally
                                Signed)</div>
                        @else
                            <div style="line-height: 60px;"></div>
                        @endif
                    </div>

                    <div class="signer-name" style="border: none;">
                        {{ $resolver->name ?? $res->officer_responsible ?? 'Pending' }}
                    </div>
                    <div class="date-line">
                        @if($res)
                            Date: {{ $res->created_at->format('d M Y') }}
                        @endif
                    </div>
                </td>

                <!-- CLOSED BY -->
                <td width="25%" style="width: 25%; vertical-align: top;">
                    <div class="role-title">Closed By</div>

                    @php 
                        // Find the action that closed the complaint for DATE purposes
                        $closedByLog = $chainOfCustody->filter(function ($log) {
                            return stripos($log->action, 'Report Sent & Complaint Closed') !== false
                                || $log->workflow_stage == 'Closed';
                        })->sortByDesc('id')->first();

                        // Completion date: prefer explicit close log, else resolution date, else last chain entry
                        $completionDate = $closedByLog
                            ? $closedByLog->created_at
                            : ($res ? $res->created_at : $chainOfCustody->sortByDesc('id')->first()?->created_at);

                        // Use the actual selected closer saved on the complaint
                        $closerUser = $complaint->closedBy;
                        $closerSig = $closerUser ? $closerUser->getSignaturePath() : null;
                        $closerPosition = $closerUser ? $closerUser->position_name() : null;
                    @endphp
    
                <div style="height: 60px; border-bottom: 1px solid #cbd5e0; margin-bottom: 5px; text-align: center;">
                        @if($closerSig)
                            <img src="{{ $closerSig }}" style="max-height: 50px; width: auto; margin-top: 5px;" alt="Signature">
                        @elseif($closerUser)
                            <div style="line-height: 60px; color: #cbd5e0; font-style: italic; font-size: 8pt;">(Digitally Signed)</div>
                        @else
                             <div style="line-height: 60px;"></div>
                        @endif
                    </div>
    
                    <div class="signer-name" style="border: none;">
                        {{ $closerUser->name ?? 'Pending' }}
                    </div>
                <div class="role-title" style="margin-top: 4px; color: #4a5568;">
                    {{ ($closerPosition && $closerPosition !== 'n/a') ? $closerPosition : '' }}
                </div>
                <div class="date-line">
                    @if($completionDate)
                        Date: {{ $completionDate->format('d M Y') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
