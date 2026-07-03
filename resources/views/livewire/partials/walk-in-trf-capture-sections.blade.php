@php
    use App\Services\Sampleworkflow\WalkInTrfFieldMapper;
    $fieldMapper = app(WalkInTrfFieldMapper::class);
    $hiddenWalkInTrfFields = [
        'job_number',
        'crm_contact_id',
        'customer_tax_id',
    ];
    $miscellaneousOnlyTrfFieldNames = [
        'packaging',
        'sample_weight',
        'sample_information',
        'ship_name',
        'port_of_loading',
        'port_of_discharge',
        'seal_number',
    ];
    $activeSection = $walkInSections->values()->get($walkInActiveStepIndex);
@endphp

@if($submissionForm && $activeSection)
    <div
        class="walk-in-trf-wizard__panel"
        role="tabpanel"
        id="walk-in-trf-step-panel-{{ $walkInActiveStepIndex }}"
        aria-labelledby="walk-in-trf-step-tab-{{ $walkInActiveStepIndex }}"
        wire:key="walk-in-trf-step-{{ $activeSection->id }}-{{ $walkInActiveStepIndex }}"
    >
        <h6 class="walk-in-trf-wizard__panel-title">{{ $activeSection->title }}</h6>
        @if(! empty($activeSection->description))
            <p class="walk-in-trf-wizard__panel-desc">{{ $activeSection->description }}</p>
        @endif

        @php
            $isRowsSection = ($activeSection->section_type ?? '') === 'rows_section';
        @endphp

        @if($isRowsSection)
            @php
                $tableColumns = $this->walkInRowTableColumns($activeSection);
                $rowCount = 1;
                foreach ($this->uniqueRowElementsForSection($activeSection) as $el) {
                    $name = (string) $el->name;
                    if (isset($formData[$name]) && is_array($formData[$name])) {
                        $rowCount = max($rowCount, count($formData[$name]));
                    }
                }
                if (isset($formData['sample_quantity_unit']) && is_array($formData['sample_quantity_unit'])) {
                    $rowCount = max($rowCount, count($formData['sample_quantity_unit']));
                }
            @endphp
            <div class="table-responsive walk-in-trf-rows-table">
                <table class="table table-bordered table-sm mb-2 walk-in-trf-rows-grid">
                    <thead class="bg-secondary text-white text-center small">
                        <tr>
                            <th class="walk-in-trf-col-sn">S. No.</th>
                            @foreach($tableColumns as $column)
                                @php $fieldName = (string) ($column['element']->name ?? ''); @endphp
                                @if(! in_array($fieldName, $hiddenWalkInTrfFields, true))
                                    <th class="{{ $column['class'] }}">{{ $column['label'] }}</th>
                                @endif
                            @endforeach
                            <th class="walk-in-trf-col-actions"></th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
                            <tr wire:key="row-{{ $activeSection->id }}-{{ $rowIndex }}">
                                <td class="text-center align-middle font-weight-bold walk-in-trf-col-sn">{{ $rowIndex + 1 }}</td>
                                @foreach($tableColumns as $column)
                                    @php
                                        $element = $column['element'];
                                        $field = $column['field'] ?? $fieldMapper->toField($element);
                                        $fieldName = (string) ($field['name'] ?? $element->name ?? '');
                                    @endphp
                                    @if(in_array($fieldName, $hiddenWalkInTrfFields, true))
                                        @continue
                                    @endif
                                    <td class="align-top {{ $column['class'] }}" wire:key="row-el-{{ $activeSection->id }}-{{ $rowIndex }}-{{ $element->id }}">
                                        @if($column['type'] === 'qty_unit')
                                            @include('livewire.partials.walk-in-trf-qty-unit-cell', ['rowIndex' => $rowIndex])
                                        @elseif($fieldName === 'sample_description')
                                            @include('livewire.partials.trf-sample-description-modal-cell', [
                                                'rowIdx' => $rowIndex,
                                                'sectionId' => $activeSection->id,
                                                'wirePrefix' => 'formData.sample_description.'.$rowIndex,
                                                'formData' => $formData,
                                            ])
                                        @else
                                            @include('livewire.sampleworkflow.test-request-field-render', [
                                                'field' => $field,
                                                'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                                'fieldId' => $element->name.'_'.$rowIndex,
                                                'rowIndex' => $rowIndex,
                                                'compact' => true,
                                            ])
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center align-middle walk-in-trf-col-actions">
                                    @if($rowCount > 1)
                                        <button type="button" class="btn btn-danger btn-xs p-1"
                                            wire:click="removeSchemaRow('{{ $activeSection->id }}', {{ $rowIndex }})"
                                            title="Remove row">
                                            <i class="mdi mdi-trash-can"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addSchemaRow('{{ $activeSection->id }}')">
                <i class="mdi mdi-plus"></i> Add sample row
            </button>
        @else
            <div class="row">
                @foreach($activeSection->elementHolders->sortBy('sort_order') as $holder)
                    @foreach($holder->elements->sortBy('sort_order') as $element)
                        @php
                            $elementName = (string) ($element->name ?? '');
                            $hideInCollectionSection = ($activeSection->title ?? '') === 'Sample collection data'
                                && in_array($elementName, $miscellaneousOnlyTrfFieldNames, true);
                        @endphp
                        @if(! in_array($elementName, $hiddenWalkInTrfFields, true) && ! $hideInCollectionSection)
                            <div class="col-md-6 mb-3" wire:key="field-{{ $element->id }}">
                                @include('livewire.sampleworkflow.test-request-field-render', [
                                    'field' => $fieldMapper->toField($element),
                                ])
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        @endif
    </div>
@endif
