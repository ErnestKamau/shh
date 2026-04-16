<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/dashboard.insights_report') }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 20px; }
        .container { padding: 0 40px; }
        .section-title { border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .report-section { page-break-inside: avoid; margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; padding: 12px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #2563eb; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 3px; font-weight: bold; }
        .chart-container { text-align: center; margin-bottom: 20px; padding: 15px; border: 1px solid #f1f5f9; border-radius: 6px; background-color: #ffffff; }
        .chart-img { width: 100%; height: 320px; object-fit: contain; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #f8fafc; padding: 10px 8px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; color: #475569; text-transform: uppercase; }
        .data-table td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .data-table tr:nth-child(even) { background-color: #fafbfc; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        .text-success { color: #16a34a; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 35px; border-bottom: 2px solid #0f172a; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="font-size: 26px; font-weight: bold; color: #0f172a; letter-spacing: -1.5px;">
                            NUVEMITE <span style="color: #2563eb;">POLUCON</span>
                        </div>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">{{ __('mas/dashboard.command_center') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-bottom: 2px;">{{ __('mas/dashboard.tat_performance') }} {{ __('mas/common.report') }}</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-TAT | {{ __('mas/report.generated') }}: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #2563eb; font-weight: bold; margin-top: 2px;">{{ __('mas/dashboard.classification') }}: {{ __('mas/dashboard.operational') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">{{ __('mas/dashboard.lab_analytics') }}</div>
            <table style="width: 100%; border-collapse: separate; border-spacing: 12px 0; margin-left: -12px; margin-bottom: 0;">
                <tr>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['summary']['active_batches'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/dashboard.active_batches') }}</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value text-danger">{{ $stats['summary']['overdue_batches'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/dashboard.batch_overdue') }}</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value" style="color: #4f46e5;">{{ $stats['summary']['tests_completed'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/dashboard.tests_completed') }}</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value" style="color: #6366f1;">{{ round((($stats['summary']['tests_completed'] ?? 0) / ($stats['summary']['tests_requested'] ?? 1)) * 100) }}%</div>
                            <div class="kpi-label">{{ __('mas/dashboard.test_progress') }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div style="width: 48%; display: inline-block; vertical-align: top; margin-right: 2%;">
                <div class="section-title">{{ __('mas/dashboard.aging_distribution') }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('mas/common.period') }}</th>
                            <th style="text-align: right;">{{ __('mas/common.count') }}</th>
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
                <div class="section-title">{{ __('mas/common.throughput') }}: {{ $stats['period_label'] }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('mas/common.analyte') }}</th>
                            <th style="text-align: right;">{{ __('mas/common.volume') }}</th>
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
        <div class="report-section">
            <div class="section-title">{{ __('mas/dashboard.operational_health') }}</div>
            <div class="chart-container">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
        </div>
        @endif

        <div class="report-section">
            <div class="section-title">{{ __('mas/dashboard.operational_watchlist') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('mas/common.batch') }}</th>
                        <th>{{ __('mas/common.client') }}</th>
                        <th>{{ __('mas/common.type') }}</th>
                        <th>{{ __('mas/common.status') }}</th>
                        <th style="text-align: center;">{{ __('mas/common.priority') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['smart_grid'] ?? [] as $item)
                    <tr>
                        <td class="font-weight-bold" style="color: #2563eb;">{{ $item['batch_code'] }}</td>
                        <td>{{ $item['client'] }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td style="text-align: center;">
                            <span class="{{ $item['priority'] == 'Urgent' ? 'text-danger' : 'text-success' }}">
                                {{ $item['priority'] == 'Urgent' ? __('mas/common.urgent') : __('mas/common.normal') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">{{ __('mas/common.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        {{ __('mas/report.confidential') }} | {{ __('mas/dashboard.insights_report') }} | Nuvemite Polucon
    </div>
</body>
</html>
