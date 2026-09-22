@if($showTrfViewModal)
    @php
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

        $unitMatch = collect($trfEditUnitOptions)->firstWhere('id', $trfEditCompanyUnitId);
        $unitLabel = is_array($unitMatch)
            ? (string) ($unitMatch['text'] ?? '—')
            : ($trfEditCompanyUnitId !== '' ? $trfEditCompanyUnitId : '—');
        $contactLabel = $trfEditContactName !== '' ? $trfEditContactName : '—';
    @endphp
    <div class="rv-modal-backdrop rv-trf-edit-modal rv-trf-view-modal"
         wire:key="trf-view-modal"
         wire:keydown.escape.window="closeTrfViewer">
        <div class="rv-trf-edit-stage">
            @if($prevSlide)
                <button type="button"
                    class="rv-trf-carousel-nav rv-trf-carousel-nav--prev"
                    wire:click="setTrfViewSlide('{{ $prevSlide }}')"
                    aria-label="Previous section">
                    <i class="mdi mdi-chevron-left" aria-hidden="true"></i>
                </button>
            @else
                <span class="rv-trf-carousel-nav rv-trf-carousel-nav--prev is-disabled" aria-hidden="true">
                    <i class="mdi mdi-chevron-left" aria-hidden="true"></i>
                </span>
            @endif

            <div class="rv-modal rv-modal--xl rv-sample-row-edit-dialog rv-trf-edit-dialog is-ready"
                 role="dialog"
                 aria-modal="true">
                <div class="rv-modal-header">
                    <div>
                        <h4 class="rv-modal-title ls-type-title mb-1">View request details</h4>
                        <p class="rv-trf-edit-subtitle ls-type-caption mb-0">Read-only customer and sample information (including per-sample collection details).</p>
                    </div>
                    <button type="button" class="rv-modal-close" wire:click="closeTrfViewer" aria-label="Close">
                        <i class="mdi mdi-close" aria-hidden="true"></i>
                    </button>
                </div>

                <nav class="rv-trf-edit-steps" aria-label="View sections">
                    @foreach($slides as $slideKey => $slideLabel)
                        <button type="button"
                            class="rv-trf-edit-step {{ $trfEditSlide === $slideKey ? 'is-active' : '' }}"
                            wire:click="setTrfViewSlide('{{ $slideKey }}')">
                            <span class="rv-trf-edit-step-index">{{ $loop->iteration }}</span>
                            {{ $slideLabel }}
                        </button>
                        @if(! $loop->last)
                            <span class="rv-trf-edit-step-divider" aria-hidden="true"></span>
                        @endif
                    @endforeach
                </nav>

                <div class="rv-modal-body rv-trf-edit-body">
                    @if(($trfEditSlide ?? 'customer') === 'customer')
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title ls-type-label">Customer Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                        'label' => 'Client',
                                        'id' => 'trf-view-client',
                                        'name' => 'trf_view_client',
                                        'value' => $trfEditClientName !== '' ? $trfEditClientName : '—',
                                        'disabled' => true,
                                    ])
                                </div>
                                <div class="col-md-6 mb-3">
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                        'label' => 'Company unit / Site',
                                        'id' => 'trf-view-unit',
                                        'name' => 'trf_view_unit',
                                        'value' => $unitLabel,
                                        'disabled' => true,
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title ls-type-label">Contact</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                        'label' => 'Contact name',
                                        'id' => 'trf-view-contact',
                                        'name' => 'trf_view_contact',
                                        'value' => $contactLabel,
                                        'disabled' => true,
                                    ])
                                </div>
                                <div class="col-md-4 mb-3">
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                        'label' => 'Email',
                                        'id' => 'trf-view-email',
                                        'name' => 'trf_view_email',
                                        'type' => 'email',
                                        'value' => $trfEditContactEmail !== '' ? $trfEditContactEmail : '—',
                                        'disabled' => true,
                                    ])
                                </div>
                                <div class="col-md-4 mb-3">
                                    @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                        'label' => 'Phone',
                                        'id' => 'trf-view-phone',
                                        'name' => 'trf_view_phone',
                                        'value' => $trfEditContactPhone !== '' ? $trfEditContactPhone : '—',
                                        'disabled' => true,
                                    ])
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rv-trf-edit-section">
                            <h5 class="rv-trf-edit-section-title ls-type-label">
                                <i class="mdi mdi-test-tube" aria-hidden="true"></i>
                                Sample Details
                            </h5>
                            <div class="rv-trf-sample-list">
                                @forelse($trfEditSampleSummaries as $sampleSummary)
                                    @php
                                        $sampleIndex = (int) $sampleSummary['index'];
                                        $draft = $trfEditSampleDrafts[$sampleIndex] ?? [];
                                        $isExpanded = $trfEditExpandedSampleIndex === $sampleIndex;
                                        $fieldsByName = collect($trfEditSampleDefinitions)->keyBy(fn ($f) => (string) ($f['name'] ?? ''));
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
                                    <div class="rv-trf-sample-card ls-trf-sample-panel ls-trf-sample-panel--view {{ $isExpanded ? 'is-expanded' : '' }}"
                                         wire:key="trf-view-sample-{{ $sampleIndex }}"
                                         x-data="{ open: {{ $isExpanded ? 'true' : 'false' }} }"
                                         :class="{ 'is-expanded': open }">
                                        <button type="button"
                                            class="rv-trf-sample-card-toggle ls-trf-sample-panel__header"
                                            @click="open = !open; $wire.expandTrfSample({{ $sampleIndex }}, open)">
                                            <span class="ls-trf-sample-panel__title">{{ $sampleSummary['summary'] }}</span>
                                            <i class="mdi ls-soft-card__chevron" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
                                        </button>
                                        @if($isExpanded)
                                            <div class="rv-trf-sample-card-body ls-trf-sample-panel__body" x-show="open" x-cloak>
                                                <div class="row">
                                                    @if($collectionFields->isNotEmpty())
                                                        <div class="col-12 mb-2">
                                                            <div class="rft-sample-section-label rft-sample-section-label--collection">Sample collection</div>
                                                        </div>
                                                        @foreach($collectionFields as $field)
                                                            @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                                'field' => $field,
                                                                'draft' => $draft,
                                                                'sampleIndex' => $sampleIndex,
                                                                'colClass' => in_array((string) ($field['name'] ?? ''), ['sample_sampling_point_description', 'extra_sampling_equipment'], true)
                                                                    ? 'col-12'
                                                                    : 'col-md-4',
                                                            ])
                                                        @endforeach
                                                        <div class="col-12 mb-2 mt-2">
                                                            <div class="rft-sample-section-label rft-sample-section-label--sample">Sample &amp; test information</div>
                                                        </div>
                                                    @endif
                                                    @if($sampleTypeField)
                                                        @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                            'field' => $sampleTypeField,
                                                            'draft' => $draft,
                                                            'sampleIndex' => $sampleIndex,
                                                            'colClass' => 'col-md-6',
                                                        ])
                                                    @endif
                                                    @if($analysisTypeField)
                                                        @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                            'field' => $analysisTypeField,
                                                            'draft' => $draft,
                                                            'sampleIndex' => $sampleIndex,
                                                            'colClass' => 'col-md-6',
                                                        ])
                                                    @endif
                                                    @if($parametersField)
                                                        @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                            'field' => $parametersField,
                                                            'draft' => $draft,
                                                            'sampleIndex' => $sampleIndex,
                                                            'colClass' => 'col-12',
                                                        ])
                                                    @endif

                                                    @foreach($trfEditSampleDefinitions as $field)
                                                        @php $fieldName = (string) ($field['name'] ?? ''); @endphp
                                                        @if($fieldName === '' || in_array($fieldName, $skipInLoop, true))
                                                            @continue
                                                        @endif
                                                        @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                            'field' => $field,
                                                            'draft' => $draft,
                                                            'sampleIndex' => $sampleIndex,
                                                            'colClass' => $fieldName === 'sample_description' ? 'col-12' : 'col-md-6',
                                                        ])
                                                    @endforeach

                                                    @if($testCategoryField || $conditionField || $tempField)
                                                        @if($testCategoryField)
                                                            @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                                'field' => $testCategoryField,
                                                                'draft' => $draft,
                                                                'sampleIndex' => $sampleIndex,
                                                                'colClass' => 'col-md-4',
                                                            ])
                                                        @endif
                                                        @if($conditionField)
                                                            @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                                'field' => $conditionField,
                                                                'draft' => $draft,
                                                                'sampleIndex' => $sampleIndex,
                                                                'colClass' => 'col-md-4',
                                                            ])
                                                        @endif
                                                        @if($tempField)
                                                            @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                                'field' => $tempField,
                                                                'draft' => $draft,
                                                                'sampleIndex' => $sampleIndex,
                                                                'colClass' => 'col-md-4',
                                                            ])
                                                        @endif
                                                    @endif

                                                    @if($descriptionField)
                                                        @include('livewire.submission-forms.request-view.partials.trf-view-sample-field', [
                                                            'field' => $descriptionField,
                                                            'draft' => $draft,
                                                            'sampleIndex' => $sampleIndex,
                                                            'colClass' => 'col-12',
                                                        ])
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="ls-type-caption mb-0">No samples on this request.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rv-modal-footer rv-trf-edit-footer">
                    <div class="rv-trf-edit-footer-left">
                        @if($prevSlide)
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="setTrfViewSlide('{{ $prevSlide }}')">
                                <i class="mdi mdi-chevron-left"></i> {{ $slides[$prevSlide] }}
                            </button>
                        @endif
                    </div>
                    <div class="rv-trf-edit-footer-dots" aria-hidden="true">
                        @foreach($slideKeys as $key)
                            <span class="rv-trf-edit-dot {{ $trfEditSlide === $key ? 'is-active' : '' }}"></span>
                        @endforeach
                    </div>
                    <div class="rv-trf-edit-footer-right">
                        <button type="button" class="btn btn-sm btn-primary" wire:click="closeTrfViewer">Close</button>
                        @if($nextSlide)
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="setTrfViewSlide('{{ $nextSlide }}')">
                                {{ $slides[$nextSlide] }} <i class="mdi mdi-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @if($nextSlide)
                <button type="button"
                    class="rv-trf-carousel-nav rv-trf-carousel-nav--next"
                    wire:click="setTrfViewSlide('{{ $nextSlide }}')"
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
