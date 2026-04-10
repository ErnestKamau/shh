<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Audits Export Report</title>
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
            line-height: 1.4;
            font-size: 10pt;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header table td {
            border: none !important;
            padding: 5px;
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

        .report-info {
            font-size: 9pt;
            margin-bottom: 10px;
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
            word-wrap: break-word;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
        }

        tr {
            page-break-inside: avoid;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 8pt;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 70%;">
                    <div class="company-name">{{ $company->name ?? 'JASIRI STENCIL LIMITED' }}</div>
                    <div style="font-size: 9pt;">Home of Quality Assurance</div>
                </td>
                <td style="width: 30%; text-align: right;">
                    <div style="font-size: 9pt;"><strong>Generated:</strong> {{ date('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">Audits Export Report</div>

    <div class="report-info">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        <br>
        <strong>Total Records:</strong> {{ $audits->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Audit Number</th>
                <th style="width: 25%;">Title</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 15%;">Lead Auditor</th>
                <th style="width: 12%;">Scheduled Date</th>
                <th style="width: 12%;">Start Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($audits as $audit)
            <tr>
                <td>{{ $audit->audit_number ?? 'N/A' }}</td>
                <td>{{ $audit->title ?? 'N/A' }}</td>
                <td>{{ $audit->auditType->name ?? $audit->audit_type_name ?? 'N/A' }}</td>
                <td>{{ $audit->status->name ?? $audit->status_name ?? 'N/A' }}</td>
                <td>{{ $audit->leadAuditor->name ?? $audit->lead_auditor_name ?? 'N/A' }}</td>
                <td>{{ $audit->scheduled_date ? \Carbon\Carbon::parse($audit->scheduled_date)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $audit->start_date ? \Carbon\Carbon::parse($audit->start_date)->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">No audits found for the selected period</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>Generated on {{ date('d/m/Y H:i:s') }} | {{ $company->name ?? 'JASIRI STENCIL LIMITED' }}</div>
    </div>
</body>
</html>








