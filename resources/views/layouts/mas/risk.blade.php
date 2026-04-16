@extends('layouts.mas.layout.app')

@section('content2')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/risk.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/risk.subtitle') }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('mas.export', 'risk') }}" class="btn btn-success btn-sm mr-2">
                <i class="mdi mdi-download"></i> {{ __('mas/common.download_report') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Risk Distribution Chart -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/risk.distribution') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="riskLevelChart" height="350"></canvas>
                </div>
            </div>
        </div>

        <!-- Risk Summary -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/risk.health') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <h3 class="font-weight-bold mb-0 text-primary">{{ $stats['active_count'] }}</h3>
                                <p class="text-muted small text-uppercase mb-0">{{ __('mas/risk.active_risks') }}</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <h3 class="font-weight-bold mb-0 text-danger">{{ $stats['critical_count'] }}</h3>
                                <p class="text-muted small text-uppercase mb-0">{{ __('mas/risk.critical_issues') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="font-weight-bold text-muted small text-uppercase mb-3">{{ __('mas/risk.review_schedule') }}</h6>
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                        <i class="mdi mdi-clock-alert mdi-24px mr-3"></i>
                        <div>
                            <h5 class="font-weight-bold mb-0">{{ $stats['requiring_review'] }}</h5>
                            <p class="small mb-0">{{ __('mas/risk.reviews_due') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-auto">
                        <p class="text-muted small mt-4">
                            {{ __('mas/risk.risk_description_text') }}
                        </p>
                        <a href="/risk-assessment" class="btn btn-block btn-outline-danger mt-3">{{ __('mas/risk.open_risk_management') }} <i class="mdi mdi-open-in-new"></i></a>
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
        var riskCtx = document.getElementById('riskLevelChart').getContext('2d');
        var riskStats = @json($stats['by_level']);
        
        new Chart(riskCtx, {
            type: 'pie',
            data: {
                labels: riskStats.map(r => r.risk_level),
                datasets: [{
                    data: riskStats.map(r => r.count),
                    backgroundColor: [
                        '#dc3545', '#fd7e14', '#ffc107', '#28a745', '#17a2b8'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right' }
            }
        });
    });
</script>
@endsection
