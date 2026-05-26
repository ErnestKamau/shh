@if($errors->any())
    <div class="alert alert-danger border-0 cf-alert mb-3">
        <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="cf-form">
    @if($showEditConfiguredFieldModal)
        <section class="cf-form-section">
            <h6 class="cf-form-section-title">Placement</h6>
            <select wire:model="configuredFieldPlacement" class="form-select cf-control" style="max-width: 280px;">
                @foreach($this->configuredFieldPlacementOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </section>
    @endif

    <section class="cf-form-section">
        <h6 class="cf-form-section-title">Field identity</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="cf-label">Label <span class="text-danger">*</span></label>
                <input type="text" wire:model="configuredFieldLabel" class="form-control cf-control @error('configuredFieldLabel') is-invalid @enderror" placeholder="e.g. Analyst name">
                @error('configuredFieldLabel') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="cf-label">Value name <span class="text-danger">*</span></label>
                <input type="text" wire:model="configuredFieldValueName" class="form-control cf-control font-monospace @error('configuredFieldValueName') is-invalid @enderror" placeholder="e.g. analyst_name">
                @error('configuredFieldValueName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <div class="cf-hint">Snake_case key stored with the log entry.</div>
            </div>
        </div>
    </section>

    <section class="cf-form-section">
        <h6 class="cf-form-section-title">Field type</h6>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="cf-label">Type <span class="text-danger">*</span></label>
                <div class="tag-select-container @error('configuredFieldType') is-invalid @enderror"
                     wire:click.away="closeConfiguredFieldTypeDropdown">
                    <div class="tag-select-input" wire:click="openConfiguredFieldTypeDropdown">
                        @if($this->selectedConfiguredFieldTypeLabel)
                            <span class="tag-badge">
                                {{ $this->selectedConfiguredFieldTypeLabel }}
                            </span>
                        @endif
                        <input type="text"
                               class="tag-input"
                               readonly
                               placeholder="{{ $this->selectedConfiguredFieldTypeLabel ? '' : 'Select field type…' }}"
                               style="cursor: pointer;">
                    </div>
                    @if($showConfiguredFieldTypeDropdown)
                        <div class="tag-dropdown">
                            @foreach($this->configuredFieldTypeOptions as $value => $label)
                                <div class="tag-dropdown-item {{ $configuredFieldType === $value ? 'active' : '' }}"
                                     wire:click.stop="selectConfiguredFieldType('{{ $value }}')">
                                    {{ $label }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('configuredFieldType') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="cf-label">Display order</label>
                <input type="number" wire:model="configuredFieldOrder" class="form-control cf-control" min="1">
            </div>
        </div>

        @if($configuredFieldType === 'monitoring_equipment')
            <div class="cf-type-panel mt-3">
                <i class="mdi mdi-tools text-primary"></i>
                <span>At capture, analysts pick from equipment selected for this template in step 3.</span>
            </div>
        @endif

        @if($configuredFieldType === 'lab_section_select')
            <div class="cf-type-panel mt-3">
                <i class="mdi mdi-flask-outline text-primary"></i>
                <span>Shows the active lab section for this environmental reading during capture.</span>
            </div>
        @endif

        @if($configuredFieldType === 'equipment_calibration')
            <div class="cf-type-panel mt-3">
                <p class="small text-muted mb-2">Select certificate attributes to expose. Values resolve from the latest calibration on or before the <strong>reading date</strong>.</p>
                <div class="row g-2">
                    @foreach($this->configuredFieldCalibrationOptions as $attr => $attrLabel)
                        <div class="col-md-6">
                            <label class="cf-check-tile">
                                <input type="checkbox" class="form-check-input" wire:model="configuredFieldCalibrationAttributes" value="{{ $attr }}">
                                <span>{{ $attrLabel }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('configuredFieldCalibrationAttributes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        @endif

        @if($configuredFieldType === 'dataset_related')
            <div class="cf-type-panel mt-3">
                <label class="cf-label">Dataset model <span class="text-danger">*</span></label>
                <select wire:model="configuredFieldModelTiedTo" class="form-select cf-control @error('configuredFieldModelTiedTo') is-invalid @enderror">
                    <option value="">Select dataset…</option>
                    @foreach($this->configuredFieldDatasetOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('configuredFieldModelTiedTo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        @endif

        @if(in_array($configuredFieldType, ['manager_dropdown', 'user_signature'], true))
            <div class="cf-type-panel mt-3">
                <label class="cf-label">Resolve manager from <span class="text-danger">*</span></label>
                <select wire:model="configuredFieldManagerSource" class="form-select cf-control @error('configuredFieldManagerSource') is-invalid @enderror">
                    @foreach($this->configuredFieldManagerSourceOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('configuredFieldManagerSource') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                @if($configuredFieldType === 'user_signature')
                    <div class="cf-hint mt-2">The manager’s electronic signature is applied automatically when a manager is resolved from the lab section or equipment location.</div>
                @else
                    <div class="cf-hint mt-2">Manager list is filtered automatically from the lab section or equipment location selected during capture.</div>
                @endif
            </div>
        @endif

        @if($configuredFieldType === 'month_day')
            <div class="cf-type-panel mt-3">
                <i class="mdi mdi-calendar-month text-primary"></i>
                <span>Captures month and day only (no year)—useful for recurring annual logs.</span>
            </div>
        @endif
    </section>

    <section class="cf-form-section mb-0">
        <h6 class="cf-form-section-title">Options</h6>
        <div class="mb-3">
            <label class="cf-label">Help text</label>
            <textarea wire:model="configuredFieldHelpText" class="form-control cf-control" rows="2" placeholder="Optional guidance shown to analysts"></textarea>
        </div>
        <div class="form-check cf-check-required">
            <input type="checkbox" wire:model="configuredFieldIsRequired" class="form-check-input" id="configuredFieldIsRequired">
            <label class="form-check-label" for="configuredFieldIsRequired">Required during log capture</label>
        </div>
    </section>
</div>
