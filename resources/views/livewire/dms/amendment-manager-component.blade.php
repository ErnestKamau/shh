<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-pencil-box-multiple text-primary"></i>
                                Amendment Management
                            </h2>
                            <p class="text-muted mb-0">Track and manage document amendments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search amendments...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="requested">Requested</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Per Page</label>
                                <select wire:model.live="perPage" class="form-select">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Amendments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($amendments->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $amendments->firstItem() ?? 0 }} to {{ $amendments->lastItem() ?? 0 }} of {{ $amendments->total() }} entries
                                </span>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover workflow-table livewire-table ls-table">
                                <thead>
                                    <tr>
                                        <th>Amendment #</th>
                                        <th>Document</th>
                                        <th>Requested By</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($amendments as $amendment)
                                        <tr>
                                            <td><strong>{{ $amendment->amendment_number }}</strong></td>
                                            <td>
                                                <div>
                                                    <strong>{{ $amendment->document->title ?? 'N/A' }}</strong>
                                                    <br><small class="text-muted">{{ $amendment->document->document_number ?? '' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $amendment->requester->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $amendment->status_class }}">
                                                    {{ ucfirst($amendment->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $amendment->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="dms-actions-group">
                                                    <button type="button"
                                                            wire:click="viewAmendment('{{ $amendment->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                            title="View">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $amendments->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-pencil-box-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No amendments found</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showWorkflowModal && $currentAmendment)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-eye"></i>
                            Amendment #{{ $currentAmendment->amendment_number }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeWorkflowModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted mb-1">Document</label>
                                <div class="fw-bold">{{ $currentAmendment->document->title ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $currentAmendment->document->document_number ?? '' }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted mb-1">Status</label>
                                <div>
                                    <span class="badge badge-{{ $currentAmendment->status_class ?? 'secondary' }}">
                                        {{ ucfirst($currentAmendment->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted mb-1">Requested By</label>
                                <div>{{ $currentAmendment->requester->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted mb-1">Requested At</label>
                                <div>{{ optional($currentAmendment->requested_at)->format('M d, Y H:i') ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Reason</label>
                            <div>{{ $currentAmendment->amendment_reason ?: 'N/A' }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Description</label>
                            <div>{{ $currentAmendment->amendment_description ?: 'N/A' }}</div>
                        </div>

                        @if($currentAmendment->authorizer)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted mb-1">Authorized By</label>
                                    <div>{{ $currentAmendment->authorizer->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted mb-1">Authorization Comment</label>
                                    <div>{{ $currentAmendment->authorization_comment ?: 'N/A' }}</div>
                                </div>
                            </div>
                        @endif

                        @if($currentAmendment->approver)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted mb-1">Approved By</label>
                                    <div>{{ $currentAmendment->approver->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted mb-1">Approval Comment</label>
                                    <div>{{ $currentAmendment->approval_comment ?: 'N/A' }}</div>
                                </div>
                            </div>
                        @endif

                        @if(in_array($currentAmendment->status, ['requested', 'authorized', 'amended'], true))
                            <hr>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Comment</label>
                                <textarea wire:model="workflowComment" class="form-control" rows="3" placeholder="Optional comment..."></textarea>
                            </div>
                        @endif

                        @if($currentAmendment->status === 'requested' && $currentAmendment->canAuthorize(auth()->user()))
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" wire:click="authorizeAmendment(true)">
                                    <i class="mdi mdi-check"></i> Authorize
                                </button>
                                <button type="button" class="btn btn-danger" wire:click="authorizeAmendment(false)">
                                    <i class="mdi mdi-close"></i> Reject
                                </button>
                            </div>
                        @elseif($currentAmendment->status === 'authorized')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload Amended File</label>
                                <input type="file" wire:model="file" class="form-control">
                                @error('file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <button type="button" class="btn btn-primary" wire:click="uploadAmendedFile" wire:loading.attr="disabled">
                                <i class="mdi mdi-upload"></i> Upload Amended File
                            </button>
                        @elseif($currentAmendment->status === 'amended' && $currentAmendment->canApprove(auth()->user()))
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" wire:click="approveAmendment(true)">
                                    <i class="mdi mdi-check"></i> Approve
                                </button>
                                <button type="button" class="btn btn-danger" wire:click="approveAmendment(false)">
                                    <i class="mdi mdi-close"></i> Reject
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeWorkflowModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
