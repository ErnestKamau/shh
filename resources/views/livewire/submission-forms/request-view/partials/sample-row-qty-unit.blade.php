{{-- Qty + searchable unit Select2 --}}
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
    <div wire:ignore class="rv-sample-row-select2-wrap" style="flex: 1;">
        <select
            id="edit-row-sample_quantity_unit"
            class="form-control form-control-sm livewire-select2"
            data-wire-field="editingRowFields.sample_quantity_unit"
            data-placeholder="Unit"
            data-selected-values="{{ json_encode(array_values(array_filter([(string) ($editingRowFields['sample_quantity_unit'] ?? '')]))) }}"
        >
            <option value="">Unit</option>
            @foreach($this->reportingUnits as $unit)
                <option value="{{ $unit->name }}" @selected((string) ($editingRowFields['sample_quantity_unit'] ?? '') === (string) $unit->name)>
                    {{ $unit->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
