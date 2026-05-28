@if($showCreateConfiguredFieldModal || $showEditConfiguredFieldModal)
    <div class="cf-modal show d-block" tabindex="-1" wire:click.self="{{ $showCreateConfiguredFieldModal ? 'closeCreateConfiguredFieldModal' : 'closeEditConfiguredFieldModal' }}">
        <div class="modal-dialog modal-lg modal-dialog-scrollable cf-modal-dialog" wire:click.stop>
            <div class="modal-content cf-modal-content">
                <div class="modal-header cf-modal-header">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="mdi {{ $showCreateConfiguredFieldModal ? 'mdi-plus-circle-outline' : 'mdi-pencil-outline' }}"></i>
                            {{ $showCreateConfiguredFieldModal ? 'Add configured field' : 'Edit configured field' }}
                        </h5>
                        <p class="cf-modal-subtitle mb-0">
                            {{ $configuredFieldPlacement === 'top' ? 'Top of worksheet' : 'Bottom of worksheet' }}
                        </p>
                    </div>
                    <button type="button" class="btn-close cf-modal-close" wire:click="{{ $showCreateConfiguredFieldModal ? 'closeCreateConfiguredFieldModal' : 'closeEditConfiguredFieldModal' }}" aria-label="Close"></button>
                </div>
                <div class="modal-body cf-modal-body">
                    @include('livewire.monitoring.partials.configured-field-form')
                </div>
                <div class="modal-footer cf-modal-footer">
                    <button type="button" class="btn btn-light" wire:click="{{ $showCreateConfiguredFieldModal ? 'closeCreateConfiguredFieldModal' : 'closeEditConfiguredFieldModal' }}">Cancel</button>
                    @if($showCreateConfiguredFieldModal)
                        <button type="button" class="btn btn-primary" wire:click="createConfiguredField">
                            <i class="mdi mdi-check"></i> Add field
                        </button>
                    @else
                        <button type="button" class="btn btn-primary" wire:click="updateConfiguredField">
                            <i class="mdi mdi-content-save-outline"></i> Save changes
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if($showDeleteConfiguredFieldModal)
    <div class="cf-modal show d-block" tabindex="-1" wire:click.self="closeDeleteConfiguredFieldModal">
        <div class="modal-dialog modal-sm cf-modal-dialog" wire:click.stop>
            <div class="modal-content cf-modal-content">
                <div class="modal-header cf-modal-header py-3">
                    <h5 class="modal-title mb-0">Delete field?</h5>
                    <button type="button" class="btn-close" wire:click="closeDeleteConfiguredFieldModal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-muted">This field will be removed from the template.</p>
                </div>
                <div class="modal-footer cf-modal-footer py-2">
                    <button type="button" class="btn btn-light btn-sm" wire:click="closeDeleteConfiguredFieldModal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm" wire:click="deleteConfiguredField">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endif

@include('livewire.monitoring.partials.configured-field-modal-styles')
