<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-flask text-primary"></i>
                                Labs Management
                            </h2>
                            <p class="text-muted mb-0">Manage laboratory facilities and their configurations</p>
                        </div>
                        @can('laboratory.components.labs.edit')
                            <button wire:click="showCreateLabModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Lab
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($message))
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

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
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search labs by name, code, or email...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Labs</h5>
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($this->labs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover lab-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Actions</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Start Sample</th>
                                        <th>Email</th>
                                        <th>Phone 1</th>
                                        <th>Internal Lab</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->labs as $lab)
                                        <tr wire:key="lab-row-{{ $lab->id }}">
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @can('laboratory.components.labs.edit')
                                                        <button wire:click="showEditLabModal('{{ $lab->id }}')"
                                                                class="rm-act-btn rm-act-btn--edit"
                                                                title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="confirmDeleteLab('{{ $lab->id }}')"
                                                                class="rm-act-btn rm-act-btn--delete"
                                                                title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                            <td>{{ $lab->code }}</td>
                                            <td>
                                                <strong>{{ $lab->name }}</strong>
                                                @if($lab->location)
                                                    <br><small class="text-muted"><i class="mdi mdi-map-marker"></i> {{ $lab->location }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $lab->start_sample_no ?? '-' }}</td>
                                            <td>{{ $lab->email }}</td>
                                            <td>{{ $lab->phone1 ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($lab->is_external == 0)
                                                    <span class="lab-badge lab-badge--internal">
                                                        <i class="mdi mdi-check-circle"></i> Internal
                                                    </span>
                                                @else
                                                    <span class="lab-badge lab-badge--external">
                                                        <i class="mdi mdi-earth"></i> External
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="lab-badge {{ $lab->active ? 'lab-badge--active' : 'lab-badge--inactive' }}">
                                                    {{ $lab->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted">
                                Showing {{ $this->labs->firstItem() ?? 0 }} to {{ $this->labs->lastItem() ?? 0 }} of {{ $this->labs->total() }} entries
                            </span>
                            <div>
                                {{ $this->labs->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No labs found</h5>
                            <p class="text-muted">Create your first lab to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showLabModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingLab ? 'pencil' : 'plus' }}"></i>
                            {{ $editingLab ? 'Edit' : 'Create' }} Lab
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeLabModal"></button>
                    </div>
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                                </h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form wire:submit.prevent="saveLab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="labForm.name" class="form-control @error('labForm.name') is-invalid @enderror" placeholder="Lab Name...">
                                        @error('labForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="labForm.code" class="form-control @error('labForm.code') is-invalid @enderror" placeholder="Lab Code...">
                                        @error('labForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Start Sample No <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="labForm.start_sample_no" class="form-control @error('labForm.start_sample_no') is-invalid @enderror" placeholder="Start Sample No...">
                                        @error('labForm.start_sample_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Postal Address <span class="text-danger">*</span></label>
                                        <textarea wire:model="labForm.address" class="form-control @error('labForm.address') is-invalid @enderror" rows="3" placeholder="Lab Postal Address..."></textarea>
                                        @error('labForm.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Location <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="labForm.location" class="form-control @error('labForm.location') is-invalid @enderror" placeholder="Lab Location...">
                                        @error('labForm.location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Website</label>
                                        <input type="text" wire:model="labForm.website" class="form-control" placeholder="Lab Website...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Fax</label>
                                        <input type="text" wire:model="labForm.fax" class="form-control" placeholder="Lab Fax...">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Email <span class="text-danger">*</span></label>
                                        <input type="email" wire:model="labForm.email" class="form-control @error('labForm.email') is-invalid @enderror" placeholder="Lab Email...">
                                        @error('labForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Phone 1 <span class="text-danger">*</span></label>
                                        <input type="tel" wire:model="labForm.phone1" class="form-control @error('labForm.phone1') is-invalid @enderror" placeholder="Lab Phone 1...">
                                        @error('labForm.phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Phone 2</label>
                                        <input type="tel" wire:model="labForm.phone2" class="form-control" placeholder="Lab Phone 2...">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Phone 3</label>
                                        <input type="tel" wire:model="labForm.phone3" class="form-control" placeholder="Lab Phone 3...">
                                    </div>
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="labForm.is_external" class="form-check-input" id="is_external">
                                            <label class="form-check-label" for="is_external">Is an External Lab?</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="labForm.active" class="form-check-input" id="active">
                                            <label class="form-check-label" for="active">Is Active?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeLabModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveLab">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-alert-circle"></i>
                            Confirm Delete
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i>
                            <strong>Warning:</strong> This action cannot be undone.
                        </div>
                        <p class="mb-3">Are you sure you want to delete the following <strong>Lab</strong>?</p>
                        <div class="card">
                            <div class="card-body bg-light">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th style="width: 35%;">Name:</th>
                                        <td><strong>{{ $deleteDetails['name'] ?? '-' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Code:</th>
                                        <td>{{ $deleteDetails['code'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $deleteDetails['email'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>{{ $deleteDetails['active'] ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteLab">
                            <i class="mdi mdi-delete"></i> Yes, Delete Permanently
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .modal.show {
        display: block !important;
    }

    .modern-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: left center, right 12px center;
        background-repeat: no-repeat, no-repeat;
        background-size: 100% 100%, 16px 16px;
        padding-right: 40px;
    }

    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }

    .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 3px;
        font-size: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .rm-act-btn--edit:hover {
        background: #dbeafe;
    }

    .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #991b1b;
        background: #fee2e2;
    }

    .rm-act-btn--delete:hover {
        background: #fecaca;
    }

    .lab-table {
        min-width: 900px;
    }

    .lab-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border: 1px solid transparent;
        line-height: 1;
        white-space: nowrap;
    }

    .lab-badge--internal {
        color: #166534;
        background: #dcfce7;
        border-color: #86efac;
    }

    .lab-badge--external {
        color: #0f766e;
        background: #ccfbf1;
        border-color: #99f6e4;
    }

    .lab-badge--active {
        color: #14532d;
        background: #dcfce7;
        border-color: #86efac;
    }

    .lab-badge--inactive {
        color: #991b1b;
        background: #fee2e2;
        border-color: #fca5a5;
    }
    </style>
</div>
