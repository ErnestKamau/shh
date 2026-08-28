@php
    $rowIndex = $rowIndex ?? 0;
    $quantityField = $quantityField ?? 'sample_quantity';
    $unitField = $unitField ?? 'sample_quantity_unit';
    $quantityWire = $quantityWire ?? ('formData.'.$quantityField.'.'.$rowIndex);
    $unitWire = $unitWire ?? ('formData.'.$unitField.'.'.$rowIndex);
    $nativeSelect = (bool) ($nativeSelect ?? false);

    if (! isset($qtyValue)) {
        $qtyValue = data_get($this->formData ?? [], $quantityField.'.'.$rowIndex, '');
    }
    if (! isset($unitValue)) {
        $unitValue = (string) data_get($this->formData ?? [], $unitField.'.'.$rowIndex, '');
    }
    $unitValue = (string) $unitValue;

    if (! isset($unitOptions) || ! is_array($unitOptions)) {
        $unitOptions = ($this->reportingUnits ?? collect())->mapWithKeys(fn ($unit) => [$unit->name => $unit->name])->all();
    } else {
        // Accept list of {value,label} or value=>label map.
        $normalized = [];
        $isList = $unitOptions === [] || array_keys($unitOptions) === range(0, count($unitOptions) - 1);
        if ($isList) {
            foreach ($unitOptions as $option) {
                if (is_array($option)) {
                    $value = (string) ($option['value'] ?? '');
                    if ($value === '') {
                        continue;
                    }
                    $normalized[$value] = (string) ($option['label'] ?? $value);
                } else {
                    $value = (string) $option;
                    if ($value !== '') {
                        $normalized[$value] = $value;
                    }
                }
            }
            $unitOptions = $normalized;
        }
    }
    if ($unitValue !== '' && ! array_key_exists($unitValue, $unitOptions)) {
        $unitOptions = [$unitValue => $unitValue] + $unitOptions;
    }
    $unitFieldId = 'field_'.str_replace('.', '_', $unitWire);
@endphp
{{-- Edit-modal Qty / Unit: ls-field affix + unit select (Select2 or native) --}}
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
            wire:model.defer="{{ $quantityWire }}"
            placeholder="Qty"
        >
        <span class="ls-field__affix ls-field__affix--suffix rv-qty-unit-select2" @if(! $nativeSelect) wire:ignore @endif>
            <select
                id="{{ $unitFieldId }}"
                @if($nativeSelect)
                    class="form-control form-control-sm"
                    wire:model.defer="{{ $unitWire }}"
                @else
                    class="form-control form-control-sm livewire-select2 ls-select2-single-dropdown-search-el walk-in-ls-select2"
                    data-wire-field="{{ $unitWire }}"
                    data-placeholder="Unit"
                    data-ls-single-dropdown-search="1"
                    data-selected-values="{{ json_encode(array_values(array_filter([$unitValue], fn ($v) => $v !== ''))) }}"
                @endif
                aria-label="Unit"
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
