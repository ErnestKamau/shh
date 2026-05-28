<div class="step-content">
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h5 class="mb-1">
                <i class="mdi mdi-format-list-numbered text-primary"></i>
                Reading Structure
            </h5>
            <p class="text-muted mb-0 small">Configure the field pipeline and formulas captured for each reading (same step types as formula worksheets).</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary btn-sm rs-add-step-btn" wire:click="showCreateReadingStepModalInit">
                <i class="mdi mdi-plus"></i> Add Step
            </button>
        </div>
    </div>

    @if($readingStepMessage && ! $showCreateReadingStepModal && ! $showEditReadingStepModal)
        @include('livewire.monitoring.partials.reading-structure-step-message')
    @endif

    @error('readingSteps')
        <div class="text-danger small mb-3">{{ $message }}</div>
    @enderror

    <div class="reading-steps-card">
        @forelse($readingSteps as $step)
            @php
                $typeLabel = $this->readingStepTypeOptions[$step['step_type'] ?? 'input'] ?? ucfirst($step['step_type'] ?? 'input');
                $typeClass = match($step['step_type'] ?? 'input') {
                    'derived' => 'rs-type--derived',
                    'lookup' => 'rs-type--lookup',
                    'parameter_result' => 'rs-type--result',
                    default => 'rs-type--input',
                };
            @endphp
            <div class="reading-step-row" wire:key="reading-step-{{ $step['id'] }}">
                <div class="reading-step-row__num">{{ $step['step_number'] }}</div>
                <div class="reading-step-row__body">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="rs-type-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                <code class="rs-var-name">{{ $step['variable_name'] }}</code>
                            </div>
                            <strong class="reading-step-row__label">{{ $step['label'] }}</strong>
                            @if(!empty($step['description']))
                                <div class="text-muted small mt-1">{{ $step['description'] }}</div>
                            @endif
                            @if(($step['step_type'] ?? '') === 'derived' && !empty($step['expression']))
                                <div class="rs-expression mt-2"><code>{{ $step['expression'] }}</code></div>
                            @endif
                            @if(($step['step_type'] ?? '') === 'input' && !empty($step['variable_slug']))
                                <div class="small text-muted mt-1">Linked variable: <code>{{ $step['variable_slug'] }}</code></div>
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="showEditReadingStepModalInit('{{ $step['id'] }}')" title="Edit step">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="showDeleteReadingStepModalInit('{{ $step['id'] }}')" title="Remove step">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="reading-steps-empty">
                <div class="reading-steps-empty__icon">
                    <i class="mdi mdi-format-list-numbered"></i>
                </div>
                <h6>No reading steps defined yet</h6>
                <p class="text-muted small mb-4">Add input, derived, lookup, or parameter result steps to define how one reading is captured and calculated.</p>
                <button type="button" class="btn btn-outline-primary rs-add-first-step-btn" wire:click="showCreateReadingStepModalInit">
                    <i class="mdi mdi-plus-circle-outline"></i>
                    <span>Add First Step</span>
                </button>
            </div>
        @endforelse
    </div>

    <div class="alert alert-light border mt-4 mb-0">
        <strong>Tip:</strong> Derived and lookup steps can reference variables from earlier steps in this pipeline, plus built-in monitoring variables such as correction factor and uncertainty of measure.
    </div>

    @include('livewire.monitoring.partials.reading-structure-step-modals')
</div>

@include('livewire.monitoring.partials.reading-structure-styles')
