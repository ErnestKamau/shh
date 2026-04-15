@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Lab Turnaround Time (TAT) Cockpit</h1>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <div class="btn-group" role="group">
                        <button id="exportBtn" type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('lab-tat.export', ['format' => 'excel']) }}">
                                <i class="fas fa-file-excel text-success"></i> Excel
                            </a>
                            <a class="dropdown-item" href="{{ route('lab-tat.export', ['format' => 'pdf']) }}">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </a>
                            <a class="dropdown-item" href="{{ route('lab-tat.export', ['format' => 'csv']) }}">
                                <i class="fas fa-file-csv text-info"></i> CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAT Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Avg TAT (Hours)</div>
                    <h3 class="h2 mb-0">{{ $metrics['averageTAT'] ?? 0 }}</h3>
                    <small class="text-muted">Target: {{ $metrics['targetTAT'] ?? 24 }}h</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">SLA Compliance</div>
                    <h3 class="h2 mb-0">{{ $metrics['slaCompliance'] ?? 0 }}%</h3>
                    <small class="text-{{ $metrics['slaCompliance'] >= 95 ? 'success' : 'danger' }}">
                        {{ $metrics['slaCompliance'] >= 95 ? '✓ On Target' : '⚠ Below Target' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Tests In Progress</div>
                    <h3 class="h2 mb-0">{{ $metrics['testsInProgress'] ?? 0 }}</h3>
                    <small class="text-muted">Pending Results</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-uppercase mb-1">Tests This Week</div>
                    <h3 class="h2 mb-0">{{ $metrics['testsThisWeek'] ?? 0 }}</h3>
                    <small class="text-muted">Completed: {{ $metrics['testsCompleted'] ?? 0 }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Compliance Breakdown -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">SLA Compliance by Test Type</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['complianceByTestType']) && count($metrics['complianceByTestType']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Test Type</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">Compliance</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['complianceByTestType'] as $test)
                                <tr>
                                    <td><strong>{{ $test['testType'] }}</strong></td>
                                    <td class="text-center">{{ $test['count'] }}</td>
                                    <td class="text-center">{{ $test['compliance'] }}%</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $test['compliance'] >= 95 ? 'success' : ($test['compliance'] >= 80 ? 'warning' : 'danger') }}">
                                            {{ $test['compliance'] >= 95 ? 'Compliant' : 'At Risk' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No compliance data available</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">TAT by Turnaround Band</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['tatBands']) && count($metrics['tatBands']) > 0)
                    @foreach($metrics['tatBands'] as $band)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><strong>{{ $band['range'] }}</strong></span>
                            <span class="text-muted">{{ $band['count'] }} tests</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar {{ $band['percentage'] <= 33 ? 'bg-success' : ($band['percentage'] <= 66 ? 'bg-warning' : 'bg-danger') }}" 
                                 role="progressbar" 
                                 style="width: {{ $band['percentage'] }}%" 
                                 aria-valuenow="{{ $band['percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $band['percentage'] }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="alert alert-info">No TAT band data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottleneck Analysis -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🔍 Bottleneck Analysis (Processes with Highest TAT Contribution)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['bottlenecks']) && count($metrics['bottlenecks']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Process Step</th>
                                    <th class="text-center">Avg Duration (Hours)</th>
                                    <th class="text-center">% of Total TAT</th>
                                    <th class="text-center">Tests</th>
                                    <th>Impact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['bottlenecks'] as $bottleneck)
                                <tr class="@if($bottleneck['percentage'] >= 30) table-danger @elseif($bottleneck['percentage'] >= 15) table-warning @endif">
                                    <td><strong>{{ $bottleneck['process'] }}</strong></td>
                                    <td class="text-center">{{ $bottleneck['avgDuration'] }}</td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $bottleneck['percentage'] }}%</span></td>
                                    <td class="text-center">{{ $bottleneck['tests'] }}</td>
                                    <td>
                                        <span class="badge badge-{{ $bottleneck['percentage'] >= 30 ? 'danger' : ($bottleneck['percentage'] >= 15 ? 'warning' : 'info') }}">
                                            {{ $bottleneck['percentage'] >= 30 ? 'Critical' : ($bottleneck['percentage'] >= 15 ? 'High' : 'Medium') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No bottleneck data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 7-Day Trend -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">TAT Trend (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['tatTrend']) && count($metrics['tatTrend']) > 0)
                    <canvas id="tatTrendChart" height="80"></canvas>
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
    @if(isset($metrics['tatTrend']) && count($metrics['tatTrend']) > 0)
    const ctx = document.getElementById('tatTrendChart').getContext('2d');
    const tatTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($metrics['tatTrend'], 'date')) !!},
            datasets: [{
                label: 'Average TAT (Hours)',
                data: {!! json_encode(array_column($metrics['tatTrend'], 'tat')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Target TAT',
                data: Array({!! count($metrics['tatTrend']) !!}).fill({{ $metrics['targetTAT'] ?? 24 }}),
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
                    title: {
                        display: true,
                        text: 'Hours'
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush

@endsection
