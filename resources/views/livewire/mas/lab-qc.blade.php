<div>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/qc.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/qc.subtitle') }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportQcPdf(false)" class="btn btn-indigo btn-sm">
                    <i class="mdi mdi-file-pdf"></i> {{ __('mas/common.download_report') }}
                </button>
                <button onclick="exportQcPdf(true)" class="btn btn-outline-indigo btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> {{ __('mas/qc.preview') }}
                </button>
            </div>
            
            <form id="pdfExportForm" action="{{ route('mas.export.visuals', 'lab-qc') }}" method="POST" style="display:none">
                @csrf
                <input type="hidden" name="chart_image" id="chart_image_input">
                <input type="hidden" name="preview" id="preview_input" value="false">
            </form>
        </div>
    </div>

    @if(!($stats['available'] ?? false))
        <div class="alert alert-warning shadow-sm border-0">
            <i class="mdi mdi-alert mr-2"></i> {{ $stats['message'] ?? __('mas/common.no_data') }}
        </div>
    @endif

    <!-- Summary Scorecards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">{{ __('mas/qc.total_analytes') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-dark">{{ $stats['summary']['total_records'] ?? 0 }}</h2>
                    <p class="text-info small mb-0 mt-2">{{ __('mas/qc.monitored_params') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 border-left border-success" style="border-left-width: 4px !important;">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">{{ __('mas/qc.stable_controls') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-success">{{ $stats['summary']['stable_records'] ?? 0 }}</h2>
                    <p class="text-muted small mb-0 mt-2">{{ __('mas/qc.cv_threshold') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 border-left border-danger" style="border-left-width: 4px !important;">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">{{ __('mas/qc.critical_exceptions') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-danger">{{ $stats['summary']['critical_records'] ?? 0 }}</h2>
                    <p class="text-muted small mb-0 mt-2">{{ __('mas/qc.recalibration_required') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">{{ __('mas/qc.avg_cv') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-primary">{{ $stats['summary']['avg_cv_percentage'] ?? 0 }}%</h2>
                    <p class="text-muted small mb-0 mt-2">{{ __('mas/qc.system_precision') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Parameter Performance Leaderboard -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/qc.leaderboard_title') }}</h5>
                    <span class="badge badge-success">{{ __('mas/qc.top_10') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase">{{ __('mas/qc.method_analyte') }}</th>
                                    <th class="border-0 small text-uppercase text-center">{{ __('mas/qc.total_tests') }}</th>
                                    <th class="border-0 small text-uppercase text-center">{{ __('mas/qc.pass_rate') }}</th>
                                    <th class="border-0 small text-uppercase">{{ __('mas/qc.trend') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['parameter_performance'] ?? [] as $perf)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $perf['name'] }}</td>
                                        <td class="text-center">{{ number_format($perf['total']) }}</td>
                                        <td class="text-center">
                                            <span class="font-weight-bold {{ $perf['rate'] > 95 ? 'text-success' : ($perf['rate'] > 85 ? 'text-warning' : 'text-danger') }}">
                                                {{ $perf['rate'] }}%
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 4px; width: 80px;">
                                                <div class="progress-bar {{ $perf['rate'] > 95 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $perf['rate'] }}%"></div>
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

        <!-- Testing Matrix (Stacked Bar) -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/qc.testing_matrix') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="testingMatrixStackedBar"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Stability Distribution -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="mdi mdi-chart-donut text-success mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/qc.distribution_title') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="stabilityDoughnutChart"></canvas>
                    </div>
                    <div class="mt-4">
                        @foreach($stats['status_summary'] ?? [] as $status)
                            <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                                <span class="small">
                                    <i class="mdi mdi-circle mr-2 {{ $status['stability_status'] == 'stable' ? 'text-success' : ($status['stability_status'] == 'warning' ? 'text-warning' : 'text-danger') }}"></i>
                                    {{ __('mas/qc.' . $status['stability_status']) }}
                                </span>
                                <span class="badge badge-light font-weight-bold">{{ $status['record_count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Top CV Exceptions -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="mdi mdi-alert-decagram text-warning mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/qc.top_exceptions_title') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="topCvChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exception Watchlist -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/qc.watchlist_title') }}</h5>
                    <span class="text-muted small">{{ __('mas/qc.watchlist_prioritized') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase">{{ __('mas/qc.analyte_name') }}</th>
                                    <th class="border-0 small text-uppercase">{{ __('mas/qc.sample_type') }}</th>
                                    <th class="border-0 small text-uppercase text-center">{{ __('mas/qc.robust_cv') }}</th>
                                    <th class="border-0 small text-uppercase text-center">{{ __('mas/qc.pass_rate') }}</th>
                                    <th class="border-0 small text-uppercase text-center">{{ __('mas/qc.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['exception_rows'] ?? [] as $row)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $row['analyte_name'] }}</td>
                                        <td>{{ $row['sample_type_name'] }}</td>
                                        <td class="text-center">
                                            <span class="font-weight-bold {{ $row['robust_cv_percentage'] > 10 ? 'text-danger' : ($row['robust_cv_percentage'] > 5 ? 'text-warning' : 'text-success') }}">
                                                {{ number_format($row['robust_cv_percentage'], 2) }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="small font-weight-bold">{{ number_format($row['pass_rate_pct'], 1) }}%</div>
                                            <div class="progress mt-1" style="height: 4px; width: 60px; margin: 0 auto;">
                                                <div class="progress-bar bg-info" style="width: {{ $row['pass_rate_pct'] }}%"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @php 
                                                $badgeClass = $row['stability_status'] == 'stable' ? 'badge-success' : ($row['stability_status'] == 'warning' ? 'badge-warning' : 'badge-danger');
                                            @endphp
                                            <span class="badge {{ $badgeClass }} text-uppercase" style="width: 80px;">
                                                {{ __('mas/qc.' . $row['stability_status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted italic">{{ __('mas/qc.no_exceptions') }}</td>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global Chart Defaults
        Chart.defaults.global.defaultFontFamily = "'Inter', sans-serif";
        Chart.defaults.global.defaultFontColor = '#64748b';

        // 1. Stability Distribution Doughnut
        var stabilityEl = document.getElementById('stabilityDoughnutChart');
        if (stabilityEl) {
            var stabilityCtx = stabilityEl.getContext('2d');
            window.stabilityChart = new Chart(stabilityCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($stats['charts']['status_labels'] ?? []),
                    datasets: [{
                        data: @json($stats['charts']['status_counts'] ?? []),
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 80,
                    legend: { display: false }
                }
            });
        }

        // 2. Top CV Analytes Bar Chart
        var cvEl = document.getElementById('topCvChart');
        if (cvEl) {
            var cvCtx = cvEl.getContext('2d');
            new Chart(cvCtx, {
                type: 'horizontalBar',
                data: {
                    labels: @json($stats['charts']['top_labels'] ?? []),
                    datasets: [{
                        label: "{{ __('mas/qc.robust_cv') }}",
                        data: @json($stats['charts']['top_cv_values'] ?? []),
                        backgroundColor: '{{ \App\Services\System\ThemeService::primaryColor() }}',
                        borderRadius: 4,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ 
                            ticks: { beginAtZero: true },
                            gridLines: { color: '#f1f5f9', zeroLineColor: '#f1f5f9' }
                        }],
                        yAxes: [{ gridLines: { display: false } }]
                    }
                }
            });
        }

        // 3. Testing Matrix Stacked Bar (Chart.js)
        var matrixData = @json($stats['testing_matrix'] ?? []);
        var sections = new Set();
        matrixData.forEach(function(type) {
            type.children.forEach(function(child) {
                sections.add(child.name);
            });
        });
        var sectionList = Array.from(sections);
        
        var datasets = sectionList.map(function(sec, idx) {
            var colors = ["#6366f1", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6", "#3b82f6", "#06b6d4"];
            return {
                label: sec,
                data: matrixData.map(function(type) {
                    var match = type.children.find(c => c.name === sec);
                    return match ? match.value : 0;
                }),
                backgroundColor: colors[idx % colors.length]
            };
        });

        var matrixCtx = document.getElementById('testingMatrixStackedBar').getContext('2d');
        new Chart(matrixCtx, {
            type: 'horizontalBar',
            data: {
                labels: matrixData.map(t => t.name),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } },
                scales: {
                    xAxes: [{ stacked: true, ticks: { beginAtZero: true } }],
                    yAxes: [{ stacked: true, gridLines: { display: false } }]
                }
            }
        });

    });

    function exportQcPdf(isPreview = false) {
        const chart = window.stabilityChart; 
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

<style>
    .btn-indigo { background-color: #660A0E; color: white; border: none; }
    .btn-indigo:hover { background-color: var(--color-primary-hover); color: white; }
    .btn-outline-indigo { border-color: #660A0E; color: #660A0E; background: transparent; }
    .btn-outline-indigo:hover { background-color: #660A0E; color: white; }
</style>
</div>