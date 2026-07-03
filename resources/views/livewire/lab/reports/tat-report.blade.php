@section('title2')
    <title>TAT Reports | Lab Reports</title>
    <style>
        .tat-report-page .stat-cards-row {
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        }
        .tat-report-page .tat-report-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .tat-report-page .tat-report-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: transparent;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
        }
        .tat-report-page .tat-report-tab.active {
            background: #fff;
            border-color: #cbd5e1;
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .tat-report-page .tat-table-wrap {
            overflow-x: auto;
        }
        .tat-report-page .tat-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 0.875rem;
        }
        .tat-report-page .tat-table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
            font-weight: 600;
            color: #334155;
        }
        .tat-report-page .tat-table tbody td {
            vertical-align: middle;
            border-top: 1px solid #f1f5f9;
        }
        .tat-report-page .tat-badge-overdue {
            background: #fee2e2;
            color: #b91c1c;
        }
        .tat-report-page .tat-badge-due-today {
            background: #fef3c7;
            color: #b45309;
        }
        .tat-report-page .tat-badge-on-time {
            background: #dcfce7;
            color: #15803d;
        }
        .tat-report-page .tat-badge-no-target {
            background: #f1f5f9;
            color: #64748b;
        }
    </style>
@endsection

<main class="container-fluid workflow-board-page lab-panel-theme tat-report-page">
    @include('layouts.lab.partials.lab-panel-theme-styles')

    @php
        $breadcrumbItems = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('lab-report-tat'), 'name' => 'TAT Reports', 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1"><i class="mdi mdi-timer-sand"></i> TAT Reports</h4>
            <p class="text-muted mb-0 small">Batch deadline tracking and per-parameter turnaround analysis.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
            <a href="{{ $activeTab === 'batch' ? $exportBatchUrl : $exportParameterUrl }}"
                class="btn btn-sm btn-outline-primary">
                <i class="mdi mdi-download"></i> Export CSV
            </a>
        </div>
    </div>

    <div class="stat-cards-row mb-3">
        @if($activeTab === 'batch')
            <div class="stat-card">
                <div class="stat-card-label">Active Batches</div>
                <div class="stat-card-content">
                    <div class="stat-card-value">{{ $batchKpis['active_batches'] ?? 0 }}</div>
                    <div class="stat-card-icon"><i class="mdi mdi-flask-outline"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Overdue</div>
                <div class="stat-card-content">
                    <div class="stat-card-value text-danger">{{ $batchKpis['overdue_batches'] ?? 0 }}</div>
                    <div class="stat-card-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Due Today</div>
                <div class="stat-card-content">
                    <div class="stat-card-value text-warning">{{ $batchKpis['due_today_batches'] ?? 0 }}</div>
                    <div class="stat-card-icon"><i class="mdi mdi-calendar-today"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">SLA Compliance</div>
                <div class="stat-card-content">
                    <div class="stat-card-value">{{ $batchKpis['sla_compliance_rate'] ?? 0 }}%</div>
                    <div class="stat-card-icon"><i class="mdi mdi-shield-check-outline"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">On Time</div>
                <div class="stat-card-content">
                    <div class="stat-card-value text-success">{{ $batchKpis['on_time_batches'] ?? 0 }}</div>
                    <div class="stat-card-icon"><i class="mdi mdi-check-circle-outline"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">No Target</div>
                <div class="stat-card-content">
                    <div class="stat-card-value">{{ $batchKpis['no_target_batches'] ?? 0 }}</div>
                    <div class="stat-card-icon"><i class="mdi mdi-calendar-remove-outline"></i></div>
                </div>
            </div>
        @else
            <div class="stat-card">
                <div class="stat-card-label">Completed Parameters</div>
                <div class="stat-card-content">
                    <div class="stat-card-value">{{ $parameterKpis['total_parameters'] ?? 0 }}</div>
                    <div class="stat-card-icon"><i class="mdi mdi-test-tube"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Within TAT</div>
                <div class="stat-card-content">
                    <div class="stat-card-value text-success">{{ $parameterKpis['on_time_count'] ?? 0 }}</div>
                    <div class="stat-card-icon"><i class="mdi mdi-check-circle-outline"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Delayed</div>
                <div class="stat-card-content">
                    <div class="stat-card-value text-danger">{{ $parameterKpis['delayed_count'] ?? 0 }}</div>
                    <div class="stat-card-icon"><i class="mdi mdi-timer-off-outline"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">On-Time Rate</div>
                <div class="stat-card-content">
                    <div class="stat-card-value">{{ $parameterKpis['on_time_rate'] ?? 0 }}%</div>
                    <div class="stat-card-icon"><i class="mdi mdi-chart-line"></i></div>
                </div>
            </div>
        @endif
    </div>

    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h6><i class="mdi mdi-filter-variant"></i> Filters</h6>
            <div class="d-flex gap-2">
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light">Reset</button>
                <button type="button" wire:click="applyFilters" class="btn btn-sm btn-primary">
                    <i class="mdi mdi-filter"></i> Apply
                </button>
            </div>
        </div>
        <div class="workflow-board-panel-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="control-label">Receipt Date From</label>
                        <input type="date" wire:model.defer="dateFrom" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="control-label">Receipt Date To</label>
                        <input type="date" wire:model.defer="dateTo" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="control-label">Sample Type</label>
                        <select wire:model="sampleTypeId" class="form-control">
                            <option value="">All</option>
                            @foreach($sampleTypes as $sampleType)
                                <option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($activeTab === 'batch')
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="control-label">Workflow Stage</label>
                            <select wire:model.defer="workflowStage" class="form-control">
                                <option value="">All</option>
                                @foreach($workflowStages as $stage)
                                    <option value="{{ $stage }}">{{ $stage }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="control-label">Deadline Status</label>
                            <select wire:model.defer="deadlineStatus" class="form-control">
                                <option value="all">All</option>
                                <option value="overdue">Overdue</option>
                                <option value="due_today">Due Today</option>
                                <option value="on_time">On Time</option>
                                <option value="no_target">No Target</option>
                            </select>
                        </div>
                    </div>
                @else
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="control-label">Analyst</label>
                            <select wire:model.defer="userId" class="form-control">
                                <option value="">All</option>
                                @foreach($analysts as $analyst)
                                    <option value="{{ $analyst->id }}">{{ $analyst->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="control-label">Analysis Type</label>
                            <select wire:model="analysisTypeId" class="form-control" @disabled(empty($analysisTypes))>
                                <option value="">All</option>
                                @foreach($analysisTypes as $analysisType)
                                    <option value="{{ $analysisType['id'] }}">{{ $analysisType['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="control-label">Analyte</label>
                            <select wire:model.defer="analyteId" class="form-control" @disabled(empty($analytes))>
                                <option value="">All</option>
                                @foreach($analytes as $analyte)
                                    <option value="{{ $analyte['id'] }}">{{ $analyte['name'] }}@if(!empty($analyte['code'])) - {{ $analyte['code'] }}@endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="control-label">TAT Remark</label>
                            <select wire:model.defer="tatRemark" class="form-control">
                                <option value="all">All</option>
                                @foreach($tatRemarks as $remarkId => $remarkLabel)
                                    <option value="{{ $remarkId }}">{{ $remarkLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="tat-report-tabs">
        <button type="button"
            wire:click="setTab('batch')"
            @class(['tat-report-tab', 'active' => $activeTab === 'batch'])>
            <i class="mdi mdi-calendar-clock"></i> Batch Deadline TAT
        </button>
        <button type="button"
            wire:click="setTab('parameters')"
            @class(['tat-report-tab', 'active' => $activeTab === 'parameters'])>
            <i class="mdi mdi-test-tube"></i> Per-Parameter TAT
        </button>
    </div>

    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h6>
                @if($activeTab === 'batch')
                    <i class="mdi mdi-table"></i> Batch Deadline Report
                @else
                    <i class="mdi mdi-table"></i> Per-Parameter TAT Report
                @endif
            </h6>
            <span class="text-muted small">
                @if($activeTab === 'batch')
                    {{ $batchRows->total() }} batch(es)
                @else
                    {{ $parameterRows->total() }} parameter result(s)
                @endif
            </span>
        </div>
        <div class="workflow-board-panel-body p-0">
            @if($activeTab === 'batch')
                <div class="tat-table-wrap">
                    <table class="table tat-table workflow-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Batch Code</th>
                                <th>Client</th>
                                <th>Sample Type</th>
                                <th>Workflow Stage</th>
                                <th>Priority</th>
                                <th>Receipt Date</th>
                                <th>Target Date</th>
                                <th>Status Days</th>
                                <th>Deadline</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batchRows as $row)
                                <tr>
                                    <td class="font-weight-bold">{{ $row['batch_code'] }}</td>
                                    <td>{{ $row['client_name'] }}</td>
                                    <td>{{ $row['sample_type_name'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td>{{ $row['priority'] ?? '—' }}</td>
                                    <td>{{ $row['receipt_date'] }}</td>
                                    <td>{{ $row['target_date'] }}</td>
                                    <td @class(['text-danger font-weight-bold' => ($row['status_days'] ?? null) !== null && $row['status_days'] < 0])>
                                        {{ $row['status_days_label'] }}
                                    </td>
                                    <td>
                                        @php
                                            $bucketClass = match($row['deadline_bucket']) {
                                                'overdue' => 'tat-badge-overdue',
                                                'due_today' => 'tat-badge-due-today',
                                                'on_time' => 'tat-badge-on-time',
                                                default => 'tat-badge-no-target',
                                            };
                                            $bucketLabel = match($row['deadline_bucket']) {
                                                'overdue' => 'Overdue',
                                                'due_today' => 'Due Today',
                                                'on_time' => 'On Time',
                                                default => 'No Target',
                                            };
                                        @endphp
                                        <span class="badge {{ $bucketClass }}">{{ $bucketLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No batch deadline records match the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($batchRows->hasPages())
                    <div class="p-3 border-top">{{ $batchRows->links() }}</div>
                @endif
            @else
                <div class="tat-table-wrap">
                    <table class="table tat-table workflow-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Analyte</th>
                                <th>Sample Code</th>
                                <th>Sample Type</th>
                                <th>Analysis</th>
                                <th>Receipt Date</th>
                                <th>Start Analysis</th>
                                <th>Expected Date</th>
                                <th>Actual Date</th>
                                <th>TAT Days</th>
                                <th>Analyst</th>
                                <th>TAT Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parameterRows as $row)
                                @php $tatOffset = (int) ($row['signed_offset'] ?? 0); @endphp
                                <tr>
                                    <td>{{ $row['analyte_name'] }}</td>
                                    <td>{{ $row['sample_code'] }}</td>
                                    <td>{{ $row['sample_type_name'] }}</td>
                                    <td>{{ $row['analysis_type_name'] }}</td>
                                    <td>{{ $row['receipt_date'] }}</td>
                                    <td>{{ $row['start_date_analysis'] }}</td>
                                    <td>{{ $row['tat_date'] }}</td>
                                    <td>{{ $row['finished_date'] }}</td>
                                    <td @class([
                                        'text-success' => $tatOffset < 0,
                                        'text-muted' => $tatOffset === 0,
                                        'text-danger' => $tatOffset > 0,
                                    ])>
                                        @if($tatOffset < 0)
                                            {{ $tatOffset }}d
                                        @elseif($tatOffset === 0)
                                            0d
                                        @else
                                            +{{ $tatOffset }}d
                                        @endif
                                    </td>
                                    <td>{{ $row['analyst_name'] }}</td>
                                    <td @class([
                                        'bg-warning' => (int) ($row['tat_remark'] ?? 0) === 4,
                                        'bg-danger text-white' => (int) ($row['tat_remark'] ?? 0) === 5,
                                    ])>
                                        {{ $row['tat_remark_label'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No per-parameter TAT records match the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($parameterRows->hasPages())
                    <div class="p-3 border-top">{{ $parameterRows->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</main>
