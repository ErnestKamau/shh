<div class="acc-wizard-root">
    @if($showModal && $acceptanceForm)
        <div class="acc-wizard-backdrop" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg acc-wizard-dialog" role="document">
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
                        @if($acceptanceForm->processing_error)
                            <div class="alert alert-warning mb-3" role="alert">
                                <i class="mdi mdi-alert-outline"></i>
                                Batch creation reported an error: {{ $acceptanceForm->processing_error }}
                            </div>
                        @endif

                        <section class="acc-wizard-section">
                            <h6 class="acc-wizard-section-title">Batch assignments</h6>
                            <p class="acc-wizard-hint">Assign the lead analyst and technical signatory before completing manager approval.</p>
                            <div class="row acc-wizard-fields">
                                <div class="col-md-6 form-group">
                                    <label class="acc-label" for="manager-sign-lead-analyst">Lead analyst</label>
                                    <select id="manager-sign-lead-analyst" class="form-control acc-input" wire:model="leadAnalystId">
                                        <option value="">Select lead analyst…</option>
                                        @foreach($analystOptions as $option)
                                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('leadAnalystId') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="acc-label" for="manager-sign-technical-signatory">Technical signatory</label>
                                    <select id="manager-sign-technical-signatory" class="form-control acc-input" wire:model="technicalSignatoryId">
                                        <option value="">Select technical signatory…</option>
                                        @foreach($signatoryOptions as $option)
                                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('technicalSignatoryId') <small class="text-danger d-block">{{ $message }}</small> @enderror
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
                                                <th class="text-right col-amount">Line total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($acceptanceForm->lines->where('is_approved', true) as $line)
                                                @php $lineTotal = (float) $line->unit_amount * max(1, (int) $line->number_of_samples); @endphp
                                                <tr wire:key="manager-sign-line-{{ $line->id }}">
                                                    <td>{{ $line->line_no }}</td>
                                                    <td class="acc-param-name">{{ $line->parameter_label }}</td>
                                                    <td class="text-right acc-amount">{{ number_format($lineTotal, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-right font-weight-bold">Total</td>
                                                <td class="text-right acc-amount font-weight-bold">{{ number_format((float) $acceptanceForm->total_amount, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </section>

                        <section class="acc-wizard-section">
                            <h6 class="acc-wizard-section-title mb-2">Sample Receipt Notification (GCLA 01)</h6>
                            @include('livewire.partials.receipt-notification-wire-fields', [
                                'wirePrefix' => 'receiptNotificationForm.',
                                'canvasPrefix' => 'mgr-acc-receipt',
                                'readOnly' => false,
                                'partLabel' => null,
                            ])
                        </section>

                            <section class="acc-wizard-section">
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
                    </div>

                    <div class="acc-wizard-footer">
                        <button type="button" class="btn btn-light acc-btn-ghost" wire:click="closeModal">Cancel</button>
                        <button
                            type="button"
                            class="btn acc-btn-success"
                            id="manager-acceptance-sign-submit"
                            wire:loading.attr="disabled"
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
                setTimeout(initManagerReceiptPads, 360);
            });

            Livewire.hook('morph.updated', function () {
                if (document.getElementById('manager-acceptance-signature-canvas')) {
                    setTimeout(initManagerSignaturePad, 200);
                }
                if (document.getElementById('mgr-acc-receipt-submitter-canvas')) {
                    setTimeout(initManagerReceiptPads, 220);
                }
            });
        });
    })();
</script>
@endpush
