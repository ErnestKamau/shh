<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/lab.tat_title') }} - {{ __('mas/common.report') }}</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1e293b; line-height: 1.6; margin: 0; padding: 0; background-color: #fff; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; }
        .container { width: 100%; }
        .section-title { font-size: 14px; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; border-left: 4px solid #3b82f6; padding-left: 10px; }
        .report-section { page-break-inside: avoid; margin-bottom: 35px; }
        
        /* KPI Grid */
        .kpi-row { width: 100%; margin-bottom: 20px; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 26px; font-weight: 800; color: #2563eb; margin-bottom: 2px; }
        .kpi-label { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 700; }
        
        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; background-color: #fff; }
        .data-table th { background-color: #f1f5f9; padding: 12px 10px; text-align: left; font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        .data-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; color: #334155; }
        .font-bold { font-weight: 700; }
        
        /* Status Badges */
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef9c3; color: #854d0e; }
        .badge-primary { background-color: #e0e7ff; color: #3730a3; }
        
        /* Charts */
        .chart-container { text-align: center; padding: 20px; border: 1px solid #f1f5f9; border-radius: 12px; background-color: #fafafa; margin-top: 10px; }
        .chart-img { max-width: 100%; height: auto; display: block; margin: 0 auto; max-height: 350px; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="footer">
        Confidential | IMARA LIMS | {{ date('Y') }}
    </div>

    <div class="container">

        <!-- Header -->
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <img src="{{ public_path('assets/branding/logo.jpeg') }}" style="height: 60px; width: auto;">
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <div style="font-size: 22px; font-weight: 900; color: #1e40af; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2px;">TAT Analysis</div>
                        <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('mas/dashboard.insights_report') }}</div>
                        <div style="font-size: 9px; color: #94a3b8;">{{ __('mas/report.generated') }}: {{ date('d M Y, H:i') }}</div>
                        <div style="font-size: 9px; color: #3b82f6; font-weight: 700; margin-top: 5px;">CONFIDENTIAL OPERATIONAL DATA</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- KPI Overview -->
        <div class="report-section">
            <div class="section-title">Performance Snapshot</div>
            <table style="width: 100%; border-spacing: 15px 0; margin-left: -15px;">
                <tr>
                    <td style="width: 25%;">
                        <div class="kpi-card">
                            <div class="kpi-value">{{ $stats['summary']['active_batches'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/dashboard.active_batches') }}</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-card" style="border-left: 4px solid #ef4444;">
                            <div class="kpi-value" style="color: #dc2626;">{{ $stats['summary']['overdue_batches'] ?? 0 }}</div>
                            <div class="kpi-label">Breached SLAs</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-card">
                            <div class="kpi-value" style="color: #10b981;">{{ $stats['summary']['tests_completed'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/dashboard.tests_completed') }}</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-card">
                            <div class="kpi-value" style="color: #3b82f6;">{{ $stats['summary']['sla_compliance_rate'] }}%</div>
                            <div class="kpi-label">{{ __('mas/lab.sla_compliance_rate') }}</div>
                        </div>
                    </td>

                </tr>
            </table>
        </div>

        <!-- Distribution & Trends -->
        <div class="report-section">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 48%; vertical-align: top; padding-right: 2%;">
                        <div class="section-title">{{ __('mas/dashboard.aging_distribution') }}</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Status Bucket</th>
                                    <th class="text-right">Batches</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['aging_buckets'] ?? [] as $bucket)
                                <tr>
                                    <td>{{ $bucket['label'] }}</td>
                                    <td class="text-right font-bold">{{ $bucket['batch_count'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                    <td style="width: 48%; vertical-align: top;">
                        <div class="section-title">Top Analyte Throughput</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Analyte Module</th>
                                    <th class="text-right">Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($stats['charts']['throughput_labels'] ?? [], 0, 8) as $index => $label)
                                <tr>
                                    <td class="font-bold">{{ $label }}</td>
                                    <td class="text-right">{{ number_format($stats['charts']['throughput_counts'][$index] ?? 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Captured Visuals -->
        @if($chartImage)
        <div class="report-section">
            <div class="section-title">Analytical Visuals</div>
            <div class="chart-container">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
            <div class="text-center" style="font-size: 8px; color: #94a3b8; margin-top: 5px;">* Live data snapshot captured at time of export.</div>
        </div>
        @endif

        <!-- Performance Leaderboard & Workload Distribution -->
        <div class="report-section">
            <div class="section-title">Laboratory Performance & Workload Distribution</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">{{ __('mas/lab.lab_section') }}</th>
                        <th class="text-center">Workload</th>
                        <th class="text-center">Share %</th>
                        <th class="text-center">Avg. Completion</th>
                        <th class="text-center">Operational Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalWorkload = collect($stats['sections']['leaderboard'] ?? [])->sum('total') ?: 1;
                    @endphp
                    @foreach($stats['sections']['leaderboard'] ?? [] as $section)
                    <tr>
                        <td class="font-bold border-left" style="border-left: 3px solid #3b82f6 !important; padding-left: 10px;">
                            {{ $section['name'] }}
                            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">SEC-ID: {{ $section['code'] }}</div>
                        </td>
                        <td class="text-center font-bold">{{ $section['total'] }} items</td>
                        <td class="text-center font-bold" style="color: #3b82f6;">
                            {{ round(($section['total'] / $totalWorkload) * 100) }}%
                        </td>
                        <td class="text-center">
                            @if($section['avg_tat'] > 0)
                                {{ $section['avg_tat'] }} <span style="font-size: 8px; color: #94a3b8;">DAYS</span>
                            @else
                                <span style="color: #94a3b8;">N/A (Pending)</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($section['overdue'] > 0)
                                <span class="badge badge-danger">{{ $section['overdue'] }} {{ __('mas/lab.overdue') }}</span>
                            @else
                                <span class="badge badge-success">Optimized</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Historical Trends -->
        <div class="report-section">
            <div class="section-title">{{ __('mas/lab.historical_section_tat') }}</div>
            <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="font-size: 8px;">Section</th>
                                    @foreach($stats['sections']['trends']['labels'] ?? [] as $label)
                                        <th class="text-center" style="font-size: 8px;">{{ explode(' ', $label)[0] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['sections']['trends']['series'] ?? [] as $series)
                                <tr>
                                    <td class="font-bold" style="font-size: 9px;">{{ $series['name'] }}</td>
                                    @foreach($series['data'] as $val)
                                        <td class="text-center">{{ $val }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Analyst Performance -->
        <div class="report-section">
            <div class="section-title">{{ __('mas/lab.analyst_performance') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">{{ __('mas/lab.analyst') }}</th>
                        <th class="text-center">Tests Completed</th>
                        <th class="text-center">Avg. Offset</th>
                        <th class="text-center">SLA Compliance</th>
                        <th class="text-center">Efficiency Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['analyst_performance'] ?? [] as $analyst)
                    <tr>
                        <td class="font-bold border-left" style="border-left: 3px solid #3b82f6 !important; padding-left: 10px;">
                            {{ $analyst['name'] }}
                        </td>
                        <td class="text-center">{{ $analyst['total_tests'] }}</td>
                        <td class="text-center font-bold" style="color: {{ $analyst['avg_offset'] <= 0 ? '#166534' : '#991b1b' }}">
                            {{ $analyst['avg_offset'] <= 0 ? '-' : '+' }}{{ abs($analyst['avg_offset']) }}d
                        </td>
                        <td class="text-center font-bold" style="color: {{ $analyst['on_time_rate'] >= 90 ? '#166534' : ($analyst['on_time_rate'] >= 75 ? '#854d0e' : '#991b1b') }};">
                            {{ $analyst['on_time_rate'] }}%
                        </td>
                        <td class="text-center">
                            @php
                                $label = $analyst['performance_label'];
                                $badgeClass = match($label) {
                                    'Excellent' => 'badge-success',
                                    'On Track' => 'badge-primary',
                                    'Behind Schedule' => 'badge-warning',
                                    'Critical Delay' => 'badge-danger',
                                    default => 'badge-primary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted small">{{ __('mas/common.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Critical Watchlist -->
        <div class="report-section">
            <div class="section-title">{{ __('mas/lab.cockpit_title') }}</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Batch Reference</th>
                        <th>Client Entity</th>
                        <th>Sample Category</th>
                        <th class="text-center">Risk Multiplier</th>
                        <th>Workflow Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_slice($stats['smart_grid'] ?? [], 0, 10) as $item)
                    <tr>
                        <td class="font-bold" style="color: #1e40af;">{{ $item['batch_code'] }}</td>
                        <td style="font-size: 10px;">{{ $item['client'] }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td class="text-center">
                            @if(($item['priority'] ?? '') == 'Urgent')
                                <span class="badge badge-danger">High Risk</span>
                            @else
                                <span class="badge badge-primary">Standard</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 10px; color: #64748b;">{{ $item['status'] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted small">No critical alerts detected in this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

        <!-- Detailed Appendix (New Page) -->
        <div style="page-break-before: always;">
            <div class="section-title">Appendix: Detailed Analyte TAT Performance</div>
            <p style="font-size: 9px; color: #64748b; margin-bottom: 15px;">
                This appendix provides a granular breakdown of individual tests completed during the period. 
                Negative (-) offsets indicate early completion, while positive (+) offsets indicate SLA breaches.
            </p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Analyte</th>
                        <th>Sample Code</th>
                        <th>Expected</th>
                        <th>Actual</th>
                        <th class="text-center">Offset</th>
                        <th>Analyst</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['detailed_logs'] ?? [] as $log)
                    <tr>
                        <td class="font-bold" style="font-size: 9px; color: #1e40af;">{{ $log['analyte'] }}</td>
                        <td style="font-size: 9px;">{{ $log['sample_code'] }}</td>
                        <td style="font-size: 9px; color: #64748b;">{{ $log['expected_date'] }}</td>
                        <td style="font-size: 9px;">{{ $log['actual_date'] }}</td>
                        <td class="text-center">
                            @if($log['offset'] <= 0)
                                <span style="color: #166534; font-weight: bold;">-{{ abs($log['offset']) }}d</span>
                            @else
                                <span style="color: #991b1b; font-weight: bold;">+{{ $log['offset'] }}d</span>
                            @endif
                        </td>
                        <td style="font-size: 9px; font-weight: bold;">{{ $log['analyst'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted small">No detailed records available for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top: 10px; font-size: 8px; color: #94a3b8; text-align: center;">
                * Appendix records are capped at the top 100 entries for readability.
            </div>
        </div>
    </div>
</body>


</html>
