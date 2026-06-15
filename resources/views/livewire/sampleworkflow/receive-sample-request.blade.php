<div class="receive-sample-modal-body">
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
                <p class="receive-sample-selected-hint text-muted small mt-1">Complete every required item below to receive all selected requests.</p>
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

        <!-- 1. Select Sample Type -->
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
                    <h6 class="font-weight-bold mb-3 text-primary"><i class="mdi mdi-clipboard-text mr-1"></i> {{ $formTemplate->name }}</h6>
                    
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
                                                                <th style="width: 10%">Sample No.</th>
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
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sample_no" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Sample No.">
                                                                    </td>
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
                                                                <th style="width: 10%">Sample No.</th>
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
                                                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sample_no" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Sample No.">
                                                                    </td>
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

        <!-- 3. Checklist Items (Only when receiving existing requests) -->
        @if ($approval && $selectedFormInstanceIds !== [])
            <section class="receive-sample-checklist mb-4">
                <header class="receive-sample-checklist-head d-flex justify-content-between align-items-center mb-2">

                    <div>
                        <h6 class="receive-sample-checklist-title mb-0 font-weight-bold">{{ $approval->name }}</h6>
                    </div>
                    <span class="receive-sample-legend small">
                        <span class="receive-required-dot text-danger" aria-hidden="true">•</span> Required
                    </span>
                </header>

                <ul class="receive-checklist-list list-group" role="list">
                    @foreach ($approval->checklistItems as $item)
                        <li
                            class="receive-checklist-item list-group-item border-0 px-0 py-2 @if($item->is_required) is-required @endif"
                            wire:key="receive-item-{{ $item->id }}"
                        >
                            @if ($item->type === 'checkbox')
                                <label class="receive-checklist-row d-flex align-items-start" for="receive-item-{{ $item->id }}">
                                    <input
                                        type="checkbox"
                                        id="receive-item-{{ $item->id }}"
                                        wire:model="responses.{{ $item->id }}"
                                        class="receive-checklist-input mr-2 mt-1 @error('responses.' . $item->id) is-invalid @enderror"
                                    >
                                    <span class="receive-checklist-copy small">
                                        <span class="receive-checklist-label font-weight-semibold">{{ $item->label }}</span>
                                        @if ($item->is_required)
                                            <span class="badge badge-danger ml-1">Required</span>
                                        @else
                                            <span class="badge badge-secondary ml-1">Optional</span>
                                        @endif
                                    </span>
                                </label>
                            @else
                                <div class="receive-checklist-field-block">
                                    <label class="receive-checklist-field-label small font-weight-bold" for="receive-item-{{ $item->id }}">
                                        {{ $item->label }}
                                        @if ($item->is_required)
                                            <span class="badge badge-danger ml-1">Required</span>
                                        @else
                                            <span class="badge badge-secondary ml-1">Optional</span>
                                        @endif
                                    </label>
                                    @if ($item->type === 'select')
                                        <select
                                            id="receive-item-{{ $item->id }}"
                                            wire:model="responses.{{ $item->id }}"
                                            class="form-control form-control-sm receive-checklist-control @error('responses.' . $item->id) is-invalid @enderror"
                                        >
                                            <option value="">Select option</option>
                                            @foreach (($item->options ?? []) as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input
                                            type="text"
                                            id="receive-item-{{ $item->id }}"
                                            wire:model="responses.{{ $item->id }}"
                                            class="form-control form-control-sm receive-checklist-control @error('responses.' . $item->id) is-invalid @enderror"
                                            placeholder="Enter details"
                                        >
                                    @endif
                                </div>
                            @endif
                            @error('responses.' . $item->id)
                                <p class="receive-checklist-error text-danger small font-weight-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </li>
                    @endforeach
                </ul>

                <div class="receive-remarks mt-3">
                    <label class="receive-checklist-field-label small font-weight-bold" for="receive-remarks">Remarks</label>
                    <textarea
                        id="receive-remarks"
                        wire:model="remarks"
                        rows="2"
                        class="form-control form-control-sm receive-checklist-control"
                        placeholder="Optional notes for this receiving action"
                    ></textarea>
                </div>
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
            @if ($loadError || ! $selectedSampleTypeId) disabled @endif
        >
            <span wire:loading.remove wire:target="confirmReceive">
                <i class="mdi mdi-package-variant-closed mr-1"></i> Confirm receive
            </span>
            <span wire:loading wire:target="confirmReceive">
                <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                Processing…
            </span>
        </button>
    </footer>
</div>
