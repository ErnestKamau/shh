{{-- Config field section modals --}}
@if($showCreateConfigSectionModal || $showEditConfigSectionModal)
<div class="pw-modal show d-block" tabindex="-1" wire:click.self="closeConfigSectionModal">
    <div class="modal-dialog modal-dialog-scrollable pw-modal-dialog" wire:click.stop>
        <div class="modal-content pw-modal-content">
            <div class="modal-header pw-modal-header">
                <h5 class="modal-title mb-0">{{ $showEditConfigSectionModal ? 'Edit field section' : 'Add field section' }}</h5>
                <button type="button" class="close pw-modal-close" wire:click="closeConfigSectionModal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Optional holder to group related configurable fields (e.g. Client details, Required solutions).</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Section title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" wire:model="configSectionTitle" placeholder="e.g. Client details and sample information">
                    @error('configSectionTitle') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" rows="2" wire:model="configSectionDescription" placeholder="Optional"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Order</label>
                    <input type="number" class="form-control" wire:model="configSectionOrder" min="1" style="max-width: 8rem;">
                </div>
            </div>
            <div class="modal-footer pw-modal-footer">
                <button type="button" class="btn btn-light" wire:click="closeConfigSectionModal">Cancel</button>
                @if($showEditConfigSectionModal)
                <button type="button" class="btn btn-primary" wire:click="updateConfigSection">Save section</button>
                @else
                <button type="button" class="btn btn-primary" wire:click="createConfigSection">Add section</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if($showDeleteConfigSectionModalOpen)
<div class="pw-modal show d-block" tabindex="-1" wire:click.self="$set('showDeleteConfigSectionModalOpen', false)">
    <div class="modal-dialog pw-modal-dialog pw-modal-dialog--sm" wire:click.stop>
        <div class="modal-content pw-modal-content">
            <div class="modal-body">
                <p>Delete section <strong>{{ $deletingConfigSection?->title }}</strong>? Fields in this section will become ungrouped.</p>
            </div>
            <div class="modal-footer pw-modal-footer">
                <button type="button" class="btn btn-light" wire:click="$set('showDeleteConfigSectionModalOpen', false)">Cancel</button>
                <button type="button" class="btn btn-danger" wire:click="deleteConfigSection">Delete</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Step group modals --}}
@if($showCreateStepGroupModal || $showEditStepGroupModal)
<div class="pw-modal show d-block" tabindex="-1" style="z-index: 1060;" wire:click.self="closeStepGroupModal">
    <div class="modal-dialog modal-dialog-scrollable pw-modal-dialog" wire:click.stop>
        <div class="modal-content pw-modal-content">
            <div class="modal-header pw-modal-header">
                <h5 class="modal-title mb-0">{{ $showEditStepGroupModal ? 'Edit step group' : 'Add step group' }}</h5>
                <button type="button" class="close pw-modal-close" wire:click="closeStepGroupModal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Optional group for procedure steps (e.g. Required screening tests, Test results captured).</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Group title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" wire:model="stepGroupTitle" placeholder="e.g. Group 1 — Required screening tests">
                    @error('stepGroupTitle') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" rows="2" wire:model="stepGroupDescription" placeholder="Optional"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Order</label>
                    <input type="number" class="form-control" wire:model="stepGroupOrder" min="1" style="max-width: 8rem;">
                </div>
            </div>
            <div class="modal-footer pw-modal-footer">
                <button type="button" class="btn btn-light" wire:click="closeStepGroupModal">Cancel</button>
                @if($showEditStepGroupModal)
                <button type="button" class="btn btn-primary" wire:click="updateStepGroup">Save group</button>
                @else
                <button type="button" class="btn btn-primary" wire:click="createStepGroup">Add group</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if($showDeleteStepGroupModalOpen)
<div class="pw-modal show d-block" tabindex="-1" style="z-index: 1060;" wire:click.self="$set('showDeleteStepGroupModalOpen', false)">
    <div class="modal-dialog pw-modal-dialog pw-modal-dialog--sm" wire:click.stop>
        <div class="modal-content pw-modal-content">
            <div class="modal-body">
                <p>Delete group <strong>{{ $deletingStepGroup?->title }}</strong>? Steps in this group will become ungrouped.</p>
            </div>
            <div class="modal-footer pw-modal-footer">
                <button type="button" class="btn btn-light" wire:click="$set('showDeleteStepGroupModalOpen', false)">Cancel</button>
                <button type="button" class="btn btn-danger" wire:click="deleteStepGroup">Delete</button>
            </div>
        </div>
    </div>
</div>
@endif
