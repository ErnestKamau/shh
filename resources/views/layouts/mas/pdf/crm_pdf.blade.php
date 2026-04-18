<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/crm.title') }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 30px; }
        .container { padding: 0 40px; }
        .section-title { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #007bff; text-transform: uppercase; }
        .report-section { page-break-inside: avoid; margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #007bff; }
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
        <div class="header" style="margin-top: 35px; border-bottom: 2px solid #007bff; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <img src="{{ public_path('assets/branding/logo.jpeg') }}" style="height: 60px; width: auto;">
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">{{ __('mas/dashboard.command_center') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #007bff; text-transform: uppercase; margin-bottom: 2px;">{{ __('mas/crm.title') }}</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-CRM | {{ __('mas/report.generated') }}: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #007bff; font-weight: bold; margin-top: 2px;">{{ __('mas/report.classification') }}: {{ __('mas/dashboard.operational') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">{{ __('mas/dashboard.summary') }}</div>
            <table style="width: 100%; border-collapse: separate; border-spacing: 15px 0; margin-left: -15px; margin-bottom: 0;">
                <tr>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['total_clients'] ?? 0 }}</div>
                            <div class="kpi-label">Total Clients</div>
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['recent_orders'] ?? 0 }}</div>
                            <div class="kpi-label">Recent Orders (30d)</div>
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['unpaid_invoices'] ?? 0 }}</div>
                            <div class="kpi-label">Unpaid Invoices</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if($chartImage)
        <div class="report-section">
            <div class="section-title">Visual Analytics</div>
            <div class="chart-container">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
        </div>
        @endif

        <div class="report-section">
            <div class="section-title">{{ __('mas/crm.top_accounts') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Rank</th>
                        <th>{{ __('mas/crm.client_name') }}</th>
                        <th style="text-align: right;">{{ __('mas/crm.total_batches') }}</th>
                        <th style="text-align: right;">Workload Share</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalBatches = collect($stats['top_clients'])->sum('total') ?: 1; @endphp
                    @foreach($stats['top_clients'] as $index => $client)
                    <tr>
                        <td>#{{ $index + 1 }}</td>
                        <td style="font-weight: bold;">{{ $client->name }}</td>
                        <td style="text-align: right;">{{ $client->total }}</td>
                        <td style="text-align: right;">{{ round(($client->total / $totalBatches) * 100, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        {{ __('mas/report.confidential') }} | {{ __('mas/crm.title') }} | {{ __('mas/common.brand_footer') }}
    </div>
</body>
</html>
