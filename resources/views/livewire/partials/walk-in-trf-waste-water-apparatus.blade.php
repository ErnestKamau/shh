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

    {{-- Fixed 6 controls in a 3-column grid --}}
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
            @php
                $instrumentEl = $collectionField($instrument['name']);
                $instrumentValue = trim((string) data_get($this->formData ?? [], $instrument['name'], ''));
            @endphp
            <div class="trf-ww-apparatus-grid__cell trf-ww-apparatus-grid__cell--instrument"
                wire:key="ww-instrument-{{ $instrument['name'] }}">
                <label class="trf-ww-instrument-row__check mb-1">
                    <input type="checkbox" class="mr-1" @checked($instrumentValue !== '')
                        disabled aria-hidden="true">
                    <span class="small font-weight-bold">{{ $instrument['label'] }}</span>
                </label>
                @if($instrumentEl)
                    <input type="text"
                        class="form-control form-control-sm"
                        wire:model.defer="formData.{{ $instrument['name'] }}"
                        placeholder="ID">
                @endif
            </div>
        @endforeach

        <div class="trf-ww-apparatus-grid__cell trf-ww-apparatus-grid__cell--instrument"
            wire:key="ww-apparatus-others">
            <label class="trf-option-chip trf-ww-apparatus-chip mb-1">
                <input type="checkbox"
                    wire:model="formData.sampling_apparatus.others">
                <span class="trf-option-chip__label">{{ $apparatusOptions['others'] ?? 'Others' }}</span>
            </label>
            @php $othersEl = $collectionField('sampling_apparatus_others'); @endphp
            @if($othersEl)
                <input type="text"
                    class="form-control form-control-sm"
                    wire:model.defer="formData.sampling_apparatus_others"
                    placeholder="Specify others">
            @endif
        </div>
    </div>

    <div class="trf-ww-extra-equipment mt-3" wire:key="ww-extra-equipment">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small font-weight-bold text-muted text-uppercase">Additional equipment IDs</span>
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addExtraSamplingEquipmentRow">
                <i class="mdi mdi-plus" aria-hidden="true"></i> Add
            </button>
        </div>
        @forelse($extraRows as $rowIndex => $row)
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
        @empty
            <p class="small text-muted mb-0">Use + to record another equipment ID.</p>
        @endforelse
    </div>
</div>
