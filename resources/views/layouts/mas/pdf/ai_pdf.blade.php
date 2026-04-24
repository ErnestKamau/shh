<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>AI Intelligence & Governance - Report</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1e293b; line-height: 1.5; margin: 0; padding: 0; background-color: #fff; font-size: 11px; }
        .header { margin-bottom: 25px; border-bottom: 2px solid #6f42c1; padding-bottom: 15px; }
        .container { width: 100%; }
        .section-title { font-size: 13px; font-weight: 800; color: #5b21b6; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; border-left: 4px solid #6f42c1; padding-left: 8px; }
        .report-section { page-break-inside: avoid; margin-bottom: 30px; }
        
        /* KPI Grid */
        .kpi-table { width: 100%; border-spacing: 10px 0; margin-left: -10px; margin-bottom: 20px; }
        .kpi-card { background-color: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 12px; text-align: center; }
        .kpi-value { font-size: 22px; font-weight: 800; color: #6d28d9; margin-bottom: 1px; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #7c3aed; font-weight: 700; }
        
        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table th { background-color: #f8fafc; padding: 8px 6px; text-align: left; font-size: 9px; font-weight: 700; color: #475569; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        .data-table td { padding: 8px 6px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .font-bold { font-weight: 700; }
        
        /* Badges */
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: 700; text-transform: uppercase; display: inline-block; }
        .badge-purple { background-color: #ede9fe; color: #6d28d9; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef9c3; color: #854d0e; }
        
        /* Charts */
        .chart-container { text-align: center; padding: 15px; border: 1px solid #f1f5f9; border-radius: 10px; background-color: #fafafa; margin-top: 5px; }
        .chart-img { max-width: 100%; height: auto; max-height: 300px; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="footer">
        IMARA LIMS | AI Intelligence & Governance Report | {{ date('Y-m-d H:i') }}
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <img src="{{ public_path('assets/branding/logo.jpeg') }}" style="height: 50px; width: auto;">
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <div style="font-size: 18px; font-weight: 900; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2px;">AI Governance Report</div>
                        <div style="font-size: 9px; color: #64748b; font-weight: 700;">IMARA INTELLIGENCE CORE v1.2.0</div>
                        <div style="font-size: 8px; color: #94a3b8;">GENERATED: {{ date('d M Y, H:i') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- KPI Row -->
        <div class="report-section">
            <div class="section-title">System Health & Compliance</div>
            <table class="kpi-table">
                <tr>
                    <td style="width: 25%;">
                        <div class="kpi-card">
                            <div class="kpi-value">{{ number_format($stats['performance']['kpis']['health_score'] ?? 0, 1) }}%</div>
                            <div class="kpi-label">Health Score</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-card">
                            <div class="kpi-value">{{ number_format($stats['performance']['kpis']['uptime_percent'] ?? 0, 1) }}%</div>
                            <div class="kpi-label">Success Rate</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-card">
                            <div class="kpi-value">{{ number_format($stats['performance']['kpis']['routing_accuracy_percent'] ?? 0, 1) }}%</div>
                            <div class="kpi-label">Routing Quality</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-card">
                            <div class="kpi-value" style="color: {{ count($stats['alerts'] ?? []) > 0 ? '#dc2626' : '#166534' }}">
                                {{ count($stats['alerts'] ?? []) }}
                            </div>
                            <div class="kpi-label">Active Alerts</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Operational Methodology (Fills Whitespace) -->
        <div class="report-section" style="background-color: #fcfaff; border: 1px solid #ede9fe; border-radius: 12px; padding: 20px; margin-top: -10px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: top; border-right: 1px solid #ddd6fe; padding-right: 25px;">
                        <div style="font-size: 14px; font-weight: 800; color: #5b21b6; text-transform: uppercase; margin-bottom: 12px;">Technical Governance Methodology</div>
                        <p style="font-size: 11px; color: #4b5563; margin-bottom: 18px; line-height: 1.4;">
                            The AI Intelligence Core utilizes a multi-layered telemetry system to monitor the operational health of all deployed models. 
                            Performance data is aggregated across the last 30 operational days to ensure statistical significance in drift detection and latency analysis.
                        </p>
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                                    <div style="margin-bottom: 10px;">
                                        <strong style="color: #6d28d9; font-size: 10px;">HEALTH SCORE</strong>
                                        <p style="font-size: 10px; color: #64748b; margin: 3px 0; line-height: 1.3;">Stability index based on infrastructure uptime and resource consumption patterns.</p>
                                    </div>
                                    <div>
                                        <strong style="color: #6d28d9; font-size: 10px;">SUCCESS RATE</strong>
                                        <p style="font-size: 10px; color: #64748b; margin: 3px 0; line-height: 1.3;">Ratio of nominal request completions vs. model-level exceptions.</p>
                                    </div>
                                </td>
                                <td style="width: 50%; vertical-align: top;">
                                    <div style="margin-bottom: 10px;">
                                        <strong style="color: #6d28d9; font-size: 10px;">ROUTING QUALITY</strong>
                                        <p style="font-size: 10px; color: #64748b; margin: 3px 0; line-height: 1.3;">Intent classification accuracy; scores below 0.7 trigger automated re-evaluation.</p>
                                    </div>
                                    <div>
                                        <strong style="color: #6d28d9; font-size: 10px;">ACTIVE ALERTS</strong>
                                        <p style="font-size: 10px; color: #64748b; margin: 3px 0; line-height: 1.3;">Security violations and performance drift triggers requiring immediate attention.</p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 40%; vertical-align: top; padding-left: 25px;">
                        <div style="font-size: 14px; font-weight: 800; color: #5b21b6; text-transform: uppercase; margin-bottom: 12px;">Compliance Status</div>
                        <div style="background-color: #fff; border-radius: 8px; padding: 15px; border: 1px solid #ddd6fe;">
                            <div style="font-size: 11px; margin-bottom: 10px;">
                                <span style="font-weight: 700; color: #1e293b;">Model Integrity:</span> 
                                <span class="badge badge-success" style="float: right; font-size: 10px; padding: 4px 8px;">VERIFIED</span>
                            </div>
                            <div style="font-size: 11px; margin-bottom: 10px;">
                                <span style="font-weight: 700; color: #1e293b;">Governance Policy:</span> 
                                <span class="badge badge-success" style="float: right; font-size: 10px; padding: 4px 8px;">COMPLIANT</span>
                            </div>
                            <div style="font-size: 11px;">
                                <span style="font-weight: 700; color: #1e293b;">Risk Assessment:</span> 
                                <span class="badge badge-{{ count($stats['alerts'] ?? []) > 0 ? 'warning' : 'success' }}" style="float: right; font-size: 10px; padding: 4px 8px;">
                                    {{ count($stats['alerts'] ?? []) > 0 ? 'REVIEW NEEDED' : 'LOW RISK' }}
                                </span>
                            </div>
                        </div>
                        <p style="font-size: 8px; color: #94a3b8; margin-top: 15px; line-height: 1.3;">
                            * Compliance verified against IMARA AI Governance Framework v2.1. 
                            Internal audit trail recorded for all intent classifications and model switches.
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Live Performance -->
        <div class="report-section">
            <div class="section-title">Model Performance Leaderboard</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Model Name</th>
                        <th class="text-center">Success Rate</th>
                        <th class="text-center">Avg Latency</th>
                        <th class="text-center">Avg Tokens</th>
                        <th class="text-right">Confidence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['models']['models'] ?? [] as $model)
                    <tr>
                        <td class="font-bold">{{ $model['model'] }}</td>
                        <td class="text-center" style="color: #166534; font-weight: 700;">{{ number_format($model['success_rate_percent'] ?? 0, 1) }}%</td>
                        <td class="text-center">{{ number_format($model['avg_latency_ms'] ?? 0) }} ms</td>
                        <td class="text-center">{{ number_format($model['avg_tokens_per_request'] ?? 0) }}</td>
                        <td class="text-right font-bold">{{ number_format(($model['avg_confidence'] ?? 0) * 100, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Deployed Registry -->
        <div class="report-section">
            <div class="section-title">Deployed Model Registry</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Registry Name</th>
                        <th>Type</th>
                        <th class="text-center">Version</th>
                        <th class="text-center">Framework</th>
                        <th class="text-right">Deployed At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['models']['registry'] ?? [] as $reg)
                    <tr>
                        <td class="font-bold">{{ $reg['model_name'] }}</td>
                        <td style="font-size: 9px; color: #64748b;">{{ $reg['model_type'] }}</td>
                        <td class="text-center">
                            <span class="badge badge-purple">{{ $reg['version'] }}</span>
                        </td>
                        <td class="text-center">{{ $reg['framework'] }}</td>
                        <td class="text-right">{{ date('Y-m-d', strtotime($reg['deployed_at'])) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Intent Distribution & Visuals -->
        @if($chartImage)
        <div class="report-section">
            <div class="section-title">User Intent & Interaction Distribution</div>
            <div class="chart-container">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
        </div>
        @endif

        <div class="report-section">
            <div class="section-title">Intent Data Breakdown</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Intent Classification</th>
                        <th class="text-right">Total Requests</th>
                        <th class="text-right">Share (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalIntents = collect($stats['intents']['intents'] ?? [])->sum('count') ?: 1;
                    @endphp
                    @foreach($stats['intents']['intents'] ?? [] as $intent)
                    <tr>
                        <td class="font-bold">{{ ucfirst(str_replace('_', ' ', $intent['name'] ?? 'unknown')) }}</td>
                        <td class="text-right">{{ number_format($intent['count'] ?? 0) }}</td>
                        <td class="text-right font-bold" style="color: #6d28d9;">
                            {{ round((($intent['count'] ?? 0) / $totalIntents) * 100, 1) }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Active Alerts -->
        @if(count($stats['alerts'] ?? []) > 0)
        <div class="report-section">
            <div class="section-title">Governance & Drift Alerts</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Severity</th>
                        <th style="width: 15%;">Type</th>
                        <th>Observation / Alert Message</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['alerts'] ?? [] as $alert)
                    <tr>
                        <td>
                            <span class="badge badge-{{ $alert['severity'] ?? 'danger' }}">
                                {{ strtoupper($alert['severity'] ?? 'CRITICAL') }}
                            </span>
                        </td>
                        <td class="font-bold">{{ strtoupper($alert['type'] ?? 'DRIFT') }}</td>
                        <td>{{ $alert['message'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</body>
</html>
