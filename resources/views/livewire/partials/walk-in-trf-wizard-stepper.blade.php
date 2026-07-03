@php
    $steps = $this->walkInWizardSteps;
    $activeIndex = $walkInActiveStepIndex;
    $progress = $this->walkInWizardProgress;
@endphp

<div class="walk-in-trf-wizard" wire:key="walk-in-trf-wizard-{{ $selectedSampleTypeId }}">
    <div class="walk-in-trf-wizard__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}" aria-label="Form progress">
        <div class="walk-in-trf-wizard__progress-bar" style="width: {{ $progress }}%;"></div>
    </div>

    <nav class="walk-in-trf-wizard__steps" role="tablist" aria-label="Test request form steps">
        <div class="walk-in-trf-wizard__track" aria-hidden="true">
            <div class="walk-in-trf-wizard__track-fill" style="width: {{ $progress }}%;"></div>
        </div>

        @foreach($steps as $step)
            @php
                $isActive = $activeIndex === $step['index'];
                $isDone = $activeIndex > $step['index'];
            @endphp
            <button
                type="button"
                role="tab"
                id="walk-in-trf-step-tab-{{ $step['index'] }}"
                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                aria-controls="walk-in-trf-step-panel-{{ $step['index'] }}"
                class="walk-in-trf-wizard__step {{ $isActive ? 'is-active' : '' }} {{ $isDone ? 'is-done' : '' }}"
                wire:click="goToWalkInStep({{ $step['index'] }})"
                @if($step['index'] > $activeIndex + 1) disabled @endif
            >
                <span class="walk-in-trf-wizard__step-index">
                    @if($isDone)
                        <i class="mdi mdi-check" aria-hidden="true"></i>
                    @else
                        {{ $step['index'] + 1 }}
                    @endif
                </span>
                <span class="walk-in-trf-wizard__step-label">{{ $step['label'] }}</span>
            </button>
        @endforeach
    </nav>
</div>
