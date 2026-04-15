@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Ticket SLA Breakdown & Performance Analysis</h1>
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

    <!-- Performance Metrics by Category -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Performance Metrics by Ticket Category</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['categoryPerformance']) && count($metrics['categoryPerformance']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Avg Resolution (h)</th>
                                    <th class="text-center">SLA Compliance</th>
                                    <th class="text-center">Breaches</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['categoryPerformance'] as $category)
                                <tr>
                                    <td><strong>{{ $category['name'] }}</strong></td>
                                    <td class="text-center">{{ $category['count'] }}</td>
                                    <td class="text-center">{{ $category['avgResolution'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $category['compliance'] >= 95 ? 'success' : ($category['compliance'] >= 80 ? 'warning' : 'danger') }}">
                                            {{ $category['compliance'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $category['breaches'] }}</td>
                                    <td>
                                        @if($category['compliance'] >= 95)
                                            <span class="badge badge-success">✓ On Target</span>
                                        @elseif($category['compliance'] >= 80)
                                            <span class="badge badge-warning">⚠ At Risk</span>
                                        @else
                                            <span class="badge badge-danger">✗ Failing</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No category performance data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Team Member Rankings -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🏆 Top Performers (SLA Compliance)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['topPerformers']) && count($metrics['topPerformers']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Resolved</th>
                                    <th class="text-center">Compliance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['topPerformers'] as $idx => $performer)
                                <tr class="table-success">
                                    <td>
                                        <strong>{{ ++$idx }}. {{ $performer['name'] }}</strong>
                                    </td>
                                    <td class="text-center">{{ $performer['resolved'] }}</td>
                                    <td class="text-center"><span class="badge badge-success">{{ $performer['compliance'] }}%</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No top performer data</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">⚠️ Needs Improvement (SLA Compliance)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['needsImprovement']) && count($metrics['needsImprovement']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Resolved</th>
                                    <th class="text-center">Compliance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['needsImprovement'] as $performer)
                                <tr class="table-danger">
                                    <td><strong>{{ $performer['name'] }}</strong></td>
                                    <td class="text-center">{{ $performer['resolved'] }}</td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $performer['compliance'] }}%</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success">All team members meeting targets!</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Distribution & Handling -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📈 Priority Distribution & Handling Comparison</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['priorityDistribution']) && count($metrics['priorityDistribution']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Priority</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">% Distribution</th>
                                    <th class="text-center">Avg Age (h)</th>
                                    <th class="text-center">SLA Target</th>
                                    <th class="text-center">Current Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['priorityDistribution'] as $priority)
                                <tr>
                                    <td><strong>{{ $priority['level'] }}</strong></td>
                                    <td class="text-center">{{ $priority['count'] }}</td>
                                    <td class="text-center">{{ $priority['percentage'] }}%</td>
                                    <td class="text-center">{{ $priority['avgAge'] }}</td>
                                    <td class="text-center">{{ $priority['target'] }}h</td>
                                    <td class="text-center">
                                        @if($priority['avgAge'] <= $priority['targetHours'])
                                            <span class="badge badge-success">✓ On Track</span>
                                        @elseif($priority['avgAge'] <= $priority['targetHours'] * 1.2)
                                            <span class="badge badge-warning">⚠ Close</span>
                                        @else
                                            <span class="badge badge-danger">✗ At Risk</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No priority distribution data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- First Response Time Analysis -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">⏱️ First Response Time (FRT) Analysis</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['firstResponseTime']))
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <div class="text-primary font-weight-bold text-uppercase mb-1">Avg FRT</div>
                                    <h3 class="h2 mb-0">{{ $metrics['firstResponseTime']['average'] ?? 0 }}h</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-success">
                                <div class="card-body">
                                    <div class="text-success font-weight-bold text-uppercase mb-1">Fastest Response</div>
                                    <h3 class="h2 mb-0">{{ $metrics['firstResponseTime']['fastest'] ?? 0 }}m</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-danger">
                                <div class="card-body">
                                    <div class="text-danger font-weight-bold text-uppercase mb-1">Slowest Response</div>
                                    <h3 class="h2 mb-0">{{ $metrics['firstResponseTime']['slowest'] ?? 0 }}d</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-warning">
                                <div class="card-body">
                                    <div class="text-warning font-weight-bold text-uppercase mb-1">FRT Compliance</div>
                                    <h3 class="h2 mb-0">{{ $metrics['firstResponseTime']['compliance'] ?? 0 }}%</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <canvas id="frtChart" height="40" class="mt-3"></canvas>
                    @else
                    <div class="alert alert-info">No FRT data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Trend Analysis -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Multi-Metric Performance Trend (60 Days)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['multiMetricTrend']) && count($metrics['multiMetricTrend']) > 0)
                    <canvas id="multiMetricTrendChart" height="80"></canvas>
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
    @if(isset($metrics['firstResponseTime']))
    const frtCtx = document.getElementById('frtChart').getContext('2d');
    const frtChart = new Chart(frtCtx, {
        type: 'bar',
        data: {
            labels: ['Priority 1', 'Priority 2', 'Priority 3', 'Priority 4'],
            datasets: [{
                label: 'Avg First Response Time (hours)',
                data: {!! json_encode($metrics['firstResponseTime']['byPriority'] ?? [2, 4, 8, 24]) !!},
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
            }
        }
    });
    @endif

    @if(isset($metrics['multiMetricTrend']) && count($metrics['multiMetricTrend']) > 0)
    const trendCtx = document.getElementById('multiMetricTrendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($metrics['multiMetricTrend'], 'date')) !!},
            datasets: [{
                label: 'SLA Compliance %',
                data: {!! json_encode(array_column($metrics['multiMetricTrend'], 'slaCompliance')) !!},
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.4
            }, {
                label: 'Avg Resolution Time (h)',
                data: {!! json_encode(array_column($metrics['multiMetricTrend'], 'avgResolution')) !!},
                borderColor: 'rgb(255, 159, 64)',
                tension: 0.4
            }, {
                label: 'Open Tickets',
                data: {!! json_encode(array_column($metrics['multiMetricTrend'], 'openTickets')) !!},
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.4,
                yAxisID: 'y1'
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
                    title: {
                        display: true,
                        text: 'Compliance / Resolution Time'
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Open Tickets'
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush

@endsection
