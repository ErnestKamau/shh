@extends('layouts.lab.layout.app')

@section('title2')
<title>Personal Dashboard | Lab</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<style>
    .personal-dashboard-shell {
        background: linear-gradient(180deg, #f6f8fc 0%, #edf1f7 100%);
        border: 1px solid #e4e8f0;
        border-radius: 16px;
        padding: 1.25rem;
    }

    .personal-hero {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
        border-radius: 14px;
        color: #fff;
        padding: 1rem 1.1rem;
        box-shadow: 0 12px 24px var(--color-primary-highlight);
        margin-bottom: 1rem;
    }

    .personal-hero h4 {
        color: #fff;
        margin-bottom: 0.2rem;
    }

    .personal-hero .text-muted {
        color: rgba(255, 255, 255, 0.86) !important;
    }

    .personal-date-filter {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 10px;
        padding: 0.55rem;
        backdrop-filter: blur(2px);
    }

    .personal-date-filter .form-control {
        background: #fff;
        border-color: rgba(255, 255, 255, 0.4);
    }

    .personal-date-filter .btn {
        border-color: #fff;
        color: #fff;
        background: transparent;
        font-weight: 600;
    }

    .personal-date-filter .btn:hover {
        background: #fff;
        color: var(--color-primary);
    }

    .personal-kpi-card {
        border-radius: 12px;
        border: 1px solid #e3e8f3;
        background: #fff;
        box-shadow: 0 6px 12px rgba(32, 45, 69, 0.06);
        height: 100%;
    }

    .personal-kpi-card .card-body {
        padding: 1rem;
    }

    .personal-kpi-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6f7c91;
        font-weight: 700;
    }

    .personal-kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1d293d;
        margin-top: 0.35rem;
        line-height: 1.1;
    }

    .personal-kpi-card.kpi-tat-met {
        border-left: 4px solid #16a34a;
    }

    .personal-kpi-card.kpi-tat-not-met {
        border-left: 4px solid #dc2626;
    }

    .personal-panel {
        border-radius: 12px;
        border: 1px solid #e3e8f3;
        background: #fff;
        box-shadow: 0 8px 16px rgba(24, 35, 58, 0.06);
        height: 100%;
    }

    .personal-panel .card-header {
        border-bottom: 1px solid #edf1f7;
        background: #fff;
        font-weight: 700;
        color: #1f2d44;
    }

    .personal-table-wrap {
        border-radius: 12px;
        border: 1px solid #e3e8f3;
        background: #fff;
        box-shadow: 0 8px 16px rgba(24, 35, 58, 0.06);
        overflow: hidden;
    }

    .personal-table-wrap .table {
        margin-bottom: 0;
    }

    .personal-table-wrap thead th {
        background: #f8fafd;
        border-bottom: 1px solid #e3e8f3;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #68768f;
        font-weight: 700;
    }

    .personal-table-wrap tbody td {
        vertical-align: middle;
    }

    .personal-status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 700;
        background: #eef2f8;
        color: #334155;
    }

    @media (max-width: 991.98px) {
        .personal-dashboard-shell {
            padding: 0.8rem;
        }

        .personal-hero {
            padding: 0.85rem;
        }

        .personal-kpi-value {
            font-size: 1.45rem;
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

    <div class="p-4 personal-dashboard-shell">
        <div class="personal-hero d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
            <div>
                <h4>Personal Dashboard</h4>
                <div class="text-muted">Track your assigned batches, progress, and turnaround performance.</div>
            </div>
            <form method="GET" action="{{ route('dashboard-lab-personal') }}" class="d-flex align-items-center personal-date-filter" style="gap: 8px;">
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                <button type="submit" class="btn btn-sm">Apply</button>
            </form>
        </div>

        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <div class="personal-kpi-card">
                    <div class="card-body">
                        <div class="personal-kpi-label">Assigned</div>
                        <div class="personal-kpi-value">{{ $statusCounts['assigned_total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="personal-kpi-card">
                    <div class="card-body">
                        <div class="personal-kpi-label">In Lab</div>
                        <div class="personal-kpi-value">{{ $statusCounts['in_lab'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="personal-kpi-card">
                    <div class="card-body">
                        <div class="personal-kpi-label">Verification</div>
                        <div class="personal-kpi-value">{{ $statusCounts['verification'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="personal-kpi-card">
                    <div class="card-body">
                        <div class="personal-kpi-label">Approval</div>
                        <div class="personal-kpi-value">{{ $statusCounts['approval'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="personal-kpi-card">
                    <div class="card-body">
                        <div class="personal-kpi-label">Complete</div>
                        <div class="personal-kpi-value">{{ $statusCounts['complete'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="personal-kpi-card kpi-tat-met">
                    <div class="card-body">
                        <div class="personal-kpi-label">TAT Met</div>
                        <div class="personal-kpi-value text-success">{{ $tatStats['met'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="personal-kpi-card kpi-tat-not-met">
                    <div class="card-body">
                        <div class="personal-kpi-label">TAT Not Met</div>
                        <div class="personal-kpi-value text-danger">{{ $tatStats['not_met'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-lg-8 mb-3">
                <div class="card personal-panel">
                    <div class="card-header">Performance Trend</div>
                    <div class="card-body"><canvas id="personalTrendChart" height="120"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card personal-panel">
                    <div class="card-header">Assigned Status Breakdown</div>
                    <div class="card-body"><canvas id="personalStatusChart" height="120"></canvas></div>
                </div>
            </div>
        </div>

        <div class="personal-table-wrap">
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="background: #fff;">
                <h6 class="mb-0" style="font-weight: 700; color: #1f2d44;">Assigned Batches</h6>
                <span class="text-muted small">{{ $batchAssignmentsPage->total() }} total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Batch</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Target Date</th>
                            <th>Assigned On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batchAssignmentsPage as $assignment)
                            @php $batch = $assignment->sampleHeader; @endphp
                            <tr>
                                <td class="font-weight-bold">{{ $batch?->batch_code ?? 'N/A' }}</td>
                                <td>{{ $batch?->client?->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="personal-status-pill">{{ $batch?->status ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $batch?->get_target_date?->date ?? 'N/A' }}</td>
                                <td>{{ optional($assignment->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($batch)
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('view-batch-details', ['batch' => $batch->id, 'client' => 0, 'portal' => 0, 'status' => $batch->status ?: 'Samples In Lab']) }}">Open</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No assigned batches in selected period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $batchAssignmentsPage->links() }}
        </div>
    </div>
</main>
@endsection

@section('script2')
<script>
    (function () {
        const chartSeries = @json($chartSeries);

        const trendCtx = document.getElementById('personalTrendChart');
        if (trendCtx && window.Chart) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: chartSeries.labels,
                    datasets: [
                        { label: 'Assigned', data: chartSeries.assigned, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.2)', fill: true, tension: 0.25 },
                        { label: 'Completed', data: chartSeries.completed, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.15)', fill: true, tension: 0.25 },
                        { label: 'TAT Met', data: chartSeries.tat_met, borderColor: '#15803d', fill: false, tension: 0.2 },
                        { label: 'TAT Not Met', data: chartSeries.tat_not_met, borderColor: '#dc2626', fill: false, tension: 0.2 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
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
                        backgroundColor: ['#3b82f6', '#f59e0b', '#ef4444', '#10b981'],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                    },
                },
            });
        }
    })();
</script>
@endsection
