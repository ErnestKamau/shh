<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Audit Report - {{ $audit->audit_number }}</title>
    <style>
        @page {
            margin: 20px;
        }

        * {
            font-family: 'Arial', 'Helvetica', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            line-height: 1.5;
            font-size: 10pt;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            text-transform: uppercase;
        }

        .info-section {
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 10px;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }

        .info-value {
            display: inline-block;
        }

        table {
            border-collapse: collapse !important;
            width: 100%;
            margin-top: 10px;
            font-size: 9pt;
        }

        table th,
        table td {
            border: 1px solid #000 !important;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 8pt;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'JASIRI STENCIL LIMITED' }}</div>
        <div style="font-size: 9pt;">Home of Quality Assurance</div>
    </div>

    <div class="report-title">Audit Report</div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Audit Number:</span>
            <span class="info-value">{{ $audit->audit_number ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Title:</span>
            <span class="info-value">{{ $audit->title ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Audit Type:</span>
            <span class="info-value">{{ $audit->auditType->name ?? $audit->audit_type_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">{{ $audit->status->name ?? $audit->status_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Lead Auditor:</span>
            <span class="info-value">{{ $audit->leadAuditor->name ?? $audit->lead_auditor_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Auditee:</span>
            <span class="info-value">{{ $audit->auditee_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Scheduled Date:</span>
            <span class="info-value">{{ $audit->scheduled_date ? \Carbon\Carbon::parse($audit->scheduled_date)->format('d/m/Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Start Date:</span>
            <span class="info-value">{{ $audit->start_date ? \Carbon\Carbon::parse($audit->start_date)->format('d/m/Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">End Date:</span>
            <span class="info-value">{{ $audit->end_date ? \Carbon\Carbon::parse($audit->end_date)->format('d/m/Y') : 'N/A' }}</span>
        </div>
    </div>

    @if($audit->objective)
    <div class="info-section">
        <div style="font-weight: bold; margin-bottom: 5px;">Objective:</div>
        <div>{{ $audit->objective }}</div>
    </div>
    @endif

    @if($audit->scope)
    <div class="info-section">
        <div style="font-weight: bold; margin-bottom: 5px;">Scope:</div>
        <div>{{ $audit->scope }}</div>
    </div>
    @endif

    @if($audit->findings->count() > 0)
    <div style="margin-top: 20px;">
        <div style="font-weight: bold; font-size: 12pt; margin-bottom: 10px;">Findings:</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 20%;">Category</th>
                    <th style="width: 15%;">Risk Level</th>
                    <th style="width: 55%;">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audit->findings as $finding)
                <tr>
                    <td>{{ $finding->item_number ?? $loop->iteration }}</td>
                    <td>{{ $finding->category->name ?? 'N/A' }}</td>
                    <td>{{ $finding->riskLevel->name ?? 'N/A' }}</td>
                    <td>{{ $finding->description ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($audit->nonConformances->count() > 0)
    <div style="margin-top: 20px;">
        <div style="font-weight: bold; font-size: 12pt; margin-bottom: 10px;">Non-Conformances:</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">NC Number</th>
                    <th style="width: 25%;">Title</th>
                    <th style="width: 15%;">Risk Level</th>
                    <th style="width: 45%;">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audit->nonConformances as $nc)
                <tr>
                    <td>{{ $nc->nc_number ?? 'N/A' }}</td>
                    <td>{{ $nc->title ?? 'N/A' }}</td>
                    <td>{{ $nc->riskLevel->name ?? 'N/A' }}</td>
                    <td>{{ Str::limit($nc->description ?? 'N/A', 100) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($audit->conclusions)
    <div class="info-section" style="margin-top: 20px;">
        <div style="font-weight: bold; margin-bottom: 5px;">Conclusions:</div>
        <div>{{ $audit->conclusions }}</div>
    </div>
    @endif

    @if($audit->recommendations)
    <div class="info-section" style="margin-top: 20px;">
        <div style="font-weight: bold; margin-bottom: 5px;">Recommendations:</div>
        <div>{{ $audit->recommendations }}</div>
    </div>
    @endif

    <div class="footer">
        <div>Generated on {{ date('d/m/Y H:i:s') }} | {{ $company->name ?? 'JASIRI STENCIL LIMITED' }}</div>
    </div>
</body>
</html>








