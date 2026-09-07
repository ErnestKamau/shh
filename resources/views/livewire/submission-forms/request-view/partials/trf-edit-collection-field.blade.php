@php
    $field = $field ?? null;
    if (! is_array($field)) {
        return;
    }

    $fieldName = (string) ($field['name'] ?? '');
    $fieldType = (string) ($field['element_type'] ?? 'text');
    $fieldLabel = (string) ($field['label'] ?? $fieldName);
    $fieldOptions = is_array($field['options'] ?? null) ? $field['options'] : [];
    $colClass = $colClass ?? 'col-md-6';
    $optionGridClass = $optionGridClass ?? 'rv-trf-option-grid';

    $checkboxOptions = [];
    foreach ($fieldOptions as $optionValue => $optionLabel) {
        if (is_array($optionLabel) && isset($optionLabel['value'])) {
            $checkboxOptions[(string) $optionLabel['value']] = (string) ($optionLabel['label'] ?? $optionLabel['value']);
        } elseif (is_string($optionValue) && ! is_numeric($optionValue)) {
            $checkboxOptions[$optionValue] = is_string($optionLabel) ? $optionLabel : $optionValue;
        } elseif (is_string($optionLabel)) {
            $checkboxOptions[$optionLabel] = $optionLabel;
        }
    }

    $isTimeField = $fieldName === 'sampling_time' || $fieldType === 'time';
    $isDateField = in_array($fieldName, ['sampling_date', 'date_received'], true) || $fieldType === 'date';
@endphp

@if($fieldName !== '')
    <div class="{{ trim((($hideOuterCol ?? false) ? '' : ($colClass ?? '')).' '.($outerColExtraClass ?? '')) }}">
        <div class="ls-field {{ filled($trfEditCollectionFields[$fieldName] ?? null) && ! is_array($trfEditCollectionFields[$fieldName] ?? null) ? 'is-success' : '' }}">
            <label class="ls-field__label" for="trf-edit-{{ $fieldName }}">{{ $fieldLabel }}</label>

        @if($fieldName === 'sampling_location' || in_array($fieldType, ['customer_sample_point_select', 'sample_point_select'], true))
            @php
                $locationOptions = collect($trfEditSamplePointOptions ?? [])->map(fn ($point) => [
                    'value' => (string) ($point['value'] ?? ''),
                    'label' => (string) ($point['label'] ?? $point['value'] ?? ''),
                ])->filter(fn ($o) => $o['value'] !== '')->values()->all();
                $locationSelected = (string) ($trfEditCollectionFields[$fieldName] ?? '');
                $locationMatch = collect($locationOptions)->firstWhere('value', $locationSelected);
                $locationLabel = is_array($locationMatch)
                    ? (string) ($locationMatch['label'] ?? $locationSelected)
                    : $locationSelected;
            @endphp
            <div
                class="ls-field ls-combo ls-search-basic ls-compact {{ $locationSelected !== '' ? 'is-success' : '' }}"
                wire:key="trf-edit-location-{{ $trfEditCompanyUnitId }}-{{ count($locationOptions) }}"
                x-data="{
                    open: false,
                    q: @js($locationLabel),
                    selected: @js($locationSelected !== '' ? $locationSelected : null),
                    options: @js($locationOptions),
                    get filtered() {
                        const q = (this.q || '').trim().toLowerCase();
                        if (!q || this.selected === this.q) return this.options;
                        return this.options.filter(o => o.label.toLowerCase().includes(q));
                    },
                    pick(opt) {
                        this.selected = opt.value;
                        this.q = opt.label;
                        this.open = false;
                        $wire.set('trfEditCollectionFields.{{ $fieldName }}', opt.value);
                    },
                    clear() {
                        this.q = '';
                        this.selected = null;
                        this.open = true;
                        $wire.set('trfEditCollectionFields.{{ $fieldName }}', '');
                    }
                }"
                :class="{ 'is-open': open, 'is-success': !!selected && !open }"
                @click.outside="open = false"
            >
                <div class="ls-field__control">
                    <div class="ls-combo__search-wrap">
                        <i class="mdi mdi-magnify"></i>
                        <input
                            id="trf-edit-{{ $fieldName }}"
                            type="text"
                            x-model="q"
                            @focus="open = true"
                            @input="open = true; selected = null"
                            placeholder="Search sampling location…"
                            autocomplete="off"
                        >
                    </div>
                    <button type="button" class="ls-field__icon-btn" x-show="q" @click="clear()" aria-label="Clear">
                        <i class="mdi mdi-close-circle"></i>
                    </button>
                    <i class="mdi mdi-check-circle" x-show="selected && !open" style="padding-right:0.45rem;"></i>
                    <button type="button" class="ls-field__icon-btn" @click="open = !open" aria-label="Toggle">
                        <i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
                    </button>
                </div>
                <div class="ls-combo__menu" role="listbox">
                    <template x-for="opt in filtered" :key="opt.value">
                        <button type="button" class="ls-combo__item" :class="{ 'is-active': selected === opt.value }" @click="pick(opt)">
                            <span x-text="opt.label"></span>
                            <i class="mdi mdi-check" x-show="selected === opt.value"></i>
                        </button>
                    </template>
                </div>
            </div>
            @if(($trfEditCompanyUnitId ?? '') === '')
                <p class="ls-field__hint mt-1 mb-0">Select a company unit on the Customer step first.</p>
            @endif
        @elseif($fieldName === 'thermometer_id' && ($this->usesTrfEditSamplingEquipmentIdPicker() ?? false))
            @php
                $equipmentOptions = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class)
                    ->activeEquipmentSelectOptions();
                $equipmentRows = is_array($trfEditCollectionFields['thermometer_id'] ?? null)
                    ? array_values($trfEditCollectionFields['thermometer_id'])
                    : [''];
                if ($equipmentRows === []) {
                    $equipmentRows = [''];
                }
            @endphp
            <div class="trf-sampling-equipment-ids">
                @foreach($equipmentRows as $rowIndex => $selectedId)
                    <div class="d-flex align-items-start gap-2 mb-2" wire:key="trf-edit-equipment-{{ $rowIndex }}">
                        <div class="flex-grow-1">
                            <x-searchable-select
                                wire:model="trfEditCollectionFields.thermometer_id.{{ $rowIndex }}"
                                :options="$equipmentOptions"
                                placeholder="Search equipment..."
                                empty-label="-- Select equipment --"
                                size="sm"
                            />
                        </div>
                        @if($rowIndex === 0)
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                wire:click="addTrfEditSamplingEquipmentIdRow"
                                title="Add equipment ID">
                                <i class="mdi mdi-plus" aria-hidden="true"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                wire:click="removeTrfEditSamplingEquipmentIdRow({{ $rowIndex }})"
                                title="Remove">
                                <i class="mdi mdi-close" aria-hidden="true"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif($fieldName === 'thermometer_id' || $fieldName === 'ph_meter_id' || $fieldName === 'chlorine_meter_id')
            <div class="ls-field__control">
                <input id="trf-edit-{{ $fieldName }}"
                    type="text"
                    class="ls-field__input"
                    wire:model.defer="trfEditCollectionFields.{{ $fieldName }}"
                    placeholder="Optional">
            </div>
        @elseif($fieldType === 'rich_text')
            @include('livewire.partials.submission-rich-text-editor', [
                'fieldId' => 'trf-edit-'.$fieldName,
                'wirePrefix' => 'trfEditCollectionFields.'.$fieldName,
                'value' => (string) ($trfEditCollectionFields[$fieldName] ?? ''),
            ])
        @elseif($fieldType === 'checkbox' || in_array($fieldName, [
            'sampling_apparatus', 'method_of_sampling', 'reason_of_collection', 'transport_condition',
            'sampling_technique', 'sampling_source', 'sample_types_ww', 'field_data_requirements',
        ], true))
            <div class="{{ $optionGridClass }}">
                @foreach($checkboxOptions as $optionKey => $optionText)
                    <label class="rv-trf-option-chip">
                        <input type="checkbox"
                            class="rv-trf-option-input"
                            wire:model.defer="trfEditCollectionFields.{{ $fieldName }}.{{ $optionKey }}">
                        <span class="rv-trf-option-chip__label">{{ $optionText }}</span>
                    </label>
                @endforeach
            </div>
        @elseif($fieldType === 'radio')
            <div class="{{ $optionGridClass ?? 'rv-trf-option-grid rv-trf-option-grid--compact' }}">
                @foreach($checkboxOptions as $optionKey => $optionText)
                    <label class="rv-trf-option-chip">
                        <input type="radio"
                            class="rv-trf-option-input"
                            wire:model.defer="trfEditCollectionFields.{{ $fieldName }}"
                            value="{{ $optionKey }}">
                        <span class="rv-trf-option-chip__label">{{ $optionText }}</span>
                    </label>
                @endforeach
            </div>
        @elseif($isDateField)
            <div class="ls-field__control">
                <input id="trf-edit-{{ $fieldName }}"
                    type="date"
                    class="ls-field__input"
                    wire:model.defer="trfEditCollectionFields.{{ $fieldName }}">
            </div>
        @elseif($isTimeField)
            <div class="ls-field__control">
                <input id="trf-edit-{{ $fieldName }}"
                    type="time"
                    class="ls-field__input"
                    wire:model.defer="trfEditCollectionFields.{{ $fieldName }}">
            </div>
        @elseif($fieldType === 'select' && $checkboxOptions !== [])
            <div class="ls-field__control">
                <select id="trf-edit-{{ $fieldName }}"
                    class="ls-field__input"
                    wire:model.defer="trfEditCollectionFields.{{ $fieldName }}">
                    <option value="">— Select —</option>
                    @foreach($checkboxOptions as $optionKey => $optionText)
                        <option value="{{ $optionKey }}">{{ $optionText }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="ls-field__control">
                <input id="trf-edit-{{ $fieldName }}"
                    type="text"
                    class="ls-field__input"
                    wire:model.defer="trfEditCollectionFields.{{ $fieldName }}">
            </div>
        @endif
        </div>
    </div>
@endif
