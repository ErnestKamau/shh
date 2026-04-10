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
                <p class="text-muted mb-0">Comprehensive overview of risk management operations and monitoring</p>
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
            <div class="kpi-card" style="border-left: 4px solid #dc3545;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['risks']['total'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-alert-octagon" style="color: #dc3545;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Total Risks</p>
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
                            <h4 class="kpi-card-value">{{ $stats['risks']['identified'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-alert-circle" style="color: #ffc107;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Identified</p>
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
                            <h4 class="kpi-card-value">{{ $stats['risk_levels']['critical'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-alert" style="color: #e74c3c;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Critical Risks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #17a2b8;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['risks']['monitored'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-eye" style="color: #17a2b8;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Monitored</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row 2 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #6c757d;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['risks']['assessed'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-file-document-edit" style="color: #6c757d;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Assessed</p>
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
                            <h4 class="kpi-card-value">{{ $stats['risks']['evaluated'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-magnify" style="color: #9b59b6;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Evaluated</p>
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
                            <h4 class="kpi-card-value">{{ $stats['risks']['treatment_planned'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-clipboard-check" style="color: #3498db;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Treatment Planned</p>
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
                            <h4 class="kpi-card-value">{{ $stats['risks']['closed'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-check-circle" style="color: #28a745;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Closed</p>
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
                        <a href="{{ route('risk.risks.create') }}" class="btn btn-sm btn-outline-danger action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-alert-plus"></i> Create Risk
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('risk.risks.index', ['status' => 'All Risks']) }}" class="btn btn-sm btn-outline-primary action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-view-list"></i> View All Risks
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('risk.risks.index', ['status' => 'Monitored']) }}" class="btn btn-sm btn-outline-info action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-eye"></i> Risks Requiring Review
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('risk.config.risk-statuses') }}" class="btn btn-sm btn-outline-warning action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-cogs"></i> Configuration
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
                    <i class="mdi mdi-chart-pie"></i> Risk Level Distribution
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="riskLevelChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-line"></i> Risk Trend - {{ $trendLabel }}
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="riskTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-bar"></i> Risks by Category
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="riskCategoryChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-bar"></i> Risks by Source
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="riskSourceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Items Row -->
    <div class="row mb-4">
        <!-- Critical Risks -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-alert"></i> Critical Risks
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($criticalRisks) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Risk #</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criticalRisks as $risk)
                                <tr>
                                    <td>{{ $risk->risk_number }}</td>
                                    <td>{{ Str::limit($risk->title, 30) }}</td>
                                    <td><span class="badge badge-info">{{ $risk->status_name }}</span></td>
                                    <td>
                                        <a href="{{ route('risk.risks.show', $risk->id) }}" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        <i class="mdi mdi-check-circle text-success fa-2x"></i><br>
                        No critical risks found!
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Overdue Reviews -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header bg-warning text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-clock-alert"></i> Overdue Reviews
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($overdueReviews) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Risk #</th>
                                    <th>Title</th>
                                    <th>Review Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueReviews as $risk)
                                <tr>
                                    <td>{{ $risk->risk_number }}</td>
                                    <td>{{ Str::limit($risk->title, 30) }}</td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ $risk->next_review_date ? $risk->next_review_date->format('M d, Y') : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('risk.risks.show', $risk->id) }}" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        <i class="mdi mdi-check-circle text-success fa-2x"></i><br>
                        No overdue reviews found!
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Risks -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-clock-outline"></i> Recent Risks
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($recentRisks) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentRisks as $risk)
                        <a href="{{ route('risk.risks.show', $risk->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $risk->risk_number }}</h6>
                                <small>
                                    @php
                                        $badgeClass = 'secondary';
                                        if ($risk->risk_level === 'Critical') $badgeClass = 'danger';
                                        elseif ($risk->risk_level === 'High') $badgeClass = 'warning';
                                        elseif ($risk->risk_level === 'Medium') $badgeClass = 'info';
                                        elseif ($risk->risk_level === 'Low') $badgeClass = 'success';
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">{{ $risk->risk_level ?? 'N/A' }}</span>
                                </small>
                            </div>
                            <p class="mb-1 text-muted small">{{ Str::limit($risk->title, 80) }}</p>
                            <small class="text-muted">{{ $risk->category->name ?? 'N/A' }} - {{ $risk->date_identified ? $risk->date_identified->format('M d, Y') : 'N/A' }}</small>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center py-4">No recent risks found.</p>
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
        background-color: #dc3545;
        border-color: #dc3545;
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
        // Get risk level distribution data
        $companyId = getUserCompany() ?? 0;
        $riskLevels = ['Critical', 'High', 'Medium', 'Low'];
        $levelCounts = [];
        $levelColors = [
            'Critical' => '#e74c3c',
            'High' => '#f39c12',
            'Medium' => '#3498db',
            'Low' => '#27ae60'
        ];
        
        foreach($riskLevels as $level) {
            $count = \App\Models\RiskManagement\Risk::where('company_id', $companyId)
                ->where('risk_level', $level)
                ->count();
            if ($count > 0) {
                $levelCounts[] = [
                    'label' => $level,
                    'value' => $count,
                    'color' => $levelColors[$level]
                ];
            }
        }
        $levelDataJson = json_encode($levelCounts ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    let riskLevelChart, riskTrendChart, riskCategoryChart, riskSourceChart;

    // Expose dashboard data globally
    window.riskDashboardData = {
        riskTrends: @json(is_array($riskTrends) ? $riskTrends : []),
        riskCategoryData: @json(is_array($riskHotspotsByCategory) ? $riskHotspotsByCategory : []),
        riskSourceData: @json(is_array($riskHotspotsBySource) ? $riskHotspotsBySource : [])
    };

    // Make function globally available
    window.initializeRiskCharts = function() {
        console.log('Initializing Risk Dashboard charts...');
        
        // Risk Level Distribution Chart
        const riskLevelCanvas = document.getElementById('riskLevelChart');
        if (riskLevelCanvas) {
            if (riskLevelChart) {
                riskLevelChart.destroy();
            }
            
            const riskLevelCtx = riskLevelCanvas.getContext('2d');
            const riskLevelData = {!! $levelDataJson !!};
            
            riskLevelChart = new Chart(riskLevelCtx, {
                type: 'doughnut',
                data: {
                    labels: riskLevelData.map(item => item.label),
                    datasets: [{
                        data: riskLevelData.map(item => item.value),
                        backgroundColor: riskLevelData.map(item => item.color),
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

        // Risk Trend Chart (Dynamic based on date filter)
        const riskTrendCanvas = document.getElementById('riskTrendChart');
        if (riskTrendCanvas) {
            if (riskTrendChart) {
                riskTrendChart.destroy();
            }
            
            const riskTrendCtx = riskTrendCanvas.getContext('2d');
            const riskTrendData = (window.riskDashboardData && Array.isArray(window.riskDashboardData.riskTrends))
                ? window.riskDashboardData.riskTrends
                : [];
            
            riskTrendChart = new Chart(riskTrendCtx, {
                type: 'line',
                data: {
                    labels: riskTrendData.map(item => item.month),
                    datasets: [{
                        label: 'Created',
                        data: riskTrendData.map(item => item.created),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true
                    }, {
                        label: 'Critical',
                        data: riskTrendData.map(item => item.critical),
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true
                    }, {
                        label: 'High',
                        data: riskTrendData.map(item => item.high),
                        borderColor: '#f39c12',
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true
                    }, {
                        label: 'Closed',
                        data: riskTrendData.map(item => item.closed),
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

        // Risk by Category Chart
        const riskCategoryCanvas = document.getElementById('riskCategoryChart');
        if (riskCategoryCanvas) {
            if (riskCategoryChart) {
                riskCategoryChart.destroy();
            }
            
            const riskCategoryCtx = riskCategoryCanvas.getContext('2d');
            const riskCategoryData = (window.riskDashboardData && Array.isArray(window.riskDashboardData.riskCategoryData))
                ? window.riskDashboardData.riskCategoryData
                : [];
            const categories = riskCategoryData.map(item => item.name);
            const categoryCounts = riskCategoryData.map(item => item.count);
            const colors = ['#dc3545', '#ffc107', '#17a2b8', '#6c757d', '#9b59b6', '#3498db', '#e74c3c'];
            
            riskCategoryChart = new Chart(riskCategoryCtx, {
                type: 'bar',
                data: {
                    labels: categories,
                    datasets: [{
                        label: 'Risks',
                        data: categoryCounts,
                        backgroundColor: colors.slice(0, categories.length),
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

        // Risk by Source Chart
        const riskSourceCanvas = document.getElementById('riskSourceChart');
        if (riskSourceCanvas) {
            if (riskSourceChart) {
                riskSourceChart.destroy();
            }
            
            const riskSourceCtx = riskSourceCanvas.getContext('2d');
            const riskSourceData = (window.riskDashboardData && Array.isArray(window.riskDashboardData.riskSourceData))
                ? window.riskDashboardData.riskSourceData
                : [];
            const sources = riskSourceData.map(item => item.name);
            const sourceCounts = riskSourceData.map(item => item.count);
            const sourceColors = ['#3498db', '#9b59b6', '#e74c3c', '#f39c12', '#1abc9c', '#34495e', '#e67e22', '#95a5a6'];
            
            riskSourceChart = new Chart(riskSourceCtx, {
                type: 'bar',
                data: {
                    labels: sources,
                    datasets: [{
                        label: 'Risks',
                        data: sourceCounts,
                        backgroundColor: sourceColors.slice(0, sources.length),
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
        initializeRiskCharts();
    });

    // Refresh charts when Livewire updates
    document.addEventListener('livewire:updated', function() {
        console.log('Livewire updated event fired');
        setTimeout(function() {
            initializeRiskCharts();
        }, 100);
    });

    // Listen for custom event from Livewire
    document.addEventListener('chartsDataUpdated', function() {
        console.log('Charts data updated event fired');
        setTimeout(function() {
            initializeRiskCharts();
        }, 100);
    });

    // Also listen for specific Livewire events
    document.addEventListener('livewire:navigated', function() {
        console.log('Livewire navigated event fired');
        setTimeout(function() {
            initializeRiskCharts();
        }, 100);
    });

    console.log('Risk Dashboard component loaded');
    </script>
</div>
