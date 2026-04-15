@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Equipment Reliability Board</h1>
                <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Total Equipment</div>
                    <h3 class="h2 mb-0">{{ $serviceMetrics['totalEquipment'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">Overdue</div>
                    <h3 class="h2 mb-0">{{ $serviceMetrics['overdueEquipment'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-uppercase mb-1">In Service</div>
                    <h3 class="h2 mb-0">{{ $serviceMetrics['inServiceCount'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">Avg Maintenance Cycle</div>
                    <h3 class="h2 mb-0">{{ round($serviceMetrics['avgMaintenanceFrequency'] ?? 0) }} days</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Assets at Risk -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Assets at Risk (Due for Calibration)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted">Next 7 Days</h6>
                                <h3 class="h2 text-warning mb-0">{{ $assetsAtRisk['7_days'] ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted">Next 14 Days</h6>
                                <h3 class="h2 text-warning mb-0">{{ $assetsAtRisk['14_days'] ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted">Next 30 Days</h6>
                                <h3 class="h2 text-warning mb-0">{{ $assetsAtRisk['30_days'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Calibrations & Maintenance -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <span class="badge badge-danger">{{ count($overduceCalibrationsMaintenances) }}</span>
                        Overdue Calibrations & Maintenance
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($overduceCalibrationsMaintenances) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Equipment</th>
                                    <th class="text-center">Last Calibration</th>
                                    <th class="text-center">Calibration Overdue (Days)</th>
                                    <th class="text-center">Last Maintenance</th>
                                    <th class="text-center">Maintenance Overdue (Days)</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overduceCalibrationsMaintenances as $item)
                                <tr class="@if($item['priority'] == 'critical') table-danger @elseif($item['priority'] == 'high') table-warning @endif">
                                    <td><strong>{{ $item['equipment'] }}</strong></td>
                                    <td class="text-center">{{ $item['lastCalibration'] }}</td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $item['calibrationOverdueDays'] }}</span></td>
                                    <td class="text-center">{{ $item['lastMaintenance'] }}</td>
                                    <td class="text-center"><span class="badge badge-{{ $item['maintenanceOverdueDays'] > 7 ? 'danger' : 'warning' }}">{{ $item['maintenanceOverdueDays'] }}</span></td>
                                    <td><span class="badge badge-{{ $item['priority'] == 'critical' ? 'danger' : 'warning' }}">{{ ucfirst($item['priority']) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> All equipment is up to date with calibration and maintenance!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Pass/Fail Trend -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Verification Pass/Fail Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @if(count($verificationTrends) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Passed</th>
                                    <th class="text-center">Failed</th>
                                    <th class="text-center">Pass Rate (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($verificationTrends as $trend)
                                <tr>
                                    <td><strong>{{ $trend['date'] }}</strong></td>
                                    <td class="text-center"><span class="badge badge-success">{{ $trend['passed'] }}</span></td>
                                    <td class="text-center"><span class="badge badge-danger">{{ $trend['failed'] }}</span></td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $trend['passRate'] }}%">{{ $trend['passRate'] }}%</div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No verification data available.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Reliability Scores -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Equipment Reliability Scores</h5>
                </div>
                <div class="card-body">
                    @if(count($equipmentReliability) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Equipment</th>
                                    <th>Status</th>
                                    <th class="text-center">Reliability Score</th>
                                    <th class="text-center">Verification Status</th>
                                    <th>Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipmentReliability as $equipment)
                                <tr class="@if($equipment['risk'] == 'high') table-danger @elseif($equipment['risk'] == 'medium') table-warning @endif">
                                    <td><strong>{{ $equipment['equipment'] }}</strong></td>
                                    <td>{{ $equipment['status'] }}</td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $equipment['risk'] == 'low' ? 'success' : ($equipment['risk'] == 'medium' ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ $equipment['reliabilityScore'] }}%">
                                                {{ round($equipment['reliabilityScore'], 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $equipment['verificationStatus'] == 'PASSED' ? 'success' : 'danger' }}">
                                            {{ $equipment['verificationStatus'] }}
                                        </span>
                                    </td>
                                    <td><span class="badge badge-{{ $equipment['risk'] == 'low' ? 'success' : ($equipment['risk'] == 'medium' ? 'warning' : 'danger') }}">{{ ucfirst($equipment['risk']) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No reliability data available.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Frequency Trend -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Maintenance Frequency Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @if(count($maintenanceTrends) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Equipment Count</th>
                                    <th class="text-center">Maintenance Events</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maintenanceTrends as $trend)
                                <tr>
                                    <td><strong>{{ $trend['date'] }}</strong></td>
                                    <td class="text-center"><span class="badge badge-info">{{ $trend['equipmentCount'] }}</span></td>
                                    <td class="text-center"><span class="badge badge-primary">{{ $trend['maintenanceEvents'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No maintenance trend data available.
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

    .border-left-success {
        border-left: 4px solid #28a745;
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
