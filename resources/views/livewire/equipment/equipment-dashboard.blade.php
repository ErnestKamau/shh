<div class="equipment-dashboard-content">
    <div class="equipment-page-header mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <div>
                <h3 class="equipment-page-title mb-1">Equipment Dashboard</h3>
                <p class="equipment-page-description mb-0">Monitor equipment status, overdue schedules, and maintenance/calibration health in one place.</p>
            </div>
            <div class="equipment-page-date text-primary">
                <i class="mdi mdi-calendar"></i>
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>
    </div>

    <div class="tab-card">
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
                                    <div class="kpi-card-row"><p class="kpi-card-label">{{ __('equipment.total_equipment') }}</p></div>
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
                                    <div class="kpi-card-row"><p class="kpi-card-label">{{ __('equipment.active') }}</p></div>
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
                                    <div class="kpi-card-row"><p class="kpi-card-label">{{ __('equipment.calibration_attention') }}</p></div>
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
                                        <div class="kpi-card-icon"><i class="mdi mdi-wrench" style="color: #fd7e14;"></i></div>
                                    </div>
                                    <div class="kpi-card-row"><p class="kpi-card-label">{{ __('equipment.maintenance_attention') }}</p></div>
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
                                    <div class="kpi-card-row"><p class="kpi-card-label">{{ __('equipment.disposed') }}</p></div>
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
                                    <div class="kpi-card-row"><p class="kpi-card-label">{{ __('equipment.calibration_overdue') }}</p></div>
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
                                    <div class="kpi-card-row"><p class="kpi-card-label">{{ __('equipment.maintenance_overdue') }}</p></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 d-flex align-items-stretch">
                        <a href="{{ route('equipment-home') }}" class="btn btn-outline-primary btn-block d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-format-list-bulleted mr-1"></i> {{ __('equipment.open_equipment_list') }}
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="quick-actions-horizontal" style="border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h5 class="mb-3"><i class="mdi mdi-lightning-bolt"></i> {{ __('equipment.quick_actions') }}</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <button type="button" wire:click="$dispatchTo('equipment.equipment-manager', 'equipment-open-create-modal')" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-plus"></i> {{ __('equipment.add_equipment') }}
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" wire:click="$dispatchTo('equipment.equipment-manager', 'equipment-open-bulk-upload-modal')" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-file-upload"></i> {{ __('equipment.import_equipment') }}
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('equipment-report-generate') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-chart-box"></i> {{ __('equipment.reports') }}
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('equipment.asset-types.index') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-layers"></i> {{ __('equipment.asset_types') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container" style="border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h5 class="mb-3"><i class="mdi mdi-chart-pie"></i> {{ __('equipment.status_distribution') }}</h5>
                            <div style="height: 300px; position: relative;">
                                <canvas id="equipmentStatusDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container" style="border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h5 class="mb-3"><i class="mdi mdi-chart-bar"></i> {{ __('equipment.maintenance_calibration_health') }}</h5>
                            <div style="height: 300px; position: relative;">
                                <canvas id="equipmentHealthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="chart-container" style="border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h5 class="mb-3"><i class="mdi mdi-chart-line"></i> {{ __('equipment.purchase_trend_last_6_months') }}</h5>
                            <div style="height: 300px; position: relative;">
                                <canvas id="equipmentPurchaseTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="critical-equipment-card h-100">
                            <div class="critical-equipment-header">
                                <h5 class="mb-0"><i class="mdi mdi-alert"></i> {{ __('equipment.critical_equipment') }}</h5>
                            </div>
                            <div class="critical-equipment-body" style="max-height: 320px; overflow-y: auto;">
                                @forelse ($criticalEquipment as $item)
                                    <div class="critical-equipment-item border rounded p-2 mb-2">
                                        <a href="{{ route('view-equipment', ['equipmentId' => $item['id']]) }}" class="font-weight-bold">{{ $item['name'] }}</a>
                                        <div class="small text-muted">{{ $item['equipment_number'] }}</div>
                                        <div class="small">Calib: {{ $item['calibration_days_left'] }}d | Maint: {{ $item['maintainance_days_left'] }}d</div>
                                    </div>
                                @empty
                                    <div class="critical-empty-state">
                                        <div class="critical-empty-icon">
                                            <i class="mdi mdi-shield-check-outline"></i>
                                        </div>
                                        <h6 class="critical-empty-title mb-1">All Equipment Healthy</h6>
                                        <p class="critical-empty-text mb-0">No critical equipment right now. Calibration and maintenance schedules are within safe limits.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .equipment-dashboard-content { padding-left: 15px; padding-right: 15px; }
        .equipment-page-header {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px 18px;
        }
        .equipment-page-title { font-size: 1.65rem; font-weight: 700; color: #1f2937; }
        .equipment-page-description { color: #4b5563; font-size: 0.98rem; }
        .equipment-page-date { font-size: 1rem; font-weight: 500; }
        .kpi-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .kpi-link { text-decoration: none !important; color: inherit !important; display: block; cursor: pointer; }
        .kpi-link:hover .kpi-card { transform: translateY(-2px); transition: all 0.2s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.14); }
        .kpi-card-body { padding: 0.75rem; }
        .kpi-card-row { display: flex; justify-content: space-between; align-items: center; }
        .kpi-card-value { font-size: 2rem; font-weight: 700; margin-bottom: 0; color: #2d3748; line-height: 1; }
        .kpi-card-label { font-size: 1rem; font-weight: 500; color: #6b7280; margin-bottom: 0; }
        .kpi-card-icon { font-size: 2rem; }
        .equipment-dashboard-content .card,
        .equipment-dashboard-content .kpi-card,
        .equipment-dashboard-content .quick-actions-horizontal,
        .equipment-dashboard-content .chart-container,
        .equipment-dashboard-content .card-header {
            background-color: #ffffff !important;
        }
        .critical-equipment-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .critical-equipment-header { margin-bottom: 12px; }
        .critical-equipment-item {
            border-color: #e5e7eb !important;
            background: #ffffff;
        }
        .critical-empty-state {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            text-align: center;
            padding: 22px 14px;
        }
        .critical-empty-icon {
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8f5ee;
            color: #1f9d63;
            font-size: 1.45rem;
        }
        .critical-empty-title {
            color: #1f2937;
            font-weight: 700;
        }
        .critical-empty-text {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.45;
        }
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
