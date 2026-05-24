{{-- Refactored SCC Enclosed Table --}}
<div class="pivot-card">
    <div class="pivot-header d-flex justify-content-between align-items-center">
        <span>Sample Control Performance (SCC)</span>
        <small class="font-weight-normal opacity-75">Ingest, Queue & Batch Tracking</small>
    </div>
    <div class="table-responsive">
        <table class="pivot-table">
            <thead>
                <tr>
                    <th style="min-width:180px;">Metric Description</th>
                    <th class="text-right" style="width:100px;">Value</th>
                    <th class="text-right" style="width:120px;">SLA Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="font-weight-bold">Number of Samples</td>
                    <td class="text-right font-weight-bold text-primary">{{ number_format($stats['testing_metrics']['scc_total_samples'] ?? 0) }}</td>
                    <td class="text-right">
                        <span class="badge badge-info">Total Active</span>
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Average SCC TAT</td>
                    <td class="text-right font-weight-bold">{{ $stats['testing_metrics']['scc_avg_tat'] ?? 0 }} d</td>
                    <td class="text-right">
                        <span class="badge badge-success">&lt; 1.0 d SLA</span>
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold">% TAT Compliance SCC</td>
                    @php $sccRate = $stats['testing_metrics']['scc_compliance'] ?? 0; @endphp
                    <td class="text-right font-weight-bold text-{{ $sccRate >= 80 ? 'success' : ($sccRate >= 50 ? 'warning' : 'danger') }}">{{ $sccRate }}%</td>
                    <td class="text-right">
                        <span class="badge badge-{{ $sccRate >= 80 ? 'success' : ($sccRate >= 50 ? 'warning' : 'danger') }}">
                            {{ $sccRate >= 80 ? 'Compliant' : 'Warning' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Active Batches</td>
                    <td class="text-right font-weight-bold text-dark">{{ number_format($stats['summary']['active_batches'] ?? 0) }}</td>
                    <td class="text-right">
                        <span class="badge badge-secondary">In Progress</span>
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Tests Requested</td>
                    <td class="text-right font-weight-bold text-dark">{{ number_format($stats['summary']['tests_requested'] ?? 0) }}</td>
                    <td class="text-right">
                        <span class="badge badge-primary">Ingested</span>
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Tests Completed</td>
                    <td class="text-right font-weight-bold text-success">{{ number_format($stats['summary']['tests_completed'] ?? 0) }}</td>
                    <td class="text-right">
                        <span class="badge badge-success">Completed</span>
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Tests Pending</td>
                    <td class="text-right font-weight-bold text-warning">{{ number_format($stats['summary']['tests_pending'] ?? 0) }}</td>
                    <td class="text-right">
                        <span class="badge badge-warning">Queue</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
