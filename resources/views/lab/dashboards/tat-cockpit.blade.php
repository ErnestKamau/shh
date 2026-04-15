@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Lab TAT & Backlog Cockpit</h1>
                <div>
                    <a href="{{ route('lab-tat.export') }}" class="btn btn-sm btn-outline-primary" title="Export TAT Report">
                        <i class="fas fa-download"></i> Export Report
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()" title="Refresh Dashboard">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row mb-4">
        <div class="col-md-5">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Total Batches</div>
                    <h3 class="h2 mb-0">{{ $tatMetrics['totalBatches'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">Breached TAT</div>
                    <div class="d-flex align-items-center">
                        <h3 class="h2 mb-0 mr-2">{{ $tatMetrics['breachedBatches'] ?? 0 }}</h3>
                        <small class="text-danger">({{ $tatMetrics['breachRate'] ?? 0 }}%)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-uppercase mb-1">Active Analysts</div>
                    <h3 class="h2 mb-0">{{ $tatMetrics['activeAnalysts'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Aging Buckets Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Aging Buckets by Workflow Stage</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Stage</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center bg-success text-white">0-3 Days</th>
                                    <th class="text-center bg-info text-white">4-7 Days</th>
                                    <th class="text-center bg-warning text-white">8-14 Days</th>
                                    <th class="text-center bg-danger text-white">15+ Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agingBuckets as $stage => $buckets)
                                <tr>
                                    <td><strong>{{ $stage }}</strong></td>
                                    <td class="text-center"><span class="badge badge-secondary">{{ $buckets['total'] }}</span></td>
                                    <td class="text-center text-success">{{ $buckets['0-3d'] }}</td>
                                    <td class="text-center text-info">{{ $buckets['4-7d'] }}</td>
                                    <td class="text-center text-warning">{{ $buckets['8-14d'] }}</td>
                                    <td class="text-center text-danger"><strong>{{ $buckets['15+d'] }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No aging data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stage Cycle Time Breakdown -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Stage Cycle Time Breakdown (Days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Stage</th>
                                    <th class="text-center">Sample Count</th>
                                    <th class="text-center">Avg Cycle Time</th>
                                    <th class="text-center">Min</th>
                                    <th class="text-center">Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stageCycleTime as $stage)
                                <tr>
                                    <td><strong>{{ $stage['stage'] }}</strong></td>
                                    <td class="text-center"><span class="badge badge-info">{{ $stage['sampleCount'] }}</span></td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $stage['avgCycleTime'] }} days</span>
                                    </td>
                                    <td class="text-center text-success">{{ $stage['minCycleTime'] }}</td>
                                    <td class="text-center text-danger">{{ $stage['maxCycleTime'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No cycle time data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stuck Batches Queue -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <span class="badge badge-danger">{{ count($stuckBatches) }}</span>
                        Stuck Batches Queue (7+ Days)
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($stuckBatches) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch Code</th>
                                    <th>Current Status</th>
                                    <th class="text-center">Days Stuck</th>
                                    <th class="text-center">Created Date</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stuckBatches as $batch)
                                <tr class="@if($batch['priority'] == 'critical') table-danger @elseif($batch['priority'] == 'high') table-warning @endif">
                                    <td><strong>{{ $batch['batchCode'] }}</strong></td>
                                    <td>{{ $batch['status'] }}</td>
                                    <td class="text-center"><span class="badge badge-{{ $batch['priority'] == 'critical' ? 'danger' : ($batch['priority'] == 'high' ? 'warning' : 'info') }}">{{ $batch['daysStuck'] }}</span></td>
                                    <td class="text-center">{{ $batch['createdAt'] }}</td>
                                    <td><span class="badge badge-{{ $batch['priority'] == 'critical' ? 'danger' : ($batch['priority'] == 'high' ? 'warning' : 'info') }}">{{ ucfirst($batch['priority']) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No stuck batches! All batches are progressing normally.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TAT Breach Heatmap -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">TAT Breach Heatmap (Top 50 Breaches)</h5>
                </div>
                <div class="card-body">
                    @if(count($tatBreaches) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Analyst</th>
                                    <th>Analyte</th>
                                    <th>Sample Type</th>
                                    <th class="text-center">Breach Count</th>
                                    <th class="text-center">Avg Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tatBreaches as $breach)
                                <tr class="@if($breach['avgDaysOverdue'] > 5) table-danger @elseif($breach['avgDaysOverdue'] > 3) table-warning @endif">
                                    <td>{{ $breach['analyst'] }}</td>
                                    <td>{{ $breach['analyte'] }}</td>
                                    <td>{{ $breach['sampleType'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">{{ $breach['breachCount'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $breach['avgDaysOverdue'] }} days</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No TAT breaches detected! All samples are on track.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .page-title {
        font-size: 2rem;
        font-weight: 600;
        color: #333;
    }

    .border-left-primary {
        border-left: 4px solid #007bff;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8;
    }

    .table-sm th, .table-sm td {
        padding: 0.5rem;
    }

    .badge {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
    }

    .card-header {
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1.25rem;
    }
</style>
@endsection
