@if($showVariableModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 1050;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-light border-0 p-4">
                    <h5 class="modal-title font-weight-bold">
                        <i class="mdi mdi-plus-box-outline text-primary me-2"></i> Define New Variable
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeVariableModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="createCustomVariable">
                        <div class="mb-3">
                            <label class="form-label form-label--modern">Variable Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model.defer="newVariable.name" class="form-control form-control--modern" placeholder="e.g. Ambient Humidity Correction" required>
                            @error('newVariable.name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-label--modern">Variable Slug <span class="text-danger">*</span></label>
                            <input type="text" wire:model.defer="newVariable.slug" class="form-control form-control--modern" placeholder="e.g. ambient_humidity_corr" required>
                            <small class="text-muted">Lowercase letters, numbers, and underscores only.</small>
                            @error('newVariable.slug') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-label--modern">Constant Value <span class="text-danger">*</span></label>
                            <input type="text" wire:model.defer="newVariable.constant_value" class="form-control form-control--modern" placeholder="e.g. 0.05" required>
                            @error('newVariable.constant_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-label--modern">Description</label>
                            <textarea wire:model.defer="newVariable.description" class="form-control form-control--modern" rows="3"></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeVariableModal">Cancel</button>
                            <button type="submit" class="btn btn-success">Define Variable</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
