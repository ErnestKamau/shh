<div class="qc-page">
    @include('livewire.qc._shared-styles')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-chart-bell-curve mr-1"></i> QC Result Chart</h5>
            <small class="text-muted">
                {{ $result->analyte->code ?? '-' }} | {{ $result->sampletype->name ?? '-' }} | {{ $result->analysistype->name ?? '-' }}
            </small>
        </div>
        <a href="{{ route('qc-reports') }}" class="btn btn-sm btn-light">Back to Reports</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-2"><small class="text-muted d-block">Mean</small><strong>{{ $mean }}</strong></div>
                <div class="col-md-2"><small class="text-muted d-block">Median</small><strong>{{ $median }}</strong></div>
                <div class="col-md-2"><small class="text-muted d-block">SD</small><strong>{{ number_format($sd, 4) }}</strong></div>
                <div class="col-md-2"><small class="text-muted d-block">CV</small><strong>{{ number_format((float) $result->robust_cv, 4) }}</strong></div>
                <div class="col-md-2"><small class="text-muted d-block">% CV</small><strong>{{ number_format((float) $result->robust_cv_percentage, 4) }}</strong></div>
                <div class="col-md-2"><small class="text-muted d-block">Results</small><strong>{{ $result->results->count() }}</strong></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" wire:ignore>
            <canvas id="qcChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function renderQcChart() {
            const chartCanvas = document.getElementById('qcChart');
            if (!chartCanvas) {
                return;
            }

            const labels = @json($labels);
            const points = @json($data);
            const mean = @json($mean);
            const median = @json($median);
            const innerUpper = @json($innerUpperLimit);
            const outerUpper = @json($outerUpperLimit);
            const innerLower = @json($innerLowerLimit);
            const outerLower = @json($outerLowerLimit);

            if (window.qcResultChartInstance) {
                window.qcResultChartInstance.destroy();
            }

            const ctx = chartCanvas.getContext('2d');
            window.qcResultChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: 'Results', data: points, borderColor: '#1d4ed8', backgroundColor: '#1d4ed8', tension: 0.25, fill: false },
                        { label: 'Mean', data: Array(points.length).fill(mean), borderColor: '#15803d', borderDash: [5, 5], fill: false, pointRadius: 0 },
                        { label: 'Median', data: Array(points.length).fill(median), borderColor: '#7c3aed', borderDash: [3, 3], fill: false, pointRadius: 0 },
                        { label: 'Upper +1SD', data: Array(points.length).fill(innerUpper), borderColor: '#d97706', borderDash: [5, 5], fill: false, pointRadius: 0 },
                        { label: 'Upper +2SD', data: Array(points.length).fill(outerUpper), borderColor: '#dc2626', borderDash: [5, 5], fill: false, pointRadius: 0 },
                        { label: 'Lower -1SD', data: Array(points.length).fill(innerLower), borderColor: '#d97706', borderDash: [5, 5], fill: false, pointRadius: 0 },
                        { label: 'Lower -2SD', data: Array(points.length).fill(outerLower), borderColor: '#dc2626', borderDash: [5, 5], fill: false, pointRadius: 0 }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: { title: { display: true, text: 'Measurement' } },
                        x: { title: { display: true, text: 'Sample' } }
                    }
                }
            });
        })();
    </script>
</div>
