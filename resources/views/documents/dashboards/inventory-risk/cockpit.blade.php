@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Inventory Risk & Stock Management Cockpit</h1>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <div class="btn-group" role="group">
                        <button id="exportBtn" type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('inventory-risk.export', ['format' => 'excel']) }}">
                                <i class="fas fa-file-excel text-success"></i> Excel
                            </a>
                            <a class="dropdown-item" href="{{ route('inventory-risk.export', ['format' => 'pdf']) }}">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </a>
                            <a class="dropdown-item" href="{{ route('inventory-risk.export', ['format' => 'csv']) }}">
                                <i class="fas fa-file-csv text-info"></i> CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Risk Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Expiry Risk Score</div>
                    <h3 class="h2 mb-0">{{ $metrics['expiryRiskScore'] ?? 0 }}%</h3>
                    <small class="text-{{ $metrics['expiryRiskScore'] <= 30 ? 'success' : 'danger' }}">
                        {{ $metrics['expiryRiskScore'] <= 30 ? '✓ Low Risk' : '⚠ High Risk' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Expiring Soon (90 Days)</div>
                    <h3 class="h2 mb-0">{{ $metrics['expiringItems'] ?? 0 }}</h3>
                    <small class="text-muted">Items at risk</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">Expired Items</div>
                    <h3 class="h2 mb-0">{{ $metrics['expiredItems'] ?? 0 }}</h3>
                    <small class="text-danger">Requires disposal</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-uppercase mb-1">Total SKUs</div>
                    <h3 class="h2 mb-0">{{ $metrics['totalSKUs'] ?? 0 }}</h3>
                    <small class="text-muted">Active inventory</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Alerts -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Critical Alerts ({{ count($criticalAlerts) }})
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($criticalAlerts) > 0)
                    @foreach($criticalAlerts as $alert)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>{{ $alert['item'] }}</strong> - {{ $alert['issue'] }}<br>
                        <small>{{ $alert['detail'] }}</small>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endforeach
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No critical inventory issues!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Status Overview -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Inventory Distribution by Status</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['statusDistribution']) && count($metrics['statusDistribution']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">% Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['statusDistribution'] as $status)
                                <tr>
                                    <td><strong>{{ $status['status'] }}</strong></td>
                                    <td class="text-center">{{ $status['count'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $status['status'] == 'Available' ? 'success' : ($status['status'] == 'Low Stock' ? 'warning' : 'danger') }}">
                                            {{ $status['percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No status data</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">💰 Inventory Value Distribution by Category</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['valueByCategory']) && count($metrics['valueByCategory']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-right">Value</th>
                                    <th class="text-center">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['valueByCategory'] as $category)
                                <tr>
                                    <td><strong>{{ $category['name'] }}</strong></td>
                                    <td class="text-right">{{ $category['value'] }}</td>
                                    <td class="text-center">{{ $category['percentage'] }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No value data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reorder Point Analysis -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📈 Reorder Point Status</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['reorderStatus']) && count($metrics['reorderStatus']) > 0)
                    <div class="row">
                        @foreach($metrics['reorderStatus'] as $status)
                        <div class="col-md-4">
                            <div class="card border-left-{{ $status['color'] }}">
                                <div class="card-body">
                                    <div class="text-{{ $status['color'] }} font-weight-bold text-uppercase mb-1">{{ $status['label'] }}</div>
                                    <h3 class="h2 mb-0">{{ $status['count'] }}</h3>
                                    <small class="text-muted">{{ $status['percentage'] }}%</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info">No reorder data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Items by Expiry Risk -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">⏰ Top 10 Items by Expiry Risk</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['topExpiryRisk']) && count($metrics['topExpiryRisk']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Expiry Date</th>
                                    <th class="text-center">Days Until Expiry</th>
                                    <th>Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['topExpiryRisk'] as $item)
                                <tr class="@if($item['riskLevel'] == 'Critical') table-danger @elseif($item['riskLevel'] == 'High') table-warning @endif">
                                    <td><strong>{{ $item['name'] }}</strong></td>
                                    <td class="text-center">{{ $item['stock'] }}</td>
                                    <td class="text-center">{{ $item['expiryDate'] }}</td>
                                    <td class="text-center"><span class="badge badge-{{ $item['riskLevel'] == 'Critical' ? 'danger' : ($item['riskLevel'] == 'High' ? 'warning' : 'info') }}">{{ $item['daysUntilExpiry'] }}</span></td>
                                    <td><span class="badge badge-{{ $item['riskLevel'] == 'Critical' ? 'danger' : ($item['riskLevel'] == 'High' ? 'warning' : 'success') }}">{{ $item['riskLevel'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success">No items at expiry risk!</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Depletion Forecast -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Stock Depletion Forecast (30-Day Projection)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['depletionForecast']) && count($metrics['depletionForecast']) > 0)
                    <canvas id="depletionForecastChart" height="80"></canvas>
                    @else
                    <div class="alert alert-info">No forecast data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    @if(isset($metrics['depletionForecast']) && count($metrics['depletionForecast']) > 0)
    const ctx = document.getElementById('depletionForecastChart').getContext('2d');
    const depletionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($metrics['depletionForecast'], 'date')) !!},
            datasets: {!! json_encode($metrics['depletionForecast']['datasets'] ?? []) !!}
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    max: 5
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Projected Stock Level'
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush

@endsection
