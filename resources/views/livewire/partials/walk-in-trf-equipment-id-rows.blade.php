{{-- Multi Equipment ID free-text rows for Food/Water TRF (per sample when $sampleRowIndex set). --}}
@php
    $sampleRowIndex = isset($sampleRowIndex) ? (int) $sampleRowIndex : null;
    $resolver = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class);
    if ($sampleRowIndex !== null) {
        $rows = $this->samplingEquipmentIdRowsForSample($sampleRowIndex);
        $wireBase = 'formData.thermometer_id.'.$sampleRowIndex;
        $addClick = "addSamplingEquipmentIdRow({$sampleRowIndex})";
        $removeClickPrefix = "removeSamplingEquipmentIdRow(";
        $removeClickSuffix = ", {$sampleRowIndex})";
        $keyPrefix = 'trf-sampling-equipment-sample-'.$sampleRowIndex;
    } else {
        $rows = is_array($this->formData['thermometer_id'] ?? null)
            ? array_values($this->formData['thermometer_id'])
            : $resolver->rowsForForm($this->formData['thermometer_id'] ?? null);
        if ($rows === []) {
            $rows = [''];
        }
        $wireBase = 'formData.thermometer_id';
        $addClick = 'addSamplingEquipmentIdRow';
        $removeClickPrefix = 'removeSamplingEquipmentIdRow(';
        $removeClickSuffix = ')';
        $keyPrefix = 'trf-sampling-equipment';
    }
    if ($rows === []) {
        $rows = [''];
    }
@endphp
<div class="trf-sampling-equipment-ids mt-1" wire:key="{{ $keyPrefix }}-ids">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="ls-type-label mb-0">Equipment ID</div>
        <button type="button"
            class="btn btn-sm btn-outline-primary"
            wire:click="{{ $addClick }}"
            title="Add equipment ID"
            aria-label="Add equipment ID">
            <i class="mdi mdi-plus" aria-hidden="true"></i> Add
        </button>
    </div>
    @foreach($rows as $equipIndex => $equipmentId)
        <div class="trf-ww-extra-equipment-row mb-2" wire:key="{{ $keyPrefix }}-row-{{ $equipIndex }}">
            <input type="text"
                class="form-control form-control-sm"
                wire:model.defer="{{ $wireBase }}.{{ $equipIndex }}"
                placeholder="Equipment ID"
                aria-label="Equipment ID">
            @if(count($rows) > 1)
                <button type="button"
                    class="btn btn-sm btn-outline-danger"
                    wire:click="{{ $removeClickPrefix }}{{ $equipIndex }}{{ $removeClickSuffix }}"
                    title="Remove"
                    aria-label="Remove equipment ID">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            @endif
        </div>
    @endforeach
</div>
