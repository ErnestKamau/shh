<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>QC Stability Performance Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 30px; }
        .container { padding: 0 30px; }
        .section-title { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 20px; font-size: 16px; font-weight: bold; color: #4338ca; text-transform: uppercase; }
        .kpi-row { margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 15px; text-align: center; width: 22%; display: inline-block; margin-right: 2%; }
        .kpi-value { font-size: 20px; font-weight: bold; color: #6366f1; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 5px; font-weight: bold; }
        .chart-container { text-align: center; margin-bottom: 30px; padding: 10px; border: 1px solid #f1f5f9; border-radius: 6px; background-color: #fcfcfc; }
        .chart-img { max-width: 100%; height: auto; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background-color: #f8fafc; padding: 8px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; color: #475569; }
        .data-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        .text-success { color: #16a34a; font-weight: bold; }
        .text-warning { color: #d97706; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 30px; border-bottom: 2px solid #4338ca; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="font-size: 26px; font-weight: bold; color: #4338ca; letter-spacing: -1.5px;">
                            NUVEMITE <span style="color: #6366f1;">POLUCON</span>
                        </div>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">Laboratory Intelligence Command Center</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #4338ca; text-transform: uppercase; margin-bottom: 2px;">QC Stability Performance</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-QC | Printed: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #6366f1; font-weight: bold; margin-top: 2px;">Classification: COMPLIANCE</div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="section-title">Stability Summary Scorecard</div>
        
        <table style="width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-left: -10px; margin-bottom: 20px;">
            <tr>
                <td style="width: 25%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0; padding: 12px;">
                        <div class="kpi-value">{{ $stats['summary']['total_records'] ?? 0 }}</div>
                        <div class="kpi-label">Analytes</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0; padding: 12px;">
                        <div class="kpi-value text-success">{{ $stats['summary']['stable_records'] ?? 0 }}</div>
                        <div class="kpi-label">Stable</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0; padding: 12px;">
                        <div class="kpi-value text-danger">{{ $stats['summary']['critical_records'] ?? 0 }}</div>
                        <div class="kpi-label">Critical</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0; padding: 12px;">
                        <div class="kpi-value">{{ $stats['summary']['avg_cv_percentage'] ?? 0 }}%</div>
                        <div class="kpi-label">Avg CV%</div>
                    </div>
                </td>
            </tr>
        </table>

        @if($chartImage)
        <div class="section-title">Stability Status Distribution</div>
        <div class="chart-container">
            <img src="{{ $chartImage }}" class="chart-img" />
        </div>
        @endif

        <div class="section-title">Critical Stability Watchlist</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Analyte Name</th>
                    <th>Sample Type</th>
                    <th style="text-align: right;">Robust CV%</th>
                    <th style="text-align: right;">Pass Rate</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($stats['exception_rows'] ?? [], 0, 15) as $row)
                <tr>
                    <td class="font-weight-bold">{{ $row['analyte_name'] }}</td>
                    <td>{{ $row['sample_type_name'] }}</td>
                    <td style="text-align: right;">{{ number_format($row['robust_cv_percentage'], 2) }}%</td>
                    <td style="text-align: right;">{{ number_format($row['pass_rate_pct'], 1) }}%</td>
                    <td style="text-align: center;">
                        @if($row['stability_status'] == 'stable')
                            <span class="text-success">STABLE</span>
                        @elseif($row['stability_status'] == 'warning')
                            <span class="text-warning">WARNING</span>
                        @else
                            <span class="text-danger">CRITICAL</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Confidential Document | Quality Control Laboratory Oversight | Nuvemite Polucon
    </div>
</body>
</html>
