@if($stepType === 'static_text')
    <section class="fs-config-panel mb-3">
        <div class="fs-config-panel-head">
            <span class="fs-config-panel-icon"><i class="mdi mdi-text-box-outline"></i></span>
            <div>
                <h6 class="mb-0">Static text</h6>
                <p class="mb-0 small text-muted">Shown on the worksheet for information only; analysts do not enter a value.</p>
            </div>
        </div>
        <div class="fs-config-panel-body">
            <label class="form-label">Static text <span class="text-danger">*</span></label>
            <textarea wire:model="staticTextContent" class="form-control fs-input" rows="5" placeholder="Enter instructions or notes…"></textarea>
            @error('staticTextContent') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </section>
@endif

@if($stepType === 'checkbox')
    <section class="fs-config-panel fs-config-panel--checkbox mb-3">
        <div class="fs-config-panel-head">
            <span class="fs-config-panel-icon"><i class="mdi mdi-checkbox-marked-outline"></i></span>
            <div>
                <h6 class="mb-0">Checkbox options</h6>
                <p class="mb-0 small text-muted">Worksheet-wide checklist shown at the bottom of the formula worksheet.</p>
            </div>
        </div>
        <div class="fs-config-panel-body fs-checkbox-options-panel">
            <label class="form-label fw-semibold d-block mb-2">Options source</label>
            <div class="fs-checkbox-options-source" role="radiogroup" aria-label="Options source">
                <label class="fs-checkbox-source-option {{ $checkboxOptionsMode === 'static' ? 'is-active' : '' }}">
                    <input type="radio" wire:model.live="checkboxOptionsMode" value="static" class="fs-checkbox-source-option-input">
                    <span class="fs-checkbox-source-option-label">Static list</span>
                </label>
                <label class="fs-checkbox-source-option {{ $checkboxOptionsMode === 'preset' ? 'is-active' : '' }}">
                    <input type="radio" wire:model.live="checkboxOptionsMode" value="preset" class="fs-checkbox-source-option-input">
                    <span class="fs-checkbox-source-option-label">Preset dataset</span>
                </label>
                <label class="fs-checkbox-source-option {{ $checkboxOptionsMode === 'dataset' ? 'is-active' : '' }}">
                    <input type="radio" wire:model.live="checkboxOptionsMode" value="dataset" class="fs-checkbox-source-option-input">
                    <span class="fs-checkbox-source-option-label">Custom table</span>
                </label>
            </div>

            @if($checkboxOptionsMode === 'static')
                <div class="fs-checkbox-option-add">
                    <input type="text"
                           wire:model="checkboxNewOption"
                           wire:keydown.enter.prevent="addCheckboxStaticOption"
                           class="form-control fs-input fs-checkbox-option-add-input"
                           placeholder="Type an option and press Enter or Add">
                    <button type="button" class="btn btn-outline-primary fs-checkbox-option-add-btn" wire:click="addCheckboxStaticOption">
                        <i class="mdi mdi-plus"></i> Add
                    </button>
                </div>
                @error('checkboxStaticOptions') <div class="text-danger small mb-2 mt-1">{{ $message }}</div> @enderror
                @if(count($checkboxStaticOptions) > 0)
                    <ul class="fs-checkbox-options-list list-unstyled mb-0">
                        @foreach($checkboxStaticOptions as $index => $option)
                            <li class="fs-checkbox-options-list-item" wire:key="cb-opt-{{ $index }}">
                                <span class="fs-checkbox-options-list-text">{{ $option }}</span>
                                <button type="button"
                                        class="btn btn-sm btn-link text-danger fs-checkbox-options-list-remove p-0"
                                        wire:click="removeCheckboxStaticOption({{ $index }})"
                                        title="Remove option"
                                        aria-label="Remove option">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0 mt-2">No options added yet.</p>
                @endif
            @elseif($checkboxOptionsMode === 'preset')
                <select wire:model="checkboxPresetModel" class="form-control fs-input">
                    <option value="equipments">Equipment</option>
                    <option value="users">Users</option>
                    <option value="methods">Methods</option>
                    <option value="analytes">Analytes</option>
                </select>
                @error('checkboxPresetModel') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            @else
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small">Source table</label>
                        <select wire:model.live="checkboxDatasetSourceTable" class="form-control form-control-sm">
                            <option value="">Select table…</option>
                            @foreach($checkboxDatasetTables ?? [] as $table)
                                <option value="{{ $table['value'] ?? $table['name'] ?? '' }}">{{ $table['label'] ?? $table['value'] ?? '' }}</option>
                            @endforeach
                        </select>
                        @error('checkboxDatasetSourceTable') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Display mode</label>
                        <select wire:model.live="checkboxDatasetDisplayMode" class="form-control form-control-sm">
                            <option value="direct">Direct column</option>
                            <option value="foreign_key">Foreign key</option>
                        </select>
                    </div>
                    @if($checkboxDatasetDisplayMode === 'direct')
                        <div class="col-md-12">
                            <label class="form-label small">Display column</label>
                            <select wire:model="checkboxDatasetSourceColumn" class="form-control form-control-sm">
                                <option value="">Select column…</option>
                                @foreach($checkboxDatasetColumns ?? [] as $col)
                                    <option value="{{ $col['value'] ?? $col['name'] ?? '' }}">{{ $col['label'] ?? $col['value'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="col-md-6">
                            <label class="form-label small">Foreign key column</label>
                            <select wire:model.live="checkboxDatasetFkColumn" class="form-control form-control-sm">
                                <option value="">Select…</option>
                                @foreach($checkboxDatasetForeignKeys ?? [] as $fk)
                                    <option value="{{ $fk['column'] }}">{{ $fk['label'] ?? $fk['column'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Referenced display column</label>
                            <select wire:model="checkboxDatasetReferencedDisplayColumn" class="form-control form-control-sm">
                                <option value="">Select…</option>
                                @foreach($checkboxDatasetReferencedColumns ?? [] as $col)
                                    <option value="{{ $col['value'] ?? $col['name'] ?? '' }}">{{ $col['label'] ?? $col['value'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endif

@if($stepType === 'pcr_plate_map')
    @include('livewire.formulars.partials.formula-step-pcr-plate-config')
@endif

@if($stepType === 'custom_table')
    <section class="fs-config-panel mb-3">
        <div class="fs-config-panel-head">
            <span class="fs-config-panel-icon"><i class="mdi mdi-table-large"></i></span>
            <div>
                <h6 class="mb-0">Table mode</h6>
                <p class="mb-0 small text-muted">After saving, use <strong>Configure table</strong> in the steps list to define columns and rows.</p>
            </div>
        </div>
        <div class="fs-config-panel-body">
            <div class="d-flex flex-wrap gap-3 mb-3">
                <label class="lec-radio-card mb-0">
                    <input type="radio" wire:model.live="table_mode" value="dynamic">
                    <span>Dynamic rows</span>
                </label>
                <label class="lec-radio-card mb-0">
                    <input type="radio" wire:model.live="table_mode" value="static">
                    <span>Static rows</span>
                </label>
            </div>
            @error('table_mode') <div class="text-danger small">{{ $message }}</div> @enderror
            @if($table_mode === 'dynamic')
                <div class="form-group mb-3">
                    <label class="form-label">Row driver</label>
                    <select class="form-control fs-input" wire:model="row_driver">
                        @foreach($rowDriverOptions ?? [] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('row_driver') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="formula-step-allow-manual-rows" wire:model="allow_manual_rows">
                    <label class="custom-control-label" for="formula-step-allow-manual-rows">Allow manual rows at capture</label>
                </div>
            @endif
        </div>
    </section>
@endif
