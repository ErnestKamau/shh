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

    @if($commercialEnquiry && $commercialEnquiry->hasCustomerFeedback())
        <div class="alert alert-warning mb-3">
            <strong><i class="mdi mdi-comment-alert-outline"></i> Customer requested quotation changes</strong>
            <div class="mt-2 mb-0" style="white-space: pre-wrap;">{{ $commercialEnquiry->customerFeedbackNotes() }}</div>
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

    <div id="receive-sample-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" id="receive-sample-modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title mb-1" id="receive-sample-modal-title">
                            <i class="mdi mdi-clipboard-arrow-right text-primary mr-2" id="receive-sample-modal-icon"></i>
                            <span id="receive-sample-modal-title-text">Move to In Review</span>
                        </h5>
                        <p class="text-muted small mb-0 d-none" id="receive-sample-modal-subtitle"></p>
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

    // Child ReceiveSampleRequest dispatches this — must use Livewire.on (not $wire.on).
    Livewire.on('show-receive-sample-modal', (payload) => {
        const physical = payload?.physicalCheckIn ?? true;
        const modalEl = document.getElementById('receive-sample-modal');
        const dialogEl = document.getElementById('receive-sample-modal-dialog');
        const titleEl = document.getElementById('receive-sample-modal-title-text');
        const iconEl = document.getElementById('receive-sample-modal-icon');
        const subtitleEl = document.getElementById('receive-sample-modal-subtitle');

        if (modalEl) {
            modalEl.classList.toggle('receive-sample-modal--compact', physical);
        }
        if (dialogEl) {
            dialogEl.classList.toggle('modal-xl', !physical);
            dialogEl.classList.toggle('modal-dialog-scrollable', !physical);
        }
        if (titleEl && iconEl) {
            if (physical) {
                titleEl.textContent = 'Move to In Review';
                iconEl.className = 'mdi mdi-clipboard-arrow-right text-primary mr-2';
                if (subtitleEl) {
                    subtitleEl.textContent = '';
                    subtitleEl.classList.add('d-none');
                }
            } else {
                titleEl.textContent = 'Test Request Form';
                iconEl.className = 'mdi mdi-clipboard-text text-primary mr-2';
                if (subtitleEl) {
                    subtitleEl.textContent = '';
                    subtitleEl.classList.add('d-none');
                }
            }
        }

        $('#receive-sample-modal').modal('show');
        setTimeout(function () {
            if (typeof window.initTrfSignaturePads === 'function') {
                window.initTrfSignaturePads();
            }
        }, 300);
    });

    Livewire.on('hide-receive-sample-modal', () => {
        $('#receive-sample-modal').modal('hide');
    });

    Livewire.on('receive-completed', () => {
        window.location.reload();
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
</script>
@endscript
