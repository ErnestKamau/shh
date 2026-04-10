<div>
    <!-- Header Card -->
    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
        <div class="card-header" style="background: #f8f9fa; border: none; border-left: 6px solid #17a2b8; padding: 18px 24px;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0" style="font-size: 1.35rem; font-weight: 600; color: #222;">
                    <i class="mdi mdi-chart-line"></i> Advanced Statistics & Analytics
                </h4>
                <a href="{{ route('audit.reports.index') }}" class="btn btn-sm btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
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
                                <button wire:click="setDateRange('month')" class="btn btn-sm btn-outline-info me-1">This Month</button>
                                <button wire:click="setDateRange('quarter')" class="btn btn-sm btn-outline-info me-1">This Quarter</button>
                                <button wire:click="setDateRange('year')" class="btn btn-sm btn-outline-info me-1">This Year</button>
                                <button wire:click="setDateRange('last_6_months')" class="btn btn-sm btn-outline-info me-1">Last 6 Months</button>
                                <button wire:click="setDateRange('last_12_months')" class="btn btn-sm btn-outline-info">Last 12 Months</button>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <span class="text-primary" style="font-size: 1rem; font-weight: 500;">
                                <i class="mdi mdi-calendar-range"></i> 
                                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trends Charts - Row 1 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-alert-circle text-danger"></i> Non-Conformance Trends</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="ncTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-clipboard-check text-success"></i> CAPA Trends</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="capaTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trends Charts - Row 2 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-file-document-outline text-info"></i> Audit Trends</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="auditTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-chart-line text-primary"></i> Root Cause Analysis Trend</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="rootCauseTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-body">
                    <h3 class="mb-2" style="color: #e74c3c; font-weight: 700;">{{ collect($ncTrends)->sum('count') }}</h3>
                    <p class="mb-0 text-muted">Total NCs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-body">
                    <h3 class="mb-2" style="color: #27ae60; font-weight: 700;">{{ collect($capaTrends)->sum('count') }}</h3>
                    <p class="mb-0 text-muted">Total CAPAs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-body">
                    <h3 class="mb-2" style="color: #3498db; font-weight: 700;">{{ collect($auditTrends)->sum('count') }}</h3>
                    <p class="mb-0 text-muted">Total Audits</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-body">
                    <h3 class="mb-2" style="color: #9b59b6; font-weight: 700;">{{ collect($rootCauseTrends)->sum('count') }}</h3>
                    <p class="mb-0 text-muted">Total RCAs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution Charts - Row 3 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-source-branch text-warning"></i> NCs by Origin</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="ncByOriginChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-alert-octagon text-danger"></i> NCs by Risk Level</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="ncByRiskLevelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution & Performance Charts - Row 4 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-office-building text-info"></i> NCs by Department</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="ncByDepartmentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-clock-outline text-primary"></i> Average Closure Time</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="avgClosureTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis & Methods Charts - Row 5 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-chart-bar text-info"></i> Top Root Cause Methods</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="rootCauseMethodsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-chart-line text-success"></i> CAPA Effectiveness Trends</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="capaEffectivenessChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Repeated NCs Section -->
    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="mdi mdi-alert-circle-multiple text-warning"></i> Repeated Non-Conformances</h5>
        </div>
        <div class="card-body">
            @if(count($repeatedNCs) > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Description</th>
                            <th>Occurrences</th>
                            <th>First Occurrence</th>
                            <th>Last Occurrence</th>
                            <th>NC Numbers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($repeatedNCs as $repeated)
                        <tr>
                            <td>{{ $repeated['description'] }}</td>
                            <td><span class="badge badge-warning" style="font-size: 0.9rem;">{{ $repeated['count'] }} times</span></td>
                            <td>{{ \Carbon\Carbon::parse($repeated['first_occurrence'])->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($repeated['last_occurrence'])->format('M d, Y') }}</td>
                            <td>
                                @foreach($repeated['nc_numbers'] as $ncNumber)
                                    <span class="badge badge-secondary me-1">{{ $ncNumber }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-4">
                <i class="mdi mdi-information text-muted" style="font-size: 2rem;"></i><br>
                No repeated non-conformances found
            </p>
            @endif
        </div>
    </div>

    <!-- NC Closure Trends Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-clock-outline text-info"></i> Closure Time by Origin</h5>
                </div>
                <div class="card-body">
                    @if(!empty($ncClosureTrends['by_origin']))
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Origin</th>
                                    <th>Total Closed</th>
                                    <th>Avg Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ncClosureTrends['by_origin'] as $origin => $data)
                                <tr>
                                    <td><strong>{{ $origin }}</strong></td>
                                    <td>{{ $data['total'] }}</td>
                                    <td><span class="badge badge-info">{{ $data['avg_days'] }} days</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        <i class="mdi mdi-information text-muted" style="font-size: 2rem;"></i><br>
                        No closure data available
                    </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="mdi mdi-clock-outline text-info"></i> Closure Time by Department</h5>
                </div>
                <div class="card-body">
                    @if(!empty($ncClosureTrends['by_department']))
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Total Closed</th>
                                    <th>Avg Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ncClosureTrends['by_department'] as $dept => $data)
                                <tr>
                                    <td><strong>{{ $dept }}</strong></td>
                                    <td>{{ $data['total'] }}</td>
                                    <td><span class="badge badge-info">{{ $data['avg_days'] }} days</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        <i class="mdi mdi-information text-muted" style="font-size: 2rem;"></i><br>
                        No closure data available
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let ncTrendsChart, capaTrendsChart, auditTrendsChart, rootCauseTrendsChart;
let ncByOriginChart, ncByRiskLevelChart, ncByDepartmentChart, avgClosureTimeChart;
let rootCauseMethodsChart, capaEffectivenessChart;

window.initializeStatisticsCharts = function() {
    console.log('Initializing Advanced Statistics charts...');
    
    // 1. NC Trends Chart (Line Chart)
    const ncTrendsCtx = document.getElementById('ncTrendsChart');
    if (ncTrendsCtx) {
        if (ncTrendsChart) ncTrendsChart.destroy();
        
        const ncData = @json($ncTrends ?? []);
        console.log('NC Trends Data:', ncData);
        
        if (ncData && ncData.length > 0) {
            ncTrendsChart = new Chart(ncTrendsCtx, {
                type: 'line',
                data: {
                    labels: ncData.map(item => item.period),
                    datasets: [{
                        label: 'Non-Conformances',
                        data: ncData.map(item => item.count),
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        } else {
            ncTrendsCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }

    // 2. CAPA Trends Chart (Line Chart)
    const capaTrendsCtx = document.getElementById('capaTrendsChart');
    if (capaTrendsCtx) {
        if (capaTrendsChart) capaTrendsChart.destroy();
        
        const capaData = @json($capaTrends ?? []);
        console.log('CAPA Trends Data:', capaData);
        
        if (capaData && capaData.length > 0) {
            capaTrendsChart = new Chart(capaTrendsCtx, {
                type: 'line',
                data: {
                    labels: capaData.map(item => item.period),
                    datasets: [{
                        label: 'Corrective Actions',
                        data: capaData.map(item => item.count),
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        } else {
            capaTrendsCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }

    // 3. Audit Trends Chart (Line Chart)
    const auditTrendsCtx = document.getElementById('auditTrendsChart');
    if (auditTrendsCtx) {
        if (auditTrendsChart) auditTrendsChart.destroy();
        
        const auditData = @json($auditTrends ?? []);
        console.log('Audit Trends Data:', auditData);
        
        if (auditData && auditData.length > 0) {
            auditTrendsChart = new Chart(auditTrendsCtx, {
                type: 'line',
                data: {
                    labels: auditData.map(item => item.period),
                    datasets: [{
                        label: 'Audits Conducted',
                        data: auditData.map(item => item.count),
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        } else {
            auditTrendsCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }

    // 4. Root Cause Trends Chart (Line Chart)
    const rootCauseTrendsCtx = document.getElementById('rootCauseTrendsChart');
    if (rootCauseTrendsCtx) {
        if (rootCauseTrendsChart) {
            rootCauseTrendsChart.destroy();
        }
        
        const trendsData = @json($rootCauseTrends ?? []);
        console.log('Root Cause Trends Data:', trendsData);
        
        // Handle empty data
        if (!trendsData || trendsData.length === 0) {
            console.warn('No root cause trends data available');
            rootCauseTrendsCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available for the selected period</div>';
            return;
        }
        
        rootCauseTrendsChart = new Chart(rootCauseTrendsCtx, {
            type: 'line',
            data: {
                labels: trendsData.map(item => item.period),
                datasets: [{
                    label: 'Root Cause Analyses',
                    data: trendsData.map(item => item.count),
                    borderColor: '#9b59b6',
                    backgroundColor: 'rgba(155, 89, 182, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // 5. NC by Origin Chart (Bar Chart)
    const ncByOriginCtx = document.getElementById('ncByOriginChart');
    if (ncByOriginCtx) {
        if (ncByOriginChart) ncByOriginChart.destroy();
        
        const originData = @json($ncByOrigin ?? []);
        console.log('NC by Origin Data:', originData);
        
        const originLabels = Object.keys(originData);
        const originValues = Object.values(originData);
        
        if (originLabels.length > 0) {
            ncByOriginChart = new Chart(ncByOriginCtx, {
                type: 'bar',
                data: {
                    labels: originLabels,
                    datasets: [{
                        label: 'Count',
                        data: originValues,
                        backgroundColor: ['#f39c12', '#e74c3c', '#3498db', '#9b59b6', '#1abc9c', '#34495e'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        } else {
            ncByOriginCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }

    // 6. NC by Risk Level Chart (Bar Chart)
    const ncByRiskLevelCtx = document.getElementById('ncByRiskLevelChart');
    if (ncByRiskLevelCtx) {
        if (ncByRiskLevelChart) ncByRiskLevelChart.destroy();
        
        const riskData = @json($ncByRiskLevel ?? []);
        console.log('NC by Risk Level Data:', riskData);
        
        const riskLabels = Object.keys(riskData);
        const riskValues = Object.values(riskData);
        
        if (riskLabels.length > 0) {
            const riskColors = riskLabels.map(label => {
                const lower = label.toLowerCase();
                if (lower.includes('high') || lower.includes('critical')) return '#e74c3c';
                if (lower.includes('medium') || lower.includes('moderate')) return '#f39c12';
                return '#27ae60';
            });
            
            ncByRiskLevelChart = new Chart(ncByRiskLevelCtx, {
                type: 'bar',
                data: {
                    labels: riskLabels,
                    datasets: [{
                        label: 'Count',
                        data: riskValues,
                        backgroundColor: riskColors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        } else {
            ncByRiskLevelCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }

    // 7. NC by Department Chart (Bar Chart)
    const ncByDepartmentCtx = document.getElementById('ncByDepartmentChart');
    if (ncByDepartmentCtx) {
        if (ncByDepartmentChart) ncByDepartmentChart.destroy();
        
        const deptData = @json($ncByDepartment ?? []);
        console.log('NC by Department Data:', deptData);
        
        const deptLabels = Object.keys(deptData);
        const deptValues = Object.values(deptData);
        
        if (deptLabels.length > 0) {
            ncByDepartmentChart = new Chart(ncByDepartmentCtx, {
                type: 'bar',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        label: 'Count',
                        data: deptValues,
                        backgroundColor: '#3498db',
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        } else {
            ncByDepartmentCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }

    // 8. Average Closure Time Chart (Line Chart)
    const avgClosureTimeCtx = document.getElementById('avgClosureTimeChart');
    if (avgClosureTimeCtx) {
        if (avgClosureTimeChart) avgClosureTimeChart.destroy();
        
        const closureData = @json($avgClosureTime ?? []);
        console.log('Average Closure Time Data:', closureData);
        
        if (closureData && closureData.length > 0) {
            avgClosureTimeChart = new Chart(avgClosureTimeCtx, {
                type: 'line',
                data: {
                    labels: closureData.map(item => item.period),
                    datasets: [{
                        label: 'Avg Days',
                        data: closureData.map(item => item.avg_days),
                        borderColor: '#e67e22',
                        backgroundColor: 'rgba(230, 126, 34, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 1 } }
                    }
                }
            });
        } else {
            avgClosureTimeCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }

    // 9. Top Root Cause Methods Chart (Bar Chart)
    const methodsCtx = document.getElementById('rootCauseMethodsChart');
    if (methodsCtx) {
        if (rootCauseMethodsChart) rootCauseMethodsChart.destroy();
        
        const methodsData = @json($topRootCauseMethods ?? []);
        console.log('Top Root Cause Methods Data:', methodsData);
        
        const methodsLabels = Object.keys(methodsData);
        const methodsValues = Object.values(methodsData);
        
        if (methodsLabels.length > 0) {
            rootCauseMethodsChart = new Chart(methodsCtx, {
            type: 'bar',
            data: {
                labels: methodsLabels.length > 0 ? methodsLabels : ['No Data'],
                datasets: [{
                    label: 'Usage Count',
                    data: methodsValues.length > 0 ? methodsValues : [0],
                    backgroundColor: '#3498db',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
        } else {
            methodsCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }

    // 10. CAPA Effectiveness Trends Chart (Line Chart)
    const effectivenessCtx = document.getElementById('capaEffectivenessChart');
    if (effectivenessCtx) {
        if (capaEffectivenessChart) capaEffectivenessChart.destroy();
        
        const effectivenessData = @json($capaEffectivenessTrends ?? []);
        console.log('CAPA Effectiveness Data:', effectivenessData);
        
        const effectivenessLabels = Object.keys(effectivenessData).map(month => {
            const date = new Date(month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });
        const effectivenessRates = Object.values(effectivenessData).map(data => data.effectiveness_rate);
        
        if (effectivenessLabels.length > 0) {
            capaEffectivenessChart = new Chart(effectivenessCtx, {
            type: 'line',
            data: {
                labels: effectivenessLabels.length > 0 ? effectivenessLabels : ['No Data'],
                datasets: [{
                    label: 'Effectiveness Rate (%)',
                    data: effectivenessRates.length > 0 ? effectivenessRates : [0],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
        } else {
            effectivenessCtx.parentElement.innerHTML = '<div class="alert alert-info text-center">No data available</div>';
        }
    }
    
    console.log('All charts initialized successfully!');
};

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeStatisticsCharts();
});

// Refresh charts when Livewire updates
document.addEventListener('livewire:updated', function() {
    setTimeout(function() {
        initializeStatisticsCharts();
    }, 100);
});

// Listen for custom event from Livewire
document.addEventListener('chartsDataUpdated', function() {
    setTimeout(function() {
        initializeStatisticsCharts();
    }, 100);
});
</script>
