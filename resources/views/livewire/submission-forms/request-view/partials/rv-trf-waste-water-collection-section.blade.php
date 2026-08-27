@php
    $collectionByName = collect($trfEditCollectionDefinitions ?? [])
        ->keyBy(fn (array $field): string => (string) ($field['name'] ?? ''));
    $fieldPartial = $fieldPartial ?? 'livewire.submission-forms.request-view.partials.trf-edit-collection-field';
    $readOnly = (bool) ($readOnly ?? false);
    $extraRows = $trfEditCollectionFields['extra_sampling_equipment'] ?? [];
    if (! is_array($extraRows)) {
        $decoded = is_string($extraRows) && trim($extraRows) !== '' ? json_decode($extraRows, true) : null;
        $extraRows = is_array($decoded) ? $decoded : [];
    }
    $descField = $collectionByName->get('sample_sampling_point_description');
    $descValue = (string) ($trfEditCollectionFields['sample_sampling_point_description'] ?? '');
    $wwPriorityNames = [
        'sampling_date', 'sampling_time', 'sampling_location',
        'sample_sampling_point_description', 'sample_types_ww', 'method_of_sampling',
        'reason_of_collection', 'sampling_technique', 'sampling_source',
        'transport_condition', 'sampling_apparatus',
        'thermometer_id', 'ph_meter_id', 'chlorine_meter_id', 'sampling_apparatus_others',
        'extra_sampling_equipment', 'field_data_requirements',
        'field_data_quantity', 'field_data_appearance', 'field_data_color', 'field_data_odor',
        'field_data_ph', 'field_data_temperature', 'field_data_free_chlorine', 'date_received',
    ];
@endphp

<div class="col-12 rv-trf-ww-collection">
    {{-- Row 1 --}}
    <div class="rv-trf-collection-trio">
        @foreach(['sampling_date', 'sampling_time', 'sampling_location'] as $name)
            @if($collectionByName->has($name))
                @include($fieldPartial, ['field' => $collectionByName->get($name), 'colClass' => '', 'hideOuterCol' => true])
            @endif
        @endforeach
    </div>

    {{-- Row 2: Description | Sample types | Method --}}
    <div class="rv-trf-collection-trio">
        <div class="trf-ww-description-cell">
            @if(is_array($descField))
                <label class="ls-field__label mb-1">{{ $descField['label'] ?? 'Sample & sampling point description' }}</label>
                @if($readOnly)
                    <div class="small text-body">{!! ($descValue !== '' ? $descValue : '—') !!}</div>
                @else
                    @include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-cell', [
                        'label' => null,
                        'value' => $descValue,
                        'wireModel' => 'trfEditCollectionFields.sample_sampling_point_description',
                        'editorId' => 'rv-ww-sample-sampling-point-description',
                        'rowLabel' => 'Sample collection',
                        'compact' => true,
                    ])
                @endif
            @endif
        </div>

        @if($collectionByName->has('sample_types_ww'))
            @include($fieldPartial, [
                'field' => array_merge($collectionByName->get('sample_types_ww'), ['label' => 'Sample types']),
                'colClass' => '',
                'hideOuterCol' => true,
                'optionGridClass' => 'rv-trf-option-grid trf-option-grid--ww-sample-types',
            ])
        @endif

        @if($collectionByName->has('method_of_sampling'))
            @include($fieldPartial, [
                'field' => $collectionByName->get('method_of_sampling'),
                'colClass' => '',
                'hideOuterCol' => true,
                'optionGridClass' => 'rv-trf-option-grid rv-trf-option-grid--method',
            ])
        @endif
    </div>

    {{-- Row 3 --}}
    <div class="trf-ww-collection-row3">
        @foreach([
            ['reason_of_collection', 'rv-trf-option-grid rv-trf-option-grid--compact trf-option-grid--ww-reason', 'trf-ww-collection-row3__reason'],
            ['sampling_technique', 'rv-trf-option-grid trf-option-grid--ww-technique', 'trf-ww-collection-row3__technique'],
            ['sampling_source', 'rv-trf-option-grid trf-option-grid--ww-source', 'trf-ww-collection-row3__source'],
        ] as [$name, $gridClass, $wrapClass])
            @if($collectionByName->has($name))
                <div class="{{ $wrapClass }}">
                    @include($fieldPartial, [
                        'field' => $collectionByName->get($name),
                        'colClass' => '',
                        'hideOuterCol' => true,
                        'optionGridClass' => $gridClass,
                    ])
                </div>
            @endif
        @endforeach
    </div>

    {{-- Row 4: Transport (Reason width) | Apparatus (from Technique onward) --}}
    <div class="trf-ww-collection-row4">
        <div class="trf-ww-collection-row4__transport">
            @if($collectionByName->has('transport_condition'))
                @include($fieldPartial, [
                    'field' => $collectionByName->get('transport_condition'),
                    'colClass' => '',
                    'hideOuterCol' => true,
                    'optionGridClass' => 'rv-trf-option-grid trf-option-grid--ww-transport',
                ])
            @endif
        </div>
        <div class="trf-ww-collection-row4__apparatus">
            <div class="trf-ww-apparatus-block">
                <div class="trf-ww-apparatus-block__title ls-type-label mb-2">Sampling apparatus</div>
                @if($collectionByName->has('sampling_apparatus'))
                    @php
                        $apparatusField = $collectionByName->get('sampling_apparatus');
                        $apparatusOptions = collect($apparatusField['options'] ?? [])
                            ->filter(function ($opt) {
                                $value = is_array($opt) ? (string) ($opt['value'] ?? '') : (string) $opt;

                                return ! in_array(strtolower($value), ['others', 'other'], true);
                            })
                            ->values()
                            ->all();
                        $apparatusField['options'] = $apparatusOptions;
                    @endphp
                    @include($fieldPartial, [
                        'field' => $apparatusField,
                        'colClass' => '',
                        'hideOuterCol' => true,
                        'optionGridClass' => 'rv-trf-option-grid rv-trf-option-grid--apparatus trf-option-grid--ww-apparatus-4',
                    ])
                @endif
                <div class="trf-ww-apparatus-grid mt-2">
                    @foreach([
                        'thermometer_id' => 'Thermometer ID',
                        'ph_meter_id' => 'pH meter ID',
                        'chlorine_meter_id' => 'Chlorine meter ID',
                    ] as $name => $instrumentLabel)
                        @if($collectionByName->has($name))
                            @php
                                $instrumentValue = (string) ($trfEditCollectionFields[$name] ?? '');
                            @endphp
                            <div class="trf-ww-apparatus-grid__cell trf-ww-apparatus-grid__cell--instrument{{ $name === 'chlorine_meter_id' ? ' trf-ww-apparatus-grid__cell--chlorine' : '' }}"
                                wire:key="rv-ww-instrument-{{ $name }}">
                                <div class="rv-trf-option-chip trf-ww-apparatus-chip trf-ww-apparatus-id-chip">
                                    <span class="rv-trf-option-chip__label">{{ $instrumentLabel }}</span>
                                    @if($readOnly)
                                        <span class="trf-ww-apparatus-id-chip__value">{{ $instrumentValue !== '' ? $instrumentValue : '—' }}</span>
                                    @else
                                        <input type="text"
                                            class="form-control form-control-sm trf-ww-apparatus-id-chip__input"
                                            wire:model.defer="trfEditCollectionFields.{{ $name }}"
                                            placeholder="ID"
                                            aria-label="{{ $instrumentLabel }}">
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                @if(! $readOnly)
                    <div class="trf-ww-extra-equipment mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addTrfEditExtraSamplingEquipmentRow">
                                <i class="mdi mdi-plus" aria-hidden="true"></i> Others
                            </button>
                        </div>
                        @foreach($extraRows as $rowIndex => $row)
                            <div class="trf-ww-extra-equipment-row" wire:key="rv-ww-extra-{{ $rowIndex }}">
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.defer="trfEditCollectionFields.extra_sampling_equipment.{{ $rowIndex }}.label"
                                    placeholder="Equipment type">
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.defer="trfEditCollectionFields.extra_sampling_equipment.{{ $rowIndex }}.id"
                                    placeholder="Equipment ID">
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    wire:click="removeTrfEditExtraSamplingEquipmentRow({{ $rowIndex }})">
                                    <i class="mdi mdi-close" aria-hidden="true"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @elseif(count($extraRows) > 0)
                    <ul class="small mb-0 pl-3 mt-2">
                        @foreach($extraRows as $row)
                            <li>{{ trim(($row['label'] ?? '').': '.($row['id'] ?? '')) }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Row 5: field data --}}
    <div class="trf-ww-field-data-block mt-2">
        <div class="trf-ww-field-data-block__title ls-type-label mb-2">Field data</div>
        <div class="trf-ww-field-data-grid">
            @foreach([
                'field_data_quantity', 'field_data_appearance', 'field_data_color', 'field_data_odor',
                'field_data_ph', 'field_data_temperature', 'field_data_free_chlorine',
            ] as $name)
                @if($collectionByName->has($name))
                    <div class="trf-ww-field-data-grid__cell">
                        @if($name === 'field_data_temperature' && ! $readOnly)
                            @php
                                $tempField = $collectionByName->get($name);
                                $tempLabel = (string) ($tempField['label'] ?? 'Field data — Temperature (°C)');
                                $tempValue = $trfEditCollectionFields['field_data_temperature'] ?? '';
                            @endphp
                            <div class="ls-field {{ filled($tempValue) ? 'is-success' : '' }}">
                                <label class="ls-field__label" for="trf-edit-field_data_temperature">{{ $tempLabel }}</label>
                                <div class="ls-field__control">
                                    <span class="ls-field__affix ls-field__affix--prefix"><i class="mdi mdi-thermometer"></i></span>
                                    <input
                                        id="trf-edit-field_data_temperature"
                                        type="number"
                                        step="any"
                                        class="ls-field__input"
                                        wire:model.defer="trfEditCollectionFields.field_data_temperature"
                                        placeholder="Temp"
                                    >
                                    <span class="ls-field__affix ls-field__affix--suffix">°C</span>
                                </div>
                            </div>
                        @else
                            @include($fieldPartial, [
                                'field' => $collectionByName->get($name),
                                'colClass' => '',
                                'hideOuterCol' => true,
                            ])
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="d-none">
        @foreach(['date_received', 'field_data_requirements', 'sampling_apparatus_others'] as $name)
            @if($collectionByName->has($name))
                @include($fieldPartial, ['field' => $collectionByName->get($name), 'colClass' => '', 'hideOuterCol' => true])
            @endif
        @endforeach
    </div>
</div>

@foreach($trfEditCollectionDefinitions as $field)
    @php $name = (string) ($field['name'] ?? ''); @endphp
    @if($name === '' || in_array($name, $wwPriorityNames, true))
        @continue
    @endif
    @include($fieldPartial, ['field' => $field, 'colClass' => 'col-md-6'])
@endforeach
