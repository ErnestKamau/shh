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
        <label class="form-label small font-weight-bold text-secondary mb-1">{{ $fieldLabel }}</label>

        @if($fieldName === 'sampling_location' || in_array($fieldType, ['customer_sample_point_select', 'sample_point_select'], true))
            <div wire:ignore class="rv-sample-row-select2-wrap">
                <select id="trf-edit-{{ $fieldName }}"
                    class="form-control form-control-sm livewire-select2"
                    data-wire-field="trfEditCollectionFields.{{ $fieldName }}"
                    data-placeholder="Select sampling location"
                    data-selected-values="{{ json_encode(array_values(array_filter([(string) ($trfEditCollectionFields[$fieldName] ?? '')]))) }}">
                    <option value="">Select sampling location</option>
                    @foreach($trfEditSamplePointOptions as $point)
                        <option value="{{ $point['value'] }}"
                            @selected((string) ($trfEditCollectionFields[$fieldName] ?? '') === (string) $point['value'])>
                            {{ $point['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(($trfEditCompanyUnitId ?? '') === '')
                <div class="small text-muted mt-1">Select a company unit on the Customer step first.</div>
            @endif
        @elseif($fieldType === 'checkbox' || in_array($fieldName, ['sampling_apparatus', 'method_of_sampling'], true))
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
        @elseif($fieldType === 'radio' || $fieldName === 'reason_of_collection' || $fieldName === 'transport_condition')
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
            <input type="date"
                class="form-control form-control-sm"
                wire:model.defer="trfEditCollectionFields.{{ $fieldName }}">
        @elseif($isTimeField)
            <input type="time"
                class="form-control form-control-sm"
                wire:model.defer="trfEditCollectionFields.{{ $fieldName }}">
        @elseif($fieldType === 'select' && $checkboxOptions !== [])
            <select class="form-control form-control-sm"
                wire:model.defer="trfEditCollectionFields.{{ $fieldName }}">
                <option value="">— Select —</option>
                @foreach($checkboxOptions as $optionKey => $optionText)
                    <option value="{{ $optionKey }}">{{ $optionText }}</option>
                @endforeach
            </select>
        @else
            <input type="text"
                class="form-control form-control-sm"
                wire:model.defer="trfEditCollectionFields.{{ $fieldName }}">
        @endif
    </div>
@endif
