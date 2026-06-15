<div class="receive-sample-modal-body">
    @if ($loadError)
        <div class="receive-sample-alert receive-sample-alert--warning" role="alert">
            <i class="mdi mdi-alert-outline"></i>
            <span>{{ $loadError }}</span>
        </div>
    @elseif ($selectedFormInstanceIds === [])
        <div class="receive-sample-alert receive-sample-alert--empty">
            No requests selected.
        </div>
    @else
        <section class="receive-sample-selected">
            <p class="receive-sample-section-label">Selected requests</p>
            <div class="receive-sample-chips">
                @foreach ($selectedFormSummaries as $summary)
                    <span class="receive-sample-chip">
                        <span class="receive-sample-chip-code">{{ $summary['label'] ?? 'Request' }}</span>
                        @if (!empty($summary['customer']))
                            <span class="receive-sample-chip-meta">{{ $summary['customer'] }}</span>
                        @endif
                    </span>
                @endforeach
                @if ($selectedFormSummaries === [] && $selectedFormInstanceIds !== [])
                    <span class="receive-sample-chip receive-sample-chip--muted">{{ count($selectedFormInstanceIds) }} request(s)</span>
                @endif
            </div>
            <p class="receive-sample-selected-hint">Verify the enquiry summary below, then complete every required checklist item to receive.</p>
        </section>

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

        @if ($approval)
            <section class="receive-sample-checklist">
                <header class="receive-sample-checklist-head">
                    <div>
                        <h6 class="receive-sample-checklist-title mb-0">{{ $approval->name }}</h6>
                    </div>
                    <span class="receive-sample-legend">
                        <span class="receive-required-dot" aria-hidden="true"></span> Required
                    </span>
                </header>

                <ul class="receive-checklist-list" role="list">
                    @foreach ($approval->checklistItems as $item)
                        <li
                            class="receive-checklist-item @if($item->is_required) is-required @endif"
                            wire:key="receive-item-{{ $item->id }}"
                        >
                            @if ($item->type === 'checkbox')
                                <label class="receive-checklist-row" for="receive-item-{{ $item->id }}">
                                    <input
                                        type="checkbox"
                                        id="receive-item-{{ $item->id }}"
                                        wire:model="responses.{{ $item->id }}"
                                        class="receive-checklist-input @error('responses.' . $item->id) is-invalid @enderror"
                                    >
                                    <span class="receive-checklist-copy">
                                        <span class="receive-checklist-label">{{ $item->label }}</span>
                                        @if ($item->is_required)
                                            <span class="receive-required-badge">Required</span>
                                        @else
                                            <span class="receive-optional-badge">Optional</span>
                                        @endif
                                    </span>
                                </label>
                            @else
                                <div class="receive-checklist-field-block">
                                    <label class="receive-checklist-field-label" for="receive-item-{{ $item->id }}">
                                        {{ $item->label }}
                                        @if ($item->is_required)
                                            <span class="receive-required-badge">Required</span>
                                        @else
                                            <span class="receive-optional-badge">Optional</span>
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
                                <p class="receive-checklist-error">{{ $message }}</p>
                            @enderror
                        </li>
                    @endforeach
                </ul>

                <div class="receive-remarks">
                    <label class="receive-checklist-field-label" for="receive-remarks">Remarks</label>
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
        <div class="receive-sample-alert receive-sample-alert--warning mt-3 mb-0">{{ $message }}</div>
    @enderror

    <footer class="receive-sample-modal-footer">
        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancel</button>
        <button
            type="button"
            class="btn btn-sm btn-outline-primary receive-sample-submit-btn"
            wire:click="confirmReceive"
            wire:loading.attr="disabled"
            @if ($loadError || $selectedFormInstanceIds === [] || ! $approval) disabled @endif
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
