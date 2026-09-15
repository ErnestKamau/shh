{{-- Multi Equipment ID free-text rows for Food/Water TRF collection (not Waste Water). --}}
@php
    $resolver = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class);
    $rows = is_array($this->formData['thermometer_id'] ?? null)
        ? array_values($this->formData['thermometer_id'])
        : $resolver->rowsForForm($this->formData['thermometer_id'] ?? null);
    if ($rows === []) {
        $rows = [''];
    }
@endphp
<div class="trf-sampling-equipment-ids mt-3" wire:key="trf-sampling-equipment-ids">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="ls-type-label mb-0">Equipment ID</div>
        <button type="button"
            class="btn btn-sm btn-outline-primary"
            wire:click="addSamplingEquipmentIdRow"
            title="Add equipment ID"
            aria-label="Add equipment ID">
            <i class="mdi mdi-plus" aria-hidden="true"></i> Add
        </button>
    </div>
    @foreach($rows as $rowIndex => $equipmentId)
        <div class="trf-ww-extra-equipment-row mb-2" wire:key="trf-sampling-equipment-row-{{ $rowIndex }}">
            <input type="text"
                class="form-control form-control-sm"
                wire:model.defer="formData.thermometer_id.{{ $rowIndex }}"
                placeholder="Equipment ID"
                aria-label="Equipment ID">
            @if(count($rows) > 1)
                <button type="button"
                    class="btn btn-sm btn-outline-danger"
                    wire:click="removeSamplingEquipmentIdRow({{ $rowIndex }})"
                    title="Remove"
                    aria-label="Remove equipment ID">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            @endif
        </div>
    @endforeach
</div>
