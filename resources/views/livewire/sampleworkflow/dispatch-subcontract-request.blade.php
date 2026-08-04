<div class="receive-sample-modal-body">
    @if ($selectedFormInstanceIds === [])
        <div class="receive-sample-alert receive-sample-alert--empty">
            No subcontracting request selected.
        </div>
    @else
        <section class="receive-sample-selected">
            <p class="receive-sample-section-label" style="margin-bottom: 1rem;">Selected request</p>
            <div class="receive-sample-chips">
                @foreach ($selectedFormSummaries as $summary)
                    <span class="receive-sample-chip">
                        <span class="receive-sample-chip-code">{{ $summary['label'] ?? 'Request' }}</span>
                        @if (!empty($summary['customer']))
                            <span class="receive-sample-chip-meta">{{ $summary['customer'] }}</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </section>

        <div class="alert alert-light border mt-3 mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
                <div>
                    <strong class="d-block">Need the system label first?</strong>
                    <span class="text-muted small">Open the generated request label in a new tab, print it if needed, then scan the barcode from that label.</span>
                </div>
                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    wire:click="openLabelInNewTab"
                    @if(empty($labelUrl)) disabled @endif
                >
                    <i class="mdi mdi-printer mr-1"></i> Generate / print label
                </button>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="receive-sample-field-label" for="subcontract-dispatch-barcode">
                Scan barcode <span class="text-danger">*</span>
            </label>
            <input
                id="subcontract-dispatch-barcode"
                type="text"
                wire:model.defer="barcode"
                wire:keydown.enter.prevent="confirmDispatch"
                class="form-control form-control-sm @error('barcode') is-invalid @enderror"
                placeholder="Scan the barcode from the generated system label"
                autocomplete="off"
            >
            @error('barcode')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mt-3 mb-0">
            <label class="receive-sample-field-label" for="subcontract-dispatch-labs">
                Receiving lab(s) <span class="text-danger">*</span>
            </label>

            @if ($availableLabs === [])
                <div class="alert alert-warning border mb-0">
                    No active external labs are available. Add labs under <strong>Labs → External (Subcontracted)</strong> first.
                </div>
            @else
                <div id="subcontract-dispatch-labs" class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                    @foreach ($availableLabs as $lab)
                        <div class="form-check mb-1" wire:key="subcontract-lab-{{ $lab['id'] }}">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                value="{{ $lab['id'] }}"
                                id="subcontract-dispatch-lab-{{ $lab['id'] }}"
                                wire:model.live="selectedLabIds"
                            >
                            <label class="form-check-label" for="subcontract-dispatch-lab-{{ $lab['id'] }}">
                                {{ $lab['label'] }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted">
                    Choose one or more external labs that will receive the sample(s).
                </small>
            @endif

            @error('selectedLabIds')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('selectedLabIds.*')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mt-3 mb-0">
            <label class="receive-sample-field-label">
                Tests per receiving lab <span class="text-danger">*</span>
            </label>

            @if ($subcontractedTests === [])
                <div class="alert alert-light border mb-0">
                    <small class="text-muted">No subcontracted tests were found on this request. You can still dispatch to the selected lab(s).</small>
                </div>
            @elseif ($this->selectedLabOptions === [])
                <div class="alert alert-light border mb-0">
                    <small class="text-muted">Select receiving lab(s) above, then tick which tests each lab will perform.</small>
                </div>
            @else
                <div class="d-flex flex-column" style="gap: 12px;">
                    @foreach ($this->selectedLabOptions as $lab)
                        <div class="border rounded p-3" wire:key="lab-tests-{{ $lab['id'] }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <strong>
                                    <i class="mdi mdi-earth text-muted mr-1"></i>
                                    {{ $lab['label'] }}
                                </strong>
                                <small class="text-muted">
                                    {{ count($labTestIds[$lab['id']] ?? []) }} /
                                    {{ count($subcontractedTests) }} tests
                                </small>
                            </div>

                            @foreach ($subcontractedTests as $test)
                                <div class="form-check mb-1" wire:key="lab-{{ $lab['id'] }}-test-{{ $test['id'] }}">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="{{ $test['id'] }}"
                                        id="lab-{{ $lab['id'] }}-test-{{ $test['id'] }}"
                                        wire:model.live="labTestIds.{{ $lab['id'] }}"
                                    >
                                    <label class="form-check-label" for="lab-{{ $lab['id'] }}-test-{{ $test['id'] }}">
                                        <span class="d-block">{{ $test['label'] }}</span>
                                        <small class="text-muted">{{ $test['analysis_type'] }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted">
                    Tick the tests each lab will perform. A test can only be assigned to one lab.
                    @if(count($this->selectedLabOptions) === 1)
                        With one lab selected, all tests are selected by default.
                    @endif
                </small>
            @endif

            @error('labTestIds')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    @endif

    @error('selection')
        <div class="alert alert-danger border-0 mt-3 mb-0">{{ $message }}</div>
    @enderror

    <div class="receive-sample-modal-footer d-flex justify-content-end align-items-center mt-4 pt-3">
        <button type="button" class="btn btn-light btn-sm mr-2" data-dismiss="modal">
            Cancel
        </button>
        <button
            type="button"
            class="btn btn-primary btn-sm receive-sample-submit-btn"
            wire:click="confirmDispatch"
            wire:loading.attr="disabled"
            @if ($selectedFormInstanceIds === [] || $availableLabs === []) disabled @endif
        >
            <span wire:loading.remove wire:target="confirmDispatch">
                <i class="mdi mdi-truck-delivery-outline mr-1"></i>
                Dispatch
            </span>
            <span wire:loading wire:target="confirmDispatch">
                <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                Saving…
            </span>
        </button>
    </div>
</div>
