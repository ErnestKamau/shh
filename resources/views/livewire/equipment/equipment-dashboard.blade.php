<div>
    <!-- CSS specifically for the Bento-Box Modern Equipment Dashboard -->
    <style type="text/css">
        :root {
            --primary-glass: #ffffff;
            --accent-blue: #0ea5e9;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-orange: #f59e0b;
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
        .pipeline-card { padding: 24px; text-align: center; position: relative; cursor: pointer; text-decoration: none; color: inherit; display: block;}
        .pipeline-card:hover { text-decoration: none; color: inherit; }
        
        .stat-value { font-size: 2.5rem; font-weight: 700; line-height: 1; margin: 10px 0; color: #1e293b; }
        .stat-label { font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .stat-subtext { font-size: 0.75rem; color: #94a3b8; margin-top: 5px; }

        .pulse-dot {
            height: 10px; width: 10px; border-radius: 50%; display: inline-block;
            animation: pulse 2.5s infinite;
        }
        .pulse-red { background-color: var(--accent-red); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
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
        .client-action-btn:hover {
            background-color: var(--accent-blue);
            color: #fff;
            text-decoration: none;
        }
        .client-action-btn i { font-size: 18px; margin-right: 12px; }

        .nav-tabs.modern-tabs { border-bottom: 2px solid #e2e8f0; }
        .nav-tabs.modern-tabs .nav-link { border: none; color: #64748b; font-weight: 600; padding: 12px 24px; position: relative; background: transparent; cursor: pointer; }
        .nav-tabs.modern-tabs .nav-link.active { color: var(--accent-blue); background: transparent; }
        .nav-tabs.modern-tabs .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: var(--accent-blue); }

        .smart-table th { background: #f8fafc; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; border-top: none; }
        .smart-table td { vertical-align: middle; font-weight: 500; color: #334155; border-color: #f1f5f9; }
    </style>

    <div x-data x-init="$nextTick(() => setTimeout(() => initializeEquipmentDashboardCharts(), 200))"></div>

    <div class="p-4">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1" style="font-weight: 700; color: #1e293b;">Equipment Center 🚀</h3>
                <p class="text-muted mb-0">{{ now()->format('l, F j, Y') }} &mdash; Compliance & Equipment Health Overview.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                @if(($overdueCalibrationCount + $overdueMaintainanceCount) > 0)
                <div class="bento-card px-3 py-2 mr-2 mb-0 d-flex align-items-center">
                    <span class="pulse-dot pulse-red mr-2" style="--box-color: 239, 68, 68;"></span>
                    <span class="font-weight-bold text-danger text-sm" style="font-size: 0.85rem;">{{ $overdueCalibrationCount + $overdueMaintainanceCount }} Overdue Assets</span>
                </div>
                @else
                <div class="bento-card px-3 py-2 mr-2 mb-0 d-flex align-items-center">
                    <span class="pulse-dot pulse-green mr-2" style="--box-color: 16, 185, 129;"></span>
                    <span class="font-weight-bold text-success text-sm" style="font-size: 0.85rem;">Zero Overdue Assets</span>
                </div>
                @endif
                
                @if(($dueCalibrationCount + $dueMaintainanceCount) > 0)
                <div class="bento-card px-3 py-2 mb-0 d-flex align-items-center">
                    <span class="pulse-dot pulse-orange mr-2" style="--box-color: 245, 158, 11;"></span>
                    <span class="font-weight-bold text-warning text-sm" style="font-size: 0.85rem;">{{ $dueCalibrationCount + $dueMaintainanceCount }} Due for Service</span>
                </div>
                @endif
            </div>
        </div>

        <!-- HERO PIPELINE (Top Row) -->
        <div class="row mb-4">
            <!-- Active Assets -->
            <div class="col-md-3">
                <a href="{{ route('equipment-home') }}" class="bento-card pipeline-card h-100">
                    <div class="stat-label text-primary"><i class="fas fa-desktop mr-1"></i> Total Active Assets</div>
                    <div class="stat-value">{{ number_format($activeCount) }} <span style="font-size: 1.25rem; color: #94a3b8; font-weight: 500;">/ {{ number_format($totalEquipmentCount) }}</span></div>
                    <div class="stat-subtext">Operating Equipment / Total Registered</div>
                </a>
            </div>
            <!-- In Maintenance / Due Repair -->
            <div class="col-md-3">
                <a href="{{ route('equipment-report-generate') }}" class="bento-card pipeline-card h-100">
                    <div class="stat-label text-warning"><i class="fas fa-wrench mr-1"></i> Due For Service</div>
                    <div class="stat-value">{{ $dueMaintainanceCount + $overdueMaintainanceCount }}</div>
                    <div class="stat-subtext">Maintenance Required / Overdue</div>
                </a>
            </div>
            <!-- Due Calibration -->
            <div class="col-md-3">
                <a href="{{ route('equipment-report-generate') }}" class="bento-card pipeline-card h-100">
                    <div class="stat-label text-danger"><i class="fas fa-balance-scale mr-1"></i> Calibration Attention</div>
                    <div class="stat-value">{{ $dueCalibrationCount + $overdueCalibrationCount }}</div>
                    <div class="stat-subtext">Calibrations Due / Overdue</div>
                </a>
            </div>
            <!-- Decommissioning -->
            <div class="col-md-3">
                <a href="{{ route('equipment-home') }}" class="bento-card pipeline-card h-100">
                    <div class="stat-label text-secondary"><i class="fas fa-trash-alt mr-1"></i> Disposed / Decommissioned</div>
                    <div class="stat-value">{{ number_format($disposedCount) }}</div>
                    <div class="stat-subtext">Items removed from workflow</div>
                </a>
            </div>
        </div>

        <!-- Analytics and Actions (Row 2) -->
        <div class="row mb-4">
            <!-- Analytics Area -->
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="row">
                    <div class="col-md-6 pr-lg-2">
                        <div class="bento-card h-100 mb-0">
                            <div class="section-header">
                                <span><i class="mdi mdi-chart-pie text-muted mr-2"></i> Status Distribution</span>
                            </div>
                            <div class="section-body p-4" style="height: 250px;">
                                <canvas id="equipmentStatusDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 pl-lg-2">
                        <div class="bento-card h-100 mb-0">
                            <div class="section-header">
                                <span><i class="mdi mdi-chart-bar text-muted mr-2"></i> Maintenance Health</span>
                            </div>
                            <div class="section-body p-4" style="height: 250px;">
                                <canvas id="equipmentHealthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="bento-card h-100 mb-0">
                    <div class="section-header">
                        <span><i class="fas fa-bolt text-warning mr-2"></i> Quick Actions</span>
                    </div>
                    <div class="section-body">
                        <button type="button" wire:click="$dispatchTo('equipment.equipment-manager', 'equipment-open-create-modal')" class="client-action-btn">
                            <i class="fas fa-plus-circle text-primary"></i> Add New Equipment
                        </button>
                        <button type="button" wire:click="$dispatchTo('equipment.equipment-manager', 'equipment-open-bulk-upload-modal')" class="client-action-btn">
                            <i class="fas fa-file-upload text-success"></i> Batch Import Assets
                        </button>
                        <a href="{{ route('equipment-report-generate') }}" class="client-action-btn">
                            <i class="fas fa-chart-line text-warning"></i> View Reports Hub
                        </a>
                        <a href="{{ route('equipment.asset-types.index') }}" class="client-action-btn mb-0">
                            <i class="fas fa-tags text-info"></i> Manage Asset Types
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Smart Grid Tabs (Bottom Row) -->
        <div class="row">
            <div class="col-12">
                <div class="bento-card mb-0">
                    <div class="d-flex justify-content-between align-items-center border-bottom px-4 pt-3 pb-0">
                        <h5 class="font-weight-bold mb-0" style="color:#1e293b; font-size: 1.1rem;">Critical Action Required</h5>
                        <ul class="nav nav-tabs modern-tabs" id="actionGridTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active text-danger" id="tab-critical" data-toggle="tab" href="#pane-critical" role="tab">⚠️ Immediate Priorities</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-purchases" data-toggle="tab" href="#pane-purchases" role="tab">📈 Purchase Trends</a>
                            </li>
                        </ul>
                    </div>
                    <div class="section-body p-0">
                        <div class="tab-content">
                            <!-- Critical Grid -->
                            <div class="tab-pane fade show active p-0" id="pane-critical" role="tabpanel">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table smart-table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pl-4">Reference</th>
                                                <th>Asset Name</th>
                                                <th>Calibration Status</th>
                                                <th>Maintenance Status</th>
                                                <th class="text-right pr-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($criticalEquipment as $item)
                                            <tr style="cursor: pointer" onclick="window.location.href='@php echo route('view-equipment', ['equipmentId' => $item['id']]); @endphp'">
                                                <td class="pl-4 font-weight-bold text-dark">{{ $item['equipment_number'] }}</td>
                                                <td>{{ $item['name'] }}</td>
                                                <td>
                                                    @if($item['calibration_days_left'] < 0)
                                                        <span class="badge badge-danger">Overdue ({{ abs($item['calibration_days_left']) }}d)</span>
                                                    @elseif($item['calibration_days_left'] == 0)
                                                        <span class="badge badge-warning">Due Today</span>
                                                    @else
                                                        <span class="badge badge-warning">Due in {{ $item['calibration_days_left'] }}d</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['maintainance_days_left'] < 0)
                                                        <span class="badge badge-danger">Overdue ({{ abs($item['maintainance_days_left']) }}d)</span>
                                                    @elseif($item['maintainance_days_left'] == 0)
                                                        <span class="badge badge-warning">Due Today</span>
                                                    @else
                                                        <span class="badge badge-warning">Due in {{ $item['maintainance_days_left'] }}d</span>
                                                    @endif
                                                </td>
                                                <td class="text-right pr-4">
                                                    <button class="btn btn-sm btn-outline-primary rounded px-3">Review</button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">All equipment parameters are within healthy thresholds! <i class="fas fa-check-circle text-success ml-1"></i></td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Purchase Trends Chart Tab -->
                            <div class="tab-pane fade p-4" id="pane-purchases" role="tabpanel">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="equipmentPurchaseTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Render invisible manager modal container -->
    <livewire:equipment.equipment-manager :embedded="true" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        let equipmentStatusChart, equipmentHealthChart, equipmentPurchaseTrendChart;

        function initializeEquipmentDashboardCharts() {
            const statusData = @this.get('statusDistribution');
            const dueData = @this.get('dueDistribution');
            const trendData = @this.get('purchaseTrend');

            const statusCanvas = document.getElementById('equipmentStatusDistributionChart');
            if (statusCanvas) {
                if (equipmentStatusChart) equipmentStatusChart.destroy();
                equipmentStatusChart = new Chart(statusCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: statusData.map(item => item.label),
                        datasets: [{ data: statusData.map(item => item.value), backgroundColor: statusData.map(item => item.color), borderWidth: 2, borderColor: '#fff' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            }

            const dueCanvas = document.getElementById('equipmentHealthChart');
            if (dueCanvas) {
                if (equipmentHealthChart) equipmentHealthChart.destroy();
                equipmentHealthChart = new Chart(dueCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: dueData.map(item => item.label),
                        datasets: [{ label: 'Count', data: dueData.map(item => item.value), backgroundColor: dueData.map(item => item.color), borderWidth: 0 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            }

            const trendCanvas = document.getElementById('equipmentPurchaseTrendChart');
            if (trendCanvas) {
                if (equipmentPurchaseTrendChart) equipmentPurchaseTrendChart.destroy();
                equipmentPurchaseTrendChart = new Chart(trendCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: trendData.map(item => item.month),
                        datasets: [{ label: 'Purchased', data: trendData.map(item => item.count), borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.12)', tension: 0.35, borderWidth: 3 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeEquipmentDashboardCharts, 150);
            
            // Re-render chart on tab shown since canvas goes zero size hidden
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if (e.target.id === 'tab-purchases') {
                    if (equipmentPurchaseTrendChart) equipmentPurchaseTrendChart.resize();
                }
            });
        });
        document.addEventListener('livewire:updated', function() {
            setTimeout(initializeEquipmentDashboardCharts, 150);
        });
        document.addEventListener('chartsDataUpdated', function() {
            setTimeout(initializeEquipmentDashboardCharts, 150);
        });
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializeEquipmentDashboardCharts, 150);
        });
    </script>
</div>
