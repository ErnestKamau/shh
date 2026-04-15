<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>General Laboratory Analytics Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 30px; }
        .container { padding: 0 30px; }
        .section-title { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 20px; font-size: 16px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; }
        .kpi-row { margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 15px; text-align: center; width: 45%; display: inline-block; margin-right: 2%; }
        .kpi-value { font-size: 22px; font-weight: bold; color: #3b82f6; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 5px; font-weight: bold; }
        .chart-container { text-align: center; margin-bottom: 30px; padding: 10px; border: 1px solid #f1f5f9; border-radius: 6px; background-color: #fcfcfc; }
        .chart-img { max-width: 100%; height: auto; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background-color: #f8fafc; padding: 8px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; color: #475569; }
        .data-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 30px; border-bottom: 2px solid #1e3a8a; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="font-size: 26px; font-weight: bold; color: #1e3a8a; letter-spacing: -1.5px;">
                            NUVEMITE <span style="color: #3b82f6;">POLUCON</span>
                        </div>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">Laboratory Intelligence Command Center</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin-bottom: 2px;">General Lab Analytics</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-GEN | Printed: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #3b82f6; font-weight: bold; margin-top: 2px;">Classification: OPERATIONAL</div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="section-title">Laboratory Operations Summary</div>
        
        <table style="width: 100%; border-collapse: separate; border-spacing: 15px 0; margin-left: -15px; margin-bottom: 20px;">
            <tr>
                <td style="width: 50%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0; padding: 20px;">
                        <div class="kpi-value" style="font-size: 28px;">{{ $stats['summary']['active_batches'] ?? 0 }}</div>
                        <div class="kpi-label">Active Workload</div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0; padding: 20px;">
                        <div class="kpi-value" style="font-size: 28px;">{{ count($stats['top_clients']) }}</div>
                        <div class="kpi-label">Key Clients Engaged</div>
                    </div>
                </td>
            </tr>
        </table>

        @if($chartImage)
        <div class="section-title">Workload Distribution Analysis</div>
        <div class="chart-container">
            <img src="{{ $chartImage }}" class="chart-img" />
            <p style="font-size: 9px; color: #94a3b8; font-style: italic;">Analytical distribution snapshot from dashboard metrics</p>
        </div>
        @endif

        <div class="section-title">Top Clients by Workload Share</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Client Name</th>
                    <th style="text-align: right;">Batch Count</th>
                    <th style="text-align: right;">Workload %</th>
                </tr>
            </thead>
            <tbody>
                @php $total = collect($stats['top_clients'])->sum('total') ?: 1; @endphp
                @foreach($stats['top_clients'] as $index => $client)
                <tr>
                    <td style="width: 50px;">#{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ $client->name }}</td>
                    <td style="text-align: right;">{{ $client->total }}</td>
                    <td style="text-align: right;">{{ round(($client->total / $total) * 100, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Confidential Document | General Laboratory Analytics Dashboard | Nuvemite Polucon
    </div>
</body>
</html>
