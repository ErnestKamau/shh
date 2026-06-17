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
                                wire:click="goToStep('{{ $meta['key'] }}')"
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

                                @if(count($collectionDataRows) > 0)
                                    <div class="mb-4">
                                        <h6 class="acc-wizard-section-title">Sample collection data</h6>
                                        <div class="row acc-wizard-fields small">
                                            @foreach($collectionDataRows as $row)
                                                <div class="col-md-6 mb-2">
                                                    <strong>{{ $row['label'] }}</strong><br>
                                                    <span>{{ $row['value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

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
                                                        <th>Tests / parameters</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($displaySampleRows as $index => $row)
                                                        <tr wire:key="enquiry-sample-{{ $index }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $row['sample_description'] }}</td>
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
                                        <h6 class="acc-wizard-section-title">Tests / parameters</h6>
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

                                <div class="form-group">
                                    <label class="acc-label">Staff commercial notes</label>
                                    <textarea class="form-control acc-input" rows="4" wire:model.defer="enquiryNotes"></textarea>
                                </div>
                            </section>

                            <div class="acc-wizard-footer">
                                <button type="button" class="btn btn-outline-secondary" wire:click="closeWizard">Cancel</button>
                                <button type="button" class="btn btn-primary" wire:click="saveReviewAndContinue">
                                    Proceed to sample configuration <i class="mdi mdi-arrow-right"></i>
                                </button>
                            </div>
                        @endif

                        @if($activeStep === 'sample_config')
                            @include('livewire.partials.acceptance-sample-config-table')

                            <div class="acc-wizard-footer">
                                <button type="button" class="btn btn-outline-secondary" wire:click="goToStep('review')">
                                    <i class="mdi mdi-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary" wire:click="saveSampleConfigAndContinue">
                                    Continue to pricing <i class="mdi mdi-arrow-right"></i>
                                </button>
                            </div>
                        @endif

                        @if($activeStep === 'pricing')
                            @if($statusMessage !== '')
                                <div class="alert alert-{{ $statusLevel === 'error' ? 'danger' : ($statusLevel === 'success' ? 'success' : 'info') }} py-2 mb-3">
                                    {{ $statusMessage }}
                                </div>
                            @endif

                            <section class="acc-wizard-section acc-pricing-section">
                                <div class="acc-pricing-toolbar mb-3">
                                    <h6 class="acc-wizard-section-title mb-0">Inline quotation</h6>
                                </div>

                                <div class="acc-pricing-table-wrap mb-3">
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
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-muted text-center py-4">No quotation lines could be prefilled.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="6" class="text-right">Subtotal</th>
                                                <th colspan="2" class="text-right">{{ number_format($this->pricingTotals['sub_total'], 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="6" class="text-right">Tax</th>
                                                <th colspan="2" class="text-right">{{ number_format($this->pricingTotals['tax'], 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="6" class="text-right">Grand total</th>
                                                <th colspan="2" class="text-right">{{ number_format($this->pricingTotals['total'], 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="d-flex flex-wrap mb-3" style="gap: 8px;">
                                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="generatePdf" wire:loading.attr="disabled">
                                        <i class="mdi mdi-file-pdf-box"></i> Generate PDF
                                    </button>
                                    @if($pdfGenerated && $quotationHeaderId)
                                        <a href="{{ route('quotation.preview', ['id' => $quotationHeaderId]) }}"
                                           class="btn btn-sm btn-outline-secondary" target="_blank">
                                            Preview quotation
                                        </a>
                                    @endif
                                    @if($quoteNumber !== '')
                                        <span class="align-self-center small text-muted">Ref: {{ $quoteNumber }}</span>
                                    @endif
                                </div>

                                <div class="row mb-3">
                                    @if(strtolower($sourceChannel) !== 'walk_in')
                                        <div class="col-md-4">
                                            <label class="acc-label d-flex align-items-center gap-2">
                                                <input type="checkbox" wire:model="sendPortal"> Send to portal
                                            </label>
                                        </div>
                                    @endif
                                    <div class="col-md-4">
                                        <label class="acc-label d-flex align-items-center gap-2">
                                            <input type="checkbox" wire:model="sendEmail"> Email PDF to customer
                                        </label>
                                    </div>
                                </div>
                            </section>

                            <div class="acc-wizard-footer">
                                <button type="button" class="btn btn-outline-secondary" wire:click="goToStep('sample_config')">
                                    <i class="mdi mdi-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-success" wire:click="sendQuotation" wire:loading.attr="disabled"
                                    @disabled($quotationSent)>
                                    <span wire:loading.remove wire:target="sendQuotation">
                                        <i class="mdi mdi-send"></i>
                                        {{ $quotationSent ? 'Quotation already sent' : 'Send to customer' }}
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

    @include('livewire.partials.acc-wizard-core-styles')
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
