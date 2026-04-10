<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Non-Conformance Report - {{ $nc->nc_number }}</title>
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

    <div class="report-title">Non-Conformance Report</div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">NC Number:</span>
            <span class="info-value">{{ $nc->nc_number ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Title:</span>
            <span class="info-value">{{ $nc->title ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Origin:</span>
            <span class="info-value">{{ $nc->origin_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Risk Level:</span>
            <span class="info-value">{{ $nc->riskLevel->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">{{ $nc->status_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date Identified:</span>
            <span class="info-value">{{ $nc->date_identified ? \Carbon\Carbon::parse($nc->date_identified)->format('d/m/Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Identified By:</span>
            <span class="info-value">{{ $nc->identifiedByUser->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Target Closure Date:</span>
            <span class="info-value">{{ $nc->target_closure_date ? \Carbon\Carbon::parse($nc->target_closure_date)->format('d/m/Y') : 'N/A' }}</span>
        </div>
        @if($nc->actual_closure_date)
        <div class="info-row">
            <span class="info-label">Actual Closure Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($nc->actual_closure_date)->format('d/m/Y') }}</span>
        </div>
        @endif
    </div>

    @if($nc->description)
    <div class="info-section">
        <div style="font-weight: bold; margin-bottom: 5px;">Description:</div>
        <div>{{ $nc->description }}</div>
    </div>
    @endif

    @if($nc->root_cause_summary)
    <div class="info-section">
        <div style="font-weight: bold; margin-bottom: 5px;">Root Cause Summary:</div>
        <div>{{ $nc->root_cause_summary }}</div>
    </div>
    @endif

    @if($nc->correctiveActions->count() > 0)
    <div style="margin-top: 20px;">
        <div style="font-weight: bold; font-size: 12pt; margin-bottom: 10px;">Corrective Actions:</div>
        <table style="border-collapse: collapse; width: 100%; font-size: 9pt;">
            <thead>
                <tr>
                    <th style="border: 1px solid #000; padding: 6px; background-color: #f0f0f0;">CAPA Number</th>
                    <th style="border: 1px solid #000; padding: 6px; background-color: #f0f0f0;">Title</th>
                    <th style="border: 1px solid #000; padding: 6px; background-color: #f0f0f0;">Status</th>
                    <th style="border: 1px solid #000; padding: 6px; background-color: #f0f0f0;">Due Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nc->correctiveActions as $capa)
                <tr>
                    <td style="border: 1px solid #000; padding: 6px;">{{ $capa->capa_number ?? 'N/A' }}</td>
                    <td style="border: 1px solid #000; padding: 6px;">{{ $capa->title ?? 'N/A' }}</td>
                    <td style="border: 1px solid #000; padding: 6px;">{{ $capa->status_name ?? 'N/A' }}</td>
                    <td style="border: 1px solid #000; padding: 6px;">{{ $capa->due_date ? \Carbon\Carbon::parse($capa->due_date)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <div>Generated on {{ date('d/m/Y H:i:s') }} | {{ $company->name ?? 'JASIRI STENCIL LIMITED' }}</div>
    </div>
</body>
</html>








