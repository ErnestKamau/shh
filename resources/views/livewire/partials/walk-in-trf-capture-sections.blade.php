@php
    use App\Services\Sampleworkflow\WalkInTrfFieldMapper;
    use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
    $fieldMapper = app(WalkInTrfFieldMapper::class);
    $hiddenWalkInTrfFields = [
        'job_number',
        'crm_contact_id',
        'customer_tax_id',
        // Hidden in UI; autofilled from step-1 contact on submit.
        'customer_representative_name',
        'customer_representative_contact',
        'customer_rep_name',
        'customer_rep_contact',
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
    $customerPrimaryFields = ['customer_name', 'client_name', 'customer', 'client'];
    $customerContactFields = ['contact_person'];
    $customerAutofillFields = [
        'customer_phone',
        'tel_fax_no',
        'phone',
        'telephone',
        'mobile_number',
        'customer_address',
        'address',
        'physical_address',
        'customer_email',
        'email',
        'email_address',
    ];
    $activeSection = $walkInSections->values()->get($walkInActiveStepIndex);
    $isCustomerSection = ($activeSection->title ?? '') === 'Customer details';
    $useCardRows = (bool) ($this->pageMode ?? false);

    $shouldOmitWalkInElement = static function ($element) use ($activeSection, $hiddenWalkInTrfFields): bool {
        $elementName = (string) ($element->name ?? '');

        if ($elementName !== '' && in_array($elementName, $hiddenWalkInTrfFields, true)) {
            return true;
        }

        return SubmissionFormSchemaHelper::shouldOmitFromFillForm($element, $activeSection);
    };
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
            $isRowsSection = $this->walkInSectionUsesSampleCards($activeSection);
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
                foreach (['sample_quantity', 'sample_quantity_unit'] as $qtyField) {
                    if (isset($formData[$qtyField]) && is_array($formData[$qtyField])) {
                        $rowCount = max($rowCount, count($formData[$qtyField]));
                    }
                }
            @endphp

            @if($useCardRows)
                @include('livewire.partials.walk-in-trf-sample-cards', [
                    'activeSection' => $activeSection,
                    'tableColumns' => $tableColumns,
                    'rowCount' => $rowCount,
                    'formData' => $formData,
                    'fieldMapper' => $fieldMapper,
                    'hiddenWalkInTrfFields' => $hiddenWalkInTrfFields,
                ])
            @else
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
            @endif
        @elseif($isCustomerSection)
            @php
                $allElements = $activeSection->elementHolders
                    ->sortBy('sort_order')
                    ->flatMap(fn ($holder) => $holder->elements->sortBy('sort_order'))
                    ->reject(fn ($el) => $shouldOmitWalkInElement($el))
                    ->values();
                $clientElement = $allElements->first(fn ($el) => in_array((string) $el->name, $customerPrimaryFields, true));
                $contactElement = $allElements->first(fn ($el) => in_array((string) $el->name, $customerContactFields, true));
                $autofillElements = $allElements
                    ->filter(fn ($el) => in_array((string) $el->name, $customerAutofillFields, true))
                    ->unique(fn ($el) => (string) $el->name)
                    ->values();
                $remainingElements = $allElements
                    ->filter(function ($el) use ($customerPrimaryFields, $customerContactFields, $customerAutofillFields) {
                        $elementName = (string) ($el->name ?? '');

                        return $elementName !== ''
                            && ! in_array($elementName, $customerPrimaryFields, true)
                            && ! in_array($elementName, $customerContactFields, true)
                            && ! in_array($elementName, $customerAutofillFields, true);
                    })
                    ->unique(fn ($el) => (string) $el->name)
                    ->values();
            @endphp
            <div class="row">
                @if($clientElement)
                    <div class="col-md-6 mb-3" wire:key="field-{{ $clientElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => array_merge($fieldMapper->toField($clientElement), ['label' => 'Client']),
                        ])
                    </div>
                @endif
                @if($contactElement)
                    <div class="col-md-6 mb-3" wire:key="field-{{ $contactElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => $fieldMapper->toField($contactElement),
                        ])
                    </div>
                @endif
            </div>
            <div class="row">
                @foreach($autofillElements as $element)
                    <div class="col-md-6 mb-3" wire:key="field-{{ $element->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => array_merge($fieldMapper->toField($element), ['readonly' => true]),
                        ])
                    </div>
                @endforeach
            </div>
            {{-- Render any remaining customer fields not covered above --}}
            <div class="row">
                @foreach($remainingElements as $element)
                    <div class="col-md-6 mb-3" wire:key="field-{{ $element->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => $fieldMapper->toField($element),
                        ])
                    </div>
                @endforeach
            </div>
        @else
            <div class="row">
                @foreach($activeSection->elementHolders->sortBy('sort_order') as $holder)
                    @foreach($holder->elements->sortBy('sort_order') as $element)
                        @php
                            $elementName = (string) ($element->name ?? '');
                            $hideInCollectionSection = ($activeSection->title ?? '') === 'Sample collection data'
                                && in_array($elementName, $miscellaneousOnlyTrfFieldNames, true);
                        @endphp
                        @if(! $shouldOmitWalkInElement($element) && ! $hideInCollectionSection)
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
