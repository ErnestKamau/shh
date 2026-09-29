@php
    $fieldName = $field['name'] ?? '';
    $fieldType = $field['element_type'] ?? 'text';
    $fieldLabel = $field['label'] ?? $fieldName;
    $fieldOptions = is_array($field['options'] ?? null) ? $field['options'] : [];
    $selectOptions = $editingRowSelectOptions[$fieldName] ?? [];
    $isAdditionalDetails = $fieldName === 'additional_details';
    $colClass = $colClass ?? (
        in_array($fieldType, ['analysis_elements_select'], true)
            || in_array($fieldName, ['parameters'], true)
            ? 'col-12'
            : 'col-md-4'
    );
    $selectedParameters = collect(is_array($editingRowFields['parameters'] ?? null) ? $editingRowFields['parameters'] : [])
        ->flatten()
        ->map(fn ($value) => is_scalar($value) ? (string) $value : '')
        ->filter(fn (string $value) => $value !== '')
        ->values()
        ->all();
    $selectedSampleTypes = collect(
        is_array($editingRowFields['sample_type_id'] ?? null)
            ? $editingRowFields['sample_type_id']
            : (filled($editingRowFields['sample_type_id'] ?? null) ? explode(',', (string) $editingRowFields['sample_type_id']) : [])
    )
        ->flatten()
        ->map(fn ($value) => is_scalar($value) ? (string) $value : '')
        ->filter(fn (string $value) => $value !== '')
        ->values()
        ->all();
    $selectedAnalysisTypes = collect(
        is_array($editingRowFields['analysis_type_id'] ?? null)
            ? $editingRowFields['analysis_type_id']
            : (filled($editingRowFields['analysis_type_id'] ?? null) ? explode(',', (string) $editingRowFields['analysis_type_id']) : [])
    )
        ->flatten()
        ->map(fn ($value) => is_scalar($value) ? (string) $value : '')
        ->filter(fn (string $value) => $value !== '')
        ->values()
        ->all();
    $isMultiSelectField = in_array($fieldName, ['sample_type_id', 'analysis_type_id', 'parameters'], true)
        || in_array($fieldType, ['analysis_elements_select'], true);
    $usesSelect2 = in_array($fieldType, ['sample_type_select', 'analysis_type_select', 'analysis_elements_select', 'sample_point_select', 'customer_sample_point_select'], true)
        || in_array($fieldName, ['sample_type_id', 'analysis_type_id', 'parameters', 'sampling_point'], true);
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
    $isTestRequirements = $fieldName === 'test_requirements';
    if ($isTestRequirements && $checkboxOptions === [] && $this->isWaterTrf()) {
        $checkboxOptions = $this->waterTestRequirementOptions();
    }
    $usesLsSelectInclude = in_array($fieldName, ['parameters', 'sample_type_id', 'analysis_type_id'], true);
    $isTempField = in_array($fieldName, ['sample_temp', 'field_sample_temp'], true);
    $isStateOfSample = $fieldName === 'state_of_sample';
    $rawFieldValue = $editingRowFields[$fieldName] ?? null;
    $fieldValueIsCheckboxMap = is_array($rawFieldValue)
        && \App\Services\SubmissionForm\SubmissionFormSchemaHelper::selectedCheckboxKeys($rawFieldValue) !== null;
    $selectedScalar = '';
    if ($fieldValueIsCheckboxMap) {
        $selectedScalar = (string) ((\App\Services\SubmissionForm\SubmissionFormSchemaHelper::selectedCheckboxKeys($rawFieldValue)[0] ?? ''));
    } elseif (is_array($rawFieldValue)) {
        $firstScalar = collect($rawFieldValue)
            ->flatten()
            ->first(fn ($value) => is_scalar($value) && trim((string) $value) !== '');
        $selectedScalar = is_scalar($firstScalar) ? (string) $firstScalar : '';
    } elseif (is_scalar($rawFieldValue) || $rawFieldValue === null) {
        $selectedScalar = (string) ($rawFieldValue ?? '');
    }
@endphp

<div class="{{ ($hideOuterCol ?? false) ? '' : $colClass.' mb-3' }}">
    @if(! ($hideLabel ?? false) && ! $usesLsSelectInclude && ! $isTempField && ! $isStateOfSample && ! $isAdditionalDetails)
        <label class="ls-field__label" for="edit-row-{{ $fieldName }}">
            {{ $fieldLabel }}
            @if($field['required'] ?? false)
                <span class="ls-req">*</span>
            @endif
        </label>
    @endif

    @if($isAdditionalDetails)
        @php
            $detailRows = $this->editingRowAdditionalDetails();
        @endphp
        <div class="rft-additional-details" wire:key="edit-row-additional-details-{{ $editingRowIndex ?? 0 }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="rft-sample-section-label rft-sample-section-label--sample mb-0 border-0 pt-0">
                    {{ $fieldLabel !== '' ? $fieldLabel : 'Additional details' }}
                </div>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    title="Add detail"
                    aria-label="Add additional detail"
                    wire:click="addEditingRowAdditionalDetail"
                >
                    <i class="mdi mdi-plus" aria-hidden="true"></i> Add
                </button>
            </div>
            @forelse($detailRows as $detailIndex => $detail)
                <div class="rft-additional-details__row mb-2" wire:key="edit-row-additional-detail-{{ $editingRowIndex ?? 0 }}-{{ $detailIndex }}">
                    <input
                        type="text"
                        class="form-control form-control-sm rft-additional-details__label"
                        wire:model.defer="editingRowFields.additional_details.{{ $detailIndex }}.label"
                        placeholder="Label"
                        aria-label="Detail label"
                    >
                    <input
                        type="text"
                        class="form-control form-control-sm rft-additional-details__value"
                        wire:model.defer="editingRowFields.additional_details.{{ $detailIndex }}.value"
                        placeholder="Value"
                        aria-label="Detail value"
                    >
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        title="Remove"
                        aria-label="Remove detail"
                        wire:click="removeEditingRowAdditionalDetail({{ (int) $detailIndex }})"
                    >
                        <i class="mdi mdi-close" aria-hidden="true"></i>
                    </button>
                </div>
            @empty
                <p class="text-muted small mb-0">Optional — add custom label/value fields for this sample.</p>
            @endforelse
        </div>
    @elseif($fieldName === 'sample_description' || $fieldType === 'rich_text')
        @include('livewire.partials.submission-rich-text-editor', [
            'wirePrefix' => 'editingRowFields.sample_description',
            'fieldId' => 'edit-row-sample-desc-'.($editingRowIndex ?? 0),
            'editorId' => 'edit-row-sample-desc-'.($editingRowIndex ?? 0),
            'value' => $editingRowFields['sample_description'] ?? '',
            'rowIndex' => (int) ($editingRowIndex ?? 0),
            'height' => 160,
        ])
    @elseif($fieldType === 'textarea')
        <div class="ls-field">
            <div class="ls-field__control">
                <textarea id="edit-row-{{ $fieldName }}"
                    class="ls-field__input"
                    rows="3"
                    wire:model.defer="editingRowFields.{{ $fieldName }}"></textarea>
            </div>
        </div>
    @elseif($fieldType === 'checkbox' || $isTestRequirements || $fieldValueIsCheckboxMap)
        <div class="d-flex flex-wrap rv-test-requirements-checkboxes" style="gap: 12px;">
            @foreach($checkboxOptions as $optionKey => $optionText)
                <label class="form-check mb-0">
                    <input type="checkbox"
                        class="form-check-input"
                        wire:model.defer="editingRowFields.{{ $fieldName }}.{{ $optionKey }}">
                    <span class="form-check-label">{{ $optionText }}</span>
                </label>
            @endforeach
        </div>
    @elseif($fieldName === 'parameters')
        <label class="ls-field__label mb-1">{{ $fieldLabel }}@if($field['required'] ?? false)<span class="ls-req">*</span>@endif</label>
        <div class="trf-ls-theme">
            @include('livewire.partials.walk-in-trf-parameters-cell', [
                'rowIndex' => null,
                'pickerContext' => 'edit',
                'fieldId' => 'edit-row-parameters-'.($editingRowIndex ?? 0),
            ])
        </div>
    @elseif(in_array($fieldName, ['sample_type_id', 'analysis_type_id'], true))
        @include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
            'label' => $fieldLabel,
            'id' => 'edit-row-'.$fieldName,
            'name' => 'editingRowFields_'.$fieldName,
            'required' => (bool) ($field['required'] ?? false),
            'placeholder' => $fieldName === 'sample_type_id' ? 'Search sample type…' : 'Search analysis type…',
            'options' => $selectOptions,
            'selected' => $fieldName === 'sample_type_id' ? $selectedSampleTypes : $selectedAnalysisTypes,
            'variant' => 'slate',
            'wireIgnore' => true,
            'dataWireField' => 'editingRowFields.'.$fieldName,
            'dataSelectLive' => '1',
            'extraSelectClass' => 'livewire-select2 rv-trf-catalog-select',
            'selectedValuesJson' => json_encode($fieldName === 'sample_type_id' ? $selectedSampleTypes : $selectedAnalysisTypes),
        ])
    @elseif($isStateOfSample)
        @php
            $stateOptions = collect($checkboxOptions)->map(fn ($label, $value) => [
                'value' => (string) $value,
                'label' => (string) $label,
            ])->values()->all();
            $stateSelected = $selectedScalar;
            $stateSelectedLabel = $checkboxOptions[$stateSelected] ?? $stateSelected;
        @endphp
        <div
            class="ls-field ls-combo ls-search-basic ls-compact {{ $stateSelected !== '' ? 'is-success' : '' }}"
            x-data="{
                open: false,
                q: @js($stateSelectedLabel),
                selected: @js($stateSelected !== '' ? $stateSelected : null),
                options: @js($stateOptions),
                get filtered() {
                    const q = (this.q || '').trim().toLowerCase();
                    if (!q || this.selected === this.q) return this.options;
                    return this.options.filter(o => o.label.toLowerCase().includes(q));
                },
                pick(opt) {
                    this.selected = opt.value;
                    this.q = opt.label;
                    this.open = false;
                    $wire.set('editingRowFields.{{ $fieldName }}', opt.value);
                },
                clear() {
                    this.q = '';
                    this.selected = null;
                    this.open = true;
                    $wire.set('editingRowFields.{{ $fieldName }}', '');
                }
            }"
            :class="{ 'is-open': open, 'is-success': !!selected && !open }"
            @click.outside="open = false"
        >
            <label class="ls-field__label" for="edit-row-{{ $fieldName }}">{{ $fieldLabel }}</label>
            <div class="ls-field__control">
                <div class="ls-combo__search-wrap">
                    <i class="mdi mdi-magnify"></i>
                    <input
                        id="edit-row-{{ $fieldName }}"
                        type="text"
                        x-model="q"
                        @focus="open = true"
                        @input="open = true; selected = null"
                        placeholder="Search…"
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
    @elseif($isTempField)
        <div class="ls-field {{ filled($editingRowFields[$fieldName] ?? null) ? 'is-success' : '' }}">
            <label class="ls-field__label" for="edit-row-{{ $fieldName }}">{{ $fieldLabel }}</label>
            <div class="ls-field__control">
                <span class="ls-field__affix ls-field__affix--prefix"><i class="mdi mdi-thermometer"></i></span>
                <input id="edit-row-{{ $fieldName }}"
                    type="number"
                    step="any"
                    class="ls-field__input"
                    wire:model.defer="editingRowFields.{{ $fieldName }}"
                    placeholder="Temp">
                <span class="ls-field__affix ls-field__affix--suffix">°C</span>
            </div>
        </div>
    @elseif($usesSelect2)
        @php
            $isTrfCatalogSelect = in_array($fieldName, ['sample_type_id', 'analysis_type_id', 'parameters'], true);
        @endphp
        <div wire:ignore class="rv-sample-row-select2-wrap {{ $isTrfCatalogSelect ? 'rv-trf-select-shell' : '' }}">
            <select id="edit-row-{{ $fieldName }}"
                class="form-control form-control-sm livewire-select2 {{ $isTrfCatalogSelect ? 'rv-trf-catalog-select' : '' }}"
                data-wire-field="editingRowFields.{{ $fieldName }}"
                data-select-live="{{ in_array($fieldName, ['sample_type_id', 'analysis_type_id'], true) ? '1' : '0' }}"
                @if($isMultiSelectField) multiple @endif
                data-selected-values="{{ json_encode(match($fieldName) {
                    'parameters' => $selectedParameters,
                    'sample_type_id' => $selectedSampleTypes,
                    'analysis_type_id' => $selectedAnalysisTypes,
                    default => [],
                }) }}"
                data-placeholder="{{ match(true) {
                    $fieldName === 'parameters' => 'Search and add tests…',
                    $fieldName === 'sample_type_id' => 'Search sample type…',
                    $fieldName === 'analysis_type_id' => 'Search analysis type…',
                    default => 'Select sampling point',
                } }}">
                @if(! $isMultiSelectField)
                    <option value="">— Select —</option>
                @endif
                @foreach($selectOptions as $option)
                    @php
                        $optionValue = (string) ($option['value'] ?? '');
                        $isSelected = match($fieldName) {
                            'parameters' => in_array($optionValue, $selectedParameters, true),
                            'sample_type_id' => in_array($optionValue, $selectedSampleTypes, true),
                            'analysis_type_id' => in_array($optionValue, $selectedAnalysisTypes, true),
                            default => $selectedScalar === $optionValue,
                        };
                    @endphp
                    <option value="{{ $optionValue }}" @selected($isSelected)>
                        {{ $option['label'] ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>
    @elseif($fieldType === 'date')
        <div class="ls-field {{ filled($editingRowFields[$fieldName] ?? null) ? 'is-success' : '' }}">
            <div class="ls-field__control">
                <input id="edit-row-{{ $fieldName }}"
                    type="date"
                    class="ls-field__input"
                    wire:model.defer="editingRowFields.{{ $fieldName }}">
            </div>
        </div>
    @elseif($fieldType === 'number')
        <div class="ls-field {{ filled($editingRowFields[$fieldName] ?? null) ? 'is-success' : '' }}">
            <div class="ls-field__control">
                <input id="edit-row-{{ $fieldName }}"
                    type="number"
                    step="any"
                    class="ls-field__input"
                    wire:model.defer="editingRowFields.{{ $fieldName }}">
            </div>
        </div>
    @elseif(in_array($fieldType, ['select', 'radio'], true) && $checkboxOptions !== [])
        <div class="ls-field {{ $selectedScalar !== '' ? 'is-success' : '' }}">
            <div class="ls-field__control">
                <select id="edit-row-{{ $fieldName }}"
                    class="ls-field__input"
                    wire:model.defer="editingRowFields.{{ $fieldName }}">
                    <option value="">— Select —</option>
                    @foreach($checkboxOptions as $optionKey => $optionText)
                        <option value="{{ $optionKey }}" @selected($selectedScalar === (string) $optionKey)>
                            {{ $optionText }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @else
        <div class="ls-field {{ $selectedScalar !== '' ? 'is-success' : '' }}">
            <div class="ls-field__control">
                <input id="edit-row-{{ $fieldName }}"
                    type="text"
                    class="ls-field__input"
                    value="{{ $selectedScalar }}"
                    wire:model.defer="editingRowFields.{{ $fieldName }}">
            </div>
        </div>
    @endif
</div>
