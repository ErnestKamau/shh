@if($showSampleRowEditModal)
    @php
        $isWaterTrf = $this->isWaterTrf();
        $fieldsByName = collect($editingRowFieldDefinitions)->keyBy(fn (array $field): string => (string) ($field['name'] ?? ''));
        $waterTypeRowRendered = false;
        $skipFieldNames = ['sample_quantity_unit'];
        if ($isWaterTrf) {
            $skipFieldNames = array_merge($skipFieldNames, ['sample_type_id', 'analysis_type_id', 'test_requirements']);
        }
    @endphp
    <div class="rv-modal-backdrop rv-sample-row-edit-modal" wire:keydown.escape.window="closeSampleRowEditor">
        <div class="rv-modal rv-modal--wide rv-sample-row-edit-dialog" wire:ignore.self role="dialog" aria-modal="true" aria-busy="true">
            <div class="rv-sample-row-edit-loading" aria-hidden="true">
                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                <span class="small text-muted ml-2">Loading form…</span>
            </div>

            <div class="rv-modal-header">
                <h4 class="rv-modal-title">Edit sample information</h4>
                <button type="button" class="rv-modal-close" wire:click="closeSampleRowEditor" aria-label="Close">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            </div>
            <div class="rv-modal-body">
                <div class="row">
                    @foreach($editingRowFieldDefinitions as $field)
                        @php
                            $fieldName = (string) ($field['name'] ?? '');
                        @endphp

                        @if($fieldName === '' || in_array($fieldName, $skipFieldNames, true))
                            @continue
                        @endif

                        @if($fieldName === 'sampling_point_manual')
                            <div class="col-12 mb-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label small font-weight-bold text-secondary mb-1">Sampling location</label>
                                        <div class="form-control form-control-sm bg-light">{{ trim($editingRowCollectionSamplingLocation ?? '') !== '' ? $editingRowCollectionSamplingLocation : '—' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                            'field' => $field,
                                            'hideOuterCol' => true,
                                        ])
                                    </div>
                                </div>
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

                        @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                            'field' => $field,
                        ])

                        @if($isWaterTrf && ! $waterTypeRowRendered && $fieldName === 'field_sample_temp')
                            @php
                                $waterTypeRowRendered = true;
                                $waterTypeFields = collect(['sample_type_id', 'analysis_type_id', 'test_requirements'])
                                    ->map(fn (string $name): ?array => $fieldsByName->get($name))
                                    ->filter()
                                    ->values()
                                    ->all();
                            @endphp
                            @if($waterTypeFields !== [])
                                <div class="col-12 mb-1">
                                    <div class="row">
                                        @foreach($waterTypeFields as $waterField)
                                            <div class="col-md-4">
                                                @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                    'field' => $waterField,
                                                    'colClass' => 'col-12',
                                                ])
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endforeach

                    @if($isWaterTrf && ! $waterTypeRowRendered)
                        @php
                            $waterTypeFields = collect(['sample_type_id', 'analysis_type_id', 'test_requirements'])
                                ->map(function (string $name) use ($fieldsByName): ?array {
                                    $field = $fieldsByName->get($name);

                                    if ($field !== null) {
                                        return $field;
                                    }

                                    if ($name !== 'test_requirements') {
                                        return null;
                                    }

                                    return [
                                        'name' => 'test_requirements',
                                        'label' => 'Test requirement',
                                        'element_type' => 'checkbox',
                                        'required' => false,
                                        'options' => $this->waterTestRequirementOptions(),
                                    ];
                                })
                                ->filter()
                                ->values()
                                ->all();
                        @endphp
                        @if($waterTypeFields !== [])
                            <div class="col-12 mb-1">
                                <div class="row">
                                    @foreach($waterTypeFields as $waterField)
                                        <div class="col-md-4">
                                            @include('livewire.submission-forms.request-view.partials.sample-row-edit-field', [
                                                'field' => $waterField,
                                                'colClass' => 'col-12',
                                            ])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            <div class="rv-modal-footer d-flex justify-content-end" style="gap: 8px; padding: 0.75rem 1rem; border-top: 1px solid rgba(30,41,59,0.12);">
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeSampleRowEditor">Cancel</button>
                <button type="button"
                    class="btn btn-sm btn-primary"
                    data-sample-row-save
                    wire:click="saveSampleRow"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveSampleRow">Save row</span>
                    <span wire:loading wire:target="saveSampleRow">Saving…</span>
                </button>
            </div>
        </div>
    </div>
@endif
