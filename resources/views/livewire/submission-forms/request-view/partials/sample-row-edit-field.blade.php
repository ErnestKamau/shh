@php
    $fieldName = $field['name'] ?? '';
    $fieldType = $field['element_type'] ?? 'text';
    $fieldLabel = $field['label'] ?? $fieldName;
    $fieldOptions = is_array($field['options'] ?? null) ? $field['options'] : [];
    $selectOptions = $editingRowSelectOptions[$fieldName] ?? [];
    $colClass = $colClass ?? (
        in_array($fieldType, ['textarea', 'rich_text', 'analysis_elements_select'], true) || $fieldName === 'sample_description'
            ? 'col-12'
            : 'col-md-6'
    );
    $selectedParameters = collect($editingRowFields['parameters'] ?? [])
        ->map(fn ($value) => (string) $value)
        ->filter(fn (string $value) => $value !== '')
        ->values()
        ->all();
    $selectedSampleTypes = collect($editingRowFields['sample_type_id'] ?? [])
        ->when(is_string($editingRowFields['sample_type_id'] ?? null), fn ($c) => collect(explode(',', (string) ($editingRowFields['sample_type_id'] ?? ''))))
        ->map(fn ($value) => (string) $value)
        ->filter(fn (string $value) => $value !== '')
        ->values()
        ->all();
    $selectedAnalysisTypes = collect($editingRowFields['analysis_type_id'] ?? [])
        ->when(is_string($editingRowFields['analysis_type_id'] ?? null), fn ($c) => collect(explode(',', (string) ($editingRowFields['analysis_type_id'] ?? ''))))
        ->map(fn ($value) => (string) $value)
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
@endphp

<div class="{{ ($hideOuterCol ?? false) ? '' : $colClass.' mb-3' }}">
    @if(! ($hideLabel ?? false))
        <label class="form-label small font-weight-bold text-secondary mb-1" for="edit-row-{{ $fieldName }}">
            {{ $fieldLabel }}
            @if($field['required'] ?? false)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    @if($fieldName === 'sample_description' || $fieldType === 'rich_text')
        @include('livewire.partials.submission-rich-text-editor', [
            'wirePrefix' => 'editingRowFields.sample_description',
            'fieldId' => 'edit-row-sample-desc-'.($editingRowIndex ?? 0),
            'editorId' => 'edit-row-sample-desc-'.($editingRowIndex ?? 0),
            'value' => $editingRowFields['sample_description'] ?? '',
            'rowIndex' => (int) ($editingRowIndex ?? 0),
            'height' => 160,
        ])
    @elseif($fieldType === 'textarea')
        <textarea id="edit-row-{{ $fieldName }}"
            class="form-control form-control-sm"
            rows="3"
            wire:model.defer="editingRowFields.{{ $fieldName }}"></textarea>
    @elseif($fieldType === 'checkbox' || $isTestRequirements)
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
                            default => (string) ($editingRowFields[$fieldName] ?? '') === $optionValue,
                        };
                    @endphp
                    <option value="{{ $optionValue }}" @selected($isSelected)>
                        {{ $option['label'] ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>
    @elseif($fieldType === 'date')
        <input id="edit-row-{{ $fieldName }}"
            type="date"
            class="form-control form-control-sm"
            wire:model.defer="editingRowFields.{{ $fieldName }}">
    @elseif($fieldType === 'number')
        <input id="edit-row-{{ $fieldName }}"
            type="number"
            step="any"
            class="form-control form-control-sm"
            wire:model.defer="editingRowFields.{{ $fieldName }}">
    @elseif(in_array($fieldType, ['select', 'radio'], true) && $checkboxOptions !== [])
        <select id="edit-row-{{ $fieldName }}"
            class="form-control form-control-sm"
            wire:model.defer="editingRowFields.{{ $fieldName }}">
            <option value="">— Select —</option>
            @foreach($checkboxOptions as $optionKey => $optionText)
                <option value="{{ $optionKey }}" @selected((string) ($editingRowFields[$fieldName] ?? '') === (string) $optionKey)>
                    {{ $optionText }}
                </option>
            @endforeach
        </select>
    @else
        <input id="edit-row-{{ $fieldName }}"
            type="text"
            class="form-control form-control-sm"
            wire:model.defer="editingRowFields.{{ $fieldName }}">
    @endif
</div>
