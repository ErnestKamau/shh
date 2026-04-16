<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/dashboard.general_report') }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 30px; }
        .container { padding: 0 40px; }
        .section-title { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; }
        .report-section { page-break-inside: avoid; margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #3b82f6; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 5px; font-weight: bold; }
        .chart-container { text-align: center; margin-bottom: 20px; padding: 10px; border: 1px solid #f1f5f9; border-radius: 6px; background-color: #ffffff; }
        .chart-img { max-width: 100%; height: 300px; object-fit: contain; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #f8fafc; padding: 8px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; color: #475569; }
        .data-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 35px; border-bottom: 2px solid #1e3a8a; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="font-size: 26px; font-weight: bold; color: #1e3a8a; letter-spacing: -1.5px;">
                            NUVEMITE <span style="color: #3b82f6;">POLUCON</span>
                        </div>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">{{ __('mas/dashboard.command_center') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin-bottom: 2px;">{{ __('mas/dashboard.lab_analytics') }}</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-GEN | {{ __('mas/report.generated') }}: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #3b82f6; font-weight: bold; margin-top: 2px;">{{ __('mas/report.classification') }}: {{ __('mas/dashboard.operational') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">{{ __('mas/dashboard.operations_summary') }}</div>
            <table style="width: 100%; border-collapse: separate; border-spacing: 15px 0; margin-left: -15px; margin-bottom: 0;">
                <tr>
                    <td style="width: 50%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['summary']['active_batches'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/dashboard.active_workload') }}</div>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ count($stats['top_clients']) }}</div>
                            <div class="kpi-label">{{ __('mas/dashboard.key_clients') }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if($chartImage)
        <div class="report-section">
            <div class="section-title">{{ __('mas/dashboard.workload_distribution') }}</div>
            <div class="chart-container">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
        </div>
        @endif

        <div class="report-section">
            <div style="width: 48%; display: inline-block; vertical-align: top; margin-right: 2%;">
                <div class="section-title">{{ __('mas/dashboard.registration_trends') }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('mas/common.month') }}</th>
                            <th style="text-align: right;">{{ __('mas/common.volume') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_reverse($stats['monthly_trends']['labels'] ?? []) as $index => $label)
                        @php $actualIndex = count($stats['monthly_trends']['labels']) - 1 - $index; @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $stats['monthly_trends']['data'][$actualIndex] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="width: 48%; display: inline-block; vertical-align: top;">
                <div class="section-title">{{ __('mas/dashboard.geographic_activity') }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('mas/dashboard.density_cluster') }}</th>
                            <th style="text-align: right;">{{ __('mas/dashboard.weight') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(array_slice($stats['geographic_data'] ?? [], 0, 5) as $point)
                        <tr>
                            <td>Point ({{ number_format($point['lat'], 3) }}, {{ number_format($point['lng'], 3) }})</td>
                            <td style="text-align: right; font-weight: bold;">{{ $point['intensity'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" style="text-align: center;">{{ __('mas/common.no_data') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section">
            <div class="section-title">{{ __('mas/dashboard.top_clients_share') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">{{ __('mas/dashboard.rank') }}</th>
                        <th>{{ __('mas/common.client') }}</th>
                        <th style="text-align: right;">{{ __('mas/common.batch') }} {{ __('mas/common.count') }}</th>
                        <th style="text-align: right;">{{ __('mas/dashboard.workload_percent') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalWorkload = collect($stats['top_clients'])->sum('total') ?: 1; @endphp
                    @foreach($stats['top_clients'] as $index => $client)
                    <tr>
                        <td>#{{ $index + 1 }}</td>
                        <td style="font-weight: bold;">{{ $client->name }}</td>
                        <td style="text-align: right;">{{ $client->total }}</td>
                        <td style="text-align: right;">{{ round(($client->total / $totalWorkload) * 100, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        {{ __('mas/report.confidential') }} | {{ __('mas/dashboard.general_report') }} | Nuvemite Polucon
    </div>
</body>
</html>
