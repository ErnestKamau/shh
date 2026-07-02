<div class="qc-page">
    @include('livewire.qc._shared-styles')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-cog-outline mr-1"></i> QC Processing Queue</h5>
            <small class="text-muted">Pending QC records waiting for robust statistics processing.</small>
        </div>
        <button type="button" class="btn btn-sm btn-primary" wire:click="processResults">Process Unprocessed Results</button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <div class="card qc-table-card">
        <div class="card-body">
            <div class="table-responsive qc-table-wrap">
                <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Receipt Date</th>
                        <th>Batch Code</th>
                        <th>Sample Code</th>
                        <th>Analyte</th>
                        <th>Sample Type</th>
                        <th>Result</th>
                        <th>Previous</th>
                        <th>+- %</th>
                        <th>Standard</th>
                        <th>Remark</th>
                        <th>Analyst</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $row)
                        <tr>
                            <td>{{ $row->receipt_date }}</td>
                            <td>{{ $row->batch_code }}</td>
                            <td>{{ $row->sample_detail_code }}</td>
                            <td>{{ $row->analyte_code }}</td>
                            <td>{{ $row->sample_type_name }}</td>
                            <td>{{ $row->result }}</td>
                            <td>{{ $row->previous_result }}</td>
                            <td>{{ $row->config_percentage }}</td>
                            <td>{{ $row->main_value }}</td>
                            <td>{{ $row->remarks }}</td>
                            <td>{{ $row->analyst_name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted">No unprocessed QC results found.</td></tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($results, 'links'))
            <div class="card-footer">{{ $results->links() }}</div>
        @endif
    </div>
</div>
