<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Audit Summary Report</title>
    <style>
        @page {
            margin-bottom: 10px;
            margin-top: 200px;
            margin-left: 20px;
            margin-right: 20px;

            @bottom-center {
                content: element(footer);
            }

            @top-center {
                content: element(header);
            }
        }

        * {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-stretch: condensed;
        }

        .header {
            position: fixed;
            top: -180px;
            left: 0;
            right: 0;
            height: 150px;
            z-index: 1000;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: auto;
            z-index: 1000;
            background-color: white;
            padding: 10px 0;
        }

        body {
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        table {
            border-collapse: collapse !important;
            width: 100%;
            margin-top: 10px;
            font-size: 9pt;
            table-layout: fixed;
        }

        table th,
        table td {
            border: 1px solid #000 !important;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt !important;
            padding: 6px 5px;
        }

        tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .col-item {
            width: 6%;
            text-align: center;
            font-weight: bold;
        }

        .col-nonconformity {
            width: 26%;
            font-size: 8.5pt;
        }

        .col-requirement {
            width: 14%;
            font-size: 8.5pt;
        }

        .col-corrective-action {
            width: 26%;
            font-size: 8.5pt;
        }

        .col-when {
            width: 11%;
            text-align: center;
            font-size: 8.5pt;
        }

        .col-responsible {
            width: 11%;
            text-align: center;
            font-size: 8.5pt;
        }

        .col-status {
            width: 6%;
            text-align: center;
            font-size: 8.5pt;
            font-weight: bold;
        }

        .main-content {
            margin-bottom: 200px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    @php
        // Use the logo path passed from controller
        $logoPath = $path ?? public_path('images/no-logo.png');
    @endphp

    <header class="header">
        <!-- Header: Company Logo (Left) and Report Info (Right) -->
        <table style="width: 100%; border-collapse: collapse !important; border: none !important; margin-bottom: 8px;">
            <tr>
                <td style="border: none !important; width: 50%; vertical-align: top; padding: 0; padding-left: 0;">
                    <!-- Company Logo and Name -->
                    <img src="{{ $logoPath }}" style="height: 70px; max-width: 100%; object-fit: contain; display: block;" alt="logo">
                    <div style="font-size: 10pt !important; font-weight: bold; margin-top: 3px; line-height: 1.3;">
                        {{ $company->name ?? 'JASIRI STENCIL LIMITED' }}
                    </div>
                    <div style="font-size: 9pt !important; line-height: 1.2;">
                        Home of Quality Assurance
                    </div>
                </td>
                <td style="border: none !important; width: 50%; vertical-align: top; padding: 0; padding-right: 0; text-align: right;">
                    <!-- Report Number and Revision -->
                    <div style="font-size: 11pt !important; font-weight: bold; margin-bottom: 3px; text-align: right;">
                        {{ $audit->report_number }}
                    </div>
                    <div style="font-size: 10pt !important; text-align: right;">
                        {{ $audit->revision_number }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Main Title -->
        <div style="text-align: center; margin-bottom: 6px; margin-top: 5px;">
            <span style="font-size: 16pt !important; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
                AUDIT SUMMARY REPORT
            </span>
        </div>

        <!-- Subtitle -->
        <div style="text-align: center; margin-bottom: 8px;">
            <span style="font-size: 11.5pt !important; font-weight: bold;">
                {{ $audit->audit_type }}
            </span>
        </div>

        <!-- Report Meta Info -->
        <table style="width: 100%; border-collapse: collapse !important; border: none !important;">
            <tr>
                <td style="border: none !important; width: 33.33%; font-size: 9.5pt !important; padding: 2px 5px; text-align: left;">
                    <strong>DATE:</strong> 
                    @if($audit->audit_date_end && $audit->audit_date_start->format('d/m/y') !== $audit->audit_date_end->format('d/m/y'))
                        {{ $audit->audit_date_start->format('d') }} - {{ $audit->audit_date_end->format('d') }} /{{ $audit->audit_date_end->format('m/y') }}
                    @else
                        {{ $audit->audit_date_start->format('d') }} - {{ $audit->audit_date_start->format('m/y') }}
                    @endif
                </td>
                <td style="border: none !important; width: 33.33%; font-size: 9.5pt !important; padding: 2px 5px; text-align: left;">
                    <strong>AUDITOR:</strong> {{ $audit->auditor_name ?: '________________' }}
                </td>
                <td style="border: none !important; width: 33.34%; font-size: 9.5pt !important; padding: 2px 5px; text-align: left;">
                    <strong>AUDITEE:</strong> {{ $audit->auditee_name ?: '________________' }}
                </td>
            </tr>
        </table>
    </header>

    <footer class="footer">
        <table style="width: 100%; border-collapse: collapse !important; border: none !important;">
            <tr>
                <td style="border: none !important; width: 50%; vertical-align: top; padding: 5px;">
                    <!-- Company Logo and Name in Footer -->
                    <img src="{{ $logoPath }}" style="height: 60px; max-width: 100%; object-fit: contain;" alt="logo">
                    <div style="font-size: 9pt; font-weight: bold; margin-top: 3px;">
                        {{ $company->name ?? 'JASIRI STENCIL LIMITED' }}
                    </div>
                    <div style="font-size: 8pt;">
                        Home of Quality Assurance
                    </div>
                </td>
                <td style="border: none !important; width: 50%; vertical-align: top; padding: 5px; text-align: right;">
                    <!-- Footer right side can be empty or have additional info -->
                </td>
            </tr>
        </table>
    </footer>

    <main class="main-content">
        <table>
            <thead>
                <tr>
                    <th class="col-item">TEM</th>
                    <th class="col-nonconformity">NONCONFORMITY</th>
                    <th class="col-requirement">REQUIREMENT</th>
                    <th class="col-corrective-action">CORRECTIVE ACTION</th>
                    <th class="col-when">WHEN</th>
                    <th class="col-responsible">RESPONSIBLE</th>
                    <th class="col-status">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audit->findings as $finding)
                <tr>
                    <td class="col-item"><strong>{{ $finding->item_number }}</strong></td>
                    <td class="col-nonconformity">
                        {{ $finding->nonconformity }}
                        @if($finding->evidence)
                            <br><br><strong>Evidence:</strong> {{ $finding->evidence }}
                        @endif
                    </td>
                    <td class="col-requirement">{{ $finding->requirement ?: '-' }}</td>
                    <td class="col-corrective-action">{{ $finding->corrective_action ?: '-' }}</td>
                    <td class="col-when">
                        {{ $finding->action_date ? $finding->action_date->format('d/m/y') : '-' }}
                    </td>
                    <td class="col-responsible">{{ $finding->responsible_party ?: '-' }}</td>
                    <td class="col-status">
                        @if($finding->status === 'closed')
                            CLOSED
                        @elseif($finding->status === 'in_progress')
                            IN PROGRESS
                        @else
                            OPEN
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">
                        No findings recorded
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>
