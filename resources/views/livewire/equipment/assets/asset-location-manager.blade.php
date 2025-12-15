<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-map-marker text-primary"></i>
                                Asset Locations
                            </h2>
                            <p class="text-muted mb-0">Manage physical locations for equipment assets</p>
                        </div>
                        <div>
                            <button wire:click="openModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add New Location
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by location code or name...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Locations List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>Location Code</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locations as $location)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $location->location_code }}</span>
                                        </td>
                                        <td>{{ $location->name }}</td>
                                        <td>
                                            @if($location->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button wire:click="edit({{ $location->id }})" class="btn btn-sm btn-outline-info me-1">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button wire:click="delete({{ $location->id }})" 
                                                    wire:confirm="Are you sure you want to delete this asset location?"
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="mdi mdi-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-map-marker text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">No Asset Locations Found</h5>
                                            <p class="text-muted">Get started by creating a new asset location.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $locations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editId ? 'pencil' : 'plus' }} text-primary"></i>
                            {{ $editId ? 'Edit Asset Location' : 'Create Asset Location' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Location Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('location_code') is-invalid @enderror" wire:model="location_code" placeholder="e.g. WH-01">
                                @error('location_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. Main Warehouse">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="isActive" wire:model="is_active">
                                    <label class="form-check-label" for="isActive">Active Status</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="mdi mdi-content-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
