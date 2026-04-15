@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Lab TAT Heatmap Analysis</h1>
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

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="heatmapFilterForm" class="form-inline" method="GET">
                        <div class="form-group mr-3">
                            <label for="testType" class="mr-2">Test Type:</label>
                            <select id="testType" name="test_type" class="form-control form-control-sm">
                                <option value="">All Tests</option>
                                @if(isset($metrics['allTestTypes']))
                                    @foreach($metrics['allTestTypes'] as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label for="department" class="mr-2">Department:</label>
                            <select id="department" name="department" class="form-control form-control-sm">
                                <option value="">All Departments</option>
                                @if(isset($metrics['allDepartments']))
                                    @foreach($metrics['allDepartments'] as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <button type="reset" class="btn btn-sm btn-secondary ml-2">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Heatmap: TAT by Test Type and Department -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 TAT Heatmap: Test Type vs Department</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['heatmapData']) && count($metrics['heatmapData']) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Test Type / Department</th>
                                    @if(isset($metrics['departments']))
                                        @foreach($metrics['departments'] as $dept)
                                        <th>{{ $dept }}</th>
                                        @endforeach
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['heatmapData'] as $testType => $deptData)
                                <tr>
                                    <td><strong>{{ $testType }}</strong></td>
                                    @foreach($deptData as $tat)
                                    <td>
                                        <div class="p-2 rounded" style="background-color: {{ $this->getTATColor($tat['tat']) }}; color: white; font-weight: bold;">
                                            {{ $tat['tat'] }}h<br>
                                            <small>({{ $tat['count'] }} tests)</small>
                                        </div>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <p class="text-muted"><small>Color Legend: 
                            <span style="background-color: #28a745; color: white;" class="px-2">Green (≤24h)</span>
                            <span style="background-color: #ffc107; color: white;" class="px-2">Yellow (24-48h)</span>
                            <span style="background-color: #dc3545; color: white;" class="px-2">Red (>48h)</span>
                        </small></p>
                    </div>
                    @else
                    <div class="alert alert-info">No heatmap data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Compliance Heatmap -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🎯 SLA Compliance Heatmap by Day and Test Type</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['slaHeatmap']) && count($metrics['slaHeatmap']) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Test Type / Day</th>
                                    @foreach(range(0, 6) as $day)
                                    <th>{{ ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$day] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['slaHeatmap'] as $testType => $dayData)
                                <tr>
                                    <td><strong>{{ $testType }}</strong></td>
                                    @foreach($dayData as $compliance)
                                    <td>
                                        <div class="p-2 rounded" style="background-color: {{ $this->getComplianceColor($compliance) }}; color: white; font-weight: bold;">
                                            {{ $compliance }}%
                                        </div>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <p class="text-muted"><small>Color Legend: 
                            <span style="background-color: #28a745; color: white;" class="px-2">Green (≥95%)</span>
                            <span style="background-color: #ffc107; color: white;" class="px-2">Yellow (80-94%)</span>
                            <span style="background-color: #dc3545; color: white;" class="px-2">Red (&lt;80%)</span>
                        </small></p>
                    </div>
                    @else
                    <div class="alert alert-info">No SLA heatmap data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Testing Load by Hour -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📈 Testing Load Distribution (Tests per Hour)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['hourlyLoad']) && count($metrics['hourlyLoad']) > 0)
                    <canvas id="hourlyLoadChart" height="80"></canvas>
                    @else
                    <div class="alert alert-info">No hourly load data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    @if(isset($metrics['hourlyLoad']) && count($metrics['hourlyLoad']) > 0)
    const ctx = document.getElementById('hourlyLoadChart').getContext('2d');
    const hourlyLoadChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($metrics['hourlyLoad'], 'hour')) !!},
            datasets: [{
                label: 'Tests Received',
                data: {!! json_encode(array_column($metrics['hourlyLoad'], 'received')) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
            }, {
                label: 'Tests Completed',
                data: {!! json_encode(array_column($metrics['hourlyLoad'], 'completed')) !!},
                backgroundColor: 'rgba(153, 102, 255, 0.7)',
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

    document.getElementById('heatmapFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const params = new URLSearchParams(new FormData(this));
        window.location.href = window.location.pathname + '?' + params.toString();
    });
</script>
@endpush

@endsection
