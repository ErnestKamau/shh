@extends('layouts.mas.layout.app')

@section('title2')
<title>AI Intelligence | MaS</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<style>
    .ai-hero-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        position: relative;
        overflow: hidden;
    }
    .ai-hero-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, transparent 70%);
        filter: blur(40px);
    }
    .kpi-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e2e8f0;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
    }
    .status-healthy { background: #dcfce7; color: #166534; }
    .status-degraded { background: #fef9c3; color: #854d0e; }
    .status-unhealthy { background: #fee2e2; color: #991b1b; }
</style>
@endsection

@section('content2')
<div class="container-fluid">
    {{-- Hero Section --}}
    <div class="ai-hero-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold mb-2">{{ __('mas/ai.title') }}</h1>
                <p class="lead text-gray-400">{{ __('mas/ai.subtitle') }}</p>
                <div class="d-flex gap-4 mt-4 align-items-center">
                    <button onclick="exportAiPdf()" class="btn btn-danger btn-sm mr-2">
                        <i class="mdi mdi-file-pdf"></i> {{ __('mas/ai.download_pdf_report') }}
                    </button>
                    
                    <form id="aiPdfExportForm" action="{{ route('mas.export.visuals', 'ai') }}" method="POST" style="display:none">
                        @csrf
                        <input type="hidden" name="chart_image" id="ai_chart_image_input">
                    </form>
                    <div>
                        <span class="text-xs text-uppercase tracking-wider text-gray-500">{{ __('mas/ai.system_status') }}</span>
                        <div class="d-flex align-items-center mt-1">
                            <span class="status-badge {{ ($stats['performance']['status'] ?? 'healthy') === 'healthy' ? 'status-healthy' : (($stats['performance']['status'] ?? '') === 'degraded' ? 'status-degraded' : 'status-unhealthy') }}">
                                {{ strtoupper($stats['performance']['status'] ?? 'HEALTHY') }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <span class="text-xs text-uppercase tracking-wider text-gray-500">{{ __('mas/ai.health_score') }}</span>
                        <div class="h3 font-weight-bold text-info mt-1">{{ $stats['performance']['kpis']['health_score'] ?? 100 }}%</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-right d-none d-md-block">
                <i class="mdi mdi-brain fa-5x text-info opacity-50" style="font-size: 8rem; opacity: 0.2"></i>
            </div>
        </div>
    </div>

    {{-- Operational KPIs --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="kpi-card text-center">
                <div class="text-gray-500 text-xs text-uppercase font-weight-bold mb-2">{{ __('mas/ai.sla_compliance') }}</div>
                <div class="h2 font-weight-bold {{ ($stats['performance']['kpis']['sla_compliant'] ?? true) ? 'text-success' : 'text-danger' }}">
                    {{ ($stats['performance']['kpis']['sla_compliant'] ?? true) ? __('mas/ai.on_time') : __('mas/ai.delayed') }}
                </div>
                <div class="text-xs text-gray-400">{{ __('mas/common.daily_check') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card text-center">
                <div class="text-gray-500 text-xs text-uppercase font-weight-bold mb-2">{{ __('mas/ai.success_rate') }}</div>
                <div class="h2 font-weight-bold text-primary">{{ $stats['performance']['kpis']['uptime_percent'] ?? 100 }}%</div>
                <div class="text-xs text-gray-400">{{ __('mas/ai.model_performance') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card text-center">
                <div class="text-gray-500 text-xs text-uppercase font-weight-bold mb-2">{{ __('mas/ai.routing_accuracy') }}</div>
                <div class="h2 font-weight-bold text-info">{{ $stats['performance']['kpis']['routing_accuracy_percent'] ?? 100 }}%</div>
                <div class="text-xs text-gray-400">{{ __('mas/ai.model_inference') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card text-center">
                <div class="text-gray-500 text-xs text-uppercase font-weight-bold mb-2">{{ __('mas/ai.quota_usage') }}</div>
                <div class="h2 font-weight-bold {{ ($stats['performance']['kpis']['quota_used_percent'] ?? 0) > 80 ? 'text-warning' : 'text-success' }}">
                    {{ $stats['performance']['kpis']['quota_used_percent'] ?? 0 }}%
                </div>
                <div class="text-xs text-gray-400">{{ __('mas/ai.intent_detection') }}</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- Performance Charts --}}
        <div class="col-lg-8">
            <div class="glass-panel h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="font-weight-bold m-0 text-dark"><i class="mdi mdi-chart-bell-curve mr-2 text-info"></i>{{ __('mas/ai.model_performance') }}</h5>
                    <button class="btn btn-xs btn-outline-info">{{ __('mas/ai.view_logs') }}</button>
                </div>
                <div style="height: 300px">
                    @if(isset($stats['models']['models']) && count($stats['models']['models']) > 0)
                        <canvas id="performanceChart"></canvas>
                    @else
                        <div class="text-center py-5 d-flex flex-column align-items-center justify-content-center h-100">
                            <i class="mdi mdi-chart-timeline-variant text-gray-300 fa-4x mb-3"></i>
                            <p class="text-gray-500">{{ __('mas/common.no_data') }}<br><small class="text-muted">{{ __('mas/common.metrics_will_appear') }}</small></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Top Intents --}}
        <div class="col-lg-4">
            <div class="glass-panel h-100 shadow-sm">
                <h5 class="font-weight-bold mb-4 text-dark"><i class="mdi mdi-target-variant mr-2 text-primary"></i>{{ __('mas/ai.top_intents') }}</h5>
                <div class="table-responsive">
                    <table class="table table-borderless table-sm">
                        @if(isset($stats['intents']['intents']) && count($stats['intents']['intents']) > 0)
                            @foreach(array_slice($stats['intents']['intents'], 0, 8) as $intent)
                            <tr>
                                <td class="py-2">
                                    <div class="font-weight-bold text-dark" style="font-size: 0.85rem">{{ ucfirst($intent['name']) }}</div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $intent['accuracy_percent'] }}%"></div>
                                    </div>
                                </td>
                                <td class="text-right py-2">
                                    <span class="badge badge-light">{{ $intent['count'] }} {{ __('mas/ai.hits') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-information-outline text-gray-400 fa-3x"></i>
                                <p class="text-gray-500 mt-2">{{ __('mas/ai.no_intent_data') }}</p>
                            </div>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- LIMS Insights --}}
    <h3 class="mb-4 font-weight-bold text-dark mt-5">{{ __('mas/ai.lims_predictive') }}</h3>
    <div class="row mb-5">
        {{-- TAT Prediction Summary --}}
        <div class="col-md-4">
            <div class="kpi-card shadow-sm" style="border-left: 5px solid #28a745;">
                <h5 class="font-weight-bold text-success mb-3">{{ __('mas/ai.on_time_performance') }}</h5>
                @php
                    $active = $stats['lims_insights']['tat']['summary']['active_batches'] ?? 0;
                    $overdue = $stats['lims_insights']['tat']['summary']['overdue_batches'] ?? 0;
                    $successRate = $active > 0 ? round((($active - $overdue) / $active) * 100, 1) : 100;
                @endphp
                <div class="d-flex align-items-end justify-content-between">
                    <div class="h2 font-weight-bold m-0">{{ $successRate }}%</div>
                </div>
                <hr>
                <div class="text-xs text-secondary">
                    <i class="mdi mdi-clock-alert mr-1"></i> {{ $active }} {{ __('mas/ai.active_batches_monitored') }}
                </div>
            </div>
        </div>

        {{-- QC Stability --}}
        <div class="col-md-4">
            <div class="kpi-card shadow-sm" style="border-left: 5px solid #ffc107;">
                <h5 class="font-weight-bold text-warning mb-3">{{ __('mas/ai.qc_drift') }}</h5>
                <div class="d-flex align-items-end justify-content-between">
                    <div class="h2 font-weight-bold m-0">{{ ($stats['lims_insights']['qc']['summary']['critical_records'] ?? 0) + ($stats['lims_insights']['qc']['summary']['warning_records'] ?? 0) }}</div>
                    <div class="text-xs text-gray-400">{{ __('mas/ai.anomalies_detected') }}</div>
                </div>
                <hr>
                <div class="text-xs text-secondary">
                    <i class="mdi mdi-alert-circle mr-1 text-danger"></i> {{ $stats['lims_insights']['qc']['summary']['critical_records'] ?? 0 }} {{ __('mas/common.critical') }} | {{ $stats['lims_insights']['qc']['summary']['warning_records'] ?? 0 }} {{ __('mas/dashboard.reorder_alerts') }}
                </div>
            </div>
        </div>

        {{-- Inventory Risk --}}
        <div class="col-md-4">
            <div class="kpi-card shadow-sm" style="border-left: 5px solid #17a2b8;">
                <h5 class="font-weight-bold text-info mb-3">{{ __('mas/ai.inventory_health_index') }}</h5>
                <div class="d-flex align-items-end justify-content-between">
                    <div class="h2 font-weight-bold m-0">{{ $stats['lims_insights']['inventory']['summary']['items_below_minimum'] ?? 0 }}</div>
                    <div class="text-xs text-info">{{ __('mas/ai.replenishment_alerts') }}</div>
                </div>
                <hr>
                <div class="text-xs text-secondary">
                    <i class="mdi mdi-package-variant mr-1 text-info"></i> {{ $stats['lims_insights']['inventory']['summary']['items_near_expiry'] ?? 0 }} {{ __('mas/ai.near_expiry') }}
                </div>
            </div>
        </div>
    </div>
    {{-- Governance & Model Registry --}}
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="m-0 font-weight-bold"><i class="mdi mdi-shield-check mr-2"></i>{{ __('mas/ai.model_registry') }}</h5>
                    <span class="badge badge-info">{{ count($stats['models']['registry'] ?? []) }} {{ __('mas/ai.models_registered') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">{{ __('mas/ai.model_id') }}</th>
                                <th class="border-0">{{ __('mas/ai.name') }}</th>
                                <th class="border-0">{{ __('mas/ai.type') }}</th>
                                <th class="border-0">{{ __('mas/ai.version') }}</th>
                                <th class="border-0">{{ __('mas/ai.framework') }}</th>
                                <th class="border-0">{{ __('mas/ai.status') }}</th>
                                <th class="border-0">{{ __('mas/ai.last_deployment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['models']['registry'] ?? [] as $model)
                                <tr>
                                    <td class="font-weight-bold text-info">#{{ $model['id'] ?? '-' }}</td>
                                    <td>{{ $model['model_name'] ?? '-' }}</td>
                                    <td><span class="badge badge-outline-secondary">{{ $model['model_type'] ?? 'Base' }}</span></td>
                                    <td class="text-primary font-weight-bold">{{ $model['version'] ?? 'v1.0' }}</td>
                                    <td><i class="mdi mdi-xml mr-1"></i>{{ $model['framework'] ?? 'Scikit-learn' }}</td>
                                    <td>
                                        @if(!empty($model['is_active']))
                                            <span class="text-success"><i class="mdi mdi-check-circle mr-1"></i>{{ __('mas/ai.active') }}</span>
                                        @else
                                            <span class="text-secondary"><i class="mdi mdi-pause-circle mr-1"></i>{{ __('mas/ai.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $model['deployed_at'] ?? '2026-04-10' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-secondary">
                                        <i class="mdi mdi-database-off fa-3x mb-3 d-block"></i>
                                        {{ __('mas/ai.no_registry_records') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- System Alerts & Drift Monitoring --}}
    <div class="row mt-4 mb-5">
        <div class="col-md-12">
            <div class="glass-panel shadow-sm">
                <h5 class="font-weight-bold mb-4 text-dark"><i class="mdi mdi-bell-ring mr-2 text-danger"></i>{{ __('mas/ai.alerts_drift') }}</h5>
                <div class="row">
                    @forelse($stats['alerts'] ?? [] as $alert)
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-{{ $alert['severity'] === 'danger' ? 'danger' : ($alert['severity'] === 'warning' ? 'warning' : 'info') }} d-flex align-items-center mb-0 border-0 shadow-sm" style="border-radius: 10px;">
                                <i class="mdi {{ $alert['severity'] === 'danger' ? 'mdi-alert-octagon' : 'mdi-alert-circle' }} fa-2x mr-3"></i>
                                <div>
                                    <h6 class="font-weight-bold mb-1">{{ strtoupper($alert['type'] ?? 'SYSTEM') }}</h6>
                                    <p class="mb-0 small">{{ $alert['message'] ?? 'Unknown alert' }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">
                            <div class="text-success">
                                <i class="mdi mdi-check-all fa-2x mb-2"></i>
                                <p class="mb-0">{{ __('mas/ai.healthy_status') }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script2')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartElement = document.getElementById('performanceChart');
        if (!chartElement) return;

        const ctx = chartElement.getContext('2d');
        const models = @json($stats['models']['models'] ?? []);
        
        if (models.length === 0) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: models.map(m => m.name),
                datasets: [
                    {
                        label: 'Success Rate (%)',
                        data: models.map(m => m.success_rate),
                        backgroundColor: 'rgba(56, 189, 248, 0.7)',
                        borderColor: 'rgb(56, 189, 248)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Avg Latency (ms)',
                        data: models.map(m => m.avg_latency_ms),
                        type: 'line',
                        borderColor: '#f59e0b',
                        backgroundColor: '#f59e0b',
                        borderWidth: 3,
                        pointRadius: 4,
                        yAxisID: 'y1',
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Success Rate %' },
                        min: 0,
                        max: 100,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Latency (ms)' }
                    }
                }
            }
        });
    });

    function exportAiPdf() {
        // Find the canvas element
        const canvas = document.getElementById('performanceChart');
        if (canvas) {
            // For Chart.js 4.x, we can get the image from the canvas directly or via chart instance
            const base64Image = canvas.toDataURL('image/png');
            document.getElementById('ai_chart_image_input').value = base64Image;
        } else {
            // No chart to capture, just send empty value
            document.getElementById('ai_chart_image_input').value = '';
        }
        document.getElementById('aiPdfExportForm').submit();
    }
</script>
@endsection
