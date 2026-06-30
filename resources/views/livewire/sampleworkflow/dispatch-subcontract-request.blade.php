<div class="receive-sample-modal-body">
    @if ($selectedFormInstanceIds === [])
        <div class="receive-sample-alert receive-sample-alert--empty">
            No subcontracting request selected.
        </div>
    @else
        <section class="receive-sample-selected">
            <p class="receive-sample-section-label">Selected request</p>
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
            <p class="receive-sample-selected-hint text-muted small mb-0">
                Dispatched subcontracting requests remain visible under <strong>Dispatched</strong> and move into the existing <strong>Samples In Lab</strong> workflow.
            </p>
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
            class="btn btn-warning btn-sm receive-sample-submit-btn"
            wire:click="confirmDispatch"
            wire:loading.attr="disabled"
            @if ($selectedFormInstanceIds === []) disabled @endif
        >
            <span wire:loading.remove wire:target="confirmDispatch">
                <i class="mdi mdi-truck-delivery-outline mr-1"></i> Mark dispatched
            </span>
            <span wire:loading wire:target="confirmDispatch">
                <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                Dispatching…
            </span>
        </button>
    </div>
</div>