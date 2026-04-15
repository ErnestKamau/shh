@extends('layouts.mas.layout.app')

@section('content2')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">Laboratory General Analytics</h1>
            <p class="text-muted small mb-0">Overview of workload distribution and customer engagement.</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportGeneralPdf(false)" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-file-pdf"></i> Download Report
                </button>
                <button onclick="exportGeneralPdf(true)" class="btn btn-outline-primary btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> Preview
                </button>
            </div>
            
            <form id="pdfExportForm" action="{{ route('mas.export.visuals', 'lab-general') }}" method="POST" style="display:none">
                @csrf
                <input type="hidden" name="chart_image" id="chart_image_input">
                <input type="hidden" name="preview" id="preview_input" value="false">
            </form>
        </div>
    </div>

    <!-- Summary Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-2 opacity-75">Workload Volume</h6>
                    <h2 class="font-weight-bold mb-0">{{ $stats['summary']['active_batches'] ?? 0 }}</h2>
                    <p class="small mb-0 mt-2">Active Batches in Lab</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-2 opacity-75">Active Clients</h6>
                    <h2 class="font-weight-bold mb-0">{{ count($stats['top_clients']) }}</h2>
                    <p class="small mb-0 mt-2">Engaged in Current Period</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sample Type Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Sample Type Distribution</h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="sampleTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Volume Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Volume by Top Clients</h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="clientVolumeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Top Clients Ranking</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Rank</th>
                                    <th class="border-0">Client Name</th>
                                    <th class="border-0 text-center">Active Batches</th>
                                    <th class="border-0">Workload Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalAll = collect($stats['top_clients'])->sum('total') ?: 1; @endphp
                                @foreach($stats['top_clients'] as $index => $client)
                                    <tr>
                                        <td>#{{ $index + 1 }}</td>
                                        <td class="font-weight-bold text-dark">{{ $client->name }}</td>
                                        <td class="text-center">{{ $client->total }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @php $percent = round(($client->total / $totalAll) * 100); @endphp
                                                <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                                    <div class="progress-bar bg-info" style="width: {{ $percent }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $percent }}%</small>
                                            </div>
                                        </td>
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

<style>
    .opacity-75 { opacity: 0.75; }
</style>
@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global Chart Defaults
        Chart.defaults.global.defaultFontFamily = "'Inter', sans-serif";
        Chart.defaults.global.defaultFontColor = '#64748b';

        // 1. Sample Type Chart
        var typeCtx = document.getElementById('sampleTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: @json($stats['charts']['type_labels']),
                datasets: [{
                    data: @json($stats['charts']['type_counts']),
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } }
            }
        });

        // 2. Client Volume Chart
        var clientCtx = document.getElementById('clientVolumeChart').getContext('2d');
        window.clientVolumeChart = new Chart(clientCtx, {
            type: 'horizontalBar',
            data: {
                labels: @json($stats['charts']['client_labels']),
                datasets: [{
                    label: 'Batch Count',
                    data: @json($stats['charts']['client_counts']),
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ ticks: { beginAtZero: true } }],
                    yAxes: [{ gridLines: { display: false } }]
                }
            }
        });
    });

    function exportGeneralPdf(isPreview = false) {
        // We capture the client volume chart for this report
        const chart = window.clientVolumeChart; 
        if (chart) {
            const base64Image = chart.toBase64Image();
            document.getElementById('chart_image_input').value = base64Image;
        } else {
            document.getElementById('chart_image_input').value = '';
        }

        const form = document.getElementById('pdfExportForm');
        document.getElementById('preview_input').value = isPreview;
        
        if (isPreview) {
            form.target = "_blank";
        } else {
            form.target = "_self";
        }

        form.submit();
    }
</script>
@endsection
