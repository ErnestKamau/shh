@extends('layouts.mas.layout.app')

@section('content2')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">AI Monitoring & Governance</h1>
            <p class="text-muted small mb-0">System performance monitoring, model registry, and data drift detection.</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportAiPdf(false)" class="btn btn-purple btn-sm text-white">
                    <i class="mdi mdi-file-pdf"></i> Governance Report
                </button>
                <button onclick="exportAiPdf(true)" class="btn btn-outline-purple btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> Preview
                </button>
            </div>
            <form id="aiExportForm" action="{{ route('mas.export.visuals', 'ai') }}" method="POST" style="display:none">
                @csrf
                <input type="hidden" name="chart_image" id="chart_image_input">
                <input type="hidden" name="preview" id="preview_input" value="false">
            </form>
        </div>
    </div>

    <!-- Health Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 border-left border-purple" style="border-left-width: 4px !important;">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">System Health</h6>
                    <h2 class="font-weight-bold mb-0 text-dark">{{ $stats['performance']['health_score'] ?? 0 }}%</h2>
                    <div class="badge badge-{{ ($stats['performance']['status'] ?? '') == 'healthy' ? 'success' : 'warning' }} mt-2">
                        {{ strtoupper($stats['performance']['status'] ?? 'UNKNOWN') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">Model Accuracy</h6>
                    <h2 class="font-weight-bold mb-0 text-success">{{ $stats['performance']['model_success_rate'] ?? 0 }}%</h2>
                    <p class="text-muted small mb-0 mt-2">Successful inferences</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">Routing Precision</h6>
                    <h2 class="font-weight-bold mb-0 text-info">{{ $stats['performance']['routing_accuracy'] ?? 0 }}%</h2>
                    <p class="text-muted small mb-0 mt-2">Intent classification accuracy</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">Active Alerts</h6>
                    <h2 class="font-weight-bold mb-0 {{ count($stats['alerts'] ?? []) > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ count($stats['alerts'] ?? []) }}
                    </h2>
                    <p class="text-muted small mb-0 mt-2">SLA & Drift violations</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Intent Distribution -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">LIMS Query Intent Distribution</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="intentDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Latency Trend -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">Model Performance Registry</h5>
                    <span class="badge badge-light">Live Analytics</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase">Model Name</th>
                                    <th class="border-0 small text-uppercase">Success Rate</th>
                                    <th class="border-0 small text-uppercase">Latency</th>
                                    <th class="border-0 small text-uppercase">Tokens/Req</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['models']['models'] ?? [] as $model)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $model['model'] }}</td>
                                        <td>
                                            <span class="font-weight-bold {{ $model['success_rate_percent'] > 98 ? 'text-success' : 'text-warning' }}">
                                                {{ $model['success_rate_percent'] }}%
                                            </span>
                                        </td>
                                        <td>{{ number_format($model['avg_latency_ms']) }}ms</td>
                                        <td>{{ number_format($model['avg_tokens_per_request']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Governance Section -->
    <div class="row">
        <!-- Drift Alerts & Governance -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Governance & Drift Monitor</h5>
                </div>
                <div class="card-body">
                    @forelse($stats['alerts'] ?? [] as $alert)
                        <div class="alert alert-{{ $alert['severity'] }} bg-{{ $alert['severity'] }}-light border-0 mb-3 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-{{ $alert['type'] == 'drift' ? 'trending-down' : 'alert-circle' }} mr-3 mdi-24px"></i>
                                <div>
                                    <div class="font-weight-bold">{{ strtoupper($alert['type']) }} ALERT</div>
                                    <div class="small">{{ $alert['message'] }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="mdi mdi-shield-check text-success mdi-48px"></i>
                            <p class="text-muted mt-2">All models compliant. No drift detected.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Model Registry (Static Metadata) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Deployed Model Registry</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase">Registry Name</th>
                                    <th class="border-0 small text-uppercase text-center">Version</th>
                                    <th class="border-0 small text-uppercase text-center">Framework</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['models']['registry'] ?? [] as $reg)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $reg['model_name'] }}</div>
                                            <div class="text-muted small">Deployed: {{ date('Y-m-d', strtotime($reg['deployed_at'])) }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary px-2">{{ $reg['version'] }}</span>
                                        </td>
                                        <td class="text-center small">{{ $reg['framework'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.global.defaultFontFamily = "'Inter', sans-serif";
        Chart.defaults.global.defaultFontColor = '#64748b';

        // Intent Distribution Doughnut
        var intentCtx = document.getElementById('intentDistributionChart').getContext('2d');
        var intentData = @json($stats['intents']['intents'] ?? []);
        
        new Chart(intentCtx, {
            type: 'doughnut',
            data: {
                labels: intentData.map(i => i.name),
                datasets: [{
                    data: intentData.map(i => i.count),
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 75,
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, usePointStyle: true }
                }
            }
        });
    });

    function exportAiPdf(isPreview = false) {
        const form = document.getElementById('aiExportForm');
        document.getElementById('preview_input').value = isPreview;
        form.target = isPreview ? "_blank" : "_self";
        form.submit();
    }
</script>

<style>
    .btn-purple { background-color: #6f42c1; border: none; color: white; }
    .btn-purple:hover { background-color: #59359a; color: white; }
    .btn-outline-purple { border-color: #6f42c1; color: #6f42c1; background: transparent; }
    .btn-outline-purple:hover { background-color: #6f42c1; color: white; }
    .bg-danger-light { background-color: #fee2e2; color: #991b1b; }
    .bg-warning-light { background-color: #fef3c7; color: #92400e; }
    .bg-info-light { background-color: #e0f2fe; color: #075985; }
    .text-purple { color: #6f42c1; }
</style>
@endsection
