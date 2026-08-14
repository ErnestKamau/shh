<div class="d-flex rv-qty-unit-wrap" style="gap: 8px;">
    <input
        type="number"
        step="0.01"
        min="0"
        wire:model.defer="editingRowFields.sample_quantity"
        class="form-control form-control-sm"
        style="flex: 1;"
        placeholder="Qty"
    >
    <select
        wire:model.defer="editingRowFields.sample_quantity_unit"
        class="form-control form-control-sm no-select2"
        style="flex: 1;"
    >
        <option value="">Unit</option>
        @foreach($this->reportingUnits as $unit)
            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
        @endforeach
    </select>
</div>
