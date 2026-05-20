<div class="registry-dashboard">
    <style>
        .registry-dashboard .registry-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .registry-dashboard .registry-card-header {
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 14px 14px 0 0;
            padding: 0.85rem 1.25rem;
            font-weight: 600;
            color: #334155;
        }
        .registry-dashboard .registry-card-body { padding: 1.25rem; }
        .registry-dashboard .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            height: 100%;
        }
        .registry-dashboard .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        }
        .registry-dashboard .kpi-card-body { padding: 1rem 1.15rem; }
        .registry-dashboard .kpi-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .registry-dashboard .kpi-card-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
            line-height: 1.1;
        }
        .registry-dashboard .kpi-card-label {
            margin: 0.35rem 0 0;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .registry-dashboard .kpi-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            background: #fff;
        }
        .registry-dashboard .filter-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }
        .registry-dashboard .quick-action-btn {
            border-radius: 10px;
            font-weight: 500;
        }
        .registry-dashboard .activity-item {
            padding: 0.65rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .registry-dashboard .activity-item:last-child { border-bottom: none; }
        .registry-dashboard .chart-wrap { position: relative; height: 260px; }
        .registry-dashboard .chart-wrap--category { height: 320px; }
    </style>

    {{-- Page title & description --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-email-multiple text-primary"></i>
                                Registry Dashboard
                            </h2>
                            <p class="text-muted mb-0" style="max-width: 640px;">
                                Monitor correspondence intake, workflow progress, approvals, and turnaround performance across all request categories.
                            </p>
                        </div>
                        <div class="text-md-right">
                            <p class="mb-0 text-primary font-weight-medium">
                                <i class="mdi mdi-calendar"></i>
                                {{ now()->format('l, F j, Y') }}
                            </p>
                            <small class="text-muted">SLA compliance: {{ $slaCompliance }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Date filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card p-3">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small text-muted font-weight-bold mb-1">From</label>
                        <input type="date" wire:model.live="startDate" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted font-weight-bold mb-1">To</label>
                        <input type="date" wire:model.live="endDate" class="form-control">
                    </div>
                    <div class="col-md-6 d-flex flex-wrap gap-2 justify-content-md-end">
                        @can('registry.components.requests.add')
                        <a href="{{ route('registry.requests.create') }}" class="btn btn-primary quick-action-btn">
                            <i class="mdi mdi-plus"></i> New Request
                        </a>
                        @endcan
                        @can('registry.components.approval queue.view')
                        <a href="{{ route('registry.approvals.index') }}" class="btn btn-outline-primary quick-action-btn">
                            <i class="mdi mdi-check-decagram"></i> Approvals
                        </a>
                        @endcan
                        @can('registry.components.correspondence register.view')
                        <a href="{{ route('registry.correspondence.index') }}" class="btn btn-outline-secondary quick-action-btn">
                            <i class="mdi mdi-book-open-page-variant"></i> Register
                        </a>
                        @endcan
                        @can('registry.components.requests.view')
                        <a href="{{ route('registry.approved.index') }}" class="btn btn-outline-secondary quick-action-btn">
                            <i class="mdi mdi-check-all"></i> Approved
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI row 1 --}}
    <div class="row mb-3">
        @php
            $kpiRow1 = [
                ['key' => 'total', 'label' => 'Total Requests', 'icon' => 'mdi-email-multiple', 'color' => '#1565C0', 'bg' => '#e3f2fd'],
                ['key' => 'open', 'label' => 'Open', 'icon' => 'mdi-folder-open', 'color' => '#0288d1', 'bg' => '#e1f5fe'],
                ['key' => 'pending_approval', 'label' => 'Pending Approvals', 'icon' => 'mdi-clock-alert', 'color' => '#f57c00', 'bg' => '#fff3e0'],
                ['key' => 'completed', 'label' => 'Completed', 'icon' => 'mdi-check-circle', 'color' => '#2e7d32', 'bg' => '#e8f5e9'],
            ];
        @endphp
        @foreach($kpiRow1 as $kpi)
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="kpi-card" style="border-left: 4px solid {{ $kpi['color'] }};">
                <div class="kpi-card-body">
                    <div class="kpi-card-row">
                        <h4 class="kpi-card-value">{{ $kpis[$kpi['key']] ?? 0 }}</h4>
                        <div class="kpi-card-icon" style="background: {{ $kpi['bg'] }};">
                            <i class="mdi {{ $kpi['icon'] }}" style="color: {{ $kpi['color'] }};"></i>
                        </div>
                    </div>
                    <p class="kpi-card-label">{{ $kpi['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- KPI row 2 --}}
    <div class="row mb-4">
        @php
            $kpiRow2 = [
                ['key' => 'delayed', 'label' => 'Delayed', 'icon' => 'mdi-alert', 'color' => '#c62828', 'bg' => '#ffebee'],
                ['key' => 'received_today', 'label' => 'Received Today', 'icon' => 'mdi-calendar-today', 'color' => '#6a1b9a', 'bg' => '#f3e5f5'],
                ['key' => 'received_month', 'label' => 'This Month', 'icon' => 'mdi-calendar-month', 'color' => '#455a64', 'bg' => '#eceff1'],
            ];
            $kpiRow2[] = ['key' => '_tat', 'label' => 'Avg TAT (hrs)', 'icon' => 'mdi-timer-outline', 'color' => '#00838f', 'bg' => '#e0f7fa', 'value' => $avgTatHours];
        @endphp
        @foreach($kpiRow2 as $kpi)
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="kpi-card" style="border-left: 4px solid {{ $kpi['color'] }};">
                <div class="kpi-card-body">
                    <div class="kpi-card-row">
                        <h4 class="kpi-card-value">{{ $kpi['value'] ?? ($kpis[$kpi['key']] ?? 0) }}</h4>
                        <div class="kpi-card-icon" style="background: {{ $kpi['bg'] }};">
                            <i class="mdi {{ $kpi['icon'] }}" style="color: {{ $kpi['color'] }};"></i>
                        </div>
                    </div>
                    <p class="kpi-card-label">{{ $kpi['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <div class="registry-card h-100">
                <div class="registry-card-header">
                    <i class="mdi mdi-chart-bar"></i> Requests by Category
                </div>
                <div class="registry-card-body">
                    <div class="chart-wrap chart-wrap--category">
                        <canvas id="registryCategoryChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="registry-card h-100">
                <div class="registry-card-header">
                    <i class="mdi mdi-chart-pie"></i> Request Status Overview
                </div>
                <div class="registry-card-body">
                    <div class="chart-wrap">
                        <canvas id="registryStatusChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="registry-card h-100">
                <div class="registry-card-header">
                    <i class="mdi mdi-chart-bar"></i> Average Stage Turnaround (hours)
                </div>
                <div class="registry-card-body">
                    <div class="chart-wrap">
                        <canvas id="registryStageChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="registry-card h-100 mb-3">
                <div class="registry-card-header">
                    <i class="mdi mdi-clipboard-list"></i> Pending Tasks
                </div>
                <div class="registry-card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($pendingTasks as $item)
                        <li class="list-group-item bg-transparent activity-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('registry.requests.show', $item->id) }}" class="text-dark">{{ $item->reference_no }}</a>
                            <span class="badge badge-warning badge-pill">{{ str_replace('_', ' ', $item->status) }}</span>
                        </li>
                        @empty
                        <li class="list-group-item bg-transparent text-muted">No pending tasks.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="registry-card">
                <div class="registry-card-header">
                    <i class="mdi mdi-history"></i> Recent Activity
                </div>
                <div class="registry-card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($activities as $item)
                        <li class="list-group-item bg-transparent activity-item">
                            <a href="{{ route('registry.requests.show', $item->id) }}" class="font-weight-medium">{{ $item->reference_no }}</a>
                            <div class="small text-muted">{{ Str::limit($item->subject, 45) }}</div>
                            <small class="text-muted">{{ $item->updated_at?->diffForHumans() }}</small>
                        </li>
                        @empty
                        <li class="list-group-item bg-transparent text-muted">No recent activity.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @php
        $chartConfig = [
            'categories' => [
                'labels' => collect($byCategory)->pluck('category')->values()->all(),
                'values' => collect($byCategory)->pluck('total')->values()->all(),
            ],
            'status' => [
                'labels' => ['Open', 'Pending Approval', 'Completed', 'Delayed'],
                'values' => [
                    (int) ($kpis['open'] ?? 0),
                    (int) ($kpis['pending_approval'] ?? 0),
                    (int) ($kpis['completed'] ?? 0),
                    (int) ($kpis['delayed'] ?? 0),
                ],
            ],
            'stages' => [
                'labels' => collect($stageMetrics)->pluck('stage')->values()->all(),
                'values' => collect($stageMetrics)->pluck('avg_hours')->values()->all(),
            ],
        ];
    @endphp

    <div id="registry-chart-config" class="d-none" data-config='@json($chartConfig)' wire:key="registry-charts-{{ $startDate }}-{{ $endDate }}"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        (function () {
            let categoryChart = null;
            let statusChart = null;
            let stageChart = null;

            const palette = ['#1565C0', '#42a5f5', '#26a69a', '#66bb6a', '#ffa726', '#ab47bc', '#78909c'];

            function readChartData() {
                const el = document.getElementById('registry-chart-config');
                if (!el || !el.dataset.config) {
                    return { categories: { labels: [], values: [] }, status: { labels: [], values: [] }, stages: { labels: [], values: [] } };
                }
                return JSON.parse(el.dataset.config);
            }

            window.initRegistryDashboardCharts = function () {
                if (typeof Chart === 'undefined') return;

                const chartData = readChartData();
                const catCtx = document.getElementById('registryCategoryChart');
                if (catCtx) {
                    if (categoryChart) categoryChart.destroy();
                    categoryChart = new Chart(catCtx, {
                        type: 'bar',
                        data: {
                            labels: chartData.categories.labels.length ? chartData.categories.labels : ['No data'],
                            datasets: [{
                                label: 'Requests',
                                data: chartData.categories.values.length ? chartData.categories.values : [0],
                                backgroundColor: palette,
                                borderRadius: 8,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, precision: 0 },
                                    title: { display: true, text: 'Count' },
                                },
                                x: {
                                    ticks: { maxRotation: 45, minRotation: 0 },
                                },
                            },
                            plugins: { legend: { display: false } },
                        },
                    });
                }

                const statusCtx = document.getElementById('registryStatusChart');
                if (statusCtx) {
                    if (statusChart) statusChart.destroy();
                    statusChart = new Chart(statusCtx, {
                        type: 'pie',
                        data: {
                            labels: chartData.status.labels,
                            datasets: [{
                                data: chartData.status.values,
                                backgroundColor: ['#0288d1', '#f57c00', '#2e7d32', '#c62828'],
                                borderWidth: 2,
                                borderColor: '#fff',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                        },
                    });
                }

                const stageCtx = document.getElementById('registryStageChart');
                if (stageCtx) {
                    if (stageChart) stageChart.destroy();
                    stageChart = new Chart(stageCtx, {
                        type: 'bar',
                        data: {
                            labels: chartData.stages.labels.length ? chartData.stages.labels : ['No data'],
                            datasets: [{
                                label: 'Avg hours',
                                data: chartData.stages.values.length ? chartData.stages.values : [0],
                                backgroundColor: 'rgba(21, 101, 192, 0.75)',
                                borderRadius: 8,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Hours' } },
                            },
                            plugins: { legend: { display: false } },
                        },
                    });
                }
            };

            document.addEventListener('DOMContentLoaded', window.initRegistryDashboardCharts);
            document.addEventListener('livewire:navigated', window.initRegistryDashboardCharts);
            document.addEventListener('registry-charts-updated', window.initRegistryDashboardCharts);
        })();
    </script>
</div>
