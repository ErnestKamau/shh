<div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #labHeatmap { height: 400px; border-radius: 12px; }
    .opacity-75 { opacity: 0.75; }
    .card { border-radius: 12px; transition: transform 0.2s; }
    .card:hover { transform: translateY(-2px); }
    .card-header { border-bottom: 1px solid rgba(0,0,0,0.05) !important; background-color: transparent !important; }
    .progress { border-radius: 10px; }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/lab.general_title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/lab.general_subtitle') }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportGeneralPdf(false)" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-file-pdf"></i> {{ __('mas/common.download') }} {{ __('mas/common.report') }}
                </button>
                <button onclick="exportGeneralPdf(true)" class="btn btn-outline-primary btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> {{ __('mas/common.preview') }}
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
                    <h6 class="text-uppercase small mb-2 opacity-75">{{ __('mas/lab.workload_volume') }}</h6>
                    <h2 class="font-weight-bold mb-0">{{ $stats['summary']['active_batches'] ?? 0 }}</h2>
                    <p class="small mb-0 mt-2">{{ __('mas/dashboard.active_batches') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-2 opacity-75">{{ __('mas/lab.active_clients') }}</h6>
                    <h2 class="font-weight-bold mb-0">{{ count($stats['top_clients'] ?? []) }}</h2>
                    <p class="small mb-0 mt-2">{{ __('mas/lab.engaged_current_period') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-warning text-dark">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-2 text-dark-50">{{ __('mas/lab.bucket_due_today') }}</h6>
                    <h2 class="font-weight-bold mb-0">{{ $stats['summary']['due_today_batches'] ?? 0 }}</h2>
                    <p class="small mb-0 mt-2">{{ __('mas/lab.efficiency_monitoring') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-2 opacity-75">{{ __('mas/lab.overdue') }}</h6>
                    <h2 class="font-weight-bold mb-0">{{ $stats['summary']['overdue_batches'] ?? 0 }}</h2>
                    <p class="small mb-0 mt-2">{{ __('mas/lab.overdue_watchlist') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Trends & Distribution -->
    <div class="row mb-4">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="mdi mdi-trending-up text-primary mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.historical_trends') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="mdi mdi-chart-pie text-info mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.sample_type_distribution') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="sampleTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Client Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="mdi mdi-account-group text-primary mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.volume_top_clients') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="clientVolumeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="mdi mdi-format-list-numbered text-primary mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.top_clients_ranking') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">{{ __('mas/lab.rank') }}</th>
                                    <th class="border-0">{{ __('mas/lab.client_name') }}</th>
                                    <th class="border-0 text-center">{{ __('mas/lab.active_batches_col') }}</th>
                                    <th class="border-0">{{ __('mas/lab.workload_share') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalAll = collect($stats['top_clients'] ?? [])->sum('total') ?: 1; @endphp
                                @foreach($stats['top_clients'] ?? [] as $index => $client)
                                    <tr>
                                        <td><span class="badge badge-soft-primary">#{{ $index + 1 }}</span></td>
                                        <td class="font-weight-bold text-dark text-truncate" style="max-width: 200px;">{{ $client->name }}</td>
                                        <td class="text-center">{{ $client->total }}</td>
                                        <td style="min-width: 150px;">
                                            <div class="d-flex align-items-center">
                                                @php $percent = round(($client->total / $totalAll) * 100); @endphp
                                                <div class="progress flex-grow-1 mr-2" style="height: 6px; background-color: #f1f5f9;">
                                                    <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
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

    <!-- Row 3: Geo & Testing Matrix -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="mdi mdi-map-marker-radius text-danger mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.geographic_density') }}</h5>
                </div>
                <div class="card-body p-2">
                    <div id="labHeatmap"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100" style="border-left: 5px solid #6366f1 !important;">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="mdi mdi-matrix text-primary mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.workflow_stages') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach(array_slice($stats['stage_summary'] ?? [], 0, 8) as $stage)
                                <tr>
                                    <td class="pl-3 py-3">
                                        <div class="font-weight-bold text-dark">{{ $stage['workflow_stage'] }}</div>
                                        <small class="text-muted">{{ $stage['total_batches'] }} Batches</small>
                                    </td>
                                    <td class="text-right pr-3 py-3">
                                        @if($stage['overdue_batches'] > 0)
                                            <span class="text-danger font-weight-bold small">
                                                <i class="mdi mdi-alert-circle"></i> {{ $stage['overdue_batches'] }}
                                            </span>
                                        @else
                                            <span class="text-success small"><i class="mdi mdi-check-circle"></i></span>
                                        @endif
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global Chart Defaults
        Chart.defaults.global.defaultFontFamily = "'Inter', sans-serif";
        Chart.defaults.global.defaultFontColor = '#64748b';

        // 1. Sample Type Chart
        var typeEl = document.getElementById('sampleTypeChart');
        if (typeEl) {
            new Chart(typeEl.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: @json($stats['charts']['type_labels'] ?? []),
                    datasets: [{
                        data: @json($stats['charts']['type_counts'] ?? []),
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
        }

        // 2. Client Volume Chart
        var clientEl = document.getElementById('clientVolumeChart');
        if (clientEl) {
            window.clientVolumeChart = new Chart(clientEl.getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    labels: @json($stats['charts']['client_labels'] ?? []),
                    datasets: [{
                        label: 'Samples',
                        data: @json($stats['charts']['client_counts'] ?? []),
                        backgroundColor: '#3b82f6',
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ ticks: { beginAtZero: true, precision: 0 } }],
                        yAxes: [{ gridLines: { display: false } }]
                    }
                }
            });
        }

        // 3. Monthly Trends Chart
        var trendEl = document.getElementById('monthlyTrendChart');
        if (trendEl) {
            new Chart(trendEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($stats['monthly_trends']['labels'] ?? []),
                    datasets: [{
                        label: 'Samples Registered',
                        data: @json($stats['monthly_trends']['data'] ?? []),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#6366f1',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        yAxes: [{ ticks: { beginAtZero: true } }],
                        xAxes: [{ gridLines: { display: false } }]
                    }
                }
            });
        }

        // 4. Geographic Map
        var heatmapEl = document.getElementById('labHeatmap');
        if (heatmapEl) {
            var map = L.map('labHeatmap').setView([-1.286389, 36.817223], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var geoData = @json($stats['geographic_data'] ?? []);
            geoData.forEach(function(point) {
                L.circle([point.lat, point.lng], {
                    color: '#ef4444',
                    fillColor: '#ef4444',
                    fillOpacity: 0.5,
                    radius: 500 * (point.intensity || 1)
                }).addTo(map).bindPopup('Intensity: ' + point.intensity);
            });
        }
    });

    function exportGeneralPdf(isPreview = false) {
        const chart = window.clientVolumeChart; 
        if (chart) {
            const base64Image = chart.toBase64Image();
            document.getElementById('chart_image_input').value = base64Image;
        }

        const form = document.getElementById('pdfExportForm');
        document.getElementById('preview_input').value = isPreview;
        form.target = isPreview ? "_blank" : "_self";
        form.submit();
    }
</script>
</div>