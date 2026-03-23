<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-sync"></i> Processed results</h5>
            <div class="d-flex align-items-center gap-2">
                <label for="perPage" class="form-label mb-0 me-2 text-muted small">Show</label>
                <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm" style="width: auto; min-width: 4.5rem; border-radius: 6px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" 
                       wire:model.live="search" 
                       class="form-control" 
                       placeholder="Search by sample code, analysis type, result, or remarks...">
            </div>

            @if($processedResults->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover workflow-table">
                        <thead>
                            <tr>
                                <th>Sample Code</th>
                                <th>Analysis Type</th>
                                <th>Analyte</th>
                                <th>Result</th>
                                <th>Analyst</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processedResults as $result)
                            <tr>
                                <td>{{ $result->captured->sample->sample_code ?? 'N/A' }}</td>
                                <td>{{ $result->captured->analysis_type->name ?? 'N/A' }}</td>
                                <td>{{ $result->analyte_code ?? 'N/A' }}</td>
                                <td><strong>{{ $result->result }}</strong></td>
                                <td>{{ $result->captured->operator->name ?? 'N/A' }}</td>
                                <td><small>{{ $result->remarks ?? '-' }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span class="text-muted">
                            Showing {{ $processedResults->firstItem() ?? 0 }} to {{ $processedResults->lastItem() ?? 0 }} of {{ $processedResults->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $processedResults->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover workflow-table">
                        <thead>
                            <tr>
                                <th>Sample Code</th>
                                <th>Analysis Type</th>
                                <th>Analyte</th>
                                <th>Result</th>
                                <th>Analyst</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-5 workflow-empty-state">
                                    <i class="mdi mdi-check-circle-outline text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Processed Results Found</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No results match your search criteria
                                        @else
                                            There are no processed results to display
                                        @endif
                                    </small></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
