<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="mdi mdi-sitemap"></i> Company Units
            </h6>
            <button wire:click="showCreateUnitModal" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus"></i> Add Unit
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Message Alert -->
        @if($message)
            <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show m-3" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" wire:click="dismissMessage"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $unit->name }}</span>
                            </td>
                            <td>
                                @if($unit->active == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button wire:click="showEditUnitModal({{ $unit->id }})" 
                                            class="btn btn-outline-warning btn-sm" 
                                            title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button wire:click="deleteUnit({{ $unit->id }})" 
                                            class="btn btn-outline-danger btn-sm" 
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this unit?')">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="mdi mdi-information-outline fs-1"></i>
                                    <p class="mt-2">No company units found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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

