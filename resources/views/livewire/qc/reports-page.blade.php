<div class="qc-page">
    @include('livewire.qc._shared-styles')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-chart-line mr-1"></i> QC Statistical Reports</h5>
            <small class="text-muted">Processed QC result groups with robust statistics.</small>
        </div>
    </div>

    <div class="card qc-table-card">
        <div class="card-body">
            <div class="table-responsive qc-table-wrap">
                <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Graph</th>
                        <th>Sample Type</th>
                        <th>Analysis Type</th>
                        <th>Method</th>
                        <th>Analyte</th>
                        <th>Results</th>
                        <th>Mean</th>
                        <th>Median</th>
                        <th>SD</th>
                        <th>CV</th>
                        <th>% CV</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $row)
                        <tr>
                            <td><a href="{{ route('qc-result-show', ['result_id' => $row->id]) }}" class="btn btn-sm btn-light">View</a></td>
                            <td>{{ $row->sampletype->name ?? '-' }}</td>
                            <td>{{ $row->analysistype->name ?? '-' }}</td>
                            <td>{{ $row->method->name ?? '-' }}</td>
                            <td>{{ $row->analyte->code ?? '-' }}</td>
                            <td>{{ $row->results->count() }}</td>
                            <td>{{ $row->robust_mean }}</td>
                            <td>{{ $row->robust_median }}</td>
                            <td>{{ number_format((float) $row->robust_standard_deviation, 4) }}</td>
                            <td>{{ number_format((float) $row->robust_cv, 4) }}</td>
                            <td>{{ number_format((float) $row->robust_cv_percentage, 4) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted">No processed QC reports found.</td></tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $results->links() }}</div>
    </div>
</div>
