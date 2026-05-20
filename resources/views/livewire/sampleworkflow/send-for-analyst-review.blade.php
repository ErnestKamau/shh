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
                Requests move to the <strong>In Review</strong> tab for analyst review.
            </p>
        </section>

        <div class="form-group mb-0">
            <label class="receive-sample-field-label" for="analyst-review-comment">
                Comment
            </label>
            <textarea
                id="analyst-review-comment"
                wire:model="comment"
                rows="3"
                class="form-control form-control-sm @error('comment') is-invalid @enderror"
                placeholder="Optional note for the analyst (context, priority, etc.)"
            ></textarea>
            @error('comment')
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
            wire:click="confirmSendForAnalystReview"
            wire:loading.attr="disabled"
            @if ($selectedFormInstanceIds === []) disabled @endif
        >
            <span wire:loading.remove wire:target="confirmSendForAnalystReview">
                <i class="mdi mdi-clipboard-arrow-right mr-1"></i> Send for Analyst review
            </span>
            <span wire:loading wire:target="confirmSendForAnalystReview">
                <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                Sending…
            </span>
        </button>
    </div>
</div>
