<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-comment-alert text-primary"></i>
                                Complaint Management
                            </h2>
                            <p class="text-muted mb-0">Manage customer complaints, feedback, and resolutions</p>
                        </div>
                        <button wire:click="openCreateModal" class="btn btn-sm btn-primary">
                            <i class="mdi mdi-plus"></i> Add Complaint
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search ID, Description, Customer...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Stage</label>
                                <select wire:model.live="stageFilter" class="form-select modern-select">
                                    <option value="">All Complaints</option>
                                    @foreach($this->workflowValues as $name => $value)
                                        @if($value != 0) {{-- Exclude "All Complaints" from dropdown as it's the default option --}}
                                        <option value="{{ $value }}">{{ $name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">From Date</label>
                                <input type="date" wire:model.live="dateFrom" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">To Date</label>
                                <input type="date" wire:model.live="dateTo" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaints Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Priority</th>
                                    <th>Description</th>
                                    <th>Received From</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($complaints as $complaint)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $complaint->complaint_id }}</span>
                                        </td>
                                        <td>
                                            @if($complaint->priority == 'High')
                                                <span class="badge bg-danger">High</span>
                                            @elseif($complaint->priority == 'Medium')
                                                <span class="badge bg-warning text-dark">Medium</span>
                                            @else
                                                <span class="badge bg-info">Normal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span title="{{ $complaint->description }}">
                                                {{ Str::limit($complaint->description, 50) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $complaint->received_from }}</div>
                                            @if($complaint->client_id)
                                                <small class="text-muted"><i class="mdi mdi-account-check"></i> Linked</small>
                                            @endif
                                        </td>
                                        <td>{{ $complaint->type }}</td>
                                        <td>{{ $complaint->date }}</td>
                                        <td>
                                            @php
                                                $statusLabel = array_search($complaint->complaint_workflow, $this->workflowValues) ?: 'Unknown';
                                            @endphp
                                            @if($complaint->rejected)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-success">{{ $statusLabel }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button wire:click="openViewModal({{ $complaint->id }})" class="btn btn-sm btn-outline-info" title="View Details">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                <button wire:click="openCreateModal({{ $complaint->id }})" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                
                                                @if(!$complaint->rejected)
                                                    <button wire:click="openActionModal({{ $complaint->id }}, 'approve')" class="btn btn-sm btn-outline-success" title="Approve/Next">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                    <button wire:click="openActionModal({{ $complaint->id }}, 'reverse')" class="btn btn-sm btn-outline-warning" title="Reverse/Back">
                                                        <i class="mdi mdi-undo"></i>
                                                    </button>
                                                    <button wire:click="openActionModal({{ $complaint->id }}, 'reject')" class="btn btn-sm btn-outline-danger" title="Reject">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="mdi mdi-comment-alert-outline text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No complaints found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $complaints->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showCreateModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $complaintId ? 'Edit' : 'Create' }} Complaint</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveComplaint">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Received From <span class="text-danger">*</span></label>
                                <input type="text" wire:model="complaintForm.received_from" class="form-control" list="customerList">
                                <datalist id="customerList">
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->name }}">
                                    @endforeach
                                </datalist>
                                @error('complaintForm.received_from') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="complaintForm.date" class="form-control">
                                @error('complaintForm.date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select wire:model="complaintForm.type" class="form-select">
                                    <option value="">Select Type</option>
                                    @foreach($complaintTypes as $type)
                                        <option value="{{ $type->name }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('complaintForm.type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select wire:model="complaintForm.priority" class="form-select">
                                    <option value="Normal">Normal</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                                @error('complaintForm.priority') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea wire:model="complaintForm.description" class="form-control" rows="4"></textarea>
                            @error('complaintForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveComplaint">Save</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Modal -->
    @if($showActionModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-{{ $actionType == 'reject' ? 'danger' : ($actionType == 'approve' ? 'success' : 'warning') }} text-white">
                    <h5 class="modal-title">
                        {{ ucfirst($actionType) }} Complaint {{ $selectedComplaintForAction?->complaint_id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to <strong>{{ $actionType }}</strong> this complaint?</p>
                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                        <textarea wire:model="actionComment" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-{{ $actionType == 'reject' ? 'danger' : ($actionType == 'approve' ? 'success' : 'warning') }}" wire:click="performAction">
                        Confirm {{ ucfirst($actionType) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal && $viewComplaint)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details: {{ $viewComplaint->complaint_id }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Status:</strong> 
                                    @if($viewComplaint->rejected)
                                        <span class="text-danger">Rejected</span>
                                    @else
                                        {{ array_search($viewComplaint->complaint_workflow, $this->workflowValues) }}
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <strong>Date:</strong> {{ $viewComplaint->date }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Type:</strong> {{ $viewComplaint->type }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Priority:</strong> {{ $viewComplaint->priority }}
                                </div>
                                <div class="col-12 mt-2">
                                    <strong>Description:</strong>
                                    <p class="text-muted bg-light p-2 rounded">{{ $viewComplaint->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-4">Chain of Custody (History)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>User</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($viewComplaint->chainOfCustody as $chain)
                                    <tr>
                                        <td>{{ $chain->created_at }}</td>
                                        <td>{{ $chain->action }}</td>
                                        <td>{{ $chain->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $chain->comments }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .modern-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
</div>
