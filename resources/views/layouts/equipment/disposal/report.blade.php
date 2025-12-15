<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Disposal Report - {{ $disposal->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .header {
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 150px;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 140px;
            max-height: 60px;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .header-info h1 {
            font-size: 18pt;
            color: #c00;
            margin-bottom: 5px;
        }
        .header-info .subtitle {
            font-size: 10pt;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f5f5f5;
            border-left: 4px solid #c00;
            padding: 8px 10px;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th {
            background-color: #e9ecef;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        .info-table {
            width: 100%;
        }
        .info-table td:first-child {
            width: 30%;
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-approved { background-color: #28a745; color: #fff; }
        .status-rejected { background-color: #dc3545; color: #fff; }
        .status-executed { background-color: #17a2b8; color: #fff; }
        .status-closed { background-color: #6c757d; color: #fff; }
        .risk-low { background-color: #28a745; color: #fff; }
        .risk-medium { background-color: #ffc107; color: #000; }
        .risk-high { background-color: #dc3545; color: #fff; }
        .risk-critical { background-color: #6f42c1; color: #fff; }
        .text-block {
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .compliance-box {
            padding: 15px;
            background-color: #e7f3ff;
            border: 2px solid #0056b3;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .compliance-box h4 {
            color: #0056b3;
            margin-bottom: 8px;
        }
        .compliance-box ul {
            margin-left: 20px;
            line-height: 1.8;
        }
        .footer {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 30px;
            font-size: 8pt;
            color: #666;
        }
        .hash-code {
            font-family: 'Courier New', monospace;
            background-color: #f0f0f0;
            padding: 5px;
            word-break: break-all;
        }
        .page-break {
            page-break-after: always;
        }
        .signature-img {
            max-width: 150px;
            max-height: 50px;
            border: 1px solid #ddd;
            padding: 2px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-logo">
                @if(isset($companyLogo) && file_exists($companyLogo))
                    <img src="{{ $companyLogo }}" alt="Company Logo">
                @endif
            </div>
            <div class="header-info">
                <h1>EQUIPMENT DISPOSAL REPORT</h1>
                <div class="subtitle">ISO/IEC 17025:2017 Compliant</div>
                <div class="subtitle">Disposal ID: {{ $disposal->id }}</div>
            </div>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="section">
        <div class="section-title">Report Summary</div>
        <table class="info-table">
            <tr>
                <td>Report Generated:</td>
                <td>{{ $generatedAt->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <td>Status:</td>
                <td><span class="status-badge status-{{ $disposal->status }}">{{ strtoupper($disposal->status) }}</span></td>
            </tr>
            <tr>
                <td>Company:</td>
                <td>{{ $company->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Equipment Information -->
    <div class="section">
        <div class="section-title">1. Equipment Information</div>
        <table class="info-table">
            <tr>
                <td>Equipment Name:</td>
                <td>{{ $equipment->name }}</td>
            </tr>
            <tr>
                <td>Equipment Number:</td>
                <td>{{ $equipment->equipment_number }}</td>
            </tr>
            <tr>
                <td>Serial Number:</td>
                <td>{{ $equipment->serial_number ?? '-' }}</td>
            </tr>
            <tr>
                <td>Make:</td>
                <td>{{ $equipment->make }}</td>
            </tr>
            <tr>
                <td>Model:</td>
                <td>{{ $equipment->model }}</td>
            </tr>
            <tr>
                <td>Manufacturer:</td>
                <td>{{ $equipment->manufacturer ?? '-' }}</td>
            </tr>
            <tr>
                <td>Asset Type:</td>
                <td>{{ $equipment->asset_type_id ?? '-' }}</td>
            </tr>
            <tr>
                <td>Location:</td>
                <td>{{ $equipment->asset_location_id ?? '-' }}</td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>{{ $equipment->assigned_department ?? '-' }}</td>
            </tr>
            <tr>
                <td>Condition:</td>
                <td>{{ $equipment->condition ?? '-' }}</td>
            </tr>
            <tr>
                <td>Date Purchased:</td>
                <td>{{ $equipment->date_purchased ? \Carbon\Carbon::parse($equipment->date_purchased)->format('Y-m-d') : '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Evaluation Summary -->
    @if($disposal->evaluation_id)
    <div class="section">
        <div class="section-title">2. Evaluation Summary</div>
        @php
            $evaluation = $disposal->evaluation;
        @endphp
        @if($evaluation)
        <table class="info-table">
            <tr>
                <td>Evaluation Date:</td>
                <td>{{ $evaluation->evaluation_date->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Evaluated By:</td>
                <td>{{ $evaluation->evaluator->name }}</td>
            </tr>
            <tr>
                <td>Physical Condition:</td>
                <td>{{ ucfirst($evaluation->physical_condition) }}</td>
            </tr>
            <tr>
                <td>Last Calibration Status:</td>
                <td>{{ $evaluation->last_calibration_status ? ucfirst($evaluation->last_calibration_status) : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Recommendation:</td>
                <td><strong>{{ ucfirst(str_replace('_', ' ', $evaluation->recommendation)) }}</strong></td>
            </tr>
            @if($evaluation->repair_cost_estimate && $evaluation->replacement_cost_estimate)
            <tr>
                <td>Cost Analysis:</td>
                <td>
                    Repair: ${{ number_format($evaluation->repair_cost_estimate, 2) }} | 
                    Replacement: ${{ number_format($evaluation->replacement_cost_estimate, 2) }}
                    ({{ number_format(($evaluation->repair_cost_estimate / $evaluation->replacement_cost_estimate) * 100, 1) }}%)
                </td>
            </tr>
            @endif
        </table>
        @if($evaluation->impact_on_testing)
        <div><strong>Impact on Testing:</strong></div>
        <div class="text-block">{{ $evaluation->impact_on_testing }}</div>
        @endif
        @endif
    </div>
    @endif

    <!-- Justification -->
    <div class="section">
        <div class="section-title">3. Disposal Justification</div>
        <div class="text-block">
            {{ $disposal->justification }}
        </div>
    </div>

    <!-- Risk Assessment -->
    <div class="section">
        <div class="section-title">4. Risk Assessment & Regulatory Category</div>
        <table class="info-table">
            <tr>
                <td>Risk Level:</td>
                <td><span class="status-badge risk-{{ strtolower($disposal->risk_level) }}">{{ $disposal->risk_level }}</span></td>
            </tr>
            <tr>
                <td>Regulatory Category:</td>
                <td>{{ $disposal->regulatory_category }}</td>
            </tr>
        </table>
    </div>

    <!-- Disposal Method -->
    <div class="section">
        <div class="section-title">5. Disposal Method</div>
        <table class="info-table">
            <tr>
                <td>Proposed Method:</td>
                <td>{{ ucfirst($disposal->proposed_method ?? '-') }}</td>
            </tr>
            <tr>
                <td>Final Method:</td>
                <td>{{ ucfirst($disposal->final_disposal_method ?? 'Not executed yet') }}</td>
            </tr>
        </table>
    </div>

    <!-- Page break before approval workflow -->
    <div class="page-break"></div>

    <!-- Approval Workflow -->
    <div class="section">
        <div class="section-title">6. Approval Workflow</div>
        @if($approvals && $approvals->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Approver</th>
                    <th>Decision</th>
                    <th>Date</th>
                    <th>Remarks</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvals as $approval)
                <tr>
                    <td>{{ $approval->step }}</td>
                    <td>{{ $approval->approver->name ?? '-' }}</td>
                    <td>
                        @if($approval->decision === 'approve')
                            <span class="status-badge status-approved">APPROVED</span>
                        @elseif($approval->decision === 'reject')
                            <span class="status-badge status-rejected">REJECTED</span>
                        @else
                            <span class="status-badge status-pending">PENDING</span>
                        @endif
                    </td>
                    <td>{{ $approval->decided_at ? $approval->decided_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ \Str::limit($approval->remarks, 50) }}</td>
                    <td>
                        @if($approval->signature_path)
                            <img src="{{ public_path($approval->signature_path) }}" class="signature-img" alt="Signature">
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No approvals configured.</p>
        @endif
    </div>

    <!-- Decommissioning Record -->
    @if($disposal->decommissioning_date)
    <div class="section">
        <div class="section-title">7. Decommissioning Record (ISO 17025 Clause 6.4.13)</div>
        <table class="info-table">
            <tr>
                <td>Decommissioning Date:</td>
                <td>{{ $disposal->decommissioning_date->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Decommissioned By:</td>
                <td>{{ $disposal->decommissioned_by ? \App\User::find($disposal->decommissioned_by)->name : '-' }}</td>
            </tr>
            <tr>
                <td>Equipment Labeled:</td>
                <td>{{ $disposal->equipment_labeled ? '✓ Yes' : '✗ No' }}</td>
            </tr>
            <tr>
                <td>Removed from Calibration Schedule:</td>
                <td>{{ $disposal->removed_from_calibration_schedule ? '✓ Yes' : '✗ No' }}</td>
            </tr>
            <tr>
                <td>Removed from Maintenance Schedule:</td>
                <td>{{ $disposal->removed_from_maintenance_schedule ? '✓ Yes' : '✗ No' }}</td>
            </tr>
            <tr>
                <td>Utilities Disconnected:</td>
                <td>{{ $disposal->utilities_disconnected ? '✓ Yes' : '✗ No' }}</td>
            </tr>
            @if($disposal->data_wiped !== null)
            <tr>
                <td>Data Wiped:</td>
                <td>{{ $disposal->data_wiped ? '✓ Yes' : '✗ No' }}</td>
            </tr>
            @endif
            @if($disposal->storage_devices_removed !== null)
            <tr>
                <td>Storage Devices Removed:</td>
                <td>{{ $disposal->storage_devices_removed ? '✓ Yes' : '✗ No' }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <!-- Execution Details -->
    @if($disposal->disposal_date)
    <div class="section">
        <div class="section-title">8. Disposal Execution Details</div>
        <table class="info-table">
            <tr>
                <td>Disposal Date:</td>
                <td>{{ $disposal->disposal_date->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Executed By:</td>
                <td>{{ $disposal->executor->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Witness:</td>
                <td>{{ $disposal->witness->name ?? '-' }}</td>
            </tr>
            @if($disposal->transport_company)
            <tr>
                <td>Transport Company:</td>
                <td>{{ $disposal->transport_company }}</td>
            </tr>
            @endif
            @if($disposal->waste_handler_company)
            <tr>
                <td>Waste Handler:</td>
                <td>{{ $disposal->waste_handler_company }}</td>
            </tr>
            @endif
            @if($disposal->waste_handler_license)
            <tr>
                <td>Handler License:</td>
                <td>{{ $disposal->waste_handler_license }}</td>
            </tr>
            @endif
        </table>

        @if($disposal->compliance_checklist_json)
        <div><strong>Compliance Checklist:</strong></div>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disposal->compliance_checklist_json as $key => $value)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                    <td>{{ $value ? '✓' : '✗' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    <!-- Page break before histories -->
    <div class="page-break"></div>

    <!-- Calibration History -->
    <div class="section">
        <div class="section-title">9. Calibration History (Last 10 Records)</div>
        @if($calibrationHistory && count($calibrationHistory) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Notes</th>
                    <th>Overseen By</th>
                    <th>Certificate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($calibrationHistory as $log)
                <tr>
                    <td>{{ $log->date->format('Y-m-d') }}</td>
                    <td>{{ \Str::limit($log->notes, 60) }}</td>
                    <td>{{ $log->overseen_by }}</td>
                    <td>{{ $log->certificate !== 'no-document' ? 'Available' : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No calibration history available.</p>
        @endif
    </div>

    <!-- Maintenance History -->
    <div class="section">
        <div class="section-title">10. Maintenance History (Last 10 Records)</div>
        @if($maintenanceHistory && count($maintenanceHistory) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Notes</th>
                    <th>Overseen By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($maintenanceHistory as $log)
                <tr>
                    <td>{{ $log->date->format('Y-m-d') }}</td>
                    <td>{{ $log->maintainance_type ?? 'General' }}</td>
                    <td>{{ \Str::limit($log->notes, 60) }}</td>
                    <td>{{ $log->overseen_by }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No maintenance history available.</p>
        @endif
    </div>

    <!-- Evidence Files -->
    @if($files && count($files) > 0)
    <div class="section">
        <div class="section-title">11. Evidence Files</div>
        <table>
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Uploaded By</th>
                    <th>Upload Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($files as $file)
                <tr>
                    <td>{{ $file->file_name }}</td>
                    <td>{{ ucfirst($file->file_type) }}</td>
                    <td>{{ $file->uploader->name ?? '-' }}</td>
                    <td>{{ $file->created_at->format('Y-m-d') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Audit Trail Summary -->
    <div class="section">
        <div class="section-title">12. Audit Trail Summary (ISO 19011 Compliant)</div>
        <p><em>Complete audit trail maintained with full traceability. Key events:</em></p>
        <table>
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Action</th>
                    <th>User</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $disposal->created_at->format('Y-m-d H:i') }}</td>
                    <td>Created</td>
                    <td>{{ $disposal->requester->name }}</td>
                    <td>Disposal request initiated</td>
                </tr>
                @if($disposal->status !== 'draft')
                <tr>
                    <td>{{ $disposal->updated_at->format('Y-m-d H:i') }}</td>
                    <td>Submitted</td>
                    <td>{{ $disposal->requester->name }}</td>
                    <td>Submitted for approval</td>
                </tr>
                @endif
                @foreach($approvals as $approval)
                    @if($approval->decided_at)
                    <tr>
                        <td>{{ $approval->decided_at->format('Y-m-d H:i') }}</td>
                        <td>{{ ucfirst($approval->decision) }}</td>
                        <td>{{ $approval->approver->name }}</td>
                        <td>Step {{ $approval->step }} {{ $approval->decision }}</td>
                    </tr>
                    @endif
                @endforeach
                @if($disposal->disposal_date)
                <tr>
                    <td>{{ $disposal->disposal_date->format('Y-m-d H:i') }}</td>
                    <td>Executed</td>
                    <td>{{ $disposal->executor->name ?? '-' }}</td>
                    <td>Physical disposal executed</td>
                </tr>
                @endif
            </tbody>
        </table>
        <p style="margin-top: 10px;"><small>Full audit trail available in system database with IP addresses and detailed change logs.</small></p>
    </div>

    <!-- Page break before compliance -->
    <div class="page-break"></div>

    <!-- Compliance Statements -->
    <div class="section">
        <div class="section-title">13. Compliance Statements</div>
        
        <div class="compliance-box">
            <h4>ISO/IEC 17025:2017 Compliance</h4>
            <p>This disposal process adheres to the following ISO/IEC 17025:2017 clauses:</p>
            <ul>
                <li><strong>Clause 6.4.3</strong> - Equipment removed from service when found to be defective or outside specified limits</li>
                <li><strong>Clause 6.4.6</strong> - Equipment maintenance records maintained and disposal documented</li>
                <li><strong>Clause 6.4.13</strong> - Equipment taken out of service clearly labeled and isolated</li>
                <li><strong>Clause 7.10</strong> - Records maintained to demonstrate laboratory competence and validity of results</li>
            </ul>
        </div>

        <div class="compliance-box">
            <h4>ISO 19011:2018 Audit Trail Compliance</h4>
            <p>Complete audit trail maintained with:</p>
            <ul>
                <li>Immutable audit log entries with timestamps</li>
                <li>Before/after value tracking for all changes</li>
                <li>User identification and IP address tracking</li>
                <li>Full traceability for internal and external audits</li>
            </ul>
        </div>

        <div class="compliance-box">
            <h4>SANAS Requirements (TR 25, TR 26)</h4>
            <p>This disposal process meets SANAS technical requirements:</p>
            <ul>
                <li>Documented justification for equipment disposal</li>
                <li>Approval workflow with authorized signatures</li>
                <li>Risk assessment and regulatory compliance</li>
                <li>Witness requirements for disposal execution</li>
                <li>Complete documentation chain maintained</li>
            </ul>
        </div>

        <div class="compliance-box">
            <h4>ILAC G8:09/2019 Compliance</h4>
            <p>Equipment management and disposal aligned with ILAC G8 guidelines for laboratory equipment:</p>
            <ul>
                <li>Equipment evaluation prior to disposal decision</li>
                <li>Cost-benefit analysis performed where applicable</li>
                <li>Impact on testing capabilities assessed</li>
                <li>Disposal method appropriate for equipment type and regulatory category</li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div style="margin-bottom: 10px;">
            <strong>Report Authenticity Verification</strong><br>
            This report is secured with a SHA-256 hash to ensure data integrity and prevent tampering.
        </div>
        <div>
            <strong>SHA-256 Hash:</strong><br>
            <span class="hash-code">{{ $shaHash }}</span>
        </div>
        <div style="margin-top: 10px;">
            <strong>Generated:</strong> {{ $generatedAt->format('Y-m-d H:i:s T') }}<br>
            <strong>Report ID:</strong> DISPOSAL-{{ $disposal->id }}-{{ $generatedAt->format('Ymd-His') }}
        </div>
        <div style="margin-top: 10px; text-align: center; font-size: 7pt;">
            This document contains confidential information and is intended for authorized personnel only.<br>
            {{ $company->name ?? 'Company Name' }} | Equipment Disposal Management System
        </div>
    </div>
</body>
</html>
