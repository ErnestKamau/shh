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
                <div class="card-body" wire:ignore>
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
                            <h2 class="font-weight-bold text-success mb-0">{{ $stats['summary']['active_batches'] ?? 0 }}</h2>
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
                            <div class="font-weight-bold text-indigo h5 mb-0">{{ $stats['summary']['sla_compliance_rate'] ?? 0 }}%</div>
                            <div class="text-muted x-small uppercase">{{ __('mas/lab.sla_compliance_rate') }}</div>
                        </div>
                    </div>

                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small uppercase font-weight-bold">{{ __('mas/lab.batch_sla_performance') }}</span>
                            <span class="text-danger font-weight-bold small">{{ __('mas/lab.overdue_count', ['count' => $stats['summary']['overdue_batches'] ?? 0]) }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['summary']['sla_compliance_rate'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-center mt-2 mb-0 x-small text-muted">{{ __('mas/lab.batches_within_target', ['percent' => $stats['summary']['sla_compliance_rate'] ?? 0]) }}</p>
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
                <div class="card-body" wire:ignore>
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
                <div class="card-body" wire:ignore>
                    <div style="height: 250px;">
                        <canvas id="completionRatioChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lab Section & Analyst Performance -->
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.performance_leaderboard') }}</h5>
                        <p class="text-muted x-small uppercase mb-0">{{ __('mas/lab.section_performance') }} & Workload Distribution</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase py-2 pl-3">{{ __('mas/lab.lab_section') }}</th>
                                    <th class="border-0 small text-uppercase py-2 text-center">{{ __('mas/lab.volume') }}</th>
                                    <th class="border-0 small text-uppercase py-2 text-center" style="width: 25%;">Occupancy</th>
                                    <th class="border-0 small text-uppercase py-2 text-center">{{ __('mas/lab.avg_tat') }}</th>
                                    <th class="border-0 small text-uppercase py-2 text-center">SLA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalVolume = collect($stats['sections']['leaderboard'] ?? [])->sum('total') ?: 1;
                                    $colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
                                @endphp
                                @forelse($stats['sections']['leaderboard'] ?? [] as $index => $section)
                                    @php 
                                        $percent = round(($section['total'] / $totalVolume) * 100);
                                        $color = $colors[$index % count($colors)];
                                    @endphp
                                    <tr>
                                        <td class="py-3 pl-3">
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2 rounded-circle" style="width: 8px; height: 8px; background-color: {{ $color }};"></div>
                                                <div>
                                                    <div class="font-weight-bold text-primary small">{{ $section['name'] }}</div>
                                                    <div class="text-muted x-small uppercase">{{ $section['code'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ $section['total'] }}</td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 mr-2" style="height: 4px;">
                                                    <div class="progress-bar" style="width: {{ $percent }}%; background-color: {{ $color }};"></div>
                                                </div>
                                                <span class="x-small font-weight-bold">{{ $percent }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="font-weight-bold">{{ $section['avg_tat'] }}</span>
                                            <span class="text-muted small">d</span>
                                        </td>
                                        <td class="text-center">
                                            @if($section['overdue'] > 0)
                                                <span class="badge badge-soft-danger px-2">{{ $section['overdue'] }} {{ __('mas/lab.overdue') }}</span>
                                            @else
                                                <span class="badge badge-soft-success px-2">{{ __('mas/common.stable') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">{{ __('mas/common.no_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.analyst_performance') }}</h5>
                        <p class="text-muted x-small uppercase mb-0">{{ __('mas/lab.top_analysts') }}</p>
                    </div>
                    <span class="badge badge-soft-indigo px-2 text-uppercase" style="font-size: 10px;">{{ $stats['period_label'] ?? __('mas/common.active') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase py-2 pl-3">{{ __('mas/lab.analyst') }}</th>
                                    <th class="border-0 small text-uppercase py-2 text-center">{{ __('mas/lab.volume') }}</th>
                                    <th class="border-0 small text-uppercase py-2 text-center">SLA %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['analyst_performance'] ?? [] as $analyst)
                                <tr>
                                    <td class="py-3 pl-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs mr-2 bg-indigo-soft text-indigo rounded-circle d-flex align-items-center justify-content-center font-weight-bold" style="width: 28px; height: 28px; font-size: 11px;">
                                                {{ substr($analyst['name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-dark small" style="font-size: 11px;">{{ Str::limit($analyst['name'], 15) }}</div>
                                                <div class="x-small {{ $analyst['avg_offset'] <= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $analyst['avg_offset'] <= 0 ? '-' : '+' }}{{ abs($analyst['avg_offset']) }}d
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-weight-bold small">{{ $analyst['total_tests'] }}</td>
                                    <td class="text-center">
                                        <div class="d-inline-block text-center" style="width: 45px;">
                                            <div class="x-small font-weight-bold {{ $analyst['on_time_rate'] >= 90 ? 'text-success' : ($analyst['on_time_rate'] >= 75 ? 'text-warning' : 'text-danger') }}">
                                                {{ $analyst['on_time_rate'] }}%
                                            </div>
                                            <div class="progress" style="height: 3px;">
                                                <div class="progress-bar {{ $analyst['on_time_rate'] >= 90 ? 'bg-success' : ($analyst['on_time_rate'] >= 75 ? 'bg-warning' : 'bg-danger') }}" 
                                                     style="width: {{ $analyst['on_time_rate'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted small italic">
                                        {{ __('mas/common.no_data') }} - <span class="x-small">{{ __('mas/lab.try_other_periods') }}</span>
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



    <div class="row mb-4">
        <!-- Section Trends -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.historical_section_tat') }}</h5>
                </div>
                <div class="card-body" wire:ignore>
                    <div style="height: 300px;">
                        <canvas id="sectionTrendsChart"></canvas>
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
                            {{ __('mas/lab.' . ($stats['period'] ?? 'active')) }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'active' ? 'active' : '' }}" href="#" wire:click.prevent="setPeriod('active')">{{ __('mas/common.active') }}</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'week' ? 'active' : '' }}" href="#" wire:click.prevent="setPeriod('week')">{{ __('mas/common.past_week') }}</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'month' ? 'active' : '' }}" href="#" wire:click.prevent="setPeriod('month')">{{ __('mas/common.past_month') }}</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'year' ? 'active' : '' }}" href="#" wire:click.prevent="setPeriod('year')">{{ __('mas/common.past_year') }}</a>
                            <a class="dropdown-item {{ ($stats['period'] ?? '') == 'lifetime' ? 'active' : '' }}" href="#" wire:click.prevent="setPeriod('lifetime')">{{ __('mas/common.lifetime') }}</a>
                        </div>
                    </div>
                </div>
                <div class="card-body" wire:ignore>
                    <div style="height: 300px;">
                        <canvas id="throughputVolumeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analyte TAT Insights -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/lab.detailed_insights') }}</h5>
                        <p class="text-muted small mb-0">Detailed breakdown of individual analyte performance for the {{ $stats['period_label'] ?? 'selected period' }}.</p>
                    </div>
                    <div class="small text-muted">Showing {{ count($this->getPaginatedDetailedLogs()) }} of {{ count($detailed_logs) }} items</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.analyte') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.sample_code') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.received') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.expected') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.actual') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold text-center">{{ __('mas/lab.offset') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/lab.analyst') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($this->getPaginatedDetailedLogs() as $idx => $log)
                                    <tr wire:key="detailed-log-{{ $idx }}">
                                        <td class="font-weight-bold small text-primary">{{ $log['analyte'] }}</td>
                                        <td class="small">{{ $log['sample_code'] }}</td>
                                        <td class="small text-muted">{{ $log['receipt_date'] }}</td>
                                        <td class="small text-muted">{{ $log['expected_date'] }}</td>
                                        <td class="small font-weight-bold">{{ $log['actual_date'] }}</td>
                                        <td class="text-center">
                                            @if($log['offset'] <= 0)
                                                <span class="badge badge-success px-2 py-1">-{{ abs($log['offset']) }}d</span>
                                            @else
                                                <span class="badge badge-danger px-2 py-1">+{{ $log['offset'] }}d</span>
                                            @endif
                                        </td>
                                        <td class="small text-dark font-weight-bold">{{ $log['analyst'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="mdi mdi-information-outline h2 d-block"></i>
                                            {{ __('mas/common.no_data') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(count($detailed_logs) > $perPage)
                <div class="card-footer bg-white border-0 py-3">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            @for($i = 1; $i <= ceil(count($detailed_logs) / $perPage); $i++)
                                <li class="page-item {{ $detailedPage === $i ? 'active' : '' }}">
                                    <a class="page-link shadow-none" href="#" wire:click.prevent="setDetailedPage({{ $i }})">{{ $i }}</a>
                                </li>
                            @endfor
                        </ul>
                    </nav>
                </div>
                @endif
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
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-sm {{ $activeGridTab === 'my_tasks' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setGridTab('my_tasks')">
                            {{ __('mas/lab.my_tasks') }}
                        </button>
                        <button class="btn btn-sm {{ $activeGridTab === 'urgent' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setGridTab('urgent')">
                            {{ __('mas/lab.urgent') }}
                        </button>
                        <button class="btn btn-sm {{ $activeGridTab === 'approvals' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setGridTab('approvals')">
                            {{ __('mas/lab.approvals') }}
                        </button>
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
                                @forelse($this->getPaginatedGridData() as $index => $item)
                                    <tr wire:key="grid-item-{{ $activeGridTab }}-{{ $index }}" class="{{ ($item['priority'] ?? '') === 'Urgent' ? 'table-warning' : '' }}">
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
                @php
                    $gridData = $stats['smart_grid'] ?? [];
                    $gridTotalPages = ceil(count($gridData) / $perGridPage);
                @endphp
                @if($gridTotalPages > 1)
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ count($this->getPaginatedGridData()) }} of {{ count($gridData) }} items
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            @for($i = 1; $i <= $gridTotalPages; $i++)
                                <li class="page-item {{ $gridPage === $i ? 'active' : '' }}">
                                    <a class="page-link shadow-none" href="#" wire:click.prevent="setGridPage({{ $i }})">{{ $i }}</a>
                                </li>
                            @endfor
                        </ul>
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<style>
    .x-small { font-size: 10px; }
    .uppercase { text-transform: uppercase; }
    .bg-indigo-soft { background-color: rgba(79, 70, 229, 0.1); }
    .text-indigo { color: #4f46e5; }
</style>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global Chart Defaults
        Chart.defaults.global.defaultFontFamily = "'Inter', sans-serif";
        Chart.defaults.global.defaultFontColor = '#64748b';

        initCharts(@json($stats));
    });

    // Listen for Livewire period changes
    window.addEventListener('period-changed', event => {
        const stats = event.detail.stats;
        initCharts(stats);
    });

    window.masLabCharts = window.masLabCharts || {};

    function initCharts(stats) {
        if (!stats || !stats.charts) return;

        // Destroy existing charts to avoid memory leaks and hover artifacts
        const chartKeys = ['labChart', 'agingChart', 'completionChart', 'throughputChart', 'sectionWorkChart', 'sectionTrendsChart'];
        chartKeys.forEach(key => {
            if (window.masLabCharts[key] && typeof window.masLabCharts[key].destroy === 'function') {
                window.masLabCharts[key].destroy();
            }
        });

        // 1. Lab Workflow Stage Chart
        var labCtx = document.getElementById('labWorkflowChart').getContext('2d');
        window.masLabCharts.labChart = new Chart(labCtx, {
            type: 'bar',
            plugins: [{
                afterDatasetsDraw: function(chart) {
                    var ctx = chart.ctx;
                    chart.data.datasets.forEach(function(dataset, i) {
                        var meta = chart.getDatasetMeta(i);
                        if (!meta.hidden) {
                            meta.data.forEach(function(element, index) {
                                ctx.fillStyle = '#ffffff';
                                ctx.font = Chart.helpers.fontString(12, 'bold', "'Inter', sans-serif");
                                var dataString = dataset.data[index].toString();
                                if (dataString === '0') return;
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                var position = element.tooltipPosition();
                                if (element._view.y > chart.chartArea.bottom - 20) {
                                    ctx.fillStyle = '#1e293b';
                                    ctx.fillText(dataString, position.x, position.y - 11);
                                } else {
                                    ctx.fillText(dataString, position.x, position.y + 11);
                                }
                            });
                        }
                    });
                }
            }],
            data: {
                labels: stats.charts.stage_labels,
                datasets: [{
                    label: '{{ __('mas/lab.batch_count') }}',
                    data: stats.charts.stage_totals,
                    backgroundColor: '#4f46e5',
                    borderRadius: 4
                }, {
                    label: '{{ __('mas/lab.overdue') }}',
                    data: stats.charts.stage_overdue,
                    backgroundColor: '#ef4444',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 15, usePointStyle: true } },
                scales: {
                    xAxes: [{ gridLines: { display: false }, ticks: { fontSize: 11, fontStyle: 'bold' } }],
                    yAxes: [{ gridLines: { color: '#f1f5f9' }, ticks: { beginAtZero: true, stepSize: 5 } }]
                }
            }
        });

        // 2. Aging Distribution Doughnut
        var agingCtx = document.getElementById('agingDoughnutChart').getContext('2d');
        window.masLabCharts.agingChart = new Chart(agingCtx, {
            type: 'doughnut',
            data: {
                labels: stats.charts.aging_labels,
                datasets: [{
                    data: stats.charts.aging_counts,
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
        window.masLabCharts.completionChart = new Chart(completionCtx, {
            type: 'doughnut',
            data: {
                labels: stats.charts.completion_labels,
                datasets: [{
                    data: stats.charts.completion_counts,
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
        window.masLabCharts.throughputChart = new Chart(throughputCtx, {
            type: 'bar',
            data: {
                labels: stats.charts.throughput_labels,
                datasets: [{
                    label: '{{ __('mas/lab.tests_processed') }}',
                    data: stats.charts.throughput_counts,
                    backgroundColor: '#6366f1',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ 
                        gridLines: { display: false }, 
                        ticks: { 
                            fontSize: 10,
                            maxRotation: 45,
                            minRotation: 0,
                            autoSkip: false
                        } 
                    }],
                    yAxes: [{ 
                        gridLines: { color: 'rgba(0,0,0,0.05)', zeroLineColor: 'rgba(0,0,0,0.1)' }, 
                        ticks: { beginAtZero: true, fontSize: 10, precision: 0 } 
                    }]
                }
            }
        });


        // 6. Section Historical Trends Line Chart

        const secTrends = stats.sections?.trends || { labels: [], series: [] };
        const colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
        
        var sectionTrendsCtx = document.getElementById('sectionTrendsChart').getContext('2d');
        window.masLabCharts.sectionTrendsChart = new Chart(sectionTrendsCtx, {
            type: 'line',
            data: {
                labels: secTrends.labels,
                datasets: secTrends.series.map((series, index) => {
                    const color = colors[index % colors.length];
                    return {
                        label: series.name,
                        data: series.data,
                        borderColor: color,
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: color,
                        tension: 0.3
                    };
                })
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'top', align: 'end', labels: { boxWidth: 15, fontSize: 11 } },
                scales: {
                    xAxes: [{ gridLines: { display: false }, ticks: { fontSize: 10 } }],
                    yAxes: [{ 
                        gridLines: { color: '#f1f5f9' }, 
                        ticks: { beginAtZero: true, fontSize: 10 }
                    }]
                },
                tooltips: { mode: 'index', intersect: false }
            }
        });
    }

    function exportLabPdf(isPreview = false) {
        const chart = window.masLabCharts.labChart;
        if (chart) {
            const base64Image = chart.toBase64Image();
            document.getElementById('chart_image_input').value = base64Image;
        } else {
            document.getElementById('chart_image_input').value = '';
        }

        const form = document.getElementById('pdfExportForm');
        document.getElementById('preview_input').value = isPreview;
        form.target = isPreview ? "_blank" : "_self";
        form.submit();
    }
</script>
</div>