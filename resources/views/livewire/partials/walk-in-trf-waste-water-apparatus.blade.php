@php
    $wwApparatus = $wwApparatus ?? null;
    $extraRows = $extraRows ?? [];
    if (! is_array($extraRows)) {
        $decoded = is_string($extraRows) && $extraRows !== '' ? json_decode($extraRows, true) : null;
        $extraRows = is_array($decoded) ? $decoded : [];
    }

    $apparatusOptions = collect($wwApparatus?->options ?? [])->mapWithKeys(function ($opt) {
        if (is_array($opt)) {
            $value = (string) ($opt['value'] ?? '');
            $label = (string) ($opt['label'] ?? $value);

            return $value !== '' ? [$value => $label] : [];
        }

        return [(string) $opt => (string) $opt];
    })->all();

    $instrumentCells = [
        ['name' => 'thermometer_id', 'label' => 'Thermometer ID'],
        ['name' => 'ph_meter_id', 'label' => 'pH meter ID'],
        ['name' => 'chlorine_meter_id', 'label' => 'Chlorine meter ID'],
    ];
@endphp
<div class="trf-ww-apparatus-block" wire:key="trf-ww-apparatus-block">
    <div class="trf-ww-apparatus-block__title ls-type-label mb-2">Sampling apparatus</div>

    <div class="trf-ww-apparatus-grid">
        @foreach(['sterile_bottle' => 'Sterile bottle', 'bottle_catcher' => 'Bottle catcher'] as $optValue => $optLabel)
            @php $displayLabel = $apparatusOptions[$optValue] ?? $optLabel; @endphp
            <div class="trf-ww-apparatus-grid__cell" wire:key="ww-apparatus-opt-{{ $optValue }}">
                <label class="trf-option-chip trf-ww-apparatus-chip">
                    <input type="checkbox"
                        wire:model="formData.sampling_apparatus.{{ $optValue }}">
                    <span class="trf-option-chip__label">{{ $displayLabel }}</span>
                </label>
            </div>
        @endforeach

        @foreach($instrumentCells as $instrument)
            @php $instrumentEl = $collectionField($instrument['name']); @endphp
            <div class="trf-ww-apparatus-grid__cell trf-ww-apparatus-grid__cell--instrument"
                wire:key="ww-instrument-{{ $instrument['name'] }}">
                <span class="small font-weight-bold d-block mb-1">{{ $instrument['label'] }}</span>
                @if($instrumentEl)
                    <input type="text"
                        class="form-control form-control-sm"
                        wire:model.defer="formData.{{ $instrument['name'] }}"
                        placeholder="ID">
                @endif
            </div>
        @endforeach
    </div>

    <div class="trf-ww-extra-equipment mt-3" wire:key="ww-extra-equipment">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addExtraSamplingEquipmentRow">
                <i class="mdi mdi-plus" aria-hidden="true"></i> Others
            </button>
        </div>
        @foreach($extraRows as $rowIndex => $row)
            <div class="trf-ww-extra-equipment-row" wire:key="ww-extra-equipment-{{ $rowIndex }}">
                <input type="text" class="form-control form-control-sm"
                    wire:model.defer="formData.extra_sampling_equipment.{{ $rowIndex }}.label"
                    placeholder="Equipment type">
                <input type="text" class="form-control form-control-sm"
                    wire:model.defer="formData.extra_sampling_equipment.{{ $rowIndex }}.id"
                    placeholder="Equipment ID">
                <button type="button" class="btn btn-sm btn-outline-danger"
                    wire:click="removeExtraSamplingEquipmentRow({{ $rowIndex }})" title="Remove row">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            </div>
        @endforeach
    </div>
</div>
