@php
    $rowIndex = $rowIndex ?? 0;
    $quantityField = $quantityField ?? 'sample_quantity';
    $unitField = $unitField ?? 'sample_quantity_unit';
    $qtyValue = data_get($this->formData ?? [], $quantityField.'.'.$rowIndex, '');
    $unitValue = (string) data_get($this->formData ?? [], $unitField.'.'.$rowIndex, '');
    $unitOptions = $this->reportingUnits->mapWithKeys(fn ($unit) => [$unit->name => $unit->name])->all();
    if ($unitValue !== '' && ! array_key_exists($unitValue, $unitOptions)) {
        $unitOptions = [$unitValue => $unitValue] + $unitOptions;
    }
    $unitFieldId = 'field_'.$unitField.'_'.$rowIndex;
@endphp
{{-- Edit-modal Qty / Unit: ls-field affix + searchable unit Select2 --}}
<div class="ls-field rv-qty-unit-field {{ filled($qtyValue) || $unitValue !== '' ? 'is-success' : '' }}">
    @if(! ($hideLabel ?? false))
        <label class="ls-field__label">Qty / Unit</label>
    @endif
    <div class="ls-field__control rv-qty-unit-control">
        <input
            type="number"
            step="0.01"
            min="0"
            class="ls-field__input"
            wire:model.defer="formData.{{ $quantityField }}.{{ $rowIndex }}"
            placeholder="Qty"
        >
        <span class="ls-field__affix ls-field__affix--suffix rv-qty-unit-select2" wire:ignore>
            <select
                id="{{ $unitFieldId }}"
                class="form-control form-control-sm livewire-select2 ls-select2-single-dropdown-search-el walk-in-ls-select2"
                aria-label="Unit"
                data-wire-field="formData.{{ $unitField }}.{{ $rowIndex }}"
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
