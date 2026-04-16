<div>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/crm.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/crm.subtitle') }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('mas.export', 'crm') }}" class="btn btn-success btn-sm mr-2">
                <i class="mdi mdi-download"></i> {{ __('mas/common.download_report') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Top Clients Chart -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/crm.top_accounts') }}</h5>
                </div>
                <div class="card-body" wire:ignore>
                    <canvas id="crmClientsChart" height="400"></canvas>
                </div>
            </div>
        </div>

        <!-- Order Trend Chart -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/crm.order_trend', ['period' => $stats['period_label'] ?? '']) }}</h5>
                        <span class="text-muted x-small text-uppercase">{{ $stats['period_label'] ?? '' }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($period === 'custom')
                        <div class="d-flex align-items-center mr-3">
                            <input type="date" wire:model="from" class="form-control form-control-sm mr-2" style="width: 130px;">
                            <input type="date" wire:model="to" class="form-control form-control-sm mr-2" style="width: 130px;">
                            <button wire:click="applyCustomFilter" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-check"></i>
                            </button>
                        </div>
                        @endif
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle font-weight-bold" type="button" data-toggle="dropdown">
                                <i class="mdi mdi-filter-variant"></i> 
                                {{ __('mas/crm.period_' . $period) }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item @if($period == '1_week') active @endif" href="#" wire:click.prevent="$set('period', '1_week')">{{ __('mas/crm.period_1_week') }}</a>
                                <a class="dropdown-item @if($period == '2_weeks') active @endif" href="#" wire:click.prevent="$set('period', '2_weeks')">{{ __('mas/crm.period_2_weeks') }}</a>
                                <a class="dropdown-item @if($period == '1_month') active @endif" href="#" wire:click.prevent="$set('period', '1_month')">{{ __('mas/crm.period_1_month') }}</a>
                                <a class="dropdown-item @if($period == '2_months') active @endif" href="#" wire:click.prevent="$set('period', '2_months')">{{ __('mas/crm.period_2_months') }}</a>
                                <a class="dropdown-item @if($period == 'quarterly') active @endif" href="#" wire:click.prevent="$set('period', 'quarterly')">{{ __('mas/crm.period_quarterly') }}</a>
                                <a class="dropdown-item @if($period == 'semi_annually') active @endif" href="#" wire:click.prevent="$set('period', 'semi_annually')">{{ __('mas/crm.period_semi_annually') }}</a>
                                <a class="dropdown-item @if($period == 'annually') active @endif" href="#" wire:click.prevent="$set('period', 'annually')">{{ __('mas/crm.period_annually') }}</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item @if($period == 'custom') active @endif" href="#" wire:click.prevent="$set('period', 'custom')">{{ __('mas/crm.period_custom') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body" wire:ignore>
                    <canvas id="crmTrendChart" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">{{ __('mas/crm.client_name') }}</th>
                                    <th class="border-0 text-right">{{ __('mas/crm.total_batches') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['top_clients'] as $client)
                                <tr>
                                    <td>{{ $client->name }}</td>
                                    <td class="text-right font-weight-bold">{{ $client->total }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var crmChart, trendChart;

        function initCharts(stats) {
            if (!stats) return;

            // Doughnut Chart for Clients
            var crmCtx = document.getElementById('crmClientsChart').getContext('2d');
            if (crmChart) crmChart.destroy();
            crmChart = new Chart(crmCtx, {
                type: 'doughnut',
                data: {
                    labels: stats.top_clients.map(c => c.name),
                    datasets: [{
                        data: stats.top_clients.map(c => c.total),
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d', '#17a2b8', '#6610f2', '#e83e8c', '#fd7e14', '#20c997'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
                }
            });

            // Line Chart for Trend
            var trendCtx = document.getElementById('crmTrendChart').getContext('2d');
            if (trendChart) trendChart.destroy();
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: stats.order_trend.map(d => d.date),
                    datasets: [{
                        label: "{{ __('mas/crm.orders') }}",
                        data: stats.order_trend.map(d => d.count),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }]
                    }
                }
            });
        }

        // Initial load
        initCharts(@json($stats));

        // Listen for Livewire updates
        document.addEventListener('statsUpdated', function(event) {
            initCharts(event.detail.stats);
        });
    });
</script>
</div>