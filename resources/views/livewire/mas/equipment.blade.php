<div>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">Equipment Maintenance & Reliability</h1>
            <p class="text-muted small mb-0">Asset health, calibration compliance, and downtime analysis.</p>
        </div>
        <div class="col-auto d-flex align-items-center">
            <a href="{{ route('mas.export', 'equipment') }}" class="btn btn-success btn-sm mr-2">
                <i class="mdi mdi-download"></i> Download Report
            </a>
            <div class="d-flex gap-2">
                <span class="badge badge-warning p-2">
                    <i class="mdi mdi-alert-circle mr-1"></i> {{ $stats['calibrationOverdue'] }} Overdue
                </span>
                <span class="badge badge-success p-2 ml-2">
                    <i class="mdi mdi-shield-check mr-1"></i> Compliance: {{ $stats['calibrationCompliance'] }}%
                </span>
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="glass-panel text-center py-4 shadow-sm h-100">
                <h6 class="text-muted text-uppercase mb-2 small font-weight-bold">Reliability Score</h6>
                <h2 class="font-weight-bold text-primary mb-0">{{ $stats['reliabilityScore'] }}%</h2>
                <div class="progress mt-3 mx-4" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: {{ $stats['reliabilityScore'] }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="glass-panel text-center py-4 shadow-sm h-100">
                <h6 class="text-muted text-uppercase mb-2 small font-weight-bold">MTBF (Hours)</h6>
                <h2 class="font-weight-bold text-info mb-0">{{ $stats['mtbf'] }}</h2>
                <p class="text-muted small mb-0 mt-2">Avg. between failures</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="glass-panel text-center py-4 shadow-sm h-100">
                <h6 class="text-muted text-uppercase mb-2 small font-weight-bold">Active Assets</h6>
                <h2 class="font-weight-bold text-success mb-0">{{ $stats['equipmentTotal'] }}</h2>
                <p class="text-muted small mb-0 mt-2">Currently operational</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="glass-panel text-center py-4 shadow-sm h-100">
                <h6 class="text-muted text-uppercase mb-2 small font-weight-bold">Maintenance Due</h6>
                <h2 class="font-weight-bold text-orange mb-0">{{ $stats['maintenanceDue'] }}</h2>
                <p class="text-muted small mb-0 mt-2">Next 7 days</p>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <!-- Maintenance Schedule -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="m-0 font-weight-bold"><i class="mdi mdi-calendar-clock mr-2"></i>Upcoming Maintenance Schedule</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Equipment</th>
                                <th>Type</th>
                                <th>Scheduled Date</th>
                                <th>Urgency</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['maintenanceSchedule'] as $item)
                                <tr>
                                    <td class="font-weight-bold">{{ $item['equipmentName'] }}</td>
                                    <td><span class="badge badge-outline-secondary">{{ $item['type'] }}</span></td>
                                    <td>{{ $item['maintenanceDate'] }}</td>
                                    <td>
                                        @if($item['daysFromNow'] <= 3)
                                            <span class="text-danger font-weight-bold">Urgent ({{ $item['daysFromNow'] }}d)</span>
                                        @else
                                            <span class="text-info">{{ $item['daysFromNow'] }} days</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-info">Scheduled</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Overdue Alerts -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="m-0 font-weight-bold"><i class="mdi mdi-alert-decagram mr-2"></i>Critical Overdue Calibration</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($stats['overdueEquipment'] as $eq)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-dark">{{ $eq['name'] }}</span>
                                    <span class="badge badge-danger p-1">{{ $eq['priority'] }}</span>
                                </div>
                                <div class="small text-muted mb-2">ID: {{ $eq['id'] }} | {{ $eq['type'] }}</div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-danger">Due: {{ $eq['calibrationDue'] }}</span>
                                    <span class="font-weight-bold">{{ $eq['daysOverdue'] }} days late</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</div>