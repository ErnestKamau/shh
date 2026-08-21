{{--
	Read-only sample field row for View request details.
	Expects: $field, $draft, $sampleIndex, $editingRowSelectOptions, optional $colClass, $hideOuterCol
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
    $colClass = ! empty($hideOuterCol) ? '' : ($colClass ?? 'col-md-6');
    $draft = is_array($draft ?? null) ? $draft : [];
    $raw = $draft[$fieldName] ?? null;
    $sampleIndex = (int) ($sampleIndex ?? 0);

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

    $catalogKeys = ['sample_type_id', 'analysis_type_id', 'parameters'];
    $isCatalog = in_array($fieldName, $catalogKeys, true);
    $isCheckbox = $fieldType === 'checkbox' || $fieldName === 'test_requirements';
    $isRadioOrSelect = in_array($fieldType, ['select', 'radio'], true) && $checkboxOptions !== [];

    $iconByName = [
        'sample_type_id' => 'mdi-test-tube',
        'analysis_type_id' => 'mdi-microscope',
        'parameters' => 'mdi-flask-outline',
        'sampling_point_manual' => 'mdi-map-marker-outline',
        'state_of_sample' => 'mdi-cube-outline',
        'production_date' => 'mdi-calendar',
        'expiration_date' => 'mdi-calendar-end',
        'batch_number' => 'mdi-barcode',
        'sample_quantity' => 'mdi-scale',
        'sample_condition' => 'mdi-shield-check-outline',
        'sample_temp' => 'mdi-thermometer',
        'field_sample_temp' => 'mdi-thermometer',
        'test_category' => 'mdi-tag-outline',
        'sample_description' => 'mdi-text-box-outline',
        'test_requirements' => 'mdi-checkbox-marked-outline',
    ];
    $icon = $iconByName[$fieldName] ?? 'mdi-form-textbox';

    $resolveCatalogLabels = function ($key, $value) use ($editingRowSelectOptions): array {
        $ids = is_array($value) ? $value : (filled($value) ? [(string) $value] : []);
        $options = $editingRowSelectOptions[$key] ?? [];

        return collect($ids)->map(function ($id) use ($options) {
            $match = collect($options)->firstWhere('value', (string) $id);

            return $match['label'] ?? (string) $id;
        })->filter()->values()->all();
    };

    $display = '—';
    $chipSelected = [];

    if ($isCatalog) {
        $labels = $resolveCatalogLabels($fieldName, $raw);
        $display = $labels === [] ? '—' : implode(', ', $labels);
        $chipSelected = $labels;
    } elseif ($isCheckbox) {
        if (is_array($raw)) {
            foreach ($raw as $key => $flag) {
                if ($flag === true || $flag === 1 || $flag === '1' || $flag === 'true') {
                    $chipSelected[] = (string) $key;
                } elseif (is_int($key) && filled($flag)) {
                    $chipSelected[] = (string) $flag;
                }
            }
        } elseif (filled($raw)) {
            $chipSelected = [(string) $raw];
        }
    } elseif ($isRadioOrSelect) {
        $selectedKey = is_array($raw) ? '' : (string) ($raw ?? '');
        $display = $checkboxOptions[$selectedKey] ?? ($selectedKey !== '' ? $selectedKey : '—');
        if ($selectedKey !== '') {
            $chipSelected = [$selectedKey];
        }
    } elseif ($fieldName === 'sample_quantity') {
        $qty = trim((string) ($draft['sample_quantity'] ?? ''));
        $unit = trim((string) ($draft['sample_quantity_unit'] ?? ''));
        $display = trim($qty.($unit !== '' ? ' '.$unit : ''));
        $display = $display !== '' ? $display : '—';
    } elseif ($fieldName === 'sample_description') {
        $display = trim(strip_tags((string) ($raw ?? '')));
        $display = $display !== '' ? $display : '—';
    } else {
        if (is_array($raw)) {
            $display = '—';
        } else {
            $string = trim((string) ($raw ?? ''));
            $display = $string !== '' ? $string : '—';
        }
    }
@endphp

@if($fieldName !== '')
    <div class="{{ trim($colClass.' mb-3') }}">
        @if($isCatalog && $chipSelected !== [])
            <div class="ls-field is-disabled">
                <span class="ls-field__label">
                    <i class="mdi {{ $icon }}" aria-hidden="true"></i>
                    {{ $fieldLabel === 'Parameters' ? 'Tests' : $fieldLabel }}
                </span>
                <div class="ls-view-tag-row">
                    @foreach($chipSelected as $tag)
                        <span class="ls-view-tag">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        @elseif($isCheckbox && $checkboxOptions !== [])
            @include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
                'label' => $fieldLabel,
                'icon' => $icon,
                'name' => 'trf_view_s'.$sampleIndex.'_'.$fieldName,
                'id' => 'trf-view-s'.$sampleIndex.'-'.$fieldName,
                'type' => 'checkbox',
                'options' => $checkboxOptions,
                'selected' => $chipSelected,
                'columns' => 'auto',
                'disabled' => true,
            ])
        @elseif($isRadioOrSelect && $checkboxOptions !== [])
            @include('layouts.lab.partials.ls-ui.fields.ls-field-option-chips', [
                'label' => $fieldLabel,
                'icon' => $icon,
                'name' => 'trf_view_s'.$sampleIndex.'_'.$fieldName,
                'id' => 'trf-view-s'.$sampleIndex.'-'.$fieldName,
                'type' => 'radio',
                'options' => $checkboxOptions,
                'selected' => $chipSelected[0] ?? null,
                'columns' => 'auto',
                'disabled' => true,
            ])
        @elseif($fieldName === 'sample_description')
            <div class="ls-field is-disabled">
                <span class="ls-field__label">
                    <i class="mdi {{ $icon }}" aria-hidden="true"></i>
                    {{ $fieldLabel }}
                </span>
                <div class="rv-sample-description-inline">{!! ($raw ?: '—') !!}</div>
            </div>
        @else
            @include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
                'label' => $fieldLabel,
                'id' => 'trf-view-s'.$sampleIndex.'-'.$fieldName,
                'name' => 'trf_view_s'.$sampleIndex.'_'.$fieldName,
                'value' => $display,
                'prefix' => '<i class="mdi '.$icon.'"></i>',
                'disabled' => true,
            ])
        @endif
    </div>
@endif
