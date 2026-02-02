<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-swap-horizontal-bold"></i> Inter Laboratory Logs</h5>
            <div class="d-flex align-items-center gap-2">
                <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" 
                       wire:model.live="search" 
                       class="form-control" 
                       placeholder="Search by sample code, lab, submitted by, or remarks...">
            </div>

            @if($interlabLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Status</th>
                                <th>Sample/Job No</th>
                                <th>From Lab</th>
                                <th>To Lab</th>
                                <th>Sample Type</th>
                                <th>Qty</th>
                                <th>Submitted By</th>
                                <th>Date Submitted</th>
                                <th>Received By</th>
                                <th>Date Received</th>
                                <th>Expected Date</th>
                                <th>Prelim Date</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($interlabLogs as $log)
                                <tr>
                                    <td>
                                        @if($log->status == 0)
                                            <span class="badge badge-primary">
                                                <i class="mdi mdi-alert-decagram-outline"></i> Awaiting Approval
                                            </span>
                                        @elseif($log->status == 1)
                                            <span class="badge badge-success">
                                                <i class="mdi mdi-thumb-up"></i> Approved
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="mdi mdi-alert-decagram-outline"></i> Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $log->sample_code ?? 'N/A' }}</strong></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $log->from_lab_section_id > 0 ? ($log->from_lab_name ?? 'Lab') : 'Reception' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $log->to_lab_code ?? 'N/A' }} - {{ $log->to_lab_name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $log->sample_type_name ?? '-' }}</td>
                                    <td>{{ $log->quantity ?? '0' }}</td>
                                    <td>{{ $log->submitted_by_name ?? 'N/A' }}</td>
                                    <td>
                                        <small>{{ $log->date_submitted ? \Carbon\Carbon::parse($log->date_submitted)->format('Y-m-d H:i') : 'N/A' }}</small>
                                    </td>
                                    <td>{{ $log->received_by_name ?? '-' }}</td>
                                    <td>
                                        <small>{{ $log->date_received ? \Carbon\Carbon::parse($log->date_received)->format('Y-m-d H:i') : '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $log->expected_date ? \Carbon\Carbon::parse($log->expected_date)->format('Y-m-d') : '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $log->prelim_date ? \Carbon\Carbon::parse($log->prelim_date)->format('Y-m-d') : '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $log->remarks ?? '-' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span class="text-muted">
                            Showing {{ $interlabLogs->firstItem() ?? 0 }} to {{ $interlabLogs->lastItem() ?? 0 }} of {{ $interlabLogs->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $interlabLogs->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Status</th>
                                <th>Sample/Job No</th>
                                <th>From Lab</th>
                                <th>To Lab</th>
                                <th>Sample Type</th>
                                <th>Qty</th>
                                <th>Submitted By</th>
                                <th>Date Submitted</th>
                                <th>Received By</th>
                                <th>Date Received</th>
                                <th>Expected Date</th>
                                <th>Prelim Date</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="13" class="text-center py-5">
                                    <i class="mdi mdi-swap-horizontal text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Interlab Logs Found</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No interlab logs match your search criteria
                                        @else
                                            There are no inter laboratory transfer logs to display
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
