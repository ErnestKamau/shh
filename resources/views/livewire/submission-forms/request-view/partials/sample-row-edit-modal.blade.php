@if($showTrfEditModal)
    @php
        $isWaterTrf = $this->isWaterTrf();
        $fieldsByName = collect($trfEditSampleDefinitions)->keyBy(fn (array $field): string => (string) ($field['name'] ?? ''));
        $slides = [
            'customer' => 'Customer',
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
                        <p class="rv-trf-edit-subtitle mb-0">Update customer and sample information (including per-sample collection details). Changes save together.</p>
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
                            <h5 class="rv-trf-edit-section-title ls-type-label">Customer Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                        'label' => 'Client',
                                        'id' => 'trf-edit-client',
                                        'name' => 'trf_edit_client',
                                        'value' => $trfEditClientName !== '' ? $trfEditClientName : '—',
                                        'disabled' => true,
                                        'success' => filled($trfEditClientName),
                                    ])
                                </div>
                                <div class="col-md-6 mb-3">
                                    @php
                                        $unitSearchOptions = collect($trfEditUnitOptions)->map(fn ($unit) => [
                                            'value' => (string) ($unit['id'] ?? ''),
                                            'label' => (string) ($unit['text'] ?? ''),
                                        ])->filter(fn ($o) => $o['value'] !== '')->values()->all();
                                    @endphp
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                                        'label' => 'Company unit / Site',
                                        'id' => 'trf-edit-unit',
                                        'name' => 'trfEditCompanyUnitId',
                                        'wireModel' => 'trfEditCompanyUnitId',
                                        'wireLive' => true,
                                        'placeholder' => 'Search company unit…',
                                        'options' => $unitSearchOptions,
                                        'selected' => $trfEditCompanyUnitId !== '' ? $trfEditCompanyUnitId : null,
                                        'success' => $trfEditCompanyUnitId !== '',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title ls-type-label">Contact</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3" wire:key="trf-edit-contact-{{ $trfEditCompanyUnitId }}-{{ count($trfEditContactOptions) }}">
                                    @php
                                        $contactSearchOptions = collect($trfEditContactOptions)->map(fn ($contact) => [
                                            'value' => (string) ($contact['id'] ?? ''),
                                            'label' => (string) ($contact['text'] ?? ''),
                                            'meta' => [
                                                'email' => (string) ($contact['email'] ?? ''),
                                                'phone' => (string) ($contact['phone'] ?? ''),
                                            ],
                                        ])->filter(fn ($o) => $o['value'] !== '')->values()->all();
                                    @endphp
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                                        'label' => 'Contact name',
                                        'id' => 'trf-edit-contact',
                                        'name' => 'trfEditContactId',
                                        'wireModel' => 'trfEditContactId',
                                        'wireLive' => true,
                                        'placeholder' => $trfEditCompanyUnitId !== ''
                                            ? 'Search contacts for this unit…'
                                            : 'Select a company unit first…',
                                        'options' => $contactSearchOptions,
                                        'selected' => $trfEditContactId !== '' ? $trfEditContactId : null,
                                        'success' => $trfEditContactId !== '',
                                        'hint' => $trfEditCompanyUnitId === ''
                                            ? 'Contacts are filtered by the selected company unit.'
                                            : (count($contactSearchOptions) === 0
                                                ? 'No CRM contacts linked to this company unit.'
                                                : null),
                                    ])
                                </div>
                                <div class="col-md-4 mb-3">
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                        'label' => 'Email',
                                        'id' => 'trf-edit-contact-email',
                                        'name' => 'trfEditContactEmail',
                                        'type' => 'email',
                                        'wireModel' => 'trfEditContactEmail',
                                        'placeholder' => 'Email',
                                        'success' => filled($trfEditContactEmail),
                                    ])
                                </div>
                                <div class="col-md-4 mb-3">
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                        'label' => 'Phone',
                                        'id' => 'trf-edit-contact-phone',
                                        'name' => 'trfEditContactPhone',
                                        'wireModel' => 'trfEditContactPhone',
                                        'placeholder' => 'Phone',
                                        'success' => filled($trfEditContactPhone),
                                    ])
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title ls-type-label">Sample Details</h5>
                            <p class="rv-trf-edit-section-hint ls-type-caption">Open a sample to edit collection details and sample/test information. Use “Copy collection from Sample 1” when samples share the same collection data.</p>
                            <div class="rv-trf-sample-list">
                                @forelse($trfEditSampleSummaries as $sampleSummary)
                                    @php
                                        $sampleIndex = (int) $sampleSummary['index'];
                                        $isExpanded = $trfEditExpandedSampleIndex === $sampleIndex;
                                    @endphp
                                    <div class="rv-trf-sample-card ls-trf-sample-panel {{ $isExpanded ? 'is-expanded' : '' }}"
                                         data-trf-sample-index="{{ $sampleIndex }}"
                                         x-data="{ open: {{ $isExpanded ? 'true' : 'false' }} }"
                                         x-on:trf-sample-card-toggled.window="if ($event.detail.rowIndex === {{ $sampleIndex }}) open = !!$event.detail.open"
                                         :class="{ 'is-expanded': open }">
                                        <button type="button"
                                            class="rv-trf-sample-card-toggle ls-trf-sample-panel__header"
                                            @click="
                                                open = !open;
                                                $wire.expandTrfSample({{ $sampleIndex }}, open);
                                            "
                                            :aria-expanded="open ? 'true' : 'false'">
                                            <span class="ls-trf-sample-panel__title">{{ $sampleSummary['summary'] }}</span>
                                            <i class="mdi ls-soft-card__chevron" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
                                        </button>

                                        @if($isExpanded)
                                            @php
                                                $collectionFieldNames = \App\Services\SubmissionForm\SubmissionFormSchemaHelper::sampleCollectionFieldNames();
                                                $skipInLoop = array_merge([
                                                    'sample_quantity_unit',
                                                    'sample_type_id',
                                                    'analysis_type_id',
                                                    'parameters',
                                                    'sample_description',
                                                    'sample_condition',
                                                    'sample_temp',
                                                    'field_sample_temp',
                                                    'test_category',
                                                ], $collectionFieldNames);
                                                if ($this->isWasteWaterTrf()) {
                                                    $skipInLoop[] = 'picture_of_samples';
                                                }
                                                $sampleTypeField = $fieldsByName->get('sample_type_id');
                                                $analysisTypeField = $fieldsByName->get('analysis_type_id');
                                                $parametersField = $fieldsByName->get('parameters');
                                                $testCategoryField = $fieldsByName->get('test_category');
                                                $conditionField = $fieldsByName->get('sample_condition');
                                                $tempField = $fieldsByName->get('sample_temp') ?? $fieldsByName->get('field_sample_temp');
                                                $descriptionField = $fieldsByName->get('sample_description');
                                                $collectionFields = collect($trfEditSampleDefinitions)
                                                    ->filter(fn (array $field): bool => in_array((string) ($field['name'] ?? ''), $collectionFieldNames, true))
                                                    ->values();
                                            @endphp
                                            <div class="rv-trf-sample-card-body"
                                                 wire:key="trf-sample-body-{{ $sampleIndex }}"
                                                 x-show="open"
                                                 x-cloak>
                                                <div class="row">
                                                    @if($collectionFields->isNotEmpty())
                                                        <div class="col-12 mb-2">
                                                            <div class="rft-sample-section-label rft-sample-section-label--collection">Sample collection</div>
                                                        </div>
                                                        @if($sampleIndex > 0)
                                                            <div class="col-12 mb-2">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-secondary"
                                                                    wire:click="copyTrfEditCollectionFromFirstSample({{ $sampleIndex }})">
                                                                    <i class="mdi mdi-content-copy"></i> Copy collection from Sample 1
                                                                </button>
                                                            </div>
                                                        @endif
                                                        @foreach($collectionFields as $field)
                                                            @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                'field' => $field,
                                                                'colClass' => in_array((string) ($field['name'] ?? ''), ['sample_sampling_point_description', 'extra_sampling_equipment'], true)
                                                                    ? 'col-12'
                                                                    : 'col-md-4',
                                                            ])
                                                        @endforeach
                                                        <div class="col-12 mb-2 mt-2">
                                                            <div class="rft-sample-section-label rft-sample-section-label--sample">Sample &amp; test information</div>
                                                        </div>
                                                    @endif

                                                    {{-- Catalog: sample type | tests (dropdown) --}}
                                                    @if($sampleTypeField || $parametersField)
                                                        <div class="col-12 mb-3">
                                                            <div class="rv-trf-catalog-row rv-trf-catalog-row--type-tests">
                                                                @if($sampleTypeField)
                                                                    <div class="rv-trf-catalog-row__cell">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $sampleTypeField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                                @if($parametersField)
                                                                    <div class="rv-trf-catalog-row__cell">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $parametersField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                            </div>
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
                                                                <label class="ls-field__label">Qty / Unit</label>
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

                                                    {{-- One row: Test category | Sample condition | Sample Temp --}}
                                                    @if($testCategoryField || $conditionField || $tempField)
                                                        <div class="col-12 mb-3">
                                                            <div class="rv-trf-category-condition-temp-row">
                                                                @if($testCategoryField)
                                                                    <div class="rv-trf-category-condition-temp-row__category">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $testCategoryField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                                @if($conditionField)
                                                                    <div class="rv-trf-category-condition-temp-row__condition">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $conditionField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                                @if($tempField)
                                                                    <div class="rv-trf-category-condition-temp-row__temp">
                                                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                                            'field' => $tempField,
                                                                            'hideOuterCol' => true,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
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
                            wire:loading.attr="disabled"
                            wire:target="saveTrfEditor"
                            x-on:click.prevent="(async () => { try { if (typeof window.flushWalkInParameterPickers === 'function') { await window.flushWalkInParameterPickers(); } } catch (error) { console.error('TRF edit save sync failed', error); } $wire.saveTrfEditor(); })()">
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
