{{-- Per-sample additional label/value detail rows --}}
@php
    $detailRows = $this->additionalDetailsRowsForSample((int) $rowIndex);
@endphp
<div
    class="rft-additional-details mt-3"
    id="rft-additional-details-{{ $rowIndex }}"
    wire:key="rft-additional-details-{{ $rowIndex }}"
>
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="rft-sample-section-label rft-sample-section-label--sample mb-0 border-0 pt-0">
            Additional details
        </div>
        <button
            type="button"
            class="btn btn-sm btn-outline-primary"
            title="Add detail column"
            aria-label="Add additional detail"
            x-on:click.prevent="
                const y = window.scrollY || window.pageYOffset || 0;
                $wire.addAdditionalDetailRow({{ (int) $rowIndex }}).then(() => {
                    requestAnimationFrame(() => {
                        window.scrollTo(0, y);
                        document.getElementById('rft-additional-details-{{ (int) $rowIndex }}')
                            ?.scrollIntoView({ block: 'nearest' });
                    });
                })
            "
        >
            <i class="mdi mdi-plus" aria-hidden="true"></i> Add
        </button>
    </div>
    @forelse($detailRows as $detailIndex => $detail)
        <div class="rft-additional-details__row mb-2" wire:key="rft-additional-detail-{{ $rowIndex }}-{{ $detailIndex }}">
            <input
                type="text"
                class="form-control form-control-sm rft-additional-details__label"
                wire:model.defer="formData.additional_details.{{ $rowIndex }}.{{ $detailIndex }}.label"
                placeholder="Label"
                aria-label="Detail label"
            >
            <input
                type="text"
                class="form-control form-control-sm rft-additional-details__value"
                wire:model.defer="formData.additional_details.{{ $rowIndex }}.{{ $detailIndex }}.value"
                placeholder="Value"
                aria-label="Detail value"
            >
            <button
                type="button"
                class="btn btn-sm btn-outline-danger"
                title="Remove"
                aria-label="Remove detail"
                x-on:click.prevent="
                    const y = window.scrollY || window.pageYOffset || 0;
                    $wire.removeAdditionalDetailRow({{ (int) $rowIndex }}, {{ (int) $detailIndex }}).then(() => {
                        requestAnimationFrame(() => window.scrollTo(0, y));
                    })
                "
            >
                <i class="mdi mdi-close" aria-hidden="true"></i>
            </button>
        </div>
    @empty
        <p class="text-muted small mb-0">Optional — add custom label/value fields for this sample.</p>
    @endforelse
</div>
