@php
    $cardLayout = $this->walkInSampleCardLayout($tableColumns, $hiddenWalkInTrfFields);
@endphp

<div class="rft-sample-cards" x-data="{
    openRow: 0,
    setOpenRow(row) {
        const previous = this.openRow;
        this.openRow = this.openRow === row ? -1 : row;
        if (previous >= 0 && previous !== this.openRow) {
            this.$dispatch('rft-sample-card-hidden', { row: previous });
        }
        if (this.openRow >= 0) {
            this.$nextTick(() => this.$dispatch('rft-sample-card-shown', { row: this.openRow }));
        }
    }
}">
    @for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
        @php
            $summary = $this->walkInSampleRowSummary($rowIndex);
        @endphp

        <article
            class="rft-sample-row-card"
            wire:key="row-card-{{ $activeSection->id }}-{{ $rowIndex }}"
            :class="{ 'is-open': openRow === {{ $rowIndex }} }"
        >
            <div class="rft-sample-row-card__header">
                <button
                    type="button"
                    class="rft-sample-row-card__toggle-main border-0 bg-transparent p-0 text-left flex-grow-1 min-width-0"
                    @click="setOpenRow({{ $rowIndex }})"
                >
                    <h6 class="rft-sample-row-card__title mb-1">
                        Sample {{ $rowIndex + 1 }}
                    </h6>
                    <div class="rft-sample-row-card__summary text-muted small" x-show="openRow !== {{ $rowIndex }}" x-cloak>
                        <span>{{ $summary['analysis_label'] !== '' ? $summary['analysis_label'] : 'No analysis' }}</span>
                        <span class="mx-1">·</span>
                        <span>{{ $summary['param_count'] }} parameter{{ $summary['param_count'] === 1 ? '' : 's' }}</span>
                        @if($summary['param_preview'] !== '')
                            <span class="mx-1">·</span>
                            <span class="rft-sample-row-card__preview">{{ $summary['param_preview'] }}{{ $summary['param_count'] > 2 ? '…' : '' }}</span>
                        @endif
                    </div>
                </button>
                <div class="rft-sample-row-card__header-actions">
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
                x-show="openRow === {{ $rowIndex }}"
                x-cloak
            >
                    @foreach($cardLayout['grid_rows'] as $gridRow)
                        <div class="row rft-sample-grid-row">
                            @foreach($gridRow as $column)
                                <div class="col-md-4 rft-sample-field">
                                    @if($column === null)
                                        <div class="rft-sample-field--empty"></div>
                                    @else
                                        @php
                                            $element = $column['element'];
                                            $field = $column['field'] ?? $fieldMapper->toField($element);
                                            $fieldName = (string) ($field['name'] ?? $element->name ?? '');
                                        @endphp
                                        <label>
                                            {{ $column['label'] ?? ($field['label'] ?? $fieldName) }}
                                            @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                        </label>
                                        @if(($column['type'] ?? '') === 'qty_unit')
                                            @include('livewire.partials.walk-in-trf-qty-unit-cell', [
                                                'rowIndex' => $rowIndex,
                                                'compact' => false,
                                                'quantityField' => 'sample_quantity',
                                                'unitField' => 'sample_quantity_unit',
                                            ])
                                        @else
                                            @include('livewire.sampleworkflow.test-request-field-render', [
                                                'field' => $field,
                                                'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                                'fieldId' => $element->name.'_'.$rowIndex,
                                                'rowIndex' => $rowIndex,
                                                'compact' => false,
                                                'hideLabel' => true,
                                            ])
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
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
                            @include('livewire.partials.walk-in-trf-parameters-cell', [
                                'fieldId' => $element->name.'_'.$rowIndex,
                                'rowIndex' => $rowIndex,
                                'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                'compact' => false,
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
                </div>
        </article>
    @endfor

    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addSchemaRow('{{ $activeSection->id }}')">
        <i class="mdi mdi-plus"></i> Add sample
    </button>
</div>
