<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>CAPA Report - {{ $capa->capa_number }}</title>
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

    <div class="report-title">Corrective Action Report</div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">CAPA Number:</span>
            <span class="info-value">{{ $capa->capa_number ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Title:</span>
            <span class="info-value">{{ $capa->title ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NC Number:</span>
            <span class="info-value">{{ $capa->nonConformance->nc_number ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Category:</span>
            <span class="info-value">{{ $capa->category->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Priority:</span>
            <span class="info-value">{{ $capa->priority->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">{{ $capa->status_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Action Owner:</span>
            <span class="info-value">{{ $capa->actionOwnerUser->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Assigned Date:</span>
            <span class="info-value">{{ $capa->assigned_date ? \Carbon\Carbon::parse($capa->assigned_date)->format('d/m/Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Due Date:</span>
            <span class="info-value">{{ $capa->due_date ? \Carbon\Carbon::parse($capa->due_date)->format('d/m/Y') : 'N/A' }}</span>
        </div>
        @if($capa->implementation_date)
        <div class="info-row">
            <span class="info-label">Implementation Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($capa->implementation_date)->format('d/m/Y') }}</span>
        </div>
        @endif
    </div>

    @if($capa->action_description)
    <div class="info-section">
        <div style="font-weight: bold; margin-bottom: 5px;">Action Description:</div>
        <div>{{ $capa->action_description }}</div>
    </div>
    @endif

    @if($capa->implementation_details)
    <div class="info-section">
        <div style="font-weight: bold; margin-bottom: 5px;">Implementation Details:</div>
        <div>{{ $capa->implementation_details }}</div>
    </div>
    @endif

    @if($capa->latestVerification)
    <div class="info-section" style="margin-top: 20px;">
        <div style="font-weight: bold; font-size: 12pt; margin-bottom: 10px;">Verification:</div>
        <div class="info-row">
            <span class="info-label">Verification Date:</span>
            <span class="info-value">{{ $capa->latestVerification->verification_date ? \Carbon\Carbon::parse($capa->latestVerification->verification_date)->format('d/m/Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Result:</span>
            <span class="info-value">{{ $capa->latestVerification->result_name ?? 'N/A' }}</span>
        </div>
        @if($capa->latestVerification->verifier_comments)
        <div style="margin-top: 10px;">
            <div style="font-weight: bold; margin-bottom: 5px;">Comments:</div>
            <div>{{ $capa->latestVerification->verifier_comments }}</div>
        </div>
        @endif
    </div>
    @endif

    <div class="footer">
        <div>Generated on {{ date('d/m/Y H:i:s') }} | {{ $company->name ?? 'JASIRI STENCIL LIMITED' }}</div>
    </div>
</body>
</html>








