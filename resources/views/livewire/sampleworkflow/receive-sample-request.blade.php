<div class="receive-sample-modal-body">
    <header class="receive-sample-modal-inline-header mb-3 pb-2 border-bottom">
        <h5 class="mb-1 font-weight-bold text-dark">
            @if($this->isPhysicalCheckIn)
                <i class="mdi mdi-package-variant-closed text-primary mr-1"></i>
                Physical sample check-in
            @else
                <i class="mdi mdi-walk mr-1"></i>
                Walk-in test request
            @endif
        </h5>
        <p class="text-muted small mb-0">
            @if($this->isPhysicalCheckIn)
                Verify the enquiry summary below and add optional remarks to confirm samples arrived at reception.
            @else
                Capture a new walk-in test request form and submit it to the commercial pipeline.
            @endif
        </p>
    </header>

    @if ($loadError)
        <div class="receive-sample-alert receive-sample-alert--warning" role="alert">
            <i class="mdi mdi-alert-outline"></i>
            <span>{{ $loadError }}</span>
        </div>
    @else
        <!-- Selected Request Chips at the Top -->
        @if ($selectedFormInstanceIds !== [])
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
                <header class="receive-sample-checklist-head mb-2">
                    <h6 class="receive-sample-checklist-title mb-0">Check-in summary</h6>
                </header>
                @foreach ($checkInContexts as $context)
                    <div class="card mb-2 border" wire:key="receive-context-{{ $context['instance_id'] ?? $loop->index }}">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div>
                                    <strong>{{ $context['form_number'] ?? 'Request' }}</strong>
                                    @if (!empty($context['customer_name']))
                                        <span class="text-muted"> — {{ $context['customer_name'] }}</span>
                                    @endif
                                </div>
                                @if (!empty($context['source_channel']))
                                    <span class="badge badge-light border text-uppercase">{{ str_replace('_', ' ', $context['source_channel']) }}</span>
                                @endif
                            </div>
                            @if (! ($context['can_receive'] ?? true))
                                <div class="alert alert-warning py-1 px-2 mt-2 mb-2 small">
                                    {{ $context['receive_block_reason'] ?? 'This request cannot be received yet.' }}
                                </div>
                            @endif
                            <dl class="row mb-0 small mt-2">
                                @if (!empty($context['sample_description']))
                                    <dt class="col-sm-4 mb-1">Sample description</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['sample_description'] }}</dd>
                                @endif
                                @if (!empty($context['number_of_samples']))
                                    <dt class="col-sm-4 mb-1">Number of samples</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['number_of_samples'] }}</dd>
                                @endif
                                @if (!empty($context['sampling_date']))
                                    <dt class="col-sm-4 mb-1">Sampling date</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['sampling_date'] }}</dd>
                                @endif
                                @if (!empty($context['sampling_location']))
                                    <dt class="col-sm-4 mb-1">Sampling location</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['sampling_location'] }}</dd>
                                @endif
                                @if (!empty($context['sampling_apparatus']))
                                    <dt class="col-sm-4 mb-1">Sampling apparatus</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['sampling_apparatus'] }}</dd>
                                @endif
                                @if (!empty($context['thermometer_id']))
                                    <dt class="col-sm-4 mb-1">Thermometer</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['thermometer_id'] }}</dd>
                                @endif
                                @if (!empty($context['quotation_number']))
                                    <dt class="col-sm-4 mb-1">Accepted quotation</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['quotation_number'] }}</dd>
                                @endif
                                @if (!empty($context['client_po_number']))
                                    <dt class="col-sm-4 mb-1">Client PO</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['client_po_number'] }}</dd>
                                @endif
                                @if (!empty($context['advance_payment_reference']))
                                    <dt class="col-sm-4 mb-1">Advance payment ref</dt>
                                    <dd class="col-sm-8 mb-1">{{ $context['advance_payment_reference'] }}</dd>
                                @endif
                                @if (!empty($context['enquiry_status']))
                                    <dt class="col-sm-4 mb-0">Enquiry status</dt>
                                    <dd class="col-sm-8 mb-0">{{ $context['enquiry_status'] }}</dd>
                                @endif
                            </dl>
                            <div class="mt-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    wire:click="openRejectWizard('{{ $context['instance_id'] }}')"
                                >
                                    <i class="mdi mdi-close-circle-outline mr-1"></i> Reject sample
                                </button>
                            </div>
                        </div>
                    </div>
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

        <!-- 2. Dynamic Test Request Form Fields -->
        @if($formTemplate && is_array($formTemplate->form_fields))
            @php
                $isSectioned = isset($formTemplate->form_fields['sections']) && is_array($formTemplate->form_fields['sections']);
            @endphp
            <div class="card bg-light border-0 mb-4 shadow-none rounded">
                <div class="card-body p-3">
                    <h6 class="font-weight-bold mb-3 text-primary"><i class="mdi mdi-clipboard-text mr-1"></i> Test Request Form</h6>
                    
                    @if($isSectioned)
                        @foreach($formTemplate->form_fields['sections'] as $sectionIndex => $section)
                            <div class="form-section mb-4" wire:key="section-{{ $sectionIndex }}">
                                <h6 class="form-section-title font-weight-bold text-dark border-bottom pb-2 mb-3">
                                    {{ $section['title'] ?? 'Section' }}
                                </h6>
                                <div class="row">
                                    @foreach($section['fields'] ?? [] as $field)
                                        @if(!empty($field['name']))
                                            <div class="col-md-6 mb-3">
                                                @include('livewire.sampleworkflow.test-request-field-render', ['field' => $field])
                                            </div>
                                        @endif
                                    @endforeach

                                    @if(($section['title'] ?? '') === 'STATEMENT OF CONFORMITY & SIGNATURES')
                                        @if($this->isFood)
                                            <!-- Food Samples Table -->
                                            <div class="col-12 mb-4">
                                                <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">SAMPLE DETAILS TABLE</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm">
                                                        <thead class="bg-secondary text-white text-center small">
                                                            <tr>
                                                                <th style="width: 5%">S. No.</th>
                                                                <th style="width: 15%">Sample Description</th>
                                                                <th style="width: 15%">Sampling Point/Location</th>
                                                                <th style="width: 8%">Qty.</th>
                                                                <th style="width: 12%">Sample Type</th>
                                                                <th style="width: 15%">Sample Condition</th>
                                                                <th style="width: 10%">Dates & Batch</th>
                                                                <th style="width: 10%">State</th>
                                                                <th style="width: 10%">Micro/Chem Param</th>
                                                                <th style="width: 5%">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="small">
                                                            @foreach($formData['sample_rows'] ?? [] as $rowIdx => $row)
                                                                <tr wire:key="food-row-{{ $rowIdx }}">
                                                                    <td class="text-center align-middle font-weight-bold">{{ $rowIdx + 1 }}</td>
                                                                    <td>
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sample_description" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Description">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sampling_point" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Point/Loc">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.qty" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Qty">
                                                                    </td>
                                                                    <td>
                                                                        <select wire:model="formData.sample_rows.{{ $rowIdx }}.sample_type" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;">
                                                                            <option value="">Select Type</option>
                                                                            <option value="Raw">Raw</option>
                                                                            <option value="Cooked">Cooked</option>
                                                                            <option value="Ready To Eat">Ready To Eat</option>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <select wire:model="formData.sample_rows.{{ $rowIdx }}.sample_condition" class="form-control form-control-xs mb-1" style="padding: 2px 5px; height: auto; font-size: 11px;">
                                                                            <option value="">Select Cond.</option>
                                                                            <option value="Acceptable">Acceptable</option>
                                                                            <option value="Chilled">Chilled</option>
                                                                            <option value="Frozen">Frozen</option>
                                                                            <option value="Ambient">Ambient</option>
                                                                        </select>
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sample_temp" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Temp (°C)">
                                                                    </td>
                                                                    <td>
                                                                        <label class="mb-0 text-muted" style="font-size: 9px;">Prod:</label>
                                                                        <input type="date" wire:model="formData.sample_rows.{{ $rowIdx }}.production_date" class="form-control form-control-xs p-1 mb-1" style="font-size: 10px; height: auto;">
                                                                        <label class="mb-0 text-muted" style="font-size: 9px;">Exp:</label>
                                                                        <input type="date" wire:model="formData.sample_rows.{{ $rowIdx }}.expiration_date" class="form-control form-control-xs p-1 mb-1" style="font-size: 10px; height: auto;">
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.batch_number" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Batch No.">
                                                                    </td>
                                                                    <td>
                                                                        <select wire:model="formData.sample_rows.{{ $rowIdx }}.state_of_sample" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;">
                                                                            <option value="">Select State</option>
                                                                            <option value="Liquid">L - Liquid</option>
                                                                            <option value="Semi Solid">SS - Semi Solid</option>
                                                                            <option value="Solid">S - Solid</option>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <textarea wire:model="formData.sample_rows.{{ $rowIdx }}.parameters" class="form-control form-control-xs" rows="2" style="padding: 2px 5px; font-size: 11px;" placeholder="Micro/Chem"></textarea>
                                                                    </td>
                                                                    <td class="text-center align-middle">
                                                                        <button type="button" wire:click="removeSampleRow({{ $rowIdx }})" class="btn btn-danger btn-xs p-1"><i class="mdi mdi-trash-can"></i></button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="mt-2 text-right">
                                                    <button type="button" wire:click="addSampleRow" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-plus mr-1"></i> Add Sample Row</button>
                                                </div>
                                            </div>
                                        @elseif($this->isWater)
                                            <!-- Water Samples Table -->
                                            <div class="col-12 mb-4">
                                                <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">SAMPLE DETAILS TABLE</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm">
                                                        <thead class="bg-secondary text-white text-center small">
                                                            <tr>
                                                                <th style="width: 5%">S. No.</th>
                                                                <th style="width: 15%">Sample Description</th>
                                                                <th style="width: 15%">Location</th>
                                                                <th style="width: 8%">Qty.</th>
                                                                <th style="width: 12%">Sampling Point</th>
                                                                <th style="width: 20%">Field Data (pH, Cl, Temp, Odor)</th>
                                                                <th style="width: 15%">Test Requirements</th>
                                                                <th style="width: 5%">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="small">
                                                            @foreach($formData['sample_rows'] ?? [] as $rowIdx => $row)
                                                                <tr wire:key="water-row-{{ $rowIdx }}">
                                                                    <td class="text-center align-middle font-weight-bold">{{ $rowIdx + 1 }}</td>
                                                                    <td>
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sample_description" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Description">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.location" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Location">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.qty" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Qty">
                                                                    </td>
                                                                    <td>
                                                                        <select wire:model="formData.sample_rows.{{ $rowIdx }}.sampling_point" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;">
                                                                            <option value="">Select Point</option>
                                                                            <option value="Tap">Tap</option>
                                                                            <option value="Tank">Tank</option>
                                                                            <option value="Pool">Pool</option>
                                                                            <option value="Shower Head">Shower Head</option>
                                                                            <option value="Others">Others</option>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <div class="row no-gutters">
                                                                            <div class="col-6 pr-1 mb-1">
                                                                                <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.ph" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="pH">
                                                                            </div>
                                                                            <div class="col-6 mb-1">
                                                                                <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.residual_chlorine" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Res. Cl">
                                                                            </div>
                                                                            <div class="col-6 pr-1">
                                                                                <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sample_temp" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Temp (°C)">
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.odor" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Odor">
                                                                            </div>
                                                                        </div>
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.appearance" class="form-control form-control-xs mt-1" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Appearance">
                                                                    </td>
                                                                    <td>
                                                                        <div class="custom-control custom-checkbox small mb-1">
                                                                            <input type="checkbox" id="water_micro_{{ $rowIdx }}" wire:model="formData.sample_rows.{{ $rowIdx }}.microbiology" class="custom-control-input">
                                                                            <label class="custom-control-label" for="water_micro_{{ $rowIdx }}">Microbiology</label>
                                                                        </div>
                                                                        <div class="custom-control custom-checkbox small mb-1">
                                                                            <input type="checkbox" id="water_leg_{{ $rowIdx }}" wire:model="formData.sample_rows.{{ $rowIdx }}.legionella" class="custom-control-input">
                                                                            <label class="custom-control-label" for="water_leg_{{ $rowIdx }}">Legionella</label>
                                                                        </div>
                                                                        <div class="custom-control custom-checkbox small">
                                                                            <input type="checkbox" id="water_chem_{{ $rowIdx }}" wire:model="formData.sample_rows.{{ $rowIdx }}.chemical_analysis" class="custom-control-input">
                                                                            <label class="custom-control-label" for="water_chem_{{ $rowIdx }}">Chemical Analysis</label>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-center align-middle">
                                                                        <button type="button" wire:click="removeSampleRow({{ $rowIdx }})" class="btn btn-danger btn-xs p-1"><i class="mdi mdi-trash-can"></i></button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="mt-2 text-right">
                                                    <button type="button" wire:click="addSampleRow" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-plus mr-1"></i> Add Sample Row</button>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row">
                            @foreach(($formTemplate->form_fields ?? []) as $field)
                                @if(!empty($field['name']))
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
        @endunless

        @if ($this->isPhysicalCheckIn)
            <section class="receive-sample-checkin-remarks mb-3">
                <label class="receive-checklist-field-label small font-weight-bold" for="receive-remarks">Remarks</label>
                <textarea
                    id="receive-remarks"
                    wire:model="remarks"
                    rows="3"
                    class="form-control form-control-sm receive-checklist-control"
                    placeholder="Optional notes about sample condition on arrival, packaging, etc."
                ></textarea>
            </section>
        @endif
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
            @if ($loadError || (! $this->isPhysicalCheckIn && ! $selectedSampleTypeId)) disabled @endif
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