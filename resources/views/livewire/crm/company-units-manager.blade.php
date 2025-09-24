<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-sitemap text-primary"></i>
                                Company Units Management
                            </h2>
                            <p class="text-muted mb-0">Manage company units for: <strong>{{ $customer->name }}</strong></p>
                        </div>
                        <button wire:click="showCreateUnitModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Unit
                        </button>
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

    <!-- Company Units Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->units->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->units as $unit)
                                        <tr>
                                            <td>
                                                <strong>{{ $unit->name }}</strong>
                                            </td>
                                            <td>
                                                @if($unit->active == 1)
                                                    <span class="badge bg-success p-2">Active</span>
                                                @else
                                                    <span class="badge bg-danger p-2">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditUnitModal({{ $unit->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteUnit({{ $unit->id }})" 
                                                            class="btn btn-sm btn-outline-danger mr-1" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this unit?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-sitemap text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No company units found</h5>
                            <p class="text-muted">Start by adding your first company unit.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Unit Modal -->
    @if($showUnitModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingUnit ? 'pencil' : 'plus' }}"></i>
                        {{ $editingUnit ? 'Edit' : 'Create' }} Company Unit
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeUnitModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveUnit">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="unitForm.name" class="form-control" placeholder="Unit name...">
                            @error('unitForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" wire:model="unitForm.active" class="form-check-input" id="unitActive">
                            <label class="form-check-label" for="unitActive">
                                Is Active?
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeUnitModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveUnit">
                        <i class="mdi mdi-content-save"></i> {{ $editingUnit ? 'Update' : 'Create' }} Unit
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

