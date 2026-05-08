<div>
    <style>
        .text-purple { color: #6f42c1; }
        .bg-purple { background-color: #6f42c1; }
        .progress-purple { background-color: #6f42c1; }
        .ai-card { border-radius: 12px; border: none; transition: all 0.3s ease; }
        .ai-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .drift-alert { background: #fff5f5; border-left: 5px solid #dc3545 !important; }
    </style>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/ai.title') }}</h1>
                <p class="text-muted small mb-0">{{ __('mas/ai.subtitle') }}</p>
            </div>
            <div class="col-auto d-flex align-items-center">
                <button wire:click="refresh" wire:loading.attr="disabled" class="btn btn-white btn-sm shadow-sm border-0 mr-3 bg-white text-purple font-weight-bold" style="border-radius: 8px; padding: 0.5rem 1rem;">
                    <i class="mdi mdi-refresh mr-1" wire:loading.class="mdi-spin"></i> {{ __('mas/common.refresh') }}
                </button>
                <span class="badge bg-purple text-white p-2 shadow-sm">
                    <i class="mdi mdi-calendar mr-1"></i> {{ date('Y-m-d H:i') }}
                </span>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card ai-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-muted text-uppercase small font-weight-bold mb-0">System Health</h6>
                            <i class="mdi mdi-heart-pulse text-purple mdi-24px"></i>
                        </div>
                        <h2 class="font-weight-bold mb-1">{{ $stats['kpis']['health_score'] ?? 0 }}%</h2>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $stats['kpis']['health_score'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card ai-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-muted text-uppercase small font-weight-bold mb-0">Accuracy</h6>
                            <i class="mdi mdi-target-variant text-purple mdi-24px"></i>
                        </div>
                        <h2 class="font-weight-bold mb-1">{{ $stats['kpis']['routing_accuracy_percent'] ?? 0 }}%</h2>
                        <p class="text-muted small mb-0">Intent detection success</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card ai-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-muted text-uppercase small font-weight-bold mb-0">Active Alerts</h6>
                            <i class="mdi mdi-alert-circle {{ ($stats['alerts_active'] ?? 0) > 0 ? 'text-danger' : 'text-purple' }} mdi-24px"></i>
                        </div>
                        <h2 class="font-weight-bold mb-1">{{ $stats['alerts_active'] ?? 0 }}</h2>
                        <p class="text-muted small mb-0">SLA or Quota violations</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card ai-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-muted text-uppercase small font-weight-bold mb-0">Est. Cost (Daily)</h6>
                            <i class="mdi mdi-currency-usd text-purple mdi-24px"></i>
                        </div>
                        <h2 class="font-weight-bold mb-1">${{ $costAnalysis['estimated_cost_usd'] ?? 0 }}</h2>
                        <p class="text-muted small mb-0">Token consumption cost</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Model Performance Leaderboard -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-purple"><i class="mdi mdi-trophy-outline mr-1"></i> Model Performance Leaderboard</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-items-center mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Rank & Model</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Success Rate</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Avg Latency</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Requests</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modelComparison['models'] ?? [] as $model)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">#{{ $model['rank'] }} {{ $model['name'] }}</h6>
                                                    @if($model['name'] === $modelComparison['leader'])
                                                        <span class="badge bg-success text-white text-xxs p-1" style="width: fit-content;">BEST PERFORMER</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="mr-2 text-xs font-weight-bold">{{ $model['success_rate'] }}%</span>
                                                <div style="width: 100px;">
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar progress-purple" style="width: {{ $model['success_rate'] }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">{{ $model['avg_latency_ms'] }}ms</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">{{ number_format($model['total_requests']) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Intent Accuracy Breakdown -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-purple"><i class="mdi mdi-brain-outline mr-1"></i> Intent Analysis</h6>
                    </div>
                    <div class="card-body">
                        @foreach($intentBreakdown['intents'] ?? [] as $intent)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-xs font-weight-bold">{{ $intent['name'] }}</span>
                                <span class="text-xs font-weight-bold">{{ $intent['accuracy_percent'] }}% Accuracy</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: {{ $intent['accuracy_percent'] }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-xxs text-muted">{{ number_format($intent['count']) }} requests</span>
                                <span class="text-xxs text-muted">Conf: {{ round($intent['avg_confidence'] * 100) }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Drift Alerts -->
        @if(!empty($driftAlerts))
        <div class="row mt-2">
            <div class="col-12">
                <div class="card shadow-sm border-0 drift-alert">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-alert-decagram text-danger mdi-24px mr-3"></i>
                            <div>
                                <h6 class="mb-0 font-weight-bold text-danger">Data Drift Detected</h6>
                                <p class="mb-0 text-sm text-muted">The following features show significant statistical deviation:</p>
                                <ul class="mb-0 text-xs mt-1">
                                    @foreach($driftAlerts as $alert)
                                        <li><strong>{{ $alert['feature_type'] }}:</strong> {{ $alert['alert'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
