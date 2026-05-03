<div>
    @if($showForm)
        <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #007bff !important; border-radius: 0.5rem;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 pb-2 px-4">
                <h5 class="mb-0 font-weight-bold text-dark d-flex align-items-center">
                    <i class="mdi {{ $editingLanguageId ? 'mdi-pencil-circle text-info' : 'mdi-plus-circle text-primary' }} mr-2" style="font-size: 1.8rem;"></i>
                    {{ $editingLanguageId ? __('system.edit_language') : __('system.add_new_language') }}
                </h5>
                <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm" wire:click="close" title="Close" style="width: 32px; height: 32px; padding: 0;">
                    <i class="mdi mdi-close" style="font-size: 1.2rem;"></i>
                </button>
            </div>
            
            <div class="card-body px-4 pt-3 pb-4">
                <div class="bg-light p-4 rounded mb-4" style="border: 2px dashed #ced4da;">
                    <div class="form-row">
                        <div class="form-group col-md-5 mb-3 mb-md-0">
                            <label class="text-muted font-weight-bold mb-2">{{ __('system.language_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" wire:model.defer="name" placeholder="{{ __('system.language_name_placeholder') }}">
                            @error('name') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group col-md-3 mb-3 mb-md-0">
                            <label class="text-muted font-weight-bold mb-2">{{ __('system.code') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" wire:model.defer="code" placeholder="{{ __('system.code_placeholder') }}">
                            @error('code') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                        </div>
                        
                        <div class="form-group col-md-2 mb-2 mb-md-0 d-flex align-items-center">
                            <div class="custom-control custom-switch mt-md-4">
                                <input type="checkbox" class="custom-control-input" id="lang-active" wire:model="is_active">
                                <label class="custom-control-label font-weight-bold text-secondary cursor-pointer" for="lang-active" style="padding-top: 2px;">{{ __('system.active') }}</label>
                            </div>
                        </div>
                        <div class="form-group col-md-2 mb-0 d-flex align-items-center">
                            <div class="custom-control custom-switch mt-md-4">
                                <input type="checkbox" class="custom-control-input" id="lang-default" wire:model="is_default">
                                <label class="custom-control-label font-weight-bold text-secondary cursor-pointer" for="lang-default" style="padding-top: 2px;">{{ __('system.default') }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="mb-4">

                <div class="d-flex justify-content-end px-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 mr-2 shadow-sm" wire:click="close">
                        {{ __('system.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" wire:click="save" wire:loading.attr="disabled">
                        <i class="mdi mdi-content-save mr-1" wire:loading.remove wire:target="save"></i> 
                        <span wire:loading.remove wire:target="save">{{ $editingLanguageId ? __('system.update_language') : __('system.save_language') }}</span>
                        <span wire:loading wire:target="save"><i class="mdi mdi-loading mdi-spin mr-1"></i> {{ __('system.saving') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
