@php
    $steps = $this->walkInWizardSteps;
    $activeIndex = $walkInActiveStepIndex;
    $progress = $this->walkInWizardProgress;
    $useRftStepper = (bool) ($this->pageMode ?? false);
@endphp

@if ($useRftStepper)
    <div class="rft-wizard-progress mb-3" wire:key="walk-in-rft-progress-{{ $selectedSampleTypeId }}-{{ $activeIndex }}">
        <div class="rft-wizard-progress__meta d-flex justify-content-between align-items-center mb-1">
            <span class="text-muted small">Progress</span>
            <strong class="small">{{ $progress }}%</strong>
        </div>
        <div class="rft-wizard-progress__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}" aria-label="Form progress">
            <div class="rft-wizard-progress__bar" style="width: {{ $progress }}%;"></div>
        </div>
    </div>

    <nav class="rft-wizard-stepper mb-3" aria-label="Form sections" wire:key="walk-in-rft-wizard-{{ $selectedSampleTypeId }}">
        <ol class="rft-wizard-stepper__list rft-wizard-stepper__list--spread">
            @foreach($steps as $step)
                @php
                    $stepState = $step['index'] < $activeIndex
                        ? 'is-complete'
                        : ($step['index'] === $activeIndex ? 'is-active' : 'is-upcoming');
                @endphp
                <li class="rft-wizard-stepper__item {{ $stepState }}">
                    @if($step['index'] <= $activeIndex)
                        <button
                            type="button"
                            class="rft-wizard-stepper__link border-0 bg-transparent"
                            wire:click="goToWalkInStep({{ $step['index'] }})"
                            title="{{ $step['title'] }}"
                        >
                            <span class="rft-wizard-stepper__index" aria-hidden="true">
                                @if($step['index'] < $activeIndex)
                                    <i class="mdi mdi-check"></i>
                                @else
                                    {{ $step['index'] + 1 }}
                                @endif
                            </span>
                            <span class="rft-wizard-stepper__label">{{ $step['label'] }}</span>
                        </button>
                    @else
                        <span class="rft-wizard-stepper__upcoming" title="{{ $step['title'] }}">
                            <span class="rft-wizard-stepper__index" aria-hidden="true">{{ $step['index'] + 1 }}</span>
                            <span class="rft-wizard-stepper__label">{{ $step['label'] }}</span>
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@else
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
@endif
