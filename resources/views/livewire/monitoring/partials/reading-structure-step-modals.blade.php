@if($showCreateReadingStepModal)
    <div class="fs-modal show d-block" tabindex="-1" wire:click.self="closeCreateReadingStepModal">
        <div class="modal-dialog modal-lg fs-modal-dialog" wire:click.stop>
            <div class="modal-content fs-modal-content">
                <div class="modal-header fs-modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Create New Step</h5>
                        <p class="fs-modal-subtitle mb-0">Define how this step captures or calculates data for one reading.</p>
                    </div>
                    <button type="button" class="close fs-modal-close" wire:click="closeCreateReadingStepModal">&times;</button>
                </div>
                <form wire:submit.prevent="createReadingStep" class="fs-step-form">
                    <div class="modal-body fs-modal-body">
                        @include('livewire.monitoring.partials.reading-structure-step-message')
                        @include('livewire.monitoring.partials.reading-structure-step-form-fields')
                    </div>
                    <div class="modal-footer fs-modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeCreateReadingStepModal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="mdi mdi-content-save-outline"></i> Create Step
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($showEditReadingStepModal)
    <div class="fs-modal show d-block" tabindex="-1" wire:click.self="closeEditReadingStepModal">
        <div class="modal-dialog modal-lg fs-modal-dialog" wire:click.stop>
            <div class="modal-content fs-modal-content">
                <div class="modal-header fs-modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Edit Step</h5>
                        <p class="fs-modal-subtitle mb-0">Update this reading pipeline step.</p>
                    </div>
                    <button type="button" class="close fs-modal-close" wire:click="closeEditReadingStepModal">&times;</button>
                </div>
                <form wire:submit.prevent="updateReadingStep" class="fs-step-form">
                    <div class="modal-body fs-modal-body">
                        @include('livewire.monitoring.partials.reading-structure-step-message')
                        @include('livewire.monitoring.partials.reading-structure-step-form-fields')
                    </div>
                    <div class="modal-footer fs-modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeEditReadingStepModal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="mdi mdi-content-save-outline"></i> Save Step
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($showDeleteReadingStepModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Remove Step</h5>
                    <button type="button" class="btn-close" wire:click="closeDeleteReadingStepModal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Remove this step from the reading structure? This cannot be undone until you save the template.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" wire:click="closeDeleteReadingStepModal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteReadingStep">Remove Step</button>
                </div>
            </div>
        </div>
    </div>
@endif
