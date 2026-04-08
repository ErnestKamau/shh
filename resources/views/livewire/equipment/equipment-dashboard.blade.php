<div>
    <div class="lab-dashboard-subtitle mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="text-muted mb-0">Vital equipment metrics: active assets, overdue maintenance/calibration, disposal status, and purchase trend.</p>
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
            <div class="tab-pane show active" id="equipment-overview" role="tabpanel" wire:key="equipment-overview-pane">
                <div x-data x-init="$nextTick(() => setTimeout(() => initializeEquipmentDashboardCharts(), 200))"></div>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <a href="{{ route('equipment-home') }}" class="kpi-link border-0 bg-transparent p-0 w-100 text-left">
                            <div class="kpi-card" style="border-left: 4px solid #0d6efd;">
                                <div class="kpi-card-body">
                                    <div class="kpi-card-row">
                                        <h4 class="kpi-card-value">{{ $totalEquipmentCount }}</h4>
                                        <div class="kpi-card-icon"><i class="mdi mdi-tools" style="color: #0d6efd;"></i></div>
                                    </div>
                                    <div class="kpi-card-row"><p class="kpi-card-label">Total Equipment</p></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('equipment-home') }}" class="kpi-link border-0 bg-transparent p-0 w-100 text-left">
                            <div class="kpi-card" style="border-left: 4px solid #28a745;">
                                <div class="kpi-card-body">
                                    <div class="kpi-card-row">
                                        <h4 class="kpi-card-value">{{ $activeCount }}</h4>
                                        <div class="kpi-card-icon"><i class="mdi mdi-check-circle" style="color: #28a745;"></i></div>
                                    </div>
                                    <div class="kpi-card-row"><p class="kpi-card-label">Active</p></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('equipment-report-generate') }}" class="kpi-link">
                            <div class="kpi-card" style="border-left: 4px solid #ffc107;">
                                <div class="kpi-card-body">
                                    <div class="kpi-card-row">
                                        <h4 class="kpi-card-value">{{ $dueCalibrationCount }}</h4>
                                        <div class="kpi-card-icon"><i class="mdi mdi-calendar-alert" style="color: #ffc107;"></i></div>
                                    </div>
                                    <div class="kpi-card-row"><p class="kpi-card-label">Calibration Attention</p></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('equipment-report-generate') }}" class="kpi-link">
                            <div class="kpi-card" style="border-left: 4px solid #fd7e14;">
                                <div class="kpi-card-body">
                                    <div class="kpi-card-row">
                                        <h4 class="kpi-card-value">{{ $dueMaintainanceCount }}</h4>
                                        <div class="kpi-card-icon"><i class="mdi mdi-wrench-clock" style="color: #fd7e14;"></i></div>
                                    </div>
                                    <div class="kpi-card-row"><p class="kpi-card-label">Maintainance Attention</p></div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <a href="{{ route('equipment-home') }}" class="kpi-link border-0 bg-transparent p-0 w-100 text-left">
                            <div class="kpi-card" style="border-left: 4px solid #6c757d;">
                                <div class="kpi-card-body">
                                    <div class="kpi-card-row">
                                        <h4 class="kpi-card-value">{{ $disposedCount }}</h4>
                                        <div class="kpi-card-icon"><i class="mdi mdi-delete" style="color: #6c757d;"></i></div>
                                    </div>
                                    <div class="kpi-card-row"><p class="kpi-card-label">Disposed</p></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('equipment-report-generate') }}" class="kpi-link">
                            <div class="kpi-card" style="border-left: 4px solid #dc3545;">
                                <div class="kpi-card-body">
                                    <div class="kpi-card-row">
                                        <h4 class="kpi-card-value">{{ $overdueCalibrationCount }}</h4>
                                        <div class="kpi-card-icon"><i class="mdi mdi-alert-circle" style="color: #dc3545;"></i></div>
                                    </div>
                                    <div class="kpi-card-row"><p class="kpi-card-label">Calibration Overdue</p></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('equipment-report-generate') }}" class="kpi-link">
                            <div class="kpi-card" style="border-left: 4px solid #e83e8c;">
                                <div class="kpi-card-body">
                                    <div class="kpi-card-row">
                                        <h4 class="kpi-card-value">{{ $overdueMaintainanceCount }}</h4>
                                        <div class="kpi-card-icon"><i class="mdi mdi-alert-decagram" style="color: #e83e8c;"></i></div>
                                    </div>
                                    <div class="kpi-card-row"><p class="kpi-card-label">Maintainance Overdue</p></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 d-flex align-items-stretch">
                        <a href="{{ route('equipment-home') }}" class="btn btn-outline-primary btn-block d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-format-list-bulleted mr-1"></i> Open Equipment List
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="quick-actions-horizontal" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h5 class="mb-3"><i class="mdi mdi-lightning-bolt"></i> Quick Actions</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <button type="button" wire:click="$dispatchTo('equipment.equipment-manager', 'equipment-open-create-modal')" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-plus"></i> Add Equipment
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" wire:click="$dispatchTo('equipment.equipment-manager', 'equipment-open-bulk-upload-modal')" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-file-upload"></i> Import Equipment
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('equipment-report-generate') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-chart-box"></i> Reports
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('equipment.asset-types.index') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-layers"></i> Asset Types
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h5 class="mb-3"><i class="mdi mdi-chart-pie"></i> Equipment Status Distribution</h5>
                            <div style="height: 300px; position: relative;">
                                <canvas id="equipmentStatusDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h5 class="mb-3"><i class="mdi mdi-chart-bar"></i> Maintainance & Calibration Health</h5>
                            <div style="height: 300px; position: relative;">
                                <canvas id="equipmentHealthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="chart-container" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h5 class="mb-3"><i class="mdi mdi-chart-line"></i> Purchase Trend (Last 6 Months)</h5>
                            <div style="height: 300px; position: relative;">
                                <canvas id="equipmentPurchaseTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="mdi mdi-alert"></i> Critical Equipment</h6>
                            </div>
                            <div class="card-body" style="max-height: 320px; overflow-y: auto;">
                                @forelse ($criticalEquipment as $item)
                                    <div class="border rounded p-2 mb-2">
                                        <a href="{{ route('view-equipment', ['equipmentId' => $item['id']]) }}" class="font-weight-bold">{{ $item['name'] }}</a>
                                        <div class="small text-muted">{{ $item['equipment_number'] }}</div>
                                        <div class="small">Calib: {{ $item['calibration_days_left'] }}d | Maint: {{ $item['maintainance_days_left'] }}d</div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No critical equipment right now.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .kpi-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .kpi-link { text-decoration: none !important; color: inherit !important; display: block; cursor: pointer; }
        .kpi-link:hover .kpi-card { transform: translateY(-2px); transition: all 0.2s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.14); }
        .kpi-card-body { padding: 0.75rem; }
        .kpi-card-row { display: flex; justify-content: space-between; align-items: center; }
        .kpi-card-value { font-size: 2rem; font-weight: 700; margin-bottom: 0; color: #2d3748; line-height: 1; }
        .kpi-card-label { font-size: 1rem; font-weight: 500; color: #6b7280; margin-bottom: 0; }
        .kpi-card-icon { font-size: 2rem; }
        .quick-action-btn { display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px; }
        .quick-action-btn:hover { color: white !important; background-color: #0d6efd; border-color: #0d6efd; }
    </style>

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

    <livewire:equipment.equipment-manager :embedded="true" />
</div>
