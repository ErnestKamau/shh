<div
    class="acc-wizard-root acc-wizard-root--enquiry lab-surface-theme"
    x-data
    x-effect="document.body.classList.toggle('modal-open', $wire.showModal)"
>
    @if($showModal)
        <div class="acc-wizard-backdrop" tabindex="-1" role="dialog" wire:click.self="closeWizard">
            <div class="modal-dialog modal-xl acc-wizard-dialog" role="document">
                <div class="modal-content acc-wizard-modal">
                    <div class="acc-wizard-header">
                        <div class="acc-wizard-header-text">
                            <span class="acc-wizard-eyebrow">Commercial / Phase 1</span>
                            <h4 class="acc-wizard-title">
                                <i class="mdi mdi-file-chart-outline"></i>
                                Process Request
                            </h4>
                            <p class="mb-0 text-muted small">{{ $requestReference }} · {{ $customerName }}</p>
                        </div>
                        <button type="button" class="acc-wizard-close" wire:click="closeWizard" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>

                    <div class="acc-wizard-steps" role="tablist">
                        @php
                            $activeIndex = collect($this->wizardSteps)->search(fn ($s) => $s['key'] === $activeStep);
                            $activeIndex = $activeIndex === false ? 0 : (int) $activeIndex;
                            $prevStepKey = $activeIndex > 0 ? ($this->wizardSteps[$activeIndex - 1]['key'] ?? null) : null;
                        @endphp
                        @foreach($this->wizardSteps as $index => $meta)
                            @php
                                $isActive = $activeStep === $meta['key'];
                                $isDone = $activeIndex > $index;
                            @endphp
                            <button
                                type="button"
                                class="acc-wizard-step {{ $isActive ? 'is-active' : '' }} {{ $isDone ? 'is-done' : '' }}"
                                wire:click.stop.prevent="goToStep('{{ $meta['key'] }}')"
                            >
                                <span class="acc-wizard-step-index">
                                    @if($isDone)
                                        <i class="mdi mdi-check"></i>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>
                                <span class="acc-wizard-step-label">{{ $meta['label'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="acc-wizard-top-nav">
                        <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            @disabled($prevStepKey === null)
                            @if($prevStepKey) wire:click.stop.prevent="goToStep('{{ $prevStepKey }}')" @endif>
                            <i class="mdi mdi-arrow-left"></i> Back
                        </button>
                        <div class="acc-wizard-top-nav-actions">
                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="closeWizard">Cancel</button>
                            @if($activeStep === 'review')
                                <button type="button" class="btn btn-primary btn-sm" wire:click="saveReviewAndContinue">
                                    Next <i class="mdi mdi-arrow-right"></i>
                                </button>
                            @elseif($activeStep === 'sample_config')
                                <button type="button" class="btn btn-primary btn-sm" wire:click="saveSampleConfigAndContinue">
                                    Next <i class="mdi mdi-arrow-right"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-primary btn-sm" wire:click="sendQuotation" wire:loading.attr="disabled" @disabled($lines === [])>
                                    <span wire:loading.remove wire:target="sendQuotation">
                                        <i class="mdi mdi-send"></i>
                                        {{ $quotationSent ? 'Send again' : 'Send to customer' }}
                                    </span>
                                    <span wire:loading wire:target="sendQuotation">Sending…</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="acc-wizard-body">
                        @if($activeStep === 'review')
                            <section class="acc-wizard-section">
                                @include('livewire.partials.process-enquiry-status-alert')

                                <h6 class="acc-wizard-section-title">Request summary</h6>
                                <div class="row acc-wizard-fields mb-3">
                                    <div class="col-md-3"><strong>Status</strong><br>{{ $enquiryStatus }}</div>
                                    <div class="col-md-3"><strong>Origin</strong><br>{{ ucwords(str_replace('_', ' ', $sourceChannel ?: '—')) }}</div>
                                    <div class="col-md-3"><strong>Customer</strong><br>{{ $customerName }}</div>
                                    <div class="col-md-3"><strong>Sample type</strong><br>{{ $headerSampleType }}</div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="acc-wizard-section-title">Statement of conformity required in reports</h6>
                                    <div class="d-flex flex-wrap" style="gap: 1rem 1.5rem;">
                                        @foreach($statementOfConformityOptions as $option)
                                            <span class="small {{ $option['checked'] ? 'font-weight-bold text-dark' : 'text-muted' }}">
                                                {{ $option['checked'] ? '☑' : '☐' }} {{ $option['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                @if(count($displaySampleRows) > 0)
                                    <div class="mb-4">
                                        <h6 class="acc-wizard-section-title">Sample details</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered workflow-table mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Sample description</th>
                                                        <th>Qty</th>
                                                        <th>Analysis types</th>
                                                        <th>Tests requested</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($displaySampleRows as $index => $row)
                                                        <tr wire:key="enquiry-sample-{{ $index }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td class="acc-enquiry-rich-text-cell">
                                                                <div class="acc-enquiry-rich-text">{!! $row['sample_description_html'] ?? '—' !!}</div>
                                                            </td>
                                                            <td>{{ $row['qty'] }}</td>
                                                            <td>{{ $row['analysis_types'] ?? '—' }}</td>
                                                            <td class="{{ ($row['tests_requested_count'] ?? 0) > 5 ? 'small' : '' }}">{{ $row['tests_requested'] ?? '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @elseif(count($requestedTests) > 0)
                                    <div class="mb-4">
                                        <h6 class="acc-wizard-section-title">Tests requested</h6>
                                        <ul class="mb-0 pl-3 {{ count($requestedTests) > 5 ? 'small' : '' }}">
                                            @foreach($requestedTests as $test)
                                                <li>{{ $test['label'] }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if($customerFeedbackNotes !== '')
                                    <div class="alert alert-warning py-2 mb-3">
                                        <strong class="d-block mb-1">Customer feedback (read-only)</strong>
                                        <div class="small mb-0" style="white-space: pre-wrap;">{{ $customerFeedbackNotes }}</div>
                                    </div>
                                @endif

                            </section>
                        @endif

                        @if($activeStep === 'sample_config')
                            @include('livewire.partials.process-enquiry-status-alert')

                            @include('livewire.partials.acceptance-sample-config-table')
                        @endif

                        @if($activeStep === 'pricing')
                            <div wire:key="enquiry-wizard-pricing-{{ $quotationMode }}-{{ $quotationModeRenderKey }}">
                            @include('livewire.partials.process-enquiry-status-alert')

                            <section class="acc-wizard-section acc-pricing-section">
                                <div class="acc-pricing-mode mb-3" wire:key="enquiry-pricing-mode-{{ $quotationMode }}-{{ $quotationModeRenderKey }}">
                                    <label class="acc-label d-block mb-2">Quotation source</label>
                                    <div class="d-flex flex-wrap" style="gap: 1rem 1.5rem;" role="radiogroup" aria-label="Quotation source">
                                        <label class="mb-0 d-flex align-items-center">
                                            <input
                                                type="radio"
                                                class="mr-2"
                                                name="enquiry-quotation-mode"
                                                value="build_new"
                                                wire:click="setQuotationMode('build_new')"
                                                @checked($quotationMode === 'build_new')
                                            >
                                            Build new from this enquiry
                                        </label>
                                        <label class="mb-0 d-flex align-items-center">
                                            <input
                                                type="radio"
                                                class="mr-2"
                                                name="enquiry-quotation-mode"
                                                value="use_existing"
                                                wire:click="setQuotationMode('use_existing')"
                                                @checked($quotationMode === 'use_existing')
                                            >
                                            Use existing quotation
                                        </label>
                                    </div>
                                </div>

                                <div wire:key="enquiry-existing-quotation-panel-{{ $quotationMode }}-{{ $quotationModeRenderKey }}">
                                @if($quotationMode === 'use_existing')
                                    <div class="form-group mb-3">
                                        <label class="acc-label" for="existing-quotation-search">Customer quotations</label>
                                        <div
                                            class="tag-select-container acc-existing-quotation-select {{ $showExistingQuotationDropdown ? 'is-open' : '' }}"
                                            wire:click="openExistingQuotationDropdown"
                                            wire:click.outside="closeExistingQuotationDropdown"
                                        >
                                            <div class="tag-select-input">
                                                @if($selectedExistingQuotationId)
                                                    <span class="tag-badge" wire:key="existing-quote-badge-{{ $selectedExistingQuotationId }}">
                                                        {{ $this->existingQuotationLabel($selectedExistingQuotationId) }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearExistingQuotation" role="button" tabindex="0" aria-label="Clear selected quotation"></i>
                                                    </span>
                                                @endif
                                                <input
                                                    id="existing-quotation-search"
                                                    type="text"
                                                    class="tag-input"
                                                    wire:model.live.debounce.200ms="existingQuotationSearch"
                                                    wire:focus="openExistingQuotationDropdown"
                                                    placeholder="{{ $selectedExistingQuotationId ? 'Search to change…' : 'Search quotations…' }}"
                                                    autocomplete="off"
                                                >
                                            </div>
                                            @if($showExistingQuotationDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($this->filteredExistingQuotationOptions() as $option)
                                                        <div
                                                            class="tag-dropdown-item {{ $selectedExistingQuotationId === $option['id'] ? 'is-selected' : '' }}"
                                                            wire:key="existing-quote-option-{{ $option['id'] }}"
                                                            wire:click.stop="selectExistingQuotation('{{ $option['id'] }}')"
                                                        >
                                                            {{ $option['quote_number'] !== '' ? $option['quote_number'] : $option['label'] }}
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">
                                                            {{ $existingQuotationOptions === [] ? 'No quotations available' : 'No matching quotations' }}
                                                        </div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @if($existingQuotationOptions === [])
                                            <p class="small text-muted mt-2 mb-0">
                                                No complete, unexpired quotations for this customer. Create one under Billing → Quotations, or switch to Build new.
                                            </p>
                                        @endif
                                    </div>
                                    @if($quotationMismatchWarning !== '')
                                        <div
                                            class="alert alert-warning py-2 px-3 small"
                                            wire:key="enquiry-mismatch-{{ md5($quotationMismatchWarning) }}"
                                            x-data
                                            x-init="window.clearTimeout($el._dismissTimer); $el._dismissTimer = window.setTimeout(() => $wire.clearQuotationMismatchWarning(), 5000)"
                                        >
                                            {{ $quotationMismatchWarning }}
                                        </div>
                                    @endif
                                @endif
                                </div>

                                <div class="acc-pricing-toolbar mb-3 d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
                                    <h6 class="acc-wizard-section-title mb-0">
                                        @if($quotationMode === 'use_existing')
                                            Selected quotation
                                        @else
                                            Parameters &amp; pricing
                                        @endif
                                    </h6>
                                    <div class="d-flex flex-wrap align-items-center acc-pricing-toolbar-actions" style="gap: 8px;">
                                        @if($quotationSent)
                                            <span class="badge badge-success">Quotation Sent</span>
                                        @elseif($quotationBuilt && $quotationMode === 'build_new')
                                            <span class="badge badge-success">Quotation saved</span>
                                        @endif
                                        @if($quotationMode === 'build_new')
                                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="syncPricesFromPricelist" wire:loading.attr="disabled">
                                                <i class="mdi mdi-sync"></i> Sync from pricelist
                                            </button>
                                        @endif
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            wire:click="viewQuotation"
                                            wire:loading.attr="disabled"
                                            @disabled($lines === [] || ($quotationMode === 'use_existing' && ! $selectedExistingQuotationId))
                                        >
                                            <span wire:loading.remove wire:target="viewQuotation">
                                                <i class="mdi mdi-file-eye-outline"></i> View Quotation
                                            </span>
                                            <span wire:loading wire:target="viewQuotation">
                                                Preparing…
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <div class="acc-pricing-table-wrap acc-pricing-table-wrap--scroll mb-3"
                                     wire:key="enquiry-pricing-lines-{{ $quotationMode }}-{{ $selectedExistingQuotationId ?? $quotationHeaderId ?? 'none' }}">
                                    <table class="table acc-pricing-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Parameter</th>
                                                <th class="text-center">LOQ</th>
                                                <th class="text-center">MU%</th>
                                                <th class="text-right">Unit price</th>
                                                <th class="text-center">Samples</th>
                                                <th class="text-right">Line total</th>
                                                <th class="text-right">Tax %</th>
                                                <th class="text-center">Subcontract</th>
                                                @if($quotationMode === 'build_new')
                                                    <th class="text-center" style="width: 90px;">Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($lines as $index => $line)
                                                @php
                                                    $sampleCount = max(1, (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1));
                                                    $unitPrice = (float) ($line['unit_price'] ?? 0);
                                                    $lineTotal = $sampleCount * $unitPrice;
                                                    $readOnly = $quotationMode === 'use_existing';
                                                    $lineKey = (string) ($line['parameter_key'] ?? $line['analysis_element_id'] ?? $line['line_no'] ?? $index);
                                                @endphp
                                                <tr wire:key="ql-{{ $selectedExistingQuotationId ?? $quotationHeaderId ?? 'new' }}-{{ $lineKey }}-{{ $index }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        {{ $line['parameter_label'] ?? 'Parameter' }}
                                                        @if(!empty($line['is_package']))
                                                            <span class="badge badge-primary badge-pill ml-1">Package</span>
                                                            @if(!empty($line['package_element_metrics']) && is_array($line['package_element_metrics']))
                                                                <div class="small text-muted mt-1">
                                                                    @foreach($line['package_element_metrics'] as $packageMetric)
                                                                        @php
                                                                            $metricParts = array_values(array_filter([
                                                                                filled($packageMetric['loq'] ?? null) ? 'LOQ: '.$packageMetric['loq'] : null,
                                                                                filled($packageMetric['mu_percent'] ?? null) ? 'MU: '.$packageMetric['mu_percent'] : null,
                                                                            ]));
                                                                        @endphp
                                                                        <div>
                                                                            · {{ $packageMetric['label'] ?? 'Parameter' }}
                                                                            @if($metricParts !== [])
                                                                                — {{ implode(' · ', $metricParts) }}
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @elseif(!empty($line['package_element_labels']) && is_array($line['package_element_labels']))
                                                                <div class="small text-muted mt-1">
                                                                    @foreach($line['package_element_labels'] as $packageLabel)
                                                                        <div>· {{ $packageLabel }}</div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td class="text-center text-muted small">{{ $line['loq'] ?? '—' }}</td>
                                                    <td class="text-center text-muted small">{{ $line['mu_percent'] ?? '—' }}</td>
                                                    <td class="text-right">
                                                        @if($readOnly)
                                                            {{ number_format($unitPrice, 2) }}
                                                        @else
                                                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-right"
                                                                   wire:model.blur="lines.{{ $index }}.unit_price">
                                                        @endif
                                                    </td>
                                                    <td class="text-center text-muted">{{ $sampleCount }}</td>
                                                    <td class="text-right text-muted">{{ number_format($lineTotal, 2) }}</td>
                                                    <td class="text-right">
                                                        @if($readOnly)
                                                            {{ number_format((float) ($line['tax'] ?? 0), 2) }}
                                                        @else
                                                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-right"
                                                                   wire:model.blur="lines.{{ $index }}.tax">
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($readOnly)
                                                            {{ !empty($line['subcontracted']) ? 'Yes' : 'No' }}
                                                        @else
                                                            <input type="checkbox" wire:model.live="lines.{{ $index }}.subcontracted">
                                                        @endif
                                                    </td>
                                                    @if($quotationMode === 'build_new')
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-outline-secondary btn-sm" title="Reset price from pricelist"
                                                                        wire:click="resetLinePriceFromPricelist({{ $index }})">
                                                                    <i class="mdi mdi-currency-usd"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" title="Remove line"
                                                                        wire:click="removeQuotationLine({{ $index }})">
                                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $quotationMode === 'use_existing' ? 9 : 10 }}" class="text-muted text-center py-4">
                                                        @if($quotationMode === 'use_existing')
                                                            Select an existing quotation to preview lines and send.
                                                        @else
                                                            No pricing lines yet. Complete sample configuration, then sync prices from the pricelist.
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="{{ $quotationMode === 'use_existing' ? 7 : 8 }}" class="text-right">Subtotal</th>
                                                <th colspan="2" class="text-right">{{ number_format($this->pricingTotals['sub_total'], 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="{{ $quotationMode === 'use_existing' ? 7 : 8 }}" class="text-right">Tax</th>
                                                <th colspan="2" class="text-right">{{ number_format($this->pricingTotals['tax'], 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="{{ $quotationMode === 'use_existing' ? 7 : 8 }}" class="text-right">Grand total @if($this->currencyDisplay !== '') ({{ $this->currencyDisplay }}) @endif</th>
                                                <th colspan="2" class="text-right">{{ number_format($this->pricingTotals['total'], 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="row mb-2 acc-delivery-options">
                                    @if(strtolower($sourceChannel) === 'portal')
                                        <div class="col-md-4">
                                            <label class="acc-label acc-label--compact d-flex align-items-center mb-0">
                                                <input type="checkbox" wire:model="sendPortal" class="mr-2"> Send to portal
                                            </label>
                                        </div>
                                    @endif
                                    <div class="col-md-4">
                                        <label class="acc-label acc-label--compact d-flex align-items-center mb-0">
                                            <input type="checkbox" wire:model="sendEmail" class="mr-2"> Email PDF to customer
                                        </label>
                                    </div>
                                </div>
                            </section>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showBuildQuotationModal)
        <div class="acc-wizard-backdrop acc-wizard-backdrop--nested" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered acc-add-modal-dialog">
                <div class="modal-content acc-wizard-modal acc-add-modal">
                    <div class="acc-wizard-header acc-wizard-header--compact">
                        <h5 class="acc-wizard-title mb-0">Build quotation</h5>
                        <button type="button" class="acc-wizard-close" wire:click="closeBuildQuotationModal">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <p class="text-muted small mb-3">
                            Review pricing for <strong>{{ $customerName }}</strong> ({{ $requestReference }}).
                            Physical samples: <strong>{{ $this->physicalSampleCount }}</strong>.
                            Unit prices will appear on the customer quotation PDF.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th class="text-center">Samples</th>
                                        <th class="text-right">Unit price</th>
                                        <th class="text-right">Line total</th>
                                        <th class="text-right">Tax %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lines as $line)
                                        @php
                                            $sampleCount = max(1, (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1));
                                            $unitPrice = (float) ($line['unit_price'] ?? 0);
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $line['parameter_label'] ?? 'Parameter' }}
                                                @if(!empty($line['is_package']))
                                                    <span class="badge badge-primary badge-pill ml-1">Package</span>
                                                    @if(!empty($line['package_element_metrics']) && is_array($line['package_element_metrics']))
                                                        <div class="small text-muted mt-1">
                                                            @foreach($line['package_element_metrics'] as $packageMetric)
                                                                @php
                                                                    $metricParts = array_values(array_filter([
                                                                        filled($packageMetric['loq'] ?? null) ? 'LOQ: '.$packageMetric['loq'] : null,
                                                                        filled($packageMetric['mu_percent'] ?? null) ? 'MU: '.$packageMetric['mu_percent'] : null,
                                                                    ]));
                                                                @endphp
                                                                <div>
                                                                    · {{ $packageMetric['label'] ?? 'Parameter' }}
                                                                    @if($metricParts !== [])
                                                                        — {{ implode(' · ', $metricParts) }}
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif(!empty($line['package_element_labels']) && is_array($line['package_element_labels']))
                                                        <div class="small text-muted mt-1">
                                                            @foreach($line['package_element_labels'] as $packageLabel)
                                                                <div>· {{ $packageLabel }}</div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $sampleCount }}</td>
                                            <td class="text-right">{{ number_format($unitPrice, 2) }}</td>
                                            <td class="text-right">{{ number_format($sampleCount * $unitPrice, 2) }}</td>
                                            <td class="text-right">{{ number_format((float) ($line['tax'] ?? 0), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Subtotal</th>
                                        <th class="text-right">{{ number_format($this->pricingTotals['sub_total'], 2) }}</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-right">Tax</th>
                                        <th class="text-right">{{ number_format($this->pricingTotals['tax'], 2) }}</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-right">Grand total</th>
                                        <th class="text-right">{{ number_format($this->pricingTotals['total'], 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="acc-wizard-footer">
                        <button type="button" class="btn btn-light" wire:click="closeBuildQuotationModal">Back to editing</button>
                        <button type="button" class="btn btn-primary" wire:click="confirmBuildQuotation" wire:loading.attr="disabled">
                            Confirm &amp; save quotation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showAddLineModal)
        <div class="acc-wizard-backdrop acc-wizard-backdrop--nested" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered acc-add-modal-dialog">
                <div class="modal-content acc-wizard-modal acc-add-modal">
                    <div class="acc-wizard-header acc-wizard-header--compact">
                        <h5 class="acc-wizard-title mb-0">Add quotation line</h5>
                        <button type="button" class="acc-wizard-close" wire:click="$set('showAddLineModal', false)">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="form-group">
                            <label class="acc-label">Sample type</label>
                            <select class="form-control acc-input" wire:model.live="addLineSampleTypeId">
                                <option value="">Select sample type...</option>
                                @foreach($addLineSampleTypes as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="acc-label">Analysis type</label>
                            <select class="form-control acc-input" wire:model.live="addLineAnalysisTypeId" @disabled(!$addLineSampleTypeId)>
                                <option value="">Select analysis type...</option>
                                @foreach($addLineAnalysisTypes as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="acc-label">Parameter</label>
                            @if($addLineAnalysisTypeId && $addLineAllParametersSelected)
                                <div class="alert alert-info py-2 mb-0 small">All parameters for this selection are already in the quotation.</div>
                            @else
                                <select class="form-control acc-input" wire:model="addLineParameterKey"
                                        @disabled(!$addLineAnalysisTypeId || ($addLineParameters === [] && !$addLineCanAddWholeAnalysisType))>
                                    @if($addLineCanAddWholeAnalysisType)
                                        <option value="">All parameters for this analysis type</option>
                                    @endif
                                    @foreach($addLineParameters as $param)
                                        <option value="{{ $param['id'] }}">{{ $param['label'] }} — {{ number_format($param['unit_amount'], 2) }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                    <div class="acc-wizard-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showAddLineModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="confirmAddQuotationLine"
                                @disabled($addLineAllParametersSelected || !$addLineAnalysisTypeId)>Add to quotation</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.partials.acc-wizard-core-styles')

    <style>
        .acc-wizard-root--enquiry {
            --acc-accent: var(--color-primary, var(--color-primary));
            --acc-accent-dark: var(--color-primary-hover, var(--color-primary-hover));
            --acc-accent-soft: var(--color-primary-soft, var(--color-primary-soft));
            --acc-text: #111827;
            --acc-muted: #6b7280;
        }

        .acc-wizard-root--enquiry .acc-wizard-section-title {
            color: #111827;
        }

        .acc-wizard-root--enquiry .acc-sample-config-table thead th {
            color: #6b7280;
        }

        .acc-wizard-root--enquiry .form-control,
        .acc-wizard-root--enquiry .acc-sample-config-param-chip,
        .acc-wizard-root--enquiry .acc-sample-config-card-title {
            color: #111827;
        }

        .acc-wizard-root--enquiry .acc-existing-quotation-select .tag-select-input {
            border: 1px solid #475569;
            box-shadow: none;
        }

        .acc-wizard-root--enquiry .acc-existing-quotation-select .tag-select-input:hover {
            border-color: #334155;
            box-shadow: none;
        }

        .acc-wizard-root--enquiry .acc-existing-quotation-select.is-open .tag-select-input,
        .acc-wizard-root--enquiry .acc-existing-quotation-select .tag-select-input:focus-within {
            border-color: transparent;
            box-shadow: none;
            outline: none;
        }

        .acc-wizard-root--enquiry .acc-existing-quotation-select .tag-dropdown-item {
            font-weight: 400;
        }

        .acc-wizard-root--enquiry .acc-existing-quotation-select .tag-dropdown-item.is-selected {
            background: #f8fafc;
        }

        .acc-wizard-root--enquiry .acc-pricing-table-wrap--scroll {
            overflow: auto;
            max-height: min(360px, 50vh);
        }

        .acc-wizard-root--enquiry .acc-pricing-table-wrap--scroll .acc-pricing-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
        }

        .acc-wizard-root--enquiry .acc-enquiry-rich-text-cell {
            min-width: 180px;
            max-width: 320px;
        }

        .acc-wizard-root--enquiry .acc-enquiry-rich-text {
            font-size: var(--ls-text-base, 0.8125rem);
            line-height: 1.45;
            word-break: break-word;
        }

        .acc-wizard-root--enquiry .acc-enquiry-rich-text p:last-child,
        .acc-wizard-root--enquiry .acc-enquiry-rich-text ul:last-child,
        .acc-wizard-root--enquiry .acc-enquiry-rich-text ol:last-child {
            margin-bottom: 0;
        }

        .acc-wizard-root--enquiry .acc-delivery-options .acc-label--compact {
            padding: 0.15rem 0;
            font-size: 0.875rem;
        }
    </style>
</div>

@script
<script>
    $wire.on('open-quotation-preview', ({ url }) => {
        if (url) {
            window.open(url, '_blank');
        }
    });
</script>
@endscript

