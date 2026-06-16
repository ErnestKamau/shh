<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LWS-011 Temperature Monitoring Record</title>
    <style>
        @page {
            margin: 15px 20px 15px 20px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 8px;
            line-height: 1.2;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
        }
        .logo-container {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }
        .title-container {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .control-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
        }
        .control-table td {
            border: none !important;
            padding: 2px !important;
        }
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 8px;
        }
        .metadata-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .metadata-table td.label-cell {
            background-color: #802424;
            color: #ffffff;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 7.5px;
        }
        .data-table th {
            border: 1px solid #000;
            background-color: #802424;
            color: #ffffff;
            padding: 3px 1px;
            font-weight: bold;
            font-size: 7.5px;
        }
        .data-table td {
            border: 1px solid #000;
            padding: 2.5px 1px;
            height: 15px;
        }
        .footer-table {
            width: 100%;
            margin-top: 10px;
            font-size: 8px;
            border-collapse: collapse;
        }
        .footer-table td {
            border: none;
            padding: 3px;
            vertical-align: top;
        }
        .signature-line {
            margin-top: 15px;
            border-top: 1px solid #000;
            width: 180px;
            padding-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Header Block -->
    <table class="header-table">
        <tr>
            <td style="width: 20%; padding: 6px;">
                <div class="logo-container">
                    @if(defined('PUBLIC_PATH') && file_exists(public_path('images/logo.png')))
                        <img src="{{ public_path('images/logo.png') }}" style="max-height: 22px;" alt="Logo"><br>
                    @endif
                    <span style="color: #802424; font-family: 'Times New Roman', Times, serif; font-size: 14px; font-weight: bold; letter-spacing: 0.5px;">AmSpec</span>
                </div>
            </td>
            <td style="width: 55%; padding: 0 10px;">
                <div class="title-container">
                    <div style="font-size: 10px; border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 2px; letter-spacing: 0.5px;">LABORATORY WORK SHEET</div>
                    <div style="font-size: 11px; font-weight: bold; color: #000;">TEMPERATURE MONITORING RECORD FOR CHILLER, REFRIGERATOR AND FREEZER</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0;">
                <table class="control-table">
                    <tr>
                        <td style="font-weight: bold; width: 45%;">Doc No.</td>
                        <td style="border-left: 1px solid #000 !important; width: 55%; padding-left: 4px !important;">AMS/QMS/LWS/011</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; border-top: 1px solid #000 !important;">Revision Date</td>
                        <td style="border-top: 1px solid #000 !important; border-left: 1px solid #000 !important; padding-left: 4px !important;">12 October, 2025</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; border-top: 1px solid #000 !important;">Revision No.</td>
                        <td style="border-top: 1px solid #000 !important; border-left: 1px solid #000 !important; padding-left: 4px !important;">10.2025.R<sub>0</sub></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Metadata Block -->
    <table class="metadata-table">
        <tr>
            <td class="label-cell" style="width: 15%;">Equipment Name:</td>
            <td style="width: 35%; font-weight: bold;">{{ $equipment?->name ?? 'Chiller' }}</td>
            <td class="label-cell" style="width: 15%;">Thermometer ID:</td>
            <td style="width: 35%; font-weight: bold;">{{ $equipment?->thermometer_id ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Equipment ID:</td>
            <td style="font-weight: bold;">{{ $equipment?->equipment_number ?? '—' }}</td>
            <td class="label-cell">Tolerance:</td>
            <td style="font-weight: bold;">{{ $toleranceStr }}</td>
        </tr>
        <tr>
            <td class="label-cell">Month:</td>
            <td style="font-weight: bold;">{{ $monthName }}</td>
            <td class="label-cell">Location:</td>
            <td style="font-weight: bold;">{{ $equipment?->assetLocation?->name ?? $section->name }}</td>
        </tr>
    </table>

    <!-- Data Grid Block -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="3" style="width: 8%;">Date</th>
                <th rowspan="3" style="width: 8%;">Time</th>
                <th rowspan="3" style="width: 12%;">Thermometer calibration Valid (Yes/No)</th>
                <th rowspan="3" style="width: 12%;">Wire & Plug Condition (Yes/No)</th>
                <th colspan="4" style="width: 36%; border-bottom: 1px solid #ffffff;">Observed Temperature (&deg;C)</th>
                <th rowspan="3" style="width: 12%;">Remarks</th>
                <th rowspan="3" style="width: 12%;">Checked By / Signature</th>
            </tr>
            <tr>
                <th colspan="2" style="border-bottom: 1px solid #ffffff;">Chiller/Refrigerator</th>
                <th colspan="2" style="border-bottom: 1px solid #ffffff;">Freezer</th>
            </tr>
            <tr>
                <th>Minimum</th>
                <th>Maximum</th>
                <th>Minimum</th>
                <th>Maximum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gridData as $day => $data)
                @php
                    $formattedDate = sprintf('%02d/%02d/%04d', $day, $monthNum, $yearNum);
                    $timeVal = $data['exists'] ? '8:00am' : '';
                @endphp
                <tr style="{{ $day % 2 === 0 ? 'background-color: #fdfdfd;' : '' }}">
                    <td style="font-weight: bold; background-color: #f9f9f9;">{{ $formattedDate }}</td>
                    <td>{{ $timeVal }}</td>
                    <td>{{ $data['calibration_valid'] }}</td>
                    <td>{{ $data['wire_plug_condition'] }}</td>
                    
                    <td>{{ $data['chiller_min'] }}</td>
                    <td>{{ $data['chiller_max'] }}</td>
                    
                    <td>{{ $data['freezer_min'] }}</td>
                    <td>{{ $data['freezer_max'] }}</td>
                    
                    <td style="text-align: left; font-size: 6.5px; padding-left: 3px; max-width: 80px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $data['remarks'] }}">
                        {{ $data['remarks'] }}
                    </td>
                    <td style="font-size: 6.5px; font-weight: bold; color: #333;">{{ $data['operator'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Verification / Signatures Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                Prepared By:<br>
                <div class="signature-line" style="font-size: 7.5px;">
                    Laboratory Technician / Specialist
                </div>
            </td>
            <td style="width: 50%;">
                Reviewed & Approved By:<br>
                <div class="signature-line" style="font-size: 7.5px;">
                    Quality Manager / Laboratory Manager
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
