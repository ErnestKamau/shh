<div>
    @if($show)
        <div class="modal fade show d-block ceq-wizard-overlay" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55); z-index: 1055;">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content ceq-wizard-modal">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">
                                <i class="mdi mdi-flask-outline text-primary"></i>
                                Create request from {{ $quoteNumber !== '' ? $quoteNumber : 'quotation' }}
                            </h5>
                            <small class="text-muted">
                                {{ $customerName !== '' ? $customerName : 'Customer' }}
                                @if(count($trfGroups) === 1)
                                    · TRF: {{ $trfGroups[0]['form_name'] ?? 'Test Request Form' }}
                                @elseif(count($trfGroups) > 1)
                                    · {{ count($trfGroups) }} Test Request Forms (one per sample type)
                                @endif
                            </small>
                        </div>
                        <button type="button" class="close" wire:click="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        @if($errorMessage !== '')
                            <div class="alert alert-danger">{{ $errorMessage }}</div>
                        @endif

                        @if($quotationId)
                            <div class="d-flex flex-wrap mb-3 ceq-wizard-steps" style="gap: 8px;">
                                @foreach($this->stepBadges as $badge)
                                    <span class="badge {{ $badge['active'] ? 'badge-primary' : 'badge-light' }} p-2">
                                        {{ $badge['label'] }}
                                    </span>
                                @endforeach
                            </div>

                            @if($phase === 'review')
                                <div class="alert alert-info">
                                    Review the quotation and confirm origin.
                                    @if($isMultiSampleType)
                                        This quote has multiple sample types — you will fill a TRF for each type next.
                                    @else
                                        Customer details will be filled automatically on the Test Request Form.
                                    @endif
                                </div>

                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Parameter / line</th>
                                                <th class="text-right">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($quoteLines as $line)
                                                <tr>
                                                    <td>{{ $line['parameter_label'] }}</td>
                                                    <td class="text-right">{{ $line['quantity'] }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="text-muted">No mapped lines</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Origin <span class="text-danger">*</span></label>
                                            <select wire:model.live="sourceChannel" class="form-control @error('sourceChannel') is-invalid @enderror">
                                                <option value="walk_in">Walk-in</option>
                                                <option value="portal">Portal</option>
                                                <option value="scheduled">Scheduled</option>
                                            </select>
                                            @error('sourceChannel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    @unless($isMultiSampleType)
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Physical samples <span class="text-danger">*</span></label>
                                                <input type="number" min="1" max="10000" wire:model="numberOfSamples"
                                                       class="form-control @error('numberOfSamples') is-invalid @enderror">
                                                @error('numberOfSamples') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    @endunless
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Customer reference</label>
                                            <input type="text" wire:model="referenceNumber" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Sample description</label>
                                    <input type="text" wire:model="sampleDescription" class="form-control">
                                </div>
                                <div class="form-group mb-0">
                                    <label>Internal notes</label>
                                    <textarea rows="2" wire:model="enquiryNotes" class="form-control"></textarea>
                                </div>
                            @endif

                            @if($phase === 'sections' && $this->currentTrfGroup)
                                @php $group = $this->currentTrfGroup; @endphp
                                <div class="alert alert-info">
                                    Choose which sections to fill for
                                    <strong>{{ $group['sample_type_name'] }}</strong>
                                    ({{ $group['form_name'] }}).
                                    Customer details are skipped (already known from the quote).
                                </div>

                                @forelse($group['sections'] as $section)
                                    <div class="ceq-wizard-section-option custom-control custom-checkbox mb-2 border rounded">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="trf-section-{{ $group['sample_type_id'] }}-{{ $section['id'] }}"
                                               value="{{ $section['id'] }}"
                                               wire:model="selectedSectionIdsByType.{{ $group['sample_type_id'] }}">
                                        <label class="custom-control-label" for="trf-section-{{ $group['sample_type_id'] }}-{{ $section['id'] }}">
                                            <strong>{{ $section['title'] }}</strong>
                                            @if(($section['section_type'] ?? '') === 'rows_section')
                                                <span class="badge badge-secondary ml-1">Sample rows</span>
                                            @endif
                                            @if(!empty($section['description']))
                                                <div class="text-muted small">{{ $section['description'] }}</div>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <div class="alert alert-warning mb-0">
                                        No fillable sections for this TRF. You can continue — the form will still be generated with quote tests.
                                    </div>
                                @endforelse
                            @endif

                            @if($phase === 'fill' && $this->currentTrfGroup)
                                @php
                                    $group = $this->currentTrfGroup;
                                    $typeId = $group['sample_type_id'];
                                @endphp
                                <h6 class="mb-3">
                                    Fill details — {{ $group['sample_type_name'] }}
                                    <small class="text-muted">({{ $group['form_name'] }})</small>
                                </h6>

                                @forelse($this->currentSelectedSections as $section)
                                    <div class="card mb-3">
                                        <div class="card-header py-2"><strong>{{ $section['title'] }}</strong></div>
                                        <div class="card-body">
                                            @if(($section['section_type'] ?? '') === 'rows_section')
                                                <p class="text-muted small">
                                                    Tests and parameters come from the quotation. Fill only supplemental fields below.
                                                </p>
                                            @endif
                                            @forelse($section['fields'] as $field)
                                                <div class="form-group">
                                                    <label>
                                                        {{ $field['label'] }}
                                                        @if($field['is_required']) <span class="text-danger">*</span> @endif
                                                    </label>
                                                    @php $type = $field['element_type'] ?? 'text'; @endphp
                                                    @if(in_array($type, ['select', 'radio'], true) && !empty($field['options']))
                                                        <select wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}"
                                                                class="form-control">
                                                            <option value="">Select...</option>
                                                            @foreach($field['options'] as $option)
                                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($type === 'checkbox' && !empty($field['options']))
                                                        <div class="d-flex flex-column">
                                                            @foreach($field['options'] as $option)
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox"
                                                                           class="custom-control-input"
                                                                           id="field-{{ $typeId }}-{{ $field['name'] }}-{{ $option['value'] }}"
                                                                           value="{{ $option['value'] }}"
                                                                           wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}">
                                                                    <label class="custom-control-label"
                                                                           for="field-{{ $typeId }}-{{ $field['name'] }}-{{ $option['value'] }}">
                                                                        {{ $option['label'] }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($type === 'checkbox')
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox"
                                                                   class="custom-control-input"
                                                                   id="field-{{ $typeId }}-{{ $field['name'] }}"
                                                                   wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}">
                                                            <label class="custom-control-label" for="field-{{ $typeId }}-{{ $field['name'] }}">Yes</label>
                                                        </div>
                                                    @elseif($type === 'textarea')
                                                        <textarea rows="2" wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}" class="form-control"></textarea>
                                                    @elseif($type === 'date')
                                                        <input type="date" wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}" class="form-control">
                                                    @elseif($type === 'number')
                                                        <input type="number" wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}" class="form-control">
                                                    @else
                                                        <input type="text" wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}" class="form-control">
                                                    @endif
                                                    @error('sectionFieldValuesByType.'.$typeId.'.'.$field['name'])
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @empty
                                                <p class="text-muted mb-0 small">No wizard-editable fields in this section.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-secondary mb-0">
                                        No sections selected for this sample type. Continue to the next step.
                                    </div>
                                @endforelse
                            @endif

                            @if($phase === 'send')
                                <div class="alert alert-warning">
                                    Finishing will create the enquiry, generate
                                    {{ count($trfGroups) > 1 ? count($trfGroups).' Test Request Forms' : 'the Test Request Form' }},
                                    send the quotation, and move the request to <strong>Quotation Sent</strong>.
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="send-email" wire:model="sendEmail">
                                        <label class="custom-control-label" for="send-email">Send by email</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="send-portal" wire:model="sendPortal">
                                        <label class="custom-control-label" for="send-portal">Send to customer portal</label>
                                    </div>
                                </div>
                                <ul class="mb-0 pl-3 text-muted small">
                                    <li>Origin: {{ $sourceChannel }}</li>
                                    <li>TRF(s): {{ count($trfGroups) }}</li>
                                    @foreach($trfGroups as $group)
                                        <li>{{ $group['sample_type_name'] }} → {{ $group['form_name'] }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                    </div>

                    <div class="modal-footer" style="gap: 8px;">
                        <button type="button" class="btn btn-secondary" wire:click="close">Cancel</button>
                        @if($quotationId)
                            @if($phase !== 'review')
                                <button type="button" class="btn btn-outline-secondary" wire:click="previousStep">Back</button>
                            @endif
                            @if($phase !== 'send')
                                <button type="button" class="btn btn-primary" wire:click="nextStep" wire:loading.attr="disabled">
                                    Next
                                </button>
                            @else
                                <button type="button" class="btn btn-success" wire:click="finish" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="finish">
                                        <i class="mdi mdi-send"></i> Create, generate TRF &amp; send
                                    </span>
                                    <span wire:loading wire:target="finish">
                                        <span class="spinner-border spinner-border-sm"></span> Working...
                                    </span>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <style>
            .ceq-wizard-modal .modal-header,
            .ceq-wizard-modal .modal-body,
            .ceq-wizard-modal .modal-footer {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .ceq-wizard-modal .modal-body {
                padding-top: 1.25rem;
                padding-bottom: 1.25rem;
            }

            .ceq-wizard-modal .ceq-wizard-section-option {
                padding: 0.85rem 1rem 0.85rem 2.25rem;
                margin-left: 0;
                margin-right: 0;
            }

            .ceq-wizard-modal .ceq-wizard-section-option .custom-control-label {
                width: 100%;
            }

            .ceq-wizard-modal .alert {
                margin-left: 0;
                margin-right: 0;
            }

            @media (min-width: 768px) {
                .ceq-wizard-modal .modal-header,
                .ceq-wizard-modal .modal-body,
                .ceq-wizard-modal .modal-footer {
                    padding-left: 1.75rem;
                    padding-right: 1.75rem;
                }
            }
        </style>
    @endif
</div>
