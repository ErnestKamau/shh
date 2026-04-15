<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laboratory Insights Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 20px; }
        .container { padding: 0 40px; }
        .section-title { border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; page-break-inside: avoid; }
        .kpi-row { margin-bottom: 20px; width: 100%; page-break-inside: avoid; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; padding: 12px; text-align: center; width: 31%; display: inline-block; margin-right: 1.5%; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #2563eb; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 3px; font-weight: bold; }
        .chart-container { text-align: center; margin-bottom: 20px; padding: 15px; border: 1px solid #f1f5f9; border-radius: 6px; background-color: #fcfcfc; }
        .chart-img { width: 100%; height: 350px; object-fit: contain; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background-color: #f8fafc; padding: 10px 8px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; color: #475569; text-transform: uppercase; }
        .data-table td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .data-table tr:nth-child(even) { background-color: #fafbfc; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        .text-success { color: #16a34a; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .page-break { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 30px; border-bottom: 2px solid #0f172a; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="font-size: 26px; font-weight: bold; color: #0f172a; letter-spacing: -1.5px;">
                            NUVEMITE <span style="color: #2563eb;">POLUCON</span>
                        </div>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">Laboratory Intelligence Command Center</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-bottom: 2px;">TAT Performance Report</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-TAT | Printed: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #2563eb; font-weight: bold; margin-top: 2px;">Classification: CONFIDENTIAL</div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="section-title">Laboratory Performance Metrics</div>
        
        <table style="width: 100%; border-collapse: separate; border-spacing: 12px 0; margin-left: -12px; margin-bottom: 20px;">
            <tr>
                <td style="width: 25%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0;">
                        <div class="kpi-value">{{ $stats['summary']['active_batches'] ?? 0 }}</div>
                        <div class="kpi-label">Active Batches</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0;">
                        <div class="kpi-value text-danger">{{ $stats['summary']['overdue_batches'] ?? 0 }}</div>
                        <div class="kpi-label">Batch Overdue</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0;">
                        <div class="kpi-value" style="color: #4f46e5;">{{ $stats['summary']['tests_completed'] ?? 0 }}</div>
                        <div class="kpi-label">Tests Completed</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box" style="width: 100%; margin-right: 0;">
                        <div class="kpi-value" style="color: #6366f1;">{{ round((($stats['summary']['tests_completed'] ?? 0) / ($stats['summary']['tests_requested'] ?? 1)) * 100) }}%</div>
                        <div class="kpi-label">Test Progress</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="avoid-break" style="width: 100%; margin-bottom: 30px;">
            <div style="width: 48%; display: inline-block; vertical-align: top; margin-right: 2%;">
                <div class="section-title">Aging Distribution (Batches)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Time Bucket</th>
                            <th style="text-align: right;">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['aging_buckets'] ?? [] as $bucket)
                        <tr>
                            <td>{{ $bucket['label'] }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $bucket['batch_count'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="width: 48%; display: inline-block; vertical-align: top;">
                <div class="section-title">Throughput: {{ $stats['period_label'] }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Top Analytes (Tests)</th>
                            <th style="text-align: right;">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($stats['charts']['throughput_labels'] ?? [], 0, 5) as $index => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $stats['charts']['throughput_counts'][$index] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($chartImage)
        <div class="avoid-break">
            <div class="section-title">Operational Health Snapshot (Workflow)</div>
            <div class="chart-container" style="background-color: #ffffff;">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
        </div>
        @endif

        <div class="page-break"></div>

        <div class="section-title">Critical Overdue Watchlist</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Workflow Stage</th>
                    <th style="text-align: center;">Target Date</th>
                    <th style="text-align: right;">Days Overdue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats['overdue_batches'] ?? [] as $batch)
                <tr>
                    <td class="font-weight-bold" style="color: #2563eb;">{{ $batch['batch_code'] }}</td>
                    <td>{{ $batch['workflow_stage'] }}</td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($batch['target_date'])->format('Y-m-d') }}</td>
                    <td style="text-align: right; color: #dc2626; font-weight: bold;">{{ $batch['days_overdue'] }}d</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No critical overdue batches detected.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Confidential Document | AI Analytics Laboratory TAT Report | Nuvemite Polucon
    </div>
</body>
</html>
