<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>AI Monitoring & Governance Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 30px; }
        .container { padding: 0 40px; }
        .section-title { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #6f42c1; text-transform: uppercase; }
        .report-section { page-break-inside: avoid; margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 22px; font-weight: bold; color: #6f42c1; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 5px; font-weight: bold; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #f8fafc; padding: 8px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; color: #475569; }
        .data-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        .text-success { color: #16a34a; font-weight: bold; }
        .text-warning { color: #d97706; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 35px; border-bottom: 2px solid #6f42c1; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <img src="{{ public_path('assets/branding/logo.jpeg') }}" style="height: 60px; width: auto;">
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">Imara LIMS Command Center</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #6f42c1; text-transform: uppercase; margin-bottom: 2px;">AI Monitoring & Governance</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-AI | Generated: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #6f42c1; font-weight: bold; margin-top: 2px;">Security Class: System Critical</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">System Health & Performance Scorecard</div>
            <table style="width: 100%; border-collapse: separate; border-spacing: 12px 0; margin-left: -12px; margin-bottom: 0;">
                <tr>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value text-purple">{{ $stats['performance']['health_score'] ?? 0 }}%</div>
                            <div class="kpi-label">Health Score</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value text-success">{{ $stats['performance']['model_success_rate'] ?? 0 }}%</div>
                            <div class="kpi-label">Model Accuracy</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value text-info">{{ $stats['performance']['routing_accuracy'] ?? 0 }}%</div>
                            <div class="kpi-label">Routing Precision</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value {{ count($stats['alerts'] ?? []) > 0 ? 'text-danger' : '' }}">{{ count($stats['alerts'] ?? []) }}</div>
                            <div class="kpi-label">Active Alerts</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div style="width: 58%; display: inline-block; vertical-align: top; margin-right: 2%;">
                <div class="section-title">Model Performance Registry</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Model Name</th>
                            <th style="text-align: right;">Success Rate</th>
                            <th style="text-align: right;">Latency</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['models']['models'] ?? [] as $model)
                        <tr>
                            <td style="font-weight: bold;">{{ $model['model'] }}</td>
                            <td style="text-align: right;" class="{{ $model['success_rate_percent'] > 95 ? 'text-success' : 'text-warning' }}">
                                {{ $model['success_rate_percent'] }}%
                            </td>
                            <td style="text-align: right;">{{ number_format($model['avg_latency_ms']) }}ms</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="width: 38%; display: inline-block; vertical-align: top;">
                <div class="section-title">Intent Distribution</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Intent Type</th>
                            <th style="text-align: right;">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($stats['intents']['intents'] ?? [], 0, 8) as $intent)
                        <tr>
                            <td>{{ $intent['name'] }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($intent['count']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section">
            <div class="section-title">Governance & Drift Audit Log</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Alert Type</th>
                        <th>Severity</th>
                        <th>Message</th>
                        <th style="text-align: right;">Detected At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['alerts'] ?? [] as $alert)
                    <tr>
                        <td style="font-weight: bold;">{{ strtoupper($alert['type']) }}</td>
                        <td>
                            <span class="badge badge-{{ $alert['severity'] }}">{{ strtoupper($alert['severity']) }}</span>
                        </td>
                        <td>{{ $alert['message'] }}</td>
                        <td style="text-align: right;">{{ date('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #94a3b8;">No governance violations or feature drift detected in current snapshot.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">Deployed Infrastructure Registry</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Model ID</th>
                        <th>Framework</th>
                        <th>Version</th>
                        <th>Deployed At</th>
                        <th>Training Rows</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['models']['registry'] ?? [] as $reg)
                    <tr>
                        <td style="font-weight: bold;">{{ $reg['model_name'] }}</td>
                        <td>{{ $reg['framework'] }}</td>
                        <td>{{ $reg['version'] }}</td>
                        <td>{{ date('Y-m-d', strtotime($reg['deployed_at'])) }}</td>
                        <td style="text-align: right;">{{ number_format($reg['training_rows']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Confidential Imara AI Governance Report | System Monitoring Module | © {{ date('Y') }} Polucon LIMS
    </div>
</body>
</html>
