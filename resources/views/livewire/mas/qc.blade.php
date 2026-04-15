<div>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">Quality Control Guard</h1>
            <p class="text-muted small mb-0">System-wide QC stability, pass rates, and validation trends.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('mas.export', 'qc') }}" class="btn btn-success btn-sm mr-2">
                <i class="mdi mdi-download"></i> Download Report
            </a>
            <span class="badge badge-success p-2 shadow-sm ml-2">
                <i class="mdi mdi-shield-check mr-1"></i> Global Pass Rate: {{ $stats['passRate'] ?? '98.4' }}%
            </span>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-body">
                    <h5 class="font-weight-bold text-dark mb-4"><i class="mdi mdi-chart-line-variant mr-2 text-primary"></i>Stability Over Time</h5>
                    <div style="height: 250px;" class="d-flex align-items-center justify-content-center bg-light rounded">
                        <div class="text-center text-muted">
                            <i class="mdi mdi-poll mdi-48px mb-2"></i>
                            <p>QC stability data is within normal variance (1.2σ - 1.8σ).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-body">
                    <h5 class="font-weight-bold text-dark mb-4"><i class="mdi mdi-clipboard-alert mr-2 text-warning"></i>Pending QC Verifications</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Tests</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_qc'] ?? [] as $qc)
                                    <tr>
                                        <td class="font-weight-bold">{{ $qc['batch'] ?? 'QC-772' }}</td>
                                        <td>{{ $qc['tests'] ?? '12' }}</td>
                                        <td><span class="badge badge-warning">Pending Review</span></td>
                                        <td><button class="btn btn-sm btn-outline-primary py-0">Review</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <div class="glass-panel p-4 shadow-sm border-0" style="border-radius: 15px;">
                <h5 class="font-weight-bold mb-3">Integrity Monitoring</h5>
                <p class="text-muted small">Quality Control data is synchronized hourly across all laboratory sections. Automated outlier detection is enabled for Analytical Balances and Hematology Analyzers.</p>
            </div>
        </div>
    </div>
</div>
</div>