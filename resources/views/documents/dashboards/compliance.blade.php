@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Document Compliance Workload Board</h1>
                <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Compliance Metrics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">Overdue Documents</div>
                    <h3 class="h2 mb-0">{{ $documentMetrics['overdueDocuments'] ?? 0 }}</h3>
                    <small class="text-danger">({{ $documentMetrics['overduePercentage'] ?? 0 }}%)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Due Soon (30 Days)</div>
                    <h3 class="h2 mb-0">{{ $documentMetrics['dueSoon'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-uppercase mb-1">Total Documents</div>
                    <h3 class="h2 mb-0">{{ $documentMetrics['totalDocuments'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Documents -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <span class="badge badge-danger">{{ count($overdueDocuments) }}</span>
                        Overdue Documents
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($overdueDocuments) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Name</th>
                                    <th>Type</th>
                                    <th class="text-center">Overdue Date</th>
                                    <th class="text-center">Days Overdue</th>
                                    <th>Owner</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueDocuments as $doc)
                                <tr class="table-danger">
                                    <td><strong>{{ $doc['documentName'] }}</strong></td>
                                    <td>{{ $doc['documentType'] }}</td>
                                    <td class="text-center">{{ $doc['overdueDate'] }}</td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $doc['daysOverdue'] }}</span></td>
                                    <td>{{ $doc['owner'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No overdue documents!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Renewals (90 Days) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <span class="badge badge-info">{{ count($upcomingRenewals) }}</span>
                        Upcoming Renewals (Next 90 Days)
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($upcomingRenewals) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Name</th>
                                    <th>Type</th>
                                    <th class="text-center">Renewal Date</th>
                                    <th class="text-center">Days Until Renewal</th>
                                    <th>Approval Status</th>
                                    <th>Owner</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingRenewals as $renewal)
                                <tr class="@if($renewal['urgency'] == 'critical') table-danger @elseif($renewal['urgency'] == 'high') table-warning @endif">
                                    <td><strong>{{ $renewal['documentName'] }}</strong></td>
                                    <td>{{ $renewal['documentType'] }}</td>
                                    <td class="text-center">{{ $renewal['renewalDate'] }}</td>
                                    <td class="text-center"><span class="badge badge-{{ $renewal['urgency'] == 'critical' ? 'danger' : 'warning' }}">{{ $renewal['daysUntilRenewal'] }}</span></td>
                                    <td><span class="badge badge-info">{{ $renewal['approvalStatus'] }}</span></td>
                                    <td>{{ $renewal['owner'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No upcoming renewals in the next 90 days!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Renewal Workload by Month -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Renewal Workload by Month (12-Month Forecast)</h5>
                </div>
                <div class="card-body">
                    @if(count($renewalWorkload) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th class="text-center">Documents Due</th>
                                    <th class="text-center">Pending Approval</th>
                                    <th class="text-center">Approval Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($renewalWorkload as $month)
                                <tr>
                                    <td><strong>{{ $month['month'] }}</strong></td>
                                    <td class="text-center"><span class="badge badge-info">{{ $month['documentsDue'] }}</span></td>
                                    <td class="text-center"><span class="badge badge-warning">{{ $month['pendingApproval'] }}</span></td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $month['approvalRate'] >= 75 ? 'success' : ($month['approvalRate'] >= 50 ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ min($month['approvalRate'], 100) }}%">
                                                {{ $month['approvalRate'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No renewal workload data available.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue by Owner -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Overdue Documents by Owner</h5>
                </div>
                <div class="card-body">
                    @if(count($overdueOwnership) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Owner</th>
                                    <th class="text-center">Overdue Count</th>
                                    <th class="text-center">Most Overdue (Days)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueOwnership as $owner)
                                <tr class="table-danger">
                                    <td><strong>{{ $owner['owner'] }}</strong></td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $owner['overdueCount'] }}</span></td>
                                    <td class="text-center">{{ abs($owner['earliestExpiry']) }} days</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No overdue documents by owner!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Cycle Time -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Approval Cycle Time Analysis</h5>
                </div>
                <div class="card-body">
                    @if(count($approvalCycleTime) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Approval Status</th>
                                    <th class="text-center">Document Count</th>
                                    <th class="text-center">Avg Cycle Time (Days)</th>
                                    <th class="text-center">Max Cycle Time (Days)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvalCycleTime as $status)
                                <tr>
                                    <td><strong>{{ $status['status'] }}</strong></td>
                                    <td class="text-center">{{ $status['documentCount'] }}</td>
                                    <td class="text-center">{{ round($status['avgCycleDays'], 1) }}</td>
                                    <td class="text-center"><span class="badge badge-warning">{{ $status['maxCycleDays'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No approval cycle time data available.
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

    .border-left-danger {
        border-left: 4px solid #dc3545;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107;
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

    .progress {
        background-color: #e9ecef;
    }

    .progress-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: white;
        font-weight: bold;
    }
</style>
@endsection
