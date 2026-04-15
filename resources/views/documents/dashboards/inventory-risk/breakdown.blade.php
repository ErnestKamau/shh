@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Inventory Risk Breakdown & Optimization Analysis</h1>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <a href="{{ route('inventory-risk.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Cockpit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ABC Analysis (Pareto) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 ABC Analysis (Inventory Value Distribution)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['abcAnalysis']) && count($metrics['abcAnalysis']) > 0)
                    <div class="row mb-3">
                        @foreach($metrics['abcAnalysis'] as $category)
                        <div class="col-md-4">
                            <div class="card border-left-{{ $category['color'] }}">
                                <div class="card-body">
                                    <div class="text-{{ $category['color'] }} font-weight-bold text-uppercase mb-1">Category {{ $category['label'] }}</div>
                                    <h3 class="h2 mb-0">{{ $category['itemCount'] }}</h3>
                                    <small class="text-muted">
                                        {{ $category['percentage'] }}% of items<br>
                                        {{ $category['valuePercentage'] }}% of value
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <canvas id="abcAnalysisChart" height="60"></canvas>
                    @else
                    <div class="alert alert-info">No ABC analysis data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Turnover Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📈 Top 10 Items by Turnover Rate</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['topTurnover']) && count($metrics['topTurnover']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Turnover Rate</th>
                                    <th class="text-center">Avg Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['topTurnover'] as $idx => $item)
                                <tr>
                                    <td><strong>{{ ++$idx }}. {{ $item['name'] }}</strong></td>
                                    <td class="text-center"><span class="badge badge-success">{{ $item['turnoverRate'] }}x/yr</span></td>
                                    <td class="text-center">{{ $item['daysInventory'] }}d</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No turnover data</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🐌 Slow-Moving Items (Turnover < 1x/year)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['slowMoving']) && count($metrics['slowMoving']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Turnover</th>
                                    <th class="text-center">Stock Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['slowMoving'] as $item)
                                <tr class="table-warning">
                                    <td><strong>{{ $item['name'] }}</strong></td>
                                    <td class="text-center"><span class="badge badge-warning">{{ $item['turnoverRate'] }}x/yr</span></td>
                                    <td class="text-center">{{ $item['stockValue'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success">No slow-moving items</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reorder Point Recommendations -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">💡 Smart Reorder Recommendations</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['reorderRecs']) && count($metrics['reorderRecs']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Reorder Point</th>
                                    <th class="text-center">Safe Stock</th>
                                    <th class="text-center">Recommended Qty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['reorderRecs'] as $item)
                                <tr class="@if($item['current'] < $item['reorderPoint']) table-warning @endif">
                                    <td><strong>{{ $item['name'] }}</strong></td>
                                    <td class="text-center">{{ $item['current'] }}</td>
                                    <td class="text-center">{{ $item['reorderPoint'] }}</td>
                                    <td class="text-center">{{ $item['safeStock'] }}</td>
                                    <td class="text-center"><strong>{{ $item['recommendedQty'] }}</strong></td>
                                    <td>
                                        @if($item['current'] < $item['reorderPoint'])
                                            <span class="badge badge-danger">⚠ REORDER NOW</span>
                                        @elseif($item['current'] < $item['reorderPoint'] * 1.5)
                                            <span class="badge badge-warning">📌 Monitor</span>
                                        @else
                                            <span class="badge badge-success">✓ OK</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">No reorder recommendations</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock-out Risk Analysis -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">🚨 Stock-out Risk Analysis (Critical Items)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['stockoutRisk']) && count($metrics['stockoutRisk']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Days Supply</th>
                                    <th class="text-center">Lead Time</th>
                                    <th class="text-center">Risk of Stock-out</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['stockoutRisk'] as $item)
                                <tr class="@if($item['daysSupply'] < $item['leadTime']) table-danger @elseif($item['daysSupply'] < $item['leadTime'] * 1.5) table-warning @endif">
                                    <td><strong>{{ $item['name'] }}</strong></td>
                                    <td class="text-center">{{ $item['daysSupply'] }}d</td>
                                    <td class="text-center">{{ $item['leadTime'] }}d</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $item['riskLevel'] }}">{{ $item['riskPercentage'] }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $item['daysSupply'] < $item['leadTime'] ? 'danger' : 'warning' }}">
                                            {{ $item['daysSupply'] < $item['leadTime'] ? 'CRITICAL' : 'HIGH' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> No critical stock-out risks!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Cost Analysis -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">💰 Inventory Cost & Waste Analysis (YTD)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['costAnalysis']))
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <div class="text-primary font-weight-bold text-uppercase mb-1">Total Inventory Value</div>
                                    <h3 class="h2 mb-0">{{ $metrics['costAnalysis']['totalValue'] ?? 0 }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-danger">
                                <div class="card-body">
                                    <div class="text-danger font-weight-bold text-uppercase mb-1">Waste (Expired/Damaged)</div>
                                    <h3 class="h2 mb-0">{{ $metrics['costAnalysis']['wasteValue'] ?? 0 }}</h3>
                                    <small class="text-danger">{{ $metrics['costAnalysis']['wastePercent'] ?? 0 }}% of total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-warning">
                                <div class="card-body">
                                    <div class="text-warning font-weight-bold text-uppercase mb-1">Carrying Costs</div>
                                    <h3 class="h2 mb-0">{{ $metrics['costAnalysis']['carryingCosts'] ?? 0 }}</h3>
                                    <small class="text-muted">Annually</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-success">
                                <div class="card-body">
                                    <div class="text-success font-weight-bold text-uppercase mb-1">Optimization Potential</div>
                                    <h3 class="h2 mb-0">{{ $metrics['costAnalysis']['optimizationPotential'] ?? 0 }}</h3>
                                    <small class="text-success">Annual savings</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <canvas id="costAnalysisChart" height="60"></canvas>
                    @else
                    <div class="alert alert-info">No cost analysis data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    @if(isset($metrics['abcAnalysis']) && count($metrics['abcAnalysis']) > 0)
    const abcCtx = document.getElementById('abcAnalysisChart').getContext('2d');
    const abcChart = new Chart(abcCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($metrics['abcAnalysis'], 'label')) !!},
            datasets: [{
                label: '% of Items',
                data: {!! json_encode(array_column($metrics['abcAnalysis'], 'percentage')) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                yAxisID: 'y'
            }, {
                label: '% of Value',
                data: {!! json_encode(array_column($metrics['abcAnalysis'], 'valuePercentage')) !!},
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '% of Items'
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: '% of Value'
                    }
                }
            }
        }
    });
    @endif

    @if(isset($metrics['costAnalysis']))
    const costCtx = document.getElementById('costAnalysisChart').getContext('2d');
    const costChart = new Chart(costCtx, {
        type: 'doughnut',
        data: {
            labels: ['Valid Stock', 'Waste (Expired/Damaged)', 'Slow-Moving'],
            datasets: [{
                data: [
                    {{ $metrics['costAnalysis']['validStockValue'] ?? 0 }},
                    {{ $metrics['costAnalysis']['wasteValue'] ?? 0 }},
                    {{ $metrics['costAnalysis']['slowMovingValue'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
    @endif
</script>
@endpush

@endsection
