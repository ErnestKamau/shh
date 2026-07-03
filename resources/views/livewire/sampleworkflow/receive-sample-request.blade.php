<div class="receive-sample-modal-body">
    <style>
        .receive-sample-modal-body .check-in-trf-metadata {
            margin-top: 1rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid #e2e8f0;
        }
        .receive-sample-modal-body .receive-sample-type-field {
            margin-top: 2px;
        }
        .receive-sample-modal-body .walk-in-trf-rows-table .table th,
        .receive-sample-modal-body .walk-in-trf-rows-table .table td {
            vertical-align: top;
        }
        .receive-sample-modal-body .walk-in-trf-rows-table .select2-container {
            font-size: 11px;
        }
        .receive-sample-modal-body .walk-in-trf-rows-table .select2-container--default .select2-selection--multiple {
            min-height: 28px;
            padding: 1px 2px;
        }
        .receive-sample-modal-body .walk-in-trf-rows-grid {
            table-layout: fixed;
            min-width: 1280px;
            font-size: 11px;
        }
        .receive-sample-modal-body .walk-in-trf-rows-grid th,
        .receive-sample-modal-body .walk-in-trf-rows-grid td {
            padding: 5px 6px;
            vertical-align: top;
        }
        .receive-sample-modal-body .walk-in-trf-rows-grid th {
            font-size: 10px;
            line-height: 1.25;
            white-space: normal;
            word-break: break-word;
        }
        .receive-sample-modal-body .walk-in-trf-col-sn { width: 42px; min-width: 42px; }
        .receive-sample-modal-body .walk-in-trf-col-desc { width: 40px; min-width: 40px; }
        .receive-sample-modal-body .walk-in-trf-col-location { width: 110px; min-width: 110px; }
        .receive-sample-modal-body .walk-in-trf-col-qty { width: 200px; min-width: 200px; }
        .receive-sample-modal-body .walk-in-trf-col-analysis-type { width: 145px; min-width: 145px; }
        .receive-sample-modal-body .walk-in-trf-col-parameters { width: 180px; min-width: 180px; }
        .receive-sample-modal-body .walk-in-trf-col-radio { width: 105px; min-width: 105px; }
        .receive-sample-modal-body .walk-in-trf-col-date { width: 115px; min-width: 115px; }
        .receive-sample-modal-body .walk-in-trf-col-batch { width: 90px; min-width: 90px; }
        .receive-sample-modal-body .walk-in-trf-col-field-data { width: 80px; min-width: 80px; }
        .receive-sample-modal-body .walk-in-trf-col-default { width: 90px; min-width: 90px; }
        .receive-sample-modal-body .walk-in-trf-col-actions { width: 36px; min-width: 36px; }
        .receive-sample-modal-body .walk-in-trf-desc-btn {
            font-size: 14px;
            padding: 4px 6px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
        }
        .receive-sample-modal-body .walk-in-trf-parameters-wrap .select2-container {
            width: 100% !important;
        }
        .receive-sample-modal-body .walk-in-trf-parameters-wrap .select2-container--default .select2-selection--multiple {
            min-height: 32px;
            max-height: 72px;
            overflow-y: auto;
        }
        .receive-sample-modal-body .walk-in-trf-desc-modal .modal-dialog {
            max-width: 640px;
        }
        .walk-in-trf-desc-modal {
            z-index: 1065 !important;
        }
        .walk-in-trf-desc-modal + .modal-backdrop {
            z-index: 1060 !important;
        }
        .receive-sample-modal-body .walk-in-trf-field-label-row {
            min-height: 1.25rem;
        }
        .receive-walk-in-entity-modal {
            z-index: 1070 !important;
        }
        .receive-walk-in-entity-modal + .modal-backdrop {
            z-index: 1065 !important;
        }
    </style>
    @if ($selectedFormInstanceIds !== [] && ! $this->isPhysicalCheckIn)
            <section class="receive-sample-selected mb-3">
                <p class="receive-sample-section-label font-weight-bold">Selected requests</p>
                <div class="receive-sample-chips">
                    @foreach ($selectedFormSummaries as $summary)
                        <span class="receive-sample-chip badge mr-1 mb-1 p-2">
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
                            <div class="receive-checkin-card__header-aside">
                                @if (!empty($context['enquiry_status']))
                                    <span class="receive-checkin-card__status">{{ $context['enquiry_status'] }}</span>
                                @endif
                                @if (!empty($context['source_channel']))
                                    <span class="receive-checkin-card__channel badge badge-light border text-uppercase">{{ str_replace('_', ' ', $context['source_channel']) }}</span>
                                @endif
                            </div>
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
                            @if (!empty($context['sample_description']) || !empty($context['sample_description_html']))
                                <div class="receive-checkin-stat receive-checkin-stat--full">
                                    <span class="receive-checkin-stat__label">Sample description</span>
                                    <div class="receive-checkin-stat__value receive-checkin-rich-text">
                                        {!! $context['sample_description_html'] ?? e($context['sample_description'] ?? '') !!}
                                    </div>
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
        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3 small" role="alert">
                <strong class="d-block mb-1">Please fix the following before submitting:</strong>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- Walk-in only: Sample Type + TRF -->
        <div class="form-group mb-4 receive-sample-type-field">
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

        @if($selectedSampleTypeId && $submissionForm)
            @include('livewire.partials.walk-in-trf-wizard-styles')
            <div class="walk-in-trf-wizard-shell mb-3">
                @include('livewire.partials.walk-in-trf-wizard-stepper')
                @include('livewire.partials.walk-in-trf-capture-sections', [
                    'submissionForm' => $submissionForm,
                    'formData' => $formData,
                    'walkInSections' => $walkInSections,
                    'walkInActiveStepIndex' => $walkInActiveStepIndex,
                ])
            </div>
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

    @if($showWalkInAddContactModal)
        <div class="modal fade show d-block receive-walk-in-entity-modal" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h5 class="modal-title">Add customer contact</h5>
                        <button type="button" class="close" wire:click="closeWalkInAddContactModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small font-weight-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model="walkInNewContactName" placeholder="Contact name">
                            @error('walkInNewContactName') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Email</label>
                            <input type="email" class="form-control form-control-sm" wire:model="walkInNewContactEmail" placeholder="Email">
                            @error('walkInNewContactEmail') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group mb-0 mt-2">
                            <label class="small font-weight-bold">Phone</label>
                            <input type="text" class="form-control form-control-sm" wire:model="walkInNewContactPhone" placeholder="Phone">
                            @error('walkInNewContactPhone') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeWalkInAddContactModal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="saveWalkInContact">Save contact</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show receive-walk-in-entity-modal"></div>
    @endif

    @if($showWalkInAddPointModal)
        <div class="modal fade show d-block receive-walk-in-entity-modal" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h5 class="modal-title">Add sample point</h5>
                        <button type="button" class="close" wire:click="closeWalkInAddPointModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small font-weight-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model="walkInNewPointName" placeholder="Sample point name">
                            @error('walkInNewPointName') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Client unit <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" wire:model="walkInNewPointUnitId">
                                <option value="">Select unit...</option>
                                @foreach($this->customerCompanyUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('walkInNewPointUnitId') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeWalkInAddPointModal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="saveWalkInSamplePoint">Save sample point</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show receive-walk-in-entity-modal"></div>
    @endif

    <footer class="receive-sample-modal-footer d-flex justify-content-between align-items-center border-top pt-3 flex-wrap" style="gap: 8px;">
        @if (! $this->isPhysicalCheckIn && $selectedSampleTypeId && $submissionForm && $this->walkInTotalSteps > 0)
            <span class="walk-in-trf-wizard__step-hint mb-0" aria-live="polite">
                Step {{ $walkInActiveStepIndex + 1 }} of {{ $this->walkInTotalSteps }}
                · {{ $this->walkInWizardSteps[$walkInActiveStepIndex]['title'] ?? '' }}
            </span>
        @else
            <span></span>
        @endif

        <div class="d-flex align-items-center" style="gap: 8px;">
            <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancel</button>

            @if (! $this->isPhysicalCheckIn && $selectedSampleTypeId && $submissionForm && $this->walkInTotalSteps > 0)
                @if (! $this->walkInIsFirstStep)
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        wire:click="prevWalkInStep"
                        wire:loading.attr="disabled"
                        wire:target="prevWalkInStep,nextWalkInStep,goToWalkInStep,confirmReceive"
                    >
                        <i class="mdi mdi-arrow-left mr-1" aria-hidden="true"></i> Back
                    </button>
                @endif

                @if (! $this->walkInIsLastStep)
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="nextWalkInStep"
                        wire:loading.attr="disabled"
                        wire:target="prevWalkInStep,nextWalkInStep,goToWalkInStep,confirmReceive"
                        onclick="try { if (typeof window.syncWalkInParametersBeforeSubmit === 'function') { window.syncWalkInParametersBeforeSubmit(); } if (typeof window.syncTrfSignaturesBeforeSubmit === 'function') { window.syncTrfSignaturesBeforeSubmit(); } } catch (error) { console.error('TRF step sync failed', error); }"
                    >
                        <span wire:loading.remove wire:target="nextWalkInStep">
                            Continue
                            <i class="mdi mdi-arrow-right ml-1" aria-hidden="true"></i>
                        </span>
                        <span wire:loading wire:target="nextWalkInStep">
                            <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                            Checking…
                        </span>
                    </button>
                @else
                    <button
                        type="button"
                        class="btn btn-sm btn-primary receive-sample-submit-btn"
                        wire:click="confirmReceive"
                        wire:loading.attr="disabled"
                        wire:target="prevWalkInStep,nextWalkInStep,goToWalkInStep,confirmReceive"
                        onclick="try { if (typeof window.syncWalkInParametersBeforeSubmit === 'function') { window.syncWalkInParametersBeforeSubmit(); } if (typeof window.syncTrfSignaturesBeforeSubmit === 'function') { window.syncTrfSignaturesBeforeSubmit(); } } catch (error) { console.error('TRF pre-submit sync failed', error); }"
                    >
                        <span wire:loading.remove wire:target="confirmReceive">
                            <i class="mdi mdi-package-variant-closed mr-1" aria-hidden="true"></i>
                            Submit walk-in request
                        </span>
                        <span wire:loading wire:target="confirmReceive">
                            <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                            Processing…
                        </span>
                    </button>
                @endif
            @else
                <button
                    type="button"
                    class="btn btn-sm btn-primary receive-sample-submit-btn"
                    wire:click="confirmReceive"
                    wire:loading.attr="disabled"
                    onclick="try { if (typeof window.syncWalkInParametersBeforeSubmit === 'function') { window.syncWalkInParametersBeforeSubmit(); } if (typeof window.syncTrfSignaturesBeforeSubmit === 'function') { window.syncTrfSignaturesBeforeSubmit(); } } catch (error) { console.error('TRF pre-submit sync failed', error); }"
                    @if (! $this->isPhysicalCheckIn && ! $selectedSampleTypeId) disabled @endif
                >
                    <span wire:loading.remove wire:target="confirmReceive">
                        <i class="mdi mdi-package-variant-closed mr-1" aria-hidden="true"></i>
                        {{ $this->isPhysicalCheckIn ? 'Confirm check-in' : 'Submit walk-in request' }}
                    </span>
                    <span wire:loading wire:target="confirmReceive">
                        <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                        Processing…
                    </span>
                </button>
            @endif
        </div>
    </footer>
</div>