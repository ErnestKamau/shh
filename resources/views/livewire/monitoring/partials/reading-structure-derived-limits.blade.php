@php
    $scopeOptions = $this->derivedScopeOptions;
@endphp

<div class="rs-derived-limits mt-4 pt-3 border-top">
    <h6 class="text-muted mb-2 small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Equipment limits</h6>
    <p class="text-muted small mb-3">
        Minimum and maximum values are loaded from the equipment selected in step 3. Adjust if this derived step needs different limits.
    </p>

    @if($scopeOptions === [])
        <div class="alert alert-warning py-2 px-3 small mb-0">
            <i class="mdi mdi-alert-outline me-1"></i>
            Select at least one equipment item in step 3 to configure limits.
        </div>
    @else
        @if(count($scopeOptions) > 1)
            <div class="mb-3">
                <label class="fs-form-label">Load limits from</label>
                <select wire:model.live="readingDerivedScopeSourceId" class="form-select fs-input">
                    @foreach($scopeOptions as $option)
                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" wire:model="readingDerivedScopeSourceId" value="{{ $scopeOptions[0]['id'] }}">
            <p class="small text-muted mb-3">
                <i class="mdi mdi-link-variant me-1"></i> Source: <strong>{{ $scopeOptions[0]['label'] }}</strong>
            </p>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="fs-form-label">Value Type</label>
                <div class="rs-readonly-field">
                    <span class="badge bg-light text-dark border">
                        {{ $this->equipmentValueTypeLabel($readingDerivedValueType) }}
                    </span>
                </div>
                <input type="hidden" wire:model="readingDerivedValueType">
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="fs-form-label">Minimum Value <span class="text-danger">*</span></label>
                <input type="number" step="any" wire:model="readingDerivedMinimumValue" class="form-control fs-input @error('readingDerivedMinimumValue') is-invalid @enderror" placeholder="Minimum">
                @error('readingDerivedMinimumValue') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="fs-form-label">Maximum Value <span class="text-danger">*</span></label>
                <input type="number" step="any" wire:model="readingDerivedMaximumValue" class="form-control fs-input @error('readingDerivedMaximumValue') is-invalid @enderror" placeholder="Maximum">
                @error('readingDerivedMaximumValue') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        </div>
    @endif
</div>
