<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-account-check-outline"></i> Approvers</h5>
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
                       placeholder="Search by approver, title, status, or remark...">
            </div>

            @if($approvers->count() > 0)
                <div class="table-responsive p-2">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>#</th>
                                <th>Status</th>
                                <th>Approval Date</th>
                                <th>Approver</th>
                                <th>Title</th>
                                <th>Workflow</th>
                                <th>Remark</th>
                                <th>Lab Sections</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvers as $approver)
                            <tr class="{{$approver->batch_status != $batch->status ? 'bg-light' : ''}}" >
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($approver->batch_status != $batch->status)
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="mdi mdi-checkbox-marked-circle"></i> Approved
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $approver->approval_date }}</td>
                                <td>{{ $approver->approvername }}</td>
                                <td>{{ $approver->title }}</td>
                                <td>{{ $approver->workflow }}</td>
                                <td>
                                    <small>{{ $approver->remark }}</small>
                                </td>
                                <td>
                                    @if($approver->lab_sections && count($approver->lab_sections) > 0)
                                        @foreach($approver->lab_sections as $section)
                                            <span class="badge badge-info"><i class="mdi mdi-flask"></i> {{ $section->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge badge-light">All Sections</span>
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
                            Showing {{ $approvers->firstItem() ?? 0 }} to {{ $approvers->lastItem() ?? 0 }} of {{ $approvers->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $approvers->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>#</th>
                                <th>Status</th>
                                <th>Approval Date</th>
                                <th>Approver</th>
                                <th>Title</th>
                                <th>Workflow</th>
                                <th>Remark</th>
                                <th>Lab Sections</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="mdi mdi-account-check-outline text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Approvers Found</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No approvers match your search criteria
                                        @else
                                            There are no approval records to display
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
