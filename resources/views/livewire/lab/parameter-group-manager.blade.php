<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-folder-outline text-primary"></i>
                                Parameter Groups
                            </h2>
                            <p class="text-muted mb-0">Configure groups used to organize analysis parameters (e.g. Contaminants, Nutritional)</p>
                        </div>
                        <button wire:click="showCreateModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Group
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-control" style="border-radius: 10px;">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($groups->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                Showing {{ $groups->firstItem() ?? 0 }} to {{ $groups->lastItem() ?? 0 }} of {{ $groups->total() }} entries
                            </span>
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Sort</th>
                                        <th>Name</th>
                                        <th>Parameters</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groups as $group)
                                        <tr>
                                            <td>{{ $group->sort_order }}</td>
                                            <td><strong>{{ $group->name }}</strong></td>
                                            <td>
                                                <span class="badge bg-info text-white">{{ $group->analysis_elements_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge p-2 bg-{{ $group->active ? 'success' : 'danger' }}">
                                                    {{ $group->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button wire:click="showEditModal('{{ $group->id }}')" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                @if($group->analysis_elements_count === 0)
                                                    <button
                                                        wire:click="delete('{{ $group->id }}')"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        onclick="return confirm('Delete this parameter group?')"
                                                    >
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="In use — deactivate instead">
                                                        <i class="mdi mdi-delete-off"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $groups->links() }}
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-folder-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No parameter groups found</h5>
                            <p class="text-muted">Add your first group to organize analysis parameters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingId ? 'pencil' : 'plus' }}"></i>
                            {{ $editingId ? 'Edit' : 'Create' }} Parameter Group
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="form-group mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" autofocus>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Sort order</label>
                                <input type="number" wire:model="sortOrder" class="form-control @error('sortOrder') is-invalid @enderror" min="0">
                                @error('sortOrder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="active" class="form-check-input" id="parameter_group_active" role="switch">
                                    <label class="form-check-label" for="parameter_group_active">Active</label>
                                </div>
                                @if($editingId)
                                    <small class="text-muted">Inactive groups are hidden from bulk-assign dropdowns but keep existing assignments.</small>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
