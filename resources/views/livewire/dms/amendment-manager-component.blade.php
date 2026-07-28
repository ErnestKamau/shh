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
                                            <td>{{ $amendment->requestedBy->name ?? 'N/A' }}</td>
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
</div>
