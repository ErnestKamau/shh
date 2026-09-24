{{--
	Read-only collection field for View request details.
	Uses gallery chips / affix / text by field type.
--}}
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
    $value = $trfEditCollectionFields[$fieldName] ?? null;

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

    $iconByName = [
        'sampling_date' => 'mdi-calendar',
        'sampling_time' => 'mdi-clock-outline',
        'sampling_location' => 'mdi-map-marker',
        'sampling_apparatus' => 'mdi-flask-outline',
        'thermometer_id' => 'mdi-thermometer',
        'method_of_sampling' => 'mdi-clipboard-list-outline',
        'reason_of_collection' => 'mdi-comment-question-outline',
        'transport_condition' => 'mdi-truck-outline',
        'date_received' => 'mdi-calendar-check',
    ];
    $icon = $iconByName[$fieldName] ?? 'mdi-form-textbox';

    $chipColumns = match ($fieldName) {
        'transport_condition' => 'transport',
        'method_of_sampling' => 'method',
        'sampling_apparatus' => 'apparatus',
        default => 'auto',
    };
    if (! empty($optionGridClass)) {
        if (str_contains((string) $optionGridClass, 'transport')) {
            $chipColumns = 'transport';
        } elseif (str_contains((string) $optionGridClass, 'method')) {
            $chipColumns = 'method';
        } elseif (str_contains((string) $optionGridClass, 'apparatus')) {
            $chipColumns = 'apparatus';
        }
    }

    $isCheckbox = $fieldType === 'checkbox' || in_array($fieldName, [
        'sampling_apparatus', 'method_of_sampling', 'reason_of_collection', 'transport_condition',
        'sampling_technique', 'sampling_source', 'sample_types_ww', 'field_data_requirements',
    ], true);
    $isRadio = $fieldType === 'radio';
    $isLocation = $fieldName === 'sampling_location' || in_array($fieldType, ['customer_sample_point_select', 'sample_point_select'], true);
    $isDate = in_array($fieldName, ['sampling_date', 'date_received'], true) || $fieldType === 'date';
    $isTime = $fieldName === 'sampling_time' || $fieldType === 'time';
    $isRichText = $fieldType === 'rich_text';

    $selectedForChips = [];
    if ($isCheckbox) {
        if (is_array($value)) {
            foreach ($value as $key => $flag) {
                if ($flag === true || $flag === 1 || $flag === '1' || $flag === 'true') {
                    $selectedForChips[] = (string) $key;
                } elseif (is_int($key) && filled($flag)) {
                    $selectedForChips[] = (string) $flag;
                }
            }
        } elseif (filled($value)) {
            $selectedForChips = [(string) $value];
        }
    } elseif ($isRadio && filled($value) && ! is_array($value)) {
        $selectedForChips = [(string) $value];
    }

    $textDisplay = '—';
    if ($isLocation) {
        $match = collect($trfEditSamplePointOptions ?? [])->firstWhere('value', (string) $value);
        $textDisplay = (string) ($match['label'] ?? ($value ?: '—'));
        if ($textDisplay === '') {
            $textDisplay = '—';
        }
    } elseif ($isRichText) {
        $html = trim(strip_tags((string) ($value ?? ''), '<p><br><ul><ol><li><strong><em>'));
        $textDisplay = $html !== '' ? $html : '—';
    } elseif ($isDate || $isTime || in_array($fieldName, ['thermometer_id', 'ph_meter_id', 'chlorine_meter_id'], true) || (! $isCheckbox && ! $isRadio)) {
        if ($fieldName === 'thermometer_id') {
            $textDisplay = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class)
                ->formatForDisplay($value);
            $textDisplay = $textDisplay !== '' ? $textDisplay : '—';
        } elseif (is_array($value)) {
            $textDisplay = implode(', ', array_filter(array_map(
                static fn ($item): string => is_scalar($item) ? trim((string) $item) : '',
                $value
            )));
            $textDisplay = $textDisplay !== '' ? $textDisplay : '—';
        } else {
            $string = trim((string) ($value ?? ''));
            $textDisplay = $string !== '' ? $string : '—';
        }
    }
@endphp

@if($fieldName !== '')
    @php
        $hideOuterCol = ! empty($hideOuterCol);
        $wrapperClass = trim(($hideOuterCol ? '' : ($colClass ?? 'col-md-6')).' '.($outerColExtraClass ?? ''));
    @endphp
    <div class="{{ $wrapperClass }} {{ $hideOuterCol ? '' : 'mb-3' }}">
        @if(($isCheckbox || $isRadio) && $checkboxOptions !== [])
            @include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
                'label' => $fieldLabel,
                'icon' => $icon,
                'name' => 'trf_view_'.$fieldName,
                'id' => 'trf-view-'.$fieldName,
                'type' => $isCheckbox ? 'checkbox' : 'radio',
                'options' => $checkboxOptions,
                'selected' => $isRadio ? ($selectedForChips[0] ?? null) : $selectedForChips,
                'columns' => $chipColumns,
                'disabled' => true,
            ])
        @elseif($isRichText)
            <div class="ls-field">
                <label class="ls-field__label">{{ $fieldLabel }}</label>
                <div class="ls-field__control">
                    <div class="small text-body">{!! $textDisplay !!}</div>
                </div>
            </div>
        @elseif($isDate || $isTime || in_array($fieldName, ['thermometer_id', 'ph_meter_id', 'chlorine_meter_id'], true))
            @include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
                'label' => $fieldLabel,
                'id' => 'trf-view-'.$fieldName,
                'name' => 'trf_view_'.$fieldName,
                'value' => $textDisplay,
                'prefix' => '<i class="mdi '.$icon.'"></i>',
                'disabled' => true,
            ])
        @else
            @include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
                'label' => $fieldLabel,
                'id' => 'trf-view-'.$fieldName,
                'name' => 'trf_view_'.$fieldName,
                'value' => $textDisplay,
                'prefix' => '<i class="mdi '.$icon.'"></i>',
                'disabled' => true,
            ])
        @endif
    </div>
@endif
