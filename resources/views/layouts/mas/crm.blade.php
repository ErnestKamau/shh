@extends('layouts.mas.layout.app')

@section('content2')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">CRM Analytics</h1>
            <p class="text-muted small mb-0">Customer order trends and key account performance.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('mas.export', 'crm') }}" class="btn btn-success btn-sm mr-2">
                <i class="mdi mdi-download"></i> Download Report
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Top Clients Chart -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Top Accounts (Order Volume)</h5>
                </div>
                <div class="card-body">
                    <canvas id="crmClientsChart" height="400"></canvas>
                </div>
            </div>
        </div>

        <!-- Order Trend Chart -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">14-Day Order Trend</h5>
                </div>
                <div class="card-body">
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
                                    <th class="border-0">Client Name</th>
                                    <th class="border-0 text-right">Total Batches</th>
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
@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Doughnut Chart for Clients
        var crmCtx = document.getElementById('crmClientsChart').getContext('2d');
        var topClients = @json($stats['top_clients']);
        
        new Chart(crmCtx, {
            type: 'doughnut',
            data: {
                labels: topClients.map(c => c.name),
                datasets: [{
                    data: topClients.map(c => c.total),
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
        var trendData = @json($stats['order_trend']);
        
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'Orders',
                    data: trendData.map(d => d.count),
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
    });
</script>
@endsection
