<div>
    <div class="lab-dashboard-subtitle mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="text-muted mb-0">Personnel overview for staffing, licensing utilization, and organization readiness.</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0 text-primary" style="font-size: 1rem; font-weight: 500;">
                    <i class="mdi mdi-calendar"></i>
                    {{ now()->format('l, F j, Y') }}
                </p>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="tab-content p-3">
            <div class="tab-pane show active">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="kpi-card" style="border-left: 4px solid #0d6efd;">
                            <div class="kpi-card-body">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ $totalPersonnel }}</h4>
                                    <div class="kpi-card-icon"><i class="mdi mdi-account-group" style="color: #0d6efd;"></i></div>
                                </div>
                                <div class="kpi-card-row"><p class="kpi-card-label">Total Personnel</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="border-left: 4px solid #28a745;">
                            <div class="kpi-card-body">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ $activePersonnel }}</h4>
                                    <div class="kpi-card-icon"><i class="mdi mdi-account-check" style="color: #28a745;"></i></div>
                                </div>
                                <div class="kpi-card-row"><p class="kpi-card-label">Active Personnel</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="border-left: 4px solid #dc3545;">
                            <div class="kpi-card-body">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ $inactivePersonnel }}</h4>
                                    <div class="kpi-card-icon"><i class="mdi mdi-account-off" style="color: #dc3545;"></i></div>
                                </div>
                                <div class="kpi-card-row"><p class="kpi-card-label">Inactive Personnel</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="border-left: 4px solid #17a2b8;">
                            <div class="kpi-card-body">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ $newThisMonth }}</h4>
                                    <div class="kpi-card-icon"><i class="mdi mdi-account-plus" style="color: #17a2b8;"></i></div>
                                </div>
                                <div class="kpi-card-row"><p class="kpi-card-label">New This Month</p></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-key-variant"></i> License Utilization</h5>
                            @foreach ($licenseUsage as $license)
                                @php
                                    $limit = max($license['limit'], 1);
                                    $percentage = min((int) round(($license['used'] / $limit) * 100), 100);
                                @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">{{ $license['label'] }}</span>
                                        <span class="font-weight-bold">{{ $license['used'] }}/{{ $license['limit'] }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-lightning-bolt"></i> Quick Actions</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn w-100 mb-2" data-toggle="modal" data-target="#add-personnel">
                                <i class="mdi mdi-plus"></i> Add Personnel
                            </button>
                            <a href="{{ route('organizational-roles') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100 mb-2">
                                <i class="mdi mdi-account-key"></i> Manage Roles
                            </a>
                            <a href="{{ route('show-organizational-departments') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100 mb-2">
                                <i class="mdi mdi-home-group"></i> Departments
                            </a>
                            <a href="{{ route('personnel-certification-home') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                <i class="mdi mdi-certificate"></i> Certifications
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-office-building"></i> Active by Department</h5>
                            @forelse ($departmentDistribution as $department)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>{{ $department['name'] }}</span>
                                    <span class="badge badge-light">{{ $department['count'] }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No department records available.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-badge-account-horizontal"></i> Active by Designation</h5>
                            @forelse ($designationDistribution as $designation)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>{{ $designation['name'] }}</span>
                                    <span class="badge badge-light">{{ $designation['count'] }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No designation records available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-chart-line"></i> Hiring Trend (Last 6 Months)</h5>
                            @foreach ($hireTrend as $trend)
                                @php
                                    $maxTrend = max(array_column($hireTrend, 'count')) ?: 1;
                                    $width = (int) round(($trend['count'] / $maxTrend) * 100);
                                @endphp
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">{{ $trend['month'] }}</span>
                                        <span>{{ $trend['count'] }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $width }}%;" aria-valuenow="{{ $width }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-account-clock"></i> Recent Joiners</h5>
                            @forelse ($recentJoiners as $joiner)
                                <div class="border rounded p-2 mb-2">
                                    <a href="{{ route('view-personnel', ['id' => $joiner['id']]) }}" class="font-weight-bold">{{ $joiner['name'] }}</a>
                                    <div class="small text-muted">{{ $joiner['department'] }}</div>
                                    <div class="small">{{ $joiner['date'] }}</div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No recent personnel records found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .kpi-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .kpi-card-body { padding: 0.75rem; }
        .kpi-card-row { display: flex; justify-content: space-between; align-items: center; }
        .kpi-card-value { font-size: 2rem; font-weight: 700; margin-bottom: 0; color: #2d3748; line-height: 1; }
        .kpi-card-label { font-size: 1rem; font-weight: 500; color: #6b7280; margin-bottom: 0; }
        .kpi-card-icon { font-size: 2rem; }
        .chart-container { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); }
        .quick-action-btn { display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; border-radius: 8px; }
        .quick-action-btn:hover { color: white !important; background-color: #0d6efd; border-color: #0d6efd; }
    </style>

    @livewire('personnel.personnel-table-manager')
</div>
