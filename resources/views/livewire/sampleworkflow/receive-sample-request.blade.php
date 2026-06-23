<div class="receive-sample-modal-body">
    <style>
        .receive-sample-modal-body .check-in-trf-metadata {
            margin-top: 1rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid #e2e8f0;
        }
    </style>
    @if ($selectedFormInstanceIds !== [] && ! $this->isPhysicalCheckIn)
            <section class="receive-sample-selected mb-3">
                <p class="receive-sample-section-label font-weight-bold">Selected requests</p>
                <div class="receive-sample-chips">
                    @foreach ($selectedFormSummaries as $summary)
                        <span class="receive-sample-chip badge badge-info mr-1 mb-1 p-2">
                            <span class="receive-sample-chip-code font-weight-bold">{{ $summary['label'] ?? 'Request' }}</span>
                            @if (!empty($summary['customer']))
                                <span class="receive-sample-chip-meta small">({{ $summary['customer'] }})</span>
                            @endif
                        </span>
                    @endforeach
                    @if ($selectedFormSummaries === [] && $selectedFormInstanceIds !== [])
                        <span class="receive-sample-chip receive-sample-chip--muted">{{ count($selectedFormInstanceIds) }} request(s)</span>
                    @endif
                </div>
                <p class="receive-sample-selected-hint text-muted small mt-1">Verify the enquiry summary below, then confirm check-in.</p>
            </section>
        @endif

        @if ($checkInContexts !== [])
            <section class="receive-sample-checkin mb-3">
                @foreach ($checkInContexts as $context)
                    <article class="receive-checkin-card" wire:key="receive-context-{{ $context['instance_id'] ?? $loop->index }}">
                        <header class="receive-checkin-card__header">
                            <div class="receive-checkin-card__identity">
                                <span class="receive-checkin-card__ref">{{ $context['form_number'] ?? 'Request' }}</span>
                                @if (!empty($context['customer_name']))
                                    <span class="receive-checkin-card__customer">{{ $context['customer_name'] }}</span>
                                @endif
                            </div>
                            @if (!empty($context['source_channel']))
                                <span class="receive-checkin-card__channel badge badge-light border text-uppercase">{{ str_replace('_', ' ', $context['source_channel']) }}</span>
                            @endif
                        </header>

                        @if (! ($context['can_receive'] ?? true))
                            <div class="alert alert-warning py-2 px-3 mb-0 mx-3 mt-2 small">
                                {{ $context['receive_block_reason'] ?? 'This request cannot be received yet.' }}
                            </div>
                        @endif

                        <div class="receive-checkin-card__grid">
                            @if (!empty($context['number_of_samples']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Samples</span>
                                    <span class="receive-checkin-stat__value">{{ $context['number_of_samples'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['sampling_date']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Sampling date</span>
                                    <span class="receive-checkin-stat__value">{{ $context['sampling_date'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['sampling_location']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Sampling location</span>
                                    <span class="receive-checkin-stat__value">{{ $context['sampling_location'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['quotation_number']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Accepted quotation</span>
                                    <span class="receive-checkin-stat__value">{{ $context['quotation_number'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['client_po_number']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Client PO</span>
                                    <span class="receive-checkin-stat__value">{{ $context['client_po_number'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['advance_payment_reference']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Advance payment</span>
                                    <span class="receive-checkin-stat__value">{{ $context['advance_payment_reference'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['enquiry_status']))
                                <div class="receive-checkin-stat receive-checkin-stat--wide">
                                    <span class="receive-checkin-stat__label">Enquiry status</span>
                                    <span class="receive-checkin-stat__value receive-checkin-stat__value--status">{{ $context['enquiry_status'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['sample_description']))
                                <div class="receive-checkin-stat receive-checkin-stat--full">
                                    <span class="receive-checkin-stat__label">Sample description</span>
                                    <span class="receive-checkin-stat__value">{{ $context['sample_description'] }}</span>
                                </div>
                            @endif
                        </div>

                        @if (!empty($context['instance_id']))
                            @include('livewire.partials.check-in-trf-metadata-fields', ['instanceId' => $context['instance_id']])
                        @endif

                        <footer class="receive-checkin-card__footer">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                wire:click="openRejectWizard('{{ $context['instance_id'] }}')"
                            >
                                <i class="mdi mdi-close-circle-outline mr-1"></i> Reject sample
                            </button>
                        </footer>
                    </article>
                @endforeach
            </section>
        @endif

        @unless($this->isPhysicalCheckIn)
        <!-- Walk-in only: Sample Type + TRF -->
        <div class="form-group mb-4">
            <label for="selectedSampleTypeId" class="font-weight-bold text-dark">Sample Type <span class="text-danger">*</span></label>
            <select id="selectedSampleTypeId" wire:model.live="selectedSampleTypeId" class="form-control form-control-sm @error('selectedSampleTypeId') is-invalid @enderror">
                <option value="">-- Select Sample Type --</option>
                @foreach($sampleTypes as $st)
                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                @endforeach
            </select>
            @error('selectedSampleTypeId')
                <div class="invalid-feedback d-block font-weight-semibold">{{ $message }}</div>
            @enderror
        </div>

        @if($selectedSampleTypeId && $formTemplate)
        <!-- Dynamic Test Request Form Fields -->
        @if($formTemplate && is_array($formTemplate->form_fields))
            @php
                $isSectioned = isset($formTemplate->form_fields['sections']) && is_array($formTemplate->form_fields['sections']);
            @endphp
            <div class="card bg-light border-0 mb-4 shadow-none rounded">
                <div class="card-body p-3">
                    @if($isSectioned)
                        @foreach($formTemplate->form_fields['sections'] as $sectionIndex => $section)
                            @php
                                $isCollapsible = ($section['collapsible'] ?? true) === true;
                                $sectionTitle = strtoupper(trim((string) ($section['title'] ?? '')));
                                $isCustomerSection = $sectionTitle === 'CUSTOMER DETAILS';
                            @endphp
                            <div
                                class="form-section mb-3"
                                wire:key="section-{{ $sectionIndex }}"
                                @if($isCollapsible) x-data="{ open: false }" @endif
                            >
                                @if($isCollapsible)
                                    <button
                                        type="button"
                                        class="form-section-title font-weight-bold text-dark border-bottom pb-2 mb-0 w-100 text-left bg-transparent border-0 d-flex align-items-center justify-content-between"
                                        @click="open = !open; $nextTick(() => { if (typeof window.initTrfSignaturePads === 'function') window.initTrfSignaturePads(true); })"
                                    >
                                        <span>{{ $section['title'] ?? 'Section' }}</span>
                                        <i class="mdi" :class="open ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                                    </button>
                                    <div class="row pt-3" x-show="open" x-collapse>
                                @else
                                    <h6 class="form-section-title font-weight-bold text-dark border-bottom pb-2 mb-3">
                                        {{ $section['title'] ?? 'Section' }}
                                    </h6>
                                    <div class="row">
                                @endif
                                    @foreach($section['fields'] ?? [] as $field)
                                        @if(!empty($field['name']) && ($field['name'] ?? '') !== 'job_number')
                                            <div class="col-md-6 mb-3">
                                                @include('livewire.sampleworkflow.test-request-field-render', ['field' => $field])
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                @if($isCustomerSection && ($this->isFood || $this->isWater))
                                    @include('livewire.partials.trf-sample-details-section')
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="row">
                            @foreach(($formTemplate->form_fields ?? []) as $field)
                                @if(!empty($field['name']) && ($field['name'] ?? '') !== 'job_number')
                                    <div class="col-md-6 mb-3">
                                        @include('livewire.sampleworkflow.test-request-field-render', ['field' => $field])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
        @endif
        @endunless

        @if ($this->isPhysicalCheckIn)
            <section class="receive-sample-checkin-remarks mb-3">
                <label class="receive-checklist-field-label small font-weight-bold" for="receive-remarks">Reception notes</label>
                <textarea
                    id="receive-remarks"
                    wire:model="remarks"
                    rows="3"
                    class="form-control form-control-sm receive-checklist-control"
                    placeholder="Optional notes about sample condition on arrival, packaging, etc. (separate from TRF remarks above)"
                ></textarea>
            </section>
        @endif

    @error('selection')
        <div class="receive-sample-alert receive-sample-alert--warning alert alert-warning mt-3 mb-0">{{ $message }}</div>
    @enderror

    <footer class="receive-sample-modal-footer d-flex justify-content-end border-top pt-3" style="gap: 8px;">
        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancel</button>
        <button
            type="button"
            class="btn btn-sm btn-primary receive-sample-submit-btn"
            wire:click="confirmReceive"
            wire:loading.attr="disabled"
            @if (! $this->isPhysicalCheckIn && ! $selectedSampleTypeId) disabled @endif
        >
            <span wire:loading.remove wire:target="confirmReceive">
                <i class="mdi mdi-package-variant-closed mr-1"></i>
                {{ $this->isPhysicalCheckIn ? 'Confirm check-in' : 'Submit walk-in request' }}
            </span>
            <span wire:loading wire:target="confirmReceive">
                <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                Processing…
            </span>
        </button>
    </footer>
</div>