@php
    $rowIdx = $rowIdx ?? 0;
@endphp
<div class="d-flex" style="gap: 4px;">
    <input
        type="number"
        step="0.01"
        min="0"
        wire:model="formData.sample_rows.{{ $rowIdx }}.sample_quantity"
        class="form-control form-control-xs"
        style="padding: 2px 5px; height: auto; font-size: 11px; width: 55%;"
        placeholder="Amt"
    >
    <select
        wire:model="formData.sample_rows.{{ $rowIdx }}.sample_quantity_unit"
        class="form-control form-control-xs"
        style="padding: 2px 5px; height: auto; font-size: 11px; width: 45%;"
    >
        <option value="">Unit</option>
        @foreach($this->reportingUnits as $unit)
            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
        @endforeach
    </select>
</div>
