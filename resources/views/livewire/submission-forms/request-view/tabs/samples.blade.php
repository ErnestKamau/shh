<div class="workflow-board-panel-body flush-top px-0">
    @if($acceptanceForm)
        <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span class="text-muted small">
                Acceptance form status:
                <span class="badge badge-secondary">{{ str_replace('_', ' ', $acceptanceForm->status) }}</span>
            </span>
            <a href="{{ route('sample-workflow', ['status' => $boardStatus]) }}" class="btn btn-sm btn-outline-primary">
                <i class="mdi mdi-open-in-new"></i> Open on workflow board
            </a>
        </div>
    @endif

    @if(count($sampleLines) > 0)
        <div class="table-responsive">
            <table class="table table-hover workflow-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer sample ID</th>
                        <th>Sample type</th>
                        <th>Analysis type</th>
                        <th>Analysis element</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sampleLines as $line)
                        <tr>
                            <td>{{ $line['row_index'] + 1 }}</td>
                            <td>{{ $line['customer_sample_id'] ?? '—' }}</td>
                            <td>{{ $line['sample_type_name'] ?? $line['sample_type_id'] ?? '—' }}</td>
                            <td>{{ $line['analysis_type_name'] ?? $line['analysis_type_id'] ?? '—' }}</td>
                            <td>{{ $line['parameter_label'] ?? $line['analysis_element_id'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted mb-0 py-3">No sample rows captured on the portal form.</p>
    @endif
</div>
