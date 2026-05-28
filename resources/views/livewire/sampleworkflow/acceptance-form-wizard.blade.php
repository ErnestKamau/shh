<div class="acc-wizard-root acc-wizard-root--acceptance">
    @if($showModal)
        <div class="acc-wizard-backdrop" tabindex="-1" role="dialog">
            <div class="modal-dialog {{ $activeStep === 'sample_config' ? 'modal-xl' : 'modal-lg' }} acc-wizard-dialog" role="document">
                <div class="modal-content acc-wizard-modal">
                    <div class="acc-wizard-header">
                        <div class="acc-wizard-header-text">
                            <span class="acc-wizard-eyebrow">GCLA / F/03</span>
                            <h4 class="acc-wizard-title">
                                <i class="mdi mdi-file-document-edit-outline"></i>
                                Analysis Acceptance Form
                            </h4>
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
                        @if($activeStep === 'sample_config')
                            @include('livewire.partials.acceptance-sample-config-table')
                        @endif

                        @if($activeStep === 'request')
                            @if($showRaiseDisclaimerOption)
                                <div class="acc-disclaimer-alert" role="alert">
                                    <div class="acc-disclaimer-alert-head">
                                        <i class="mdi mdi-alert-outline"></i>
                                        <strong>Sample integrity checklist incomplete</strong>
                                    </div>
                                    <p class="mb-2">One or more mandatory receiving checklist items were not marked as done:</p>
                                    <ul class="acc-disclaimer-alert-list mb-3">
                                        @foreach($incompleteChecklistItems as $item)
                                            <li>{{ $item['label'] }}</li>
                                        @endforeach
                                    </ul>
                                    <label class="acc-disclaimer-check mb-0">
                                        <input type="checkbox" wire:model.live="raiseSampleDisclaimer">
                                        <span class="acc-disclaimer-check-ui"></span>
                                        <span class="acc-disclaimer-check-label">Raise sample disclaimer form</span>
                                    </label>
                                </div>
                            @endif

                            <section class="acc-wizard-section">
                                <h6 class="acc-wizard-section-title">Request details</h6>
                                <div class="row acc-wizard-fields">
                                    <div class="col-md-6 form-group">
                                        <label class="acc-label">Customer name</label>
                                        <input type="text" class="form-control acc-input" wire:model="customerName">
                                        @error('customerName') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="acc-label">Request date</label>
                                        <input type="date" class="form-control acc-input" wire:model="requestDate">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="acc-label">Number of samples</label>
                                        <input type="number" min="1" class="form-control acc-input" wire:model="numberOfSamples">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="acc-label">Mode of work</label>
                                        <select class="form-control acc-input" wire:model="modeOfWork">
                                            <option value="Normal">Normal</option>
                                            <option value="Express">Express</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="acc-label">Date of sampling</label>
                                        <input type="date" class="form-control acc-input" wire:model="dateOfSampling">
                                    </div>
                                </div>
                            </section>

                            <section class="acc-wizard-section acc-pricing-section">
                                <div class="acc-pricing-toolbar">
                                    <div>
                                        <h6 class="acc-wizard-section-title mb-1">Parameters &amp; pricing</h6>
                                        <p class="acc-wizard-hint mb-0">Review pricing from your sample configuration. Toggle approval per line or go back to edit configuration.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="backFromRequestStep">
                                        <i class="mdi mdi-arrow-left"></i> Edit configuration
                                    </button>
                                </div>

                                <div class="acc-pricing-table-wrap">
                                    <table class="table acc-pricing-table mb-0">
                                        <thead>
                                            <tr>
                                                <th class="col-no">No</th>
                                                <th class="col-param">Parameter</th>
                                                <th class="col-amount text-right">Amount</th>
                                                <th class="col-samples">Samples</th>
                                                <th class="col-approve text-center">Approve</th>
                                                <th class="col-action"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($this->groupedLines as $sampleGroup)
                                                <tr class="acc-row-sample-type" wire:key="st-{{ $sampleGroup['sample_type_id'] ?? 'x' }}-{{ $loop->index }}">
                                                    <td colspan="6">
                                                        <i class="mdi mdi-flask-outline"></i>
                                                        {{ $sampleGroup['sample_type_name'] }}
                                                    </td>
                                                </tr>
                                                @foreach($sampleGroup['analysis_groups'] as $analysisGroup)
                                                    @php
                                                        $headerIndex = $analysisGroup['header_line_index'] ?? null;
                                                        $headerLine = $headerIndex !== null ? ($this->lines[$headerIndex] ?? null) : null;
                                                    @endphp
                                                    <tr class="acc-row-analysis-type {{ $headerLine ? 'acc-row-analysis-type--with-controls' : '' }}" wire:key="at-{{ $analysisGroup['analysis_type_id'] ?? 'x' }}-{{ $loop->parent->index }}-{{ $loop->index }}">
                                                        <td colspan="{{ $headerLine ? 1 : 6 }}" class="acc-analysis-type-label">
                                                            <i class="mdi mdi-chart-timeline-variant"></i>
                                                            {{ $analysisGroup['analysis_type_name'] }}
                                                        </td>
                                                        @if($headerLine)
                                                            <td colspan="2"></td>
                                                            <td class="col-amount text-right">
                                                                <span class="acc-amount acc-amount--on-header">{{ number_format((float) ($headerLine['unit_amount'] ?? 0), 2) }}</span>
                                                            </td>
                                                            <td class="col-samples">
                                                                <input type="number" min="1" class="form-control form-control-sm acc-input-sm" wire:model.live="lines.{{ $headerIndex }}.number_of_samples">
                                                            </td>
                                                            <td class="col-approve text-center">
                                                                <label class="acc-check-wrap mb-0">
                                                                    <input type="checkbox" wire:model.live="lines.{{ $headerIndex }}.is_approved">
                                                                    <span class="acc-check-ui"></span>
                                                                </label>
                                                            </td>
                                                            <td class="col-action text-center">
                                                                <button type="button" class="btn btn-sm acc-btn-remove" wire:click="removeLine({{ $headerIndex }})" title="Remove">
                                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                                </button>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                    @foreach($analysisGroup['items'] as $item)
                                                        @php $index = $item['index']; $line = $item['line']; @endphp
                                                        <tr class="acc-row-parameter" wire:key="line-{{ $index }}">
                                                            <td class="col-no text-muted">{{ $line['line_no'] ?? $index + 1 }}</td>
                                                            <td class="col-param">
                                                                <span class="acc-param-name">{{ $line['parameter_label'] ?? 'Parameter' }}</span>
                                                            </td>
                                                            <td class="col-amount text-right">
                                                                <span class="acc-amount">{{ number_format((float) ($line['unit_amount'] ?? 0), 2) }}</span>
                                                            </td>
                                                            <td class="col-samples">
                                                                <input type="number" min="1" class="form-control form-control-sm acc-input-sm" wire:model.live="lines.{{ $index }}.number_of_samples">
                                                            </td>
                                                            <td class="col-approve text-center">
                                                                <label class="acc-check-wrap mb-0">
                                                                    <input type="checkbox" wire:model.live="lines.{{ $index }}.is_approved">
                                                                    <span class="acc-check-ui"></span>
                                                                </label>
                                                            </td>
                                                            <td class="col-action text-center">
                                                                <button type="button" class="btn btn-sm acc-btn-remove" wire:click="removeLine({{ $index }})" title="Remove">
                                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center acc-empty">No parameters loaded for this request.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="acc-row-total">
                                                <td colspan="2" class="text-right">Total (approved lines)</td>
                                                <td class="text-right acc-total-amount">{{ number_format($this->totalAmount, 2) }}</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </section>
                        @endif

                        @if($activeStep === 'disclaimer')
                            <section class="acc-wizard-section">
                                <h6 class="acc-wizard-section-title">Sample receiving disclaimer</h6>
                                <p class="acc-wizard-hint mb-3">Complete the disclaimer when sample integrity criteria were not met at receiving.</p>
                                @include('livewire.partials.sample-disclaimer-wire-fields', [
                                    'wirePrefix' => 'disclaimerForm.',
                                    'canvasPrefix' => 'acc-wizard-disclaimer',
                                    'readOnly' => false,
                                ])
                            </section>
                        @endif

                        @if($activeStep === 'customer')
                            <section
                                class="acc-wizard-section"
                                @if($status === 'awaiting_customer_sign') wire:poll.10s="refreshAcceptanceStatus" @endif
                            >
                                <div class="acc-cert-card">
                                    <p class="acc-cert-quote">{{ \App\Services\Sampleworkflow\AcceptanceFormService::CUSTOMER_CERTIFICATION_TEXT }}</p>
                                    @if($status === 'awaiting_customer_sign')
                                        <div class="acc-status-banner acc-status-banner--info">
                                            <i class="mdi mdi-email-send-outline"></i>
                                            <div>
                                                <strong>Awaiting customer signature</strong>
                                                <span>A notification was sent. The customer signs in the customer portal.</span>
                                            </div>
                                        </div>
                                    @elseif(in_array($status, ['awaiting_lab_manager_sign', 'completed'], true))
                                        @php $signedForm = $acceptanceFormId ? \App\Models\Sampleworkflow\AnalysisAcceptanceForm::find($acceptanceFormId) : null; @endphp
                                        <div class="acc-status-banner acc-status-banner--success">
                                            <i class="mdi mdi-check-decagram"></i>
                                            <div>
                                                <strong>Customer signed</strong>
                                                @if($signedForm?->customer_signer_name)
                                                    <span>{{ $signedForm->customer_signer_name }} · {{ optional($signedForm->customer_signed_at)->format('d M Y, H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        @endif

                        @if($activeStep === 'manager')
                            @include('livewire.partials.manager-assignment-fields', [
                                'analystOptions' => $analystOptions,
                                'signatoryOptions' => $signatoryOptions,
                                'assignedAnalystIds' => $assignedAnalystIds,
                                'leadAnalystOptions' => $this->leadAnalystOptions,
                            ])

                            <section class="acc-wizard-section">
                                <h6 class="acc-wizard-section-title">Laboratory manager — approval &amp; signature <span class="text-danger">*</span></h6>
                                <div class="acc-cert-card">
                                    <p class="acc-cert-quote">{{ \App\Services\Sampleworkflow\AcceptanceFormService::MANAGER_CERTIFICATION_TEXT }}</p>
                                </div>
                                <div class="row acc-wizard-fields">
                                    <div class="col-md-6 form-group">
                                        <label class="acc-label">Manager name</label>
                                        <input type="text" class="form-control acc-input" wire:model="managerSignerName">
                                        @error('managerSignerName') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="acc-label">Date</label>
                                        <input type="date" class="form-control acc-input" wire:model="managerSignedAt">
                                    </div>
                                </div>
                                <label class="acc-label d-block mt-2">Manager signature</label>
                                <div class="acc-signature-pad" wire:ignore>
                                    <canvas id="acceptance-manager-signature-canvas"></canvas>
                                    <div class="acc-signature-actions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="acceptance-manager-sign-clear">Clear</button>
                                    </div>
                                </div>
                                <input type="hidden" id="acceptance-manager-signature-input" wire:model="managerSignature">
                                @error('managerSignature') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </section>
                        @endif

                        @if($activeStep === 'receipt')
                            <section class="acc-wizard-section">
                                <div class="acc-pricing-toolbar mb-3">
                                    <div>
                                        <h6 class="acc-wizard-section-title mb-1">Sample Receipt Notification (GCLA 01)</h6>
                                        <p class="acc-wizard-hint mb-0">Pre-filled from the selected request. Receiving person is the currently signed-in user; the lab batch number is assigned after the customer signs.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="saveReceiptNotificationDraft">
                                        Save receipt draft
                                    </button>
                                </div>
                                @include('livewire.partials.receipt-notification-wire-fields', [
                                    'wirePrefix' => 'receiptNotificationForm.',
                                    'canvasPrefix' => 'acc-wizard-receipt',
                                    'readOnly' => false,
                                    'partLabel' => null,
                                    'showSubmitterSection' => false,
                                    'showSubmitterSigningNotice' => true,
                                    'showLabNumberPendingNote' => true,
                                    'labNumberPlaceholder' => 'Request no. — lab batch no. after customer signs',
                                ])
                            </section>
                        @endif
                    </div>

                    <div class="acc-wizard-footer">
                        <button type="button" class="btn btn-light acc-btn-ghost" wire:click="closeWizard">Close</button>
                        @if($activeStep === 'sample_config')
                            <button type="button" class="btn acc-btn-primary" wire:click="continueToRequestStep" wire:loading.attr="disabled">
                                <span wire:loading wire:target="continueToRequestStep" class="spinner-border spinner-border-sm mr-1"></span>
                                Continue
                            </button>
                        @elseif($activeStep === 'request')
                            <button type="button" class="btn btn-light" wire:click="backFromRequestStep">Back</button>
                            <button type="button" class="btn acc-btn-primary" wire:click="continueToReceiptStep" wire:loading.attr="disabled">
                                <span wire:loading wire:target="continueToReceiptStep" class="spinner-border spinner-border-sm mr-1"></span>
                                Continue
                            </button>
                        @elseif($activeStep === 'receipt' && !$acceptanceFormId)
                            <button type="button" class="btn btn-light" wire:click="backFromReceiptStep">Back</button>
                            <button type="button" class="btn acc-btn-primary" wire:click="submitStep1" wire:loading.attr="disabled">
                                <span wire:loading wire:target="submitStep1" class="spinner-border spinner-border-sm mr-1"></span>
                                @if($raiseSampleDisclaimer)
                                    Continue
                                @else
                                    Save &amp; send to customer
                                @endif
                            </button>
                        @elseif($activeStep === 'disclaimer' && !$acceptanceFormId)
                            <button type="button" class="btn btn-light" wire:click="backFromDisclaimerStep">Back</button>
                            <button type="button" class="btn acc-btn-primary" wire:click="submitDisclaimerStep" wire:loading.attr="disabled" id="acceptance-disclaimer-submit">
                                <span wire:loading wire:target="submitDisclaimerStep" class="spinner-border spinner-border-sm mr-1"></span>
                                Save &amp; send to customer
                            </button>
                        @elseif($activeStep === 'manager' && $status === 'awaiting_lab_manager_sign')
                            <button type="button" class="btn acc-btn-success" id="acceptance-manager-sign-submit" wire:loading.attr="disabled">
                                Complete acceptance
                            </button>
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
                        <h5 class="acc-wizard-title mb-0">Add parameter</h5>
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
                            <label class="acc-label">Parameter (from pricelist)</label>
                            @if($addLineAnalysisTypeId && $addLineAllParametersSelected)
                                <div class="acc-add-line-alert" role="status">
                                    <i class="mdi mdi-check-all"></i>
                                    <span>All parameters for this sample type and analysis type are already in the list below.</span>
                                </div>
                            @else
                                <select
                                    class="form-control acc-input"
                                    wire:model="addLineParameterKey"
                                    @disabled(!$addLineAnalysisTypeId || ($addLineParameters === [] && !$addLineCanAddWholeAnalysisType))
                                >
                                    @if($addLineCanAddWholeAnalysisType)
                                        <option value="">All parameters for this analysis type</option>
                                    @endif
                                    @foreach($addLineParameters as $param)
                                        <option value="{{ $param['id'] }}">{{ $param['label'] }} — {{ number_format($param['unit_amount'], 2) }}</option>
                                    @endforeach
                                </select>
                                @if($addLineAnalysisTypeId && !$addLineCanAddWholeAnalysisType && $addLineParameters === [])
                                    <p class="acc-add-line-hint mb-0 mt-2">No additional pricelist parameters are available for this selection.</p>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="acc-wizard-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showAddLineModal', false)">Cancel</button>
                        <button
                            type="button"
                            class="btn acc-btn-primary"
                            wire:click="confirmAddLine"
                            @disabled($addLineAllParametersSelected || !$addLineAnalysisTypeId)
                        >Add to list</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .acc-wizard-root {
            --acc-accent: #3b5fc0;
            --acc-accent-dark: #2f4da0;
            --acc-accent-soft: #eef2ff;
            --acc-border: #e2e8f0;
            --acc-muted: #64748b;
            --acc-text: #0f172a;
            --acc-sample-bg: #1e3a5f;
            --acc-analysis-bg: #334155;
            --acc-param-bg: #ffffff;
        }

        .acc-wizard-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.52);
            backdrop-filter: blur(4px);
        }

        .acc-wizard-backdrop--nested { z-index: 1060; }

        .acc-add-modal-dialog {
            width: 540px;
            max-width: calc(100vw - 2rem);
            min-width: min(540px, calc(100vw - 2rem));
            margin: 0 auto;
        }

        .acc-add-modal .modal-body {
            min-height: 280px;
        }

        .acc-wizard-dialog { max-width: 920px; margin: 0; }

        .acc-wizard-dialog.modal-xl {
            max-width: 1140px;
        }

        .acc-sample-config-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .acc-sample-config-card {
            border: 1px solid var(--acc-border);
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .acc-sample-config-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
            border-bottom: 1px solid var(--acc-border);
        }

        .acc-sample-config-card-title {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--acc-muted);
        }

        .acc-sample-config-table-wrap {
            overflow-x: auto;
        }

        .acc-sample-config-table thead th {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--acc-muted);
            background: #f8fafc;
            border-bottom: 1px solid var(--acc-border);
            white-space: nowrap;
            padding: 0.65rem 0.75rem;
        }

        .acc-sample-config-table td {
            vertical-align: middle;
            padding: 0.65rem 0.75rem;
            border-top: 1px solid #f1f5f9;
        }

        .acc-sample-config-main-row td {
            background: #fff;
        }

        .acc-sample-config-params-row td,
        .acc-sample-config-section-row td {
            background: #f8fafc;
            padding-top: 0;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .acc-sample-config-section-row td {
            padding-bottom: 0.5rem;
        }

        .acc-sample-config-params-panel {
            padding: 0.5rem 0.35rem 0.35rem;
        }

        .acc-sample-config-params-band {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: #e8edf3;
            border-radius: 8px;
            border: 1px solid #dde4ec;
        }

        .acc-sample-config-params-band-actions {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .acc-sample-config-select-all {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            white-space: nowrap;
        }

        .acc-sample-config-select-all:hover:not(:disabled) {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #334155;
        }

        .acc-sample-config-select-all:disabled {
            opacity: 0.55;
        }

        .acc-sample-config-params-search-row {
            margin: 0.5rem 0 0.35rem;
        }

        .acc-sample-config-section-toggle {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0;
            padding: 0;
            border: none;
            background: transparent;
            text-align: left;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.15s;
        }

        .acc-sample-config-section-toggle:hover,
        .acc-sample-config-section-toggle:focus {
            outline: none;
            background: rgba(148, 163, 184, 0.15);
        }

        .acc-sample-config-section-toggle-main {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .acc-sample-config-chevron {
            font-size: 1.1rem;
            color: var(--acc-muted);
            line-height: 1;
        }

        .acc-sample-config-section-badge {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: none;
            color: #1e40af;
            background: #dbeafe;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
        }

        .acc-sample-config-section-body {
            padding: 0.5rem 0.35rem 0.15rem;
        }

        .acc-sample-config-params-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--acc-muted);
        }

        .acc-sample-config-search {
            max-width: 220px;
        }

        .acc-sample-config-param-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 0.15rem;
        }

        .acc-sample-config-param-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            border: 1px solid var(--acc-border);
            background: #fff;
            font-size: 0.8rem;
            cursor: pointer;
            margin: 0;
            transition: border-color 0.15s, background 0.15s;
        }

        .acc-sample-config-param-chip.is-selected {
            border-color: #93c5fd;
            background: var(--acc-accent-soft);
            color: #1e3a8a;
        }

        .acc-sample-config-param-chip input {
            margin: 0;
        }

        .acc-sample-config-instances-panel {
            margin-top: 0.35rem;
        }

        .acc-sample-config-instances-band {
            background: #eef2ff;
            border-color: #c7d2fe;
        }

        .acc-sample-config-instances-band .acc-sample-config-section-toggle:hover,
        .acc-sample-config-instances-band .acc-sample-config-section-toggle:focus {
            background: rgba(99, 102, 241, 0.12);
        }

        .acc-sample-config-instances-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #3730a3;
        }

        .acc-sample-config-instances-badge {
            background: #c7d2fe;
            color: #312e81;
        }

        .acc-sample-config-instances-body {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .acc-sample-config-instance-item {
            display: grid;
            grid-template-columns: 100px 1fr 1fr;
            gap: 0.75rem;
            align-items: end;
            padding: 0.5rem 0.35rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .acc-sample-config-instance-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            padding-bottom: 0.35rem;
        }

        .acc-sample-config-instance-fields {
            display: contents;
        }

        @media (max-width: 768px) {
            .acc-sample-config-instance-item {
                grid-template-columns: 1fr;
            }
        }

        .acc-wizard-modal {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
        }

        .acc-wizard-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background: #fff;
            color: var(--acc-text);
            border-bottom: 1px solid var(--acc-border);
        }

        .acc-wizard-header .acc-wizard-eyebrow {
            color: var(--acc-muted);
            opacity: 1;
        }

        .acc-wizard-header--compact {
            background: #fff;
            color: var(--acc-text);
            border-bottom: 1px solid var(--acc-border);
            padding: 1rem 1.25rem;
        }

        .acc-wizard-eyebrow {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.75;
            margin-bottom: 0.15rem;
        }

        .acc-wizard-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .acc-wizard-close {
            border: none;
            background: #f1f5f9;
            color: var(--acc-muted);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .acc-wizard-close:hover {
            background: #e2e8f0;
            color: var(--acc-text);
        }

        .acc-wizard-header--compact .acc-wizard-close {
            background: #f1f5f9;
            color: var(--acc-muted);
        }

        .acc-wizard-root--acceptance .acc-wizard-header,
        .acc-wizard-root--acceptance .acc-wizard-header--compact {
            background: #fff !important;
            color: var(--acc-text) !important;
        }

        .acc-wizard-root--acceptance .acc-wizard-header .acc-wizard-close,
        .acc-wizard-root--acceptance .acc-wizard-header--compact .acc-wizard-close {
            background: #f1f5f9 !important;
            color: var(--acc-muted) !important;
        }

        .acc-wizard-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 0;
            padding: 0;
            background: #f8fafc;
            border-bottom: 1px solid var(--acc-border);
        }

        .acc-wizard-step {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 0.5rem;
            border: none;
            background: transparent;
            color: var(--acc-muted);
            font-size: 0.8rem;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: color 0.15s, border-color 0.15s, background 0.15s;
        }

        .acc-wizard-step:hover:not(:disabled) {
            color: var(--acc-accent);
            background: rgba(59, 95, 192, 0.06);
        }

        .acc-wizard-step.is-active {
            color: var(--acc-accent);
            border-bottom-color: var(--acc-accent);
            background: #fff;
        }

        .acc-wizard-step.is-done { color: #059669; }

        .acc-wizard-step:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .acc-wizard-step-index {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            background: #e2e8f0;
            color: var(--acc-muted);
        }

        .acc-wizard-step.is-active .acc-wizard-step-index {
            background: var(--acc-accent);
            color: #fff;
        }

        .acc-wizard-step.is-done .acc-wizard-step-index {
            background: #d1fae5;
            color: #059669;
        }

        .acc-wizard-body {
            padding: 1.25rem 1.5rem 1rem;
            background: #f8fafc;
            max-height: min(70vh, 640px);
            overflow-y: auto;
        }

        .acc-wizard-section {
            background: #fff;
            border: 1px solid var(--acc-border);
            border-radius: 12px;
            padding: 1.15rem 1.25rem;
            margin-bottom: 1rem;
        }

        .acc-wizard-section:last-child { margin-bottom: 0; }

        .acc-wizard-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--acc-muted);
            margin-bottom: 0.85rem;
        }

        .acc-wizard-hint { font-size: 0.8rem; color: var(--acc-muted); }

        .acc-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--acc-muted);
            margin-bottom: 0.35rem;
        }

        .acc-input {
            border-radius: 8px;
            border-color: var(--acc-border);
            font-size: 0.9rem;
        }

        .acc-input:focus {
            border-color: var(--acc-accent);
            box-shadow: 0 0 0 3px rgba(59, 95, 192, 0.15);
        }

        .acc-input-sm {
            max-width: 72px;
            margin: 0 auto;
            text-align: center;
        }

        .acc-pricing-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.85rem;
        }

        .acc-btn-add {
            background: var(--acc-accent-soft);
            color: var(--acc-accent);
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            font-weight: 600;
            white-space: nowrap;
        }

        .acc-btn-add:hover {
            background: var(--acc-accent);
            color: #fff;
            border-color: var(--acc-accent);
        }

        .acc-pricing-table-wrap {
            border: 1px solid var(--acc-border);
            border-radius: 10px;
            overflow: auto;
            max-height: 360px;
        }

        .acc-pricing-table {
            font-size: 0.875rem;
            margin: 0;
        }

        .acc-pricing-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f1f5f9;
            color: var(--acc-muted);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--acc-border);
            padding: 0.6rem 0.75rem;
            white-space: nowrap;
        }

        .acc-pricing-table .col-no { width: 48px; }
        .acc-pricing-table .col-amount { width: 100px; }
        .acc-pricing-table .col-samples { width: 88px; }
        .acc-pricing-table .col-approve { width: 72px; }
        .acc-pricing-table .col-action { width: 48px; }

        .acc-row-sample-type td {
            background: linear-gradient(90deg, #1e3a5f 0%, #2a4d73 100%);
            color: #fff;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            padding: 0.55rem 1rem;
            border: none;
        }

        .acc-row-sample-type td i {
            margin-right: 0.4rem;
            opacity: 0.9;
        }

        .acc-row-analysis-type td {
            background: #475569;
            color: #f8fafc;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 0.45rem 1rem 0.45rem 1.75rem;
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .acc-row-analysis-type td i {
            margin-right: 0.35rem;
            font-size: 1rem;
            vertical-align: -2px;
            opacity: 0.85;
        }

        .acc-row-analysis-type--with-controls td {
            vertical-align: middle;
        }

        .acc-row-analysis-type--with-controls .acc-analysis-type-label {
            padding-left: 1.75rem;
        }

        .acc-amount--on-header {
            color: #f8fafc;
            font-weight: 600;
        }

        .acc-row-analysis-type--with-controls .acc-input-sm {
            background: #fff;
        }

        .acc-row-analysis-type--with-controls .acc-check-ui {
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.15);
        }

        .acc-row-analysis-type--with-controls .acc-check-wrap input:checked + .acc-check-ui {
            background: #fff;
            border-color: #fff;
        }

        .acc-row-analysis-type--with-controls .acc-check-wrap input:checked + .acc-check-ui::after {
            border-color: #475569;
        }

        .acc-row-analysis-type--with-controls .acc-btn-remove {
            color: #fecaca;
        }

        .acc-row-analysis-type--with-controls .acc-btn-remove:hover {
            background: rgba(254, 202, 202, 0.15);
        }

        .acc-row-parameter td {
            background: var(--acc-param-bg);
            vertical-align: middle;
            padding: 0.5rem 0.75rem;
            border-top: 1px solid #f1f5f9;
        }

        .acc-row-parameter .col-param {
            padding-left: 2.25rem;
        }

        .acc-param-name {
            font-weight: 500;
            color: var(--acc-text);
        }

        .acc-amount {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            color: var(--acc-text);
        }

        .acc-row-total td {
            background: #f1f5f9;
            font-weight: 700;
            border-top: 2px solid var(--acc-border);
            padding: 0.65rem 0.75rem;
        }

        .acc-total-amount {
            font-size: 1rem;
            color: var(--acc-accent-dark);
        }

        .acc-empty {
            padding: 2rem !important;
            color: var(--acc-muted);
        }

        .acc-btn-remove {
            color: #dc2626;
            border: none;
            background: transparent;
            padding: 0.15rem 0.35rem;
            line-height: 1;
        }

        .acc-btn-remove:hover { background: #fef2f2; border-radius: 6px; }

        .acc-check-wrap {
            display: inline-flex;
            cursor: pointer;
        }

        .acc-check-wrap input { position: absolute; opacity: 0; width: 0; height: 0; }

        .acc-check-ui {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            display: inline-block;
            position: relative;
            transition: all 0.15s;
        }

        .acc-check-wrap input:checked + .acc-check-ui {
            background: var(--acc-accent);
            border-color: var(--acc-accent);
        }

        .acc-check-wrap input:checked + .acc-check-ui::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .acc-cert-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem 1.15rem;
            border: 1px solid var(--acc-border);
        }

        .acc-cert-quote {
            font-size: 0.95rem;
            color: var(--acc-text);
            font-style: italic;
            margin-bottom: 1rem;
        }

        .acc-manager-assignments-section {
            background: #f8fafc;
            border: 1px solid var(--acc-border);
            border-radius: 12px;
            padding: 1rem 1.1rem;
        }

        .acc-wizard-root .tag-select-container {
            position: relative;
            width: 100%;
            cursor: text;
        }

        .acc-wizard-root .tag-select-container--disabled {
            opacity: 0.65;
            pointer-events: none;
        }

        .acc-wizard-root .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 6px 12px;
            background: #fff;
            border: 1px solid var(--acc-border);
            border-radius: 10px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .acc-wizard-root .tag-select-input:focus-within {
            border-color: var(--acc-accent);
            box-shadow: 0 0 0 3px rgba(59, 95, 192, 0.12);
        }

        .acc-wizard-root .tag-input {
            flex: 1;
            min-width: 140px;
            border: none;
            outline: none;
            padding: 4px 0;
            font-size: 0.9rem;
            background: transparent;
        }

        .acc-wizard-root .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .acc-wizard-root .tag-badge--success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .acc-wizard-root .tag-badge--primary {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .acc-wizard-root .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.75;
        }

        .acc-wizard-root .tag-badge i:hover {
            opacity: 1;
        }

        .acc-wizard-root .tag-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 1200;
            background: #fff;
            border: 1px solid var(--acc-border);
            border-radius: 10px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1);
            max-height: 220px;
            overflow-y: auto;
        }

        .acc-wizard-root .tag-dropdown-item {
            padding: 10px 14px;
            cursor: pointer;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .acc-wizard-root .tag-dropdown-item:last-child {
            border-bottom: none;
        }

        .acc-wizard-root .tag-dropdown-item:hover {
            background: #f8fafc;
        }

        .acc-wizard-root .tag-select-container.is-invalid .tag-select-input {
            border-color: #dc3545;
        }

        .acc-status-banner {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            border-radius: 10px;
            font-size: 0.875rem;
        }

        .acc-status-banner i { font-size: 1.35rem; flex-shrink: 0; }

        .acc-status-banner strong { display: block; margin-bottom: 0.15rem; }

        .acc-status-banner span { color: var(--acc-muted); }

        .acc-status-banner--info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .acc-status-banner--success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }

        .acc-signature-pad {
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 0.5rem;
        }

        .acc-signature-pad canvas {
            width: 100%;
            height: 160px;
            display: block;
            border-radius: 6px;
        }

        .acc-wizard-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            background: #fff;
            border-top: 1px solid var(--acc-border);
        }

        .acc-btn-primary {
            background: var(--acc-accent);
            border-color: var(--acc-accent);
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.45rem 1.1rem;
        }

        .acc-btn-primary:hover {
            background: var(--acc-accent-dark);
            border-color: var(--acc-accent-dark);
        }

        .acc-btn-success {
            background: #059669;
            border-color: #059669;
            font-weight: 600;
            border-radius: 8px;
        }

        .acc-btn-ghost { border-radius: 8px; }

        .acc-add-line-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            padding: 0.75rem 0.85rem;
            border-radius: 8px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .acc-add-line-alert i {
            font-size: 1.15rem;
            flex-shrink: 0;
            margin-top: 0.05rem;
        }

        .acc-disclaimer-alert {
            margin-bottom: 1.25rem;
            padding: 1rem 1.1rem;
            border-radius: 10px;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
            font-size: 0.875rem;
        }

        .acc-disclaimer-alert-head {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.35rem;
        }

        .acc-disclaimer-alert-head i { font-size: 1.25rem; }

        .acc-disclaimer-alert-list {
            margin: 0;
            padding-left: 1.25rem;
        }

        .acc-disclaimer-check {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            cursor: pointer;
            font-weight: 600;
        }

        .acc-disclaimer-check input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .acc-disclaimer-check-ui {
            width: 18px;
            height: 18px;
            border: 2px solid #d97706;
            border-radius: 4px;
            flex-shrink: 0;
            margin-top: 2px;
            background: #fff;
        }

        .acc-disclaimer-check input:checked + .acc-disclaimer-check-ui {
            background: #d97706;
            box-shadow: inset 0 0 0 3px #fff;
        }

        .acc-disclaimer-inline-name {
            width: 12rem;
            max-width: 100%;
            vertical-align: baseline;
            display: inline-block;
            margin: 0 0.2rem;
        }

        .acc-disclaimer-legal-text {
            text-align: justify;
            line-height: 1.55;
            margin: 0;
            color: var(--acc-text);
        }

        .acc-disclaimer-footer-note {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-top: 1rem;
            padding: 0.75rem 0.9rem;
            border-radius: 8px;
            background: #f1f5f9;
            color: var(--acc-muted);
            font-size: 0.8rem;
        }

        .acc-add-line-hint {
            font-size: 0.8rem;
            color: var(--acc-muted);
        }

        @media (max-width: 576px) {
            .acc-wizard-step-label { display: none; }
            .acc-pricing-toolbar { flex-direction: column; }
        }
    </style>
</div>

@push('scripts')
<script>
    (function () {
        let managerSignaturePad = null;
        let wizardReceiptSubmitterPad = null;
        let wizardReceiptReceiverPad = null;
        let disclaimerClaimantPad = null;
        let disclaimerAnalystPad = null;

        function initWizardReceiptPads() {
            function setup(canvasId, inputId, clearBtnId, existingVal) {
                const canvas = document.getElementById(canvasId);
                const input = document.getElementById(inputId);
                const clearBtn = document.getElementById(clearBtnId);
                if (!canvas || !input || typeof SignaturePad === 'undefined') {
                    return null;
                }
                if (canvas.dataset.signatureReadyWizard === '1') {
                    return null;
                }
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
                canvas.dataset.signatureReadyWizard = '1';

                if (existingVal && existingVal.startsWith('data:image')) {
                    pad.fromDataURL(existingVal);
                }

                pad.addEventListener('endStroke', function () {
                    input.value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });

                if (clearBtn) {
                    clearBtn.onclick = function () {
                        pad.clear();
                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    };
                }
                return pad;
            }

            const subIn = document.getElementById('acc-wizard-receipt-submitter-input');
            const recIn = document.getElementById('acc-wizard-receipt-receiver-input');
            wizardReceiptSubmitterPad = setup(
                'acc-wizard-receipt-submitter-canvas',
                'acc-wizard-receipt-submitter-input',
                'acc-wizard-receipt-submitter-clear',
                subIn ? subIn.value : ''
            );
            wizardReceiptReceiverPad = setup(
                'acc-wizard-receipt-receiver-canvas',
                'acc-wizard-receipt-receiver-input',
                'acc-wizard-receipt-receiver-clear',
                recIn ? recIn.value : ''
            );
        }

        function resetWizardReceiptCanvasFlags() {
            [
                'acc-wizard-receipt-submitter-canvas',
                'acc-wizard-receipt-receiver-canvas',
                'acc-wizard-disclaimer-claimant-canvas',
                'acc-wizard-disclaimer-analyst-canvas',
            ].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.removeAttribute('data-signature-ready-wizard');
                }
            });
        }

        function initDisclaimerSignaturePads() {
            function setup(canvasId, inputId, clearBtnId, existingVal) {
                const canvas = document.getElementById(canvasId);
                const input = document.getElementById(inputId);
                const clearBtn = document.getElementById(clearBtnId);
                if (!canvas || !input || typeof SignaturePad === 'undefined') {
                    return null;
                }
                if (canvas.dataset.signatureReadyWizard === '1') {
                    return null;
                }
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
                canvas.dataset.signatureReadyWizard = '1';

                if (existingVal && existingVal.startsWith('data:image')) {
                    pad.fromDataURL(existingVal);
                }

                pad.addEventListener('endStroke', function () {
                    input.value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });

                if (clearBtn) {
                    clearBtn.onclick = function () {
                        pad.clear();
                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    };
                }
                return pad;
            }

            const claimantIn = document.getElementById('acc-wizard-disclaimer-claimant-input');
            const analystIn = document.getElementById('acc-wizard-disclaimer-analyst-input');
            disclaimerClaimantPad = setup(
                'acc-wizard-disclaimer-claimant-canvas',
                'acc-wizard-disclaimer-claimant-input',
                'acc-wizard-disclaimer-claimant-clear',
                claimantIn ? claimantIn.value : ''
            );
            disclaimerAnalystPad = setup(
                'acc-wizard-disclaimer-analyst-canvas',
                'acc-wizard-disclaimer-analyst-input',
                'acc-wizard-disclaimer-analyst-clear',
                analystIn ? analystIn.value : ''
            );
        }

        function initManagerSignaturePad() {
            const canvas = document.getElementById('acceptance-manager-signature-canvas');
            if (!canvas || typeof SignaturePad === 'undefined') {
                return;
            }

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            managerSignaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });

            $('#acceptance-manager-sign-clear').off('click.acceptance').on('click.acceptance', function () {
                managerSignaturePad?.clear();
            });

            $('#acceptance-manager-sign-submit').off('click.acceptance').on('click.acceptance', function () {
                const assignedCount = (@this.get('assignedAnalystIds') || []).length;
                if (assignedCount === 0) {
                    alert('Select at least one analyst assigned to this batch.');
                    return;
                }
                if (!@this.get('leadAnalystId')) {
                    alert('Select the lead analyst from the assigned analysts.');
                    return;
                }
                if (!@this.get('technicalSignatoryId')) {
                    alert('Select the technical signatory.');
                    return;
                }
                if (!managerSignaturePad || managerSignaturePad.isEmpty()) {
                    alert('Please provide a manager signature.');
                    return;
                }
                @this.set('managerSignature', managerSignaturePad.toDataURL('image/png'));
                @this.call('submitManagerSign');
            });
        }

        document.addEventListener('livewire:init', function () {
            Livewire.on('open-acceptance-wizard', function () {
                resetWizardReceiptCanvasFlags();
                setTimeout(initManagerSignaturePad, 400);
                setTimeout(initWizardReceiptPads, 460);
                setTimeout(initDisclaimerSignaturePads, 500);
            });

            Livewire.on('acceptance-receipt-step-opened', function () {
                resetWizardReceiptCanvasFlags();
                setTimeout(initWizardReceiptPads, 300);
            });

            Livewire.on('acceptance-disclaimer-step-opened', function () {
                resetWizardReceiptCanvasFlags();
                setTimeout(initDisclaimerSignaturePads, 300);
            });

            Livewire.hook('morph.updated', function () {
                if (document.getElementById('acceptance-manager-signature-canvas')) {
                    setTimeout(initManagerSignaturePad, 200);
                }
                if (document.getElementById('acc-wizard-receipt-submitter-canvas')) {
                    resetWizardReceiptCanvasFlags();
                    setTimeout(initWizardReceiptPads, 200);
                }
                if (document.getElementById('acc-wizard-disclaimer-analyst-canvas')) {
                    resetWizardReceiptCanvasFlags();
                    setTimeout(initDisclaimerSignaturePads, 200);
                }
            });

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('#acceptance-disclaimer-submit');
                if (!btn) {
                    return;
                }
                const analystInput = document.getElementById('acc-wizard-disclaimer-analyst-input');
                if (disclaimerAnalystPad && disclaimerAnalystPad.isEmpty() && analystInput) {
                    alert('Please provide the laboratory analyst signature.');
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            }, true);
        });
    })();
</script>
@endpush
