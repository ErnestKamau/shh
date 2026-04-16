<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/ai.title') }}</title>
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
        <h1>{{ strtoupper(__('mas/ai.title')) }}</h1>
        <p>{{ __('mas/report.system_state') }}: {{ ($stats['performance']['status'] ?? 'healthy') === 'healthy' ? __('mas/ai.active') : __('mas/ai.inactive') }} | {{ __('mas/report.generated') }}: {{ date('F d, Y') }}</p>
    </div>

    <div class="container">
        <div class="section-title">{{ __('mas/ai.health_score') }}</div>
        
        <div class="kpi-row">
            <div class="kpi-box">
                <div class="kpi-value">{{ $stats['performance']['kpis']['health_score'] ?? 100 }}%</div>
                <div class="kpi-label">{{ __('mas/ai.health_score') }}</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-value">{{ $stats['performance']['kpis']['uptime_percent'] ?? 100 }}%</div>
                <div class="kpi-label">{{ __('mas/ai.success_rate') }}</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-value text-info">{{ $stats['performance']['kpis']['routing_accuracy_percent'] ?? 100 }}%</div>
                <div class="kpi-label">{{ __('mas/ai.routing_accuracy') }}</div>
            </div>
            <div class="kpi-box" style="margin-right: 0;">
                <div class="kpi-value">{{ $stats['performance']['kpis']['quota_used_percent'] ?? 0 }}%</div>
                <div class="kpi-label">{{ __('mas/ai.quota_usage') }}</div>
            </div>
        </div>

        @if($chartImage)
        <div class="section-title">{{ __('mas/ai.model_performance') }}</div>
        <div class="chart-container">
            <img src="{{ $chartImage }}" class="chart-img" />
        </div>
        @endif

        <div class="section-title">{{ __('mas/ai.model_registry') }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('mas/ai.name') }}</th>
                    <th>{{ __('mas/ai.type') }}</th>
                    <th>{{ __('mas/ai.version') }}</th>
                    <th>{{ __('mas/ai.framework') }}</th>
                    <th>{{ __('mas/ai.status') }}</th>
                    <th style="text-align: right;">{{ __('mas/ai.last_deployment') }}</th>
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
                        <span class="status-healthy">{{ !empty($model['is_active']) ? __('mas/ai.active') : __('mas/ai.inactive') }}</span>
                    </td>
                    <td style="text-align: right;">{{ $model['deployed_at'] ?? '2026-04-10' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 40px;">
            <div class="section-title">{{ __('mas/ai.lims_predictive') }}</div>
            <p style="font-size: 11px;">{{ __('mas/ai.predictive_subtitle') }}</p>
            <ul style="font-size: 11px;">
                <li>{{ __('mas/ai.predictive_sla') }}</li>
                <li>{{ __('mas/ai.predictive_inventory') }}</li>
                <li>{{ __('mas/ai.predictive_qc') }}</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        {{ __('mas/report.confidential') }} {{ __('mas/report.governance_document') }} | Nuvemite Polucon | {{ __('mas/report.page') }} 1 {{ __('mas/report.of') }} 1
    </div>
</body>
</html>
