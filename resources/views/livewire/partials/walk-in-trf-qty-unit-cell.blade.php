@php
    $rowIndex = $rowIndex ?? 0;
    $quantityField = $quantityField ?? 'sample_quantity';
    $unitField = $unitField ?? 'sample_quantity_unit';
@endphp
<div class="d-flex walk-in-trf-qty-unit rft-gap-xs">
    <input
        type="number"
        step="0.01"
        min="0"
        wire:model="formData.{{ $quantityField }}.{{ $rowIndex }}"
        class="form-control form-control-sm rft-qty-input"
        placeholder="Qty"
    >
    <select
        wire:model="formData.{{ $unitField }}.{{ $rowIndex }}"
        class="form-control form-control-sm no-select2 rft-unit-input"
    >
        <option value="">Unit</option>
        @foreach($this->reportingUnits as $unit)
            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
        @endforeach
    </select>
</div>
