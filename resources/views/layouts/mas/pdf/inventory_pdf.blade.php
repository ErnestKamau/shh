<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/inventory.title') }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 30px; }
        .container { padding: 0 40px; }
        .section-title { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #dc2626; text-transform: uppercase; }
        .report-section { page-break-inside: avoid; margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #dc2626; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 5px; font-weight: bold; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #f8fafc; padding: 8px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; color: #475569; }
        .data-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-success { background-color: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 35px; border-bottom: 2px solid #dc2626; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <img src="{{ public_path('assets/branding/logo.jpeg') }}" style="height: 60px; width: auto;">
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">{{ __('mas/dashboard.command_center') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #dc2626; text-transform: uppercase; margin-bottom: 2px;">{{ __('mas/inventory.title') }}</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-INV | {{ __('mas/report.generated') }}: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #dc2626; font-weight: bold; margin-top: 2px;">{{ __('mas/report.classification') }}: {{ __('mas/dashboard.operational') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">{{ __('mas/inventory.high_risk_analysis') }}</div>
            <table style="width: 100%; border-collapse: separate; border-spacing: 15px 0; margin-left: -15px; margin-bottom: 0;">
                <tr>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['summary']['tracked_items'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/inventory.tracked_items') }}</div>
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value" style="color: #f59e0b;">{{ $stats['summary']['items_near_expiry'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/inventory.near_expiry') }}</div>
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ $stats['summary']['items_below_minimum'] ?? 0 }}</div>
                            <div class="kpi-label">{{ __('mas/inventory.below_minimum') }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">Critical Attention Items</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('mas/inventory.item_name') }}</th>
                        <th>{{ __('mas/inventory.store') }}</th>
                        <th style="text-align: right;">{{ __('mas/inventory.available') }}</th>
                        <th style="text-align: right;">{{ __('mas/inventory.min_level') }}</th>
                        <th style="text-align: center;">{{ __('mas/inventory.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['priority_items'] as $item)
                    <tr>
                        <td>
                            <div style="font-weight: bold;">{{ $item['item_name'] }}</div>
                            <div style="font-size: 8px; color: #64748b;">{{ $item['item_code'] }}</div>
                        </td>
                        <td>{{ $item['store_name'] }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($item['available_qty'], 1) }}</td>
                        <td style="text-align: right;">{{ number_format($item['minimum_level'], 1) }}</td>
                        <td style="text-align: center;">
                            @if($item['available_qty'] < $item['minimum_level'])
                                <span class="badge badge-danger">{{ __('mas/inventory.critical') }}</span>
                            @elseif($item['near_expiry_qty'] > 0)
                                <span class="badge badge-warning">{{ __('mas/inventory.expiry_risk') }}</span>
                            @else
                                <span class="badge badge-success">{{ __('mas/inventory.healthy') }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        {{ __('mas/report.confidential') }} | {{ __('mas/inventory.title') }} | {{ __('mas/common.brand_footer') }}
    </div>
</body>
</html>
