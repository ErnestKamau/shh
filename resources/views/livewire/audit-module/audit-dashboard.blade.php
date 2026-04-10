<div>
    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    
    <!-- Dashboard Subtitle -->
    <div class="lab-dashboard-subtitle mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="text-muted mb-0">Comprehensive overview of audit operations, non-conformances, and corrective actions</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0 text-primary" style="font-size: 1rem; font-weight: 500;">
                    <i class="mdi mdi-calendar"></i> 
                    {{ now()->format('l, F j, Y') }}
                </p>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row 1 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #3498db;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['audits']['total'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-file-document-multiple" style="color: #3498db;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Total Audits</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #6c757d;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['audits']['scheduled'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-calendar-clock" style="color: #6c757d;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Scheduled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #e74c3c;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['non_conformances']['identified'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-alert-circle" style="color: #e74c3c;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">NCs - Identified</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #ffc107;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['corrective_actions']['in_progress'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-progress-check" style="color: #ffc107;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">CAPAs In Progress</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row 2 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #17a2b8;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['audits']['in_progress'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-file-document-edit" style="color: #17a2b8;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Audits - In Progress</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #9b59b6;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['non_conformances']['rca_in_progress'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-magnify" style="color: #9b59b6;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">NCs - RCA In Progress</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #3498db;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['non_conformances']['capa_assigned'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-clipboard-check" style="color: #3498db;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">NCs - CAPA Assigned</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #28a745;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['corrective_actions']['verified'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-check-circle" style="color: #28a745;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">CAPAs - Verified</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card date-filter-card" style="background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                            <div class="d-flex align-items-center">
                                <label for="startDate" class="me-2 fw-bold">From:</label>
                                <input type="date" id="startDate" wire:model.live="startDate" class="form-control form-control-sm" style="width: 140px;">
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <label for="endDate" class="me-2 fw-bold">To:</label>
                                <input type="date" id="endDate" wire:model.live="endDate" class="form-control form-control-sm" style="width: 140px;">
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <button wire:click="setDateRange('today')" class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">Today</button>
                                <button wire:click="setDateRange('yesterday')" class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">Yesterday</button>
                                <button wire:click="setDateRange('week')" class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">This Week</button>
                                <button wire:click="setDateRange('month')" class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">This Month</button>
                                <button wire:click="setDateRange('quarter')" class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">This Quarter</button>
                                <button wire:click="setDateRange('year')" class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">This Year</button>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <span class="text-primary me-3" style="font-size: 1rem; font-weight: 500;">
                                <i class="mdi mdi-calendar-range"></i> 
                                Data for period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="quick-actions-horizontal" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-lightning-bolt"></i> Quick Actions
                </h5>
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('audit.audits.create') }}" class="btn btn-sm btn-outline-primary action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-calendar-plus"></i> Schedule Audit
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('audit.nc.create') }}" class="btn btn-sm btn-outline-danger action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-alert-plus"></i> Raise NC
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('audit.corrective-actions.create') }}" class="btn btn-sm btn-outline-success action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-plus-circle"></i> Create CAPA
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('audit.reports.index') }}" class="btn btn-sm btn-outline-info action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-chart-bar"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-pie"></i> Audit Status Distribution
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="auditStatusChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-line"></i> Audit Trend - {{ $trendLabel }}
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="auditTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-bar"></i> NCs by Origin
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="ncOriginChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Items Row -->
    <div class="row mb-4">
        <!-- Overdue CAPAs -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-alert"></i> Overdue Corrective Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($overdueCAPAs) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>CAPA #</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueCAPAs as $capa)
                                <tr>
                                    <td>
                                        <a href="{{ route('audit.capa.show', $capa->id) }}">{{ $capa->capa_number }}</a>
                                    </td>
                                    <td>{{ $capa->actionOwnerUser?->name ?? 'N/A' }}</td>
                                    <td>{{ $capa->due_date?->format('M d, Y') }}</td>
                                    <td><span class="badge badge-danger">{{ abs($capa->days_to_deadline ?? 0) }} days</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        <i class="mdi mdi-check-circle text-success fa-2x"></i><br>
                        No overdue corrective actions!
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Audits -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-calendar-clock"></i> Upcoming Audits (Next 30 Days)
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($upcomingAudits) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Audit #</th>
                                    <th>Type</th>
                                    <th>Scheduled Date</th>
                                    <th>Lead Auditor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingAudits as $audit)
                                <tr>
                                    <td>
                                        <a href="{{ route('audit.audits.show', $audit->id) }}">{{ $audit->audit_number }}</a>
                                    </td>
                                    <td>{{ $audit->auditType?->name ?? 'N/A' }}</td>
                                    <td>{{ $audit->scheduled_date?->format('M d, Y') }}</td>
                                    <td>{{ $audit->leadAuditor?->name ?? 'Not assigned' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">No audits scheduled for the next 30 days.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Items Row 2 -->
    <div class="row">
        <!-- Recent NCs -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-alert-circle"></i> Recent Non-Conformances
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($recentNCs) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentNCs as $nc)
                        <a href="{{ route('audit.nc.show', $nc->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $nc->nc_number }}</h6>
                                <small>
                                    <span class="badge badge-{{ $nc->status_name === 'Identified' ? 'danger' : 'warning' }}">{{ $nc->status_name }}</span>
                                </small>
                            </div>
                            <p class="mb-1 text-muted small">{{ Str::limit($nc->description ?? $nc->title ?? 'N/A', 80) }}</p>
                            <small class="text-muted">{{ $nc->date_identified?->format('M d, Y') }} - {{ $nc->origin_name ?? 'N/A' }}</small>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center py-4">No open non-conformances.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Audits -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-file-document"></i> Recent Audits
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($recentAudits) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentAudits as $audit)
                        <a href="{{ route('audit.audits.show', $audit->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $audit->audit_number }}</h6>
                                <small>
                                    @php
                                        $statusColors = [
                                            'Closed' => 'success',
                                            'In Progress' => 'info',
                                            'Scheduled' => 'primary',
                                            'Pending Closure' => 'warning'
                                        ];
                                        $statusColor = $statusColors[$audit->status_name] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $statusColor }}">{{ $audit->status_name }}</span>
                                </small>
                            </div>
                            <p class="mb-1 text-muted small">{{ $audit->title }}</p>
                            <small class="text-muted">{{ $audit->auditType?->name }} - {{ $audit->scheduled_date?->format('M d, Y') }}</small>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center py-4">No audits recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        overflow: hidden;
        min-height: 70px;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .kpi-card-body {
        padding: 0.75rem;
    }

    .kpi-card-content {
        display: flex;
        flex-direction: column;
        height: 100%;
        justify-content: space-between;
    }

    .kpi-card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .kpi-card-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0;
        color: #2d3748;
        line-height: 1;
    }

    .kpi-card-label {
        font-size: 1rem;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 0;
    }

    .kpi-card-icon {
        font-size: 2rem;
        color: var(--widget-color, #10b981);
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .quick-action-btn:hover {
        color: white !important;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .chart-container {
        transition: transform 0.3s ease;
    }

    .chart-container:hover {
        transform: translateY(-2px);
    }

    .date-filter-card {
        transition: all 0.3s ease;
    }

    .date-filter-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .date-range-btn {
        transition: all 0.3s ease;
        border-radius: 8px;
        font-weight: 500;
    }

    .date-range-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    </style>

    @php
        // Get all active audit statuses from database (status-based workflow)
        $auditStatuses = \App\Models\AuditModule\AuditStatus::forCompany()
            ->active()
            ->where('name', '!=', 'Cancelled') // Exclude cancelled from chart
            ->ordered()
            ->get();
        
        $companyId = getUserCompany() ?? 0;
        $audits = \App\Models\AuditModule\Audit::where('company_id', $companyId)->get();
        
        // Count audits by status_name
        $statusCounts = [];
        foreach($auditStatuses as $status) {
            $count = $audits->where('status_name', $status->name)->count();
            if ($count > 0) { // Only include statuses with audits
                $statusCounts[] = [
                    'label' => $status->name,
                    'value' => $count,
                    'color' => $status->color_code ?? '#6c757d'
                ];
            }
        }
        $statusDataJson = json_encode($statusCounts ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    let auditStatusChart, auditTrendChart, ncOriginChart;

    // Expose dashboard data globally (similar pattern to other dashboards)
    window.auditDashboardData = {
        auditTrends: @json(is_array($auditTrends) ? $auditTrends : []),
        ncOriginData: @json(is_array($ncHotspotsByOrigin) ? $ncHotspotsByOrigin : [])
    };

    // Make function globally available
    window.initializeAuditCharts = function() {
        console.log('Initializing Audit Dashboard charts...');
        
        // Audit Workflow Step Distribution Chart
        const auditStatusCanvas = document.getElementById('auditStatusChart');
        if (auditStatusCanvas) {
            if (auditStatusChart) {
                auditStatusChart.destroy();
            }
            
            const auditStatusCtx = auditStatusCanvas.getContext('2d');
            const auditStatusData = {!! $statusDataJson !!};
            
            auditStatusChart = new Chart(auditStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: auditStatusData.map(item => item.label),
                    datasets: [{
                        data: auditStatusData.map(item => item.value),
                        backgroundColor: auditStatusData.map(item => item.color),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Audit Trend Chart (Dynamic based on date filter)
        const auditTrendCanvas = document.getElementById('auditTrendChart');
        if (auditTrendCanvas) {
            if (auditTrendChart) {
                auditTrendChart.destroy();
            }
            
            const auditTrendCtx = auditTrendCanvas.getContext('2d');
            const auditTrendData = (window.auditDashboardData && Array.isArray(window.auditDashboardData.auditTrends))
                ? window.auditDashboardData.auditTrends
                : [];
            
            auditTrendChart = new Chart(auditTrendCtx, {
                type: 'line',
                data: {
                    labels: auditTrendData.map(item => item.month),
                    datasets: [{
                        label: 'Created',
                        data: auditTrendData.map(item => item.created),
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true
                    }, {
                        label: 'Scheduled',
                        data: auditTrendData.map(item => item.scheduled),
                        borderColor: '#9b59b6',
                        backgroundColor: 'rgba(155, 89, 182, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true
                    }, {
                        label: 'In Progress',
                        data: auditTrendData.map(item => item.in_progress),
                        borderColor: '#f39c12',
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true
                    }, {
                        label: 'Closed',
                        data: auditTrendData.map(item => item.closed),
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // NC by Origin Chart
        const ncOriginCanvas = document.getElementById('ncOriginChart');
        if (ncOriginCanvas) {
            if (ncOriginChart) {
                ncOriginChart.destroy();
            }
            
            const ncOriginCtx = ncOriginCanvas.getContext('2d');
            const ncOriginData = (window.auditDashboardData && window.auditDashboardData.ncOriginData)
                ? window.auditDashboardData.ncOriginData
                : {};
            const origins = Object.keys(ncOriginData);
            const originCounts = Object.values(ncOriginData);
            const colors = ['#3498db', '#9b59b6', '#e74c3c', '#f39c12', '#1abc9c', '#34495e', '#e67e22', '#95a5a6'];
            
            ncOriginChart = new Chart(ncOriginCtx, {
                type: 'bar',
                data: {
                    labels: origins,
                    datasets: [{
                        label: 'NCs',
                        data: originCounts,
                        backgroundColor: colors.slice(0, origins.length),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

    };

    // Initialize charts when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeAuditCharts();
    });

    // Refresh charts when Livewire updates
    document.addEventListener('livewire:updated', function() {
        console.log('Livewire updated event fired');
        setTimeout(function() {
            initializeAuditCharts();
        }, 100);
    });

    // Listen for custom event from Livewire
    document.addEventListener('chartsDataUpdated', function() {
        console.log('Charts data updated event fired');
        setTimeout(function() {
            initializeAuditCharts();
        }, 100);
    });

    // Also listen for specific Livewire events
    document.addEventListener('livewire:navigated', function() {
        console.log('Livewire navigated event fired');
        setTimeout(function() {
            initializeAuditCharts();
        }, 100);
    });

    console.log('Audit Dashboard component loaded');
    </script>
</div>
