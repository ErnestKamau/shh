<div>
    @if(session('request_view_message'))
        <div class="request-view-alerts">
            <div class="alert alert-success mb-3">{{ session('request_view_message') }}</div>
        </div>
    @endif

    @if(is_array(session('apply_batches_warnings')) && count(session('apply_batches_warnings')) > 0)
        <div class="request-view-alerts">
            <div class="alert alert-warning mb-3">
                <strong>Please note:</strong>
                <ul class="mb-0 pl-3 mt-2">
                    @foreach(session('apply_batches_warnings') as $w)
                        <li>{{ $w }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @include('livewire.submission-forms.request-view.cards.header', [
        'viewHeader' => $viewHeader,
        'nextStepActions' => $nextStepActions,
        'quotationHeader' => $quotationHeader ?? null,
        'quotationPendingApproval' => $quotationPendingApproval ?? false,
        'quotationApprovedReadyToSend' => $quotationApprovedReadyToSend ?? false,
        'canApproveQuotation' => $canApproveQuotation ?? false,
        'commercialEnquiry' => $commercialEnquiry ?? null,
    ])

    @if($commercialEnquiry && ($commercialEnquiry->isQuotationUnderReview() || $commercialEnquiry->hasCustomerFeedback()))
        <div class="alert alert-warning mb-3">
            <strong>
                <i class="mdi mdi-comment-alert-outline"></i>
                @if($commercialEnquiry->isQuotationUnderReview())
                    Customer sent this quotation back for review
                @else
                    Customer feedback on quotation
                @endif
            </strong>
            @if($commercialEnquiry->hasCustomerFeedback())
                <div class="mt-2 mb-0" style="white-space: pre-wrap;">{{ $commercialEnquiry->customerFeedbackNotes() }}</div>
            @else
                <div class="mt-2 mb-0 text-muted">No review reason was recorded.</div>
            @endif
        </div>
    @endif

    <div class="rv-console">
        @include('livewire.submission-forms.request-view.cards.context-rail', [
            'contextRail' => $contextRail,
            'canEditSampleRows' => $canEditSampleRows,
        ])

        <div class="rv-console-canvas">
            @if($workflowForms->count() > 0)
                <div class="workflow-board-panel mb-3">
                    <div class="workflow-board-panel-header">
                        <h5><i class="mdi mdi-file-document-multiple-outline"></i> Workflow decision forms</h5>
                    </div>
                    <div class="workflow-board-panel-body flush-top">
                        <div class="table-responsive">
                            <table class="table table-hover workflow-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Form type</th>
                                        <th>Reference</th>
                                        <th>Submitted at</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workflowForms as $workflowForm)
                                        <tr>
                                            <td>{{ $workflowForm->form_type === 'laboratory_analysis_acceptance' ? 'Laboratory Analysis Acceptance' : 'Sample Rejection' }}</td>
                                            <td>{{ $workflowForm->request_reference ?: ($workflowForm->batch_code ?: '—') }}</td>
                                            <td>{{ optional($workflowForm->submitted_at)->format('Y-m-d H:i') ?: optional($workflowForm->created_at)->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @if($workflowForm->pdf_path)
                                                    <a href="{{ $workflowForm->pdf_path }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">PDF</a>
                                                @else
                                                    <span class="text-muted small">PDF unavailable</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="workflow-board-panel batch-tabs-panel mb-0">
                <div class="workflow-board-panel-body flush-top">
                    <ul class="nav batch-nav-tabs mb-0" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $activeTab === 'tests' ? 'active' : '' }}" wire:click="setTab('tests')">
                                <i class="mdi mdi-flask-outline"></i> Tests
                                <span class="badge">{{ $testSamplesCard['count'] }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $activeTab === 'sample_collection' ? 'active' : '' }}" wire:click="setTab('sample_collection')">
                                <i class="mdi mdi-map-marker-radius-outline"></i> Sample collection
                                <span class="badge">{{ count($contextRail['sample_collection'] ?? []) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $activeTab === 'notes' ? 'active' : '' }}" wire:click="setTab('notes')">
                                <i class="mdi mdi-comment-text-outline"></i> Notes
                                <span class="badge">{{ $instance->notes->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $activeTab === 'attachments' ? 'active' : '' }}" wire:click="setTab('attachments')">
                                <i class="mdi mdi-paperclip"></i> Attachments
                                <span class="badge">{{ $attachmentInstances->count() + $batchAttachments->count() + (isset($customAttachments) ? $customAttachments->count() : 0) + (isset($formMediaAttachments) ? $formMediaAttachments->count() : 0) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $activeTab === 'custody' ? 'active' : '' }}" wire:click="setTab('custody')">
                                <i class="mdi mdi-sitemap"></i> Chain of custody
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        @if($activeTab === 'tests')
                            @include('livewire.submission-forms.request-view.tabs.tests', [
                                'testSamplesCard' => $testSamplesCard,
                                'canEditSampleRows' => $canEditSampleRows,
                                'acceptanceForm' => $acceptanceForm,
                                'boardStatus' => $boardStatus,
                                'attachmentInstances' => $attachmentInstances,
                                'instance' => $instance,
                                'linkedBatchesOutOfSyncWithForm' => $linkedBatchesOutOfSyncWithForm,
                            ])
                        @elseif($activeTab === 'sample_collection')
                            <div class="tab-pane-pad">
                                @include('livewire.submission-forms.request-view.tabs.sample-collection', [
                                    'contextRail' => $contextRail,
                                    'canEditSampleRows' => $canEditSampleRows,
                                ])
                            </div>
                        @elseif($activeTab === 'notes')
                            <div class="tab-pane-pad">
                                @include('livewire.submission-forms.request-view.tabs.notes')
                            </div>
                        @elseif($activeTab === 'attachments')
                            <div class="tab-pane-pad">
                                @include('livewire.submission-forms.request-view.tabs.attachments', [
                                    'attachmentInstances' => $attachmentInstances,
                                    'batchAttachments' => $batchAttachments,
                                    'customAttachments' => $customAttachments,
                                    'formMediaAttachments' => $formMediaAttachments,
                                ])
                            </div>
                        @elseif($activeTab === 'custody')
                            <div class="tab-pane-pad">
                                @include('livewire.submission-forms.request-view.tabs.chain-of-custody', [
                                    'custodyTimeline' => $this->custodyTimeline,
                                    'custodyEnteredLab' => $this->custodyEnteredLab,
                                ])
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewire('sampleworkflow.process-enquiry-wizard')
    @livewire('sampleworkflow.sample-rejection-wizard')
    @livewire('sampleworkflow.acceptance-form-wizard')

    @include('livewire.submission-forms.request-view.partials.sample-row-edit-modal')
    @include('livewire.submission-forms.request-view.partials.trf-view-modal')
    @include('livewire.submission-forms.request-view.partials.approve-quotation-modal')

    {{-- Keep receive/walk-in capture in a Bootstrap modal so it is not inline on the read-only view. --}}
    <div id="receive-sample-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div id="receive-sample-modal-dialog" class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content receive-sample-modal-content border-0 shadow">
                <div class="modal-header receive-sample-modal-header border-0">
                    <div>
                        <h5 class="modal-title mb-0" id="receive-sample-modal-title-wrap">
                            <i id="receive-sample-modal-icon" class="mdi mdi-clipboard-text text-primary mr-2"></i>
                            <span id="receive-sample-modal-title-text">Test Request Form</span>
                        </h5>
                        <p id="receive-sample-modal-subtitle" class="text-muted small mb-0 mt-1 d-none"></p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @livewire('sampleworkflow.receive-sample-request', key('request-view-receive-'.$instance->id))
                </div>
            </div>
        </div>
    </div>

    @teleport('body')
    <div id="print-sample-labels-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-printer mr-1"></i> Print Labels
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 text-muted">Choose which label to generate. Each option opens in a new tab.</p>
                    <div class="list-group">
                        <button type="button"
                            class="list-group-item list-group-item-action js-open-sample-label text-left"
                            data-label-url="{{ route('submission-forms.instances.sample-collection-label', ['instance' => $instance->id, 'type' => 'collection']) }}">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-tag-outline mr-2 text-primary" aria-hidden="true"></i>
                                <div>
                                    <strong class="d-block">Sample Collection Label</strong>
                                    <small class="text-muted">Collection details with TRF barcode</small>
                                </div>
                            </div>
                        </button>
                        <button type="button"
                            class="list-group-item list-group-item-action js-open-sample-label text-left"
                            data-label-url="{{ route('submission-forms.instances.sample-collection-label', ['instance' => $instance->id, 'type' => 'registration']) }}">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-barcode mr-2 text-primary" aria-hidden="true"></i>
                                <div>
                                    <strong class="d-block">Registration Label with barcode</strong>
                                    <small class="text-muted">Job / sample registration label for scanning</small>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endteleport

    @if($showTrfOrientationModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45);">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-document-outline mr-1"></i>
                            Generate Test Request Form
                        </h5>
                        <button type="button" class="close" wire:click="closeTrfOrientationModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Choose page orientation. Official AMSPEC defaults use
                            <strong>{{ $defaultTrfPdfOrientation }}</strong> for this form type.
                        </p>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small d-block mb-2">Orientation</label>
                            <div class="d-flex flex-wrap" style="gap: 1rem;">
                                <label class="mb-0 d-flex align-items-center">
                                    <input type="radio" class="mr-2" wire:model="trfPdfOrientation" value="landscape">
                                    Landscape
                                    @if($defaultTrfPdfOrientation === 'landscape')
                                        <span class="badge badge-light border ml-1">default</span>
                                    @endif
                                </label>
                                <label class="mb-0 d-flex align-items-center">
                                    <input type="radio" class="mr-2" wire:model="trfPdfOrientation" value="portrait">
                                    Portrait
                                    @if($defaultTrfPdfOrientation === 'portrait')
                                        <span class="badge badge-light border ml-1">default</span>
                                    @endif
                                </label>
                            </div>
                            @error('trfPdfOrientation')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>
                        @if($trfPdfOrientation !== $defaultTrfPdfOrientation)
                            <div class="alert alert-warning py-2 small mb-0">
                                This form is designed for {{ $defaultTrfPdfOrientation }}. Portrait/landscape override uses a spaced layout suited to the chosen page.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeTrfOrientationModal">Cancel</button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="confirmGenerateTestRequestFormReport"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="confirmGenerateTestRequestFormReport">
                                <i class="mdi mdi-file-pdf-box"></i> Generate PDF
                            </span>
                            <span wire:loading wire:target="confirmGenerateTestRequestFormReport">
                                Generating…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showQuotationAcceptanceModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $poCaptureBlanketOnly ? 'Use blanket PO' : ($quotationAcceptancePoOnly ? 'Record PO' : 'Customer quotation acceptance') }}
                        </h5>
                        <button type="button" class="close" wire:click="closeQuotationAcceptanceModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if(! $quotationAcceptancePoOnly)
                            <p class="small text-muted mb-3">Capture the customer signature to accept this quotation. The signature will appear on the quotation PDF.</p>
                            <div class="form-group">
                                <label>Customer contact <span class="text-danger">*</span></label>
                                <select class="form-control" wire:model.live="quotationAcceptanceContactId">
                                    <option value="">Select contact...</option>
                                    @foreach($quotationAcceptanceContactOptions as $contact)
                                        <option value="{{ $contact['id'] }}">{{ $contact['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('quotationAcceptanceContactId')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                            <label class="d-block">Signature <span class="text-danger">*</span></label>
                            <div class="border rounded p-2 bg-white" wire:ignore>
                                <canvas id="request-view-quotation-acceptance-canvas" style="width: 100%; height: 160px; touch-action: none;"></canvas>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="request-view-quotation-acceptance-clear">Clear</button>
                                </div>
                            </div>
                            @error('quotationAcceptanceSignature')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                            <hr class="my-3">
                        @endif

                        @include('livewire.commercial.partials.enquiry-po-capture', ['poNumberInputId' => 'clientPoNumber'])

                        @if($this->poCaptureAcceptsFile)
                        <div class="form-group">
                            <label for="quotationAcceptanceAttachmentType">Attachment type (optional)</label>
                            <select id="quotationAcceptanceAttachmentType" class="form-control" wire:model="quotationAcceptanceAttachmentType">
                                <option value="">No attachment</option>
                                <option value="Test Request Form">Test Request Form</option>
                                <option value="Quotation">Quotation</option>
                                <option value="Purchase Order">Purchase Order</option>
                                <option value="Invoice">Invoice</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            @include('layouts.lab.partials.ls-ui.upload.ls-upload-files', [
                                'title' => 'Upload attachment (optional)',
                                'subtitle' => 'Use this for a purchase order or supporting document. Signature is still required to accept.',
                                'hint' => 'JPEG, PNG, or PDF, up to 10 MB.',
                                'accept' => '.pdf,.png,.jpg,.jpeg',
                                'showUrlImport' => false,
                                'showDemoFiles' => false,
                                'showHeadClose' => false,
                                'multiple' => false,
                                'inputId' => 'quotationAcceptanceAttachment',
                                'wireModel' => 'quotationAcceptanceAttachment',
                                'errorBag' => 'quotationAcceptanceAttachment',
                            ])
                            <div wire:loading wire:target="quotationAcceptanceAttachment" class="small text-muted mt-1">Uploading…</div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeQuotationAcceptanceModal">Cancel</button>
                        @if($quotationAcceptancePoOnly)
                            <button type="button" class="btn btn-primary" wire:click="submitPoAndReadyForReception" wire:loading.attr="disabled">
                                Mark ready for reception
                            </button>
                        @else
                            <button type="button" class="btn btn-primary" id="request-view-quotation-acceptance-submit">Accept quotation</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.partials.walk-in-trf-ls-theme')
</div>

@script
<script>
    if (!window.__printSampleLabelsBound) {
        window.__printSampleLabelsBound = true;

        window.closePrintSampleLabelsModal = function () {
            const modal = document.getElementById('print-sample-labels-modal');
            if (!modal) {
                return;
            }

            if (typeof window.jQuery === 'function' && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery(modal).modal('hide');
                return;
            }

            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        };

        document.addEventListener('click', function (event) {
            const labelOption = event.target.closest('#print-sample-labels-modal .js-open-sample-label');
            if (!labelOption) {
                return;
            }

            event.preventDefault();
            const url = labelOption.getAttribute('data-label-url');
            if (url) {
                window.open(url, '_blank');
            }
            window.closePrintSampleLabelsModal();
        });
    }

    $wire.on('open-test-request-pdf', ({ url }) => {
        if (url) {
            window.open(url, '_blank');
        }
    });

    Livewire.on('acceptance-form-completed', (event) => {
        const redirectUrl = event?.redirectUrl ?? event?.detail?.redirectUrl;
        if (redirectUrl) {
            window.location.href = redirectUrl;
            return;
        }
        window.location.reload();
    });

    Livewire.on('sample-rejection-completed', () => {
        window.location.reload();
    });

    Livewire.on('show-receive-sample-modal', (payload) => {
        const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});
        if (data.physicalCheckIn ?? false) {
            return;
        }

        if (typeof window.$ === 'function') {
            $('#receive-sample-modal').modal('show');
        }
    });

    Livewire.on('hide-receive-sample-modal', () => {
        if (typeof window.$ === 'function') {
            $('#receive-sample-modal').modal('hide');
        }
    });

    let requestViewQuotationPad = null;
    let requestViewQuotationPadToken = 0;

    function scheduleRequestViewQuotationPadInit(callback) {
        requestAnimationFrame(function () {
            requestAnimationFrame(callback);
        });
    }

    function resetRequestViewQuotationPad() {
        requestViewQuotationPadToken += 1;
        requestViewQuotationPad = null;
        const canvas = document.getElementById('request-view-quotation-acceptance-canvas');
        if (canvas) {
            delete canvas.dataset.quotationAcceptancePadReady;
        }
    }

    function ensureSignaturePadLoaded() {
        if (typeof SignaturePad !== 'undefined') {
            return Promise.resolve();
        }

        if (!window.__signaturePadLoader) {
            window.__signaturePadLoader = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        return window.__signaturePadLoader;
    }

    function sizeRequestViewQuotationCanvas(canvas) {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const width = Math.max(canvas.clientWidth || canvas.offsetWidth || 0, 1);
        const height = Math.max(canvas.clientHeight || canvas.offsetHeight || 160, 1);
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        canvas.width = Math.floor(width * ratio);
        canvas.height = Math.floor(height * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    }

    async function applyRequestViewQuotationSignature(signature, token) {
        if (!requestViewQuotationPad || token !== requestViewQuotationPadToken) {
            return;
        }

        requestViewQuotationPad.clear();
        if (signature && String(signature).startsWith('data:image/')) {
            try {
                await requestViewQuotationPad.fromDataURL(String(signature));
            } catch (e) {}
        }
    }

    async function initRequestViewQuotationPad(initialSignature) {
        const canvas = document.getElementById('request-view-quotation-acceptance-canvas');
        const clearBtn = document.getElementById('request-view-quotation-acceptance-clear');
        if (!canvas || typeof SignaturePad === 'undefined') {
            return;
        }

        if (canvas.dataset.quotationAcceptancePadReady === '1' && requestViewQuotationPad) {
            return;
        }

        const token = ++requestViewQuotationPadToken;
        const existingStrokeData = (requestViewQuotationPad && !requestViewQuotationPad.isEmpty())
            ? requestViewQuotationPad.toData()
            : null;

        sizeRequestViewQuotationCanvas(canvas);
        requestViewQuotationPad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255,255,255)',
            penColor: 'rgb(0,0,0)',
        });
        canvas.dataset.quotationAcceptancePadReady = '1';

        if (clearBtn) {
            clearBtn.onclick = function () {
                if (!requestViewQuotationPad) {
                    return;
                }
                requestViewQuotationPad.clear();
            };
        }

        if (existingStrokeData && existingStrokeData.length) {
            try {
                requestViewQuotationPad.fromData(existingStrokeData);
            } catch (e) {}
            return;
        }

        await applyRequestViewQuotationSignature(initialSignature || '', token);
    }

    if (!window.__requestViewQuotationAcceptancePadBound) {
        window.__requestViewQuotationAcceptancePadBound = true;

        Livewire.on('quotation-acceptance-modal-opened', (payload) => {
            const data = payload?.detail ?? payload ?? {};
            const signature = data.signature ?? data[0]?.signature ?? '';
            resetRequestViewQuotationPad();
            ensureSignaturePadLoaded()
                .then(() => scheduleRequestViewQuotationPadInit(() => initRequestViewQuotationPad(signature)))
                .catch(() => {});
        });

        Livewire.on('quotation-acceptance-modal-closed', () => {
            resetRequestViewQuotationPad();
        });

        Livewire.on('quotation-acceptance-signature-changed', (payload) => {
            const data = payload?.detail ?? payload ?? {};
            const signature = data.signature ?? data[0]?.signature ?? '';
            const token = requestViewQuotationPadToken;
            scheduleRequestViewQuotationPadInit(() => applyRequestViewQuotationSignature(signature, token));
        });

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('#request-view-quotation-acceptance-submit');
            if (!btn) {
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            if (!requestViewQuotationPad || requestViewQuotationPad.isEmpty()) {
                alert('Please provide the customer signature.');
                return;
            }

            $wire.call('submitQuotationAcceptanceSignature', requestViewQuotationPad.toDataURL('image/png'));
        }, true);
    }

    (function initSampleRowEditModalUi() {
        if (window.__sampleRowEditModalUiBound) {
            return;
        }
        window.__sampleRowEditModalUiBound = true;

        const preloadTinyMce = () => {
            if (typeof tinymce !== 'undefined' || document.querySelector('script[data-rft-tinymce]')) {
                return;
            }

            const script = document.createElement('script');
            script.src = '/tinymce/tinymce.min.js';
            script.dataset.rftTinymce = '1';
            document.head.appendChild(script);
        };

        preloadTinyMce();

        const modalDialog = () => document.querySelector('.rv-sample-row-edit-modal .rv-sample-row-edit-dialog');
        let sampleRowModalPrepared = false;
        const setModalLoading = (loading) => {
            const dialog = modalDialog();
            if (!dialog) {
                return;
            }

            dialog.classList.toggle('is-ready', !loading);
            dialog.setAttribute('aria-busy', loading ? 'true' : 'false');
        };
        const revealPreparedModal = () => {
            if (!sampleRowModalPrepared || !modalDialog()) {
                return;
            }

            setModalLoading(false);
        };

        const flushSampleRowRichText = () => {
            if (typeof tinymce === 'undefined') {
                return;
            }

            tinymce.triggerSave();
            document.querySelectorAll('.rv-sample-row-edit-modal .sf-rich-text-livewire textarea[id]').forEach((textarea) => {
                const editor = tinymce.get(textarea.id);
                if (!editor) {
                    return;
                }

                const alpineRoot = textarea.closest('[x-data]');
                if (!alpineRoot || !window.Alpine) {
                    return;
                }

                const state = Alpine.$data(alpineRoot);
                if (state?.wireKey && typeof $wire?.set === 'function') {
                    $wire.set(state.wireKey, editor.getContent(), false);
                }
            });
        };

        const flushSampleRowSelect2 = () => {
            if (!window.jQuery?.fn?.select2) {
                return;
            }

            window.jQuery('.rv-sample-row-edit-modal select.livewire-select2').each(function () {
                const $el = window.jQuery(this);
                const wireField = $el.data('wireField');
                if (!wireField || typeof $wire?.set !== 'function') {
                    return;
                }

                const val = $el.prop('multiple') ? ($el.val() || []) : ($el.val() || '');
                $wire.set(wireField, val, false);
            });
        };

        const bindSelect2LivewireSync = ($el) => {
            $el.off('change.sampleRowEditSync select2:select.sampleRowEditSync select2:unselect.sampleRowEditSync select2:clear.sampleRowEditSync')
                .on('change.sampleRowEditSync select2:select.sampleRowEditSync select2:unselect.sampleRowEditSync select2:clear.sampleRowEditSync', function () {
                    const $select = window.jQuery(this);
                    const wireField = $select.data('wireField');
                    const isLive = String($select.data('selectLive')) === '1';

                    if (!wireField || typeof $wire?.set !== 'function') {
                        return;
                    }

                    const val = $select.prop('multiple') ? ($select.val() || []) : ($select.val() || '');
                    $wire.set(wireField, val, isLive);
                });
        };

        const readSelectedValues = (select) => {
            const raw = select.dataset.selectedValues;
            if (!raw) {
                return select.multiple ? [] : [''];
            }

            try {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    return parsed.map((value) => String(value));
                }
            } catch (error) {
                // ignore malformed JSON
            }

            return select.multiple ? [] : [''];
        };

        // Gallery pattern: clamp zero-size inline search; drive filtering via .ls-dd-search only.
        // Do NOT use Select2 DropdownSearch adapter for LS multi (avoids dual search bars).
        const clampLsSelect2Search = ($el) => {
            const $container = $el.next('.select2-container');
            if (! $container.length) {
                return;
            }
            $container.find('.select2-search--inline .select2-search__field').attr(
                'style',
                'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;opacity:0!important;position:absolute!important;left:-9999px!important;'
            );
            $container.css({ maxWidth: '100%', overflow: 'hidden' });
        };

        const wireLsMultiDropdownSearch = ($el) => {
            $el.off('select2:open.lsDdSearch select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch')
                .on('select2:open.lsDdSearch', function () {
                    clampLsSelect2Search($el);

                    const select2Instance = $el.data('select2');
                    // Gallery targets .select2-dropdown (inner shell), not the AttachBody wrapper.
                    let $dropdown = window.jQuery();
                    if (select2Instance?.$dropdown?.length) {
                        $dropdown = select2Instance.$dropdown.find('.select2-dropdown');
                        if (! $dropdown.length && select2Instance.$dropdown.hasClass('select2-dropdown')) {
                            $dropdown = select2Instance.$dropdown;
                        }
                    }
                    if (! $dropdown.length) {
                        const $parent = $el.closest('.rv-modal');
                        $dropdown = ($parent.length ? $parent : window.jQuery(document.body))
                            .find('.select2-container--open .select2-dropdown')
                            .last();
                    }
                    if (! $dropdown.length) {
                        return;
                    }

                    $dropdown.addClass('ls-select2-dropdown-search');
                    // Never show native dropdown search if present.
                    $dropdown.find('.select2-search--dropdown').attr(
                        'style',
                        'display:none!important;height:0!important;padding:0!important;margin:0!important;border:0!important;overflow:hidden!important;'
                    );

                    const $existing = $dropdown.find('.ls-dd-search');
                    if ($existing.length) {
                        $existing.find('input').val('').trigger('focus');
                        return;
                    }

                    const $box = window.jQuery(
                        '<div class="ls-dd-search">' +
                            '<i class="mdi mdi-magnify" aria-hidden="true"></i>' +
                            '<input type="search" placeholder="Search…" autocomplete="off">' +
                        '</div>'
                    );
                    $dropdown.prepend($box);

                    const $input = $box.find('input');
                    $input.on('input keyup', function () {
                        const q = $input.val();
                        // Single: native dropdown search field; Multi (gallery): clamped inline search.
                        let $hidden = $dropdown.find('.select2-search--dropdown .select2-search__field');
                        if (! $hidden.length && select2Instance?.$selection) {
                            $hidden = select2Instance.$selection.find('.select2-search__field');
                        }
                        if (! $hidden.length) {
                            $hidden = window.jQuery('.select2-container--open .select2-search--inline .select2-search__field');
                        }
                        $hidden.val(q).trigger('input').trigger('keyup');
                    });

                    window.setTimeout(function () {
                        $input.trigger('focus');
                    }, 0);
                })
                .on('select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch', function () {
                    clampLsSelect2Search($el);
                });
        };

        const initOneSelect2 = ($el) => {
            if ($el.data('select2')) {
                $el.off('.rvTrfSelect2');
                $el.off('.lsDdSearch');
                $el.select2('destroy');
            }

            const isMultiple = !! $el.prop('multiple');
            const isLsMultiSearch = isMultiple && (
                $el.data('ls-multi-dropdown-search')
                || $el.hasClass('ls-select2-multi-dropdown-search-el')
                || $el.hasClass('ls-select2-multi-columns-el')
                || $el.data('ls-multi-columns')
            );
            const isLsSingleDdSearch = ! isMultiple && (
                $el.data('ls-single-dropdown-search')
                || $el.hasClass('ls-select2-single-dropdown-search-el')
            );
            const options = {
                placeholder: $el.data('placeholder') || 'Select an option',
                width: '100%',
                allowClear: ! isMultiple,
                closeOnSelect: ! isMultiple,
                dropdownParent: $el.closest('.rv-modal').length ? $el.closest('.rv-modal') : window.jQuery(document.body),
            };

            // Gallery pattern: .ls-dd-search (magnify) inside open dropdown list.
            if (isLsMultiSearch || isLsSingleDdSearch) {
                options.dropdownCssClass = 'ls-select2-dropdown-search';
            }

            if (isLsSingleDdSearch) {
                options.closeOnSelect = true;
                options.allowClear = true;
            }

            if ($el.data('ls-multi-columns') || $el.hasClass('ls-select2-multi-columns-el')) {
                options.escapeMarkup = function (markup) { return markup; };
                options.templateResult = function (data) {
                    if (! data.id) {
                        return data.text;
                    }
                    const $opt = window.jQuery(data.element);
                    const method = String($opt.attr('data-meta-method') || '').trim();
                    const lab = String($opt.attr('data-meta-lab') || '').trim();
                    const metaParts = [];
                    if (method) {
                        metaParts.push('<i class="mdi mdi-flask-outline"></i> ' + window.jQuery('<div>').text(method).html());
                    }
                    if (lab) {
                        metaParts.push('<i class="mdi mdi-domain"></i> ' + window.jQuery('<div>').text(lab).html());
                    }
                    const selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
                    const $row = window.jQuery(
                        '<span class="ls-select2-meta-row ls-select2-meta-row--spread">' +
                            '<span class="ls-select2-meta-row__label"></span>' +
                            '<span class="ls-select2-meta-row__meta"></span>' +
                            (selected ? '<i class="mdi mdi-check" style="color:#2563eb;"></i>' : '') +
                        '</span>'
                    );
                    $row.find('.ls-select2-meta-row__label').text(data.text);
                    $row.find('.ls-select2-meta-row__meta').html(metaParts.join(' · '));
                    return $row;
                };
                options.templateSelection = function (data) {
                    if (! data.id) {
                        return data.text;
                    }
                    const method = String(window.jQuery(data.element).attr('data-meta-method') || '').trim();
                    if (! method) {
                        return data.text;
                    }
                    const $chip = window.jQuery(
                        '<span class="ls-select2-choice-with-meta">' +
                            '<span class="ls-select2-choice-with-meta__label"></span>' +
                            '<span class="ls-select2-choice-with-meta__meta"></span>' +
                        '</span>'
                    );
                    $chip.find('.ls-select2-choice-with-meta__label').text(data.text);
                    $chip.find('.ls-select2-choice-with-meta__meta').text(method);
                    return $chip;
                };
            } else if (isLsMultiSearch) {
                // Checkbox-style rows like gallery multi-dropdown-search.
                options.escapeMarkup = function (markup) { return markup; };
                options.templateResult = function (data) {
                    if (! data.id) {
                        return data.text;
                    }
                    const selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
                    const $row = window.jQuery(
                        '<span class="ls-select2-meta-row">' +
                            '<span class="ls-select2-check"></span>' +
                            '<span class="ls-select2-meta-row__label"></span>' +
                        '</span>'
                    );
                    $row.find('.ls-select2-check').text(selected ? '✓' : '');
                    $row.find('.ls-select2-meta-row__label').text(data.text);
                    return $row;
                };
            }

            $el.select2(options);

            const selectedValues = readSelectedValues($el.get(0));
            if (isMultiple) {
                $el.val(selectedValues).trigger('change.select2');
            } else {
                $el.val(selectedValues[0] ?? '').trigger('change.select2');
            }

            if (isLsMultiSearch || isLsSingleDdSearch) {
                wireLsMultiDropdownSearch($el);
                if (isLsMultiSearch) {
                    clampLsSelect2Search($el);
                    window.setTimeout(() => clampLsSelect2Search($el), 0);
                    window.setTimeout(() => clampLsSelect2Search($el), 50);
                }
            }

            if (isMultiple) {
                const select2Instance = $el.data('select2');
                const fitMultipleSelect2 = () => {
                    const $container = select2Instance?.$container;
                    if (! $container || ! $container.length) {
                        return;
                    }

                    $container.css({ height: 'auto', minHeight: 0 });
                    $container.find('.selection, .select2-selection--multiple, .select2-selection__rendered').css({
                        height: 'auto',
                        minHeight: 0,
                        maxHeight: 'none',
                    });
                    if (isLsMultiSearch) {
                        clampLsSelect2Search($el);
                    }
                };

                fitMultipleSelect2();
                $el.on('select2:open.rvTrfSelect2 select2:close.rvTrfSelect2 select2:select.rvTrfSelect2 select2:unselect.rvTrfSelect2', fitMultipleSelect2);
                window.setTimeout(fitMultipleSelect2, 0);
                window.setTimeout(fitMultipleSelect2, 50);
            }

            bindSelect2LivewireSync($el);
        };

        const initSampleRowEditSelect2 = () => {
            if (!window.jQuery?.fn?.select2) {
                return;
            }

            window.jQuery('.rv-sample-row-edit-modal select.livewire-select2').each(function () {
                initOneSelect2(window.jQuery(this));
            });
        };

        const initTrfSampleCardEnhancements = (rowIndex) => {
            const card = document.querySelector('.rv-trf-sample-card[data-trf-sample-index="' + rowIndex + '"]');
            if (!card || !window.jQuery?.fn?.select2) {
                return;
            }

            window.jQuery(card).find('select.livewire-select2').each(function () {
                initOneSelect2(window.jQuery(this));
            });
        };

        const rebuildSelectOptions = (select, options, selectedValues) => {
            const isMultiple = select.multiple;
            select.innerHTML = '';

            if (!isMultiple) {
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = '— Select —';
                select.appendChild(emptyOption);
            }

            (options || []).forEach((option) => {
                const node = document.createElement('option');
                node.value = String(option.value ?? '');
                node.textContent = String(option.label ?? option.value ?? '');
                select.appendChild(node);
            });

            const $el = window.jQuery(select);
            if ($el.data('select2')) {
                $el.val(isMultiple ? selectedValues : (selectedValues[0] ?? '')).trigger('change.select2');
            } else {
                if (isMultiple) {
                    Array.from(select.options).forEach((option) => {
                        option.selected = selectedValues.includes(option.value);
                    });
                } else {
                    select.value = selectedValues[0] ?? '';
                }
            }
        };

        const refreshSampleRowSelectOptions = (payload) => {
            const detail = payload?.options !== undefined ? payload : (payload?.[0] ?? {});
            const optionsByField = detail.options ?? {};
            const cleared = Array.isArray(detail.cleared) ? detail.cleared : [];

            cleared.forEach((fieldName) => {
                const select = document.getElementById('edit-row-' + fieldName);
                if (!select) {
                    return;
                }

                const fieldOptions = optionsByField[fieldName] ?? [];
                rebuildSelectOptions(select, fieldOptions, select.multiple ? [] : ['']);
            });

            Object.entries(optionsByField).forEach(([fieldName, fieldOptions]) => {
                if (cleared.includes(fieldName)) {
                    return;
                }

                const select = document.getElementById('edit-row-' + fieldName);
                if (!select) {
                    return;
                }

                const $el = window.jQuery(select);
                const current = $el.prop('multiple') ? ($el.val() || []) : [($el.val() || '')];
                rebuildSelectOptions(select, fieldOptions, current);
            });
        };

        const waitForRichTextEditor = () => new Promise((resolve) => {
            const textarea = document.querySelector('.rv-sample-row-edit-modal .sf-rich-text-livewire textarea[id]');
            if (!textarea) {
                resolve();
                return;
            }

            const editorId = textarea.id;
            const finish = () => resolve();

            if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                finish();
                return;
            }

            const onReady = (event) => {
                if (event.detail?.editorId === editorId) {
                    window.removeEventListener('sample-row-rich-text-ready', onReady);
                    finish();
                }
            };

            window.addEventListener('sample-row-rich-text-ready', onReady);
            setTimeout(() => {
                window.removeEventListener('sample-row-rich-text-ready', onReady);
                finish();
            }, 4000);
        });

        const prepareSampleRowEditModal = () => {
            sampleRowModalPrepared = false;
            setModalLoading(true);

            window.requestAnimationFrame(() => {
                initSampleRowEditSelect2();

                Promise.all([
                    waitForRichTextEditor(),
                    new Promise((resolve) => setTimeout(resolve, 80)),
                ]).then(() => {
                    sampleRowModalPrepared = true;
                    setModalLoading(false);
                });
            });
        };

        const bootSampleRowEditModal = () => {
            Livewire.on('sample-row-edit-modal-opened', () => {
                setTimeout(prepareSampleRowEditModal, 100);
            });

            Livewire.on('trf-edit-modal-opened', () => {
                setTimeout(prepareSampleRowEditModal, 100);
            });

            Livewire.on('trf-sample-card-opened', (payload) => {
                const detail = payload?.rowIndex !== undefined ? payload : (payload?.[0] ?? {});
                const rowIndex = detail.rowIndex;
                if (rowIndex === undefined || rowIndex === null) {
                    return;
                }

                // Scoped init only — avoid full-modal loading overlay / Select2 rebuild.
                setTimeout(() => initTrfSampleCardEnhancements(rowIndex), 40);
            });

            Livewire.on('sample-row-edit-modal-closed', () => {
                sampleRowModalPrepared = false;
                setModalLoading(true);

                if (typeof tinymce !== 'undefined') {
                    document.querySelectorAll('.rv-sample-row-edit-modal .sf-rich-text-livewire textarea[id]').forEach((textarea) => {
                        if (tinymce.get(textarea.id)) {
                            tinymce.remove('#' + textarea.id);
                        }
                    });
                }

                if (window.jQuery?.fn?.select2) {
                    window.jQuery('.rv-sample-row-edit-modal select.livewire-select2').each(function () {
                        const $el = window.jQuery(this);
                        if ($el.data('select2')) {
                            try {
                                $el.select2('destroy');
                            } catch (e) {
                                // ignore
                            }
                        }
                    });
                }
            });

            Livewire.on('trf-edit-modal-closed', () => {
                sampleRowModalPrepared = false;
                setModalLoading(true);

                if (typeof tinymce !== 'undefined') {
                    document.querySelectorAll('.rv-sample-row-edit-modal .sf-rich-text-livewire textarea[id]').forEach((textarea) => {
                        if (tinymce.get(textarea.id)) {
                            tinymce.remove('#' + textarea.id);
                        }
                    });
                }

                if (window.jQuery?.fn?.select2) {
                    window.jQuery('.rv-sample-row-edit-modal select.livewire-select2').each(function () {
                        const $el = window.jQuery(this);
                        if ($el.data('select2')) {
                            try {
                                $el.select2('destroy');
                            } catch (e) {
                                // ignore
                            }
                        }
                    });
                }
            });

            Livewire.on('sample-row-select-options-refreshed', (payload) => {
                refreshSampleRowSelectOptions(payload);
                revealPreparedModal();
            });

            if (typeof Livewire.hook === 'function') {
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => revealPreparedModal());
                });
            }

            document.addEventListener('click', (event) => {
                const saveButton = event.target.closest('[data-sample-row-save]');
                if (!saveButton || !saveButton.closest('.rv-sample-row-edit-modal')) {
                    return;
                }

                flushSampleRowSelect2();
                flushSampleRowRichText();
            }, true);
        };

        if (window.Livewire) {
            bootSampleRowEditModal();
        } else {
            document.addEventListener('livewire:init', bootSampleRowEditModal);
        }

        const registerRftSampleDescriptionEditor = () => {
            if (window.__rftSampleDescriptionEditorRegistered || !window.Alpine) {
                return;
            }

            window.__rftSampleDescriptionEditorRegistered = true;

            Alpine.data('rftSampleDescriptionEditor', (config) => ({
                editorId: config.editorId,
                wireKey: config.wireKey,
                rowIndex: config.rowIndex ?? 0,
                init() {
                    this.$nextTick(() => this.mountEditor());
                },
                mountEditor() {
                    if (typeof tinymce === 'undefined') {
                        const existing = document.querySelector('script[data-rft-tinymce]');
                        if (existing) {
                            existing.addEventListener('load', () => this.initTiny(), { once: true });
                            return;
                        }

                        const script = document.createElement('script');
                        script.src = '/tinymce/tinymce.min.js';
                        script.dataset.rftTinymce = '1';
                        script.onload = () => this.initTiny();
                        document.head.appendChild(script);
                        return;
                    }

                    this.initTiny();
                },
                initTiny() {
                    if (typeof tinymce === 'undefined') {
                        return;
                    }

                    if (tinymce.get(this.editorId)) {
                        tinymce.remove('#' + this.editorId);
                    }

                    const self = this;
                    tinymce.init({
                        selector: '#' + this.editorId,
                        height: 160,
                        menubar: false,
                        statusbar: false,
                        branding: false,
                        plugins: 'lists',
                        toolbar: 'bold italic underline | bullist numlist',
                        setup(editor) {
                            editor.on('init', () => {
                                window.dispatchEvent(new CustomEvent('sample-row-rich-text-ready', {
                                    detail: { editorId: self.editorId },
                                }));
                            });
                            editor.on('change keyup blur', function () {
                                if (self.$wire) {
                                    self.$wire.set(self.wireKey, editor.getContent(), false);
                                }
                            });
                        },
                    });
                },
                destroy() {
                    if (typeof tinymce !== 'undefined' && tinymce.get(this.editorId)) {
                        tinymce.remove('#' + this.editorId);
                    }
                },
            }));
        };

        if (window.Alpine) {
            registerRftSampleDescriptionEditor();
        } else {
            document.addEventListener('alpine:init', registerRftSampleDescriptionEditor);
        }
    })();

    @include('livewire.partials.walk-in-trf-rft-param-picker-alpine')
</script>
@endscript
