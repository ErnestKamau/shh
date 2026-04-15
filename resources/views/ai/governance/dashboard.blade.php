@extends('layouts.ImaraAi.manager')

@section('kb-content')
<div class="manager-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">AI Model Governance Dashboard</h3>
        <small class="text-muted">Last updated: {{ $overview['last_updated'] ?? now()->toIso8601String() }}</small>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted">System Status</div>
                    <h5 class="mb-0 text-capitalize">{{ $overview['system_status'] ?? 'unknown' }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted">Active Alerts</div>
                    <h5 class="mb-0">{{ $overview['alerts_active'] ?? 0 }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted">Health Score</div>
                    <h5 class="mb-0">{{ $overview['kpis']['health_score'] ?? 0 }}%</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted">Routing Accuracy</div>
                    <h5 class="mb-0">{{ $overview['kpis']['routing_accuracy_percent'] ?? 0 }}%</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Model Registry</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Model</th>
                        <th>Type</th>
                        <th>Version</th>
                        <th>Framework</th>
                        <th>Active</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($models ?? []) as $model)
                        <tr>
                            <td>{{ $model['id'] ?? '-' }}</td>
                            <td>{{ $model['model_name'] ?? '-' }}</td>
                            <td>{{ $model['model_type'] ?? '-' }}</td>
                            <td>{{ $model['version'] ?? '-' }}</td>
                            <td>{{ $model['framework'] ?? '-' }}</td>
                            <td>
                                @if(!empty($model['is_active']))
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>{{ $model['created_at'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No model registry records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Live Performance</strong>
                </div>
                <div class="card-body">
                    @forelse(($performance ?? []) as $perf)
                        <div class="border rounded p-3 mb-3">
                            <div><strong>{{ $perf['model'] ?? '-' }}</strong></div>
                            <div class="small text-muted">Requests: {{ $perf['total_requests'] ?? 0 }}</div>
                            <div class="small text-muted">Success: {{ $perf['success_rate_percent'] ?? 0 }}%</div>
                            <div class="small text-muted">Avg latency: {{ $perf['avg_latency_ms'] ?? 0 }}ms</div>
                        </div>
                    @empty
                        <div class="text-muted">No live analytics data yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Alerts & Drift</strong>
                </div>
                <div class="card-body">
                    @php($hasAlerts = !empty($alerts) || !empty($driftAlerts))
                    @if($hasAlerts)
                        @foreach(($alerts ?? []) as $alert)
                            <div class="alert alert-warning py-2 mb-2">
                                <strong>{{ strtoupper($alert['type'] ?? 'alert') }}:</strong>
                                {{ $alert['message'] ?? 'N/A' }}
                            </div>
                        @endforeach

                        @foreach(($driftAlerts ?? []) as $drift)
                            <div class="alert alert-danger py-2 mb-2">
                                <strong>Drift:</strong> {{ $drift['alert'] ?? 'Feature drift detected.' }}
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted">No active alerts.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
