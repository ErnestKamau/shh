<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>CAPA Status Report</title>
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
            font-size: 8.5pt;
        }

        table th,
        table td {
            border: 1px solid #000 !important;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
        }

        tr {
            page-break-inside: avoid;
        }

        .overdue {
            color: #dc3545;
            font-weight: bold;
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

    <div class="report-title">Corrective Action Status Report</div>

    <div class="report-info">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        <br>
        <strong>Total Records:</strong> {{ $capas->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">CAPA Number</th>
                <th style="width: 20%;">Title</th>
                <th style="width: 10%;">NC Number</th>
                <th style="width: 12%;">Category</th>
                <th style="width: 10%;">Priority</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 12%;">Action Owner</th>
                <th style="width: 14%;">Due Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($capas as $capa)
            <tr>
                <td>{{ $capa->capa_number ?? 'N/A' }}</td>
                <td>{{ $capa->title ?? 'N/A' }}</td>
                <td>{{ $capa->nonConformance->nc_number ?? 'N/A' }}</td>
                <td>{{ $capa->category->name ?? 'N/A' }}</td>
                <td>{{ $capa->priority->name ?? 'N/A' }}</td>
                <td>{{ $capa->status_name ?? 'N/A' }}</td>
                <td>{{ $capa->actionOwnerUser->name ?? 'N/A' }}</td>
                <td class="{{ $capa->due_date && now()->greaterThan(\Carbon\Carbon::parse($capa->due_date)) && !$capa->implementation_date ? 'overdue' : '' }}">
                    {{ $capa->due_date ? \Carbon\Carbon::parse($capa->due_date)->format('d/m/Y') : 'N/A' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">No corrective actions found for the selected period</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>Generated on {{ date('d/m/Y H:i:s') }} | {{ $company->name ?? 'JASIRI STENCIL LIMITED' }}</div>
    </div>
</body>
</html>








