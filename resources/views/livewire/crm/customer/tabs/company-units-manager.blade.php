<div>
    <x-livewire.flash-messages />

    <div class="d-flex justify-content-between align-items-center p-2 mb-3">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 mr-2 imara-section-title">
                <i class="mdi mdi-format-list-bulleted"></i>
                {{ trim($customer->unit_configurable_name) !== '' ? $customer->unit_configurable_name : 'Company Units' }}
            </h5>
            <button type="button" wire:click="openLabelModal('unit_configurable_name')"
                class="btn btn-sm btn-link text-info p-0" title="Edit label">
                <i class="mdi mdi-pencil"></i>
            </button>
        </div>
        <x-imara.primary-btn subject="Company Unit" wire:click="openCreateModal" />
    </div>

    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th style="min-width: 100px;">Actions</th>
                <th>No</th>
                <th nowrap>Name</th>

                <th nowrap>Active</th>
            </tr>
        </x-slot:header>
        <tbody>
            @foreach ($this->units as $unit)
                <tr wire:key="unit-{{ $unit->id }}">
                    <td nowrap>
                        <x-crm.action-buttons>
                            <button class="btn crm-btn crm-btn-edit btn-sm" title="Edit"
                                wire:click="editUnit({{ $unit->id }})">
                                <i class="mdi mdi-pencil-outline"></i>
                            </button>
                            @if (!$this->isQplus)
                                <button class="btn crm-btn crm-btn-delete btn-sm" title="Delete"
                                    wire:click="deleteUnit({{ $unit->id }})"
                                    wire:confirm="Are you sure you want to delete this company unit?">
                                    <i class="mdi mdi-trash-can-outline"></i>
                                </button>
                            @endif
                        </x-crm.action-buttons>
                    </td>
                    <td>{{ $this->units->firstItem() + $loop->index }}</td>
                    <td>{{ $unit->name }}</td>

                    <td>
                        @if($unit->active == '1')
                            <span class="crm-badge crm-badge-success">Active</span>
                        @else
                            <span class="crm-badge crm-badge-danger">Inactive</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-crm.data-table>

    @if ($this->units->hasPages())
        <div class="border-top bg-white py-2 px-3">
            {{ $this->units->links() }}
        </div>
    @endif

    @if ($showCreateModal)
        <div class="modal fade show d-block livewire-modal-overlay" style="background: rgba(0,0,0,0.5); z-index: 1050;"
            tabindex="-1" wire:key="create-unit-modal" role="dialog" x-data="{
                        initSelect2() {
                            // Logic removed
                        }
                     }" x-init="$nextTick(() => initSelect2())">
            <div class="modal-dialog">
                <div class="modal-content imara-form-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-pencil-outline"></i> {{ $editingUnitId ? 'Edit' : 'Add' }}
                            Company Unit</h5>
                        <button type="button" wire:click="$set('showCreateModal', false)" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if ($modalError)
                            <div class="alert alert-danger">{{ $modalError }}</div>
                        @endif

                        <x-imara.form-field label="Name" :required="true" :error="$errors->first('name')">
                            <input type="text" wire:model="name" class="form-control" placeholder="Name..." required />
                        </x-imara.form-field>

                        <div class="form-group">
                            <x-imara.custom-checkbox wire:model="active" label="Is active?" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="saveUnit" wire:loading.attr="disabled" class="btn btn-primary">
                            <span wire:loading.remove wire:target="saveUnit"><i class="mdi mdi-content-save"></i>
                                Save</span>
                            <span wire:loading wire:target="saveUnit">Saving...</span>
                        </button>
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showLabelModal)
        <div class="modal fade show d-block livewire-modal-overlay" style="background: rgba(0,0,0,0.5); z-index: 1050;"
            tabindex="-1" wire:key="label-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content imara-form-modal">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Label Name</h5>
                        <button type="button" wire:click="$set('showLabelModal', false)" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <x-imara.form-field label="Name" :required="true" :error="$errors->first('labelValue')">
                            <input type="text" wire:model="labelValue" class="form-control" placeholder="Name..."
                                required />
                        </x-imara.form-field>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="saveLabel" wire:loading.attr="disabled" class="btn btn-primary">
                            <span wire:loading.remove wire:target="saveLabel"><i class="mdi mdi-content-save"></i>
                                Save</span>
                            <span wire:loading wire:target="saveLabel">Saving...</span>
                        </button>
                        <button type="button" wire:click="$set('showLabelModal', false)"
                            class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>