<div class="receive-sample-modal-body">
    @if ($selectedFormInstanceIds === [])
        <div class="receive-sample-alert receive-sample-alert--empty">
            No requests selected.
        </div>
    @else
        <section class="receive-sample-selected">
            <p class="receive-sample-section-label">Selected requests</p>
            <div class="receive-sample-chips">
                @foreach ($selectedFormSummaries as $summary)
                    <span class="receive-sample-chip">
                        <span class="receive-sample-chip-code">{{ $summary['label'] ?? 'Request' }}</span>
                        @if (!empty($summary['customer']))
                            <span class="receive-sample-chip-meta">{{ $summary['customer'] }}</span>
                        @endif
                    </span>
                @endforeach
                @if ($selectedFormSummaries === [] && $selectedFormInstanceIds !== [])
                    <span class="receive-sample-chip receive-sample-chip--muted">{{ count($selectedFormInstanceIds) }} request(s)</span>
                @endif
            </div>
            <p class="receive-sample-selected-hint text-muted small mb-0">
                The customer will be asked to provide more information. Requests move to the <strong>Request Additional Info</strong> tab.
            </p>
        </section>

        <div class="form-group">
            <label class="receive-sample-field-label" for="request-additional-info-remarks">
                Message to customer <span class="text-danger">*</span>
            </label>
            <textarea
                id="request-additional-info-remarks"
                wire:model="remarks"
                rows="4"
                class="form-control form-control-sm @error('remarks') is-invalid @enderror"
                placeholder="Describe what additional information or documents you need from the customer…"
            ></textarea>
            @error('remarks')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-0">
            <div class="custom-control custom-checkbox">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="request-additional-info-notify"
                    wire:model="notifyCustomer"
                >
                <label class="custom-control-label" for="request-additional-info-notify">
                    Notify customer by email
                </label>
            </div>
            <p class="text-muted small mb-0 mt-1">If no email is on file, the status will still be updated.</p>
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
            wire:click="confirmRequestAdditionalInfo"
            wire:loading.attr="disabled"
            @if ($selectedFormInstanceIds === []) disabled @endif
        >
            <span wire:loading.remove wire:target="confirmRequestAdditionalInfo">
                <i class="mdi mdi-file-document-edit-outline mr-1"></i> Request more info
            </span>
            <span wire:loading wire:target="confirmRequestAdditionalInfo">
                <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                Sending…
            </span>
        </button>
    </div>
</div>
