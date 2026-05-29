@php
    $isEdit = $showEditFieldModal ?? false;
    $fieldTypeMeta = [
        'input' => ['icon' => 'mdi-form-textbox', 'hint' => 'Free text entered once per worksheet'],
        'datetime' => ['icon' => 'mdi-calendar-clock', 'hint' => 'Date and time picker'],
        'date' => ['icon' => 'mdi-calendar', 'hint' => 'Date picker'],
        'checkbox' => ['icon' => 'mdi-checkbox-marked-outline', 'hint' => 'Multiple options analysts can tick'],
        'dataset_related' => ['icon' => 'mdi-database-search', 'hint' => 'Pick from equipment, users, or methods'],
    ];
@endphp

@if($showCreateFieldModal || $showEditFieldModal)
    <div class="fs-modal show d-block fm-mandatory-modal" tabindex="-1" wire:click.self="closeMandatoryFieldModal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable fs-modal-dialog" wire:click.stop>
            <div class="modal-content fs-modal-content">
                <div class="modal-header fs-modal-header">
                    <div>
                        <h5 class="modal-title mb-1">{{ $isEdit ? 'Edit' : 'Create' }} Mandatory Field</h5>
                        <p class="fs-modal-subtitle mb-0">Shared fields shown once per worksheet during batch capture.</p>
                    </div>
                    <button type="button" class="close fs-modal-close" wire:click="closeMandatoryFieldModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body fs-modal-body">
                    @if($message)
                        <div class="alert alert-{{ $this->messageAlertClass() }} mb-3" role="alert">
                            {{ $message }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger mb-3" role="alert">
                            <h6 class="alert-heading mb-2">
                                <i class="mdi mdi-alert-circle"></i> Please fix the following:
                            </h6>
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="{{ $isEdit ? 'edit-formula-mandatory-field-form' : 'create-formula-mandatory-field-form' }}"
                          wire:submit.prevent="{{ $isEdit ? 'updateMandatoryField' : 'createMandatoryField' }}"
                          class="fs-step-form">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <section class="fs-form-section h-100 mb-0">
                                    <h6 class="fs-form-section-title">Field identity</h6>
                                    <div class="mb-3">
                                        <label class="fs-form-label" for="{{ $isEdit ? 'editFieldLabel' : 'fieldLabel' }}">Label <span class="text-danger">*</span></label>
                                        <input type="text"
                                               wire:model="fieldLabel"
                                               class="form-control fs-input @error('fieldLabel') is-invalid @enderror"
                                               id="{{ $isEdit ? 'editFieldLabel' : 'fieldLabel' }}"
                                               placeholder="e.g. Equipment used">
                                        @error('fieldLabel') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-0">
                                        <label class="fs-form-label" for="{{ $isEdit ? 'editFieldValueName' : 'fieldValueName' }}">Value name <span class="text-danger">*</span></label>
                                        <input type="text"
                                               wire:model="fieldValueName"
                                               class="form-control fs-input @error('fieldValueName') is-invalid @enderror"
                                               id="{{ $isEdit ? 'editFieldValueName' : 'fieldValueName' }}"
                                               placeholder="e.g. capture_date">
                                        @error('fieldValueName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        <span class="fm-field-hint">Lowercase key with underscores only (e.g. capture_date).</span>
                                    </div>
                                </section>
                            </div>

                            <div class="col-lg-6">
                                <section class="fs-form-section h-100 mb-0">
                                    <h6 class="fs-form-section-title">Capture settings</h6>
                                    <div class="mb-3">
                                        <label class="fs-form-label" for="{{ $isEdit ? 'editFieldOrder' : 'fieldOrder' }}">Display order</label>
                                        <input type="number"
                                               wire:model="fieldOrder"
                                               class="form-control fs-input @error('fieldOrder') is-invalid @enderror"
                                               id="{{ $isEdit ? 'editFieldOrder' : 'fieldOrder' }}"
                                               min="1">
                                        @error('fieldOrder') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="fs-form-label" for="{{ $isEdit ? 'editFieldHelpText' : 'fieldHelpText' }}">Help text</label>
                                        <input type="text"
                                               wire:model="fieldHelpText"
                                               class="form-control fs-input @error('fieldHelpText') is-invalid @enderror"
                                               id="{{ $isEdit ? 'editFieldHelpText' : 'fieldHelpText' }}"
                                               placeholder="Optional hint for analysts">
                                        @error('fieldHelpText') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <label class="fm-checkbox-card">
                                        <input type="checkbox" wire:model="fieldIsRequired">
                                        <span>Required at capture</span>
                                    </label>
                                </section>
                            </div>
                        </div>

                        <section class="fs-form-section mt-4 mb-0">
                            <h6 class="fs-form-section-title">Field type</h6>
                            <p class="fs-form-section-hint mb-3">Choose how analysts enter this value on the worksheet.</p>
                            <div class="fm-field-type-grid @error('fieldType') is-invalid @enderror">
                                @foreach($mandatoryFieldTypes as $value => $typeLabel)
                                    @php $meta = $fieldTypeMeta[$value] ?? ['icon' => 'mdi-help-circle-outline', 'hint' => '']; @endphp
                                    <label class="fm-field-type-tile {{ $fieldType === $value ? 'is-active' : '' }}">
                                        <input type="radio"
                                               wire:model.live="fieldType"
                                               value="{{ $value }}"
                                               class="fm-field-type-input"
                                               name="{{ $isEdit ? 'edit_mandatory_field_type' : 'create_mandatory_field_type' }}">
                                        <span class="fm-field-type-icon"><i class="mdi {{ $meta['icon'] }}"></i></span>
                                        <span class="fm-field-type-name">{{ $typeLabel }}</span>
                                        <span class="fm-field-type-hint">{{ $meta['hint'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('fieldType') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
                        </section>

                        <section class="fs-form-section mt-4 mb-0 fm-field-placement-section">
                            <label class="fs-form-label d-block mb-1">Form field placement <span class="text-danger">*</span></label>
                            <p class="fm-field-placement-intro text-muted small mb-2">
                                Choose where this field appears on the worksheet when analysts capture results.
                            </p>
                            <div class="fm-placement-options fm-placement-options--field @error('fieldFormPlacement') is-invalid @enderror"
                                 role="radiogroup"
                                 aria-label="Form field placement">
                                <label class="fm-placement-chip {{ $fieldFormPlacement === 'top' ? 'is-active' : '' }}"
                                       title="Top of form — above the sample table">
                                    <input type="radio"
                                           wire:model.live="fieldFormPlacement"
                                           value="top"
                                           class="fm-placement-input"
                                           name="{{ $isEdit ? 'edit_field_form_placement' : 'create_field_form_placement' }}">
                                    <i class="mdi mdi-arrow-up-bold" aria-hidden="true"></i>
                                    <span>Top</span>
                                </label>
                                <label class="fm-placement-chip {{ $fieldFormPlacement === 'bottom' ? 'is-active' : '' }}"
                                       title="Bottom of form — below the sample table">
                                    <input type="radio"
                                           wire:model.live="fieldFormPlacement"
                                           value="bottom"
                                           class="fm-placement-input"
                                           name="{{ $isEdit ? 'edit_field_form_placement' : 'create_field_form_placement' }}">
                                    <i class="mdi mdi-arrow-down-bold" aria-hidden="true"></i>
                                    <span>Bottom</span>
                                </label>
                            </div>
                            @error('fieldFormPlacement') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            <span class="fm-field-hint d-block mt-2">
                                <strong>Top</strong> shows the field above the sample table; <strong>Bottom</strong> shows it below the table.
                            </span>
                        </section>

                        @if($fieldType === 'checkbox')
                            <section class="fs-config-panel fs-config-panel--checkbox mt-4">
                                <div class="fs-config-panel-head">
                                    <span class="fs-config-panel-icon"><i class="mdi mdi-checkbox-marked-outline"></i></span>
                                    <div>
                                        <h6 class="mb-0">Checkbox options</h6>
                                        <p class="mb-0 small text-muted">Add choices analysts can select (one or more).</p>
                                    </div>
                                    <span class="badge badge-light border ml-auto">{{ count($fieldChoiceOptions) }} {{ count($fieldChoiceOptions) === 1 ? 'option' : 'options' }}</span>
                                </div>
                                <div class="fs-config-panel-body">
                                    <div class="fs-checkbox-option-add mb-3">
                                        <input type="text"
                                               class="form-control fs-input"
                                               wire:model="fieldNewChoice"
                                               wire:keydown.enter.prevent="addFieldChoice"
                                               placeholder="Type an option and press Enter or Add…"
                                               aria-label="New checkbox option">
                                        <button type="button" class="btn btn-primary btn-sm px-3" wire:click="addFieldChoice">
                                            <i class="mdi mdi-plus"></i> Add
                                        </button>
                                    </div>
                                    @error('fieldNewChoice') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
                                    @error('fieldChoiceOptions') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

                                    @if(count($fieldChoiceOptions) > 0)
                                        <ul class="fm-choices-list list-unstyled mb-0">
                                            @foreach($fieldChoiceOptions as $index => $choice)
                                                <li class="fm-choices-list__item" wire:key="fm-choice-{{ $index }}">
                                                    <span class="fm-choices-list__order">{{ $index + 1 }}</span>
                                                    <input type="text"
                                                           class="form-control form-control-sm"
                                                           wire:model.blur="fieldChoiceOptions.{{ $index }}"
                                                           placeholder="Option label">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger fm-choices-list__remove"
                                                            wire:click="removeFieldChoice({{ $index }})"
                                                            title="Remove">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="fm-choices-empty text-center text-muted py-3">
                                            <i class="mdi mdi-format-list-checkbox mdi-36px d-block mb-2"></i>
                                            <p class="mb-0 small">No options yet. Add your first choice above.</p>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        @endif

                        @if($fieldType === 'dataset_related')
                            <section class="fs-config-panel fs-config-panel--lookup mt-4">
                                <div class="fs-config-panel-head">
                                    <span class="fs-config-panel-icon"><i class="mdi mdi-database-search"></i></span>
                                    <div>
                                        <h6 class="mb-0">Dataset model</h6>
                                        <p class="mb-0 small text-muted">Link this field to a predefined list.</p>
                                    </div>
                                </div>
                                <div class="fs-config-panel-body">
                                    <label class="fs-form-label" for="{{ $isEdit ? 'editFieldModelTiedTo' : 'fieldModelTiedTo' }}">Dataset model <span class="text-danger">*</span></label>
                                    <select wire:model="fieldModelTiedTo"
                                            class="form-select fs-input modern-select no-select2 @error('fieldModelTiedTo') is-invalid @enderror"
                                            id="{{ $isEdit ? 'editFieldModelTiedTo' : 'fieldModelTiedTo' }}">
                                        <option value="">Select dataset model</option>
                                        <option value="equipments">Equipments</option>
                                        <option value="users">Users</option>
                                        <option value="methods">Methods</option>
                                    </select>
                                    @error('fieldModelTiedTo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </section>
                        @endif
                    </form>
                </div>

                <div class="modal-footer fs-modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeMandatoryFieldModal">Cancel</button>
                    <button type="button"
                            wire:click="{{ $isEdit ? 'updateMandatoryField' : 'createMandatoryField' }}"
                            class="btn btn-primary px-4"
                            wire:loading.attr="disabled"
                            wire:target="{{ $isEdit ? 'updateMandatoryField' : 'createMandatoryField' }}">
                        <span wire:loading.remove wire:target="{{ $isEdit ? 'updateMandatoryField' : 'createMandatoryField' }}">
                            <i class="mdi mdi-content-save-outline mr-1"></i>
                            {{ $isEdit ? 'Update Field' : 'Create Field' }}
                        </span>
                        <span wire:loading wire:target="{{ $isEdit ? 'updateMandatoryField' : 'createMandatoryField' }}">
                            Saving…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
