<div class="row mb-3">
@foreach([
    'total' => 'Total Requests',
    'open' => 'Open',
    'pending_approval' => 'Pending Approvals',
    'completed' => 'Completed',
    'delayed' => 'Delayed',
    'received_today' => 'Received Today',
] as $key => $label)
    <div class="col-md-2 mb-2">
        <div class="card registry-kpi-card p-3">
            <small class="text-muted">{{ $label }}</small>
            <h4 class="mb-0">{{ $kpis[$key] ?? 0 }}</h4>
        </div>
    </div>
@endforeach
</div>
