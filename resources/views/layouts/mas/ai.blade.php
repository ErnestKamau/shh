@extends('layouts.mas.layout.app')

@section('content2')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">AI Intelligence & Governance</h1>
            <p class="text-muted small mb-0">Monitoring Imara AI model performance, governance drift, and LIMS-wide insights.</p>
        </div>
        <div class="col-auto d-flex">
            <button onclick="exportAiPdf(true)" class="btn btn-sm btn-outline-purple mr-2">
                <i class="mdi mdi-eye"></i> Preview Report
            </button>
            <button onclick="exportAiPdf(false)" class="btn btn-sm btn-purple">
                <i class="mdi mdi-file-pdf"></i> Export Report
            </button>
            <span class="badge badge-soft-purple p-2 shadow-sm ml-3">
                <i class="mdi mdi-brain mr-1"></i> Imara AI v1.2.0
            </span>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-left: 5px solid #6f42c1 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase font-weight-bold small mb-2">System Health</h6>
                    <h2 class="font-weight-bold mb-1 text-dark">{{ number_format($stats['performance']['kpis']['health_score'] ?? 0, 1) }}%</h2>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-purple" style="width: {{ $stats['performance']['kpis']['health_score'] ?? 0 }}%; background-color: #6f42c1;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-left: 5px solid #007bff !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase font-weight-bold small mb-2">Success Rate</h6>
                    <h2 class="font-weight-bold mb-1 text-dark">{{ number_format($stats['performance']['kpis']['uptime_percent'] ?? 0, 1) }}%</h2>
                    <p class="text-muted small mb-0">Across all model types</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-left: 5px solid #17a2b8 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase font-weight-bold small mb-2">Routing Accuracy</h6>
                    <h2 class="font-weight-bold mb-1 text-dark">{{ number_format($stats['performance']['kpis']['routing_accuracy_percent'] ?? 0, 1) }}%</h2>
                    <p class="text-muted small mb-0">Intent classification quality</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-left: 5px solid #dc3545 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase font-weight-bold small mb-2">Active Alerts</h6>
                    <h2 class="font-weight-bold mb-1 text-danger">{{ count($stats['alerts'] ?? []) }}</h2>
                    <p class="text-muted small mb-0">Security & Drift warnings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Live Model Performance (Full Width) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold mb-0 text-dark">Live Model Performance</h5>
                    <span class="badge badge-light border">Real-time Analytics</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table class="table table-hover table-condensed table-sm" id="modelPerformanceTable">
                            <thead class="bg-light text-uppercase small">
                                <tr>
                                    <th class="border-0 px-4">Model Name</th>
                                    <th class="border-0 text-center">Success</th>
                                    <th class="border-0 text-center">Latency</th>
                                    <th class="border-0 text-center">Avg Tokens</th>
                                    <th class="border-0 text-right pr-4">Confidence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['models']['models'] ?? [] as $model)
                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-brain text-purple mr-2"></i>
                                            <span class="font-weight-bold text-dark">{{ $model['model'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center font-weight-bold text-success">{{ number_format($model['success_rate_percent'] ?? 0, 1) }}%</td>
                                    <td class="text-center text-muted">{{ number_format($model['avg_latency_ms'] ?? 0) }}ms</td>
                                    <td class="text-center">{{ number_format($model['avg_tokens_per_request'] ?? 0) }}</td>
                                    <td class="text-right pr-4 font-weight-bold">{{ number_format(($model['avg_confidence'] ?? 0) * 100, 1) }}%</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No live performance data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Deployed Model Registry (Full Width) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="font-weight-bold mb-0 text-dark">Deployed Model Registry</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table class="table table-hover table-condensed table-sm">
                            <thead class="bg-light text-uppercase small">
                                <tr>
                                    <th class="border-0 px-4">Registry Name</th>
                                    <th class="border-0 text-center">Version</th>
                                    <th class="border-0 text-center">Framework</th>
                                    <th class="border-0 text-right pr-4">Deployed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['models']['registry'] ?? [] as $reg)
                                <tr>
                                    <td class="px-4">
                                        <div class="font-weight-bold text-dark">{{ $reg['model_name'] }}</div>
                                        <div class="text-muted small">{{ $reg['model_type'] }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-soft-purple px-2 border">{{ $reg['version'] }}</span>
                                    </td>
                                    <td class="text-center small">{{ $reg['framework'] }}</td>
                                    <td class="text-right pr-4 small text-muted">{{ date('Y-m-d', strtotime($reg['deployed_at'])) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Query Intent Distribution (Bar Chart) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold mb-0 text-dark">Query Intent Distribution</h5>
                    <span class="badge badge-soft-purple">Total Requests Breakdown</span>
                </div>
                <div class="card-body">
                    <div style="height: 350px; width: 100%;">
                        <canvas id="intentDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Governance Alerts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="font-weight-bold mb-0 text-dark">Governance & Drift Monitor</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($stats['alerts'] ?? [] as $alert)
                        <div class="col-md-6">
                            <div class="alert alert-{{ $alert['severity'] ?? 'danger' }} bg-soft-{{ $alert['severity'] ?? 'danger' }} border-0 mb-3 shadow-sm">
                                <div class="d-flex align-items-start">
                                    <i class="mdi mdi-{{ ($alert['type'] ?? '') == 'drift' ? 'trending-down' : 'alert-circle' }} mr-3 mdi-24px"></i>
                                    <div>
                                        <div class="font-weight-bold small text-uppercase">{{ $alert['type'] ?? 'DRIFT' }} ALERT</div>
                                        <div class="small">{{ $alert['message'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-4">
                            <i class="mdi mdi-shield-check text-success mdi-48px opacity-50"></i>
                            <p class="text-muted mt-2">All models compliant. No drift detected.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="aiExportForm" action="{{ route('mas.export.visuals', ['module' => 'ai']) }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="preview" id="preview_input" value="false">
    <input type="hidden" name="chart_image" id="chart_image_input">
</form>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart !== 'undefined') {
            Chart.defaults.global.defaultFontFamily = "'Inter', -apple-system, system-ui, sans-serif";
            Chart.defaults.global.defaultFontColor = '#64748b';

            var intentCtx = document.getElementById('intentDistributionChart');
            if (intentCtx) {
                var intentData = @json($stats['intents']['intents'] ?? []);
                new Chart(intentCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: intentData.map(i => (i.name || 'unknown').split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')),
                        datasets: [{
                            label: 'Requests',
                            data: intentData.map(i => i.count),
                            backgroundColor: '#6f42c1',
                            borderRadius: 6,
                            barThickness: 35
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: { display: false },
                        scales: {
                            yAxes: [{
                                ticks: { beginAtZero: true, stepSize: 1 },
                                gridLines: { color: 'rgba(0,0,0,0.05)', drawBorder: false }
                            }],
                            xAxes: [{
                                gridLines: { display: false }
                            }]
                        },
                        tooltips: {
                            backgroundColor: '#1e293b',
                            titleFontSize: 13,
                            bodyFontSize: 12,
                            xPadding: 10,
                            yPadding: 10,
                            displayColors: false
                        }
                    }
                });
            }
        }
    });

    function exportAiPdf(isPreview = false) {
        const form = document.getElementById('aiExportForm');
        const canvas = document.getElementById('intentDistributionChart');
        
        // Capture chart as image
        if (canvas) {
            document.getElementById('chart_image_input').value = canvas.toDataURL('image/png');
        }
        
        document.getElementById('preview_input').value = isPreview;
        form.target = isPreview ? "_blank" : "_self";
        form.submit();
    }
</script>

<style>
    .bg-soft-purple { background-color: rgba(111, 66, 193, 0.1); }
    .text-purple { color: #6f42c1; }
    .badge-soft-purple { background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; }
    .bg-soft-danger { background-color: #fee2e2; color: #991b1b; }
    .bg-soft-warning { background-color: #fef3c7; color: #92400e; }
    .bg-soft-info { background-color: #e0f2fe; color: #075985; }
    .bg-soft-success { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
    .btn-outline-purple { border-color: #6f42c1; color: #6f42c1; }
    .btn-outline-purple:hover { background-color: #6f42c1; color: white; }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px 10px;
        margin-left: 10px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #6f42c1 !important;
        color: white !important;
        border: none !important;
        border-radius: 4px;
    }
</style>
@endsection
