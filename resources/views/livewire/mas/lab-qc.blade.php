<div>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">QC Stability Analytics</h1>
            <p class="text-muted small mb-0">Quality Control performance monitoring and stability tracking.</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportQcPdf(false)" class="btn btn-indigo btn-sm">
                    <i class="mdi mdi-file-pdf"></i> Download Report
                </button>
                <button onclick="exportQcPdf(true)" class="btn btn-outline-indigo btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> Preview
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
            <i class="mdi mdi-alert mr-2"></i> {{ $stats['message'] ?? 'QC stability data is currently unavailable.' }}
        </div>
    @endif

    <!-- Summary Scorecards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">Total QC Analytes</h6>
                    <h2 class="font-weight-bold mb-0 text-dark">{{ $stats['summary']['total_records'] ?? 0 }}</h2>
                    <p class="text-info small mb-0 mt-2">Monitored parameters</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 border-left border-success" style="border-left-width: 4px !important;">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">Stable Controls</h6>
                    <h2 class="font-weight-bold mb-0 text-success">{{ $stats['summary']['stable_records'] ?? 0 }}</h2>
                    <p class="text-muted small mb-0 mt-2">Within 5% CV threshold</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 border-left border-danger" style="border-left-width: 4px !important;">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">Critical Exceptions</h6>
                    <h2 class="font-weight-bold mb-0 text-danger">{{ $stats['summary']['critical_records'] ?? 0 }}</h2>
                    <p class="text-muted small mb-0 mt-2">Requires immediate recalibration</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted mb-2 font-weight-bold">Avg. Robust CV%</h6>
                    <h2 class="font-weight-bold mb-0 text-primary">{{ $stats['summary']['avg_cv_percentage'] ?? 0 }}%</h2>
                    <p class="text-muted small mb-0 mt-2">System-wide precision</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Stability Distribution -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Stability Status Distribution</h5>
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
                                    {{ $status['label'] }}
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
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">High Variation Analytes (CV%)</h5>
                </div>
                <div class="card-body">
                    <canvas id="topCvChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Exception Watchlist -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">Stability Watchlist</h5>
                    <span class="text-muted small">Prioritized by Variation Level</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase">Analyte Name</th>
                                    <th class="border-0 small text-uppercase">Sample Type</th>
                                    <th class="border-0 small text-uppercase text-center">Robust CV%</th>
                                    <th class="border-0 small text-uppercase text-center">Pass Rate</th>
                                    <th class="border-0 small text-uppercase text-center">Status</th>
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
                                                {{ $row['stability_status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted italic">No QC exceptions detected. Systems are stable.</td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global Chart Defaults
        Chart.defaults.global.defaultFontFamily = "'Inter', sans-serif";
        Chart.defaults.global.defaultFontColor = '#64748b';

        // 1. Stability Distribution Doughnut
        var stabilityCtx = document.getElementById('stabilityDoughnutChart').getContext('2d');
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

        // 2. Top CV Analytes Bar Chart
        var cvCtx = document.getElementById('topCvChart').getContext('2d');
        new Chart(cvCtx, {
            type: 'horizontalBar',
            data: {
                labels: @json($stats['charts']['top_labels'] ?? []),
                datasets: [{
                    label: 'CV%',
                    data: @json($stats['charts']['top_cv_values'] ?? []),
                    backgroundColor: '#6366f1',
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
    });

    function exportQcPdf(isPreview = false) {
        // We capture the stability distribution chart for this report
        const chart = window.stabilityChart; 
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

<style>
    .btn-indigo { background-color: #6366f1; color: white; border: none; }
    .btn-indigo:hover { background-color: #4f46e5; color: white; }
    .btn-outline-indigo { border-color: #6366f1; color: #6366f1; background: transparent; }
    .btn-outline-indigo:hover { background-color: #6366f1; color: white; }
</style>
</div>