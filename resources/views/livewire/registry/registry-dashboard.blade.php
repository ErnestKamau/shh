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
        .registry-dashboard {
            --rd-primary: #2563eb;
            --rd-primary-soft: #eff6ff;
            --rd-slate-50: #f8fafc;
            --rd-slate-100: #f1f5f9;
            --rd-slate-200: #e2e8f0;
            --rd-slate-500: #64748b;
            --rd-slate-800: #1e293b;
        }
        .registry-dashboard .rd-section {
            margin-bottom: 1.5rem;
        }
        .registry-dashboard .rd-section__head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .registry-dashboard .rd-section__eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--rd-slate-500);
            margin: 0 0 0.2rem;
        }
        .registry-dashboard .rd-section__title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--rd-slate-800);
            margin: 0;
        }
        .registry-dashboard .rd-panel {
            background: #fff;
            border: 1px solid var(--rd-slate-200);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            height: 100%;
            overflow: hidden;
        }
        .registry-dashboard .rd-panel__head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, var(--rd-slate-50) 0%, var(--rd-primary-soft) 100%);
            border-bottom: 1px solid var(--rd-slate-200);
        }
        .registry-dashboard .rd-panel__icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid var(--rd-slate-200);
            color: var(--rd-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .registry-dashboard .rd-panel__title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--rd-slate-800);
            margin: 0;
        }
        .registry-dashboard .rd-panel__sub {
            font-size: 0.78rem;
            color: var(--rd-slate-500);
            margin: 0.1rem 0 0;
        }
        .registry-dashboard .rd-panel__body {
            padding: 1.25rem;
        }
        .registry-dashboard .rd-panel__body--flush {
            padding: 0;
        }
        .registry-dashboard .rd-chart-wrap {
            position: relative;
            height: 280px;
        }
        .registry-dashboard .rd-chart-wrap--tall {
            height: 300px;
        }
        .registry-dashboard .rd-chart-wrap--category { height: 320px; }
        .registry-dashboard .rd-status-legend {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
            margin-top: 1rem;
        }
        .registry-dashboard .rd-status-legend__item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.65rem;
            background: var(--rd-slate-50);
            border: 1px solid var(--rd-slate-200);
            border-radius: 10px;
            font-size: 0.8rem;
        }
        .registry-dashboard .rd-status-legend__dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .registry-dashboard .rd-status-legend__value {
            margin-left: auto;
            font-weight: 700;
            color: var(--rd-slate-800);
        }
        .registry-dashboard .rd-feed {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .registry-dashboard .rd-feed__item {
            display: block;
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid var(--rd-slate-200);
            text-decoration: none;
            color: inherit;
            transition: background 0.15s ease;
        }
        .registry-dashboard .rd-feed__item:hover {
            background: var(--rd-primary-soft);
            text-decoration: none;
            color: inherit;
        }
        .registry-dashboard .rd-feed__item:last-child {
            border-bottom: none;
        }
        .registry-dashboard .rd-feed__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.35rem;
        }
        .registry-dashboard .rd-feed__ref {
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--rd-primary);
        }
        .registry-dashboard .rd-feed__subject {
            font-size: 0.82rem;
            color: var(--rd-slate-500);
            margin: 0 0 0.35rem;
            line-height: 1.4;
        }
        .registry-dashboard .rd-feed__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--rd-slate-500);
        }
        .registry-dashboard .rd-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .registry-dashboard .rd-badge--open {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .registry-dashboard .rd-badge--pending {
            background: #ffedd5;
            color: #c2410c;
        }
        .registry-dashboard .rd-badge--priority-high,
        .registry-dashboard .rd-badge--priority-urgent {
            background: #fee2e2;
            color: #b91c1c;
        }
        .registry-dashboard .rd-badge--category {
            background: var(--rd-slate-100);
            color: #475569;
        }
        .registry-dashboard .rd-empty {
            padding: 2.5rem 1.25rem;
            text-align: center;
            color: var(--rd-slate-500);
        }
        .registry-dashboard .rd-empty__icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 0.75rem;
            border-radius: 14px;
            background: var(--rd-primary-soft);
            color: var(--rd-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .registry-dashboard .rd-panel__footer-link {
            display: block;
            padding: 0.75rem 1.25rem;
            text-align: center;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--rd-primary);
            background: var(--rd-slate-50);
            border-top: 1px solid var(--rd-slate-200);
            text-decoration: none;
        }
        .registry-dashboard .rd-panel__footer-link:hover {
            background: var(--rd-primary-soft);
            text-decoration: none;
        }
        .registry-dashboard .rd-activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--rd-primary);
            margin-top: 0.35rem;
            flex-shrink: 0;
        }
        .registry-dashboard .rd-activity-row {
            display: flex;
            gap: 0.65rem;
        }
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
    {{-- Analytics: status, turnaround & activity --}}
    <section class="rd-section">
        <div class="rd-section__head">
            <div>
                <p class="rd-section__eyebrow">Workflow analytics</p>
                <h3 class="rd-section__title">Status, turnaround &amp; activity</h3>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-5">
                <div class="rd-panel">
                    <div class="rd-panel__head">
                        <span class="rd-panel__icon"><i class="mdi mdi-chart-donut"></i></span>
                        <div>
                            <h4 class="rd-panel__title">Request status overview</h4>
                            <p class="rd-panel__sub">Distribution of open, pending, completed &amp; delayed</p>
                        </div>
                    </div>
                    <div class="rd-panel__body">
                        <div class="rd-chart-wrap">
                            <canvas id="registryStatusChart" wire:ignore></canvas>
                        </div>
                        @php
                            $statusLegend = [
                                ['label' => 'Open', 'key' => 'open', 'color' => '#0288d1'],
                                ['label' => 'Pending approval', 'key' => 'pending_approval', 'color' => '#f57c00'],
                                ['label' => 'Completed', 'key' => 'completed', 'color' => '#2e7d32'],
                                ['label' => 'Delayed', 'key' => 'delayed', 'color' => '#c62828'],
                            ];
                        @endphp
                        <div class="rd-status-legend">
                            @foreach($statusLegend as $item)
                                <div class="rd-status-legend__item">
                                    <span class="rd-status-legend__dot" style="background: {{ $item['color'] }};"></span>
                                    <span>{{ $item['label'] }}</span>
                                    <span class="rd-status-legend__value">{{ $kpis[$item['key']] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="rd-panel">
                    <div class="rd-panel__head">
                        <span class="rd-panel__icon"><i class="mdi mdi-timer-sand"></i></span>
                        <div>
                            <h4 class="rd-panel__title">Average stage turnaround</h4>
                            <p class="rd-panel__sub">Mean hours spent per workflow stage</p>
                        </div>
                    </div>
                    <div class="rd-panel__body">
                        <div class="rd-chart-wrap rd-chart-wrap--tall">
                            <canvas id="registryStageChart" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="rd-panel">
                    <div class="rd-panel__head">
                        <span class="rd-panel__icon"><i class="mdi mdi-clipboard-clock-outline"></i></span>
                        <div>
                            <h4 class="rd-panel__title">Pending tasks</h4>
                            <p class="rd-panel__sub">Requests awaiting action or approval</p>
                        </div>
                    </div>
                    <div class="rd-panel__body rd-panel__body--flush">
                        @forelse($pendingTasks as $item)
                            <a href="{{ route('registry.requests.show', $item->id) }}" class="rd-feed__item">
                                <div class="rd-feed__top">
                                    <span class="rd-feed__ref">{{ $item->reference_no }}</span>
                                    <span class="rd-badge {{ $item->status === 'pending_approval' ? 'rd-badge--pending' : 'rd-badge--open' }}">
                                        {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </div>
                                <p class="rd-feed__subject mb-0">{{ Str::limit($item->subject, 52) }}</p>
                                <div class="rd-feed__meta">
                                    @if($item->category)
                                        <span class="rd-badge rd-badge--category">{{ $item->category->name }}</span>
                                    @endif
                                    @if(in_array($item->priority, ['high', 'urgent'], true))
                                        <span class="rd-badge rd-badge--priority-{{ $item->priority }}">{{ ucfirst($item->priority) }}</span>
                                    @endif
                                    @if($item->current_stage)
                                        <span><i class="mdi mdi-source-branch"></i> {{ ucwords(str_replace('_', ' ', $item->current_stage)) }}</span>
                                    @endif
                                    <span><i class="mdi mdi-clock-outline"></i> {{ $item->updated_at?->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rd-empty">
                                <div class="rd-empty__icon"><i class="mdi mdi-check-circle-outline"></i></div>
                                <p class="mb-0 font-weight-medium">All caught up</p>
                                <small>No open or pending requests right now.</small>
                            </div>
                        @endforelse
                        @if($pendingTasks->isNotEmpty())
                            @can('registry.components.approval queue.view')
                            <a href="{{ route('registry.approvals.index') }}" class="rd-panel__footer-link">
                                View approval queue <i class="mdi mdi-arrow-right"></i>
                            </a>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="rd-panel">
                    <div class="rd-panel__head">
                        <span class="rd-panel__icon"><i class="mdi mdi-history"></i></span>
                        <div>
                            <h4 class="rd-panel__title">Recent activity</h4>
                            <p class="rd-panel__sub">Latest updates across the registry</p>
                        </div>
                    </div>
                    <div class="rd-panel__body rd-panel__body--flush">
                        @forelse($activities as $item)
                            <a href="{{ route('registry.requests.show', $item->id) }}" class="rd-feed__item">
                                <div class="rd-activity-row">
                                    <span class="rd-activity-dot"></span>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="rd-feed__top">
                                            <span class="rd-feed__ref">{{ $item->reference_no }}</span>
                                            <span class="rd-badge rd-badge--category">
                                                {{ $item->category?->name ?? 'Uncategorised' }}
                                            </span>
                                        </div>
                                        <p class="rd-feed__subject mb-0">{{ Str::limit($item->subject, 52) }}</p>
                                        <div class="rd-feed__meta">
                                            <span><i class="mdi mdi-update"></i> {{ $item->updated_at?->diffForHumans() }}</span>
                                            @if($item->direction)
                                                <span><i class="mdi mdi-{{ $item->direction === 'outgoing' ? 'arrow-top-right' : 'arrow-bottom-left' }}"></i> {{ ucfirst($item->direction) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="rd-empty">
                                <div class="rd-empty__icon"><i class="mdi mdi-inbox-outline"></i></div>
                                <p class="mb-0 font-weight-medium">No recent activity</p>
                                <small>Updates will appear here as requests move through workflows.</small>
                            </div>
                        @endforelse
                        @if(count($activities) > 0)
                            @can('registry.components.requests.view')
                            <a href="{{ route('registry.requests.index') }}" class="rd-panel__footer-link">
                                View all requests <i class="mdi mdi-arrow-right"></i>
                            </a>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

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

                const chartFont = { family: "'Inter', system-ui, -apple-system, sans-serif", size: 12 };
                const gridColor = 'rgba(148, 163, 184, 0.25)';

                const statusCtx = document.getElementById('registryStatusChart');
                if (statusCtx) {
                    if (statusChart) statusChart.destroy();
                    statusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: chartData.status.labels,
                            datasets: [{
                                data: chartData.status.values,
                                backgroundColor: ['#0288d1', '#f57c00', '#2e7d32', '#c62828'],
                                borderWidth: 3,
                                borderColor: '#fff',
                                hoverOffset: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '62%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    padding: 12,
                                    cornerRadius: 8,
                                    titleFont: chartFont,
                                    bodyFont: chartFont,
                                },
                            },
                        },
                    });
                }

                const stageCtx = document.getElementById('registryStageChart');
                if (stageCtx) {
                    if (stageChart) stageChart.destroy();
                    const stageLabels = chartData.stages.labels.length ? chartData.stages.labels : ['No data'];
                    const stageValues = chartData.stages.values.length ? chartData.stages.values : [0];
                    stageChart = new Chart(stageCtx, {
                        type: 'bar',
                        data: {
                            labels: stageLabels.map((l) => String(l).replace(/_/g, ' ')),
                            datasets: [{
                                label: 'Avg hours',
                                data: stageValues,
                                backgroundColor: 'rgba(37, 99, 235, 0.85)',
                                borderRadius: 6,
                                borderSkipped: false,
                                maxBarThickness: 28,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grid: { color: gridColor },
                                    ticks: { font: chartFont, color: '#64748b' },
                                    title: { display: true, text: 'Hours', font: chartFont, color: '#64748b' },
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { font: chartFont, color: '#334155' },
                                },
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    padding: 12,
                                    cornerRadius: 8,
                                    titleFont: chartFont,
                                    bodyFont: chartFont,
                                },
                            },
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
