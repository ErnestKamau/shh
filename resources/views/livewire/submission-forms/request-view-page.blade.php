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

    @include('livewire.submission-forms.request-view.cards.request-info', [
        'requestInfoCard' => $requestInfoCard,
    ])

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

    <div class="workflow-board-panel batch-tabs-panel">
        <div class="workflow-board-panel-body flush-top">
            <ul class="nav batch-nav-tabs mb-0" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'tests' ? 'active' : '' }}" wire:click="setTab('tests')">
                        <i class="mdi mdi-flask-outline"></i> Tests
                        <span class="badge">{{ $testSamplesCard['count'] }}</span>
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
                        'acceptanceForm' => $acceptanceForm,
                        'boardStatus' => $boardStatus,
                        'attachmentInstances' => $attachmentInstances,
                        'instance' => $instance,
                        'linkedBatchesOutOfSyncWithForm' => $linkedBatchesOutOfSyncWithForm,
                    ])
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

    @livewire('sampleworkflow.process-enquiry-wizard')
    @livewire('sampleworkflow.sample-rejection-wizard')
    @livewire('sampleworkflow.acceptance-form-wizard')

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

    <div id="print-sample-labels-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
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
                        <a href="{{ route('submission-forms.instances.sample-collection-label', ['instance' => $instance->id, 'type' => 'collection']) }}"
                            target="_blank"
                            rel="noopener"
                            class="list-group-item list-group-item-action"
                            data-dismiss="modal">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-tag-outline mr-2 text-primary" aria-hidden="true"></i>
                                <div>
                                    <strong class="d-block">Sample Collection Label</strong>
                                    <small class="text-muted">Collection details with TRF barcode</small>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('submission-forms.instances.sample-collection-label', ['instance' => $instance->id, 'type' => 'registration']) }}"
                            target="_blank"
                            rel="noopener"
                            class="list-group-item list-group-item-action"
                            data-dismiss="modal">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-barcode mr-2 text-primary" aria-hidden="true"></i>
                                <div>
                                    <strong class="d-block">Registration Label with barcode</strong>
                                    <small class="text-muted">Job / sample registration label for scanning</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if($showQuotationAcceptanceModal)
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45);">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Customer quotation acceptance</h5>
                        <button type="button" class="close" wire:click="closeQuotationAcceptanceModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">Capture the customer signature to accept this quotation. The signature will appear on the quotation PDF.</p>
                        @if(count($quotationAcceptanceContactOptions) > 0)
                            <div class="form-group">
                                <label>Customer contact</label>
                                <select class="form-control" wire:model="quotationAcceptanceContactId">
                                    <option value="">Select contact...</option>
                                    @foreach($quotationAcceptanceContactOptions as $contact)
                                        <option value="{{ $contact['id'] }}">{{ $contact['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="quotationAcceptanceSignerName">Signer name <span class="text-danger">*</span></label>
                            <input type="text" id="quotationAcceptanceSignerName" class="form-control" wire:model.defer="quotationAcceptanceSignerName">
                            @error('quotationAcceptanceSignerName')
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
                        <input type="hidden" wire:model="quotationAcceptanceSignature">
                        @error('quotationAcceptanceSignature')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeQuotationAcceptanceModal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="request-view-quotation-acceptance-submit">Accept quotation</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showPoCaptureModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45);">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record customer PO</h5>
                        <button type="button" class="close" wire:click="closePoCaptureModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 mb-3 small">
                            {{ $poRuleMessage }}
                        </div>
                        <div class="form-group">
                            <label for="clientPoNumber">Client PO number</label>
                            <input type="text" id="clientPoNumber" class="form-control" wire:model="clientPoNumber" @disabled($poSkipped)>
                            @error('client_po_number')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="poSkipped" wire:model.live="poSkipped" @disabled(! $poAllowsSkip)>
                            <label class="form-check-label" for="poSkipped">Skip PO (non-credit / walk-in without PO)</label>
                            @error('po_skipped')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="advancePaymentReference">Advance payment reference (optional)</label>
                            <input type="text" id="advancePaymentReference" class="form-control" wire:model="advancePaymentReference">
                            @error('advance_payment_reference')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closePoCaptureModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="submitPoAndReadyForReception" wire:loading.attr="disabled">
                            Save PO &amp; mark ready for reception
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    $wire.on('open-test-request-pdf', ({ url }) => {
        if (url) {
            window.open(url, '_blank');
        }
    });

    Livewire.on('notify', (payload) => {
        const data = payload?.detail ?? payload ?? {};
        const type = data.type ?? 'info';
        const message = data.message ?? data[0]?.message ?? '';
        if (!message) {
            return;
        }
        if (typeof toastr !== 'undefined') {
            toastr[type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success')](message);
            return;
        }
        alert(message);
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

    function initRequestViewQuotationPad() {
        const canvas = document.getElementById('request-view-quotation-acceptance-canvas');
        const clearBtn = document.getElementById('request-view-quotation-acceptance-clear');
        if (!canvas || typeof SignaturePad === 'undefined') {
            return;
        }

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);

        requestViewQuotationPad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
        requestViewQuotationPad.addEventListener('endStroke', function () {
            $wire.set('quotationAcceptanceSignature', requestViewQuotationPad.isEmpty() ? '' : requestViewQuotationPad.toDataURL('image/png'));
        });

        if (clearBtn) {
            clearBtn.onclick = function () {
                requestViewQuotationPad.clear();
                $wire.set('quotationAcceptanceSignature', '');
            };
        }
    }

    Livewire.on('quotation-acceptance-modal-opened', () => {
        setTimeout(initRequestViewQuotationPad, 250);
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

        $wire.set('quotationAcceptanceSignature', requestViewQuotationPad.toDataURL('image/png')).then(() => {
            $wire.call('submitQuotationAcceptanceSignature');
        });
    }, true);
</script>
@endscript
