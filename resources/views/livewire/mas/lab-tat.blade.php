<div>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/lab.tat_title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/lab.tat_subtitle') }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportLabPdf(false)" class="btn btn-danger btn-sm">
                    <i class="mdi mdi-file-pdf"></i> {{ __('mas/common.download') }} {{ __('mas/common.report') }}
                </button>
                <button onclick="exportLabPdf(true)" class="btn btn-outline-danger btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> {{ __('mas/common.preview') }}
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
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.workflow_distribution') }}</h5>
                    <span class="badge badge-primary">{{ __('mas/common.live_data') }}</span>
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
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.efficiency_monitoring') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-6 border-right">
                            <h2 class="font-weight-bold text-success mb-0">{{ $stats['summary']['active_batches'] }}</h2>
                            <p class="text-muted text-uppercase x-small">{{ __('mas/dashboard.active_batches') }}</p>
                        </div>
                        <div class="col-6">
                            <h2 class="font-weight-bold text-info mb-0">{{ $stats['summary']['avg_completion_days'] ?? '4.2' }}</h2>
                            <p class="text-muted text-uppercase x-small">{{ __('mas/lab.avg_days_tat') }}</p>
                        </div>
                    </div>

                    <div class="row text-center mb-4 bg-light mx-0 py-3 rounded">
                        <div class="col-6 border-right">
                            <div class="font-weight-bold text-dark h5 mb-0">{{ $stats['summary']['tests_completed'] ?? 0 }}</div>
                            <div class="text-muted x-small uppercase">{{ __('mas/lab.tests_completed') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="font-weight-bold text-primary h5 mb-0">{{ $stats['summary']['tests_requested'] ?? 0 }}</div>
                            <div class="text-muted x-small uppercase">{{ __('mas/lab.total_requested') }}</div>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small uppercase font-weight-bold">{{ __('mas/lab.batch_sla_performance') }}</span>
                            <span class="text-danger font-weight-bold small">{{ __('mas/lab.overdue_count', ['count' => $stats['summary']['overdue_batches']]) }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $total = $stats['summary']['active_batches'] ?: 1;
                                $onTime = $total - $stats['summary']['overdue_batches'];
                                $percent = round(($onTime / $total) * 100);
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="text-center mt-2 mb-0 x-small text-muted">{{ __('mas/lab.batches_within_target', ['percent' => $percent]) }}</p>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-dark mb-3">{{ __('mas/lab.target_distribution') }}</h6>
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
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.aging_health') }}</h5>
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
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.completion_ratio') }}</h5>
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
                        <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.throughput_volume') }}</h5>
                        <span class="text-muted x-small uppercase">{{ $stats['period_label'] ?? __('mas/common.active') }}</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle font-weight-bold" type="button" data-toggle="dropdown">
                            <i class="mdi mdi-filter-variant"></i> 
                            {{ ucfirst($stats['period'] ?? __('mas/common.active')) }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'active' ? 'active' : '' }}" href="?period=active">{{ __('mas/common.active') }}</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'week' ? 'active' : '' }}" href="?period=week">{{ __('mas/common.past_week') }}</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'month' ? 'active' : '' }}" href="?period=month">{{ __('mas/common.past_month') }}</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'year' ? 'active' : '' }}" href="?period=year">{{ __('mas/common.past_year') }}</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'lifetime' ? 'active' : '' }}" href="?period=lifetime">{{ __('mas/common.lifetime') }}</a>
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

    <!-- Operational Cockpit (Smart Action Grid) -->
    <div class="row" x-data="{ currentTab: 'my_tasks' }">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.cockpit_title') }}</h5>
                        <p class="text-muted small mb-0">{{ __('mas/lab.cockpit_subtitle') }}</p>
                    </div>
                    <div class="btn-group btn-group-toggle shadow-sm" data-toggle="buttons">
                        <label class="btn btn-outline-primary btn-sm active" onclick="@this.set('stats.smart_grid', @this.getSmartGridData('my_tasks')); currentTab = 'my_tasks'" x-on:click="currentTab = 'my_tasks'">
                            <input type="radio" name="options" id="option1" checked> {{ __('mas/lab.my_tasks') }}
                        </label>
                        <label class="btn btn-outline-primary btn-sm" onclick="@this.set('stats.smart_grid', @this.getSmartGridData('urgent')); currentTab = 'urgent'" x-on:click="currentTab = 'urgent'">
                            <input type="radio" name="options" id="option2"> {{ __('mas/lab.urgent') }}
                        </label>
                        <label class="btn btn-outline-primary btn-sm" onclick="@this.set('stats.smart_grid', @this.getSmartGridData('approvals')); currentTab = 'approvals'" x-on:click="currentTab = 'approvals'">
                            <input type="radio" name="options" id="option3"> {{ __('mas/lab.approvals') }}
                        </label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.batch_code') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.client') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.sample_type') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold text-center">{{ __('mas/lab.priority') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.status') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold text-center">{{ __('mas/lab.action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="smartGridBody">
                                @forelse($stats['smart_grid'] ?? [] as $item)
                                    <tr class="{{ $item['priority'] === 'Urgent' ? 'table-warning' : '' }}">
                                        <td class="font-weight-bold">{{ $item['batch_code'] }}</td>
                                        <td class="small">{{ $item['client'] }}</td>
                                        <td class="small">{{ $item['type'] }}</td>
                                        <td class="text-center">
                                            @if($item['priority'] === 'Urgent')
                                                <span class="badge badge-danger">{{ __('mas/lab.urgent') }}</span>
                                            @else
                                                <span class="badge badge-light border">{{ $item['priority'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small text-muted">
                                                <i class="mdi mdi-circle-small text-{{ $item['priority'] === 'Urgent' ? 'danger' : 'primary' }}"></i>
                                                {{ $item['status'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-white btn-sm border shadow-sm">
                                                <i class="mdi mdi-open-in-new text-primary"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="mdi mdi-check-circle-outline h2 d-block"></i>
                                                {{ __('mas/lab.all_clear') }}
                                            </div>
                                        </td>
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