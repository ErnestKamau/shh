@if($showTrfEditModal)
    @php
        $isWaterTrf = $this->isWaterTrf();
        $fieldsByName = collect($trfEditSampleDefinitions)->keyBy(fn (array $field): string => (string) ($field['name'] ?? ''));
        $slides = [
            'customer' => 'Customer',
            'collection' => 'Collection',
            'samples' => 'Samples',
        ];
        $slideKeys = array_keys($slides);
        $slideIndex = array_search($trfEditSlide, $slideKeys, true);
        $prevSlide = $slideIndex > 0 ? $slideKeys[$slideIndex - 1] : null;
        $nextSlide = ($slideIndex !== false && $slideIndex < count($slideKeys) - 1)
            ? $slideKeys[$slideIndex + 1]
            : null;
    @endphp
    <div class="rv-modal-backdrop rv-trf-edit-modal rv-sample-row-edit-modal"
         wire:keydown.escape.window="closeTrfEditor">
        <div class="rv-trf-edit-stage">
            @if($prevSlide)
                <button type="button"
                    class="rv-trf-carousel-nav rv-trf-carousel-nav--prev"
                    wire:click="setTrfEditSlide('{{ $prevSlide }}')"
                    aria-label="Previous section">
                    <i class="mdi mdi-chevron-left" aria-hidden="true"></i>
                </button>
            @else
                <span class="rv-trf-carousel-nav rv-trf-carousel-nav--prev is-disabled" aria-hidden="true">
                    <i class="mdi mdi-chevron-left" aria-hidden="true"></i>
                </span>
            @endif

            <div class="rv-modal rv-modal--xl rv-sample-row-edit-dialog rv-trf-edit-dialog"
                 wire:ignore.self
                 role="dialog"
                 aria-modal="true">
                <div class="rv-modal-header">
                    <div>
                        <h4 class="rv-modal-title mb-1">Edit request details</h4>
                        <p class="rv-trf-edit-subtitle mb-0">Update customer, collection, and sample information. Changes save together.</p>
                    </div>
                    <button type="button" class="rv-modal-close" wire:click="closeTrfEditor" aria-label="Close">
                        <i class="mdi mdi-close" aria-hidden="true"></i>
                    </button>
                </div>

                <nav class="rv-trf-edit-steps" aria-label="Edit sections">
                    @foreach($slides as $slideKey => $slideLabel)
                        <button type="button"
                            class="rv-trf-edit-step {{ $trfEditSlide === $slideKey ? 'is-active' : '' }}"
                            wire:click="setTrfEditSlide('{{ $slideKey }}')">
                            <span class="rv-trf-edit-step-index">{{ $loop->iteration }}</span>
                            {{ $slideLabel }}
                        </button>
                        @if(! $loop->last)
                            <span class="rv-trf-edit-step-divider" aria-hidden="true"></span>
                        @endif
                    @endforeach
                </nav>

                <div class="rv-modal-body rv-trf-edit-body">
                    @if($trfEditSlide === 'customer')
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title">Customer Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small font-weight-bold text-secondary mb-1">Client</label>
                                    <div class="form-control form-control-sm bg-light">{{ $trfEditClientName !== '' ? $trfEditClientName : '—' }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small font-weight-bold text-secondary mb-1" for="trf-edit-unit">Company unit / Site</label>
                                    <div wire:ignore class="rv-sample-row-select2-wrap">
                                        <select id="trf-edit-unit"
                                            class="form-control form-control-sm livewire-select2"
                                            data-wire-field="trfEditCompanyUnitId"
                                            data-select-live="1"
                                            data-placeholder="Select unit"
                                            data-selected-values="{{ json_encode(array_values(array_filter([$trfEditCompanyUnitId]))) }}">
                                            <option value="">Select unit</option>
                                            @foreach($trfEditUnitOptions as $unit)
                                                <option value="{{ $unit['id'] }}" @selected($trfEditCompanyUnitId === (string) $unit['id'])>
                                                    {{ $unit['text'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title">Contact</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small font-weight-bold text-secondary mb-1" for="trf-edit-contact">Contact name</label>
                                    <div wire:ignore class="rv-sample-row-select2-wrap">
                                        <select id="trf-edit-contact"
                                            class="form-control form-control-sm livewire-select2"
                                            data-wire-field="trfEditContactId"
                                            data-select-live="1"
                                            data-placeholder="Select contact"
                                            data-selected-values="{{ json_encode(array_values(array_filter([$trfEditContactId]))) }}">
                                            <option value="">Select contact</option>
                                            @foreach($trfEditContactOptions as $contact)
                                                <option value="{{ $contact['id'] }}"
                                                    @selected($trfEditContactId === (string) $contact['id'])>
                                                    {{ $contact['text'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small font-weight-bold text-secondary mb-1" for="trf-edit-contact-email">Email</label>
                                    <input id="trf-edit-contact-email"
                                        type="email"
                                        class="form-control form-control-sm"
                                        wire:model.defer="trfEditContactEmail">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small font-weight-bold text-secondary mb-1" for="trf-edit-contact-phone">Phone</label>
                                    <input id="trf-edit-contact-phone"
                                        type="text"
                                        class="form-control form-control-sm"
                                        wire:model.defer="trfEditContactPhone">
                                </div>
                            </div>
                        </div>
                    @elseif($trfEditSlide === 'collection')
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title">Sample Collection Data</h5>
                            <div class="row rv-trf-collection-grid">
                                @php
                                    $collectionByName = collect($trfEditCollectionDefinitions)
                                        ->keyBy(fn (array $field): string => (string) ($field['name'] ?? ''));
                                    $priorityCollectionNames = [
                                        'sampling_date',
                                        'sampling_time',
                                        'date_received',
                                        'sampling_location',
                                        'transport_condition',
                                        'reason_of_collection',
                                        'sampling_apparatus',
                                        'method_of_sampling',
                                        'thermometer_id',
                                    ];
                                @endphp

                                {{-- Shared 3-column tracks so left/right edges stay aligned across rows --}}
                                <div class="col-12">
                                    <div class="rv-trf-collection-trio">
                                        @foreach([
                                            ['sampling_date', null],
                                            ['sampling_time', null],
                                            ['date_received', null],
                                        ] as [$name, $optionGridClass])
                                            @if($collectionByName->has($name))
                                                @include('livewire.submission-forms.request-view.partials.trf-edit-collection-field', [
                                                    'field' => $collectionByName->get($name),
                                                    'colClass' => '',
                                                    'hideOuterCol' => true,
                                                    'outerColExtraClass' => $name === 'date_received' ? 'rv-trf-collection-col--right' : ($name === 'sampling_date' ? 'rv-trf-collection-col--left' : 'rv-trf-collection-col--mid'),
                                                ])
                                            @else
                                                <div class="{{ $name === 'date_received' ? 'rv-trf-collection-col--right' : ($name === 'sampling_date' ? 'rv-trf-collection-col--left' : 'rv-trf-collection-col--mid') }}" aria-hidden="true"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="rv-trf-collection-trio">
                                        @foreach([
                                            ['sampling_location', null],
                                            ['transport_condition', 'rv-trf-option-grid rv-trf-option-grid--transport'],
                                            ['reason_of_collection', 'rv-trf-option-grid rv-trf-option-grid--compact'],
                                        ] as [$name, $optionGridClass])
                                            @if($collectionByName->has($name))
                                                @include('livewire.submission-forms.request-view.partials.trf-edit-collection-field', [
                                                    'field' => $collectionByName->get($name),
                                                    'colClass' => '',
                                                    'hideOuterCol' => true,
                                                    'outerColExtraClass' => $name === 'reason_of_collection' ? 'rv-trf-collection-col--right' : ($name === 'sampling_location' ? 'rv-trf-collection-col--left' : 'rv-trf-collection-col--mid'),
                                                    'optionGridClass' => $optionGridClass,
                                                ])
                                            @else
                                                <div class="{{ $name === 'reason_of_collection' ? 'rv-trf-collection-col--right' : ($name === 'sampling_location' ? 'rv-trf-collection-col--left' : 'rv-trf-collection-col--mid') }}" aria-hidden="true"></div>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if($collectionByName->has('sampling_apparatus') || $collectionByName->has('method_of_sampling') || $collectionByName->has('thermometer_id'))
                                        <div class="rv-trf-collection-trio rv-trf-collection-trio--bottom">
                                            <div class="rv-trf-collection-col--left">
                                                @if($collectionByName->has('sampling_apparatus'))
                                                    @include('livewire.submission-forms.request-view.partials.trf-edit-collection-field', [
                                                        'field' => $collectionByName->get('sampling_apparatus'),
                                                        'colClass' => '',
                                                        'hideOuterCol' => true,
                                                        'optionGridClass' => 'rv-trf-option-grid rv-trf-option-grid--apparatus',
                                                    ])
                                                @endif
                                                @if($collectionByName->has('thermometer_id'))
                                                    <div class="mt-3">
                                                        @include('livewire.submission-forms.request-view.partials.trf-edit-collection-field', [
                                                            'field' => $collectionByName->get('thermometer_id'),
                                                            'colClass' => '',
                                                            'hideOuterCol' => true,
                                                        ])
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="rv-trf-collection-col--mid" aria-hidden="true"></div>
                                            <div class="rv-trf-collection-col--right">
                                                @if($collectionByName->has('method_of_sampling'))
                                                    @include('livewire.submission-forms.request-view.partials.trf-edit-collection-field', [
                                                        'field' => $collectionByName->get('method_of_sampling'),
                                                        'colClass' => '',
                                                        'hideOuterCol' => true,
                                                        'optionGridClass' => 'rv-trf-option-grid rv-trf-option-grid--method',
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @foreach($trfEditCollectionDefinitions as $field)
                                    @php $name = (string) ($field['name'] ?? ''); @endphp
                                    @if($name === '' || in_array($name, $priorityCollectionNames, true))
                                        @continue
                                    @endif
                                    @include('livewire.submission-forms.request-view.partials.trf-edit-collection-field', [
                                        'field' => $field,
                                        'colClass' => 'col-md-6',
                                    ])
                                @endforeach

                                @if(count($trfEditCollectionDefinitions) === 0)
                                    <div class="col-12">
                                        <p class="text-muted mb-0 small">No sample collection fields on this form.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title">Sample Details</h5>
                            <p class="rv-trf-edit-section-hint">Open a sample to edit its details. You can edit sample data, state of sample , condition and tests to be perfomed</p>
                            <div class="rv-trf-sample-list">
                                @forelse($trfEditSampleSummaries as $sampleSummary)
                                    @php
                                        $sampleIndex = (int) $sampleSummary['index'];
                                        $isExpanded = $trfEditExpandedSampleIndex === $sampleIndex;
                                    @endphp
                                    <div class="rv-trf-sample-card {{ $isExpanded ? 'is-expanded' : '' }}"
                                         data-trf-sample-index="{{ $sampleIndex }}"
                                         x-data="{ open: {{ $isExpanded ? 'true' : 'false' }} }"
                                         x-on:trf-sample-card-toggled.window="if ($event.detail.rowIndex === {{ $sampleIndex }}) open = !!$event.detail.open"
                                         :class="{ 'is-expanded': open }">
                                        <button type="button"
                                            class="rv-trf-sample-card-toggle"
                                            @click="
                                                open = !open;
                                                $wire.expandTrfSample({{ $sampleIndex }}, open);
                                            "
                                            :aria-expanded="open ? 'true' : 'false'">
                                            <span>{{ $sampleSummary['summary'] }}</span>
                                            <i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
                                        </button>

                                        @if($isExpanded)
                                            @php
                                                $skipInLoop = [
                                                    'sample_quantity_unit',
                                                    'sample_type_id',
                                                    'analysis_type_id',
                                                    'parameters',
                                                    'sample_description',
                                                    'sample_condition',
                                                    'sample_temp',
                                                    'field_sample_temp',
                                                    'test_category',
                                                ];
                                                $sampleTypeField = $fieldsByName->get('sample_type_id');
                                                $analysisTypeField = $fieldsByName->get('analysis_type_id');
                                                $parametersField = $fieldsByName->get('parameters');
                                                $testCategoryField = $fieldsByName->get('test_category');
                                                $conditionField = $fieldsByName->get('sample_condition');
                                                $tempField = $fieldsByName->get('sample_temp') ?? $fieldsByName->get('field_sample_temp');
                                                $descriptionField = $fieldsByName->get('sample_description');
                                            @endphp
                                            <div class="rv-trf-sample-card-body"
                                                 wire:key="trf-sample-body-{{ $sampleIndex }}"
                                                 x-show="open"
                                                 x-cloak>
                                                <div class="row">
                                                    {{-- Catalog selects: type | analysis, then tests --}}
                                                    @if($sampleTypeField || $analysisTypeField)
                                                        <div class="col-12 mb-3">
                                                            <div class="rv-trf-catalog-row">
                                                                @if($sampleTypeField)
                                                                    <div class="rv-trf-catalog-row__cell">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $sampleTypeField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                                @if($analysisTypeField)
                                                                    <div class="rv-trf-catalog-row__cell">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $analysisTypeField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($parametersField)
                                                        <div class="col-12 mb-3">
                                                            @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                'field' => $parametersField,
                                                                'hideOuterCol' => true,
                                                            ])
                                                        </div>
                                                    @endif

                                                    {{-- Mid fields (batch, production date, etc.) --}}
                                                    @foreach($trfEditSampleDefinitions as $field)
                                                        @php
                                                            $fieldName = (string) ($field['name'] ?? '');
                                                        @endphp
                                                        @if($fieldName === '' || in_array($fieldName, $skipInLoop, true))
                                                            @continue
                                                        @endif

                                                        @if($fieldName === 'sampling_point_manual')
                                                            <div class="col-md-6 mb-3">
                                                                @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                    'field' => $field,
                                                                    'hideOuterCol' => true,
                                                                ])
                                                            </div>
                                                            @continue
                                                        @endif

                                                        @if($fieldName === 'sample_quantity')
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small font-weight-bold text-secondary mb-1">Qty / Unit</label>
                                                                @include('livewire.submission-forms.request-view.partials.sample-row-qty-unit')
                                                            </div>
                                                            @continue
                                                        @endif

                                                        @if($isWaterTrf && $fieldName === 'test_requirements')
                                                            @continue
                                                        @endif

                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                            'field' => $field,
                                                        ])
                                                    @endforeach

                                                    @if($isWaterTrf && $fieldsByName->has('test_requirements'))
                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                            'field' => $fieldsByName->get('test_requirements'),
                                                            'colClass' => 'col-md-6',
                                                        ])
                                                    @endif

                                                    {{-- Same Bootstrap columns as Batch | Production: Test category | Sample condition + temp --}}
                                                    @if($testCategoryField)
                                                        <div class="col-md-6 mb-3">
                                                            @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                'field' => $testCategoryField,
                                                                'hideOuterCol' => true,
                                                            ])
                                                        </div>
                                                    @endif
                                                    @if($conditionField || $tempField)
                                                        <div class="col-md-6 mb-3">
                                                            <div class="rv-trf-condition-temp-row">
                                                                @if($conditionField)
                                                                    <div class="rv-trf-condition-temp-row__condition">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $conditionField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                                @if($tempField)
                                                                    <div class="rv-trf-condition-temp-row__temp">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $tempField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @elseif($testCategoryField)
                                                        {{-- Keep row balance when only category exists --}}
                                                        <div class="col-md-6 mb-3"></div>
                                                    @endif

                                                    {{-- Description last --}}
                                                    @if($descriptionField)
                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                            'field' => $descriptionField,
                                                            'colClass' => 'col-12',
                                                        ])
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted mb-0 small">No sample rows on this request yet.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rv-modal-footer rv-trf-edit-footer">
                    <div class="rv-trf-edit-footer-left"></div>
                    <div class="rv-trf-edit-footer-dots" aria-hidden="true">
                        @foreach($slideKeys as $key)
                            <span class="rv-trf-edit-dot {{ $trfEditSlide === $key ? 'is-active' : '' }}"></span>
                        @endforeach
                    </div>
                    <div class="rv-trf-edit-footer-right">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeTrfEditor">Cancel</button>
                        <button type="button"
                            class="btn btn-sm btn-primary"
                            data-sample-row-save
                            wire:click="saveTrfEditor"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveTrfEditor">Save changes</span>
                            <span wire:loading wire:target="saveTrfEditor">Saving…</span>
                        </button>
                    </div>
                </div>
            </div>

            @if($nextSlide)
                <button type="button"
                    class="rv-trf-carousel-nav rv-trf-carousel-nav--next"
                    wire:click="setTrfEditSlide('{{ $nextSlide }}')"
                    aria-label="Next section">
                    <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                </button>
            @else
                <span class="rv-trf-carousel-nav rv-trf-carousel-nav--next is-disabled" aria-hidden="true">
                    <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                </span>
            @endif
        </div>
    </div>
@endif
