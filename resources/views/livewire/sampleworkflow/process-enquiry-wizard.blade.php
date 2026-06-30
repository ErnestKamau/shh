<div class="acc-wizard-root acc-wizard-root--enquiry">
    @if($showModal)
        <div class="acc-wizard-backdrop" tabindex="-1" role="dialog" wire:click.self="closeWizard">
            <div class="modal-dialog modal-xl acc-wizard-dialog" role="document">
                <div class="modal-content acc-wizard-modal">
                    <div class="acc-wizard-header">
                        <div class="acc-wizard-header-text">
                            <span class="acc-wizard-eyebrow">Commercial / Phase 1</span>
                            <h4 class="acc-wizard-title">
                                <i class="mdi mdi-file-chart-outline"></i>
                                Process Enquiry
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

                    <div class="acc-wizard-body">
                        @if($activeStep === 'review')
                            <section class="acc-wizard-section">
                                @include('livewire.partials.process-enquiry-status-alert')

                                <h6 class="acc-wizard-section-title">Enquiry summary</h6>
                                <div class="row acc-wizard-fields mb-3">
                                    <div class="col-md-3"><strong>Status</strong><br>{{ $enquiryStatus }}</div>
                                    <div class="col-md-3"><strong>Origin</strong><br>{{ ucwords(str_replace('_', ' ', $sourceChannel ?: '—')) }}</div>
                                    <div class="col-md-3"><strong>Customer</strong><br>{{ $customerName }}</div>
                                    <div class="col-md-3"><strong>Reference</strong><br>{{ $requestReference }}</div>
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
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Sample description</th>
                                                        <th>Qty</th>
                                                        <th>Sample type</th>
                                                        <th>Sample condition</th>
                                                        <th>Test category</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($displaySampleRows as $index => $row)
                                                        <tr wire:key="enquiry-sample-{{ $index }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td class="acc-enquiry-rich-text-cell">
                                                                <div class="acc-enquiry-rich-text">{!! $row['sample_description_html'] ?? e($row['sample_description'] ?? '—') !!}</div>
                                                            </td>
                                                            <td>{{ $row['qty'] }}</td>
                                                            <td>{{ $row['sample_type'] }}</td>
                                                            <td>{{ $row['sample_condition'] }}</td>
                                                            <td>{{ $row['tests'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @elseif(count($requestedTests) > 0)
                                    <div class="mb-4">
                                        <h6 class="acc-wizard-section-title">Test category</h6>
                                        <ul class="mb-0 pl-3 small">
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

                            <div class="acc-wizard-footer">
                                <button type="button" class="btn btn-outline-secondary" wire:click="closeWizard">Cancel</button>
                                <button type="button" class="btn btn-primary" wire:click="saveReviewAndContinue">
                                    Proceed to sample configuration <i class="mdi mdi-arrow-right"></i>
                                </button>
                            </div>
                        @endif

                        @if($activeStep === 'sample_config')
                            @include('livewire.partials.process-enquiry-status-alert')

                            @include('livewire.partials.acceptance-sample-config-table')

                            <div class="acc-wizard-footer">
                                <button type="button" class="btn btn-outline-secondary" wire:click.stop.prevent="goToStep('review')">
                                    <i class="mdi mdi-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary" wire:click="saveSampleConfigAndContinue">
                                    Continue to pricing <i class="mdi mdi-arrow-right"></i>
                                </button>
                            </div>
                        @endif

                        @if($activeStep === 'pricing')
                            @include('livewire.partials.process-enquiry-status-alert')

                            <section class="acc-wizard-section acc-pricing-section">
                                <div class="acc-pricing-toolbar mb-3 d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
                                    <h6 class="acc-wizard-section-title mb-0">Inline quotation</h6>
                                    <div class="d-flex flex-wrap align-items-center acc-pricing-toolbar-actions" style="gap: 8px;">
                                        @if($this->currencyDisplay !== '')
                                            <span class="small text-muted">Currency: <strong>{{ $this->currencyDisplay }}</strong></span>
                                        @endif
                                        <span class="small text-muted">VAT regime: <strong>{{ number_format($this->taxRate, 2) }}%</strong></span>
                                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="generatePdf" wire:loading.attr="disabled">
                                            <i class="mdi mdi-file-pdf-box"></i> Generate PDF
                                        </button>
                                        @if($pdfGenerated && $quotationHeaderId)
                                            <a href="{{ route('quotation.preview', ['id' => $quotationHeaderId]) }}"
                                               class="btn btn-sm btn-outline-secondary" target="_blank">
                                                Preview quotation
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openAddQuotationLineModal">
                                            <i class="mdi mdi-plus"></i> Add line
                                        </button>
                                    </div>
                                </div>

                                <div class="acc-pricing-table-wrap acc-pricing-table-wrap--scroll mb-3">
                                    <table class="table acc-pricing-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Parameter</th>
                                                <th class="text-center">LOQ</th>
                                                <th class="text-center">MU%</th>
                                                <th class="text-right">Amount</th>
                                                <th>Samples</th>
                                                <th class="text-right">Tax %</th>
                                                <th class="text-center">Subcontract</th>
                                                <th class="text-center" style="width: 90px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($lines as $index => $line)
                                                <tr wire:key="ql-{{ $index }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $line['parameter_label'] ?? 'Parameter' }}</td>
                                                    <td class="text-center text-muted small">{{ $line['loq'] ?? '—' }}</td>
                                                    <td class="text-center text-muted small">{{ $line['mu_percent'] ?? '—' }}</td>
                                                    <td class="text-right">
                                                        <input type="number" min="0" step="0.01" class="form-control form-control-sm text-right"
                                                               wire:model.blur="lines.{{ $index }}.unit_price">
                                                    </td>
                                                    <td>
                                                        <input type="number" min="1" class="form-control form-control-sm"
                                                               wire:model.blur="lines.{{ $index }}.quantity">
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="number" min="0" step="0.01" class="form-control form-control-sm text-right"
                                                               wire:model.blur="lines.{{ $index }}.tax">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" wire:model.live="lines.{{ $index }}.subcontracted">
                                                    </td>
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
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-muted text-center py-4">No quotation lines could be prefilled.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="7" class="text-right">Subtotal</th>
                                                <th colspan="2" class="text-right">{{ number_format($this->pricingTotals['sub_total'], 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="7" class="text-right">Tax</th>
                                                <th colspan="2" class="text-right">{{ number_format($this->pricingTotals['tax'], 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="7" class="text-right">Grand total @if($this->currencyDisplay !== '') ({{ $this->currencyDisplay }}) @endif</th>
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

                            <div class="acc-wizard-footer">
                                <button type="button" class="btn btn-outline-secondary" wire:click.stop.prevent="goToStep('sample_config')">
                                    <i class="mdi mdi-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary" wire:click="sendQuotation" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="sendQuotation">
                                        <i class="mdi mdi-send"></i>
                                        {{ $quotationSent ? 'Send again' : 'Send to customer' }}
                                    </span>
                                    <span wire:loading wire:target="sendQuotation">Generating & sending…</span>
                                </button>
                            </div>
                        @endif
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
            --acc-accent: var(--color-primary, #6D0A0E);
            --acc-accent-dark: var(--color-primary-hover, #8B1E22);
            --acc-accent-soft: var(--color-primary-soft, rgba(109, 10, 14, 0.08));
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
            font-size: 0.875rem;
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
    $wire.on('show-process-enquiry-modal', () => {
        document.body.classList.add('modal-open');
    });
    $wire.on('hide-process-enquiry-modal', () => {
        document.body.classList.remove('modal-open');
    });
</script>
@endscript
