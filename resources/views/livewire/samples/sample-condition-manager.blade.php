<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-thermometer text-primary"></i>
                                Sample Condition Manager
                            </h2>
                            <p class="text-muted mb-0">Manage sample conditions</p>
                        </div>
                        <button wire:click="showCreateModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Condition
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
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Sample Type</label>
                                <x-searchable-select
                                    wire:model.live="sampleTypeFilter"
                                    :options="collect($sampleTypes)->map(fn($sampleType) => ['id' => $sampleType->id, 'name' => $sampleType->name])"
                                    placeholder="Search sample types..."
                                    empty-label="All Sample Types"
                                />
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

    <!-- Conditions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($conditions->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $conditions->firstItem() ?? 0 }} to {{ $conditions->lastItem() ?? 0 }} of {{ $conditions->total() }} entries
                                </span>
                            </div>
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
                                        <th>Name</th>
                                        <th>Sample Type</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conditions as $condition)
                                        <tr>
                                            <td>{{ $condition->name }}</td>
                                            <td>{{ $condition->sample_type?->name ?? '—' }}</td>
                                            <td>
                                                @if($condition->active)
                                                    <span class="badge badge-success p-2">Active</span>
                                                @else
                                                    <span class="badge badge-secondary p-2">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($condition->created_at)->format('Y-m-d') ?? '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditModal(@js($condition->id))" 
                                                            class="btn btn-sm mr-2 btn-outline-warning" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="delete(@js($condition->id))" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this condition?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $conditions->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-thermometer text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No conditions found</h5>
                            <p class="text-muted">Start by adding your first condition.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingId ? 'pencil' : 'plus' }}"></i>
                            {{ $editingId ? 'Edit' : 'Create' }} Condition
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="form-group mb-3">
                                <label class="form-label">Sample Type <span class="text-danger">*</span></label>
                                <x-searchable-select
                                    wire:model="sampleTypeId"
                                    :options="collect($sampleTypes)->map(fn($sampleType) => ['id' => $sampleType->id, 'name' => $sampleType->name])"
                                    placeholder="Search sample types..."
                                    empty-label="Select sample type..."
                                />
                                @error('sampleTypeId') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control" placeholder="Enter condition name">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="active" class="form-check-input" id="activeCheck">
                                    <label class="form-check-label" for="activeCheck">Active</label>
                                </div>
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
