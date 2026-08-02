@extends('layouts.lab.layout.app')

@section('title2')
<title>Personal Dashboard | Lab</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<style>
    .personal-dashboard-page .personal-date-filter {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: var(--radius-md);
        padding: var(--space-sm);
    }

    .personal-dashboard-page .personal-date-filter label {
        color: rgba(255, 255, 255, 0.9);
        font-size: var(--text-caption);
        font-weight: var(--font-semibold);
        margin: 0;
    }

    .personal-dashboard-page .personal-date-filter .form-control {
        background: var(--color-surface);
        border-color: rgba(255, 255, 255, 0.45);
        min-width: 138px;
    }

    .personal-dashboard-page .personal-date-filter .btn {
        background: var(--color-surface);
        border-color: var(--color-surface);
        color: var(--color-primary);
    }

    .personal-dashboard-page .personal-stat-strip {
        grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
        margin-bottom: var(--space-lg);
    }

    .personal-dashboard-page .personal-stat-strip .workflow-stat-strip__item {
        align-items: center;
    }

    .personal-dashboard-page .personal-stat-strip .workflow-stat-strip__dot {
        margin-top: 0;
    }

    .personal-dashboard-page .personal-chart-body {
        height: 290px;
        min-height: 290px;
    }

    .personal-dashboard-page .workflow-table {
        margin-bottom: 0;
    }

    .personal-dashboard-page .workflow-table thead th:first-child,
    .personal-dashboard-page .workflow-table tbody td:first-child {
        padding-left: 1rem !important;
    }

    .personal-dashboard-page .workflow-table thead th:last-child,
    .personal-dashboard-page .workflow-table tbody td:last-child {
        padding-right: 1rem !important;
    }

    .personal-dashboard-page .history-tabs {
        border-bottom: 1px solid var(--color-border);
        padding: 0 var(--space-md);
        background: var(--color-surface);
        display: flex;
        gap: var(--space-xs);
        overflow-x: auto;
    }

    .personal-dashboard-page .history-tabs .nav-link {
        border: 0;
        border-bottom: 2px solid transparent;
        color: var(--color-text-secondary);
        font-size: var(--text-sm);
        font-weight: var(--font-semibold);
        padding: 0.8rem 0.75rem;
        white-space: nowrap;
    }

    .personal-dashboard-page .history-tabs .nav-link.active {
        border-bottom-color: var(--color-primary);
        color: var(--color-primary);
        background: var(--color-primary-soft-light);
    }

    .personal-dashboard-page .history-count {
        border-radius: var(--radius-pill);
        background: var(--color-bg);
        color: var(--color-muted);
        font-size: var(--text-caption);
        margin-left: var(--space-xs);
        padding: 0.1rem 0.45rem;
    }

    .personal-dashboard-page .history-tabs .nav-link.active .history-count {
        background: var(--color-primary-soft-medium);
        color: var(--color-primary);
    }

    .personal-dashboard-page .history-status--rejected {
        background: var(--color-error-soft, #fee2e2);
        border: 1px solid #fecaca;
        color: var(--color-error);
    }

    @media (max-width: 991.98px) {
        .personal-dashboard-page .personal-date-filter {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .personal-dashboard-page .personal-date-filter {
            align-items: stretch !important;
            flex-direction: column;
        }

        .personal-dashboard-page .personal-date-filter .form-control {
            min-width: 0;
            width: 100%;
        }
    }
</style>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => null, 'name' => 'Personal Dashboard', 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <div data-sf-slot="after_breadcrumb"></div>
    @include('layouts.partials.dashboard-page-styles')

    <div class="container-fluid workflow-board-page lab-dashboard-page personal-dashboard-page lab-panel-theme workflow-theme lab-surface-theme" data-ls-type="plex">
        <div class="dashboard-welcome-hero d-flex flex-wrap align-items-center justify-content-between" style="gap: 16px;">
            <div>
                <h3>Personal Dashboard</h3>
                <div class="dashboard-welcome-subtitle">Your active workload, turnaround performance, and complete activity history.</div>
            </div>
            <form method="GET" action="{{ route('dashboard-lab-personal') }}" class="d-flex align-items-end personal-date-filter" style="gap: 8px;">
                <div>
                    <label for="personal-start-date">From</label>
                    <input id="personal-start-date" type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="personal-end-date">To</label>
                    <input id="personal-end-date" type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-action-sm">Apply</button>
            </form>
        </div>

        <div class="workflow-stat-strip personal-stat-strip">
            <div class="workflow-stat-strip__item">
                <span class="workflow-stat-strip__dot workflow-stat-strip__dot--primary"></span>
                <div>
                    <p class="workflow-stat-strip__value">{{ $statusCounts['assigned_total'] }}</p>
                    <p class="workflow-stat-strip__label">Assigned</p>
                </div>
            </div>
            <div class="workflow-stat-strip__item">
                <span class="workflow-stat-strip__dot workflow-stat-strip__dot--info"></span>
                <div>
                    <p class="workflow-stat-strip__value">{{ $statusCounts['in_lab'] }}</p>
                    <p class="workflow-stat-strip__label">In Lab</p>
                </div>
            </div>
            <div class="workflow-stat-strip__item">
                <span class="workflow-stat-strip__dot workflow-stat-strip__dot--warning"></span>
                <div>
                    <p class="workflow-stat-strip__value">{{ $statusCounts['verification'] }}</p>
                    <p class="workflow-stat-strip__label">Verification</p>
                </div>
            </div>
            <div class="workflow-stat-strip__item">
                <span class="workflow-stat-strip__dot workflow-stat-strip__dot--neutral"></span>
                <div>
                    <p class="workflow-stat-strip__value">{{ $statusCounts['approval'] }}</p>
                    <p class="workflow-stat-strip__label">Approval</p>
                </div>
            </div>
            <div class="workflow-stat-strip__item">
                <span class="workflow-stat-strip__dot workflow-stat-strip__dot--success"></span>
                <div>
                    <p class="workflow-stat-strip__value">{{ $statusCounts['complete'] }}</p>
                    <p class="workflow-stat-strip__label">Complete</p>
                </div>
            </div>
            <div class="workflow-stat-strip__item">
                <span class="workflow-stat-strip__dot workflow-stat-strip__dot--success"></span>
                <div>
                    <p class="workflow-stat-strip__value text-success">{{ $tatStats['met'] }}</p>
                    <p class="workflow-stat-strip__label">TAT Met</p>
                </div>
            </div>
            <div class="workflow-stat-strip__item">
                <span class="workflow-stat-strip__dot" style="background: var(--color-error);"></span>
                <div>
                    <p class="workflow-stat-strip__value text-danger">{{ $tatStats['not_met'] }}</p>
                    <p class="workflow-stat-strip__label">TAT Not Met</p>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-lg-8 mb-3">
                <section class="workflow-board-panel h-100">
                    <div class="workflow-board-panel-header">
                        <h5><i class="mdi mdi-chart-line" aria-hidden="true"></i> Performance Trend</h5>
                        <span class="text-muted small">{{ $startDate }} to {{ $endDate }}</span>
                    </div>
                    <div class="workflow-board-panel-body personal-chart-body">
                        <canvas id="personalTrendChart" role="img" aria-label="Assigned work, completions, and turnaround performance over the selected period"></canvas>
                    </div>
                </section>
            </div>
            <div class="col-lg-4 mb-3">
                <section class="workflow-board-panel h-100">
                    <div class="workflow-board-panel-header">
                        <h5><i class="mdi mdi-chart-donut" aria-hidden="true"></i> Workload Status</h5>
                    </div>
                    <div class="workflow-board-panel-body personal-chart-body">
                        <canvas id="personalStatusChart" role="img" aria-label="Current assigned workload by status"></canvas>
                    </div>
                </section>
            </div>
        </div>

        <section class="workflow-board-panel">
            <div class="workflow-board-panel-header">
                <h5><i class="mdi mdi-file-sign" aria-hidden="true"></i> Quotation Approvals</h5>
                <span class="text-muted small">{{ $quotationApprovals->count() }} pending</span>
            </div>
            <div class="workflow-board-panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover workflow-table">
                        <thead>
                            <tr>
                                <th>Quotation</th>
                                <th>Customer</th>
                                <th>Requested</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quotationApprovals as $approval)
                                <tr>
                                    <td class="font-weight-bold">{{ $approval['quote_number'] }}</td>
                                    <td>{{ $approval['customer'] }}</td>
                                    <td>{{ $approval['requested_at'] }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-sm btn-outline-primary btn-action-sm" href="{{ $approval['open_url'] }}">Review</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No quotations are awaiting your approval in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="workflow-board-panel">
            <div class="workflow-board-panel-header">
                <h5><i class="mdi mdi-flask-outline" aria-hidden="true"></i> Active Assigned Batches</h5>
                <span class="text-muted small">{{ $batchAssignmentsPage->total() }} total</span>
            </div>
            <div class="workflow-board-panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover workflow-table">
                        <thead>
                            <tr>
                                <th>Batch</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Target Date</th>
                                <th>Assigned On</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batchAssignmentsPage as $assignment)
                                @php $batch = $assignment->sampleHeader; @endphp
                                <tr>
                                    <td class="font-weight-bold">{{ $batch?->batch_code ?? 'N/A' }}</td>
                                    <td>{{ $batch?->client?->name ?? 'N/A' }}</td>
                                    <td><span class="workflow-status-chip">{{ $batch?->status ?? 'N/A' }}</span></td>
                                    <td>{{ $batch?->get_target_date?->date ?? 'N/A' }}</td>
                                    <td>{{ optional($assignment->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="text-right">
                                        @if($batch)
                                            <a class="btn btn-sm btn-outline-primary btn-action-sm" href="{{ route('view-batch-details', ['batch' => $batch->id, 'client' => 0, 'portal' => 0, 'status' => $batch->status ?: 'Samples In Lab']) }}">Open</a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No active assigned batches in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($batchAssignmentsPage->hasPages())
                <div class="px-3 pt-3 border-top">
                    {{ $batchAssignmentsPage->links() }}
                </div>
            @endif
        </section>

        <section id="activity-history" class="workflow-board-panel">
            <div class="workflow-board-panel-header">
                <div>
                    <h5><i class="mdi mdi-history" aria-hidden="true"></i> Complete Activity History</h5>
                    <div class="text-muted small mt-1">All recorded quotation decisions, assignments, verifications, and approvals completed by you.</div>
                </div>
            </div>
            <nav class="history-tabs" aria-label="Activity history categories">
                <a class="nav-link {{ $historyTab === 'quotations' ? 'active' : '' }}" href="{{ route('dashboard-lab-personal', ['start_date' => $startDate, 'end_date' => $endDate, 'history_tab' => 'quotations']) }}#activity-history">
                    Quotation approvals <span class="history-count">{{ $quotationApprovalHistory->total() }}</span>
                </a>
                <a class="nav-link {{ $historyTab === 'assignments' ? 'active' : '' }}" href="{{ route('dashboard-lab-personal', ['start_date' => $startDate, 'end_date' => $endDate, 'history_tab' => 'assignments']) }}#activity-history">
                    Lab work assigned <span class="history-count">{{ $labAssignmentHistory->total() }}</span>
                </a>
                <a class="nav-link {{ $historyTab === 'verifications' ? 'active' : '' }}" href="{{ route('dashboard-lab-personal', ['start_date' => $startDate, 'end_date' => $endDate, 'history_tab' => 'verifications']) }}#activity-history">
                    Sample verification <span class="history-count">{{ $sampleVerificationHistory->total() }}</span>
                </a>
                <a class="nav-link {{ $historyTab === 'approvals' ? 'active' : '' }}" href="{{ route('dashboard-lab-personal', ['start_date' => $startDate, 'end_date' => $endDate, 'history_tab' => 'approvals']) }}#activity-history">
                    Sample approval <span class="history-count">{{ $sampleApprovalHistory->total() }}</span>
                </a>
            </nav>

            <div class="workflow-board-panel-body p-0">
                @if($historyTab === 'quotations')
                    <div class="table-responsive">
                        <table class="table table-hover workflow-table">
                            <thead>
                                <tr>
                                    <th>Quotation</th>
                                    <th>Customer</th>
                                    <th>Decision</th>
                                    <th>Comments</th>
                                    <th>Completed</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotationApprovalHistory as $approvalLog)
                                    @php
                                        $quote = $approvalLog->quotationHeader;
                                        $enquiry = $approvalLog->enquiry;
                                        $quoteUrl = $enquiry?->staffViewUrl() ?? ($quote ? route('add-qoute-details-view', ['id' => $quote->id]) : null);
                                        if ($quoteUrl && $enquiry?->submissionFormInstance?->submissionForm) {
                                            $quoteUrl .= (str_contains($quoteUrl, '?') ? '&' : '?').'tab=quotation_approvals';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">{{ $quote?->quote_number ?? 'N/A' }}</td>
                                        <td>{{ $quote?->customer?->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="workflow-status-pill {{ $approvalLog->action === \App\Models\QuotationApprovalLog::ACTION_APPROVED ? 'workflow-status-pill--complete' : 'history-status--rejected' }}">
                                                {{ ucfirst($approvalLog->action) }}
                                            </span>
                                        </td>
                                        <td>{{ $approvalLog->comments ?: '—' }}</td>
                                        <td>{{ optional($approvalLog->created_at)->format('Y-m-d H:i') }}</td>
                                        <td class="text-right">
                                            @if($quoteUrl)
                                                <a class="btn btn-sm btn-outline-primary btn-action-sm" href="{{ $quoteUrl }}">Open</a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No quotation approval decisions have been recorded for you.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($quotationApprovalHistory->hasPages())
                        <div class="px-3 pt-3 border-top">{{ $quotationApprovalHistory->links() }}</div>
                    @endif
                @elseif($historyTab === 'assignments')
                    <div class="table-responsive">
                        <table class="table table-hover workflow-table">
                            <thead>
                                <tr>
                                    <th>Batch</th>
                                    <th>Client</th>
                                    <th>Assigned By</th>
                                    <th>Assigned</th>
                                    <th>Status</th>
                                    <th>Completed</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labAssignmentHistory as $assignment)
                                    @php $batch = $assignment->sampleHeader; @endphp
                                    <tr>
                                        <td class="font-weight-bold">{{ $batch?->batch_code ?? 'N/A' }}</td>
                                        <td>{{ $batch?->client?->name ?? 'N/A' }}</td>
                                        <td>{{ $assignment->assignedBy?->name ?? 'N/A' }}</td>
                                        <td>{{ optional($assignment->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <span class="workflow-status-pill {{ $assignment->status === \App\Models\Sampleworkflow\SampleHeaderUserAssignment::STATUS_COMPLETED ? 'workflow-status-pill--complete' : 'workflow-status-pill--pending' }}">
                                                {{ ucfirst($assignment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($assignment->completed_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td class="text-right">
                                            @if($batch)
                                                <a class="btn btn-sm btn-outline-primary btn-action-sm" href="{{ route('view-batch-details', ['batch' => $batch->id, 'client' => 0, 'portal' => 0, 'status' => $batch->status ?: 'Samples In Lab']) }}">Open</a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No lab assignments have been recorded for you.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($labAssignmentHistory->hasPages())
                        <div class="px-3 pt-3 border-top">{{ $labAssignmentHistory->links() }}</div>
                    @endif
                @else
                    @php
                        $workflowHistory = $historyTab === 'verifications' ? $sampleVerificationHistory : $sampleApprovalHistory;
                        $activityLabel = $historyTab === 'verifications' ? 'verification' : 'approval';
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover workflow-table">
                            <thead>
                                <tr>
                                    <th>Batch</th>
                                    <th>Client</th>
                                    <th>Stage Entered</th>
                                    <th>Completed</th>
                                    <th>Notes</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workflowHistory as $custody)
                                    @php $batch = $custody->sampleHeader; @endphp
                                    <tr>
                                        <td class="font-weight-bold">{{ $batch?->batch_code ?? 'N/A' }}</td>
                                        <td>{{ $batch?->client?->name ?? 'N/A' }}</td>
                                        <td>{{ optional($custody->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ $custody->moved_out_date ? \Carbon\Carbon::parse((string) $custody->moved_out_date)->format('Y-m-d H:i') : '—' }}</td>
                                        <td>{{ $custody->comments ?: '—' }}</td>
                                        <td class="text-right">
                                            @if($batch)
                                                <a class="btn btn-sm btn-outline-primary btn-action-sm" href="{{ route('view-batch-details', ['batch' => $batch->id, 'client' => 0, 'portal' => 0, 'status' => $batch->status ?: 'Samples In Lab']) }}">Open</a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No sample {{ $activityLabel }} activity has been recorded for you.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($workflowHistory->hasPages())
                        <div class="px-3 pt-3 border-top">{{ $workflowHistory->links() }}</div>
                    @endif
                @endif
            </div>
        </section>
    </div>
</main>
@endsection

@section('script2')
<script>
    (function () {
        const chartSeries = @json($chartSeries);
        const rootStyles = window.getComputedStyle(document.documentElement);
        const themeColor = (name, fallback) => rootStyles.getPropertyValue(name).trim() || fallback;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const colors = {
            primary: themeColor('--color-primary', '#2563eb'),
            success: themeColor('--color-success', '#16a34a'),
            successStrong: themeColor('--color-success-strong', '#15803d'),
            warning: themeColor('--color-warning', '#f59e0b'),
            error: themeColor('--color-error', '#dc2626'),
            info: themeColor('--color-info', '#0ea5e9'),
            muted: themeColor('--color-muted', '#64748b'),
            border: themeColor('--color-border', '#e2e8f0'),
            primarySoft: themeColor('--color-primary-soft', '#eef2ff'),
        };

        const trendCtx = document.getElementById('personalTrendChart');
        if (trendCtx && window.Chart) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: chartSeries.labels,
                    datasets: [
                        { label: 'Assigned', data: chartSeries.assigned, borderColor: colors.primary, backgroundColor: colors.primarySoft, fill: true, tension: 0.25 },
                        { label: 'Completed', data: chartSeries.completed, borderColor: colors.success, fill: false, tension: 0.25 },
                        { label: 'TAT Met', data: chartSeries.tat_met, borderColor: colors.successStrong, fill: false, tension: 0.2 },
                        { label: 'TAT Not Met', data: chartSeries.tat_not_met, borderColor: colors.error, fill: false, tension: 0.2 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: reduceMotion ? false : undefined,
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: colors.border },
                        },
                    },
                },
            });
        }

        const statusCtx = document.getElementById('personalStatusChart');
        if (statusCtx && window.Chart) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(chartSeries.status_breakdown),
                    datasets: [{
                        data: Object.values(chartSeries.status_breakdown),
                        backgroundColor: [colors.info, colors.warning, colors.error, colors.success],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: reduceMotion ? false : undefined,
                    plugins: {
                        legend: { position: 'bottom' },
                    },
                },
            });
        }
    })();
</script>
@endsection
