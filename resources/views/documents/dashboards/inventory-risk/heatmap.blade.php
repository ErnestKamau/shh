@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0">Inventory Expiry Heatmap & Dead Stock Analysis</h1>
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

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="expiryFilterForm" class="form-inline" method="GET">
                        <div class="form-group mr-3">
                            <label for="category" class="mr-2">Category:</label>
                            <select id="category" name="category" class="form-control form-control-sm">
                                <option value="">All Categories</option>
                                @if(isset($metrics['allCategories']))
                                    @foreach($metrics['allCategories'] as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label for="status" class="mr-2">Status:</label>
                            <select id="status" name="status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="expired">Expired</option>
                                <option value="expiring_soon">Expiring Soon (30 days)</option>
                                <option value="at_risk">At Risk (60-90 days)</option>
                                <option value="valid">Valid</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <button type="reset" class="btn btn-sm btn-secondary ml-2">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiry Heatmap -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Expiry Status Heatmap: Category vs Expiry Window</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['expiryHeatmap']) && count($metrics['expiryHeatmap']) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Category / Expiry Window</th>
                                    <th>Already Expired</th>
                                    <th>0-30 Days</th>
                                    <th>30-60 Days</th>
                                    <th>60-90 Days</th>
                                    <th>90+ Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['expiryHeatmap'] as $category => $windows)
                                <tr>
                                    <td><strong>{{ $category }}</strong></td>
                                    @foreach($windows as $window)
                                    <td>
                                        <div class="p-2 rounded" style="background-color: {{ $this->getExpiryColor($window['status']) }}; color: white; font-weight: bold;">
                                            {{ $window['count'] }}
                                        </div>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <p class="text-muted"><small>Color Legend: 
                            <span style="background-color: #28a745; color: white;" class="px-2">Green (90+ Days)</span>
                            <span style="background-color: #ffc107; color: white;" class="px-2">Yellow (30-90 Days)</span>
                            <span style="background-color: #fd7e14; color: white;" class="px-2">Orange (0-30 Days)</span>
                            <span style="background-color: #dc3545; color: white;" class="px-2">Red (Expired)</span>
                        </small></p>
                    </div>
                    @else
                    <div class="alert alert-info">No expiry heatmap data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Dead Stock Analysis -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">💀 Dead Stock Analysis (No Movement in 6+ Months)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['deadStock']) && count($metrics['deadStock']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Unit Cost</th>
                                    <th class="text-center">Total Value</th>
                                    <th class="text-center">Last Movement</th>
                                    <th>Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['deadStock'] as $item)
                                <tr class="table-danger">
                                    <td><strong>{{ $item['name'] }}</strong></td>
                                    <td>{{ $item['category'] }}</td>
                                    <td class="text-center">{{ $item['stock'] }}</td>
                                    <td class="text-center">{{ $item['unitCost'] }}</td>
                                    <td class="text-center"><strong>{{ $item['totalValue'] }}</strong></td>
                                    <td class="text-center">{{ $item['lastMovement'] }}</td>
                                    <td>{{ $item['expiryDate'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <strong>Recommendation:</strong> Consider disposal/donation for expired dead stock or markdowns for aging stock.
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No significant dead stock detected!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Expiry by Month (Forecast) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📅 Projected Expiries by Month (Next 12 Months)</h5>
                </div>
                <div class="card-body">
                    @if(isset($metrics['expiryForecast']) && count($metrics['expiryForecast']) > 0)
                    <canvas id="expiryForecastChart" height="80"></canvas>
                    @else
                    <div class="alert alert-info">No expiry forecast data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Rapidly Expiring Items -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">⚠️ Rapidly Expiring - Immediate Action Required ({{ count($rapidlyExpiring) }} items)</h5>
                </div>
                <div class="card-body">
                    @if(count($rapidlyExpiring) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Expiry Date</th>
                                    <th class="text-center">Days Remaining</th>
                                    <th>Recommendation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rapidlyExpiring as $item)
                                <tr class="table-warning">
                                    <td><strong>{{ $item['name'] }}</strong></td>
                                    <td class="text-center">{{ $item['stock'] }}</td>
                                    <td class="text-center">{{ $item['expiryDate'] }}</td>
                                    <td class="text-center"><span class="badge badge-warning">{{ $item['daysRemaining'] }}</span></td>
                                    <td>
                                        @if($item['daysRemaining'] <= 7)
                                            <strong>URGENT:</strong> Expedite usage or dispose
                                        @else
                                            Prioritize for upcoming uses
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> No items rapidly expiring!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    @if(isset($metrics['expiryForecast']) && count($metrics['expiryForecast']) > 0)
    const ctx = document.getElementById('expiryForecastChart').getContext('2d');
    const expiryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($metrics['expiryForecast'], 'month')) !!},
            datasets: [{
                label: 'Projected Expiries',
                data: {!! json_encode(array_column($metrics['expiryForecast'], 'count')) !!},
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    @endif

    document.getElementById('expiryFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const params = new URLSearchParams(new FormData(this));
        window.location.href = window.location.pathname + '?' + params.toString();
    });
</script>
@endpush

@endsection
