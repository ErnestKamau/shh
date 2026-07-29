<div class="acc-wizard-root acc-wizard-root--acceptance lab-surface-theme">
    @if($showModal)
        <div class="acc-wizard-backdrop" tabindex="-1" role="dialog">
            <div class="modal-dialog {{ $activeStep === 'sample_config' ? 'modal-xl' : 'modal-lg' }} acc-wizard-dialog" role="document">
                <div class="modal-content acc-wizard-modal">
                    <div class="acc-wizard-header">
                        <div class="acc-wizard-header-text">
                            <h4 class="acc-wizard-title">
                                <i class="mdi mdi-file-document-edit-outline"></i>
                                Analysis Acceptance
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
                        @if($activeStep === 'sample_config')
                            <section class="acc-wizard-summary mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="acc-wizard-hint mb-1">Customer</p>
                                        <p class="mb-0 font-weight-bold">{{ $customerName ?: '—' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="acc-wizard-hint mb-1">Samples</p>
                                        <p class="mb-0 font-weight-bold">{{ $numberOfSamples }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="acc-wizard-hint mb-1 d-block" for="acceptance-mode-of-work">Mode of work</label>
                                        <select id="acceptance-mode-of-work" class="form-control form-control-sm acc-input" wire:model="modeOfWork">
                                            <option value="Normal">Normal</option>
                                            <option value="Express">Express</option>
                                        </select>
                                        @error('modeOfWork') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <p class="acc-wizard-hint mb-2">
                                            For each sample: select test parameters, choose the lab section for each test, then assign analyst(s) from that section.
                                        </p>
                                        <div class="custom-control custom-checkbox">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="acceptance-shelf-life-testing"
                                                wire:model.live="isShelfLifeTesting"
                                            >
                                            <label class="custom-control-label font-weight-bold" for="acceptance-shelf-life-testing">
                                                Shelf Life Testing
                                            </label>
                                        </div>
                                        <p class="acc-wizard-hint mb-0 mt-1">
                                            When checked, this job is diverted to the Shelf Life Studies module after acceptance (same physical samples are pulled, tested, and returned at each interval).
                                        </p>
                                    </div>
                                </div>
                            </section>

                            @include('livewire.partials.acceptance-sample-config-table')
                        @else
                            <section class="acc-wizard-summary mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="acc-wizard-hint mb-1">Customer</p>
                                        <p class="mb-0 font-weight-bold">{{ $customerName ?: '—' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="acc-wizard-hint mb-1">Samples</p>
                                        <p class="mb-0 font-weight-bold">{{ $numberOfSamples }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="acc-wizard-hint mb-1">Mode of work</p>
                                        <p class="mb-0 font-weight-bold">{{ $modeOfWork }}</p>
                                    </div>
                                </div>
                                @if($isShelfLifeTesting)
                                    <div class="alert alert-info py-2 px-3 mb-0 mt-3">
                                        <i class="mdi mdi-flask-outline"></i>
                                        Shelf Life Testing — this job will go to the Shelf Life Studies module (not the normal sample workflow).
                                    </div>
                                @endif
                            </section>

                            <div class="row">
                                <div class="col-md-12">
                                    <section class="acc-wizard-section">
                                        <h6 class="acc-wizard-section-title">Receiving personnel <span class="text-danger">*</span></h6>
                                        <p class="acc-wizard-hint mb-3">Lab staff confirming physical receipt of samples.</p>

                                        <div class="form-group">
                                            <label class="acc-label">Name</label>
                                            <input type="text" class="form-control acc-input" wire:model="receivingPersonName">
                                            @error('receivingPersonName') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="acc-label">Received date &amp; time</label>
                                            <input type="datetime-local" class="form-control acc-input" wire:model="receivedAt">
                                            @error('receivedAt') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <label class="acc-label d-block">Signature</label>
                                        <div class="acc-signature-pad" wire:ignore>
                                            <canvas id="acceptance-receiving-signature-canvas"></canvas>
                                            <div class="acc-signature-actions">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="acceptance-receiving-sign-clear">Clear</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="acceptance-receiving-signature-input" wire:model="receivingPersonSignature">
                                        @error('receivingPersonSignature') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                                    </section>
                                </div>

                                <div class="col-md-6">
                                    <section class="acc-wizard-section">
                                        <h6 class="acc-wizard-section-title">Customer contact <span class="text-danger">*</span></h6>
                                        <p class="acc-wizard-hint mb-3">Person authorising the analysis request.</p>

                                        <div class="form-group">
                                            <label class="acc-label">Contact</label>
                                            <select class="form-control acc-input" wire:model.live="selectedCustomerContactId">
                                                <option value="">Select contact...</option>
                                                @foreach($customerContactOptions as $contact)
                                                    <option value="{{ $contact['id'] }}">{{ $contact['label'] }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedCustomerContactId') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="acc-label">Signer name</label>
                                            <input type="text" class="form-control acc-input" wire:model="customerSignerName">
                                            @error('customerSignerName') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="acc-label">Date</label>
                                            <input type="date" class="form-control acc-input" wire:model="customerSignedAt">
                                            @error('customerSignedAt') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <label class="acc-label d-block">Signature</label>
                                        @if($customerSignature !== '')
                                            <div class="mb-2">
                                                <img src="{{ $customerSignature }}" alt="Saved contact signature" style="max-height: 120px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px; background: #fff; width: 100%; object-fit: contain;">
                                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" wire:click="clearSavedCustomerSignature">
                                                    Clear and sign new
                                                </button>
                                            </div>
                                            <input type="hidden" id="acceptance-customer-signature-input" wire:model="customerSignature">
                                        @else
                                            <div class="acc-signature-pad" wire:ignore>
                                                <canvas id="acceptance-customer-signature-canvas"></canvas>
                                                <div class="acc-signature-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="acceptance-customer-sign-clear">Clear</button>
                                                </div>
                                            </div>
                                            <input type="hidden" id="acceptance-customer-signature-input" wire:model="customerSignature">
                                        @endif
                                        @error('customerSignature') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                                    </section>
                                </div>
                            </div>

                            <section class="acc-wizard-checkboxes mt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="acc-checkbox-label d-flex align-items-start mb-2">
                                            <input type="checkbox" class="mr-2 mt-1" wire:model="clientInstructionClear">
                                            <span>Are client`s instructions clear?</span>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="acc-checkbox-label d-flex align-items-start mb-2">
                                            <input type="checkbox" class="mr-2 mt-1" wire:model="labCapable">
                                            <span>Is the laboratory capable of performing the requested tests?</span>
                                        </label>
                                    </div>
                                </div>
                            </section>
                        @endif
                    </div>

                    <div class="acc-wizard-footer">
                        @if($activeStep === 'signatures')
                            <button type="button" class="btn btn-light acc-btn-ghost mr-auto" wire:click="goBackToSampleConfig">Back</button>
                        @endif
                        <button type="button" class="btn btn-light acc-btn-ghost" wire:click="closeWizard">Close</button>
                        @if($activeStep === 'sample_config')
                            <button type="button" class="btn acc-btn-success" wire:click="saveSampleConfigAndContinue" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveSampleConfigAndContinue" class="spinner-border spinner-border-sm mr-1"></span>
                                Continue
                            </button>
                        @else
                            <button type="button" class="btn acc-btn-success" id="acceptance-dual-sign-submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="submitDualAccept" class="spinner-border spinner-border-sm mr-1"></span>
                                Accept samples
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.partials.acc-wizard-core-styles')

    <style>
        .acc-wizard-root--acceptance {
            --acc-accent: #3b5fc0;
            --acc-accent-dark: #2f4da0;
            --acc-border: #e2e8f0;
            --acc-muted: #64748b;
            --acc-text: #0f172a;
        }

        .acc-wizard-root--acceptance .acc-wizard-backdrop {
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

        .acc-wizard-root--acceptance .acc-wizard-dialog { margin: 0; }
        .acc-wizard-root--acceptance .acc-wizard-dialog.modal-lg { max-width: 920px; }
        .acc-wizard-root--acceptance .acc-wizard-dialog.modal-xl { max-width: 1140px; }

        .acc-wizard-root--acceptance .acc-wizard-modal {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
        }

        .acc-wizard-root--acceptance .acc-wizard-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background: #fff;
            color: var(--acc-text);
            border-bottom: 1px solid var(--acc-border);
        }

        .acc-wizard-root--acceptance .acc-wizard-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .acc-wizard-root--acceptance .acc-wizard-close {
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

        .acc-wizard-root--acceptance .acc-wizard-close:hover {
            background: #e2e8f0;
            color: var(--acc-text);
        }

        .acc-wizard-root--acceptance .acc-wizard-body {
            padding: 1.25rem 1.5rem;
            background: #fff;
            max-height: min(75vh, 720px);
            overflow-y: auto;
        }

        .acc-wizard-root--acceptance .acc-wizard-summary {
            padding: 0.85rem 1rem;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid var(--acc-border);
        }

        .acc-wizard-root--acceptance .acc-wizard-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .acc-wizard-root--acceptance .acc-wizard-hint {
            font-size: 0.82rem;
            color: var(--acc-muted);
            margin: 0;
        }

        .acc-wizard-root--acceptance .acc-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--acc-muted);
            margin-bottom: 0.35rem;
        }

        .acc-wizard-root--acceptance .acc-input {
            border-radius: 8px;
            border-color: #cbd5e1;
        }

        .acc-wizard-root--acceptance .acc-config-readonly {
            display: block;
            padding: 0.35rem 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--acc-text);
        }

        .acc-wizard-root--acceptance .acc-signature-pad {
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 0.5rem;
        }

        .acc-wizard-root--acceptance .acc-signature-pad canvas {
            width: 100%;
            height: 160px;
            display: block;
            border-radius: 6px;
        }

        .acc-wizard-root--acceptance .acc-signature-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.35rem;
        }

        .acc-wizard-root--acceptance .acc-wizard-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            background: #fff;
            border-top: 1px solid var(--acc-border);
        }

        .acc-wizard-root--acceptance .acc-btn-success {
            background: #059669;
            border-color: #059669;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
        }

        .acc-wizard-root--acceptance .acc-btn-success:hover {
            background: #047857;
            border-color: #047857;
            color: #fff;
        }

        .acc-wizard-root--acceptance .acc-btn-ghost { border-radius: 8px; }

        .acc-wizard-root--acceptance .acc-wizard-checkboxes {
            padding: 0.85rem 1rem;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid var(--acc-border);
        }

        .acc-wizard-root--acceptance .acc-checkbox-label {
            font-size: 0.875rem;
            color: var(--acc-text);
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 0;
        }
    </style>
</div>

@push('scripts')
<script>
    (function () {
        let receivingSignaturePad = null;
        let customerSignaturePad = null;
        let signaturePadsInitialized = false;

        function initSignaturePad(canvasId, propertyName, clearBtnId, existingDataUrl) {
            const canvas = document.getElementById(canvasId);
            const clearBtn = document.getElementById(clearBtnId);

            if (!canvas || typeof SignaturePad === 'undefined') {
                return null;
            }

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });

            if (existingDataUrl) {
                try {
                    pad.fromDataURL(existingDataUrl);
                } catch (e) {}
            }

            pad.addEventListener('endStroke', function () {
                @this.set(propertyName, pad.isEmpty() ? '' : pad.toDataURL('image/png'));
            });

            if (clearBtn) {
                clearBtn.onclick = function () {
                    pad.clear();
                    @this.set(propertyName, '');
                };
            }

            return pad;
        }

        function initSignaturePads() {
            signaturePadsInitialized = false;

            if (!document.getElementById('acceptance-receiving-signature-canvas')) {
                return;
            }

            receivingSignaturePad = initSignaturePad(
                'acceptance-receiving-signature-canvas',
                'receivingPersonSignature',
                'acceptance-receiving-sign-clear',
                @this.receivingPersonSignature
            );

            const customerCanvas = document.getElementById('acceptance-customer-signature-canvas');
            if (customerCanvas) {
                customerSignaturePad = initSignaturePad(
                    'acceptance-customer-signature-canvas',
                    'customerSignature',
                    'acceptance-customer-sign-clear',
                    @this.customerSignature
                );
            } else {
                customerSignaturePad = null;
            }

            signaturePadsInitialized = true;
        }

        document.addEventListener('livewire:init', function () {
            Livewire.on('acceptance-wizard-opened', function () {
                signaturePadsInitialized = false;
            });

            Livewire.on('acceptance-wizard-signatures-step', function () {
                signaturePadsInitialized = false;
                setTimeout(initSignaturePads, 300);
            });

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('#acceptance-dual-sign-submit');
                if (!btn) {
                    return;
                }

                if (!receivingSignaturePad || receivingSignaturePad.isEmpty()) {
                    alert('Please provide the receiving personnel signature.');
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return;
                }

                const savedCustomerSignature = @this.get('customerSignature') || '';
                const hasCustomerSignature = (customerSignaturePad && !customerSignaturePad.isEmpty())
                    || (typeof savedCustomerSignature === 'string' && savedCustomerSignature.startsWith('data:image'));

                if (!hasCustomerSignature) {
                    alert('Please provide the customer contact signature.');
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return;
                }

                @this.set('receivingPersonSignature', receivingSignaturePad.toDataURL('image/png'));
                if (customerSignaturePad && !customerSignaturePad.isEmpty()) {
                    @this.set('customerSignature', customerSignaturePad.toDataURL('image/png'));
                }
                e.preventDefault();
                @this.call('submitDualAccept');
            }, true);
        });
    })();
</script>
@endpush
