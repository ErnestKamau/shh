{{-- Qty + searchable unit Select2 (gallery .ls-dd-search in dropdown list, not native menu) --}}
@php
    $qtyValue = $editingRowFields['sample_quantity'] ?? '';
    $unitValue = (string) ($editingRowFields['sample_quantity_unit'] ?? '');
    $unitOptions = $this->reportingUnits->mapWithKeys(fn ($unit) => [$unit->name => $unit->name])->all();
    if ($unitValue !== '' && ! array_key_exists($unitValue, $unitOptions)) {
        $unitOptions = [$unitValue => $unitValue] + $unitOptions;
    }
@endphp
<div class="ls-field rv-qty-unit-field {{ filled($qtyValue) || $unitValue !== '' ? 'is-success' : '' }}">
    <div class="ls-field__control rv-qty-unit-control">
        <input
            type="number"
            step="0.01"
            min="0"
            id="edit-row-sample_quantity"
            class="ls-field__input"
            wire:model.defer="editingRowFields.sample_quantity"
            placeholder="Qty"
        >
        <span class="ls-field__affix ls-field__affix--suffix rv-qty-unit-select2" wire:ignore>
            <select
                id="edit-row-sample_quantity_unit"
                class="form-control form-control-sm livewire-select2 ls-select2-single-dropdown-search-el"
                aria-label="Unit"
                data-wire-field="editingRowFields.sample_quantity_unit"
                data-placeholder="Unit"
                data-ls-single-dropdown-search="1"
                data-selected-values="{{ json_encode(array_values(array_filter([$unitValue], fn ($v) => $v !== ''))) }}"
                style="width: 100%;"
            >
                <option value=""></option>
                @foreach($unitOptions as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected($unitValue === (string) $optValue)>{{ $optLabel }}</option>
                @endforeach
            </select>
        </span>
    </div>
</div>
