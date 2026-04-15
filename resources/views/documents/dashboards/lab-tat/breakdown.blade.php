@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Lab TAT Breakdown Analysis</h1>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <a href="{{ route('lab-tat.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Cockpit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed TAT by Test Type -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🔬 Detailed TAT Breakdown by Test Type</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['detailedBreakdown']) && count($metrics['detailedBreakdown']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Test Type</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">Min TAT (h)</th>
                                    <th class="text-center">Avg TAT (h)</th>
                                    <th class="text-center">Max TAT (h)</th>
                                    <th class="text-center">Std Dev</th>
                                    <th class="text-center">SLA %</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['detailedBreakdown'] as $test)
                                <tr>
                                    <td><strong>{{ $test['testType'] }}</strong></td>
                                    <td class="text-center">{{ $test['count'] }}</td>
                                    <td class="text-center">{{ $test['minTAT'] }}</td>
                                    <td class="text-center"><strong>{{ $test['avgTAT'] }}</strong></td>
                                    <td class="text-center">{{ $test['maxTAT'] }}</td>
                                    <td class="text-center">{{ $test['stdDev'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $test['slaCompliance'] >= 95 ? 'success' : ($test['slaCompliance'] >= 80 ? 'warning' : 'danger') }}">
                                            {{ $test['slaCompliance'] }}%
                                        </span>
                                    </td>
                                    <td>
                                        @if($test['slaCompliance'] >= 95)
                                            <span class="badge badge-success">✓ On Target</span>
                                        @elseif($test['slaCompliance'] >= 80)
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
                    <div class="alert alert-info">No detailed breakdown data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TAT by Department -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🏢 TAT by Department</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['byDepartment']) && count($metrics['byDepartment']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th class="text-center">Avg TAT</th>
                                    <th class="text-center">Tests</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['byDepartment'] as $dept)
                                <tr>
                                    <td><strong>{{ $dept['department'] }}</strong></td>
                                    <td class="text-center">{{ $dept['avgTAT'] }}h</td>
                                    <td class="text-center">{{ $dept['count'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $dept['performance'] >= 80 ? 'bg-success' : ($dept['performance'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                                 style="width: {{ $dept['performance'] }}%">
                                                {{ $dept['performance'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No department data</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- TAT by Status -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Tests by Status</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['byStatus']) && count($metrics['byStatus']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">% Total</th>
                                    <th>Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['byStatus'] as $status)
                                <tr>
                                    <td><strong>{{ $status['status'] }}</strong></td>
                                    <td class="text-center">{{ $status['count'] }}</td>
                                    <td class="text-center">{{ $status['percentage'] }}%</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" style="width: {{ $status['percentage'] }}%">
                                                {{ $status['percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No status data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Longest Delayed Tests -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">⚠️ Top 10 Longest Delayed Tests</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['longestDelayed']) && count($metrics['longestDelayed']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Sample ID</th>
                                    <th>Test Type</th>
                                    <th class="text-center">Received Date</th>
                                    <th class="text-center">Current TAT (h)</th>
                                    <th class="text-center">Target TAT (h)</th>
                                    <th class="text-center">Overdue By</th>
                                    <th>Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['longestDelayed'] as $test)
                                <tr class="table-danger">
                                    <td><strong>{{ $test['sampleId'] }}</strong></td>
                                    <td>{{ $test['testType'] }}</td>
                                    <td class="text-center">{{ $test['receivedDate'] }}</td>
                                    <td class="text-center"><strong>{{ $test['currentTAT'] }}h</strong></td>
                                    <td class="text-center">{{ $test['targetTAT'] }}h</td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $test['overdueBy'] }}h</span></td>
                                    <td>{{ $test['department'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> No delayed tests!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TAT Trend by Department (Last 30 Days) -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📈 TAT Trend by Department (30-Day Forecast)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['departmentTrends']) && count($metrics['departmentTrends']) > 0)
                    <canvas id="deptTrendChart" height="80"></canvas>
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
    @if(isset($metrics['departmentTrends']) && count($metrics['departmentTrends']) > 0)
    const ctx = document.getElementById('deptTrendChart').getContext('2d');
    const deptTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($metrics['dates']) !!},
            datasets: {!! json_encode($metrics['departmentTrends']) !!}
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
                    title: {
                        display: true,
                        text: 'Average TAT (Hours)'
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush

@endsection
