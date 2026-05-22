<div class="acc-wizard-root">
    @if($showModal && $acceptanceForm)
        <div class="acc-wizard-backdrop" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl acc-wizard-dialog" role="document">
                <div class="modal-content acc-wizard-modal">
                    <div class="acc-wizard-header">
                        <div class="acc-wizard-header-text">
                            <span class="acc-wizard-eyebrow">Laboratory manager</span>
                            <h4 class="acc-wizard-title">
                                <i class="mdi mdi-clipboard-check-outline"></i>
                                Approve acceptance form
                            </h4>
                            <p class="acc-wizard-hint mb-0 mt-1">
                                {{ $acceptanceForm->customer_name }}
                                @if($acceptanceForm->submissionFormInstance?->getDocumentControlNumber())
                                    · {{ $acceptanceForm->submissionFormInstance->getDocumentControlNumber() }}
                                @endif
                                @if($acceptanceForm->sampleHeader?->batch_code)
                                    · {{ $acceptanceForm->sampleHeader->batch_code }}
                                @endif
                            </p>
                        </div>
                        <button type="button" class="acc-wizard-close" wire:click="closeModal" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>

                    <div class="acc-wizard-body">
                        @if(! $acceptanceForm->sample_header_id)
                            <div class="alert alert-danger mb-3" role="alert">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                Sample batch has not been created yet.
                                @if($acceptanceForm->processing_error)
                                    {{ $acceptanceForm->processing_error }}
                                @else
                                    Batch creation may still be running. Refresh the page and try again, or contact support if this persists.
                                @endif
                            </div>
                        @elseif($acceptanceForm->processing_error)
                            <div class="alert alert-warning mb-3" role="alert">
                                <i class="mdi mdi-alert-outline"></i>
                                Batch creation reported an error: {{ $acceptanceForm->processing_error }}
                            </div>
                        @endif

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
                                @if($acceptanceForm->customer_signer_name)
                                    <p class="acc-wizard-hint mb-0">
                                        Customer signed by <strong>{{ $acceptanceForm->customer_signer_name }}</strong>
                                        @if($acceptanceForm->customer_signed_at)
                                            · {{ $acceptanceForm->customer_signed_at->format('d M Y, H:i') }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div class="row acc-wizard-fields mt-3">
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
                                <canvas id="manager-acceptance-signature-canvas"></canvas>
                                <div class="acc-signature-actions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="manager-acceptance-sign-clear">Clear</button>
                                </div>
                            </div>
                            <input type="hidden" id="manager-acceptance-signature-input" wire:model="managerSignature">
                            @error('managerSignature') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </section>

                        <div class="acc-review-panels" wire:ignore>
                            <div
                                x-data="{
                                    sections: {
                                        analysis: false,
                                        pricing: false,
                                        receipt: false,
                                        disclaimer: false,
                                    },
                                    toggle(id) {
                                        this.sections[id] = !this.sections[id];
                                    },
                                }"
                            >
                        <div class="acc-collapsible" :class="{ 'is-open': sections.analysis }">
                            <button
                                type="button"
                                class="acc-collapsible-trigger"
                                @click="toggle('analysis')"
                                :aria-expanded="sections.analysis"
                            >
                                <span class="acc-collapsible-trigger-main">
                                    <span class="acc-collapsible-icon-wrap" aria-hidden="true">
                                        <i class="mdi mdi-file-document-outline"></i>
                                    </span>
                                    <span class="acc-collapsible-titles">
                                        <span class="acc-collapsible-title">Analysis acceptance (GCLA / F/03)</span>
                                        <span class="acc-collapsible-subtitle">Customer &amp; request summary</span>
                                    </span>
                                </span>
                                <i class="mdi mdi-chevron-down acc-collapsible-chevron"></i>
                            </button>
                            <div class="acc-collapsible-panel" x-show="sections.analysis" x-cloak>
                            <div class="acc-collapsible-body">
                            <div class="row acc-wizard-fields mb-0">
                                <div class="col-md-3 mb-3">
                                    <span class="acc-label d-block">Customer</span>
                                    <span class="acc-summary-value">{{ $acceptanceForm->customer_name ?: '—' }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="acc-label d-block">Request date</span>
                                    <span class="acc-summary-value">{{ optional($acceptanceForm->request_date)->format('Y-m-d') ?? '—' }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="acc-label d-block">Date of sampling</span>
                                    <span class="acc-summary-value">{{ optional($acceptanceForm->date_of_sampling)->format('Y-m-d') ?? '—' }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="acc-label d-block">Mode of work</span>
                                    <span class="acc-summary-value">{{ $acceptanceForm->mode_of_work ?: '—' }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="acc-label d-block">Number of samples</span>
                                    <span class="acc-summary-value">{{ $acceptanceForm->number_of_samples }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="acc-label d-block">Customer signed</span>
                                    <span class="acc-summary-value">
                                        {{ $acceptanceForm->customer_signer_name ?: '—' }}
                                        @if($acceptanceForm->customer_signed_at)
                                            <small class="text-muted d-block">{{ $acceptanceForm->customer_signed_at->format('d M Y, H:i') }}</small>
                                        @endif
                                    </span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="acc-label d-block">Pricelist</span>
                                    <span class="acc-summary-value">{{ $acceptanceForm->pricelist?->description ?? $acceptanceForm->pricelist?->code ?? '—' }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="acc-label d-block">Total amount</span>
                                    <span class="acc-summary-value">{{ number_format((float) $acceptanceForm->total_amount, 2) }}</span>
                                </div>
                            </div>
                            @if($acceptanceForm->customer_signature && str_starts_with((string) $acceptanceForm->customer_signature, 'data:image'))
                                <div class="acc-collapsible-signature-field mt-2">
                                    <span class="acc-label d-block">Customer signature (acceptance form)</span>
                                    <div class="acc-signature-review">
                                        <img src="{{ $acceptanceForm->customer_signature }}" alt="Customer acceptance signature">
                                    </div>
                                </div>
                            @endif
                            </div>
                            </div>
                        </div>

                        <div class="acc-collapsible" :class="{ 'is-open': sections.pricing }">
                            <button
                                type="button"
                                class="acc-collapsible-trigger"
                                @click="toggle('pricing')"
                                :aria-expanded="sections.pricing"
                            >
                                <span class="acc-collapsible-trigger-main">
                                    <span class="acc-collapsible-icon-wrap" aria-hidden="true">
                                        <i class="mdi mdi-currency-usd"></i>
                                    </span>
                                    <span class="acc-collapsible-titles">
                                        <span class="acc-collapsible-title">Parameters &amp; pricing</span>
                                        <span class="acc-collapsible-subtitle">{{ $acceptanceForm->lines->where('is_approved', true)->count() }} approved line(s)</span>
                                    </span>
                                </span>
                                <i class="mdi mdi-chevron-down acc-collapsible-chevron"></i>
                            </button>
                            <div class="acc-collapsible-panel" x-show="sections.pricing" x-cloak>
                            <div class="acc-collapsible-body acc-pricing-section">
                            <div class="acc-pricing-table-wrap">
                                <table class="table acc-pricing-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="col-no">No</th>
                                            <th>Parameter</th>
                                            <th>Sample type</th>
                                            <th>Analysis type</th>
                                            <th class="text-right col-amount">Unit</th>
                                            <th class="col-samples">Qty</th>
                                            <th class="text-right col-amount">Line total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($acceptanceForm->lines->where('is_approved', true) as $line)
                                            @php $lineTotal = (float) $line->unit_amount * max(1, (int) $line->number_of_samples); @endphp
                                            <tr wire:key="manager-sign-line-{{ $line->id }}">
                                                <td>{{ $line->line_no }}</td>
                                                <td class="acc-param-name">{{ $line->parameter_label }}</td>
                                                <td>{{ $line->sampleType?->name ?? '—' }}</td>
                                                <td>{{ $line->analysisType?->name ?? '—' }}</td>
                                                <td class="text-right acc-amount">{{ number_format((float) $line->unit_amount, 2) }}</td>
                                                <td>{{ $line->number_of_samples }}</td>
                                                <td class="text-right acc-amount">{{ number_format($lineTotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-right font-weight-bold">Total</td>
                                            <td class="text-right acc-amount font-weight-bold">{{ number_format((float) $acceptanceForm->total_amount, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            </div>
                            </div>
                        </div>

                        <div class="acc-collapsible" :class="{ 'is-open': sections.receipt }">
                            <button
                                type="button"
                                class="acc-collapsible-trigger"
                                @click="toggle('receipt')"
                                :aria-expanded="sections.receipt"
                            >
                                <span class="acc-collapsible-trigger-main">
                                    <span class="acc-collapsible-icon-wrap" aria-hidden="true">
                                        <i class="mdi mdi-clipboard-text-outline"></i>
                                    </span>
                                    <span class="acc-collapsible-titles">
                                        <span class="acc-collapsible-title">Sample Receipt Notification (GCLA 01)</span>
                                        <span class="acc-collapsible-subtitle">Captured at customer sign</span>
                                    </span>
                                </span>
                                <i class="mdi mdi-chevron-down acc-collapsible-chevron"></i>
                            </button>
                            <div class="acc-collapsible-panel" x-show="sections.receipt" x-cloak>
                            <div class="acc-collapsible-body">
                                <p class="acc-wizard-hint mb-3">Expand to review receipt details before approving.</p>
                                @include('livewire.partials.receipt-notification-review-fields', [
                                    'receiptForm' => $receiptNotificationForm,
                                ])
                            </div>
                            </div>
                        </div>

                        @if($showSampleDisclaimer)
                            <div class="acc-collapsible" :class="{ 'is-open': sections.disclaimer }">
                                <button
                                    type="button"
                                    class="acc-collapsible-trigger"
                                    @click="toggle('disclaimer')"
                                    :aria-expanded="sections.disclaimer"
                                >
                                    <span class="acc-collapsible-trigger-main">
                                        <span class="acc-collapsible-icon-wrap" aria-hidden="true">
                                            <i class="mdi mdi-shield-alert-outline"></i>
                                        </span>
                                        <span class="acc-collapsible-titles">
                                            <span class="acc-collapsible-title">Sample receiving disclaimer</span>
                                            <span class="acc-collapsible-subtitle">Integrity disclaimer at receiving</span>
                                        </span>
                                    </span>
                                    <i class="mdi mdi-chevron-down acc-collapsible-chevron"></i>
                                </button>
                                <div class="acc-collapsible-panel" x-show="sections.disclaimer" x-cloak>
                                <div class="acc-collapsible-body">
                                    @include('livewire.partials.sample-disclaimer-wire-fields', [
                                        'wirePrefix' => 'disclaimerForm.',
                                        'canvasPrefix' => 'mgr-acc-disclaimer',
                                        'readOnly' => true,
                                        'disclaimantDisplay' => $disclaimerForm['disclaimant_name'] ?? '',
                                        'claimantSignatureDisplay' => $disclaimerForm['claimant_signature'] ?? '',
                                        'analystSignatureDisplay' => $disclaimerForm['analyst_signature'] ?? '',
                                    ])
                                </div>
                                </div>
                            </div>
                        @endif
                            </div>
                        </div>
                    </div>

                    <div class="acc-wizard-footer">
                        <button type="button" class="btn btn-light acc-btn-ghost" wire:click="closeModal">Cancel</button>
                        <button
                            type="button"
                            class="btn acc-btn-success"
                            id="manager-acceptance-sign-submit"
                            wire:loading.attr="disabled"
                            wire:target="submitManagerSign"
                            @disabled(! $acceptanceForm->sample_header_id)
                        >
                            <span wire:loading.remove wire:target="submitManagerSign">Approve &amp; complete</span>
                            <span wire:loading wire:target="submitManagerSign">Submitting…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .acc-wizard-root {
            --acc-accent: #3b5fc0;
            --acc-border: #e2e8f0;
            --acc-muted: #64748b;
            --acc-text: #0f172a;
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

        .acc-wizard-dialog {
            max-width: 1140px;
            margin: 0;
            width: 100%;
        }

        .acc-pricing-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--acc-border);
            border-radius: 10px;
        }

        .acc-pricing-table {
            margin: 0;
            font-size: 0.875rem;
        }

        .acc-pricing-table thead th {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--acc-muted);
            background: #f8fafc;
            border-bottom: 1px solid var(--acc-border);
            white-space: nowrap;
        }

        .acc-pricing-table .col-no { width: 48px; }
        .acc-pricing-table .col-amount { width: 100px; }
        .acc-pricing-table .col-samples { width: 72px; }

        .acc-review-panels .acc-collapsible-signature-field {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
        }

        .acc-review-panels .acc-collapsible-signature-field .acc-label,
        .acc-review-panels .acc-collapsible-signature-field .receipt-notif-form__label {
            width: 100%;
        }

        .acc-review-panels .acc-signature-review,
        .acc-review-panels .acc-signature-review-box,
        .acc-review-panels .receipt-notif-review-sig {
            width: 100%;
            max-width: 420px;
            margin-inline: auto;
        }

        .acc-signature-review,
        .acc-signature-review-box {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 0.5rem;
            background: #fff;
        }

        .acc-signature-review img,
        .acc-signature-review-box img,
        .acc-review-panels .receipt-notif-review-sig img {
            max-width: 100%;
            max-height: 140px;
            display: block;
            margin-inline: auto;
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

        .acc-wizard-body {
            max-height: min(70vh, 720px);
            overflow-y: auto;
            padding: 1.25rem 1.5rem;
        }

        .acc-wizard-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--acc-border);
            background: #f8fafc;
        }

        .acc-wizard-steps--two {
            grid-template-columns: repeat(2, 1fr);
        }

        .acc-summary-value {
            font-weight: 600;
            color: var(--acc-text, #0f172a);
            font-size: 0.9rem;
        }

        .acc-cert-card {
            background: #f8fafc;
            border: 1px solid var(--acc-border, #e2e8f0);
            border-radius: 10px;
            padding: 1rem 1.1rem;
        }

        .acc-cert-quote {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--acc-text, #0f172a);
            margin-bottom: 0.5rem;
        }

        .acc-manager-assignments-section {
            background: #f8fafc;
            border: 1px solid var(--acc-border, #e2e8f0);
            border-radius: 12px;
            padding: 1rem 1.1rem;
        }

        .acc-review-panels {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            margin-top: 0.25rem;
        }

        .acc-review-panels [x-cloak] {
            display: none !important;
        }

        .acc-collapsible {
            border: 1px solid #e8edf3;
            border-radius: 12px;
            background: linear-gradient(180deg, #fafbfd 0%, #f4f7fb 100%);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .acc-collapsible:hover {
            border-color: #d5deea;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
        }

        .acc-collapsible.is-open {
            background: #fff;
            border-color: #c7d4e8;
            box-shadow: 0 8px 24px rgba(59, 95, 192, 0.08);
        }

        .acc-collapsible-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            width: 100%;
            padding: 0.9rem 1rem;
            border: none;
            background: transparent;
            text-align: left;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .acc-collapsible-trigger:hover {
            background: rgba(59, 95, 192, 0.04);
        }

        .acc-collapsible-trigger:focus {
            outline: none;
        }

        .acc-collapsible-trigger:focus-visible {
            outline: 2px solid var(--acc-accent, #3b5fc0);
            outline-offset: -2px;
        }

        .acc-collapsible-trigger-main {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }

        .acc-collapsible-icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: var(--acc-accent, #3b5fc0);
            flex-shrink: 0;
            font-size: 1.1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        }

        .acc-collapsible.is-open .acc-collapsible-icon-wrap {
            background: var(--acc-accent, #3b5fc0);
            border-color: var(--acc-accent, #3b5fc0);
            color: #fff;
        }

        .acc-collapsible-titles {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .acc-collapsible-title {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--acc-text, #0f172a);
            line-height: 1.3;
        }

        .acc-collapsible-subtitle {
            font-size: 0.78rem;
            color: var(--acc-muted, #64748b);
            line-height: 1.25;
        }

        .acc-collapsible-chevron {
            flex-shrink: 0;
            font-size: 1.25rem;
            color: #94a3b8;
            transition: transform 0.22s ease, color 0.2s ease;
        }

        .acc-collapsible.is-open .acc-collapsible-chevron {
            transform: rotate(180deg);
            color: var(--acc-accent, #3b5fc0);
        }

        .acc-collapsible-panel {
            border-top: 1px solid #e8edf3;
        }

        .acc-collapsible-body {
            padding: 1rem 1rem 1.1rem;
            background: #fff;
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
            border: 1px solid var(--acc-border, #e2e8f0);
            border-radius: 10px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .acc-wizard-root .tag-select-input:focus-within {
            border-color: var(--acc-accent, #3b5fc0);
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
            border: 1px solid var(--acc-border, #e2e8f0);
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

        .acc-signature-pad {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            background: #fff;
            padding: 0.5rem;
        }

        .acc-signature-pad canvas {
            width: 100%;
            height: 160px;
            display: block;
            touch-action: none;
        }

        .acc-signature-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }

        .password-input-wrap {
            position: relative;
        }

        .password-input-wrap .form-control {
            padding-right: 2.75rem;
        }

        .password-input-wrap .password-toggle-btn {
            position: absolute;
            top: 50%;
            right: 0.35rem;
            transform: translateY(-50%);
            width: 2.25rem;
            height: 2.25rem;
            margin: 0;
            padding: 0;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .password-input-wrap .password-toggle-btn:hover,
        .password-input-wrap .password-toggle-btn:focus {
            color: #1e293b;
            background: #f1f5f9;
        }

        .password-input-wrap .password-toggle-btn:focus {
            outline: none;
        }

        .password-input-wrap .password-toggle-btn:focus-visible {
            outline: 2px solid var(--acc-accent, #3b5fc0);
            outline-offset: 2px;
        }

        .password-input-wrap .password-toggle-btn .mdi {
            font-size: 1.2rem;
            line-height: 1;
            pointer-events: none;
        }
    </style>
</div>

@push('scripts')
<script>
    (function () {
        let managerSignaturePad = null;
        let managerReceiptSubmitterPad = null;
        let managerReceiptReceiverPad = null;

        function initManagerReceiptPads() {
            function setup(canvasId, inputId, clearBtnId, existingVal) {
                const canvas = document.getElementById(canvasId);
                const input = document.getElementById(inputId);
                const clearBtn = document.getElementById(clearBtnId);
                if (!canvas || !input || typeof SignaturePad === 'undefined') {
                    return null;
                }
                if (canvas.dataset.mgrRecReady === '1') {
                    return null;
                }
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
                canvas.dataset.mgrRecReady = '1';
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
            const si = document.getElementById('mgr-acc-receipt-submitter-input');
            const ri = document.getElementById('mgr-acc-receipt-receiver-input');
            ['mgr-acc-receipt-submitter-canvas', 'mgr-acc-receipt-receiver-canvas'].forEach(function (cid) {
                const c = document.getElementById(cid);
                if (c) {
                    c.removeAttribute('data-mgr-rec-ready');
                }
            });
            managerReceiptSubmitterPad = setup('mgr-acc-receipt-submitter-canvas', 'mgr-acc-receipt-submitter-input', 'mgr-acc-receipt-submitter-clear', si ? si.value : '');
            managerReceiptReceiverPad = setup('mgr-acc-receipt-receiver-canvas', 'mgr-acc-receipt-receiver-input', 'mgr-acc-receipt-receiver-clear', ri ? ri.value : '');
        }

        function initManagerSignaturePad() {
            const canvas = document.getElementById('manager-acceptance-signature-canvas');
            if (!canvas || typeof SignaturePad === 'undefined') {
                return;
            }

            if (canvas.dataset.signatureReady === '1') {
                return;
            }

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            managerSignaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
            canvas.dataset.signatureReady = '1';

            $('#manager-acceptance-sign-clear').off('click.managerSign').on('click.managerSign', function () {
                managerSignaturePad?.clear();
            });

            $('#manager-acceptance-sign-submit').off('click.managerSign').on('click.managerSign', function () {
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
            Livewire.on('manager-acceptance-sign-opened', function () {
                setTimeout(initManagerSignaturePad, 300);
            });

            Livewire.hook('morph.updated', function () {
                if (document.getElementById('manager-acceptance-signature-canvas')) {
                    setTimeout(initManagerSignaturePad, 200);
                }
            });
        });
    })();
</script>
@endpush
