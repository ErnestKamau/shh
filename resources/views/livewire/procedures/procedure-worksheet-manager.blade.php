<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-clipboard-text text-primary"></i>
                                Procedure Capture Worksheets
                            </h2>
                            <p class="text-muted mb-0">Create and manage procedure worksheets and steps</p>
                        </div>
                        <button wire:click="create" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Create Procedure
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Content -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="col-md-4">
                            <input type="text" wire:model.live="search" class="form-control" placeholder="Search procedures...">
                        </div>
                        <div class="d-flex align-items-center">
                            <label class="form-label mb-0 me-2 text-muted">Show:</label>
                            <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="75">75</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($worksheets as $worksheet)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $worksheet->name }}</strong></td>
                                        <td>{{ Str::limit($worksheet->description, 50) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $worksheet->is_active ? 'success' : 'secondary' }}">
                                                {{ $worksheet->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $worksheet->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button wire:click="edit({{ $worksheet->id }})" class="btn btn-sm btn-outline-primary mr-2" title="Edit Details">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <a href="{{ route('formulars.procedures.edit', $worksheet->id) }}" class="btn btn-sm btn-outline-success mr-2" title="Manage Steps">
                                                    <i class="mdi mdi-format-list-numbered"></i>
                                                </a>
                                                <button wire:click="toggleActive({{ $worksheet->id }})" class="btn btn-sm btn-outline-{{ $worksheet->is_active ? 'warning' : 'success' }} mr-2" title="{{ $worksheet->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="mdi mdi-{{ $worksheet->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                <button wire:click="delete({{ $worksheet->id }})" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No procedure worksheets found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <span class="text-muted">
                                Showing {{ $worksheets->firstItem() ?? 0 }} to {{ $worksheets->lastItem() ?? 0 }} of {{ $worksheets->total() }} entries
                            </span>
                        </div>
                        <div>
                            {{ $worksheets->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showCreateModal || $showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $showEditModal ? 'Edit' : 'Create' }} Procedure Worksheet</h5>
                        <button type="button" class="btn-close" wire:click="resetForm; $set('showCreateModal', false); $set('showEditModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="{{ $showEditModal ? 'update' : 'save' }}">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control" rows="3"></textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="is_active" class="form-check-input" id="isActive">
                                    <label class="form-check-label" for="isActive">Active</label>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary me-2" wire:click="resetForm; $set('showCreateModal', false); $set('showEditModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Create' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
