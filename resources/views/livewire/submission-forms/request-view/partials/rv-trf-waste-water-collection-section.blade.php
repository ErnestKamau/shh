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
        'sample_sampling_point_description', 'sampling_apparatus', 'method_of_sampling',
        'thermometer_id', 'ph_meter_id', 'chlorine_meter_id', 'sampling_apparatus_others',
        'extra_sampling_equipment', 'reason_of_collection', 'sampling_technique', 'sampling_source',
        'transport_condition', 'sample_types_ww', 'field_data_requirements',
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

    {{-- Row 2 --}}
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

        <div class="trf-ww-apparatus-block">
            <div class="trf-ww-apparatus-block__title ls-type-label mb-2">Sampling apparatus</div>
            @if($collectionByName->has('sampling_apparatus'))
                @include($fieldPartial, [
                    'field' => $collectionByName->get('sampling_apparatus'),
                    'colClass' => '',
                    'hideOuterCol' => true,
                    'optionGridClass' => 'rv-trf-option-grid rv-trf-option-grid--apparatus trf-option-grid--ww-apparatus-3',
                ])
            @endif
            <div class="trf-ww-apparatus-grid mt-2">
                @foreach(['thermometer_id', 'ph_meter_id', 'chlorine_meter_id', 'sampling_apparatus_others'] as $name)
                    @if($collectionByName->has($name))
                        <div class="trf-ww-apparatus-grid__cell">
                            @include($fieldPartial, [
                                'field' => $collectionByName->get($name),
                                'colClass' => '',
                                'hideOuterCol' => true,
                            ])
                        </div>
                    @endif
                @endforeach
            </div>
            @if(! $readOnly)
                <div class="trf-ww-extra-equipment mt-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small font-weight-bold text-muted text-uppercase">Additional equipment IDs</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addTrfEditExtraSamplingEquipmentRow">
                            <i class="mdi mdi-plus" aria-hidden="true"></i> Add
                        </button>
                    </div>
                    @forelse($extraRows as $rowIndex => $row)
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
                    @empty
                        <p class="small text-muted mb-0">Use + to record another equipment ID.</p>
                    @endforelse
                </div>
            @elseif(count($extraRows) > 0)
                <ul class="small mb-0 pl-3 mt-2">
                    @foreach($extraRows as $row)
                        <li>{{ trim(($row['label'] ?? '').': '.($row['id'] ?? '')) }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

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

    {{-- Row 4: transport | test category --}}
    <div class="trf-ww-collection-row4">
        @if($collectionByName->has('transport_condition'))
            @include($fieldPartial, [
                'field' => $collectionByName->get('transport_condition'),
                'colClass' => '',
                'hideOuterCol' => true,
                'optionGridClass' => 'rv-trf-option-grid trf-option-grid--ww-transport',
            ])
        @endif
        @if($collectionByName->has('field_data_requirements'))
            @include($fieldPartial, [
                'field' => array_merge($collectionByName->get('field_data_requirements'), ['label' => 'Test category']),
                'colClass' => '',
                'hideOuterCol' => true,
                'optionGridClass' => 'rv-trf-option-grid trf-option-grid--ww-test-category',
            ])
        @endif
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
                        @include($fieldPartial, [
                            'field' => $collectionByName->get($name),
                            'colClass' => '',
                            'hideOuterCol' => true,
                        ])
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="d-none">
        @foreach(['date_received', 'sample_types_ww'] as $name)
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
