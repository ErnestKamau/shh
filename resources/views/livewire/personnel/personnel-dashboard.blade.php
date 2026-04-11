<div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <style>
        :root {
            --primary-glass: #ffffff;
            --accent-blue: #0ea5e9;
            --accent-green: #10b981;
            --accent-red: #dc3545;
            --accent-orange: #f59e0b;
            --accent-purple: #6f42c1;
            --bg-color: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --transition-speed: 0.2s;
        }

        .bento-card {
            background: var(--primary-glass);
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .bento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }

        .pipeline-card { 
            padding: 24px; 
            text-align: center; 
            position: relative; 
            cursor: pointer; 
            text-decoration: none !important; 
            display: block; 
            color: inherit; 
        }

        .pipeline-card:hover { text-decoration: none; color: inherit; }

        .stat-value { font-size: 2.5rem; font-weight: 700; line-height: 1; margin: 10px 0; color: #1e293b; }
        .stat-label { font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .stat-subtext { font-size: 0.75rem; color: #94a3b8; margin-top: 5px; }

        .pulse-dot {
            height: 10px; width: 10px; border-radius: 50%; display: inline-block;
            animation: pulse 2.5s infinite;
        }
        .pulse-red { background-color: var(--accent-red); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        .pulse-orange { background-color: var(--accent-orange); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        .pulse-green { background-color: var(--accent-green); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(var(--box-color), 0.5); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(var(--box-color), 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(var(--box-color), 0); }
        }

        .section-header { padding: 16px 20px; border-bottom: 1px solid #f8fafc; font-weight: 600; color: #334155; display: flex; align-items: center; justify-content: space-between;}
        .section-body { padding: 20px; }

        .client-action-btn {
            background-color: #f1f5f9;
            color: #334155;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 10px;
            border: none;
            width: 100%;
            text-align: left;
        }
        .client-action-btn:hover { background-color: var(--accent-blue); color: #fff; text-decoration: none; }
        .client-action-btn i { font-size: 18px; margin-right: 12px; }

        .nav-tabs.modern-tabs { border-bottom: 2px solid #e2e8f0; }
        .nav-tabs.modern-tabs .nav-link { border: none; color: #64748b; font-weight: 600; padding: 12px 24px; position: relative; background: transparent; cursor: pointer; }
        .nav-tabs.modern-tabs .nav-link.active { color: var(--accent-blue); background: transparent; }
        .nav-tabs.modern-tabs .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: var(--accent-blue); }

        .smart-table th { background: #f8fafc; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; border-top: none; }
        .smart-table td { vertical-align: middle; font-weight: 500; color: #334155; border-color: #f1f5f9; }
    </style>

    <div class="p-4">
    <!-- HEADER OVERVIEW -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1" style="font-weight: 700; color: #1e293b;">HR Command Center 👥</h3>
            <p class="text-muted mb-0">Overview of Personnel, Credentials, Roles & Compliance.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($recentAuditCount > 0)
                <div class="bento-card px-3 py-2 mr-2 mb-0 d-flex align-items-center">
                    <span class="pulse-dot pulse-orange mr-2" style="--box-color: 245, 158, 11;"></span>
                    <span class="font-weight-bold text-warning text-sm" style="font-size: 0.85rem;">{{ $recentAuditCount }} Recent User Audits</span>
                </div>
            @endif
            <div class="bento-card px-3 py-2 mb-0 d-flex align-items-center">
                <span class="pulse-dot pulse-green mr-2" style="--box-color: 16, 185, 129;"></span>
                <span class="font-weight-bold text-success text-sm" style="font-size: 0.85rem;">Compliance Healthy</span>
            </div>
        </div>
    </div>

    <!-- MAIN KPI HERO PIPELINE -->
    <div class="row mb-4">
        <!-- Active Personnel -->
        <div class="col-md-3">
            <a href="{{ route('personnel-list') }}" class="bento-card pipeline-card h-100">
                <div class="stat-label text-success"><i class="fas fa-users mr-1"></i> Active Personnel</div>
                <div class="stat-value">{{ number_format($activePersonnel) }} <span style="font-size: 1.25rem; color: #94a3b8; font-weight: 500;">/ {{ number_format($totalPersonnel) }}</span></div>
                <div class="stat-subtext">Total Employees Working</div>
            </a>
        </div>
        <!-- Departments -->
        <div class="col-md-3">
            <a href="{{ route('show-organizational-departments') }}" class="bento-card pipeline-card h-100">
                <div class="stat-label" style="color: var(--accent-purple);"><i class="fas fa-building mr-1"></i> Org Departments</div>
                <div class="stat-value">{{ number_format($totalDepartments) }}</div>
                <div class="stat-subtext">Operating Business Units</div>
            </a>
        </div>
        <!-- Active Roles -->
        <div class="col-md-3">
            <a href="{{ route('organizational-roles') }}" class="bento-card pipeline-card h-100">
                <div class="stat-label text-primary"><i class="fas fa-user-shield mr-1"></i> System Roles</div>
                <div class="stat-value">{{ number_format($activeRolesCount) }}</div>
                <div class="stat-subtext">Active Permissions Cast</div>
            </a>
        </div>
        <!-- License Utilization -->
        <div class="col-md-3">
            <!-- Simplified License utilization for Hero -->
            @php 
                $primaryLicense = count($licenseUsage) > 0 ? $licenseUsage[0] : null; 
            @endphp
            <div class="bento-card pipeline-card h-100">
                <div class="stat-label text-warning"><i class="fas fa-key mr-1"></i> Account Licenses</div>
                @if($primaryLicense)
                    <div class="stat-value">{{ $primaryLicense['used'] }} <span style="font-size: 1.25rem; color: #94a3b8; font-weight: 500;">/ {{ $primaryLicense['limit'] }}</span></div>
                    <div class="stat-subtext">Named User Slots</div>
                @else
                    <div class="stat-value">0</div>
                    <div class="stat-subtext">-</div>
                @endif
            </div>
        </div>
    </div>

    <!-- DATA DISTRIBUTION ANALYTICS -->
    <div class="row mb-4">
        <!-- Analytics & Distribution list column -->
        <div class="col-lg-8 mb-3 mb-lg-0">
            <div class="row">
                <!-- Departments Distribution -->
                <div class="col-md-6 pr-lg-2">
                    <div class="bento-card h-100 mb-0">
                        <div class="section-header">
                            <span><i class="mdi mdi-office-building text-muted mr-2"></i> Employee By Department</span>
                        </div>
                        <div class="section-body pb-2 pt-3">
                            @forelse ($departmentDistribution as $department)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span class="font-weight-500">{{ $department['name'] }}</span>
                                    <span class="badge badge-light" style="font-size:13px">{{ $department['count'] }}</span>
                                </div>
                            @empty
                                <div class="text-center text-muted p-4"><i class="fas fa-ghost"></i> No mapping found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Roles Distribution -->
                <div class="col-md-6 pl-lg-2">
                    <div class="bento-card h-100 mb-0">
                        <div class="section-header">
                            <span><i class="mdi mdi-shield-account text-muted mr-2"></i> Active Access Roles</span>
                        </div>
                        <div class="section-body pb-2 pt-3">
                            @forelse ($rolesDistribution as $role)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span class="font-weight-500">{{ $role['name'] }}</span>
                                    <span class="badge badge-success text-white" style="font-size:12px">ACTIVE</span>
                                </div>
                            @empty
                                <div class="text-center text-muted p-4"><i class="fas fa-ghost"></i> No roles found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS FRAME -->
        <div class="col-lg-4">
            <div class="bento-card h-100 mb-0">
                <div class="section-header">
                    <span><i class="fas fa-bolt text-warning mr-2"></i> HR Actions</span>
                </div>
                <div class="section-body">
                    <button type="button" wire:click="$dispatchTo('personnel.personnel-table-manager', 'personnel-open-add-modal')" class="client-action-btn">
                        <i class="fas fa-user-plus text-primary"></i> Onboard New Hire
                    </button>
                    <a href="{{ route('show-organizational-departments') }}" class="client-action-btn">
                        <i class="fas fa-sitemap text-info"></i> Manage Departments
                    </a>
                    <a href="{{ route('organizational-roles') }}" class="client-action-btn">
                        <i class="fas fa-user-shield text-danger"></i> Configure Roles & Security
                    </a>
                    <a href="{{ route('personnel-certification-home') }}" class="client-action-btn">
                        <i class="fas fa-award text-success"></i> Training & Certifications
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SMART ACTION GRID (TABS: Activity & Hiring) -->
    <div class="row">
        <div class="col-12">
            <div class="bento-card mb-0">
                <div class="d-flex justify-content-between align-items-center border-bottom px-4 pt-3 pb-0">
                    <h5 class="font-weight-bold mb-0" style="color:#1e293b; font-size: 1.1rem;">Operational Intelligence</h5>
                    <ul class="nav nav-tabs modern-tabs" id="actionGridTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-audits" data-toggle="tab" href="#pane-audits" role="tab">🔍 System Audits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-hires" data-toggle="tab" href="#pane-hires" role="tab">👤 Recent Joiners</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-hirecurve" data-toggle="tab" href="#pane-hirecurve" role="tab">📈 Hiring Trend</a>
                        </li>
                    </ul>
                </div>
                
                <div class="section-body p-0">
                    <div class="tab-content">
                        <!-- System Audits Area -->
                        <div class="tab-pane fade show active p-0" id="pane-audits" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table smart-table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="pl-4">Audit ID</th>
                                            <th>Activity Type</th>
                                            <th>Model Target</th>
                                            <th>Triggered By</th>
                                            <th class="text-right pr-4">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentAudits as $log)
                                        <tr>
                                            <td class="pl-4 font-weight-bold text-muted">#LOG-{{ $log['id'] }}</td>
                                            <td>
                                                @if(strtolower($log['event']) == 'created') <span class="badge badge-success px-2 py-1">Created</span>
                                                @elseif(strtolower($log['event']) == 'updated') <span class="badge badge-warning px-2 py-1">Updated</span>
                                                @elseif(strtolower($log['event']) == 'deleted') <span class="badge badge-danger px-2 py-1">Deleted</span>
                                                @else <span class="badge badge-secondary px-2 py-1">{{ $log['event'] }}</span>
                                                @endif
                                            </td>
                                            <td><i class="mdi mdi-database text-info pr-1"></i> {{ $log['auditable_type'] }}</td>
                                            <td class="font-weight-500">{{ $log['user_name'] }}</td>
                                            <td class="text-right pr-4 text-muted">{{ $log['date'] }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted border-0">No active system events. Logging disabled.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Recent Joiners Area -->
                        <div class="tab-pane fade p-0" id="pane-hires" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table smart-table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="pl-4">Employee ID</th>
                                            <th>Name</th>
                                            <th>Assigned Department</th>
                                            <th class="text-right pr-4">Joined On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentJoiners as $joiner)
                                        <tr style="cursor: pointer" onclick="window.location.href='@php echo route('view-personnel', ['id' => $joiner['id']]); @endphp'">
                                            <td class="pl-4 font-weight-bold text-dark">LIMS-00{{ $joiner['id'] }}</td>
                                            <td class="font-weight-500 text-primary">{{ $joiner['name'] }}</td>
                                            <td>{{ $joiner['department'] }}</td>
                                            <td class="text-right pr-4 text-muted">{{ $joiner['date'] }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted border-0">No recent personnel joined.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Hire Trend Chart Area -->
                        <div class="tab-pane fade p-4" id="pane-hirecurve" role="tabpanel">
                            <div class="chart-container border-0 shadow-none" style="height: 350px;">
                                <canvas id="hiringTrendChart"></canvas>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modal that comes up from "Add Personnel" action -->
    <div id="personnel-list" class="mt-4">
        @livewire('personnel.personnel-table-manager', ['embedded' => true])
    </div>
    </div>

    <script>
        var hiringTrendChart = null;

        function initializePersonnelCharts() {
            var hireTrendData = @json($hireTrend);
            
            var ctx = document.getElementById('hiringTrendChart');
            if (ctx) {
                if (hiringTrendChart) {
                    hiringTrendChart.destroy();
                }
                
                hiringTrendChart = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: hireTrendData.map(item => item.month),
                        datasets: [{
                            label: 'New Personnel',
                            data: hireTrendData.map(item => item.count),
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14, 165, 233, 0.12)',
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#0ea5e9',
                            pointHoverBackgroundColor: '#0ea5e9',
                            pointHoverBorderColor: '#fff',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    stepSize: 1,
                                    padding: 10,
                                    fontColor: '#64748b'
                                },
                                gridLines: {
                                    color: '#f1f5f9',
                                    zeroLineColor: '#f1f5f9'
                                }
                            }],
                            xAxes: [{
                                ticks: {
                                    padding: 10,
                                    fontColor: '#64748b'
                                },
                                gridLines: {
                                    display: false
                                }
                            }]
                        },
                        tooltips: {
                            backgroundColor: '#1e293b',
                            titleFontColor: '#fff',
                            bodyFontColor: '#fff',
                            cornerRadius: 6,
                            padding: 12,
                            displayColors: false
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializePersonnelCharts, 200);
            
            // Handle tab switching
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if (e.target.id === 'tab-hirecurve') {
                    if (hiringTrendChart) hiringTrendChart.resize();
                }
            });
        });

        // Livewire lifecycle hooks
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializePersonnelCharts, 200);
        });

        document.addEventListener('livewire:load', function() {
            setTimeout(initializePersonnelCharts, 200);
        });

        document.addEventListener('livewire:updated', function() {
            setTimeout(initializePersonnelCharts, 200);
        });

        if (window.Livewire) {
            window.Livewire.on('refreshCharts', () => {
                setTimeout(initializePersonnelCharts, 200);
            });
        }
    </script>
</div>
