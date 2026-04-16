<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/logistics.report_title') }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { margin-bottom: 30px; }
        .container { padding: 0 40px; }
        .section-title { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #0891b2; text-transform: uppercase; }
        .report-section { page-break-inside: avoid; margin-bottom: 30px; width: 100%; }
        .kpi-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #06b6d4; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-top: 5px; font-weight: bold; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #f8fafc; padding: 8px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #e2e8f0; color: #475569; }
        .data-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        .text-danger { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 35px; border-bottom: 2px solid #0891b2; padding-bottom: 15px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="font-size: 26px; font-weight: bold; color: #0891b2; letter-spacing: -1.5px;">
                            NUVEMITE <span style="color: #06b6d4;">POLUCON</span>
                        </div>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; font-weight: bold; text-transform: uppercase;">{{ __('mas/dashboard.command_center') }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: bold; color: #0891b2; text-transform: uppercase; margin-bottom: 2px;">{{ __('mas/logistics.audit_title') }}</div>
                        <div style="font-size: 9px; color: #64748b;">Ref: #{{ date('Ymd') }}-LOG | {{ __('mas/report.generated') }}: {{ date('F d, Y H:i') }}</div>
                        <div style="font-size: 9px; color: #06b6d4; font-weight: bold; margin-top: 2px;">{{ __('mas/dashboard.classification') }}: {{ __('mas/dashboard.operational') }}</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="report-section">
            <div class="section-title">{{ __('mas/logistics.health_scorecard') }}</div>
            <table style="width: 100%; border-collapse: separate; border-spacing: 12px 0; margin-left: -12px; margin-bottom: 0;">
                <tr>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value">{{ count($stats['buffers'] ?? []) }}</div>
                            <div class="kpi-label">{{ __('mas/logistics.active_buffers') }}</div>
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value text-danger">{{ collect($stats['buffers'] ?? [])->where('is_low', true)->count() }}</div>
                            <div class="kpi-label">{{ __('mas/logistics.critically_low') }}</div>
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="kpi-box">
                            <div class="kpi-value" style="color: #6366f1;">{{ count($stats['movements'] ?? []) }}</div>
                            <div class="kpi-label">{{ __('mas/logistics.monthly_movements') }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title">{{ __('mas/logistics.status_title') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('mas/logistics.item_description') }}</th>
                        <th>{{ __('mas/logistics.batch_code') }}</th>
                        <th style="text-align: right;">{{ __('mas/logistics.min_level') }}</th>
                        <th style="text-align: right;">{{ __('mas/logistics.current_qty') }}</th>
                        <th style="text-align: center;">{{ __('mas/common.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['buffers'] ?? [] as $buffer)
                    <tr>
                        <td class="font-weight-bold">{{ $buffer['name'] }}</td>
                        <td>{{ $buffer['code'] }}</td>
                        <td style="text-align: right;">{{ number_format($buffer['min_level'], 1) }}</td>
                        <td style="text-align: right;">{{ number_format($buffer['current_qty'], 1) }}</td>
                        <td style="text-align: center;">
                            @if($buffer['is_low'])
                                <span class="text-danger">{{ __('mas/logistics.low_stock') }}</span>
                            @else
                                <span style="color: #16a34a;">{{ __('mas/logistics.optimal') }}</span>
                            @endif
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

        <div class="report-section">
            <div class="section-title">{{ __('mas/logistics.movements_30d') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('mas/logistics.timestamp') }}</th>
                        <th>{{ __('mas/logistics.buffer_consumable') }}</th>
                        <th>{{ __('mas/common.action') }}</th>
                        <th style="text-align: right;">{{ __('mas/common.quantity') }}</th>
                        <th>{{ __('mas/logistics.operator') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_slice($stats['movements'] ?? [], 0, 15) as $move)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($move['created_at'])->format('Y-m-d H:i') }}</td>
                        <td class="font-weight-bold">{{ $move['buffer_name'] }}</td>
                        <td>{{ strtoupper($move['action_type']) }}</td>
                        <td style="text-align: right;">{{ number_format($move['quantity'], 1) }}</td>
                        <td>{{ $move['operator'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">{{ __('mas/common.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-section">
            <div class="section-title" style="color: #6366f1;">{{ __('mas/logistics.ai_insights') }}</div>
            <div style="background-color: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 15px;">
                <p style="font-size: 11px; color: #4338ca; margin: 0;">
                    <strong>{{ __('mas/logistics.consumable_pulse') }}:</strong> 
                    @php $lowCount = collect($stats['buffers'] ?? [])->where('is_low', true)->count(); @endphp
                    @if($lowCount > 0)
                        {{ __('mas/logistics.critical_depletion', ['count' => $lowCount]) }}
                    @else
                        {{ __('mas/logistics.stable_supply') }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="footer">
        {{ __('mas/report.confidential') }} | {{ __('mas/logistics.monitoring') }} | Nuvemite Polucon
    </div>
</body>
</html>
