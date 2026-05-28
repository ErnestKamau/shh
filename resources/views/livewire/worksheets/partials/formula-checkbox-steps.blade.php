@php
    $checkboxSteps = $formulaSteps->where('step_type', 'checkbox')->sortBy('step_number');
@endphp
@if($checkboxSteps->isNotEmpty())
<div class="card border mt-4" wire:key="formula-checkbox-steps-card">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="mdi mdi-checkbox-marked-outline text-primary"></i> Checklist steps
            <small class="text-muted font-weight-normal">(applies to all samples on this worksheet)</small>
        </h5>
    </div>
    <div class="card-body">
        @foreach($checkboxSteps as $step)
            @php
                $options = $this->checkboxOptionsForStep($step);
                $selected = $sharedCheckboxStepData[$step->id] ?? [];
            @endphp
            <div class="mb-4" wire:key="formula-checkbox-step-{{ $step->id }}">
                <label class="form-label fw-semibold d-block">{{ $step->label }}</label>
                @if($step->description)
                    <p class="text-muted small">{{ $step->description }}</p>
                @endif
                @if($options->isEmpty())
                    <p class="text-muted small mb-0">No options configured for this step.</p>
                @else
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($options as $opt)
                            @php
                                $optId = (string) $opt->id;
                                $isChecked = in_array($optId, $selected, true);
                            @endphp
                            <label class="custom-control custom-checkbox mb-0">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       @checked($isChecked)
                                       wire:click="toggleSharedCheckboxOption(@js($step->id), @js($optId))">
                                <span class="custom-control-label">{{ $opt->label }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
