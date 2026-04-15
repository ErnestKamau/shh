@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Ticket Aging Analysis & Queue Management</h1>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <a href="{{ route('ticket-sla.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Cockpit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Aging Buckets Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Ticket Aging Buckets</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['agingBuckets']) && count($metrics['agingBuckets']) > 0)
                    <div class="row">
                        @foreach($metrics['agingBuckets'] as $bucket)
                        <div class="col-md-2">
                            <div class="card border-left-{{ $bucket['color'] }}">
                                <div class="card-body">
                                    <div class="text-{{ $bucket['color'] }} font-weight-bold text-uppercase mb-1" style="font-size: 11px;">{{ $bucket['label'] }}</div>
                                    <h3 class="h3 mb-0">{{ $bucket['count'] }}</h3>
                                    <small class="text-muted">{{ $bucket['percentage'] }}%</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <canvas id="agingBucketsChart" height="60" class="mt-3"></canvas>
                    @else
                    <div class="alert alert-info">No aging data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets by Age Range (Detailed) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🕐 Detailed Aging Analysis (Current Age Distribution)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['detailedAging']) && count($metrics['detailedAging']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Age Range</th>
                                    <th class="text-center">Ticket Count</th>
                                    <th class="text-center">% Total</th>
                                    <th class="text-center">Avg Time in Status</th>
                                    <th class="text-center">Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['detailedAging'] as $range)
                                <tr class="@if($range['riskLevel'] == 'Critical') table-danger @elseif($range['riskLevel'] == 'High') table-warning @endif">
                                    <td><strong>{{ $range['range'] }}</strong></td>
                                    <td class="text-center">{{ $range['count'] }}</td>
                                    <td class="text-center">{{ $range['percentage'] }}%</td>
                                    <td class="text-center">{{ $range['avgTime'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $range['riskLevel'] == 'Critical' ? 'danger' : ($range['riskLevel'] == 'High' ? 'warning' : 'success') }}">
                                            {{ $range['riskLevel'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No aging analysis data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Unresponded Queue (No Assignee Activity) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">⚠️ Unresponded Queue - No Activity ({{ count($unrespondedQueue) }} tickets)</h5>
                </div>
                <div class="card-body">
                    @if(count($unrespondedQueue) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Priority</th>
                                    <th class="text-center">Opened</th>
                                    <th class="text-center">Hours No Activity</th>
                                    <th>Category</th>
                                    <th>Last Update</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unrespondedQueue as $ticket)
                                <tr class="table-danger">
                                    <td><strong>#{{ $ticket['id'] }}</strong></td>
                                    <td><span class="badge badge-danger">{{ $ticket['priority'] }}</span></td>
                                    <td class="text-center">{{ $ticket['openedDate'] }}</td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $ticket['noActivityHours'] }}h</span></td>
                                    <td>{{ $ticket['category'] }}</td>
                                    <td>{{ $ticket['lastUpdate'] }}</td>
                                    <td><a href="#" class="btn btn-xs btn-warning">Reassign</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> All tickets have recent activity!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Aging by Assignee -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">👥 Oldest Tickets by Assigned Person</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['agingByAssignee']) && count($metrics['agingByAssignee']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Assigned Person</th>
                                    <th class="text-center">Assigned Tickets</th>
                                    <th class="text-center">Oldest Ticket Age</th>
                                    <th class="text-center">Avg Age</th>
                                    <th class="text-center">% Overdue SLA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['agingByAssignee'] as $assignee)
                                <tr class="@if($assignee['pctOverdue'] > 10) table-warning @endif">
                                    <td><strong>{{ $assignee['name'] }}</strong></td>
                                    <td class="text-center">{{ $assignee['count'] }}</td>
                                    <td class="text-center">{{ $assignee['oldestAge'] }}h</td>
                                    <td class="text-center">{{ $assignee['avgAge'] }}h</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $assignee['pctOverdue'] > 10 ? 'warning' : 'success' }}">
                                            {{ $assignee['pctOverdue'] }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No assignee data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Resolution Time Analysis -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🎯 Resolution Time Distribution (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['resolutionTimeDistribution']) && count($metrics['resolutionTimeDistribution']) > 0)
                    <canvas id="resolutionTimeChart" height="80"></canvas>
                    @else
                    <div class="alert alert-info">No resolution time data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Escalation Tracking -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🚀 Escalation Tracking (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['escalations']) && count($metrics['escalations']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket ID</th>
                                    <th class="text-center">Original Priority</th>
                                    <th class="text-center">Escalated Priority</th>
                                    <th class="text-center">Escalation Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['escalations'] as $ticket)
                                <tr>
                                    <td><strong>#{{ $ticket['id'] }}</strong></td>
                                    <td class="text-center">{{ $ticket['originalPriority'] }}</td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $ticket['escalatedPriority'] }}</span></td>
                                    <td class="text-center">{{ $ticket['escalationDate'] }}</td>
                                    <td>{{ $ticket['reason'] }}</td>
                                    <td>
                                        <span class="badge badge-{{ $ticket['status'] == 'Resolved' ? 'success' : 'warning' }}">
                                            {{ $ticket['status'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> No escalations in the last 30 days!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    @if(isset($metrics['agingBuckets']) && count($metrics['agingBuckets']) > 0)
    const agingCtx = document.getElementById('agingBucketsChart').getContext('2d');
    const agingChart = new Chart(agingCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_column($metrics['agingBuckets'], 'label')) !!},
            datasets: [{
                data: {!! json_encode(array_column($metrics['agingBuckets'], 'count')) !!},
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 99, 132, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
    @endif

    @if(isset($metrics['resolutionTimeDistribution']) && count($metrics['resolutionTimeDistribution']) > 0)
    const resCtx = document.getElementById('resolutionTimeChart').getContext('2d');
    const resChart = new Chart(resCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($metrics['resolutionTimeDistribution'], 'range')) !!},
            datasets: [{
                label: 'Ticket Count',
                data: {!! json_encode(array_column($metrics['resolutionTimeDistribution'], 'count')) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
    @endif
</script>
@endpush

@endsection
