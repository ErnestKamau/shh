@php
    $cardLayout = $this->walkInSampleCardLayout($tableColumns, $hiddenWalkInTrfFields);
    $expandAllSampleCards = (bool) ($expandAllSampleCards ?? false);
@endphp

<div class="rft-sample-cards" x-data="{
    expandAll: {{ $expandAllSampleCards ? 'true' : 'false' }},
    openRow: {{ $expandAllSampleCards ? -1 : 0 }},
    init() {
        if (this.expandAll) {
            this.$nextTick(() => {
                for (let i = 0; i < {{ (int) $rowCount }}; i++) {
                    this.$dispatch('rft-sample-card-shown', { row: i });
                }
            });
            return;
        }
        if (this.openRow >= 0) {
            this.$nextTick(() => {
                this.$dispatch('rft-sample-card-shown', { row: this.openRow });
            });
        }
    },
    setOpenRow(row) {
        if (this.expandAll) {
            return;
        }
        const previous = this.openRow;
        this.openRow = this.openRow === row ? -1 : row;
        if (previous >= 0 && previous !== this.openRow) {
            this.$dispatch('rft-sample-card-hidden', { row: previous });
        }
        if (this.openRow >= 0) {
            this.$nextTick(() => this.$dispatch('rft-sample-card-shown', { row: this.openRow }));
        }
    }
}" wire:ignore.self>
    @for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
        @php
            $summary = $this->walkInSampleRowSummary($rowIndex);
        @endphp

        <article
            class="rft-sample-row-card"
            data-trf-sample-index="{{ $rowIndex }}"
            wire:key="row-card-{{ $activeSection->id }}-{{ $rowIndex }}"
            :class="{ 'is-open': expandAll || openRow === {{ $rowIndex }} }"
        >
            <div class="rft-sample-row-card__header">
                <div
                    class="flex-grow-1 min-width-0"
                    x-data="{
                        rowIndex: {{ $rowIndex }},
                        analysisLabel: @js($summary['analysis_label']),
                        paramCount: {{ (int) $summary['param_count'] }},
                        paramPreview: @js($summary['param_preview']),
                    }"
                    @rft-trf-tests-saved.window="
                        if (Number($event.detail?.rowIndex) !== rowIndex) { return; }
                        paramCount = Number($event.detail?.count ?? 0);
                        paramPreview = String($event.detail?.preview ?? '');
                        analysisLabel = String($event.detail?.analysisLabel ?? analysisLabel);
                    "
                >
                <button
                    type="button"
                    class="rft-sample-row-card__toggle-main border-0 bg-transparent p-0 text-left w-100"
                    @click="setOpenRow({{ $rowIndex }})"
                >
                    <h6 class="rft-sample-row-card__title mb-1">
                        Sample {{ $rowIndex + 1 }}<span x-show="analysisLabel !== ''" x-cloak> — <span x-text="analysisLabel"></span></span>
                    </h6>
                    <div class="rft-sample-row-card__summary text-muted small" x-show="!expandAll && openRow !== {{ $rowIndex }}" x-cloak>
                        <span x-text="analysisLabel !== '' ? analysisLabel : 'No analysis'"></span>
                        <span class="mx-1">·</span>
                        <span x-text="paramCount + ' parameter' + (paramCount === 1 ? '' : 's')"></span>
                        <template x-if="paramPreview !== ''">
                            <span>
                                <span class="mx-1">·</span>
                                <span class="rft-sample-row-card__preview" x-text="paramPreview + (paramCount > 2 ? '…' : '')"></span>
                            </span>
                        </template>
                    </div>
                </button>
                </div>
                <div class="rft-sample-row-card__header-actions">
                    @if($rowIndex === 0 && $rowCount > 1)
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary btn-action-sm"
                            wire:click="copyFirstSampleToAllBelow"
                            title="Copy Sample 1 details to all samples below"
                            aria-label="Copy Sample 1 details to all samples below"
                        >
                            <i class="mdi mdi-content-copy"></i>
                        </button>
                    @endif
                    @if($rowCount > 1)
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger btn-action-sm"
                            wire:click="removeSchemaRow('{{ $activeSection->id }}', {{ $rowIndex }})"
                            title="Remove sample"
                        >
                            <i class="mdi mdi-trash-can-outline"></i>
                        </button>
                    @endif
                    <button
                        type="button"
                        class="rft-sample-row-card__chevron border-0 bg-transparent p-0"
                        @click="setOpenRow({{ $rowIndex }})"
                        x-show="!expandAll"
                        :aria-expanded="openRow === {{ $rowIndex }} ? 'true' : 'false'"
                        aria-label="Toggle sample {{ $rowIndex + 1 }}"
                    >
                        <i class="mdi" :class="openRow === {{ $rowIndex }} ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
                    </button>
                </div>
            </div>

            {{-- x-show keeps parameter picker state; TinyMCE is destroyed/restored via events --}}
            <div
                class="rft-sample-row-card__body"
                x-show="expandAll || openRow === {{ $rowIndex }}"
                x-cloak
            >
                    @foreach($cardLayout['grid_rows'] as $gridRow)
                        @if(($gridRow['type'] ?? 'fields') === 'catalog')
                            @if($cardLayout['catalog_row'] ?? null)
                                @php $catalogRow = $cardLayout['catalog_row']; @endphp
                                <div class="rv-trf-catalog-row rft-sample-catalog-row rft-sample-catalog-row--type-tests">
                                    @foreach(['sample_type' => 'Sample type', 'parameters' => 'Tests'] as $catalogKey => $defaultLabel)
                                        @php $column = $catalogRow[$catalogKey] ?? null; @endphp
                                        @if($column !== null)
                                            @php
                                                $element = $column['element'];
                                                $field = $column['field'] ?? $fieldMapper->toField($element);
                                            @endphp
                                            <div class="rv-trf-catalog-row__cell">
                                                <label>
                                                    {{ $column['label'] ?? $defaultLabel }}
                                                    @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                                </label>
                                                @if($catalogKey === 'parameters')
                                                    @include('livewire.partials.walk-in-trf-parameters-cell', [
                                                        'fieldId' => $element->name.'_'.$rowIndex,
                                                        'rowIndex' => $rowIndex,
                                                        'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                                        'formData' => $formData,
                                                    ])
                                                @else
                                                    <div class="rv-trf-select-shell">
                                                        @include('livewire.sampleworkflow.test-request-field-render', [
                                                            'field' => $field,
                                                            'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                                            'fieldId' => $element->name.'_'.$rowIndex,
                                                            'rowIndex' => $rowIndex,
                                                            'compact' => false,
                                                            'hideLabel' => true,
                                                        ])
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        @elseif(($gridRow['type'] ?? 'fields') === 'section')
                            <div class="rft-sample-section-label {{ ($gridRow['group'] ?? '') === 'collection' ? 'rft-sample-section-label--collection' : 'rft-sample-section-label--sample' }}">
                                {{ $gridRow['label'] ?? '' }}
                            </div>
                        @elseif(($gridRow['type'] ?? 'fields') === 'divider')
                            <div class="rft-sample-grid-row-divider" aria-hidden="true"></div>
                        @else
                            @php
                                $gridColumns = $gridRow['columns'] ?? [];
                                $gridCols = (int) ($gridRow['cols'] ?? 3);
                                $gridRowClass = 'rft-sample-grid-row rft-sample-grid-row--cols-' . $gridCols;
                                if (! empty($gridRow['compact'])) {
                                    $gridRowClass .= ' rft-sample-grid-row--compact';
                                }
                                if (($gridRow['group'] ?? '') === 'field_data') {
                                    $gridRowClass .= ' rft-sample-grid-row--field-data';
                                }
                                if (($gridRow['group'] ?? '') === 'collection') {
                                    $gridRowClass .= ' rft-sample-grid-row--collection';
                                }
                            @endphp
                            <div class="{{ $gridRowClass }}">
                                @foreach($gridColumns as $column)
                                    @php
                                        $fieldColClass = match ($gridCols) {
                                            1 => 'col-md-12',
                                            2 => 'col-md-6',
                                            default => 'col-md-4',
                                        };
                                        $fieldName = '';
                                        if ($column !== null) {
                                            $fieldName = (string) (($column['field']['name'] ?? '') !== ''
                                                ? $column['field']['name']
                                                : ($column['element']->name ?? ''));
                                        }
                                        $fieldModifiers = in_array($fieldName, ['test_requirements', 'test_category'], true)
                                            ? ' rft-sample-field--test-requirements'
                                            : '';
                                        if (($gridRow['group'] ?? '') === 'collection') {
                                            $fieldModifiers .= ' rft-sample-field--collection';
                                        }
                                    @endphp
                                    <div class="{{ $fieldColClass }} rft-sample-field{{ $fieldModifiers }}">
                                        @if($column === null)
                                            <div class="rft-sample-field--empty"></div>
                                        @else
                                            @php
                                                $element = $column['element'];
                                                $field = $column['field'] ?? $fieldMapper->toField($element);
                                                $nestedColumn = $column['nested'] ?? null;
                                                $isEquipmentId = ! empty($column['is_equipment_id'])
                                                    || in_array($fieldName, ['thermometer_id', 'equipment_id'], true)
                                                        && $this->usesSamplingEquipmentIdPicker();
                                            @endphp
                                            @if($isEquipmentId)
                                                @include('livewire.partials.walk-in-trf-equipment-id-rows', [
                                                    'sampleRowIndex' => $rowIndex,
                                                ])
                                            @else
                                            <label>
                                                {{ $column['label'] ?? ($field['label'] ?? $fieldName) }}
                                                @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                            </label>
                                            @if(($column['type'] ?? '') === 'qty_unit')
                                                @include('livewire.partials.walk-in-trf-qty-unit-cell', [
                                                    'rowIndex' => $rowIndex,
                                                    'compact' => false,
                                                    'hideLabel' => true,
                                                    'quantityField' => 'sample_quantity',
                                                    'unitField' => 'sample_quantity_unit',
                                                ])
                                            @elseif($fieldName === 'sample_description')
                                                @include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-cell', [
                                                    'label' => null,
                                                    'value' => data_get($formData, 'sample_description.'.$rowIndex, ''),
                                                    'wireModel' => 'formData.sample_description.'.$rowIndex,
                                                    'editorId' => 'rft-desc-cell-'.$rowIndex,
                                                    'rowLabel' => 'Sample '.($rowIndex + 1),
                                                    'compact' => true,
                                                ])
                                            @else
                                                @include('livewire.sampleworkflow.test-request-field-render', [
                                                    'field' => $field,
                                                    'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                                    'fieldId' => $element->name.'_'.$rowIndex,
                                                    'rowIndex' => $rowIndex,
                                                    'compact' => false,
                                                    'hideLabel' => true,
                                                    'optionCols' => $column['option_cols'] ?? ($field['option_cols'] ?? null),
                                                ])
                                            @endif
                                            @endif
                                            @if(is_array($nestedColumn) && isset($nestedColumn['element']))
                                                @php
                                                    $nestedElement = $nestedColumn['element'];
                                                    $nestedField = $nestedColumn['field'] ?? $fieldMapper->toField($nestedElement);
                                                    $nestedName = (string) (($nestedField['name'] ?? '') !== ''
                                                        ? $nestedField['name']
                                                        : ($nestedElement->name ?? ''));
                                                    $nestedIsEquipmentId = ! empty($nestedColumn['is_equipment_id'])
                                                        || (in_array($nestedName, ['thermometer_id', 'equipment_id'], true)
                                                            && $this->usesSamplingEquipmentIdPicker());
                                                @endphp
                                                <div class="mt-2 rft-sample-field-nested" wire:key="row-nested-{{ $activeSection->id }}-{{ $rowIndex }}-{{ $nestedElement->id }}">
                                                    @if($nestedIsEquipmentId)
                                                        @include('livewire.partials.walk-in-trf-equipment-id-rows', [
                                                            'sampleRowIndex' => $rowIndex,
                                                        ])
                                                    @else
                                                    <label>
                                                        {{ $nestedColumn['label'] ?? ($nestedField['label'] ?? $nestedName) }}
                                                        @if($nestedField['required'] ?? false)<span class="text-danger">*</span>@endif
                                                    </label>
                                                    @include('livewire.sampleworkflow.test-request-field-render', [
                                                        'field' => $nestedField,
                                                        'wirePrefix' => 'formData.'.$nestedElement->name.'.'.$rowIndex,
                                                        'fieldId' => $nestedElement->name.'_'.$rowIndex,
                                                        'rowIndex' => $rowIndex,
                                                        'compact' => false,
                                                        'hideLabel' => true,
                                                    ])
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach

                    @if($cardLayout['extra_columns'] !== [])
                        <div class="row rft-sample-grid-row">
                            @foreach($cardLayout['extra_columns'] as $column)
                                @php
                                    $element = $column['element'];
                                    $field = $column['field'] ?? $fieldMapper->toField($element);
                                    $fieldName = (string) ($field['name'] ?? $element->name ?? '');
                                @endphp
                                <div class="col-md-4 rft-sample-field" wire:key="row-extra-{{ $activeSection->id }}-{{ $rowIndex }}-{{ $element->id }}">
                                    <label>
                                        {{ $column['label'] ?? ($field['label'] ?? $fieldName) }}
                                        @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                    </label>
                                    @include('livewire.sampleworkflow.test-request-field-render', [
                                        'field' => $field,
                                        'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                        'fieldId' => $element->name.'_'.$rowIndex,
                                        'rowIndex' => $rowIndex,
                                        'compact' => false,
                                        'hideLabel' => true,
                                    ])
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @php
                        $catalogRenderedInGrid = collect($cardLayout['grid_rows'] ?? [])->contains(
                            static fn (array $row): bool => ($row['type'] ?? '') === 'catalog'
                        );
                    @endphp
                    @if(($cardLayout['catalog_row'] ?? null) && ! $catalogRenderedInGrid)
                        @php $catalogRow = $cardLayout['catalog_row']; @endphp
                        <div class="rv-trf-catalog-row rft-sample-catalog-row rft-sample-catalog-row--type-tests">
                            @foreach(['sample_type' => 'Sample type', 'parameters' => 'Tests'] as $catalogKey => $defaultLabel)
                                @php $column = $catalogRow[$catalogKey] ?? null; @endphp
                                @if($column !== null)
                                    @php
                                        $element = $column['element'];
                                        $field = $column['field'] ?? $fieldMapper->toField($element);
                                    @endphp
                                    <div class="rv-trf-catalog-row__cell">
                                        <label>
                                            {{ $column['label'] ?? $defaultLabel }}
                                            @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                        </label>
                                        @if($catalogKey === 'parameters')
                                            @include('livewire.partials.walk-in-trf-parameters-cell', [
                                                'fieldId' => $element->name.'_'.$rowIndex,
                                                'rowIndex' => $rowIndex,
                                                'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                                'formData' => $formData,
                                            ])
                                        @else
                                            <div class="rv-trf-select-shell">
                                                @include('livewire.sampleworkflow.test-request-field-render', [
                                                    'field' => $field,
                                                    'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                                    'fieldId' => $element->name.'_'.$rowIndex,
                                                    'rowIndex' => $rowIndex,
                                                    'compact' => false,
                                                    'hideLabel' => true,
                                                ])
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($cardLayout['parameters_column'])
                        @php
                            $element = $cardLayout['parameters_column']['element'];
                            $field = $cardLayout['parameters_column']['field'] ?? $fieldMapper->toField($element);
                        @endphp
                        <div class="rft-sample-field rft-sample-field--full">
                            <label>
                                {{ $cardLayout['parameters_column']['label'] ?? 'Parameters' }}
                                @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                            </label>
                            @include('livewire.partials.walk-in-trf-parameters-multi', [
                                'fieldId' => $element->name.'_'.$rowIndex,
                                'rowIndex' => $rowIndex,
                                'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                'label' => $cardLayout['parameters_column']['label'] ?? 'Tests',
                                'required' => (bool) ($field['required'] ?? false),
                                'hideLabel' => true,
                            ])
                        </div>
                    @endif

                    @if($cardLayout['description_column'])
                        @php
                            $element = $cardLayout['description_column']['element'];
                            $field = $cardLayout['description_column']['field'] ?? $fieldMapper->toField($element);
                        @endphp
                        <div class="rft-sample-field rft-sample-field--full rft-sample-field--description">
                            <label>
                                {{ $cardLayout['description_column']['label'] ?? 'Sample description' }}
                                @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                            </label>
                            @include('livewire.partials.walk-in-trf-sample-description-inline', [
                                'rowIdx' => $rowIndex,
                                'wirePrefix' => 'formData.sample_description.'.$rowIndex,
                                'formData' => $formData,
                            ])
                        </div>
                    @endif

                    @include('livewire.partials.walk-in-trf-additional-details', [
                        'rowIndex' => $rowIndex,
                        'formData' => $formData,
                    ])
                </div>
        </article>
    @endfor

    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addSchemaRow('{{ $activeSection->id }}')">
        <i class="mdi mdi-plus"></i> Add sample
    </button>
</div>
