<div class="acc-wizard-root acc-wizard-root--customer-sign">
    @if($showModal && $acceptanceForm)
        <div class="acc-wizard-backdrop" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg acc-wizard-dialog" role="document">
                <div class="modal-content acc-wizard-modal">
                    <div class="acc-wizard-header">
                        <div class="acc-wizard-header-text">
                            <span class="acc-wizard-eyebrow">Customer acceptance</span>
                            <h4 class="acc-wizard-title">
                                <i class="mdi mdi-draw"></i>
                                Sign acceptance form
                            </h4>
                            <p class="acc-wizard-hint mb-0 mt-1">
                                {{ $acceptanceForm->customer_name }}
                                @if($acceptanceForm->submissionFormInstance?->getDocumentControlNumber())
                                    · {{ $acceptanceForm->submissionFormInstance->getDocumentControlNumber() }}
                                @endif
                            </p>
                        </div>
                        <button type="button" class="acc-wizard-close" wire:click="closeModal" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>

                    <div class="acc-wizard-steps acc-wizard-steps--three" role="tablist">
                        @foreach([
                            1 => ['label' => 'Verify identity', 'icon' => 'mdi-account-key-outline'],
                            2 => ['label' => 'Sign Acceptance Form', 'icon' => 'mdi-file-sign'],
                            3 => ['label' => 'Sign Sample Receipt Notification Form', 'icon' => 'mdi-clipboard-text-outline'],
                        ] as $step => $meta)
                            @php
                                $isActive = $currentStep === $step;
                                $isDone = $currentStep > $step;
                                $stepDisabled = match (true) {
                                    $step === 2 => ! $verifiedContactId,
                                    $step === 3 => ! $verifiedContactId || $customerSignature === '',
                                    default => false,
                                };
                            @endphp
                            <button
                                type="button"
                                class="acc-wizard-step {{ $isActive ? 'is-active' : '' }} {{ $isDone ? 'is-done' : '' }}"
                                wire:click="goToStep({{ $step }})"
                                @disabled($stepDisabled)
                            >
                                <span class="acc-wizard-step-index">
                                    @if($isDone)
                                        <i class="mdi mdi-check"></i>
                                    @else
                                        {{ $step }}
                                    @endif
                                </span>
                                <span class="acc-wizard-step-label">{{ $meta['label'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="acc-wizard-body">
                        @if($currentStep === 1)
                            <section class="acc-wizard-section">
                                <h6 class="acc-wizard-section-title">Verify customer contact</h6>
                                <p class="acc-wizard-hint">
                                    Select the customer’s portal contact. Portal password is optional for in-person lab-assisted signing; enter it when the customer is verifying themselves.
                                </p>
                                <div class="row acc-wizard-fields">
                                    <div class="col-md-12 form-group">
                                        <label class="acc-label" for="customer-sign-contact">Contact</label>
                                        <select
                                            id="customer-sign-contact"
                                            class="form-control acc-input"
                                            wire:model="selectedContactId"
                                        >
                                            <option value="">Select contact…</option>
                                            @foreach($contactOptions as $option)
                                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedContactId') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-12 form-group mb-0">
                                        <label class="acc-label" for="customer-sign-password">Portal password <span class="text-muted">(optional)</span></label>
                                        <div class="password-input-wrap">
                                            <input
                                                type="password"
                                                id="customer-sign-password"
                                                class="form-control acc-input"
                                                wire:model="password"
                                                autocomplete="current-password"
                                                placeholder="Enter portal password"
                                            >
                                            <button
                                                type="button"
                                                class="password-toggle-btn"
                                                id="customer-sign-password-toggle"
                                                aria-label="Show password"
                                                aria-pressed="false"
                                            >
                                                <i class="mdi mdi-eye-outline" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        @error('password') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </section>
                        @elseif($currentStep === 2)
                            <section class="acc-wizard-section">
                                <h6 class="acc-wizard-section-title">Request summary</h6>
                                <div class="row acc-wizard-fields mb-0">
                                    <div class="col-md-4">
                                        <span class="acc-label d-block">Request date</span>
                                        <span class="acc-summary-value">{{ optional($acceptanceForm->request_date)->format('Y-m-d') ?? '—' }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="acc-label d-block">Mode of work</span>
                                        <span class="acc-summary-value">{{ $acceptanceForm->mode_of_work }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="acc-label d-block">Samples</span>
                                        <span class="acc-summary-value">{{ $acceptanceForm->number_of_samples }}</span>
                                    </div>
                                </div>
                            </section>

                            <section class="acc-wizard-section acc-pricing-section">
                                <h6 class="acc-wizard-section-title mb-2">Parameters &amp; pricing</h6>
                                <div class="acc-pricing-table-wrap">
                                    <table class="table acc-pricing-table mb-0">
                                        <thead>
                                            <tr>
                                                <th class="col-no">No</th>
                                                <th>Parameter</th>
                                                <th>Sample type</th>
                                                <th>Analysis</th>
                                                <th class="text-right col-amount">Unit</th>
                                                <th class="col-samples">Qty</th>
                                                <th class="text-right col-amount">Line total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($acceptanceForm->lines->where('is_approved', true) as $line)
                                                @php
                                                    $lineTotal = (float) $line->unit_amount * max(1, (int) $line->number_of_samples);
                                                @endphp
                                                <tr class="acc-row-parameter" wire:key="sign-line-{{ $line->id }}">
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
                            </section>

                            @if($showDisclaimerClaimantSign)
                                <section class="acc-wizard-section">
                                    <h6 class="acc-wizard-section-title">Sample receiving disclaimer — claimant</h6>
                                    <p class="acc-wizard-hint mb-3">Sign as the disclaimant to acknowledge analysis despite sample integrity concerns.</p>
                                    @include('livewire.partials.sample-disclaimer-wire-fields', [
                                        'wirePrefix' => 'disclaimerForm.',
                                        'canvasPrefix' => 'cust-acc-disclaimer',
                                        'readOnly' => false,
                                        'claimantOnly' => true,
                                        'disclaimantDisplay' => $disclaimerForm['disclaimant_name'] ?? '',
                                    ])
                                </section>
                            @endif

                            <section class="acc-wizard-section">
                                <div class="acc-cert-card">
                                    <p class="acc-cert-quote">{{ \App\Services\Sampleworkflow\AcceptanceFormService::CUSTOMER_CERTIFICATION_TEXT }}</p>
                                    <p class="acc-wizard-hint mb-0">
                                        Signing as: <strong>{{ $customerSignerName }}</strong>
                                    </p>
                                </div>
                                <label class="acc-label d-block mt-3">Customer signature</label>
                                <div class="acc-signature-pad" wire:ignore>
                                    <canvas id="customer-acceptance-signature-canvas"></canvas>
                                    <div class="acc-signature-actions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="customer-acceptance-sign-clear">Clear</button>
                                    </div>
                                </div>
                                <input type="hidden" id="customer-acceptance-signature-input" wire:model="customerSignature">
                                @error('customerSignature') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </section>
                        @else
                            <section class="acc-wizard-section">
                                <h6 class="acc-wizard-section-title">Sample Receipt Notification (GCLA 01)</h6>
                                <p class="acc-wizard-hint mb-3">
                                    Review the receipt details and sign as the person submitting the sample or exhibit.
                                </p>
                                @include('livewire.partials.receipt-notification-wire-fields', [
                                    'wirePrefix' => 'receiptNotificationForm.',
                                    'canvasPrefix' => 'cust-acc-receipt',
                                    'readOnly' => false,
                                    'partLabel' => null,
                                    'showSubmitterSection' => true,
                                    'showSubmitterSigningNotice' => false,
                                ])
                            </section>
                        @endif
                    </div>

                    <div class="acc-wizard-footer">
                        <button type="button" class="btn btn-light acc-btn-ghost" wire:click="closeModal">Cancel</button>
                        @if($currentStep === 1)
                            <button
                                type="button"
                                class="btn acc-btn-primary"
                                wire:click="verifyIdentity"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="verifyIdentity">Continue</span>
                                <span wire:loading wire:target="verifyIdentity">Verifying…</span>
                            </button>
                        @elseif($currentStep === 2)
                            <button type="button" class="btn btn-light acc-btn-ghost" wire:click="goToStep(1)">Back</button>
                            <button
                                type="button"
                                class="btn acc-btn-primary"
                                id="customer-acceptance-continue-receipt"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="continueToReceiptStep">Continue</span>
                                <span wire:loading wire:target="continueToReceiptStep">Saving…</span>
                            </button>
                        @else
                            <button type="button" class="btn btn-light acc-btn-ghost" wire:click="goToStep(2)">Back</button>
                            <button
                                type="button"
                                class="btn acc-btn-success"
                                id="customer-acceptance-sign-submit"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="submitCustomerSign">Sign &amp; submit</span>
                                <span wire:loading wire:target="submitCustomerSign">Submitting…</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .acc-wizard-root--customer-sign .acc-wizard-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background: #fff !important;
            color: var(--acc-text, #0f172a) !important;
            border-bottom: 1px solid var(--acc-border, #e2e8f0);
        }

        .acc-wizard-root--customer-sign .acc-wizard-header .acc-wizard-eyebrow {
            color: var(--acc-muted, #64748b);
            opacity: 1;
        }

        .acc-wizard-root--customer-sign .acc-wizard-header .acc-wizard-title {
            color: var(--acc-text, #0f172a);
        }

        .acc-wizard-root--customer-sign .acc-wizard-header .acc-wizard-hint {
            color: var(--acc-muted, #64748b);
        }

        .acc-wizard-root--customer-sign .acc-wizard-close {
            border: none;
            background: #f1f5f9 !important;
            color: var(--acc-muted, #64748b) !important;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .acc-wizard-root--customer-sign .acc-wizard-close:hover {
            background: #e2e8f0 !important;
            color: var(--acc-text, #0f172a) !important;
        }

        .acc-wizard-steps--three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .acc-wizard-root--customer-sign .acc-wizard-step-label {
            font-size: 0.72rem;
            line-height: 1.25;
            text-align: center;
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
        let customerSignaturePad = null;
        let customerReceiptSubmitterPad = null;
        let customerReceiptReceiverPad = null;
        let customerDisclaimerClaimantPad = null;

        function initCustomerReceiptPads() {
            function setup(canvasId, inputId, clearBtnId, existingVal) {
                const canvas = document.getElementById(canvasId);
                const input = document.getElementById(inputId);
                const clearBtn = document.getElementById(clearBtnId);
                if (!canvas || !input || typeof SignaturePad === 'undefined') {
                    return null;
                }
                if (canvas.dataset.custRecReady === '1') {
                    return canvasId === 'cust-acc-receipt-submitter-canvas'
                        ? customerReceiptSubmitterPad
                        : customerReceiptReceiverPad;
                }
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
                canvas.dataset.custRecReady = '1';
                if (existingVal && existingVal.startsWith('data:image')) {
                    pad.fromDataURL(existingVal);
                }
                if (clearBtn) {
                    clearBtn.onclick = function () {
                        pad.clear();
                        input.value = '';
                    };
                }
                return pad;
            }

            const submitterCanvas = document.getElementById('cust-acc-receipt-submitter-canvas');
            const receiverCanvas = document.getElementById('cust-acc-receipt-receiver-canvas');
            if (
                submitterCanvas?.dataset.custRecReady === '1'
                && receiverCanvas?.dataset.custRecReady === '1'
                && customerReceiptSubmitterPad
                && customerReceiptReceiverPad
            ) {
                return;
            }

            const si = document.getElementById('cust-acc-receipt-submitter-input');
            const ri = document.getElementById('cust-acc-receipt-receiver-input');
            customerReceiptSubmitterPad = setup('cust-acc-receipt-submitter-canvas', 'cust-acc-receipt-submitter-input', 'cust-acc-receipt-submitter-clear', si ? si.value : '');
            customerReceiptReceiverPad = setup('cust-acc-receipt-receiver-canvas', 'cust-acc-receipt-receiver-input', 'cust-acc-receipt-receiver-clear', ri ? ri.value : '');
        }

        function initCustomerSignPasswordToggle() {
            const input = document.getElementById('customer-sign-password');
            const btn = document.getElementById('customer-sign-password-toggle');
            if (!input || !btn || btn.dataset.bound === '1') {
                return;
            }

            btn.dataset.bound = '1';
            btn.addEventListener('click', function () {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('mdi-eye-outline', !show);
                    icon.classList.toggle('mdi-eye-off-outline', show);
                }
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        }

        function initCustomerSignaturePad() {
            const canvas = document.getElementById('customer-acceptance-signature-canvas');
            if (!canvas || typeof SignaturePad === 'undefined') {
                return;
            }

            // Bail out if this exact canvas element is already set up.
            // The canvas is freshly rendered each time step 2 becomes active, so
            // this flag is naturally absent then and only skips spurious re-inits
            // triggered by morph.updated while the user is already on step 2.
            if (canvas.dataset.signatureReady === '1') {
                return;
            }

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            customerSignaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
            canvas.dataset.signatureReady = '1';

            $('#customer-acceptance-sign-clear').off('click.customerSign').on('click.customerSign', function () {
                customerSignaturePad?.clear();
            });

            $('#customer-acceptance-continue-receipt').off('click.customerSign').on('click.customerSign', function () {
                if (!customerSignaturePad || customerSignaturePad.isEmpty()) {
                    alert('Please provide your signature on the Analysis Acceptance Form.');
                    return;
                }
                @this.set('customerSignature', customerSignaturePad.toDataURL('image/png'));
                @this.call('continueToReceiptStep');
            });
        }

        function padSignatureValue(pad, input) {
            if (pad && !pad.isEmpty()) {
                return pad.toDataURL('image/png');
            }
            if (input && input.value) {
                return input.value;
            }

            return '';
        }

        function initCustomerSignSubmitButton() {
            const btn = document.getElementById('customer-acceptance-sign-submit');
            if (!btn) {
                return;
            }

            if (btn._custSubmitClickHandler) {
                btn.removeEventListener('click', btn._custSubmitClickHandler);
            }

            btn._custSubmitClickHandler = function () {
                const submitterInput = document.getElementById('cust-acc-receipt-submitter-input');
                const receiverInput = document.getElementById('cust-acc-receipt-receiver-input');
                const submitterSig = padSignatureValue(customerReceiptSubmitterPad, submitterInput);
                const receiverSig = padSignatureValue(customerReceiptReceiverPad, receiverInput);

                if (!submitterSig) {
                    alert('Please sign as the person submitting the sample or exhibit.');
                    return;
                }

                if (!receiverSig) {
                    alert('Receiving person signature is required.');
                    return;
                }

                const dateInput = document.querySelector('[wire\\:model\\.live="receiptNotificationForm.sample_receiving_date"], [wire\\:model="receiptNotificationForm.sample_receiving_date"]');
                const receivingDate = dateInput && dateInput.value
                    ? dateInput.value
                    : new Date().toISOString().slice(0, 10);

                @this.set('receiptNotificationForm.submitter_signature', submitterSig)
                    .then(function () {
                        return @this.set('receiptNotificationForm.receiver_signature', receiverSig);
                    })
                    .then(function () {
                        return @this.set('receiptNotificationForm.sample_receiving_date', receivingDate);
                    })
                    .then(function () {
                        @this.call('submitCustomerSign');
                    });
            };

            btn.addEventListener('click', btn._custSubmitClickHandler);
        }

        document.addEventListener('livewire:init', function () {
            Livewire.on('customer-acceptance-sign-opened', function () {
                customerReceiptSubmitterPad = null;
                customerReceiptReceiverPad = null;
                setTimeout(function () {
                    initCustomerSignPasswordToggle();
                    initCustomerSignaturePad();
                }, 400);
            });

            function initCustomerDisclaimerClaimantPad() {
                const canvas = document.getElementById('cust-acc-disclaimer-claimant-canvas');
                const input = document.getElementById('cust-acc-disclaimer-claimant-input');
                const clearBtn = document.getElementById('cust-acc-disclaimer-claimant-clear');
                if (!canvas || !input || typeof SignaturePad === 'undefined') {
                    return;
                }
                if (canvas.dataset.custDiscReady === '1') {
                    return;
                }
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                customerDisclaimerClaimantPad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
                canvas.dataset.custDiscReady = '1';
                if (input.value && input.value.startsWith('data:image')) {
                    customerDisclaimerClaimantPad.fromDataURL(input.value);
                }
                customerDisclaimerClaimantPad.addEventListener('endStroke', function () {
                    input.value = customerDisclaimerClaimantPad.isEmpty() ? '' : customerDisclaimerClaimantPad.toDataURL('image/png');
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
                if (clearBtn) {
                    clearBtn.onclick = function () {
                        customerDisclaimerClaimantPad.clear();
                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    };
                }
            }

            Livewire.on('customer-acceptance-sign-step2', function () {
                setTimeout(initCustomerSignaturePad, 300);
                setTimeout(initCustomerDisclaimerClaimantPad, 350);
            });

            Livewire.on('customer-acceptance-sign-step3', function () {
                customerReceiptSubmitterPad = null;
                customerReceiptReceiverPad = null;
                ['cust-acc-receipt-submitter-canvas', 'cust-acc-receipt-receiver-canvas'].forEach(function (cid) {
                    const c = document.getElementById(cid);
                    if (c) {
                        c.removeAttribute('data-cust-rec-ready');
                    }
                });
                setTimeout(function () {
                    initCustomerReceiptPads();
                    initCustomerSignSubmitButton();
                }, 300);
            });

            Livewire.hook('morph.updated', function () {
                if (document.getElementById('customer-sign-password')) {
                    setTimeout(initCustomerSignPasswordToggle, 50);
                }
                if (document.getElementById('customer-acceptance-signature-canvas')) {
                    setTimeout(initCustomerSignaturePad, 200);
                }
                const receiptSubmitterCanvas = document.getElementById('cust-acc-receipt-submitter-canvas');
                if (receiptSubmitterCanvas && receiptSubmitterCanvas.dataset.custRecReady !== '1') {
                    setTimeout(function () {
                        initCustomerReceiptPads();
                        initCustomerSignSubmitButton();
                    }, 220);
                }
                if (document.getElementById('customer-acceptance-sign-submit')) {
                    setTimeout(initCustomerSignSubmitButton, 240);
                }
                if (document.getElementById('cust-acc-disclaimer-claimant-canvas')) {
                    const c = document.getElementById('cust-acc-disclaimer-claimant-canvas');
                    if (c && c.dataset.custDiscReady !== '1') {
                        setTimeout(initCustomerDisclaimerClaimantPad, 220);
                    }
                }
            });
        });
    })();
</script>
@endpush
