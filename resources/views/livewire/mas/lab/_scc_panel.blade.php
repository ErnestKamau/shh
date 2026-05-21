{{-- SCC Right Panel: Sample Control metrics + reactive doughnut chart --}}
<div class="kebs-title-bar py-2 mb-3" style="background-color: #1e3a8a;">
    <h6 class="mb-0 small text-center">Sample Control Performance</h6>
</div>

<div class="row mb-2">
    <div class="col-6">
        <div class="scc-box">
            <div class="scc-label">Number of Samples</div>
            <div class="scc-value">{{ number_format($stats['testing_metrics']['scc_total_samples'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-6">
        <div class="scc-box">
            <div class="scc-label">Average SCC TAT</div>
            <div class="scc-value">{{ $stats['testing_metrics']['scc_avg_tat'] ?? 0 }}<small class="font-weight-normal"> d</small></div>
        </div>
    </div>
</div>

<div class="scc-box mb-3" style="background-color: #e0f2fe;">
    <div class="scc-label">% TAT Compliance SCC</div>
    @php $sccRate = $stats['testing_metrics']['scc_compliance'] ?? 0; @endphp
    <div class="scc-value" style="font-size:24px; color: {{ $sccRate >= 80 ? '#0369a1' : ($sccRate >= 50 ? '#b45309' : '#dc2626') }};">
        {{ $sccRate }}%
    </div>
</div>

{{-- Reactive Doughnut Chart --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-2" wire:ignore>
        <canvas id="performanceChart" data-sla-rate="{{ $stats['summary']['sla_compliance_rate'] ?? 0 }}" height="200"></canvas>
    </div>
</div>

{{-- Summary boxes --}}
<div class="mt-3">
    <div class="d-flex justify-content-between text-muted small mb-1">
        <span>Active Batches</span>
        <strong class="text-dark">{{ number_format($stats['summary']['active_batches'] ?? 0) }}</strong>
    </div>
    <div class="d-flex justify-content-between text-muted small mb-1">
        <span>Tests Requested</span>
        <strong class="text-dark">{{ number_format($stats['summary']['tests_requested'] ?? 0) }}</strong>
    </div>
    <div class="d-flex justify-content-between text-muted small mb-1">
        <span>Tests Completed</span>
        <strong class="text-success">{{ number_format($stats['summary']['tests_completed'] ?? 0) }}</strong>
    </div>
    <div class="d-flex justify-content-between text-muted small">
        <span>Tests Pending</span>
        <strong class="text-warning">{{ number_format($stats['summary']['tests_pending'] ?? 0) }}</strong>
    </div>
</div>

@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script>
(function() {
    var slaRate = 0;
    var chartInst = null;

    function getChartRate() {
        var canvas = document.getElementById('performanceChart');
        return canvas ? parseInt(canvas.dataset.slaRate || '0', 10) : 0;
    }

    function buildChart() {
        var ctx = document.getElementById('performanceChart');
        if (!ctx) return;
        if (typeof Chart === 'undefined') return;

        slaRate = getChartRate();

        if (chartInst) { chartInst.destroy(); }

        chartInst = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Compliance', 'Pending'],
                datasets: [{
                    data: [slaRate, Math.max(0, 100 - slaRate)],
                    backgroundColor: ['#0ea5e9', '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 80,
                legend: { position: 'bottom', labels: { boxWidth: 12, fontSize: 10 } }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', buildChart);

    document.addEventListener('mas-lab-tat-chart-updated', function(event) {
        var canvas = document.getElementById('performanceChart');
        if (canvas && event.detail && typeof event.detail.slaRate !== 'undefined') {
            canvas.dataset.slaRate = event.detail.slaRate;
        }
        setTimeout(buildChart, 50);
    });

})();
</script>
@endpush
@endonce
