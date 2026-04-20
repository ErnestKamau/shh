<div>
    @if($showForm)
        <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #007bff !important; border-radius: 0.5rem;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 pb-2 px-4">
                <h5 class="mb-0 font-weight-bold text-dark d-flex align-items-center">
                    <i class="mdi {{ $editingLineId ? 'mdi-pencil-circle text-info' : 'mdi-plus-circle text-primary' }} mr-2" style="font-size: 1.8rem;"></i>
                    {{ $editingLineId ? 'Edit Translation' : 'Add New Translation' }}
                </h5>
                <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm" wire:click="close" title="Close" style="width: 32px; height: 32px; padding: 0;">
                    <i class="mdi mdi-close" style="font-size: 1.2rem;"></i>
                </button>
            </div>
            
            <div class="card-body px-4 pt-3 pb-4">
                <div class="bg-light p-4 rounded mb-4" style="border: 2px dashed #ced4da;">
                    <div class="form-row">
                        <div class="form-group col-md-6 mb-md-0 pr-md-3">
                            <label class="text-muted font-weight-bold mb-2">Group <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" wire:model.defer="group" placeholder="e.g., trips, system, modules">
                            @error('group') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group col-md-6 mb-0 pl-md-3">
                            <label class="text-muted font-weight-bold mb-2">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" wire:model.defer="key" placeholder="e.g., trip_assigned">
                            @error('key') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3 px-2">
                    <h6 class="font-weight-bold text-dark mb-4 border-bottom pb-2">
                        <i class="mdi mdi-earth mr-1"></i> Language Values
                    </h6>
                    <div class="row">
                        @foreach($languages as $language)
                            <div class="col-md-6 mb-4">
                                <label class="text-muted font-weight-bold mb-2 d-flex justify-content-between align-items-center">
                                    <span>{{ $language->name }}</span>
                                    <span class="badge badge-secondary px-2 py-1">{{ strtoupper($language->code) }}</span>
                                </label>
                                <textarea class="form-control shadow-sm" rows="2" wire:model.defer="textInputs.{{ $language->code }}" placeholder="Enter translation for {{ $language->name }}..."></textarea>
                            </div>
                        @endforeach
                    </div>
                    @error('textInputs') <small class="text-danger d-block mt-1"><i class="mdi mdi-alert-circle mr-1"></i>{{ $message }}</small> @enderror
                </div>
                
                <hr class="mb-4">

                <div class="d-flex justify-content-end px-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 mr-2 shadow-sm" wire:click="close">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" wire:click="save" wire:loading.attr="disabled">
                        <i class="mdi mdi-content-save mr-1" wire:loading.remove wire:target="save"></i> 
                        <span wire:loading.remove wire:target="save">{{ $editingLineId ? 'Update Translation' : 'Save Translation' }}</span>
                        <span wire:loading wire:target="save"><i class="mdi mdi-loading mdi-spin mr-1"></i> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
