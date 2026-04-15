<div>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">Laboratory TAT Analysis</h1>
            <p class="text-muted small mb-0">Turnaround time monitoring and SLA compliance tracking.</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportLabPdf(false)" class="btn btn-danger btn-sm">
                    <i class="mdi mdi-file-pdf"></i> Download Report
                </button>
                <button onclick="exportLabPdf(true)" class="btn btn-outline-danger btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> Preview
                </button>
            </div>
            
            <form id="pdfExportForm" action="{{ route('mas.export.visuals', 'lab') }}" method="POST" style="display:none">
                @csrf
                <input type="hidden" name="chart_image" id="chart_image_input">
                <input type="hidden" name="preview" id="preview_input" value="false">
                <input type="hidden" name="period" value="{{ $stats['period'] ?? 'active' }}">
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Lab Workflow Chart -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">Workflow Stage Distribution</h5>
                    <span class="badge badge-primary">Live Data</span>
                </div>
                <div class="card-body">
                    <canvas id="labWorkflowChart" height="400"></canvas>
                </div>
            </div>
        </div>

        <!-- Overview Panel -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Efficiency Monitoring</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-6 border-right">
                            <h2 class="font-weight-bold text-success mb-0">{{ $stats['summary']['active_batches'] }}</h2>
                            <p class="text-muted text-uppercase x-small">Active Batches</p>
                        </div>
                        <div class="col-6">
                            <h2 class="font-weight-bold text-info mb-0">{{ $stats['summary']['avg_completion_days'] ?? '4.2' }}</h2>
                            <p class="text-muted text-uppercase x-small">Avg. Days (TAT)</p>
                        </div>
                    </div>

                    <div class="row text-center mb-4 bg-light mx-0 py-3 rounded">
                        <div class="col-6 border-right">
                            <div class="font-weight-bold text-dark h5 mb-0">{{ $stats['summary']['tests_completed'] ?? 0 }}</div>
                            <div class="text-muted x-small uppercase">Tests Completed</div>
                        </div>
                        <div class="col-6">
                            <div class="font-weight-bold text-primary h5 mb-0">{{ $stats['summary']['tests_requested'] ?? 0 }}</div>
                            <div class="text-muted x-small uppercase">Total Requested</div>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small uppercase font-weight-bold">Batch SLA Performance</span>
                            <span class="text-danger font-weight-bold small">{{ $stats['summary']['overdue_batches'] }} Overdue</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $total = $stats['summary']['active_batches'] ?: 1;
                                $onTime = $total - $stats['summary']['overdue_batches'];
                                $percent = round(($onTime / $total) * 100);
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="text-center mt-2 mb-0 x-small text-muted">{{ $percent }}% of batches within target</p>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-dark mb-3">Target Distribution</h6>
                    <ul class="list-group list-group-flush">
                        @foreach(array_slice($stats['stage_counts'], 0, 5) as $status => $count)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-1">
                                <span class="text-muted small">
                                    <i class="mdi mdi-circle-medium text-primary"></i> 
                                    {{ $status }}
                                </span>
                                <span class="badge badge-pill badge-light font-weight-bold">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Aging Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Aging Health Distribution</h5>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="agingDoughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Completion Ratio -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Test Completion Ratio</h5>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="completionRatioChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Throughput Volume -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">Throughput Volume (Analytes)</h5>
                        <span class="text-muted x-small uppercase">{{ $stats['period_label'] ?? 'Active Workload' }}</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle font-weight-bold" type="button" data-toggle="dropdown">
                            <i class="mdi mdi-filter-variant"></i> 
                            {{ ucfirst($stats['period'] ?? 'Active') }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'active' ? 'active' : '' }}" href="?period=active">Active Workload</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'week' ? 'active' : '' }}" href="?period=week">Past Week</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'month' ? 'active' : '' }}" href="?period=month">Past Month</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'year' ? 'active' : '' }}" href="?period=year">Past Year</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'lifetime' ? 'active' : '' }}" href="?period=lifetime">Lifetime</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="throughputVolumeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Watchlist -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark">Critical Overdue Watchlist</h5>
                    <span class="text-muted small">Top {{ count($stats['overdue_batches']) }} High Risk Items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase">Batch Code</th>
                                    <th class="border-0 small text-uppercase">Workflow Stage</th>
                                    <th class="border-0 small text-uppercase">Target Date</th>
                                    <th class="border-0 small text-uppercase text-center">Days Overdue</th>
                                    <th class="border-0 small text-uppercase text-center">Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['overdue_batches'] as $batch)
                                    <tr>
                                        <td class="font-weight-bold text-primary">{{ $batch['batch_code'] }}</td>
                                        <td>{{ $batch['workflow_stage'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($batch['target_date'])->format('M d, Y') }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-danger px-3">{{ $batch['days_overdue'] }} Days</span>
                                        </td>
                                        <td class="text-center">
                                            @if($batch['days_overdue'] > 7)
                                                <i class="mdi mdi-alert-circle text-danger" title="Critical Delay"></i>
                                            @else
                                                <i class="mdi mdi-alert text-warning" title="Warning"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted italic">No critical overdue batches detected.</td>
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

<style>
    .x-small { font-size: 10px; }
    .uppercase { text-transform: uppercase; }
</style>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global Chart Defaults
        Chart.defaults.global.defaultFontFamily = "'Inter', sans-serif";
        Chart.defaults.global.defaultFontColor = '#64748b';

        // 1. Lab Workflow Stage Chart
        var labCtx = document.getElementById('labWorkflowChart').getContext('2d');
        window.labChart = new Chart(labCtx, {
            type: 'bar',
            plugins: [{
                afterDatasetsDraw: function(chart) {
                    var ctx = chart.ctx;
                    chart.data.datasets.forEach(function(dataset, i) {
                        var meta = chart.getDatasetMeta(i);
                        if (!meta.hidden) {
                            meta.data.forEach(function(element, index) {
                                // Draw the text in the middle of the bar
                                ctx.fillStyle = '#ffffff';
                                var fontSize = 12;
                                var fontStyle = 'bold';
                                var fontFamily = "'Inter', sans-serif";
                                ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

                                var dataString = dataset.data[index].toString();
                                if (dataString === '0') return; // Don't show zero values

                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';

                                var padding = 5;
                                var position = element.tooltipPosition();
                                // If the bar is too short, put text above it
                                if (element._view.y > chart.chartArea.bottom - 20) {
                                    ctx.fillStyle = '#1e293b';
                                    ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
                                } else {
                                    ctx.fillText(dataString, position.x, position.y + (fontSize / 2) + padding);
                                }
                            });
                        }
                    });
                }
            }],
            data: {
                labels: @json($stats['charts']['stage_labels']),
                datasets: [{
                    label: 'Batch Count',
                    data: @json($stats['charts']['stage_totals']),
                    backgroundColor: '#4f46e5',
                    hoverBackgroundColor: '#4338ca',
                    borderRadius: 4
                }, {
                    label: 'Overdue',
                    data: @json($stats['charts']['stage_overdue']),
                    backgroundColor: '#ef4444',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { 
                    display: true, 
                    position: 'top', 
                    align: 'end', 
                    labels: { 
                        boxWidth: 15, 
                        fontSize: 12, 
                        fontStyle: 'bold', 
                        usePointStyle: true,
                        padding: 20
                    } 
                },
                scales: {
                    xAxes: [{ 
                        gridLines: { display: false }, 
                        ticks: { fontSize: 11, fontStyle: 'bold' },
                        scaleLabel: { display: true, labelString: 'Laboratory Workflow Stages', fontSize: 12, fontStyle: 'bold' }
                    }],
                    yAxes: [{ 
                        gridLines: { color: '#f1f5f9' }, 
                        ticks: { beginAtZero: true, stepSize: 5, fontSize: 11 },
                        scaleLabel: { display: true, labelString: 'Batch Count', fontSize: 12, fontStyle: 'bold' }
                    }]
                }
            }
        });

        // 2. Aging Distribution Doughnut
        var agingCtx = document.getElementById('agingDoughnutChart').getContext('2d');
        new Chart(agingCtx, {
            type: 'doughnut',
            data: {
                labels: @json($stats['charts']['aging_labels']),
                datasets: [{
                    data: @json($stats['charts']['aging_counts']),
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#7f1d1d', '#94a3b8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 70,
                legend: { display: true, position: 'right', labels: { boxWidth: 12, fontSize: 11 } }
            }
        });

        // 3. Test Completion Ratio Doughnut
        var completionCtx = document.getElementById('completionRatioChart').getContext('2d');
        window.completionChart = new Chart(completionCtx, {
            type: 'doughnut',
            data: {
                labels: @json($stats['charts']['completion_labels']),
                datasets: [{
                    data: @json($stats['charts']['completion_counts']),
                    backgroundColor: ['#4f46e5', '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 75,
                legend: { display: true, position: 'right', labels: { boxWidth: 12, fontSize: 11 } }
            }
        });

        // 4. Throughput Volume Bar
        var throughputCtx = document.getElementById('throughputVolumeChart').getContext('2d');
        window.throughputChart = new Chart(throughputCtx, {
            type: 'horizontalBar',
            data: {
                labels: @json($stats['charts']['throughput_labels']),
                datasets: [{
                    label: 'Tests Processed',
                    data: @json($stats['charts']['throughput_counts']),
                    backgroundColor: '#6366f1',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ gridLines: { display: false }, ticks: { beginAtZero: true, fontSize: 10 } }],
                    yAxes: [{ gridLines: { display: false }, ticks: { fontSize: 10 } }]
                }
            }
        });

    });

    function exportLabPdf(isPreview = false) {
        // Collect multiple chart snapshots if needed, but the current PDF template 
        // mainly focuses on the workflow distribution. We'll send the primary workflow chart.
        const chart = window.labChart;
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
</div>