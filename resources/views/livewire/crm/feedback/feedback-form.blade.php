<div>
    <template x-teleport="body">
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
            role="dialog" wire:click.self="close" wire:ignore.self>
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $feedbackId ? 'pencil' : 'plus' }}"></i>
                            {{ $feedbackId ? 'Edit' : 'Add' }} Feedback
                        </h4>
                        <button type="button" class="close" wire:click="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label">Received From <span class="text-danger">*</span></label>
                                <div class="tag-select-container @error('received_from') is-invalid @enderror"
                                    wire:click="$set('showCustomerDropdown', true)"
                                    wire:click.outside="$set('showCustomerDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedCustomer)
                                            <span class="tag-badge">
                                                {{ $this->selectedCustomer->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearReceivedFrom"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                            wire:model.live="customerSearch"
                                            class="tag-input"
                                            placeholder="{{ $this->selectedCustomer ? '' : 'Select Customer' }}"
                                            autocomplete="off">
                                    </div>

                                    @if($showCustomerDropdown)
                                        <div class="tag-dropdown">
                                            @if(count($this->filteredCustomers) > 0)
                                                @foreach($this->filteredCustomers as $customer)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectReceivedFrom(@js($customer->name))">
                                                        {{ $customer->name }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="tag-dropdown-item text-muted">No customers found</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @error('received_from') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="control-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                    wire:model="date" required />
                                @error('date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="control-label">Feedback <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('feedback') is-invalid @enderror"
                                    wire:model="feedback" rows="4" required></textarea>
                                @error('feedback') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" wire:model="isActive" />
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-default" wire:click="close">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>

<style>
    .tag-select-container {
        position: relative;
        width: 100%;
    }

    .tag-select-input {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 4px 8px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        background-color: #fff;
    }

    .tag-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 120px;
        font-size: 0.9rem;
    }

    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0f2f5;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 0.85rem;
    }

    .tag-badge i {
        cursor: pointer;
    }

    .tag-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        max-height: 220px;
        overflow-y: auto;
        z-index: 1100;
    }

    .tag-dropdown-item {
        padding: 8px 10px;
        cursor: pointer;
    }

    .tag-dropdown-item:hover {
        background: #f8f9fa;
    }

    .tag-select-container.is-invalid .tag-select-input {
        border-color: #dc3545;
    }
</style>