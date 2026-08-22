@if (! $this->isPhysicalCheckIn && $this->isWalkInCaptureReady && $this->walkInTotalSteps > 0)
    <div class="rft-wizard-nav d-flex align-items-center flex-wrap">
        @if ($pageMode ?? false)
            @php
                $cancelRoute = ($plannerMode ?? false)
                    ? (($wizardOnly ?? false)
                        ? route('system-planner.fill-sampling-forms')
                        : route('system-planner.dashboard'))
                    : (($wizardOnly ?? false)
                        ? route('sample-workflow.request-for-testing')
                        : route('sample-workflow', ['status' => 'Samples Receiving']));
            @endphp
            <a href="{{ $cancelRoute }}" class="btn btn-sm btn-light">
                Cancel
            </a>
        @else
            <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancel</button>
        @endif

        @if (! $this->walkInIsFirstStep)
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                wire:click="prevWalkInStep"
                wire:loading.attr="disabled"
                wire:target="prevWalkInStep,nextWalkInStep,goToWalkInStep,confirmReceive"
            >
                <i class="mdi mdi-arrow-left mr-1" aria-hidden="true"></i> Back
            </button>
        @endif

        @if (! $this->walkInIsLastStep)
            <button
                type="button"
                class="btn btn-sm btn-primary"
                wire:loading.attr="disabled"
                wire:target="prevWalkInStep,nextWalkInStep,goToWalkInStep,confirmReceive"
                x-on:click.prevent="(async () => { try { if (typeof window.syncTrfSignaturesBeforeSubmit === 'function') { window.syncTrfSignaturesBeforeSubmit(); } } catch (error) { console.error('TRF step sync failed', error); } $wire.nextWalkInStep(); })()"
            >
                <span wire:loading.remove wire:target="nextWalkInStep">
                    Continue
                    <i class="mdi mdi-arrow-right ml-1" aria-hidden="true"></i>
                </span>
                <span wire:loading wire:target="nextWalkInStep">
                    <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                    Checking…
                </span>
            </button>
        @else
            <button
                type="button"
                class="btn btn-sm btn-primary receive-sample-submit-btn"
                wire:loading.attr="disabled"
                wire:target="prevWalkInStep,nextWalkInStep,goToWalkInStep,confirmReceive"
                x-on:click.prevent="(async () => { try { if (typeof window.flushWalkInParameterPickers === 'function') { await window.flushWalkInParameterPickers(); } if (typeof window.syncTrfSignaturesBeforeSubmit === 'function') { window.syncTrfSignaturesBeforeSubmit(); } } catch (error) { console.error('TRF pre-submit sync failed', error); } $wire.confirmReceive(); })()"
            >
                <span wire:loading.remove wire:target="confirmReceive">
                    <i class="mdi mdi-package-variant-closed mr-1" aria-hidden="true"></i>
                    {{ ($plannerMode ?? false) ? 'Submit sampling form' : ($this->isOfflineIntake() ? 'Save' : 'Submit') }}
                </span>
                <span wire:loading wire:target="confirmReceive">
                    <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                    Processing…
                </span>
            </button>
        @endif
    </div>
@endif
