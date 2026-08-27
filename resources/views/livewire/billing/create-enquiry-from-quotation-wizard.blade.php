<div>
    @if($show)
        <div class="modal fade show d-block ceq-wizard-overlay" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable ceq-wizard-dialog">
                <div class="modal-content ceq-wizard-modal ls-ui-kit" data-ls-type="plex">
                    <div class="ceq-wizard-header">
                        <div class="ceq-wizard-header__main">
                            <div class="ceq-wizard-header__eyebrow">
                                <i class="mdi mdi-flask-outline"></i>
                                Create enquiry from quotation
                            </div>
                            <h2 class="ceq-wizard-header__title">
                                {{ $quoteNumber !== '' ? $quoteNumber : 'Quotation' }}
                            </h2>
                            <div class="ceq-wizard-header__meta">
                                <span class="ceq-chip">
                                    <i class="mdi mdi-domain"></i>
                                    {{ $customerName !== '' ? $customerName : 'Customer' }}
                                </span>
                                <span class="ceq-chip ceq-chip--muted">
                                    <i class="mdi mdi-counter"></i>
                                    {{ $numberOfSamples }} physical sample{{ $numberOfSamples === 1 ? '' : 's' }}
                                </span>
                                @if(count($trfGroups) === 1)
                                    <span class="ceq-chip ceq-chip--muted">
                                        <i class="mdi mdi-file-document-outline"></i>
                                        {{ $trfGroups[0]['form_name'] ?? 'Test Request Form' }}
                                    </span>
                                @elseif(count($trfGroups) > 1)
                                    <span class="ceq-chip ceq-chip--muted">
                                        <i class="mdi mdi-file-multiple-outline"></i>
                                        {{ count($trfGroups) }} TRFs
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button type="button"
                                class="ceq-wizard-close"
                                wire:click="close"
                                x-on:click="window.dispatchEvent(new CustomEvent('lw-destroy-tinymce'))"
                                aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>

                    @if($quotationId)
                        <nav class="ceq-stepper" aria-label="Wizard steps">
                            @foreach($this->stepBadges as $index => $badge)
                                <div class="ceq-stepper__item {{ $badge['active'] ? 'is-active' : '' }} {{ $badge['done'] ? 'is-done' : '' }}">
                                    <div class="ceq-stepper__node" aria-current="{{ $badge['active'] ? 'step' : 'false' }}">
                                        @if($badge['done'])
                                            <i class="mdi mdi-check"></i>
                                        @else
                                            <i class="mdi {{ $badge['icon'] }}"></i>
                                        @endif
                                    </div>
                                    <div class="ceq-stepper__copy">
                                        <span class="ceq-stepper__label">{{ $badge['label'] }}</span>
                                        <span class="ceq-stepper__hint">{{ $badge['hint'] }}</span>
                                    </div>
                                </div>
                                @if(! $loop->last)
                                    <div class="ceq-stepper__rail {{ $badge['done'] ? 'is-done' : '' }}" aria-hidden="true"></div>
                                @endif
                            @endforeach
                        </nav>
                    @endif

                    <div class="ceq-wizard-body">
                        @if($errorMessage !== '')
                            <div class="ceq-alert ceq-alert--danger" role="alert">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                <div>{{ $errorMessage }}</div>
                            </div>
                        @endif

                        @if($quotationId)
                            <div class="ceq-step-pane" wire:key="ceq-phase-{{ $phase }}-{{ $trfIndex }}">
                                @if($phase === 'review')
                                    <div class="ceq-step-intro">
                                        <h3 class="ceq-step-intro__title">Confirm what comes from the quotation</h3>
                                        <p class="ceq-step-intro__text">
                                            Tests, sample types, and sample counts are locked from this quote.
                                            @if($isMultiSampleType)
                                                You will fill TRF details for each sample type next.
                                            @else
                                                Customer details will be applied automatically on the Test Request Form.
                                            @endif
                                        </p>
                                    </div>

                                    <div class="ceq-stat-row">
                                        <div class="ceq-stat">
                                            <span class="ceq-stat__label">Physical samples</span>
                                            <strong class="ceq-stat__value">{{ $numberOfSamples }}</strong>
                                            <span class="ceq-stat__hint">From quotation quantities</span>
                                        </div>
                                        <div class="ceq-stat">
                                            <span class="ceq-stat__label">Quote lines</span>
                                            <strong class="ceq-stat__value">{{ count($quoteLines) }}</strong>
                                            <span class="ceq-stat__hint">Parameters / packages</span>
                                        </div>
                                        <div class="ceq-stat">
                                            <span class="ceq-stat__label">TRF forms</span>
                                            <strong class="ceq-stat__value">{{ max(1, count($trfGroups)) }}</strong>
                                            <span class="ceq-stat__hint">{{ $isMultiSampleType ? 'One per sample type' : 'Single form' }}</span>
                                        </div>
                                    </div>

                                    <div class="ceq-panel">
                                        <div class="ceq-panel__head">
                                            <h4 class="ceq-panel__title"><i class="mdi mdi-playlist-check"></i> Quotation lines</h4>
                                        </div>
                                        <div class="ceq-panel__body ceq-panel__body--flush">
                                            <div class="table-responsive">
                                                <table class="ceq-table mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Parameter / line</th>
                                                            <th class="text-right">Samples</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($quoteLines as $line)
                                                            <tr>
                                                                <td>{{ $line['parameter_label'] }}</td>
                                                                <td class="text-right">
                                                                    <span class="ceq-qty-pill">{{ $line['quantity'] }}</span>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="text-muted">No mapped lines</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ceq-panel">
                                        <div class="ceq-panel__head">
                                            <h4 class="ceq-panel__title"><i class="mdi mdi-tune-variant"></i> Request setup</h4>
                                        </div>
                                        <div class="ceq-panel__body">
                                            <div class="ceq-field">
                                                <label class="ceq-field__label">Origin <span class="text-danger">*</span></label>
                                                <div class="ceq-choice-grid">
                                                    @foreach([
                                                        'walk_in' => ['Walk-in', 'mdi-walk', 'Customer present at the lab'],
                                                        'portal' => ['Portal', 'mdi-web', 'Customer uses the portal'],
                                                        'scheduled' => ['Scheduled', 'mdi-calendar-clock', 'Planned collection / visit'],
                                                    ] as $channel => $meta)
                                                        <label class="ceq-choice {{ $sourceChannel === $channel ? 'is-selected' : '' }}">
                                                            <input type="radio" class="ceq-choice__input" wire:model.live="sourceChannel" value="{{ $channel }}">
                                                            <span class="ceq-choice__icon"><i class="mdi {{ $meta[1] }}"></i></span>
                                                            <span class="ceq-choice__title">{{ $meta[0] }}</span>
                                                            <span class="ceq-choice__hint">{{ $meta[2] }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                @error('sourceChannel') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="ceq-field">
                                                        <label class="ceq-field__label" for="ceq-reference">Customer reference</label>
                                                        <input id="ceq-reference" type="text" wire:model="referenceNumber" class="form-control ceq-input" placeholder="Optional PO / reference">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="ceq-field">
                                                        <label class="ceq-field__label">Physical samples</label>
                                                        <div class="ceq-locked-value">
                                                            <strong>{{ $numberOfSamples }}</strong>
                                                            <span>Locked from quotation — edit the quote to change</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="ceq-field">
                                                <label class="ceq-field__label" for="ceq-sample-description">Sample description</label>
                                                <input id="ceq-sample-description" type="text" wire:model="sampleDescription" class="form-control ceq-input" placeholder="Optional short description">
                                            </div>
                                            <div class="ceq-field mb-0">
                                                <label class="ceq-field__label" for="ceq-enquiry-notes">Internal notes</label>
                                                <textarea id="ceq-enquiry-notes" rows="2" wire:model="enquiryNotes" class="form-control ceq-input" placeholder="Visible to lab staff only"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($phase === 'sections' && $this->currentTrfGroup)
                                    @php $group = $this->currentTrfGroup; @endphp
                                    <div class="ceq-step-intro">
                                        <h3 class="ceq-step-intro__title">Choose sections for {{ $group['sample_type_name'] }}</h3>
                                        <p class="ceq-step-intro__text">
                                            Select which TRF sections to complete now for
                                            <strong>{{ $group['form_name'] }}</strong>.
                                            You can finish remaining fields on the request view after create.
                                        </p>
                                    </div>

                                    <div class="ceq-section-list">
                                        @forelse($group['sections'] as $section)
                                            @php
                                                $sectionId = (string) $section['id'];
                                                $checked = in_array($sectionId, $selectedSectionIdsByType[$group['sample_type_id']] ?? [], true);
                                            @endphp
                                            <label class="ceq-section-card {{ $checked ? 'is-selected' : '' }}" for="trf-section-{{ $group['sample_type_id'] }}-{{ $section['id'] }}">
                                                <input type="checkbox"
                                                       class="ceq-section-card__input"
                                                       id="trf-section-{{ $group['sample_type_id'] }}-{{ $section['id'] }}"
                                                       value="{{ $section['id'] }}"
                                                       wire:model.live="selectedSectionIdsByType.{{ $group['sample_type_id'] }}">
                                                <span class="ceq-section-card__check"><i class="mdi mdi-check"></i></span>
                                                <span class="ceq-section-card__body">
                                                    <span class="ceq-section-card__title">
                                                        {{ $section['title'] }}
                                                        @if(($section['section_type'] ?? '') === 'rows_section')
                                                            <span class="ceq-tag">Sample rows</span>
                                                        @endif
                                                    </span>
                                                    @if(!empty($section['description']))
                                                        <span class="ceq-section-card__desc">{{ $section['description'] }}</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @empty
                                            <div class="ceq-alert ceq-alert--warn mb-0">
                                                <i class="mdi mdi-information-outline"></i>
                                                <div>No fillable sections for this TRF. Continue — the form will still be generated with quote tests.</div>
                                            </div>
                                        @endforelse
                                    </div>
                                @endif

                                @if($phase === 'fill' && $this->currentTrfGroup)
                                    @php
                                        $group = $this->currentTrfGroup;
                                        $typeId = $group['sample_type_id'];
                                    @endphp
                                    <div class="ceq-step-intro">
                                        <h3 class="ceq-step-intro__title">Fill details — {{ $group['sample_type_name'] }}</h3>
                                        <p class="ceq-step-intro__text">
                                            Tests and sample counts stay locked from the quotation.
                                            After finishing, you open the request view to review PDFs and complete anything left.
                                        </p>
                                    </div>

                                    @forelse($this->currentSelectedSections as $section)
                                        <div class="ceq-panel">
                                            <div class="ceq-panel__head">
                                                <h4 class="ceq-panel__title">
                                                    <i class="mdi {{ ($section['section_type'] ?? '') === 'rows_section' ? 'mdi-view-list-outline' : 'mdi-form-select' }}"></i>
                                                    {{ $section['title'] }}
                                                </h4>
                                            </div>
                                            <div class="ceq-panel__body">
                                                @if(($section['section_type'] ?? '') === 'rows_section')
                                                    <p class="ceq-help mb-3">
                                                        Fill supplemental details for each physical sample below.
                                                    </p>
                                                    @include('livewire.partials.quotation-wizard-sample-cards', [
                                                        'typeId' => $typeId,
                                                        'trfIndex' => $trfIndex,
                                                        'rowCount' => $this->physicalSampleCountForType($typeId),
                                                        'fields' => $section['fields'] ?? [],
                                                        'sectionRowFieldValuesByType' => $sectionRowFieldValuesByType,
                                                    ])
                                                @else
                                                    <div class="row">
                                                    @forelse($section['fields'] as $field)
                                                        @php
                                                            $fieldName = (string) ($field['name'] ?? '');
                                                            if (($field['render_paired'] ?? false) && $fieldName === 'sample_quantity_unit') {
                                                                continue;
                                                            }
                                                            $type = $field['element_type'] ?? 'text';
                                                            $isFullWidth = in_array($type, ['textarea', 'rich_text'], true)
                                                                || in_array($fieldName, ['sample_description'], true);
                                                            $colClass = $isFullWidth ? 'col-12' : 'col-md-6';
                                                        @endphp
                                                        <div class="{{ $colClass }}">
                                                            <div class="ceq-field">
                                                                <label class="ceq-field__label">
                                                                    @if($fieldName === 'sample_quantity')
                                                                        Qty / Unit
                                                                    @else
                                                                        {{ $field['label'] }}
                                                                    @endif
                                                                    @if($field['is_required']) <span class="text-danger">*</span> @endif
                                                                </label>
                                                                @if($fieldName === 'sample_quantity')
                                                                    <div class="d-flex ceq-wizard-qty-unit" style="gap: 8px;">
                                                                        <input type="number"
                                                                               step="0.01"
                                                                               min="0"
                                                                               placeholder="Amount"
                                                                               wire:model="sectionFieldValuesByType.{{ $typeId }}.sample_quantity"
                                                                               class="form-control ceq-input @error('sectionFieldValuesByType.'.$typeId.'.sample_quantity') is-invalid @enderror">
                                                                        <select wire:model="sectionFieldValuesByType.{{ $typeId }}.sample_quantity_unit"
                                                                                class="form-control ceq-input no-select2 @error('sectionFieldValuesByType.'.$typeId.'.sample_quantity_unit') is-invalid @enderror">
                                                                            <option value="">Unit</option>
                                                                            @foreach($field['unit_options'] ?? [] as $option)
                                                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @error('sectionFieldValuesByType.'.$typeId.'.sample_quantity')
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                    @error('sectionFieldValuesByType.'.$typeId.'.sample_quantity_unit')
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                @elseif(in_array($type, ['select', 'radio'], true) && !empty($field['options']))
                                                                    <select wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}"
                                                                            class="form-control ceq-input">
                                                                        <option value="">Select...</option>
                                                                        @foreach($field['options'] as $option)
                                                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @elseif($type === 'checkbox' && !empty($field['options']))
                                                                    <div class="d-flex flex-column" style="gap: 0.35rem;">
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
                                                                @elseif($type === 'rich_text' || $fieldName === 'sample_description')
                                                                    @include('livewire.partials.livewire-tinymce-field', [
                                                                        'wireKey' => 'sectionFieldValuesByType.'.$typeId.'.sample_description',
                                                                        'editorId' => 'ceq-desc-'.$typeId.'-'.$trfIndex,
                                                                        'value' => $sectionFieldValuesByType[$typeId]['sample_description'] ?? '',
                                                                    ])
                                                                @elseif($type === 'textarea')
                                                                    <textarea rows="2" wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}" class="form-control ceq-input"></textarea>
                                                                @elseif($type === 'date')
                                                                    <input type="date" wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}" class="form-control ceq-input">
                                                                @elseif($type === 'number')
                                                                    <input type="number" step="0.01" min="0" wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}" class="form-control ceq-input">
                                                                @else
                                                                    <input type="text" wire:model="sectionFieldValuesByType.{{ $typeId }}.{{ $field['name'] }}" class="form-control ceq-input">
                                                                @endif
                                                                @if($fieldName !== 'sample_quantity')
                                                                    @error('sectionFieldValuesByType.'.$typeId.'.'.$field['name'])
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="col-12">
                                                            <p class="ceq-help mb-0">No wizard-editable fields in this section.</p>
                                                        </div>
                                                    @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="ceq-alert ceq-alert--muted mb-0">
                                            <i class="mdi mdi-information-outline"></i>
                                            <div>No sections selected for this sample type. Continue to the next step.</div>
                                        </div>
                                    @endforelse
                                @endif

                                @if($phase === 'send')
                                    <div class="ceq-step-intro">
                                        <h3 class="ceq-step-intro__title">Ready to create the enquiry</h3>
                                        <p class="ceq-step-intro__text">
                                            Review delivery options, then create the enquiry and TRF. You will land on the request view afterward.
                                        </p>
                                    </div>

                                    <div class="ceq-summary-grid">
                                        <div class="ceq-summary-card">
                                            <span class="ceq-summary-card__label">Quotation</span>
                                            <strong>{{ $quoteNumber !== '' ? $quoteNumber : '—' }}</strong>
                                        </div>
                                        <div class="ceq-summary-card">
                                            <span class="ceq-summary-card__label">Customer</span>
                                            <strong>{{ $customerName !== '' ? $customerName : '—' }}</strong>
                                        </div>
                                        <div class="ceq-summary-card">
                                            <span class="ceq-summary-card__label">Origin</span>
                                            <strong>{{ str_replace('_', '-', $sourceChannel) }}</strong>
                                        </div>
                                        <div class="ceq-summary-card">
                                            <span class="ceq-summary-card__label">Physical samples</span>
                                            <strong>{{ $numberOfSamples }}</strong>
                                        </div>
                                    </div>

                                    <div class="ceq-panel">
                                        <div class="ceq-panel__head">
                                            <h4 class="ceq-panel__title"><i class="mdi mdi-file-multiple-outline"></i> TRF forms</h4>
                                        </div>
                                        <div class="ceq-panel__body">
                                            <ul class="ceq-plain-list mb-0">
                                                @forelse($trfGroups as $group)
                                                    <li>
                                                        <strong>{{ $group['sample_type_name'] }}</strong>
                                                        <span class="text-muted">→ {{ $group['form_name'] }}</span>
                                                    </li>
                                                @empty
                                                    <li class="text-muted">No TRF groups resolved</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>

                                    @if($quoteAlreadySentFromBilling && ! $notifyAgain)
                                        <div class="ceq-alert ceq-alert--info">
                                            <i class="mdi mdi-information-outline"></i>
                                            <div>
                                                This quotation was already sent from Billing. Finishing creates the enquiry and
                                                {{ count($trfGroups) > 1 ? count($trfGroups).' TRFs' : 'the TRF' }},
                                                and marks the request as <strong>Quotation Sent</strong> without re-sending.
                                            </div>
                                        </div>
                                        <label class="ceq-toggle-row">
                                            <input type="checkbox" wire:model.live="notifyAgain">
                                            <span>
                                                <strong>Notify customer again</strong>
                                                <small>Optional — re-send delivery channels below</small>
                                            </span>
                                        </label>
                                    @else
                                        <div class="ceq-alert ceq-alert--warn">
                                            <i class="mdi mdi-send-outline"></i>
                                            <div>
                                                Finishing creates the enquiry, generates
                                                {{ count($trfGroups) > 1 ? count($trfGroups).' TRFs' : 'the TRF' }},
                                                sends the quotation, and moves the request to <strong>Quotation Sent</strong>.
                                            </div>
                                        </div>

                                        <div class="ceq-choice-grid ceq-choice-grid--channels">
                                            <label class="ceq-choice {{ $sendEmail ? 'is-selected' : '' }}">
                                                <input type="checkbox" class="ceq-choice__input" wire:model="sendEmail">
                                                <span class="ceq-choice__icon"><i class="mdi mdi-email-outline"></i></span>
                                                <span class="ceq-choice__title">Email</span>
                                                <span class="ceq-choice__hint">Send quotation PDF by email</span>
                                            </label>
                                            <label class="ceq-choice {{ $sendPortal ? 'is-selected' : '' }}">
                                                <input type="checkbox" class="ceq-choice__input" wire:model="sendPortal">
                                                <span class="ceq-choice__icon"><i class="mdi mdi-monitor-cellphone"></i></span>
                                                <span class="ceq-choice__title">Portal</span>
                                                <span class="ceq-choice__hint">Deliver to customer portal</span>
                                            </label>
                                        </div>

                                        @if($quoteAlreadySentFromBilling)
                                            <label class="ceq-toggle-row mt-3">
                                                <input type="checkbox" wire:model.live="notifyAgain">
                                                <span>
                                                    <strong>Notify customer again</strong>
                                                    <small>Re-send even though Billing already delivered this quote</small>
                                                </span>
                                            </label>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="ceq-wizard-footer">
                        <button type="button"
                                class="ceq-btn ceq-btn--ghost"
                                wire:click="close"
                                x-on:click="window.dispatchEvent(new CustomEvent('lw-destroy-tinymce'))">
                            Cancel
                        </button>
                        <div class="ceq-wizard-footer__actions">
                            @if($quotationId)
                                @if($phase !== 'review')
                                    <button type="button" class="ceq-btn ceq-btn--ghost" wire:click="previousStep">
                                        <i class="mdi mdi-arrow-left"></i> Back
                                    </button>
                                @endif
                                @if($phase !== 'send')
                                    <button type="button" class="ceq-btn ceq-btn--primary" wire:loading.attr="disabled"
                                            x-on:click="
                                                window.dispatchEvent(new CustomEvent('ceq-sync-tinymce'));
                                                if (document.activeElement) document.activeElement.blur();
                                                setTimeout(() => $wire.nextStep(), 80);
                                            ">
                                        Continue <i class="mdi mdi-arrow-right"></i>
                                    </button>
                                @else
                                    <button type="button" class="ceq-btn ceq-btn--accent" wire:loading.attr="disabled"
                                            x-on:click="
                                                window.dispatchEvent(new CustomEvent('ceq-sync-tinymce'));
                                                if (document.activeElement) document.activeElement.blur();
                                                setTimeout(() => $wire.finish(), 80);
                                            ">
                                        <span wire:loading.remove wire:target="finish">
                                            <i class="mdi {{ $quoteAlreadySentFromBilling && ! $notifyAgain ? 'mdi-check-decagram' : 'mdi-send' }}"></i>
                                            {{ $quoteAlreadySentFromBilling && ! $notifyAgain ? 'Create enquiry & TRF' : 'Create, generate TRF & send' }}
                                        </span>
                                        <span wire:loading wire:target="finish">
                                            <span class="spinner-border spinner-border-sm"></span> Working…
                                        </span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .ceq-wizard-overlay {
                background: rgba(15, 23, 42, 0.58);
                z-index: 1055;
                backdrop-filter: blur(2px);
            }

            .ceq-wizard-dialog {
                max-width: 980px;
            }

            .ceq-wizard-modal {
                --ceq-brand: #6D0A0E;
                --ceq-brand-soft: #f8ecec;
                --ceq-ink: #0f172a;
                --ceq-muted: #64748b;
                --ceq-border: #e2e8f0;
                --ceq-surface: #f8fafc;
                border: 0;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
            }

            .ceq-wizard-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                padding: 1.15rem 1.35rem 1rem;
                background:
                    linear-gradient(135deg, rgba(109, 10, 14, 0.08), transparent 42%),
                    linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
                border-bottom: 1px solid var(--ceq-border);
            }

            .ceq-wizard-header__eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: var(--ceq-brand);
                margin-bottom: 0.35rem;
            }

            .ceq-wizard-header__title {
                margin: 0;
                font-size: 1.35rem;
                font-weight: 750;
                color: var(--ceq-ink);
                line-height: 1.2;
            }

            .ceq-wizard-header__meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.4rem;
                margin-top: 0.65rem;
            }

            .ceq-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
                padding: 0.22rem 0.65rem;
                border-radius: 999px;
                border: 1px solid #fecaca;
                background: var(--ceq-brand-soft);
                color: var(--ceq-brand);
                font-size: 0.75rem;
                font-weight: 600;
            }

            .ceq-chip--muted {
                border-color: var(--ceq-border);
                background: #fff;
                color: var(--ceq-muted);
            }

            .ceq-wizard-close {
                width: 2.1rem;
                height: 2.1rem;
                border-radius: 999px;
                border: 1px solid var(--ceq-border);
                background: #fff;
                color: var(--ceq-muted);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }

            .ceq-wizard-close:hover {
                color: var(--ceq-ink);
                border-color: #cbd5e1;
            }

            .ceq-stepper {
                display: flex;
                align-items: stretch;
                gap: 0.35rem;
                padding: 0.85rem 1.35rem;
                background: var(--ceq-surface);
                border-bottom: 1px solid var(--ceq-border);
                overflow-x: auto;
            }

            .ceq-stepper__item {
                display: flex;
                align-items: center;
                gap: 0.55rem;
                min-width: 0;
                flex: 0 1 auto;
            }

            .ceq-stepper__node {
                width: 2rem;
                height: 2rem;
                border-radius: 999px;
                border: 1.5px solid var(--ceq-border);
                background: #fff;
                color: var(--ceq-muted);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 0.95rem;
            }

            .ceq-stepper__item.is-active .ceq-stepper__node {
                border-color: var(--ceq-brand);
                background: var(--ceq-brand);
                color: #fff;
                box-shadow: 0 0 0 4px rgba(109, 10, 14, 0.12);
            }

            .ceq-stepper__item.is-done .ceq-stepper__node {
                border-color: #86efac;
                background: #ecfdf5;
                color: #15803d;
            }

            .ceq-stepper__copy {
                display: flex;
                flex-direction: column;
                min-width: 0;
            }

            .ceq-stepper__label {
                font-size: 0.78rem;
                font-weight: 700;
                color: var(--ceq-ink);
                white-space: nowrap;
            }

            .ceq-stepper__hint {
                font-size: 0.68rem;
                color: var(--ceq-muted);
                white-space: nowrap;
            }

            .ceq-stepper__item.is-active .ceq-stepper__label {
                color: var(--ceq-brand);
            }

            .ceq-stepper__rail {
                width: 1.5rem;
                height: 2px;
                align-self: center;
                background: #e2e8f0;
                flex: 0 0 auto;
            }

            .ceq-stepper__rail.is-done {
                background: #86efac;
            }

            .ceq-wizard-body {
                padding: 1.15rem 1.35rem 1.25rem;
                background: #fff;
                max-height: min(62vh, 640px);
                overflow-y: auto;
            }

            .ceq-step-pane {
                animation: ceq-fade-up 0.22s ease;
            }

            @keyframes ceq-fade-up {
                from { opacity: 0; transform: translateY(6px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @media (prefers-reduced-motion: reduce) {
                .ceq-step-pane { animation: none; }
            }

            .ceq-step-intro {
                margin-bottom: 1rem;
            }

            .ceq-step-intro__title {
                margin: 0 0 0.3rem;
                font-size: 1.05rem;
                font-weight: 720;
                color: var(--ceq-ink);
            }

            .ceq-step-intro__text {
                margin: 0;
                font-size: 0.875rem;
                color: var(--ceq-muted);
                line-height: 1.45;
                max-width: 46rem;
            }

            .ceq-stat-row {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.75rem;
                margin-bottom: 1rem;
            }

            .ceq-stat {
                border: 1px solid var(--ceq-border);
                border-radius: 12px;
                background: var(--ceq-surface);
                padding: 0.85rem 0.95rem;
            }

            .ceq-stat__label,
            .ceq-stat__hint {
                display: block;
                font-size: 0.7rem;
                color: var(--ceq-muted);
            }

            .ceq-stat__value {
                display: block;
                font-size: 1.35rem;
                font-weight: 750;
                color: var(--ceq-ink);
                margin: 0.15rem 0;
            }

            .ceq-panel {
                border: 1px solid var(--ceq-border);
                border-radius: 12px;
                background: #fff;
                margin-bottom: 1rem;
                overflow: hidden;
            }

            .ceq-panel__head {
                padding: 0.75rem 1rem;
                background: var(--ceq-surface);
                border-bottom: 1px solid var(--ceq-border);
            }

            .ceq-panel__title {
                margin: 0;
                font-size: 0.88rem;
                font-weight: 700;
                color: var(--ceq-ink);
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
            }

            .ceq-panel__title .mdi {
                color: var(--ceq-brand);
            }

            .ceq-panel__body {
                padding: 1rem;
            }

            .ceq-panel__body--flush {
                padding: 0;
            }

            .ceq-table {
                width: 100%;
                font-size: 0.84rem;
            }

            .ceq-table th,
            .ceq-table td {
                padding: 0.7rem 1rem;
                border-bottom: 1px solid #f1f5f9;
                vertical-align: middle;
            }

            .ceq-table thead th {
                background: #fff;
                color: var(--ceq-muted);
                font-size: 0.72rem;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                font-weight: 700;
                border-bottom-color: var(--ceq-border);
            }

            .ceq-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .ceq-qty-pill {
                display: inline-flex;
                min-width: 1.75rem;
                justify-content: center;
                padding: 0.15rem 0.5rem;
                border-radius: 999px;
                background: var(--ceq-brand-soft);
                color: var(--ceq-brand);
                font-weight: 700;
                font-size: 0.78rem;
            }

            .ceq-field {
                margin-bottom: 0.95rem;
            }

            .ceq-field__label {
                display: block;
                font-size: 0.78rem;
                font-weight: 700;
                color: #334155;
                margin-bottom: 0.35rem;
            }

            .ceq-input {
                border-radius: 8px !important;
                border-color: #cbd5e1 !important;
                font-size: 0.875rem;
            }

            .ceq-input:focus {
                border-color: #b45353 !important;
                box-shadow: 0 0 0 0.15rem rgba(109, 10, 14, 0.12) !important;
            }

            .ceq-locked-value {
                display: flex;
                flex-direction: column;
                gap: 0.15rem;
                padding: 0.65rem 0.8rem;
                border-radius: 8px;
                border: 1px dashed #cbd5e1;
                background: var(--ceq-surface);
            }

            .ceq-locked-value strong {
                font-size: 1rem;
                color: var(--ceq-ink);
            }

            .ceq-locked-value span {
                font-size: 0.72rem;
                color: var(--ceq-muted);
            }

            .ceq-choice-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.65rem;
            }

            .ceq-choice-grid--channels {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ceq-choice {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 0.2rem;
                padding: 0.85rem 0.9rem;
                border: 1px solid var(--ceq-border);
                border-radius: 12px;
                background: #fff;
                cursor: pointer;
                transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
                margin: 0;
            }

            .ceq-choice:hover {
                border-color: #f0b4b4;
            }

            .ceq-choice.is-selected {
                border-color: var(--ceq-brand);
                background: var(--ceq-brand-soft);
                box-shadow: 0 0 0 3px rgba(109, 10, 14, 0.08);
            }

            .ceq-choice__input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            .ceq-choice__icon {
                color: var(--ceq-brand);
                font-size: 1.15rem;
                margin-bottom: 0.15rem;
            }

            .ceq-choice__title {
                font-size: 0.84rem;
                font-weight: 720;
                color: var(--ceq-ink);
            }

            .ceq-choice__hint {
                font-size: 0.72rem;
                color: var(--ceq-muted);
                line-height: 1.35;
            }

            .ceq-section-list {
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
            }

            .ceq-section-card {
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 0.9rem 1rem;
                border: 1px solid var(--ceq-border);
                border-radius: 12px;
                background: #fff;
                cursor: pointer;
                margin: 0;
                transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
            }

            .ceq-section-card:hover {
                border-color: #f0b4b4;
            }

            .ceq-section-card.is-selected {
                border-color: var(--ceq-brand);
                background: var(--ceq-brand-soft);
                box-shadow: 0 0 0 3px rgba(109, 10, 14, 0.08);
            }

            .ceq-section-card__input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            .ceq-section-card__check {
                width: 1.35rem;
                height: 1.35rem;
                border-radius: 6px;
                border: 1.5px solid #cbd5e1;
                background: #fff;
                color: transparent;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                margin-top: 0.1rem;
            }

            .ceq-section-card.is-selected .ceq-section-card__check {
                background: var(--ceq-brand);
                border-color: var(--ceq-brand);
                color: #fff;
            }

            .ceq-section-card__title {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.4rem;
                font-size: 0.9rem;
                font-weight: 700;
                color: var(--ceq-ink);
            }

            .ceq-section-card__desc {
                display: block;
                margin-top: 0.2rem;
                font-size: 0.78rem;
                color: var(--ceq-muted);
            }

            .ceq-tag {
                display: inline-flex;
                padding: 0.1rem 0.45rem;
                border-radius: 999px;
                background: #eff6ff;
                color: #1d4ed8;
                border: 1px solid #bfdbfe;
                font-size: 0.68rem;
                font-weight: 700;
            }

            .ceq-summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.65rem;
                margin-bottom: 1rem;
            }

            .ceq-summary-card {
                border: 1px solid var(--ceq-border);
                border-radius: 12px;
                padding: 0.8rem 0.9rem;
                background: var(--ceq-surface);
            }

            .ceq-summary-card__label {
                display: block;
                font-size: 0.68rem;
                color: var(--ceq-muted);
                text-transform: uppercase;
                letter-spacing: 0.03em;
                font-weight: 700;
                margin-bottom: 0.2rem;
            }

            .ceq-summary-card strong {
                font-size: 0.9rem;
                color: var(--ceq-ink);
                word-break: break-word;
            }

            .ceq-plain-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .ceq-plain-list li {
                padding: 0.45rem 0;
                border-bottom: 1px solid #f1f5f9;
                font-size: 0.84rem;
            }

            .ceq-plain-list li:last-child {
                border-bottom: 0;
                padding-bottom: 0;
            }

            .ceq-alert {
                display: flex;
                gap: 0.65rem;
                align-items: flex-start;
                padding: 0.85rem 0.95rem;
                border-radius: 12px;
                border: 1px solid var(--ceq-border);
                margin-bottom: 1rem;
                font-size: 0.84rem;
                line-height: 1.4;
            }

            .ceq-alert .mdi {
                font-size: 1.15rem;
                margin-top: 0.05rem;
            }

            .ceq-alert--danger {
                background: #fef2f2;
                border-color: #fecaca;
                color: #991b1b;
            }

            .ceq-alert--info {
                background: #eff6ff;
                border-color: #bfdbfe;
                color: #1e3a8a;
            }

            .ceq-alert--warn {
                background: #fffbeb;
                border-color: #fde68a;
                color: #92400e;
            }

            .ceq-alert--muted {
                background: var(--ceq-surface);
                color: #334155;
            }

            .ceq-toggle-row {
                display: flex;
                align-items: flex-start;
                gap: 0.65rem;
                padding: 0.85rem 0.95rem;
                border: 1px solid var(--ceq-border);
                border-radius: 12px;
                background: #fff;
                margin: 0;
                cursor: pointer;
            }

            .ceq-toggle-row strong {
                display: block;
                font-size: 0.86rem;
            }

            .ceq-toggle-row small {
                display: block;
                color: var(--ceq-muted);
                font-size: 0.74rem;
                margin-top: 0.1rem;
            }

            .ceq-help {
                font-size: 0.8rem;
                color: var(--ceq-muted);
                margin: 0;
            }

            .ceq-wizard-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.9rem 1.35rem;
                border-top: 1px solid var(--ceq-border);
                background: #fff;
            }

            .ceq-wizard-footer__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                justify-content: flex-end;
            }

            .ceq-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.35rem;
                border-radius: 9px;
                border: 1px solid var(--ceq-border);
                background: #fff;
                color: var(--ceq-ink);
                font-size: 0.84rem;
                font-weight: 700;
                padding: 0.55rem 0.95rem;
                cursor: pointer;
                transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
            }

            .ceq-btn:disabled {
                opacity: 0.65;
                cursor: wait;
            }

            .ceq-btn--ghost:hover {
                border-color: #cbd5e1;
                background: var(--ceq-surface);
            }

            .ceq-btn--primary {
                background: var(--ceq-brand);
                border-color: var(--ceq-brand);
                color: #fff;
            }

            .ceq-btn--primary:hover {
                background: #56080b;
                border-color: #56080b;
                color: #fff;
            }

            .ceq-btn--accent {
                background: #15803d;
                border-color: #15803d;
                color: #fff;
            }

            .ceq-btn--accent:hover {
                background: #166534;
                border-color: #166534;
                color: #fff;
            }

            .ceq-wizard-modal .ceq-wizard-qty-unit input {
                flex: 1 1 55%;
                min-width: 0;
            }

            .ceq-wizard-modal .ceq-wizard-qty-unit select {
                flex: 1 1 45%;
                min-width: 0;
            }

            .ceq-wizard-modal .rft-sample-row-card {
                border: 1px solid var(--ceq-border);
                border-radius: 12px;
                margin-bottom: 0.75rem;
                background: #fff;
            }

            .ceq-wizard-modal .rft-sample-row-card.is-open {
                border-color: #f0b4b4;
                box-shadow: 0 0 0 3px rgba(109, 10, 14, 0.06);
            }

            .ceq-wizard-modal .rft-sample-row-card__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.85rem 1rem;
            }

            .ceq-wizard-modal .rft-sample-row-card__body {
                padding: 0 1rem 1rem;
            }

            .ceq-wizard-modal .rft-sample-row-card__title {
                font-size: 0.92rem;
                font-weight: 700;
            }

            .ceq-wizard-modal .rft-sample-row-card__chevron {
                color: var(--ceq-muted);
                font-size: 1.25rem;
                line-height: 1;
            }

            .ceq-wizard-modal .ceq-sample-progress__bar {
                height: 6px;
                background-color: #e2e8f0;
                border-radius: 999px;
                overflow: hidden;
            }

            .ceq-wizard-modal .ceq-sample-progress__bar .progress-bar {
                border-radius: 999px;
                background: var(--ceq-brand);
                transition: width 0.25s ease;
            }

            .ceq-wizard-modal .ceq-clone-first-sample-btn,
            .ceq-wizard-modal .ceq-clone-row-btn {
                border-radius: 8px;
                font-weight: 600;
            }

            @media (max-width: 991.98px) {
                .ceq-stat-row,
                .ceq-summary-grid,
                .ceq-choice-grid,
                .ceq-choice-grid--channels {
                    grid-template-columns: 1fr;
                }

                .ceq-stepper__hint {
                    display: none;
                }
            }

            @media (min-width: 768px) {
                .ceq-wizard-header,
                .ceq-stepper,
                .ceq-wizard-body,
                .ceq-wizard-footer {
                    padding-left: 1.55rem;
                    padding-right: 1.55rem;
                }
            }
        </style>
    @endif
</div>
