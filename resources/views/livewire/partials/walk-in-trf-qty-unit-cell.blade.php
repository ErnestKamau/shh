@php
    $rowIndex = $rowIndex ?? 0;
    $inputStyle = 'padding: 2px 5px; height: auto; font-size: 10px;';
@endphp
<div class="d-flex walk-in-trf-qty-unit" style="gap: 3px;">
    <input
        type="number"
        step="0.01"
        min="0"
        wire:model="formData.sample_quantity.{{ $rowIndex }}"
        class="form-control form-control-xs"
        style="{{ $inputStyle }} width: 48%;"
        placeholder="Qty"
    >
    <select
        wire:model="formData.sample_quantity_unit.{{ $rowIndex }}"
        class="form-control form-control-xs no-select2"
        style="{{ $inputStyle }} width: 52%;"
    >
        <option value="">Unit</option>
        @foreach($this->reportingUnits as $unit)
            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
        @endforeach
    </select>
</div>
