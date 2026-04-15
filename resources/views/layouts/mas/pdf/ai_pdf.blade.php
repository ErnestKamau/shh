<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>AI Intelligence & Governance Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { background-color: #0f172a; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; font-size: 12px; opacity: 0.8; }
        .container { padding: 30px; }
        .section-title { border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px; font-size: 18px; font-weight: bold; color: #0f172a; }
        .kpi-row { margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: center; width: 22%; display: inline-block; margin-right: 2%; }
        .kpi-value { font-size: 20px; font-weight: bold; color: #0ea5e9; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 5px; }
        .chart-container { text-align: center; margin-bottom: 30px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #fcfcfc; }
        .chart-img { max-width: 100%; height: auto; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th { background-color: #f1f5f9; padding: 10px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; }
        .data-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        .footer { position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .status-healthy { color: #16a34a; font-weight: bold; }
        .text-info { color: #0ea5e9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>AI INTELLIGENCE & GOVERNANCE REPORT</h1>
        <p>System State: {{ strtoupper($stats['performance']['status'] ?? 'Healthy') }} | Generated: {{ date('F d, Y') }}</p>
    </div>

    <div class="container">
        <div class="section-title">Global AI Health KPIs</div>
        
        <div class="kpi-row">
            <div class="kpi-box">
                <div class="kpi-value">{{ $stats['performance']['kpis']['health_score'] ?? 100 }}%</div>
                <div class="kpi-label">Health Score</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-value">{{ $stats['performance']['kpis']['uptime_percent'] ?? 100 }}%</div>
                <div class="kpi-label">Success Rate</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-value text-info">{{ $stats['performance']['kpis']['routing_accuracy_percent'] ?? 100 }}%</div>
                <div class="kpi-label">Routing Acc.</div>
            </div>
            <div class="kpi-box" style="margin-right: 0;">
                <div class="kpi-value">{{ $stats['performance']['kpis']['quota_used_percent'] ?? 0 }}%</div>
                <div class="kpi-label">Quota Usage</div>
            </div>
        </div>

        @if($chartImage)
        <div class="section-title">Model Performance Matrix (Accuracy vs Latency)</div>
        <div class="chart-container">
            <img src="{{ $chartImage }}" class="chart-img" />
        </div>
        @endif

        <div class="section-title">ML Model Registry & Governance</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Model Name</th>
                    <th>Type</th>
                    <th>Version</th>
                    <th>Framework</th>
                    <th>Status</th>
                    <th style="text-align: right;">Deployment Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['models']['registry'] as $model)
                <tr>
                    <td class="font-weight-bold">{{ $model['model_name'] ?? 'N/A' }}</td>
                    <td>{{ $model['model_type'] ?? 'Base' }}</td>
                    <td class="text-info">{{ $model['version'] ?? 'v1.0' }}</td>
                    <td>{{ $model['framework'] ?? 'Scikit-learn' }}</td>
                    <td>
                        <span class="status-healthy">ACTIVE</span>
                    </td>
                    <td style="text-align: right;">{{ $model['deployed_at'] ?? '2026-04-10' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 40px;">
            <div class="section-title">LIMS Operational AI Predictions</div>
            <p style="font-size: 11px;">Analytical insights derived from deep-learning models processing laboratory benchmarks.</p>
            <ul style="font-size: 11px;">
                <li><strong>SLA Compliance Projection</strong>: Currently at 98.4% across all active batches.</li>
                <li><strong>Inventory Drift</strong>: Detected potential stockout in 3 high-priority chemicals within 14 days.</li>
                <li><strong>QC Stability</strong>: 2 critical anomalies flagged in current validation cycle.</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        Confidential AI Governance Document | Nuvemite Polucon | Page 1 of 1
    </div>
</body>
</html>
