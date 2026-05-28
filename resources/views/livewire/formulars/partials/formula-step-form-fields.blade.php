@php
    $isEdit = ($stepFormMode ?? 'create') === 'edit';
    $stepTypeRadioName = $isEdit ? 'edit_step_type' : 'create_step_type';
    $lookupKeySuffix = $isEdit ? 'edit' : 'create';
    $stepTypeMeta = [
        'input' => ['icon' => 'mdi-form-textbox', 'color' => 'input', 'hint' => 'Analyst enters a value during worksheet capture'],
        'derived' => ['icon' => 'mdi-function-variant', 'color' => 'derived', 'hint' => 'Calculated from an expression using prior steps'],
        'lookup' => ['icon' => 'mdi-table-search', 'color' => 'lookup', 'hint' => 'Resolves a value from a configured lookup table'],
        'parameter_result' => ['icon' => 'mdi-flask-outline', 'color' => 'result', 'hint' => 'Posts results for a selected analyte parameter'],
        'static_text' => ['icon' => 'mdi-text-box-outline', 'color' => 'input', 'hint' => 'Read-only information on the worksheet'],
        'checkbox' => ['icon' => 'mdi-checkbox-marked-outline', 'color' => 'lookup', 'hint' => 'Worksheet-wide checklist at the bottom'],
        'custom_table' => ['icon' => 'mdi-table-large', 'color' => 'derived', 'hint' => 'Per-sample table with dynamic or static rows'],
    ];
@endphp

<section class="fs-form-section">
    <h6 class="fs-form-section-title">Step basics</h6>
    <div class="row">
        <div class="col-md-3">
            <label for="{{ $isEdit ? 'editStepNumber' : 'stepNumber' }}" class="fs-form-label">Step number <span class="text-danger">*</span></label>
            <input type="number" wire:model="stepNumber" class="form-control fs-input @error('stepNumber') is-invalid @enderror" id="{{ $isEdit ? 'editStepNumber' : 'stepNumber' }}" required min="1">
            @error('stepNumber') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="{{ $isEdit ? 'editVariableName' : 'variableName' }}" class="fs-form-label">Variable name <span class="text-danger">*</span></label>
            <input type="text" wire:model="variableName" class="form-control fs-input @error('variableName') is-invalid @enderror" id="{{ $isEdit ? 'editVariableName' : 'variableName' }}" placeholder="e.g. temperature_c" required>
            @error('variableName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-5">
            <label for="{{ $isEdit ? 'editLabel' : 'label' }}" class="fs-form-label">Display label <span class="text-danger">*</span></label>
            <input type="text" wire:model="label" class="form-control fs-input @error('label') is-invalid @enderror" id="{{ $isEdit ? 'editLabel' : 'label' }}" placeholder="e.g. Temperature (°C)" required>
            @error('label') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>
</section>

<section class="fs-form-section">
    <h6 class="fs-form-section-title">Step type</h6>
    <p class="fs-form-section-hint">Choose how this step behaves when analysts run the formula worksheet.</p>
    <div class="fs-step-type-grid @error('stepType') is-invalid @enderror" id="{{ $isEdit ? 'editStepType' : 'stepType' }}">
        @foreach($this->stepTypeOptions as $value => $typeLabel)
            @php $meta = $stepTypeMeta[$value] ?? ['icon' => 'mdi-help-circle-outline', 'color' => 'input', 'hint' => '']; @endphp
            <label class="fs-step-type-tile {{ $stepType === $value ? 'is-active' : '' }} fs-step-type-tile--{{ $meta['color'] }}">
                <input type="radio"
                       wire:model.live="stepType"
                       value="{{ $value }}"
                       class="fs-step-type-input"
                       name="{{ $stepTypeRadioName }}">
                <span class="fs-step-type-icon"><i class="mdi {{ $meta['icon'] }}"></i></span>
                <span class="fs-step-type-name">{{ $typeLabel }}</span>
                <span class="fs-step-type-hint">{{ $meta['hint'] }}</span>
            </label>
        @endforeach
    </div>
    @error('stepType') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
</section>

<div class="formula-step-modal-scroll fs-form-scroll">
@if($stepType === 'derived')
    <section class="fs-config-panel fs-config-panel--derived">
        <div class="fs-config-panel-head">
            <span class="fs-config-panel-icon"><i class="mdi mdi-function-variant"></i></span>
            <div>
                <h6 class="mb-0">Expression configuration</h6>
                <p class="mb-0 small text-muted">Build the calculation using variables from earlier steps.</p>
            </div>
        </div>
        <div class="fs-config-panel-body">
            <div class="mb-3">
                <label for="{{ $isEdit ? 'editExpression' : 'expression' }}" class="form-label">Expression *</label>
                <div class="input-group">
                    <textarea wire:model="expression" class="form-control @error('expression') is-invalid @enderror" id="{{ $isEdit ? 'editExpression' : 'expression' }}" rows="3" required placeholder="e.g., (temperature * 1.8) + 32"></textarea>
                    <button type="button" wire:click="validateExpression" class="btn btn-outline-info">
                        <i class="mdi mdi-check"></i> Validate
                    </button>
                </div>
                @error('expression') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Use variable names from previous steps in your expression</div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Available Variables</h6>
                    @if(count($availableVariables) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($availableVariables as $var)
                                @php
                                    $varTypeBadge = match ($var['type']) {
                                        'input' => 'success',
                                        'derived' => 'info',
                                        'lookup' => 'warning',
                                        'checkbox' => 'primary',
                                        'parameter_result' => 'dark',
                                        default => 'secondary',
                                    };
                                @endphp
                                <div class="list-group-item px-0 py-1 border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <code class="text-primary">{{ $var['name'] }}</code>
                                            <small class="text-muted d-block">{{ $var['label'] }}</small>
                                        </div>
                                        <span class="badge badge-{{ $varTypeBadge }} badge-sm">
                                            {{ ucfirst(str_replace('_', ' ', $var['type'])) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small">No variables available from previous steps</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Global Constants</h6>
                    @if(count($globalVariables) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($globalVariables as $constant)
                                <div class="list-group-item px-0 py-1 border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <code class="text-success">{{ $constant->name }}</code>
                                            <small class="text-muted d-block">{{ $constant->description }}</small>
                                        </div>
                                        <span class="badge badge-secondary badge-sm">
                                            {{ $constant->value }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small">No global constants defined</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif

@if($stepType === 'lookup')
    <section class="fs-config-panel fs-config-panel--lookup">
        <div class="fs-config-panel-head">
            <span class="fs-config-panel-icon"><i class="mdi mdi-table-search"></i></span>
            <div>
                <h6 class="mb-0">Lookup configuration</h6>
                <p class="mb-0 small text-muted">Map keys to lookup table columns for automatic resolution.</p>
            </div>
        </div>
        <div class="fs-config-panel-body">
            <div class="mb-3">
                <label for="{{ $isEdit ? 'editLookupTableId' : 'lookupTableId' }}" class="form-label"><i class="mdi mdi-table-search text-primary"></i> Lookup Table *</label>
                <select wire:model.live="lookupTableId" class="form-select modern-select no-select2 @error('lookupTableId') is-invalid @enderror" id="{{ $isEdit ? 'editLookupTableId' : 'lookupTableId' }}" required>
                    <option value="">Select Lookup Table</option>
                    @foreach($lookupTables as $table)
                        <option value="{{ $table->id }}">{{ $table->name }}</option>
                    @endforeach
                </select>
                @error('lookupTableId') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @if($lookupTableId)
                @php
                    $selectedTable = $lookupTables->firstWhere('id', $lookupTableId);
                @endphp
                @if($selectedTable)
                    <div class="alert alert-{{ $selectedTable->lookup_type === 'range_based' ? 'warning' : 'info' }}">
                        <h6 class="alert-heading">
                            <i class="mdi mdi-information"></i> Table Information
                        </h6>
                        <div class="mb-2">
                            <strong>Type:</strong>
                            @if($selectedTable->lookup_type === 'range_based')
                                <span class="badge badge-warning">
                                    <i class="mdi mdi-chart-line"></i> Range-Based
                                </span>
                                <br><small class="text-muted">Variable: {{ $selectedTable->range_variable_name }}</small>
                            @else
                                <span class="badge badge-primary">
                                    <i class="mdi mdi-key"></i> Key-Value Comparison
                                </span>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>{{ $selectedTable->lookup_type === 'range_based' ? 'Range Keys:' : 'Key Columns:' }}</strong>
                                @foreach($selectedTable->key_columns as $column)
                                    <span class="badge badge-secondary me-1">{{ $column }}</span>
                                @endforeach
                            </div>
                            <div class="col-md-6">
                                <strong>Value Column:</strong>
                                <span class="badge badge-primary">{{ $selectedTable->value_column }}</span>
                                @if($selectedTable->value_interpretation_column)
                                    <br><small>+ {{ $selectedTable->value_interpretation_column }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($selectedTable && $selectedTable->lookup_type === 'range_based')
                    <div class="alert alert-warning mb-3">
                        <i class="mdi mdi-information"></i>
                        <strong>Range-Based Lookup:</strong> Select which variable to check against the ranges.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Variable to Check *</label>
                        <select wire:model="lookupConfig.range_variable" class="form-select modern-select no-select2">
                            <option value="">Select Variable...</option>
                            @foreach($availableVariables as $varName => $varData)
                                <option value="{{ $varName }}">{{ $varData['label'] }} ({{ $varName }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Select which formula variable to check against ranges in "{{ $selectedTable->name }}"
                            <br>(Expected range variable: <code>{{ $selectedTable->range_variable_name }}</code>)
                        </small>
                    </div>

                    @if($selectedTable->value_interpretation_column)
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       wire:model="lookupConfig.return_interpretation"
                                       class="form-check-input"
                                       id="{{ $isEdit ? 'editReturnInterpretation' : 'returnInterpretation' }}">
                                <label class="form-check-label" for="{{ $isEdit ? 'editReturnInterpretation' : 'returnInterpretation' }}">
                                    Return Interpretation Text
                                    <span class="badge badge-info">{{ $selectedTable->value_interpretation_column }}</span>
                                </label>
                            </div>
                            <small class="text-muted">
                                If checked, returns the text interpretation instead of the numeric value
                            </small>
                        </div>
                    @endif

                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Preview</h6>
                            <code>
                                IF {{ $lookupConfig['range_variable'] ?? 'variable' }} is in range →
                                Return {{ ($lookupConfig['return_interpretation'] ?? false) ? 'interpretation' : 'value' }}
                            </code>
                        </div>
                    </div>
                @elseif(!empty($lookupConfig))
                    <div class="mb-3">
                        <label class="form-label">Key Configuration</label>
                        <div class="card bg-white">
                            <div class="card-body">
                                @foreach($lookupConfig['key_expressions'] as $key => $value)
                                    <div class="mb-3">
                                        <label class="form-label small">
                                            {{ $selectedTable->key_label ?: $key }}
                                            <span class="text-muted">({{ $key }})</span>
                                        </label>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="mdi mdi-key"></i>
                                                    </span>
                                                    <input type="text"
                                                           wire:model="lookupConfig.key_expressions.{{ $key }}"
                                                           class="form-control lookup-key-input"
                                                           id="lookup-key-{{ $key }}-{{ $lookupKeySuffix }}"
                                                           data-key="{{ $key }}"
                                                           placeholder="Expression or value (e.g., temperature, {{ $key }})">
                                                </div>
                                                <div class="form-text">Use variable names or direct values</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small text-muted">Quick Select</label>
                                                <select class="form-select form-select-sm modern-select-sm quick-select-variable"
                                                        data-target-input="lookup-key-{{ $key }}-{{ $lookupKeySuffix }}">
                                                    <option value="">Select Variable</option>
                                                    @foreach($availableVariables as $varName => $varData)
                                                        <option value="{{ $varName }}">{{ $varData['label'] }} ({{ $varName }})</option>
                                                    @endforeach
                                                    @foreach($globalVariables as $constant)
                                                        <option value="{{ $constant->name }}">{{ $constant->name }} ({{ $constant->value }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>
@endif

@if($stepType === 'parameter_result')
    <section class="fs-config-panel fs-config-panel--result">
        <div class="fs-config-panel-head">
            <span class="fs-config-panel-icon"><i class="mdi mdi-flask-outline"></i></span>
            <div>
                <h6 class="mb-0">Analyte selection</h6>
                <p class="mb-0 small text-muted">Link this step to the analyte whose result will be posted.</p>
            </div>
        </div>
        <div class="fs-config-panel-body">
            <div class="mb-3">
                <label for="{{ $isEdit ? 'editAnalyteId' : 'analyteId' }}" class="form-label"><i class="mdi mdi-flask text-primary"></i> Select Analyte *</label>
                <select wire:model.live="analyteId" class="form-select modern-select no-select2 @error('analyteId') is-invalid @enderror" id="{{ $isEdit ? 'editAnalyteId' : 'analyteId' }}" required>
                    <option value="">Select Analyte</option>
                    @foreach($analytes as $analyte)
                        <option value="{{ $analyte->id }}">{{ $analyte->name }} ({{ $analyte->code }})</option>
                    @endforeach
                </select>
                @error('analyteId') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </section>
@endif

@include('livewire.formulars.partials.formula-step-type-config')

<section class="fs-form-section mb-0">
    <label for="{{ $isEdit ? 'editDescription' : 'description' }}" class="fs-form-label">Description <span class="text-muted fw-normal">(optional)</span></label>
    <textarea wire:model="description" class="form-control fs-input @error('description') is-invalid @enderror" id="{{ $isEdit ? 'editDescription' : 'description' }}" rows="2" placeholder="Optional guidance for analysts configuring this step…"></textarea>
    @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
</section>
</div>
