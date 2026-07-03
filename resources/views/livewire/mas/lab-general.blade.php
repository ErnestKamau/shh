<div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .lab-general-page { color: #1e293b; }
    .lab-general-card { border-radius: 8px; }
    .lab-general-card .card-header { background-color: #fff; border-bottom: 0; }
    .lab-general-kpi { border-left: 4px solid #e2e8f0; }
    .lab-general-kpi-primary { border-left-color: var(--color-primary); }
    .lab-general-kpi-success { border-left-color: #10b981; }
    .lab-general-kpi-warning { border-left-color: #f59e0b; }
    .lab-general-kpi-danger { border-left-color: #ef4444; }
    .lab-general-chart { height: 350px; }
    .lab-general-chart-sm { height: 300px; }
    #labHeatmap { height: 390px; border-radius: 8px; background: #f8fafc; }
    .lab-general-page .progress { border-radius: 10px; background-color: #f1f5f9; }
    .lab-general-empty { min-height: 90px; display: flex; align-items: center; justify-content: center; }
    .lab-general-page .btn-indigo { background-color: var(--color-primary); color: #fff; border-color: var(--color-primary); }
    .lab-general-page .btn-indigo:hover,
    .lab-general-page .btn-indigo:focus { background-color: var(--color-primary-hover); color: #fff; border-color: var(--color-primary-hover); }
    .lab-general-page .btn-outline-indigo { background-color: #fff; color: var(--color-primary); border-color: var(--color-primary); }
    .lab-general-page .btn-outline-indigo:hover,
    .lab-general-page .btn-outline-indigo:focus { background-color: var(--color-primary); color: #fff; border-color: var(--color-primary); }
    .lab-general-filter-bar { background: #fff; border-radius: 8px; padding: 14px 16px; }
    .lab-general-filter-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 5px; }
    .lab-general-range-chip { background: var(--color-primary-soft); color: var(--color-primary); border-radius: 999px; padding: 7px 12px; font-size: 12px; font-weight: 700; white-space: nowrap; }
</style>

<div class="container-fluid py-4 lab-general-page">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/lab.general_title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/lab.general_subtitle') }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportGeneralPdf(false)" class="btn btn-indigo btn-sm">
                    <i class="mdi mdi-file-pdf"></i> {{ __('mas/common.download') }} {{ __('mas/common.report') }}
                </button>
                <button onclick="exportGeneralPdf(true)" class="btn btn-outline-indigo btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> {{ __('mas/common.preview') }}
                </button>
            </div>
            
            <form id="pdfExportForm" action="{{ route('mas.export.visuals', 'lab-general') }}" method="POST" style="display:none">
                @csrf
                <input type="hidden" name="chart_image" id="chart_image_input">
                <input type="hidden" name="preview" id="preview_input" value="false">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
            </form>
        </div>
    </div>

    <!-- Date Filters -->
    <div class="card shadow-sm border-0 lab-general-card mb-4">
        <div class="card-body lab-general-filter-bar">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label for="general-start-date" class="lab-general-filter-label">From</label>
                    <input id="general-start-date" type="date" wire:model="startDate" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label for="general-end-date" class="lab-general-filter-label">To</label>
                    <input id="general-end-date" type="date" wire:model="endDate" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="lab-general-range-chip d-inline-flex align-items-center">
                        <i class="mdi mdi-calendar-range mr-1"></i>
                        {{ $startDate ?: 'Start' }} to {{ $endDate ?: 'Today' }}
                    </div>
                </div>
                <div class="col-md-3 text-md-right">
                    <div class="btn-group btn-group-sm shadow-sm">
                        <button type="button" wire:click="applyFilters" class="btn btn-indigo">
                            <i class="mdi mdi-filter-check-outline mr-1"></i> Apply
                        </button>
                        <button type="button" wire:click="resetFilters" class="btn btn-outline-indigo">
                            <i class="mdi mdi-filter-remove-outline mr-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($stats['available']) && !($stats['available'] ?? false))
        <div class="alert alert-warning shadow-sm border-0">
            <i class="mdi mdi-alert mr-2"></i> {{ $stats['message'] ?? __('mas/common.no_data') }}
        </div>
    @endif

    <!-- Summary Row -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 h-100 lab-general-card lab-general-kpi lab-general-kpi-primary">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">{{ __('mas/lab.workload_volume') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-dark">{{ number_format($stats['summary']['active_batches'] ?? 0) }}</h2>
                    <p class="text-primary small mb-0 mt-2">{{ __('mas/dashboard.active_batches') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 h-100 lab-general-card lab-general-kpi lab-general-kpi-success">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">{{ __('mas/lab.active_clients') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-success">{{ number_format(count($stats['top_clients'] ?? [])) }}</h2>
                    <p class="text-muted small mb-0 mt-2">{{ __('mas/lab.engaged_current_period') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 h-100 lab-general-card lab-general-kpi lab-general-kpi-warning">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">{{ __('mas/lab.bucket_due_today') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-warning">{{ number_format($stats['summary']['due_today_batches'] ?? 0) }}</h2>
                    <p class="text-muted small mb-0 mt-2">{{ __('mas/lab.efficiency_monitoring') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 lab-general-card lab-general-kpi lab-general-kpi-danger">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">{{ __('mas/lab.overdue') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-danger">{{ number_format($stats['summary']['overdue_batches'] ?? 0) }}</h2>
                    <p class="text-muted small mb-0 mt-2">{{ __('mas/lab.overdue_watchlist') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Trends & Distribution -->
    <div class="row mb-4">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100 lab-general-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.historical_trends') }}</h5>
                    <span class="badge badge-light text-primary font-weight-bold">
                        <i class="mdi mdi-trending-up mr-1"></i>{{ date('Y') }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="lab-general-chart">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100 lab-general-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.sample_type_distribution') }}</h5>
                    <span class="text-muted small">{{ number_format(array_sum($stats['charts']['type_counts'] ?? [])) }} samples</span>
                </div>
                <div class="card-body">
                    <div class="lab-general-chart">
                        <canvas id="sampleTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Client Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 lab-general-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.volume_top_clients') }}</h5>
                    <span class="badge badge-primary">{{ __('mas/lab.top_clients_ranking') }}</span>
                </div>
                <div class="card-body">
                    <div class="lab-general-chart">
                        <canvas id="clientVolumeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 lab-general-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.top_clients_ranking') }}</h5>
                    <span class="text-muted small">{{ count($stats['top_clients'] ?? []) }} clients</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase">{{ __('mas/lab.rank') }}</th>
                                    <th class="border-0 small text-uppercase">{{ __('mas/lab.client_name') }}</th>
                                    <th class="border-0 small text-uppercase text-center">{{ __('mas/lab.active_batches_col') }}</th>
                                    <th class="border-0 small text-uppercase">{{ __('mas/lab.workload_share') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalAll = collect($stats['top_clients'] ?? [])->sum('total') ?: 1; @endphp
                                @forelse($stats['top_clients'] ?? [] as $index => $client)
                                    <tr>
                                        <td><span class="badge badge-light text-primary">#{{ $index + 1 }}</span></td>
                                        <td class="font-weight-bold text-dark text-truncate" style="max-width: 200px;">{{ $client->name }}</td>
                                        <td class="text-center">{{ $client->total }}</td>
                                        <td style="min-width: 150px;">
                                            <div class="d-flex align-items-center">
                                                @php $percent = round(($client->total / $totalAll) * 100); @endphp
                                                <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $percent }}%</small>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">{{ __('mas/common.no_data') }}</td>
                                    </tr>
                                @endforelse
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
            <div class="card shadow-sm border-0 lab-general-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.geographic_density') }}</h5>
                    <span class="badge badge-light text-danger">
                        <i class="mdi mdi-map-marker-radius mr-1"></i>{{ count($stats['geographic_data'] ?? []) }} points
                    </span>
                </div>
                <div class="card-body p-2">
                    <div id="labHeatmap"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 lab-general-card lab-general-kpi lab-general-kpi-primary">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.workflow_stages') }}</h5>
                    <span class="text-muted small">{{ count($stats['stage_summary'] ?? []) }} stages</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @forelse(array_slice($stats['stage_summary'] ?? [], 0, 8) as $stage)
                                <tr>
                                    <td class="pl-3 py-3">
                                        <div class="font-weight-bold text-dark">{{ $stage['workflow_stage'] }}</div>
                                        <small class="text-muted">{{ number_format($stage['total_batches']) }} Batches</small>
                                    </td>
                                    <td class="text-right pr-3 py-3">
                                        @if($stage['overdue_batches'] > 0)
                                            <span class="badge badge-danger">
                                                {{ number_format($stage['overdue_batches']) }} {{ __('mas/lab.overdue') }}
                                            </span>
                                        @else
                                            <span class="badge badge-success">{{ __('mas/common.stable') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">{{ __('mas/common.no_data') }}</td>
                                    </tr>
                                @endforelse
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
    (function() {
        window.labGeneralCharts = window.labGeneralCharts || {};
        window.labGeneralMap = window.labGeneralMap || null;
        window.labGeneralMapElement = window.labGeneralMapElement || null;
        window.labGeneralMapOverlays = window.labGeneralMapOverlays || [];

        function initialPayload() {
            return {
                charts: @json($stats['charts'] ?? []),
                monthly_trends: @json($stats['monthly_trends'] ?? []),
                geographic_data: @json($stats['geographic_data'] ?? []),
            };
        }

        function normalizePayload(detail) {
            return Array.isArray(detail) ? detail[0] : detail;
        }

        function destroyChart(name) {
            if (window.labGeneralCharts[name]) {
                window.labGeneralCharts[name].destroy();
                window.labGeneralCharts[name] = null;
            }
        }

        function initCharts(payload) {
            if (typeof Chart === 'undefined') return;

            Chart.defaults.global.defaultFontFamily = "'Inter', sans-serif";
            Chart.defaults.global.defaultFontColor = '#64748b';

            var chartData = payload.charts || {};
            var trendData = payload.monthly_trends || {};

            var typeEl = document.getElementById('sampleTypeChart');
            if (typeEl) {
                destroyChart('sampleType');
                window.labGeneralCharts.sampleType = new Chart(typeEl.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: chartData.type_labels || [],
                    datasets: [{
                        data: chartData.type_counts || [],
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

            var clientEl = document.getElementById('clientVolumeChart');
            if (clientEl) {
                destroyChart('clientVolume');
                window.clientVolumeChart = window.labGeneralCharts.clientVolume = new Chart(clientEl.getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    labels: chartData.client_labels || [],
                    datasets: [{
                        label: 'Samples',
                        data: chartData.client_counts || [],
                        backgroundColor: '{{ \App\Services\System\ThemeService::primaryColor() }}',
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

            var trendEl = document.getElementById('monthlyTrendChart');
            if (trendEl) {
                destroyChart('monthlyTrend');
                window.labGeneralCharts.monthlyTrend = new Chart(trendEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendData.labels || [],
                    datasets: [{
                        label: 'Samples Registered',
                        data: trendData.data || [],
                        borderColor: '{{ \App\Services\System\ThemeService::primaryColor() }}',
                        backgroundColor: '{{ \App\Services\System\ThemeService::rgbaFromHex(\App\Services\System\ThemeService::primaryColor(), 0.1) }}',
                        borderWidth: 3,
                        pointBackgroundColor: 'var(--color-primary)',
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
        }

        function initMap(payload) {
            if (typeof L === 'undefined') return;

            var heatmapEl = document.getElementById('labHeatmap');
            if (!heatmapEl) return;

            if (window.labGeneralMapElement !== heatmapEl) {
                if (window.labGeneralMap) {
                    window.labGeneralMap.remove();
                }
                window.labGeneralMap = null;
                window.labGeneralMapElement = heatmapEl;
            }

            if (!window.labGeneralMap) {
                window.labGeneralMap = L.map('labHeatmap', {
                    center: [-6.3690, 34.8888],
                    zoom: 6,
                    scrollWheelZoom: false,
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 18,
                }).addTo(window.labGeneralMap);
            }

            window.labGeneralMapOverlays.forEach(function(overlay) {
                window.labGeneralMap.removeLayer(overlay);
            });
            window.labGeneralMapOverlays = [];

            var geoData = payload.geographic_data || [];
            var bounds = [];

            geoData.forEach(function(point) {
                var lat = parseFloat(point.lat);
                var lng = parseFloat(point.lng);

                if (isNaN(lat) || isNaN(lng)) return;

                var intensity = parseInt(point.intensity || 1, 10);
                var circle = L.circle([lat, lng], {
                    color: '#dc2626',
                    opacity: 0.85,
                    weight: 2,
                    fillColor: '#ef4444',
                    fillOpacity: 0.35,
                    radius: Math.max(12000, Math.min(65000, 9000 + (intensity * 2500))),
                }).addTo(window.labGeneralMap).bindPopup('<strong>Sample density</strong><br>Samples: ' + intensity);

                var marker = L.marker([lat, lng]).addTo(window.labGeneralMap)
                    .bindPopup('<strong>Sample density</strong><br>Samples: ' + intensity);

                window.labGeneralMapOverlays.push(circle, marker);
                bounds.push([lat, lng]);
            });

            if (bounds.length > 0) {
                window.labGeneralMap.fitBounds(bounds, { padding: [40, 40] });
            } else {
                window.labGeneralMap.setView([-6.3690, 34.8888], 6);
            }

            setTimeout(function() {
                window.labGeneralMap.invalidateSize();
            }, 100);
        };

        function renderLabGeneral(payload) {
            payload = payload || initialPayload();
            initCharts(payload);
            initMap(payload);
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderLabGeneral(initialPayload());
        });

        document.addEventListener('mas-lab-general-updated', function(event) {
            setTimeout(function() {
                renderLabGeneral(normalizePayload(event.detail));
            }, 50);
        });
    })();

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
