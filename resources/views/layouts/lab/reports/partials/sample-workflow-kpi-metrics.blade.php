@php
    $metrics = $metrics ?? [];
    $registrationKpi = $registrationKpiPeriod ?? [];
    $laboratoryKpi = $laboratoryKpiPeriod ?? [];
    $registrationDetailRows = $registrationDetailRows ?? [];
    $laboratoryDetailRows = $laboratoryDetailRows ?? [];
    $activeKpiTab = $activeKpiTab ?? 'overview';
    $registrationFilters = $registrationFilters ?? [];
    $laboratoryFilters = $laboratoryFilters ?? [];

    $kpiStart = $registrationKpi['start_date'] ?? now()->startOfMonth()->toDateString();
    $kpiEnd = $registrationKpi['end_date'] ?? now()->endOfMonth()->toDateString();
    $labKpiStart = request('lab_kpi_start_date', $laboratoryKpi['start_date'] ?? $kpiStart);
    $labKpiEnd = request('lab_kpi_end_date', $laboratoryKpi['end_date'] ?? $kpiEnd);

    $cards = [
        ['key' => 'total', 'label' => 'Total Active Jobs', 'sublabel' => 'All active batches', 'icon' => 'mdi-clipboard-text-multiple', 'color' => '#3498db', 'link' => route('sample-workflow')],
        ['key' => 'samples_receiving', 'label' => 'Samples Receiving', 'sublabel' => 'Registration pipeline', 'icon' => 'mdi-truck-delivery', 'color' => '#17a2b8', 'link' => route('sample-workflow', ['status' => 'Samples Receiving'])],
        ['key' => 'request_review', 'label' => 'Request Review', 'sublabel' => 'Awaiting review', 'icon' => 'mdi-clipboard-check-outline', 'color' => '#6f42c1', 'link' => route('sample-workflow', ['status' => 'Samples Request Review'])],
        ['key' => 'samples_in_lab', 'label' => 'Samples In Lab', 'sublabel' => 'Testing in progress', 'icon' => 'mdi-flask', 'color' => '#fd7e14', 'link' => route('sample-workflow', ['status' => 'Samples In Lab'])],
        ['key' => 'verification', 'label' => 'Verification', 'sublabel' => 'Results verification', 'icon' => 'mdi-check-decagram', 'color' => '#20c997', 'link' => route('sample-workflow', ['status' => 'Sample Verification'])],
        ['key' => 'approval', 'label' => 'Approval', 'sublabel' => 'Pending approval', 'icon' => 'mdi-stamper', 'color' => '#28a745', 'link' => route('sample-workflow', ['status' => 'Sample Approval'])],
        ['key' => 'completed', 'label' => 'Completed', 'sublabel' => 'Finished samples', 'icon' => 'mdi-check-all', 'color' => '#6c757d', 'link' => route('sample-workflow', ['status' => 'Completed Sample'])],
        ['key' => 'portal_submitted', 'label' => 'Portal Submitted', 'sublabel' => 'TRF / portal queue', 'icon' => 'mdi-web', 'color' => '#e83e8c', 'link' => route('sample-workflow', ['status' => 'Samples Receiving'])],
    ];

    $kpiTabs = [
        'overview' => ['label' => 'Overview', 'icon' => 'mdi-view-dashboard-outline'],
        'registration' => ['label' => 'Sample Login KPIs', 'icon' => 'mdi-clipboard-plus-outline'],
        'laboratory' => ['label' => 'Testing KPIs', 'icon' => 'mdi-flask-outline'],
    ];
@endphp

<div class="sample-workflow-kpi-dashboard px-4 mb-4">
    <div class="lab-kpi-tabs mb-4">
        @foreach($kpiTabs as $tabKey => $tab)
            <a href="{{ route('sample-workflow.kpis', array_merge(request()->except('page'), ['kpi_tab' => $tabKey])) }}"
               class="lab-kpi-tab {{ $activeKpiTab === $tabKey ? 'active' : '' }}">
                <i class="mdi {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    @if($activeKpiTab === 'overview')
        <div class="lab-kpi-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-1">Sample Workflow Overview</h6>
                    <p class="text-muted mb-0 lab-kpi-subtitle">Key statistics across registration and laboratory workflow</p>
                </div>
            </div>

            <div class="row mb-3">
                @foreach(array_slice($cards, 0, 4) as $card)
                    <div class="col-md-3 col-sm-6 mb-3">
                        @include('layouts.lab.reports.partials.sample-workflow-metric-card', ['card' => $card, 'metrics' => $metrics])
                    </div>
                @endforeach
            </div>

            <div class="row mb-2">
                @foreach(array_slice($cards, 4, 4) as $card)
                    <div class="col-md-3 col-sm-6 mb-3">
                        @include('layouts.lab.reports.partials.sample-workflow-metric-card', ['card' => $card, 'metrics' => $metrics])
                    </div>
                @endforeach
            </div>

            <div class="row mb-2">
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="quotation-metric-mini">
                        <span class="text-muted">Ready for reception</span>
                        <strong>{{ $metrics['ready_for_reception'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="quotation-metric-mini">
                        <span class="text-muted">Registered this month</span>
                        <strong>{{ $metrics['this_month_registered'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($activeKpiTab === 'registration')
        <div class="lab-kpi-section card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1"><i class="mdi mdi-clipboard-plus-outline"></i> KPI — Registration (Sample Login)</h6>
                        <p class="text-muted mb-0 lab-kpi-subtitle">Scheduled, collected, registration rate and clients for the selected period</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('sample-workflow.kpis') }}" class="lab-kpi-filter-form mb-4">
                    <input type="hidden" name="kpi_tab" value="registration">

                    <div class="lab-kpi-filter-panel">
                        <div class="lab-kpi-filter-panel-header">
                            <span><i class="mdi mdi-filter-variant"></i> Filters</span>
                            <a href="{{ route('sample-workflow.kpis', ['kpi_tab' => 'registration']) }}" class="btn btn-sm btn-light">Reset</a>
                        </div>
                        <div class="row">
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">From</label>
                                <input type="date" name="kpi_start_date" class="form-control form-control-sm" value="{{ $kpiStart }}">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">To</label>
                                <input type="date" name="kpi_end_date" class="form-control form-control-sm" value="{{ $kpiEnd }}">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Client Name</label>
                                <input type="text" name="reg_client" class="form-control form-control-sm" value="{{ $registrationFilters['client'] ?? '' }}" placeholder="Search client">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Sampler Name</label>
                                <input type="text" name="reg_sampler_name" class="form-control form-control-sm" value="{{ $registrationFilters['sampler_name'] ?? '' }}" placeholder="Sampler">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Sampler ID</label>
                                <input type="text" name="reg_sampler_id" class="form-control form-control-sm" value="{{ $registrationFilters['sampler_id'] ?? '' }}" placeholder="Sampler ID">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Equipment ID</label>
                                <input type="text" name="reg_equipment_id" class="form-control form-control-sm" value="{{ $registrationFilters['equipment_id'] ?? '' }}" placeholder="Equipment">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Job ID</label>
                                <input type="text" name="reg_job_id" class="form-control form-control-sm" value="{{ $registrationFilters['job_id'] ?? '' }}" placeholder="Batch / Job">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Sample ID</label>
                                <input type="text" name="reg_sample_id" class="form-control form-control-sm" value="{{ $registrationFilters['sample_id'] ?? '' }}" placeholder="Sample code">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Location</label>
                                <input type="text" name="reg_location" class="form-control form-control-sm" value="{{ $registrationFilters['location'] ?? '' }}" placeholder="Location">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Sampling Points</label>
                                <input type="text" name="reg_sampling_points" class="form-control form-control-sm" value="{{ $registrationFilters['sampling_points'] ?? '' }}" placeholder="Sampling point">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Registered By</label>
                                <input type="text" name="reg_registered_by" class="form-control form-control-sm" value="{{ $registrationFilters['registered_by'] ?? '' }}" placeholder="Officer">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Type</label>
                                <select name="reg_type" class="form-control form-control-sm">
                                    <option value="all" @selected(($registrationFilters['registration_type'] ?? 'all') === 'all')>All</option>
                                    <option value="scheduled" @selected(($registrationFilters['registration_type'] ?? '') === 'scheduled')>Scheduled only</option>
                                    <option value="collected" @selected(($registrationFilters['registration_type'] ?? '') === 'collected')>Collected only</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary btn-block">
                                    <i class="mdi mdi-filter"></i> Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row mb-4">
                    @foreach([
                        ['label' => 'Scheduled', 'value' => $registrationKpi['samples_scheduled'] ?? 0],
                        ['label' => 'Collected', 'value' => $registrationKpi['samples_collected'] ?? 0],
                        ['label' => 'Registration Rate', 'value' => (($registrationKpi['samples_scheduled'] ?? 0) + ($registrationKpi['samples_collected'] ?? 0)) > 0 ? ($registrationKpi['registration_rate_percent'] ?? 0).'%' : '—'],
                        ['label' => 'Clients Registered', 'value' => $registrationKpi['clients_registered'] ?? 0],
                    ] as $stat)
                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="quotation-metric-mini flex-column align-items-start">
                                <span class="text-muted">{{ $stat['label'] }}</span>
                                <strong class="lab-kpi-stat-value">{{ $stat['value'] }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>

                @include('layouts.lab.reports.partials.kpi-export-buttons', [
                    'summaryRoute' => route('lab.kpi.registration.summary.export'),
                    'detailRoute' => route('lab.kpi.registration.detail.export'),
                    'startDate' => $kpiStart,
                    'endDate' => $kpiEnd,
                    'filterFields' => [
                        'reg_client' => $registrationFilters['client'] ?? '',
                        'reg_sampler_name' => $registrationFilters['sampler_name'] ?? '',
                        'reg_sampler_id' => $registrationFilters['sampler_id'] ?? '',
                        'reg_equipment_id' => $registrationFilters['equipment_id'] ?? '',
                        'reg_job_id' => $registrationFilters['job_id'] ?? '',
                        'reg_sample_id' => $registrationFilters['sample_id'] ?? '',
                        'reg_location' => $registrationFilters['location'] ?? '',
                        'reg_sampling_points' => $registrationFilters['sampling_points'] ?? '',
                        'reg_registered_by' => $registrationFilters['registered_by'] ?? '',
                        'reg_type' => $registrationFilters['registration_type'] ?? 'all',
                    ],
                    'helper' => 'Detail export includes sampler, equipment, parameters, temperature, volume and due dates.',
                ])

                <div class="lab-kpi-table-wrap mt-4">
                    <div class="lab-kpi-table-toolbar">
                        <h6 class="mb-0">Registration Detail <span class="text-muted">({{ count($registrationDetailRows) }} rows)</span></h6>
                        <small class="text-muted lab-kpi-scroll-hint"><i class="mdi mdi-arrow-left-right"></i> Scroll to view all columns</small>
                    </div>
                    <div class="table-responsive lab-kpi-table-responsive">
                        <table class="table lab-kpi-table mb-0">
                            <thead>
                                <tr>
                                    <th class="lab-kpi-col-date">Date</th>
                                    <th class="lab-kpi-col-client">Client</th>
                                    <th class="lab-kpi-col-num">Sched.</th>
                                    <th class="lab-kpi-col-num">Coll.</th>
                                    <th class="lab-kpi-col-text">Sampler</th>
                                    <th class="lab-kpi-col-id">Sampler ID</th>
                                    <th class="lab-kpi-col-id">Equipment</th>
                                    <th class="lab-kpi-col-id">Job ID</th>
                                    <th class="lab-kpi-col-id">Sample ID</th>
                                    <th class="lab-kpi-col-wide">Details</th>
                                    <th class="lab-kpi-col-text">Location</th>
                                    <th class="lab-kpi-col-text">Sampling Point</th>
                                    <th class="lab-kpi-col-wide">Parameters</th>
                                    <th class="lab-kpi-col-num">Temp</th>
                                    <th class="lab-kpi-col-text">Units</th>
                                    <th class="lab-kpi-col-num">Volume</th>
                                    <th class="lab-kpi-col-text">Registered By</th>
                                    <th class="lab-kpi-col-date">Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registrationDetailRows as $row)
                                    <tr>
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['date'], 'type' => 'date'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['client'], 'type' => 'client'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['samples_scheduled'] ?? 0, 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['samples_collected'] ?? 0, 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['sampler_name'] ?? ($row['sampler'] ?? '—'), 'type' => 'text'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['sampler_id'] ?? '—', 'type' => 'id'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['equipment_id'], 'type' => 'id'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['job_id'] ?? '—', 'type' => 'id'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['sample_id'] ?? '—', 'type' => 'id'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['sample_details'] ?? '—', 'type' => 'stack'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['location'], 'type' => 'text'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['sampling_points'], 'type' => 'text'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['parameters'], 'type' => 'text'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['temperature'] ?? ($row['temp'] ?? '—'), 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['units'], 'type' => 'text'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['volume'], 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['registered_by'], 'type' => 'text'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['due_date'], 'type' => 'date'])
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="18" class="text-center text-muted py-4">No registration records for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($activeKpiTab === 'laboratory')
        <div class="lab-kpi-section card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1"><i class="mdi mdi-flask-outline"></i> KPI — Laboratory (Samples In Lab / Testing)</h6>
                        <p class="text-muted mb-0 lab-kpi-subtitle">Jobs received, completed, pending, data entry and review status</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('sample-workflow.kpis') }}" class="lab-kpi-filter-form mb-4">
                    <input type="hidden" name="kpi_tab" value="laboratory">

                    <div class="lab-kpi-filter-panel">
                        <div class="lab-kpi-filter-panel-header">
                            <span><i class="mdi mdi-filter-variant"></i> Filters</span>
                            <a href="{{ route('sample-workflow.kpis', ['kpi_tab' => 'laboratory']) }}" class="btn btn-sm btn-light">Reset</a>
                        </div>
                        <div class="row">
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">From</label>
                                <input type="date" name="lab_kpi_start_date" class="form-control form-control-sm" value="{{ $labKpiStart }}">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">To</label>
                                <input type="date" name="lab_kpi_end_date" class="form-control form-control-sm" value="{{ $labKpiEnd }}">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Client Name</label>
                                <input type="text" name="lab_client" class="form-control form-control-sm" value="{{ $laboratoryFilters['client'] ?? '' }}" placeholder="Search client">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Job ID</label>
                                <input type="text" name="lab_job_id" class="form-control form-control-sm" value="{{ $laboratoryFilters['job_id'] ?? '' }}" placeholder="Batch / Job">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Data Entry Status</label>
                                <select name="lab_data_entry_status" class="form-control form-control-sm">
                                    <option value="all" @selected(($laboratoryFilters['data_entry_status'] ?? 'all') === 'all')>All</option>
                                    <option value="complete" @selected(($laboratoryFilters['data_entry_status'] ?? '') === 'complete')>Complete</option>
                                    <option value="partial" @selected(($laboratoryFilters['data_entry_status'] ?? '') === 'partial')>Partial</option>
                                    <option value="not_started" @selected(($laboratoryFilters['data_entry_status'] ?? '') === 'not_started')>Not started</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="lab-kpi-filter-label">Review / Approval</label>
                                <input type="text" name="lab_review_status" class="form-control form-control-sm" value="{{ $laboratoryFilters['review_status'] ?? '' }}" placeholder="Review status">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary btn-block">
                                    <i class="mdi mdi-filter"></i> Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row mb-4">
                    @foreach([
                        ['label' => 'Jobs Received', 'value' => $laboratoryKpi['jobs_received'] ?? 0],
                        ['label' => 'Jobs Completed', 'value' => $laboratoryKpi['jobs_completed'] ?? 0],
                        ['label' => 'Jobs Pending', 'value' => $laboratoryKpi['jobs_pending'] ?? 0],
                        ['label' => 'Data Entry Complete', 'value' => $laboratoryKpi['data_entry_complete'] ?? 0],
                        ['label' => 'Review Pending', 'value' => $laboratoryKpi['review_pending'] ?? 0],
                        ['label' => 'Review Approved', 'value' => $laboratoryKpi['review_approved'] ?? 0],
                    ] as $stat)
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <div class="quotation-metric-mini flex-column align-items-start">
                                <span class="text-muted">{{ $stat['label'] }}</span>
                                <strong class="lab-kpi-stat-value">{{ $stat['value'] }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>

                @include('layouts.lab.reports.partials.kpi-export-buttons', [
                    'summaryRoute' => route('lab.kpi.laboratory.summary.export'),
                    'detailRoute' => route('lab.kpi.laboratory.detail.export'),
                    'startDate' => $labKpiStart,
                    'endDate' => $labKpiEnd,
                    'startField' => 'start_date',
                    'endField' => 'end_date',
                    'filterFields' => [
                        'lab_client' => $laboratoryFilters['client'] ?? '',
                        'lab_job_id' => $laboratoryFilters['job_id'] ?? '',
                        'lab_data_entry_status' => $laboratoryFilters['data_entry_status'] ?? 'all',
                        'lab_review_status' => $laboratoryFilters['review_status'] ?? '',
                    ],
                    'helper' => 'Detail export: one row per sample in lab with analysis, data entry, review and final report status.',
                ])

                <div class="lab-kpi-table-wrap mt-4">
                    <div class="lab-kpi-table-toolbar">
                        <h6 class="mb-0">Laboratory Detail <span class="text-muted">({{ count($laboratoryDetailRows) }} rows)</span></h6>
                        <small class="text-muted lab-kpi-scroll-hint"><i class="mdi mdi-arrow-left-right"></i> Scroll to view all columns</small>
                    </div>
                    <div class="table-responsive lab-kpi-table-responsive">
                        <table class="table lab-kpi-table mb-0">
                            <thead>
                                <tr>
                                    <th class="lab-kpi-col-date">Date</th>
                                    <th class="lab-kpi-col-client">Client</th>
                                    <th class="lab-kpi-col-num">Samples</th>
                                    <th class="lab-kpi-col-wide">Sample Details</th>
                                    <th class="lab-kpi-col-num">Received</th>
                                    <th class="lab-kpi-col-num">Done</th>
                                    <th class="lab-kpi-col-num">Pending</th>
                                    <th class="lab-kpi-col-status">Data Entry</th>
                                    <th class="lab-kpi-col-status">Review</th>
                                    <th class="lab-kpi-col-num">Approvals</th>
                                    <th class="lab-kpi-col-wide">Final Reports</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laboratoryDetailRows as $row)
                                    <tr>
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['date'], 'type' => 'date'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['client'], 'type' => 'client'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['no_of_samples'] ?? 1, 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['sample_details'], 'type' => 'stack'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['jobs_received'] ?? 0, 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['analysis_completed'] ?? 0, 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['analysis_pending'] ?? 0, 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['data_entry_status'], 'type' => 'status'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['review_for_approval'] ?? ($row['review_approval_status'] ?? '—'), 'type' => 'status'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['pending_approval_count'] ?? 0, 'type' => 'num'])
                                        @include('layouts.lab.reports.partials.kpi-detail-table-cell', ['value' => $row['final_reports_issued'] ?? ($row['final_reports'] ?? '—'), 'type' => 'text'])
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">No laboratory records for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .sample-workflow-kpi-dashboard .lab-kpi-subtitle { font-size: 0.85rem; }
    .sample-workflow-kpi-dashboard .lab-kpi-tabs {
        display: flex; flex-wrap: wrap; gap: 8px; padding: 4px;
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 8px; border: 1px solid transparent;
        background: transparent; color: #475569; font-weight: 600; text-decoration: none;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-tab.active {
        background: #fff; border-color: #cbd5e1; color: #0f172a;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }
    .sample-workflow-kpi-dashboard .lab-kpi-filter-panel {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-filter-panel-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 0.75rem; font-weight: 600; color: #334155;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-filter-label {
        display: block; font-size: 0.75rem; font-weight: 600; color: #64748b;
        margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.02em;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-stat-value { font-size: 1.25rem; }
    .sample-workflow-kpi-dashboard .lab-kpi-table-wrap {
        border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-table-toolbar {
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;
        padding: 0.85rem 1rem; border-bottom: 1px solid #e2e8f0; background: #fafbfc;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-scroll-hint { font-size: 0.78rem; }
    .sample-workflow-kpi-dashboard .lab-kpi-table-responsive {
        max-height: 70vh; overflow: auto;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-table {
        font-size: 0.84rem; margin-bottom: 0; border-collapse: separate; border-spacing: 0;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-table thead th {
        position: sticky; top: 0; z-index: 3;
        background: #f1f5f9; border-bottom: 2px solid #e2e8f0;
        font-weight: 600; color: #334155; font-size: 0.72rem;
        text-transform: uppercase; letter-spacing: 0.03em;
        padding: 0.7rem 0.75rem; white-space: nowrap; vertical-align: middle;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody td {
        padding: 0.75rem; vertical-align: top; border-top: 1px solid #eef2f7;
        background: #fff;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody tr:nth-child(even) td { background: #fafbfc; }
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody tr:hover td { background: #f0f7ff; }
    .sample-workflow-kpi-dashboard .lab-kpi-col-date {
        min-width: 6.5rem; max-width: 7.5rem; white-space: nowrap;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-col-client {
        min-width: 8.5rem; max-width: 11rem;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-col-num {
        min-width: 4.25rem; max-width: 5.5rem; text-align: center; white-space: nowrap;
        font-variant-numeric: tabular-nums; font-weight: 600; color: #0f172a;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-col-id {
        min-width: 7rem; max-width: 10rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.78rem; word-break: break-all;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-col-text {
        min-width: 7.5rem; max-width: 12rem;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-col-wide {
        min-width: 11rem; max-width: 16rem;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-col-status {
        min-width: 9rem; max-width: 13rem;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-cell-text {
        display: block; white-space: normal; word-break: break-word; line-height: 1.45;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-cell-stack {
        display: flex; flex-direction: column; gap: 0.2rem; line-height: 1.35;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-cell-primary {
        font-weight: 600; color: #0f172a; word-break: break-word;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-cell-secondary {
        font-size: 0.78rem; color: #64748b; word-break: break-word;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-badge {
        display: inline-block; max-width: 100%;
        padding: 0.2rem 0.5rem; border-radius: 999px;
        font-size: 0.74rem; font-weight: 600; line-height: 1.35;
        white-space: normal; word-break: break-word;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-badge-success { background: #dcfce7; color: #166534; }
    .sample-workflow-kpi-dashboard .lab-kpi-badge-warning { background: #fef3c7; color: #92400e; }
    .sample-workflow-kpi-dashboard .lab-kpi-badge-info { background: #e0f2fe; color: #075985; }
    .sample-workflow-kpi-dashboard .lab-kpi-badge-primary { background: #dbeafe; color: #1e40af; }
    .sample-workflow-kpi-dashboard .lab-kpi-badge-muted { background: #f1f5f9; color: #475569; }
    .sample-workflow-kpi-dashboard .lab-kpi-table thead th.lab-kpi-col-date,
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody td.lab-kpi-col-date {
        position: sticky; left: 0; z-index: 2;
        box-shadow: 1px 0 0 #e2e8f0;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-table thead th.lab-kpi-col-client,
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody td.lab-kpi-col-client {
        position: sticky; left: 6.5rem; z-index: 2;
        box-shadow: 1px 0 0 #e2e8f0;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-table thead th.lab-kpi-col-date { z-index: 4; }
    .sample-workflow-kpi-dashboard .lab-kpi-table thead th.lab-kpi-col-client { z-index: 4; left: 6.5rem; }
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody tr:nth-child(even) td.lab-kpi-col-date,
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody tr:nth-child(even) td.lab-kpi-col-client { background: #f5f7fa; }
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody tr:hover td.lab-kpi-col-date,
    .sample-workflow-kpi-dashboard .lab-kpi-table tbody tr:hover td.lab-kpi-col-client { background: #e8f2ff; }
    .sample-workflow-kpi-dashboard .quotation-kpi-card {
        background: #fff; border: 1px solid #e9ecef;
        border-left: 4px solid var(--metric-color, #3498db);
        border-radius: 8px; padding: 1rem 1.15rem; height: 100%;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .sample-workflow-kpi-dashboard .quotation-kpi-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08); transform: translateY(-1px);
    }
    .sample-workflow-kpi-dashboard .quotation-kpi-card a { color: inherit; text-decoration: none; }
    .sample-workflow-kpi-dashboard .quotation-kpi-value {
        font-size: 1.75rem; font-weight: 700; margin-bottom: 0; line-height: 1.2;
    }
    .sample-workflow-kpi-dashboard .quotation-kpi-label {
        font-size: 0.9rem; font-weight: 600; margin-bottom: 0.15rem; color: #343a40;
    }
    .sample-workflow-kpi-dashboard .quotation-kpi-sublabel {
        font-size: 0.78rem; color: #6c757d; margin-bottom: 0;
    }
    .sample-workflow-kpi-dashboard .quotation-kpi-icon { font-size: 1.75rem; opacity: 0.85; }
    .sample-workflow-kpi-dashboard .quotation-metric-mini {
        background: #f8f9fa; border-radius: 6px; padding: 0.65rem 0.85rem;
        display: flex; justify-content: space-between; align-items: center;
    }
    .sample-workflow-kpi-dashboard .lab-kpi-export-group .btn { margin-right: 0.35rem; margin-bottom: 0.35rem; }
</style>
