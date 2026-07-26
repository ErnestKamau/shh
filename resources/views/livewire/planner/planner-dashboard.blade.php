<div class="planner-dashboard">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
        <div>
            <h3 class="mb-1 font-weight-bold">
                <i class="mdi mdi-view-dashboard-outline text-primary mr-1"></i>
                {{ __('planner.dashboard') }}
            </h3>
            <p class="text-muted mb-0">{{ __('planner.dashboard_subtitle') }}</p>
        </div>
        <div class="text-muted small mt-2 mt-md-0">
            <i class="mdi mdi-calendar mr-1"></i>{{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-6 col-md-4 col-xl-2 mb-3">
            <a href="{{ route('system-planner.schedule-sampling') }}" class="pd-kpi-link">
                <div class="pd-kpi" style="border-left-color:#8a1a1f;">
                    <div class="pd-kpi-value">{{ $totalSchedules }}</div>
                    <div class="pd-kpi-label">{{ __('planner.total_schedules') }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2 mb-3">
            <a href="{{ route('system-planner.schedule-sampling') }}" class="pd-kpi-link">
                <div class="pd-kpi" style="border-left-color:#2563eb;">
                    <div class="pd-kpi-value">{{ $upcomingSchedules }}</div>
                    <div class="pd-kpi-label">{{ __('planner.upcoming_7_days') }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2 mb-3">
            <a href="{{ route('system-planner.schedule-sampling') }}" class="pd-kpi-link">
                <div class="pd-kpi" style="border-left-color:#e65100;">
                    <div class="pd-kpi-value">{{ $overdueSchedules }}</div>
                    <div class="pd-kpi-label">{{ __('planner.overdue_schedules') }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2 mb-3">
            <a href="{{ route('system-planner.actual-collections') }}" class="pd-kpi-link">
                <div class="pd-kpi" style="border-left-color:#2e7d32;">
                    <div class="pd-kpi-value">{{ $collectedSchedules }}</div>
                    <div class="pd-kpi-label">{{ __('planner.collected') }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2 mb-3">
            <a href="{{ route('system-planner.kpi-reports') }}" class="pd-kpi-link">
                <div class="pd-kpi" style="border-left-color:#0d9488;">
                    <div class="pd-kpi-value">{{ number_format($collectionRate, 1) }}%</div>
                    <div class="pd-kpi-label">{{ __('planner.collection_rate') }}</div>
                    <div class="pd-kpi-hint">{{ __('planner.last_30_days') }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2 mb-3">
            <a href="{{ route('system-planner.tasks') }}" class="pd-kpi-link">
                <div class="pd-kpi" style="border-left-color:#1565c0;">
                    <div class="pd-kpi-value">{{ $openTasks }}</div>
                    <div class="pd-kpi-label">{{ __('planner.open_tasks') }}</div>
                </div>
            </a>
        </div>
    </div>

    @if($showMySchedules)
    <div class="pd-card mb-3 pd-my-schedules">
        <div class="pd-card-header d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <div>{{ __('planner.my_schedules') }}</div>
                <div class="pd-kpi-hint mb-0">{{ __('planner.my_schedules_subtitle') }}</div>
            </div>
            <a href="{{ route('system-planner.schedule-sampling') }}" class="small">{{ __('planner.view_all') }}</a>
        </div>
        <div class="row">
            @foreach($mySchedules as $row)
            <div class="col-12 col-md-6 col-xl-4 mb-3">
                <div class="pd-my-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="pd-list-title">{{ $row['title'] }}</div>
                        <span class="pd-status pd-status--{{ strtolower($row['status']) }}">{{ $row['status'] }}</span>
                    </div>
                    <div class="pd-list-meta">{{ $row['client'] }} · {{ $row['when'] }}</div>
                    <div class="pd-list-sub">{{ $row['location'] }}</div>
                    @php
                        $collectedCount = (int) ($row['collected'] ?? 0);
                        $scheduledCount = max(0, (int) ($row['scheduled'] ?? 0));
                        $progressPct = $scheduledCount > 0
                            ? (int) min(100, round(($collectedCount / $scheduledCount) * 100))
                            : 0;
                        $progressLabel = __('planner.samples_collected_progress', [
                            'collected' => $collectedCount,
                            'scheduled' => $scheduledCount,
                        ]);
                        if ($progressLabel === 'planner.samples_collected_progress') {
                            $progressLabel = $collectedCount.' of '.$scheduledCount.' samples collected';
                        }
                    @endphp
                    <div class="pd-progress">
                        <div class="pd-progress-meta">
                            <span>{{ $progressLabel }}</span>
                            <span>{{ $progressPct }}%</span>
                        </div>
                        <div class="pd-progress-track" aria-hidden="true">
                            <div class="pd-progress-fill" style="width: {{ $progressPct }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="row mb-3">
        <div class="col-lg-4 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('planner.upcoming_schedules') }}</span>
                    <a href="{{ route('system-planner.schedule-sampling') }}" class="small">{{ __('planner.view_all') }}</a>
                </div>
                @forelse($upcomingList as $row)
                <div class="pd-list-item">
                    <div class="pd-list-title">{{ $row['title'] }}</div>
                    <div class="pd-list-meta">{{ $row['client'] }} · {{ $row['when'] }}</div>
                    <div class="pd-list-sub">{{ $row['location'] }}</div>
                </div>
                @empty
                <div class="pd-empty">{{ __('planner.no_upcoming_schedules') }}</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('planner.overdue_schedules') }}</span>
                    <a href="{{ route('system-planner.schedule-sampling') }}" class="small">{{ __('planner.view_all') }}</a>
                </div>
                @forelse($overdueList as $row)
                <div class="pd-list-item pd-list-item--warn">
                    <div class="pd-list-title">{{ $row['title'] }}</div>
                    <div class="pd-list-meta">{{ $row['client'] }} · {{ $row['when'] }}</div>
                    <div class="pd-list-sub">{{ $row['location'] }}</div>
                </div>
                @empty
                <div class="pd-empty">{{ __('planner.no_overdue_schedules') }}</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('planner.recent_collections') }}</span>
                    <a href="{{ route('system-planner.actual-collections') }}" class="small">{{ __('planner.view_all') }}</a>
                </div>
                @forelse($recentCollections as $row)
                <div class="pd-list-item">
                    <div class="pd-list-title">{{ $row['title'] }}</div>
                    <div class="pd-list-meta">{{ $row['client'] }} · {{ $row['when'] }}</div>
                    <div class="pd-list-sub">{{ $row['personnel'] }}</div>
                </div>
                @empty
                <div class="pd-empty">{{ __('planner.no_recent_collections') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-4 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header">{{ __('planner.collection_status_30d') }}</div>
                <div class="pd-chart-wrap" wire:ignore>
                    <canvas id="plannerCollectionStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header">{{ __('planner.schedules_by_frequency') }}</div>
                <div class="pd-chart-wrap" wire:ignore>
                    <canvas id="plannerFrequencyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header">{{ __('planner.task_status_breakdown') }}</div>
                <div class="pd-chart-wrap" wire:ignore>
                    <canvas id="plannerTaskStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-8 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header">{{ __('planner.scheduled_vs_collected_trend') }}</div>
                <div class="pd-chart-wrap pd-chart-wrap--tall" wire:ignore>
                    <canvas id="plannerMonthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header">{{ __('planner.quick_actions') }}</div>
                <div class="pd-actions">
                    <a href="{{ route('system-planner.schedule-sampling') }}" class="pd-action">
                        <i class="mdi mdi-clock-outline"></i> {{ __('planner.sampling_schedule') }}
                    </a>
                    <a href="{{ route('system-planner.fill-sampling-forms') }}" class="pd-action">
                        <i class="mdi mdi-clipboard-edit-outline"></i>
                        {{ __('planner.fill_sampling_forms') === 'planner.fill_sampling_forms' ? 'Fill Sampling Forms' : __('planner.fill_sampling_forms') }}
                    </a>
                    <a href="{{ route('system-planner.actual-collections') }}" class="pd-action">
                        <i class="mdi mdi-clipboard-check-outline"></i> {{ __('planner.actual_collections') }}
                    </a>
                    <a href="{{ route('system-planner.kpi-reports') }}" class="pd-action">
                        <i class="mdi mdi-chart-timeline-variant"></i> {{ __('planner.kpi_reports') }}
                    </a>
                    <a href="{{ route('system-planner.tasks') }}" class="pd-action">
                        <i class="mdi mdi-calendar-text-outline"></i> {{ __('planner.tasks') }}
                    </a>
                    <a href="{{ route('full-calendar') }}" class="pd-action">
                        <i class="mdi mdi-calendar-month-outline"></i> {{ __('planner.calendar') }}
                    </a>
                </div>
                <div class="pd-stat-row mt-3">
                    <div>
                        <div class="pd-stat-value">{{ $pendingSchedules }}</div>
                        <div class="pd-stat-label">{{ __('planner.pending') }}</div>
                    </div>
                    <div>
                        <div class="pd-stat-value">{{ $completedTasks }}</div>
                        <div class="pd-stat-label">{{ __('planner.completed_tasks') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header">{{ __('planner.top_clients') }}</div>
                <div class="pd-chart-wrap" wire:ignore>
                    <canvas id="plannerTopClientsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="pd-card h-100">
                <div class="pd-card-header">{{ __('planner.personnel_workload') }}</div>
                <div class="pd-chart-wrap" wire:ignore>
                    <canvas id="plannerPersonnelChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @php
        $plannerDashboardChartData = [
            'collectionStatus' => $collectionStatusChart,
            'frequency' => $frequencyChart,
            'taskStatus' => $taskStatusChart,
            'monthlyTrend' => $monthlyTrend,
            'topClients' => $topClients,
            'personnel' => $personnelWorkload,
            'emptyLabel' => __('planner.no_chart_data'),
        ];
    @endphp
    <script type="application/json" id="planner-dashboard-chart-data">{!! json_encode($plannerDashboardChartData) !!}</script>

    <style>
        .planner-dashboard { padding-bottom: 1rem; }
        .pd-kpi-link { text-decoration: none !important; color: inherit !important; display: block; height: 100%; }
        .pd-kpi {
            background: #fff;
            border: 1px solid #e8ecf1;
            border-left: 4px solid #8a1a1f;
            border-radius: 12px;
            padding: 14px 16px;
            height: 100%;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .pd-kpi-link:hover .pd-kpi {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15,23,42,0.08);
        }
        .pd-kpi-value { font-size: 1.65rem; font-weight: 700; color: #111827; line-height: 1.1; }
        .pd-kpi-label { font-size: 0.8rem; color: #6b7280; margin-top: 6px; font-weight: 600; }
        .pd-kpi-hint { font-size: 0.7rem; color: #94a3b8; margin-top: 2px; }
        .pd-card {
            background: #fff;
            border: 1px solid #e8ecf1;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
        }
        .pd-card-header {
            font-size: 0.85rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 12px;
            letter-spacing: 0.01em;
        }
        .pd-chart-wrap { position: relative; height: 240px; width: 100%; }
        .pd-chart-wrap--tall { height: 280px; }
        .pd-chart-wrap canvas { width: 100% !important; height: 100% !important; }
        .pd-actions { display: flex; flex-direction: column; gap: 8px; }
        .pd-action {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e8ecf1;
            color: #1f2937;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background .15s ease, border-color .15s ease;
        }
        .pd-action i { color: #8a1a1f; font-size: 1.15rem; }
        .pd-action:hover { background: #faf7f7; border-color: #e2b8bb; color: #8a1a1f; }
        .pd-stat-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .pd-stat-value { font-size: 1.35rem; font-weight: 700; color: #111827; }
        .pd-stat-label { font-size: 0.75rem; color: #6b7280; font-weight: 600; }
        .pd-list-item {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .pd-list-item:last-child { border-bottom: 0; }
        .pd-list-item--warn .pd-list-title { color: #9a3412; }
        .pd-list-title { font-size: 0.9rem; font-weight: 700; color: #111827; }
        .pd-list-meta { font-size: 0.78rem; color: #4b5563; margin-top: 2px; }
        .pd-list-sub { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pd-empty { text-align: center; color: #94a3b8; font-size: 0.9rem; padding: 1.5rem 0.5rem; }
        .pd-my-schedules { margin-top: 0.25rem; }
        .pd-my-item {
            border: 1px solid #e8ecf1;
            border-radius: 12px;
            padding: 12px 14px;
            height: 100%;
            background: #fcfcfd;
        }
        .pd-status {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
            margin-left: 8px;
        }
        .pd-status--collected { background: #e8f5e9; color: #1b5e20; }
        .pd-status--upcoming { background: #e3f2fd; color: #0d47a1; }
        .pd-status--overdue { background: #fff3e0; color: #e65100; }
        .pd-status--pending { background: #f3e8e9; color: #8a1a1f; }
        .pd-status--partial { background: #fff8e1; color: #f57f17; }
        .pd-progress {
            margin-top: 10px;
        }
        .pd-progress-meta {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .pd-progress-track {
            height: 6px;
            border-radius: 999px;
            background: #eef1f5;
            overflow: hidden;
        }
        .pd-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #8a1a1f, #b4232a);
            transition: width .2s ease;
        }
        @media (max-width: 991.98px) {
            .pd-kpi-value { font-size: 1.35rem; }
            .pd-chart-wrap { height: 220px; }
            .pd-chart-wrap--tall { height: 240px; }
        }
        @media (max-width: 767.98px) {
            .planner-dashboard h3 { font-size: 1.25rem; }
            .pd-kpi { padding: 12px; }
            .pd-kpi-value { font-size: 1.2rem; }
            .pd-kpi-label { font-size: 0.72rem; }
            .pd-card { padding: 12px; border-radius: 12px; }
            .pd-chart-wrap { height: 200px; }
            .pd-chart-wrap--tall { height: 220px; }
            .pd-action { font-size: 0.85rem; padding: 9px 10px; }
            .pd-my-item { padding: 10px 12px; }
        }
        @media (max-width: 575.98px) {
            .planner-dashboard .d-flex.justify-content-between { flex-direction: column; gap: 6px; }
            .pd-stat-row { grid-template-columns: 1fr 1fr; }
        }
    </style>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
@endassets

@script
<script>
    const loadPlannerCharts = () => {
        if (typeof Chart === 'undefined') {
            setTimeout(loadPlannerCharts, 100);
            return;
        }

        const dataEl = document.getElementById('planner-dashboard-chart-data');
        if (!dataEl) {
            setTimeout(loadPlannerCharts, 100);
            return;
        }

        let payload = {};
        try {
            payload = JSON.parse(dataEl.textContent || '{}');
        } catch (e) {
            return;
        }

        const emptyLabel = payload.emptyLabel || 'No data to display.';

        const showEmpty = (canvas) => {
            if (!canvas || !canvas.parentElement) return;
            canvas.parentElement.replaceChildren();
            const empty = document.createElement('div');
            empty.className = 'pd-empty py-5';
            empty.textContent = emptyLabel;
            canvas.parentElement.appendChild(empty);
        };

        const doughnut = (canvasId, data) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const hasData = Array.isArray(data) && data.some((item) => Number(item.value) > 0);
            if (!hasData) {
                showEmpty(canvas);
                return;
            }
            new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: data.map((i) => i.label),
                    datasets: [{
                        data: data.map((i) => i.value),
                        backgroundColor: data.map((i) => i.color),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        };

        const bar = (canvasId, data, color) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            if (!Array.isArray(data) || data.length === 0) {
                showEmpty(canvas);
                return;
            }
            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.map((i) => i.label),
                    datasets: [{
                        data: data.map((i) => i.value),
                        backgroundColor: color,
                        borderRadius: 6,
                        maxBarThickness: 28
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        };

        doughnut('plannerCollectionStatusChart', payload.collectionStatus || []);
        doughnut('plannerFrequencyChart', payload.frequency || []);
        doughnut('plannerTaskStatusChart', payload.taskStatus || []);

        const trendCanvas = document.getElementById('plannerMonthlyTrendChart');
        const monthlyTrend = payload.monthlyTrend || [];
        if (trendCanvas) {
            new Chart(trendCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: monthlyTrend.map((i) => i.month),
                    datasets: [
                        {
                            label: 'Scheduled',
                            data: monthlyTrend.map((i) => i.scheduled),
                            borderColor: '#8a1a1f',
                            backgroundColor: 'rgba(138,26,31,0.12)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Collected',
                            data: monthlyTrend.map((i) => i.collected),
                            borderColor: '#2e7d32',
                            backgroundColor: 'rgba(46,125,50,0.12)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        bar('plannerTopClientsChart', payload.topClients || [], '#8a1a1f');
        bar('plannerPersonnelChart', payload.personnel || [], '#1565c0');
    };

    queueMicrotask(() => setTimeout(loadPlannerCharts, 50));
</script>
@endscript
