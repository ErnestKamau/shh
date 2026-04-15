@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Ticket SLA Board</h1>
                <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- SLA Compliance Metrics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">First-Response SLA Compliance</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-{{ $firstResponseSla['complianceRate'] >= 95 ? 'success' : ($firstResponseSla['complianceRate'] >= 90 ? 'warning' : 'danger') }}">
                        {{ $firstResponseSla['complianceRate'] ?? 0 }}%
                    </div>
                    <small class="text-muted">
                        {{ $firstResponseSla['slaMet'] }} met of {{ $firstResponseSla['totalTickets'] }} tickets
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Resolution SLA Compliance</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-{{ $resolutionSla['complianceRate'] >= 95 ? 'success' : ($resolutionSla['complianceRate'] >= 90 ? 'warning' : 'danger') }}">
                        {{ $resolutionSla['complianceRate'] ?? 0 }}%
                    </div>
                    <small class="text-muted">
                        {{ $resolutionSla['slaMet'] }} met of {{ $resolutionSla['totalTickets'] }} tickets
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Open Tickets</div>
                    <h3 class="h2 mb-0">{{ $slaMetrics['openTickets'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-uppercase mb-1">In Progress</div>
                    <h3 class="h2 mb-0">{{ $slaMetrics['inProgressTickets'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">Closed Tickets</div>
                    <h3 class="h2 mb-0">{{ $slaMetrics['closedTickets'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Avg Resolution</div>
                    <h3 class="h2 mb-0">{{ round($slaMetrics['avgResolutionTime'] ?? 0) }} days</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Unresponded Queue -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <span class="badge badge-danger">{{ count($unrespondedQueue) }}</span>
                        Unresponded Queue (No Response in 24h)
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($unrespondedQueue) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Customer</th>
                                    <th class="text-center">Created</th>
                                    <th class="text-center">Hours Since Creation</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unrespondedQueue as $ticket)
                                <tr class="@if($ticket['hoursSinceCreation'] > 48) table-danger @elseif($ticket['hoursSinceCreation'] > 24) table-warning @endif">
                                    <td><strong>{{ $ticket['ticketNumber'] }}</strong></td>
                                    <td>{{ $ticket['customer'] }}</td>
                                    <td class="text-center">{{ $ticket['createdAt'] }}</td>
                                    <td class="text-center"><span class="badge badge-{{ $ticket['hoursSinceCreation'] > 48 ? 'danger' : 'warning' }}">{{ $ticket['hoursSinceCreation'] }}</span></td>
                                    <td><span class="badge badge-warning">{{ $ticket['priority'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No unresponded tickets! All tickets are being handled.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Aging by Assignee/Team -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Aging by Assignee/Team (Active Tickets)</h5>
                </div>
                <div class="card-body">
                    @if(count($agingByAssignee) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Assignee</th>
                                    <th class="text-center">Active Tickets</th>
                                    <th class="text-center">Avg Days Open</th>
                                    <th class="text-center">Max Days Open</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agingByAssignee as $item)
                                <tr class="@if($item['avgDaysOpen'] > 7) table-warning @endif">
                                    <td><strong>{{ $item['assignee'] }}</strong></td>
                                    <td class="text-center"><span class="badge badge-primary">{{ $item['ticketCount'] }}</span></td>
                                    <td class="text-center">{{ round($item['avgDaysOpen'], 1) }} days</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $item['maxDaysOpen'] > 14 ? 'danger' : ($item['maxDaysOpen'] > 7 ? 'warning' : 'success') }}">
                                            {{ $item['maxDaysOpen'] }} days
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No active tickets to display.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Escalation Waterfall -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Escalation Waterfall</h5>
                </div>
                <div class="card-body">
                    @if(count($escalationWaterfall) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Escalation Level</th>
                                    <th class="text-center">Ticket Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($escalationWaterfall as $escalation)
                                <tr>
                                    <td><strong>{{ $escalation['level'] }}</strong></td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $escalation['ticketCount'] }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No escalations recorded.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Reopen Rate -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Reopen Rate</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-{{ $reopenRate['reopenRate'] < 5 ? 'success' : ($reopenRate['reopenRate'] < 10 ? 'warning' : 'danger') }}">
                        {{ $reopenRate['reopenRate'] ?? 0 }}%
                    </div>
                    <small class="text-muted">
                        {{ $reopenRate['reopenedTickets'] }} reopened of {{ $reopenRate['totalClosed'] }} closed tickets
                    </small>
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

    .border-left-info {
        border-left: 4px solid #17a2b8;
    }

    .border-left-success {
        border-left: 4px solid #28a745;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107;
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

    .display-4 {
        font-size: 3.5rem;
        font-weight: 300;
        line-height: 1.2;
    }
</style>
@endsection
