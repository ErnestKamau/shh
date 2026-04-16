<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/qc.report_title') }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 30px; }
        .container { padding: 0 40px; }
        .section-title { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #4338ca; text-transform: uppercase; }
        .report-section { page-break-inside: avoid; margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 22px; font-weight: bold; color: #6366f1; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 5px; font-weight: bold; }
        .chart-container { text-align: center; margin-bottom: 20px; padding: 10px; border: 1px solid #f1f5f9; border-radius: 6px; background-color: #ffffff; }
        .chart-img { max-width: 100%; height: 320px; object-fit: contain; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
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
        <div class="header" style="margin-top: 35px; border-bottom: 2px solid #4338ca; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="font-size: 26px; font-weight: bold; color: #4338ca; letter-spacing: -1.5px;">
                            NUVEMITE <span style="color: #6366f1;">POLUCON</span>
                        </div>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">{{ __('mas/dashboard.command_center') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #4338ca; text-transform: uppercase; margin-bottom: 2px;">{{ __('mas/qc.title') }}</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-QC | {{ __('mas/report.generated') }}: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #6366f1; font-weight: bold; margin-top: 2px;">{{ __('mas/dashboard.classification') }}: {{ __('mas/qc.compliance') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">{{ __('mas/qc.summary_scorecard') }}</div>
            <table style="width: 100%; border-collapse: separate; border-spacing: 12px 0; margin-left: -12px; margin-bottom: 0;">
                <tr>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['summary']['total_records'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/qc.analytes') }}</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value text-success">{{ $stats['summary']['stable_records'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/qc.stable') }}</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value text-danger">{{ $stats['summary']['critical_records'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/qc.critical') }}</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['summary']['avg_cv_percentage'] ?? 0 }}%</div>
                            <div class="kpi-label">{{ __('mas/qc.avg_cv_pct') }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if($chartImage)
        <div class="report-section">
            <div class="section-title">{{ __('mas/qc.snapshot_title') }}</div>
            <div class="chart-container">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
        </div>
        @endif

        <div class="report-section">
            <div style="width: 58%; display: inline-block; vertical-align: top; margin-right: 2%;">
                <div class="section-title">{{ __('mas/qc.leaderboard_title') }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('mas/qc.method_analyte') }}</th>
                            <th style="text-align: right;">{{ __('mas/qc.total_tests') }}</th>
                            <th style="text-align: right;">{{ __('mas/qc.pass_rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['parameter_performance'] ?? [] as $perf)
                        <tr>
                            <td class="font-weight-bold">{{ $perf['name'] }}</td>
                            <td style="text-align: right;">{{ number_format($perf['total']) }}</td>
                            <td style="text-align: right;">
                                <span class="{{ $perf['rate'] > 95 ? 'text-success' : ($perf['rate'] > 85 ? 'text-warning' : 'text-danger') }}">
                                    {{ $perf['rate'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="width: 38%; display: inline-block; vertical-align: top;">
                <div class="section-title">{{ __('mas/qc.workload_matrix') }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('mas/common.type') }}</th>
                            <th style="text-align: right;">{{ __('mas/qc.share') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalMatrix = collect($stats['testing_matrix'])->sum(function($t) { return collect($t['children'])->sum('value'); }) ?: 1; @endphp
                        @foreach(array_slice($stats['testing_matrix'] ?? [], 0, 8) as $type)
                        @php $typeSum = collect($type['children'])->sum('value'); @endphp
                        <tr>
                            <td>{{ $type['name'] }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ round(($typeSum / $totalMatrix) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section">
            <div class="section-title">{{ __('mas/qc.watchlist_title') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('mas/common.analyte') }}</th>
                        <th>{{ __('mas/common.type') }}</th>
                        <th style="text-align: right;">{{ __('mas/qc.robust_cv') }}</th>
                        <th style="text-align: right;">{{ __('mas/qc.pass_rate') }}</th>
                        <th style="text-align: center;">{{ __('mas/common.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($stats['exception_rows'] ?? [], 0, 12) as $row)
                    <tr>
                        <td class="font-weight-bold">{{ $row['analyte_name'] }}</td>
                        <td>{{ $row['sample_type_name'] }}</td>
                        <td style="text-align: right;">{{ number_format($row['robust_cv_percentage'], 2) }}%</td>
                        <td style="text-align: right;">{{ number_format($row['pass_rate_pct'], 1) }}%</td>
                        <td style="text-align: center;">
                            <span class="{{ $row['stability_status'] == 'stable' ? 'text-success' : ($row['stability_status'] == 'warning' ? 'text-warning' : 'text-danger') }}">
                                {{ strtoupper(__('mas/qc.' . $row['stability_status'])) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        {{ __('mas/report.confidential') }} | {{ __('mas/qc.oversight') }} | Nuvemite Polucon
    </div>
</body>
</html>
