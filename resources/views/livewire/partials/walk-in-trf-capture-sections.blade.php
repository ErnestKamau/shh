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
    $customerCompanyUnitFields = ['company_unit_id'];
    $customerContactFields = ['contact_person'];
    $customerEmailFields = ['customer_email', 'email', 'email_address'];
    $customerMobileFields = ['mobile_number'];
    $customerPhoneFields = ['customer_phone', 'tel_fax_no', 'phone', 'telephone'];
    $customerAddressFields = ['customer_address', 'address', 'physical_address'];
    $customerAutofillFields = array_merge(
        $customerEmailFields,
        $customerMobileFields,
        $customerPhoneFields,
        $customerAddressFields,
    );
    $activeSection = $walkInSections->values()->get($walkInActiveStepIndex);
    $isCustomerSection = ($activeSection->title ?? '') === 'Customer details';
    $useCardRows = (bool) ($this->pageMode ?? false) || $this->isOfflineIntake();
    $stepCardTitle = $this->isOfflineIntake()
        ? 'Batch and sample details'
        : (string) ($activeSection->title ?? 'Section');
    $stepCardSummary = $this->isOfflineIntake()
        ? 'Copy each sample from the paper form. Add a row when the TRF has more samples.'
        : trim((string) ($activeSection->description ?? ''));

    $shouldOmitWalkInElement = static function ($element) use ($activeSection, $hiddenWalkInTrfFields): bool {
        $elementName = (string) ($element->name ?? '');

        if ($elementName !== '' && in_array($elementName, $hiddenWalkInTrfFields, true)) {
            return true;
        }

        return SubmissionFormSchemaHelper::shouldOmitFromFillForm($element, $activeSection);
    };
@endphp

@if($submissionForm && $activeSection)
    @php
        $isRowsSection = $this->walkInSectionUsesSampleCards($activeSection);
        $wrapInStepCard = $useCardRows && ! $isRowsSection;
        $panelClass = 'walk-in-trf-wizard__panel'.($isRowsSection ? ' walk-in-trf-wizard__panel--samples' : '');
    @endphp
    <div
        class="{{ $panelClass }}"
        role="tabpanel"
        id="walk-in-trf-step-panel-{{ $walkInActiveStepIndex }}"
        aria-labelledby="walk-in-trf-step-tab-{{ $walkInActiveStepIndex }}"
        wire:key="walk-in-trf-step-{{ $activeSection->id }}-{{ $walkInActiveStepIndex }}"
    >
        @if(! $wrapInStepCard)
            <h6 class="walk-in-trf-wizard__panel-title">{{ $stepCardTitle }}</h6>
            @if($stepCardSummary !== '')
                <p class="walk-in-trf-wizard__panel-desc">{{ $stepCardSummary }}</p>
            @endif
        @endif

        @if($wrapInStepCard)
            @include('livewire.partials.walk-in-trf-step-card-open', [
                'stepCardTitle' => $stepCardTitle,
                'stepCardSummary' => $stepCardSummary,
                'stepCardKey' => 'trf-step-card-'.$activeSection->id,
                'stepCardOpen' => true,
            ])
        @endif

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
                    'expandAllSampleCards' => false,
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

                $findCustomerElement = static function (array $names) use ($allElements) {
                    return $allElements->first(fn ($el) => in_array((string) ($el->name ?? ''), $names, true));
                };

                $clientElement = $findCustomerElement($customerPrimaryFields);
                $companyUnitElement = $findCustomerElement($customerCompanyUnitFields);
                $contactElement = $findCustomerElement($customerContactFields);
                $emailElement = $findCustomerElement($customerEmailFields);
                $mobileElement = $findCustomerElement($customerMobileFields);
                $phoneElement = $findCustomerElement($customerPhoneFields);
                $addressElement = $findCustomerElement($customerAddressFields);

                $placedCustomerFieldNames = array_merge(
                    $customerPrimaryFields,
                    $customerCompanyUnitFields,
                    $customerContactFields,
                    $customerAutofillFields,
                );
                $remainingElements = $allElements
                    ->filter(function ($el) use ($placedCustomerFieldNames) {
                        $elementName = (string) ($el->name ?? '');

                        return $elementName !== ''
                            && ! in_array($elementName, $placedCustomerFieldNames, true);
                    })
                    ->unique(fn ($el) => (string) $el->name)
                    ->values();
            @endphp

            <div class="row rft-customer-grid-row">
                @if($clientElement)
                    <div class="col-md-4 mb-3" wire:key="field-{{ $clientElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => array_merge($fieldMapper->toField($clientElement), ['label' => 'Client']),
                        ])
                    </div>
                @endif
                @if($companyUnitElement)
                    <div class="col-md-4 mb-3" wire:key="field-{{ $companyUnitElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => $fieldMapper->toField($companyUnitElement),
                        ])
                    </div>
                @endif
                @if($contactElement)
                    <div class="col-md-4 mb-3" wire:key="field-{{ $contactElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => $fieldMapper->toField($contactElement),
                        ])
                    </div>
                @endif
            </div>

            <div class="row rft-customer-grid-row">
                @if($emailElement)
                    <div class="col-md-4 mb-3" wire:key="field-{{ $emailElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => array_merge($fieldMapper->toField($emailElement), ['readonly' => true]),
                        ])
                    </div>
                @endif
                @if($mobileElement)
                    <div class="col-md-4 mb-3" wire:key="field-{{ $mobileElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => array_merge($fieldMapper->toField($mobileElement), ['readonly' => true]),
                        ])
                    </div>
                @endif
                @if($phoneElement)
                    <div class="col-md-4 mb-3" wire:key="field-{{ $phoneElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => array_merge($fieldMapper->toField($phoneElement), ['readonly' => true]),
                        ])
                    </div>
                @endif
            </div>

            @if($addressElement)
                <div class="row">
                    <div class="col-12 mb-3" wire:key="field-{{ $addressElement->id }}">
                        @include('livewire.sampleworkflow.test-request-field-render', [
                            'field' => array_merge($fieldMapper->toField($addressElement), ['readonly' => true]),
                        ])
                    </div>
                </div>
            @endif

            @if($remainingElements->isNotEmpty())
                <div class="row">
                    @foreach($remainingElements as $element)
                        <div class="col-md-4 mb-3" wire:key="field-{{ $element->id }}">
                            @include('livewire.sampleworkflow.test-request-field-render', [
                                'field' => $fieldMapper->toField($element),
                            ])
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            @php
                $isCollectionSection = ($activeSection->title ?? '') === 'Sample collection data';
                $regularElements = $activeSection->elementHolders
                    ->sortBy('sort_order')
                    ->flatMap(fn ($holder) => $holder->elements->sortBy('sort_order'))
                    ->reject(fn ($el) => $shouldOmitWalkInElement($el))
                    ->reject(function ($el) use ($activeSection, $miscellaneousOnlyTrfFieldNames): bool {
                        $elementName = (string) ($el->name ?? '');
                        $hideInCollectionSection = ($activeSection->title ?? '') === 'Sample collection data'
                            && in_array($elementName, $miscellaneousOnlyTrfFieldNames, true);

                        return $hideInCollectionSection;
                    })
                    ->values();
                $thermometerElement = $isCollectionSection
                    ? $regularElements->first(fn ($el) => (string) ($el->name ?? '') === 'thermometer_id')
                    : null;
                $usesFoodCollectionLayout = $isCollectionSection && $this->usesFoodCollectionLayout();
            @endphp
            @if($usesFoodCollectionLayout)
                @php
                    $collectionField = function (string $name) use ($regularElements) {
                        return $this->walkInCollectionElementByName($regularElements, $name);
                    };
                @endphp
                {{-- Edit-modal style: date | time | received --}}
                <div class="trf-collection-trio" wire:key="collection-trio-dates">
                    <div wire:key="field-collection-sampling-date">
                        @php $el = $collectionField('sampling_date'); @endphp
                        @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
                    </div>
                    <div wire:key="field-collection-sampling-time">
                        @php $el = $collectionField('sampling_time'); @endphp
                        @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
                    </div>
                    <div wire:key="field-collection-date-received">
                        @php $el = $collectionField('date_received'); @endphp
                        @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
                    </div>
                </div>
                {{-- location | transport | reason --}}
                <div class="trf-collection-trio" wire:key="collection-trio-mid">
                    <div wire:key="field-collection-sampling-location">
                        @php $el = $collectionField('sampling_location'); @endphp
                        @if($el) @include('livewire.sampleworkflow.test-request-field-render', ['field' => $fieldMapper->toField($el)]) @endif
                    </div>
                    <div wire:key="field-collection-transport">
                        @php $el = $collectionField('transport_condition'); @endphp
                        @if($el)
                            @include('livewire.sampleworkflow.test-request-field-render', [
                                'field' => $fieldMapper->toField($el),
                                'optionGridClass' => 'trf-option-grid trf-option-grid--transport',
                            ])
                        @endif
                    </div>
                    <div wire:key="field-collection-reason">
                        @php $el = $collectionField('reason_of_collection'); @endphp
                        @if($el)
                            @include('livewire.sampleworkflow.test-request-field-render', [
                                'field' => $fieldMapper->toField($el),
                                'optionGridClass' => 'trf-option-grid trf-option-grid--compact',
                            ])
                        @endif
                    </div>
                </div>
                {{-- apparatus | · | method (+ thermometer under apparatus) --}}
                <div class="trf-collection-trio trf-collection-trio--bottom" wire:key="collection-trio-bottom">
                    <div wire:key="field-collection-apparatus">
                        @php $apparatusEl = $collectionField('sampling_apparatus'); @endphp
                        @if($apparatusEl)
                            @include('livewire.sampleworkflow.test-request-field-render', [
                                'field' => $fieldMapper->toField($apparatusEl),
                                'optionGridClass' => 'trf-option-grid trf-option-grid--apparatus',
                            ])
                            @if($thermometerElement)
                                <div class="mt-3" wire:key="field-{{ $thermometerElement->id }}-nested">
                                    @include('livewire.sampleworkflow.test-request-field-render', [
                                        'field' => $fieldMapper->toField($thermometerElement),
                                    ])
                                </div>
                            @endif
                        @endif
                    </div>
                    <div wire:key="field-collection-method">
                        @php $el = $collectionField('method_of_sampling'); @endphp
                        @if($el)
                            @include('livewire.sampleworkflow.test-request-field-render', [
                                'field' => $fieldMapper->toField($el),
                                'optionGridClass' => 'trf-option-grid trf-option-grid--method',
                            ])
                        @endif
                    </div>
                    <div aria-hidden="true"></div>
                </div>
            @else
            <div class="row">
                @foreach($regularElements as $element)
                    @php
                        $elementName = (string) ($element->name ?? '');
                        if ($isCollectionSection && $elementName === 'thermometer_id') {
                            continue;
                        }
                    @endphp
                    @if(! $shouldOmitWalkInElement($element))
                        <div class="col-md-6 mb-3" wire:key="field-{{ $element->id }}">
                            @include('livewire.sampleworkflow.test-request-field-render', [
                                'field' => $fieldMapper->toField($element),
                            ])
                            @if($isCollectionSection && $elementName === 'sampling_apparatus' && $thermometerElement)
                                <div class="mt-2" wire:key="field-{{ $thermometerElement->id }}-nested">
                                    @include('livewire.sampleworkflow.test-request-field-render', [
                                        'field' => $fieldMapper->toField($thermometerElement),
                                    ])
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
            @endif
        @endif

        @if($wrapInStepCard)
            @include('livewire.partials.walk-in-trf-step-card-close')
        @endif
    </div>
@endif
