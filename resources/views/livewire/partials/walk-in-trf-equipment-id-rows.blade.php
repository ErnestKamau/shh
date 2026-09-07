{{-- Multi Equipment ID picker for Food/Water TRF collection (not Waste Water). --}}
@php
    $resolver = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class);
    $equipmentOptions = $resolver->activeEquipmentSelectOptions();
    $rows = is_array($this->formData['thermometer_id'] ?? null)
        ? array_values($this->formData['thermometer_id'])
        : $resolver->rowsForForm($this->formData['thermometer_id'] ?? null);
    if ($rows === []) {
        $rows = [''];
    }
@endphp
<div class="trf-sampling-equipment-ids mt-3" wire:key="trf-sampling-equipment-ids">
    <div class="ls-type-label mb-2">Equipment ID</div>
    @foreach($rows as $rowIndex => $selectedId)
        <div class="d-flex align-items-start gap-2 mb-2" wire:key="trf-sampling-equipment-row-{{ $rowIndex }}">
            <div class="flex-grow-1">
                <x-searchable-select
                    wire:model="formData.thermometer_id.{{ $rowIndex }}"
                    :options="$equipmentOptions"
                    placeholder="Search equipment..."
                    empty-label="-- Select equipment --"
                    size="sm"
                />
            </div>
            @if($rowIndex === 0)
                <button type="button"
                    class="btn btn-sm btn-outline-primary"
                    wire:click="addSamplingEquipmentIdRow"
                    title="Add equipment ID"
                    aria-label="Add equipment ID">
                    <i class="mdi mdi-plus" aria-hidden="true"></i>
                </button>
            @else
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
