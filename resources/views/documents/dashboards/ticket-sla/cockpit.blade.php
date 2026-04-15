@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Support Ticket SLA Compliance Cockpit</h1>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <div class="btn-group" role="group">
                        <button id="exportBtn" type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('ticket-sla.export', ['format' => 'excel']) }}">
                                <i class="fas fa-file-excel text-success"></i> Excel
                            </a>
                            <a class="dropdown-item" href="{{ route('ticket-sla.export', ['format' => 'pdf']) }}">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </a>
                            <a class="dropdown-item" href="{{ route('ticket-sla.export', ['format' => 'csv']) }}">
                                <i class="fas fa-file-csv text-info"></i> CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Compliance Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">SLA Compliance %</div>
                    <h3 class="h2 mb-0">{{ $metrics['slaCompliance'] ?? 0 }}%</h3>
                    <small class="text-{{ $metrics['slaCompliance'] >= 95 ? 'success' : 'danger' }}">
                        {{ $metrics['slaCompliance'] >= 95 ? '✓ On Target' : '⚠ Below Target' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">SLA Breaches</div>
                    <h3 class="h2 mb-0">{{ $metrics['slaBreaches'] ?? 0 }}</h3>
                    <small class="text-muted">Open tickets</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Avg Resolution Time</div>
                    <h3 class="h2 mb-0">{{ $metrics['avgResolutionTime'] ?? 0 }}h</h3>
                    <small class="text-muted">{{ $metrics['resolutionTarget'] ?? 24 }}h target</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-uppercase mb-1">Active Tickets</div>
                    <h3 class="h2 mb-0">{{ $metrics['activeTickets'] ?? 0 }}</h3>
                    <small class="text-muted">Unresolved</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical SLA Breaches -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Critical SLA Breaches ({{ count($criticalBreaches) }})
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($criticalBreaches) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Priority</th>
                                    <th class="text-center">Opened</th>
                                    <th class="text-center">Hours Past SLA</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criticalBreaches as $ticket)
                                <tr class="table-danger">
                                    <td><strong>#{{ $ticket['id'] }}</strong></td>
                                    <td><span class="badge badge-danger">{{ $ticket['priority'] }}</span></td>
                                    <td class="text-center">{{ $ticket['openedDate'] }}</td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $ticket['hoursPastSLA'] }}h</span></td>
                                    <td>{{ $ticket['assignedTo'] }}</td>
                                    <td>{{ $ticket['status'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No critical SLA breaches!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SLA by Priority Level -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🎯 SLA Compliance by Priority Level</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['slaByPriority']) && count($metrics['slaByPriority']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Priority</th>
                                    <th class="text-center">Tickets</th>
                                    <th class="text-center">Compliance</th>
                                    <th class="text-center">Breaches</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['slaByPriority'] as $priority)
                                <tr class="@if($priority['compliance'] < 95) table-warning @endif">
                                    <td><strong>{{ $priority['level'] }}</strong></td>
                                    <td class="text-center">{{ $priority['count'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $priority['compliance'] >= 95 ? 'success' : 'danger' }}">
                                            {{ $priority['compliance'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $priority['breaches'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No priority data</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">👥 SLA Compliance by Team</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['slaByTeam']) && count($metrics['slaByTeam']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Team / Person</th>
                                    <th class="text-center">Assigned</th>
                                    <th class="text-center">Compliance</th>
                                    <th class="text-center">Avg Resolution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['slaByTeam'] as $team)
                                <tr>
                                    <td><strong>{{ $team['name'] }}</strong></td>
                                    <td class="text-center">{{ $team['assigned'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $team['compliance'] >= 95 ? 'success' : ($team['compliance'] >= 80 ? 'warning' : 'danger') }}">
                                            {{ $team['compliance'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $team['avgResolution'] }}h</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No team data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Distribution by Status -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Ticket Distribution by Status</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['ticketByStatus']) && count($metrics['ticketByStatus']) > 0)
                    <div class="row">
                        @foreach($metrics['ticketByStatus'] as $status)
                        <div class="col-md-3">
                            <div class="card border-left-{{ $status['color'] }}">
                                <div class="card-body">
                                    <div class="text-{{ $status['color'] }} font-weight-bold text-uppercase mb-1">{{ $status['status'] }}</div>
                                    <h3 class="h2 mb-0">{{ $status['count'] }}</h3>
                                    <small class="text-muted">{{ $status['percentage'] }}% of total</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info">No status data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Compliance Trend -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📈 SLA Compliance Trend (30-Day History)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['slaComplianceTrend']) && count($metrics['slaComplianceTrend']) > 0)
                    <canvas id="slaComplianceChart" height="80"></canvas>
                    @else
                    <div class="alert alert-info">No trend data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    @if(isset($metrics['slaComplianceTrend']) && count($metrics['slaComplianceTrend']) > 0)
    const ctx = document.getElementById('slaComplianceChart').getContext('2d');
    const slaChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($metrics['slaComplianceTrend'], 'date')) !!},
            datasets: [{
                label: 'SLA Compliance %',
                data: {!! json_encode(array_column($metrics['slaComplianceTrend'], 'compliance')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Target (95%)',
                data: Array({!! count($metrics['slaComplianceTrend']) !!}).fill(95),
                borderColor: 'rgb(255, 99, 132)',
                borderDash: [5, 5],
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    @endif
</script>
@endpush

@endsection
