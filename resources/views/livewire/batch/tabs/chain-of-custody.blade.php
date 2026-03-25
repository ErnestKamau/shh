<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-sitemap"></i> Chain of custody</h5>
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
                       placeholder="Search by workflow stage, tracking stage, user, or comments...">
            </div>

            @if($custodyRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>No.</th>
                                <th>Workflow Stage</th>
                                <th>Tracking Stage</th>
                                <th>Started By</th>
                                <th>Start Date</th>
                                <th>Completed By</th>
                                <th>Complete Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($custodyRecords as $custody)
                                <tr>
                                    <td class="text-nowrap">{{ ($custodyRecords->firstItem() ?? 0) + $loop->index }}</td>
                                    <td class="text-nowrap">{{ $custody->workflow_stage ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $custody->tracking_stage->name ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $custody->started_by->name ?? '-' }}</td>
                                    <td class="text-nowrap">
                                        {{ optional($custody->created_at)->format('Y-m-d H:i') ?? '-' }}
                                    </td>
                                    <td class="text-nowrap">
                                        {!! $custody->completed_by->name ?? '<i class="mdi mdi-timer-sand text-warning" style="font-size: 16px!important"></i>' !!}
                                    </td>
                                    <td class="text-nowrap">
                                        @if(!empty($custody->moved_out_date))
                                            {{ $custody->moved_out_date }}
                                        @else
                                            <i class="mdi mdi-timer-sand text-warning" style="font-size: 16px!important"></i>
                                        @endif
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
                            Showing {{ $custodyRecords->firstItem() ?? 0 }} to {{ $custodyRecords->lastItem() ?? 0 }} of {{ $custodyRecords->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $custodyRecords->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover workflow-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Workflow Stage</th>
                                <th>Tracking Stage</th>
                                <th>Started By</th>
                                <th>Start Date</th>
                                <th>Completed By</th>
                                <th>Complete Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center py-5 workflow-empty-state">
                                    <i class="mdi mdi-link-variant-off text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Chain of Custody Records</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No records match your search criteria
                                        @else
                                            There are no chain of custody records to display
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
