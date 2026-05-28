@php
    $stepTypeMeta = [
        'input' => ['icon' => 'mdi-form-textbox', 'color' => 'input', 'hint' => 'Analyst enters a value during reading capture'],
        'derived' => ['icon' => 'mdi-function-variant', 'color' => 'derived', 'hint' => 'Calculated from an expression using prior steps'],
        'lookup' => ['icon' => 'mdi-table-search', 'color' => 'lookup', 'hint' => 'Resolves a value from a configured lookup table'],
        'parameter_result' => ['icon' => 'mdi-flask-outline', 'color' => 'result', 'hint' => 'Posts results for a selected analyte parameter'],
    ];
    $variableGroups = $this->readingStepVariableGroups;
    $availableVariables = $this->getReadingStepAvailableVariables();
@endphp

<section class="fs-form-section">
    <h6 class="fs-form-section-title">Step basics</h6>
    <div class="row">
        <div class="col-md-3">
            <label class="fs-form-label">Step number <span class="text-danger">*</span></label>
            <input type="number" wire:model="readingStepNumber" class="form-control fs-input @error('readingStepNumber') is-invalid @enderror" min="1" required>
            @error('readingStepNumber') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="fs-form-label">Variable name <span class="text-danger">*</span></label>
            <input type="text" wire:model="readingVariableName" class="form-control fs-input @error('readingVariableName') is-invalid @enderror" placeholder="e.g. temperature_c" required>
            @error('readingVariableName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-5">
            <label class="fs-form-label">Display label <span class="text-danger">*</span></label>
            <input type="text" wire:model="readingLabel" class="form-control fs-input @error('readingLabel') is-invalid @enderror" placeholder="e.g. Temperature (°C)" required>
            @error('readingLabel') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-12">
            <div class="form-check">
                <input type="checkbox"
                       class="form-check-input"
                       id="readingStepShowInMonitoringLogs"
                       wire:model="readingStepShowInMonitoringLogs">
                <label class="form-check-label" for="readingStepShowInMonitoringLogs">
                    Show in Monitoring Logs capture table
                </label>
            </div>
        </div>
    </div>
</section>

<section class="fs-form-section">
    <h6 class="fs-form-section-title">Step type</h6>
    <p class="fs-form-section-hint">Choose how this step behaves when analysts capture a reading.</p>
    <div class="fs-step-type-grid @error('readingStepType') is-invalid @enderror">
        @foreach($this->readingStepTypeOptions as $value => $typeLabel)
            @php $meta = $stepTypeMeta[$value] ?? ['icon' => 'mdi-help-circle-outline', 'color' => 'input', 'hint' => '']; @endphp
            <label class="fs-step-type-tile {{ $readingStepType === $value ? 'is-active' : '' }} fs-step-type-tile--{{ $meta['color'] }}">
                <input type="radio" wire:model.live="readingStepType" value="{{ $value }}" class="fs-step-type-input">
                <span class="fs-step-type-icon"><i class="mdi {{ $meta['icon'] }}"></i></span>
                <span class="fs-step-type-name">{{ $typeLabel }}</span>
                <span class="fs-step-type-hint">{{ $meta['hint'] }}</span>
            </label>
        @endforeach
    </div>
    @error('readingStepType') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
</section>

<div class="fs-form-scroll">
    @if($readingStepType === 'input')
        <section class="fs-config-panel fs-config-panel--input">
            <div class="fs-config-panel-head">
                <span class="fs-config-panel-icon"><i class="mdi mdi-form-textbox"></i></span>
                <div>
                    <h6 class="mb-0">Input configuration</h6>
                    <p class="mb-0 small text-muted">Optionally link this input to a monitoring variable for auto-fill.</p>
                </div>
            </div>
            <div class="fs-config-panel-body">
                <label class="fs-form-label">Linked monitoring variable <span class="text-muted fw-normal">(optional)</span></label>
                <select wire:model="readingVariableSlug" class="form-select fs-input">
                    <option value="">— Manual entry only —</option>
                    @foreach($this->availableVariables as $var)
                        <option value="{{ $var['slug'] }}">{{ $var['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </section>
    @endif

    @if($readingStepType === 'derived')
        <section class="fs-config-panel fs-config-panel--derived">
            <div class="fs-config-panel-head">
                <span class="fs-config-panel-icon"><i class="mdi mdi-function-variant"></i></span>
                <div>
                    <h6 class="mb-0">Expression configuration</h6>
                    <p class="mb-0 small text-muted">Build the calculation using variables from earlier steps.</p>
                </div>
            </div>
            <div class="fs-config-panel-body">
                <label class="fs-form-label">Expression <span class="text-danger">*</span></label>
                <div class="input-group mb-3">
                    <textarea wire:model="readingExpression" class="form-control @error('readingExpression') is-invalid @enderror" rows="3" placeholder="e.g., (temperature_c * correction_factor)"></textarea>
                    <button type="button" wire:click="validateReadingStepExpression" class="btn btn-outline-info">
                        <i class="mdi mdi-check"></i> Validate
                    </button>
                </div>
                @error('readingExpression') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                <h6 class="text-muted mb-2 small mt-3">Available variables</h6>
                @if(count($availableVariables) > 0)
                    @foreach($variableGroups as $group)
                        <p class="text-muted small mb-1 fw-semibold">{{ $group['title'] }}</p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($group['variables'] as $var)
                                <button type="button" class="btn btn-sm btn-light border" wire:click="appendToReadingExpression('{{ $var['name'] }}')">
                                    <code>{{ $var['name'] }}</code>
                                    <span class="text-muted small ms-1">{{ $var['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <p class="text-muted small mb-0">Add input steps first, or define custom monitoring variables.</p>
                @endif

                @if($templateType === 'equipment')
                    @include('livewire.monitoring.partials.reading-structure-derived-limits')
                @endif
            </div>
        </section>
    @endif

    @if($readingStepType === 'lookup')
        <section class="fs-config-panel fs-config-panel--lookup">
            <div class="fs-config-panel-head">
                <span class="fs-config-panel-icon"><i class="mdi mdi-table-search"></i></span>
                <div>
                    <h6 class="mb-0">Lookup configuration</h6>
                    <p class="mb-0 small text-muted">Resolve a value from a lookup table.</p>
                </div>
            </div>
            <div class="fs-config-panel-body">
                <label class="fs-form-label">Lookup table <span class="text-danger">*</span></label>
                <select wire:model.live="readingLookupTableId" class="form-select fs-input @error('readingLookupTableId') is-invalid @enderror">
                    <option value="">Select lookup table</option>
                    @foreach($lookupTables as $table)
                        <option value="{{ $table->id }}">{{ $table->name }}</option>
                    @endforeach
                </select>
                @error('readingLookupTableId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                @if($readingLookupTableId)
                    @php $selectedTable = $lookupTables->firstWhere('id', $readingLookupTableId); @endphp
                    @if($selectedTable && $selectedTable->isRangeBased())
                        <div class="mt-3">
                            <label class="fs-form-label">Variable to check <span class="text-danger">*</span></label>
                            <select wire:model="readingLookupConfig.range_variable" class="form-select fs-input">
                                <option value="">Select variable…</option>
                                @foreach($availableVariables as $var)
                                    <option value="{{ $var['name'] }}">{{ $var['label'] }} ({{ $var['name'] }})</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif(!empty($readingLookupConfig['key_expressions']))
                        <div class="mt-3">
                            <label class="fs-form-label">Key configuration</label>
                            @foreach($readingLookupConfig['key_expressions'] as $key => $value)
                                <div class="mb-2">
                                    <label class="small text-muted">{{ $key }}</label>
                                    <input type="text" wire:model="readingLookupConfig.key_expressions.{{ $key }}" class="form-control fs-input" placeholder="Expression or variable name">
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </section>
    @endif

    @if($readingStepType === 'parameter_result')
        <section class="fs-config-panel fs-config-panel--result">
            <div class="fs-config-panel-head">
                <span class="fs-config-panel-icon"><i class="mdi mdi-flask-outline"></i></span>
                <div>
                    <h6 class="mb-0">Analyte selection</h6>
                    <p class="mb-0 small text-muted">Link this step to the analyte whose result will be posted.</p>
                </div>
            </div>
            <div class="fs-config-panel-body">
                <label class="fs-form-label">Select analyte <span class="text-danger">*</span></label>
                <select wire:model="readingAnalyteId" class="form-select fs-input @error('readingAnalyteId') is-invalid @enderror">
                    <option value="">Select analyte</option>
                    @foreach($analytes as $analyte)
                        <option value="{{ $analyte->id }}">{{ $analyte->name }} ({{ $analyte->code }})</option>
                    @endforeach
                </select>
                @error('readingAnalyteId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        </section>
    @endif

    <section class="fs-form-section mb-0">
        <label class="fs-form-label">Description <span class="text-muted fw-normal">(optional)</span></label>
        <textarea wire:model="readingDescription" class="form-control fs-input" rows="2" placeholder="Optional guidance for analysts…"></textarea>
    </section>
</div>
