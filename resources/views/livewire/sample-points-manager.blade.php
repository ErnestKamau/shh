<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="mdi mdi-map-marker"></i> Sample Points
            </h6>
            <button wire:click="showCreatePointModal" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus"></i> Add Sample Point
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
                        <th>Unit</th>
                        <th>GPS</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($samplePoints as $point)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $point->name }}</span>
                            </td>
                            <td>{{ $point->unit->name ?? 'N/A' }}</td>
                            <td>
                                @if($point->gps)
                                    <small class="text-muted">{{ $point->gps }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($point->active == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button wire:click="showEditPointModal({{ $point->id }})" 
                                            class="btn btn-outline-warning btn-sm" 
                                            title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button wire:click="deletePoint({{ $point->id }})" 
                                            class="btn btn-outline-danger btn-sm" 
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this sample point?')">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="mdi mdi-information-outline fs-1"></i>
                                    <p class="mt-2">No sample points found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sample Point Modal -->
@if($showPointModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingPoint ? 'pencil' : 'plus' }}"></i>
                        {{ $editingPoint ? 'Edit' : 'Create' }} Sample Point
                    </h5>
                    <button type="button" class="btn-close" wire:click="closePointModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="savePoint">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="pointForm.name" class="form-control" placeholder="Sample point name...">
                                    @error('pointForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Unit <span class="text-danger">*</span></label>
                                    <select wire:model="pointForm.crm_company_unit_id" class="form-select">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('pointForm.crm_company_unit_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">GPS Coordinates</label>
                            <input type="text" wire:model="pointForm.gps" class="form-control" placeholder="GPS coordinates...">
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" wire:model="pointForm.active" class="form-check-input" id="pointActive">
                            <label class="form-check-label" for="pointActive">
                                Is Active?
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closePointModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="savePoint">
                        <i class="mdi mdi-content-save"></i> {{ $editingPoint ? 'Update' : 'Create' }} Sample Point
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

