<div>
    @php
        $formNumber = $instance->getDocumentControlNumber() ?? $instance->form_number ?? 'Pending';
        $boardStatus = $this->workflowBoardStatus();
        $statusChipClass = match ($instance->status) {
            'in_review' => 'workflow-status-chip--in-review',
            'submitted' => 'workflow-status-chip--submitted',
            'approved', 'complete' => 'workflow-status-chip--approved',
            'rejected' => 'workflow-status-chip--rejected',
            default => '',
        };
        $priorityChipClass = match ($instance->priority) {
            'high', 'urgent' => 'priority-chip--high',
            'normal' => 'priority-chip--normal',
            default => '',
        };
    @endphp

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

    <div class="workflow-board-header batch-header-bar">
        <div class="batch-header-top">
            <div class="batch-title-group">
                <h1 class="request-view-title">{{ $formNumber }}</h1>
                <p class="request-view-form-name">
                    <i class="mdi mdi-file-document-outline"></i>
                    {{ $submissionForm->name }}
                </p>
                <div class="request-view-meta">
                    @if($instance->crmCustomer)
                        <span class="text-muted"><i class="mdi mdi-domain"></i> {{ $instance->crmCustomer->name }}</span>
                    @elseif($instance->submittedBy)
                        <span class="text-muted"><i class="mdi mdi-account-outline"></i> {{ $instance->submittedBy->name }}</span>
                    @endif
                    @if($commercialEnquiry && $commercialEnquiry->isCommercialEnquiry())
                        <span class="text-muted"><i class="mdi mdi-file-chart-outline"></i> {{ $commercialEnquiry->commercialStatus() }}</span>
                    @endif
                    <span class="workflow-status-chip {{ $statusChipClass }}">{{ ucfirst(str_replace('_', ' ', $instance->status)) }}</span>
                    <span class="priority-chip {{ $priorityChipClass }}">{{ ucfirst($instance->priority) }} priority</span>
                </div>
            </div>
            <div class="batch-header-actions">
                <div class="btn-group request-view-actions-dropdown">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Actions
                    </button>
                    <div class="dropdown-menu dropdown-menu-right request-view-actions-menu">
                        @unless($instance->isDraft())
                            <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" class="dropdown-item">
                                <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                <span>Edit information</span>
                            </a>
                            @if($canCreateSamples)
                            <a href="#" class="dropdown-item create-samples-btn" data-instance-id="{{ $instance->id }}">
                                <i class="mdi mdi-flask" aria-hidden="true"></i>
                                <span>Create job / batch</span>
                            </a>
                            @endif
                            @if($instance->batches->isNotEmpty() && $linkedBatchesOutOfSyncWithForm)
                                <form method="POST" action="{{ route('submission-forms.instances.apply-to-batches', $instance->id) }}" class="request-view-actions-form" onsubmit="return confirm('Update all linked batches from the current saved form data?');">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="mdi mdi-sync" aria-hidden="true"></i>
                                        <span>Apply form to linked batches</span>
                                    </button>
                                </form>
                            @endif
                        @endunless
                        @if($instance->isDraft())
                            <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" class="dropdown-item">
                                <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                <span>Continue editing</span>
                            </a>
                        @endif
                        @if($this->shouldShowSampleCollectionLabel())
                        <a href="{{ route('submission-forms.instances.sample-collection-label', $instance->id) }}" target="_blank" class="dropdown-item">
                            <i class="mdi mdi-label" aria-hidden="true"></i>
                            <span>Sample collection label</span>
                        </a>
                        @endif
                        @if($commercialEnquiry && $commercialEnquiry->isCommercialEnquiry())
                            <div class="dropdown-divider"></div>
                            @if(in_array($commercialEnquiry->status, [
                                \App\Models\SampleSubmissionRequest::STATUS_REQUESTED,
                                \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
                                \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_SENT,
                                \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW,
                            ], true))
                                <button type="button" class="dropdown-item" wire:click="openProcessEnquiry">
                                    <i class="mdi mdi-file-chart-outline" aria-hidden="true"></i>
                                    <span>Process enquiry</span>
                                </button>
                            @endif
                            @if($commercialEnquiry->status === \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_SENT
                                && strtolower((string) ($commercialEnquiry->source_channel ?? '')) === 'walk_in')
                                <button type="button" class="dropdown-item" wire:click="recordWalkInQuotationAcceptance" wire:loading.attr="disabled">
                                    <i class="mdi mdi-check-decagram" aria-hidden="true"></i>
                                    <span>Record walk-in acceptance</span>
                                </button>
                            @endif
                            @if($commercialEnquiry->status === \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED)
                                <button type="button" class="dropdown-item" wire:click="openPoCaptureModal">
                                    <i class="mdi mdi-file-document-edit-outline" aria-hidden="true"></i>
                                    <span>Record PO</span>
                                </button>
                            @endif
                            @if($commercialEnquiry->status === \App\Models\SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION)
                                <button type="button" class="dropdown-item" wire:click="openPhysicalReceiveModal">
                                    <i class="mdi mdi-package-variant-closed" aria-hidden="true"></i>
                                    <span>Receive physical samples</span>
                                </button>
                                <a href="{{ route('sample-workflow', ['status' => 'Samples Receiving']) }}?workflowSubTab=ready_for_reception" class="dropdown-item">
                                    <i class="mdi mdi-open-in-new" aria-hidden="true"></i>
                                    <span>Open on receiving board</span>
                                </a>
                            @endif
                            @if($commercialEnquiry->currentQuotation)
                                <a href="{{ route('quotation.preview.pdf', ['id' => $commercialEnquiry->currentQuotation->id]) }}" target="_blank" class="dropdown-item">
                                    <i class="mdi mdi-file-pdf-box" aria-hidden="true"></i>
                                    <span>View quotation PDF ({{ $commercialEnquiry->currentQuotation->quote_number }})</span>
                                </a>
                            @endif
                        @endif
                        {{-- Duplicate of Generate TRF; kept commented per product request.
                        @if($instance->testRequestFormInstance)
                            <div class="dropdown-divider"></div>
                            <button
                                type="button"
                                class="dropdown-item"
                                wire:click="generateTestRequestFormReport"
                                wire:loading.attr="disabled"
                                wire:target="generateTestRequestFormReport"
                            >
                                <i class="mdi mdi-file-document-edit-outline" aria-hidden="true"></i>
                                <span wire:loading.remove wire:target="generateTestRequestFormReport">Generate test request form</span>
                                <span wire:loading wire:target="generateTestRequestFormReport">Generating…</span>
                            </button>
                        @endif
                        --}}
                        @php $firstBatch = $instance->batches->first(); @endphp
                        @if($firstBatch)
                            <a href="{{ route('view-batch-details', ['batch' => $firstBatch->id, 'client' => 0, 'portal' => 0, 'status' => $firstBatch->status]) }}" class="dropdown-item">
                                <i class="mdi mdi-flask" aria-hidden="true"></i>
                                <span>View sample batch</span>
                            </a>
                        @endif
                        @if($this->isTrfForm())
                            <div class="dropdown-divider"></div>
                            <button type="button" class="dropdown-item" wire:click="generateTrfPdf" wire:loading.attr="disabled" wire:target="generateTrfPdf">
                                <i class="mdi mdi-file-pdf-box" aria-hidden="true"></i>
                                <span wire:loading.remove wire:target="generateTrfPdf">Generate TRF</span>
                                <span wire:loading wire:target="generateTrfPdf">Generating…</span>
                            </button>
                            @if($trfPdfUrl)
                                <button type="button" class="dropdown-item" wire:click="downloadTrfPdf">
                                    <i class="mdi mdi-download" aria-hidden="true"></i>
                                    <span>Download TRF</span>
                                </button>
                                <button type="button" class="dropdown-item" wire:click="sendTrfPdfToCustomer" wire:loading.attr="disabled" wire:target="sendTrfPdfToCustomer">
                                    <i class="mdi mdi-email-send-outline" aria-hidden="true"></i>
                                    <span wire:loading.remove wire:target="sendTrfPdfToCustomer">Send to customer</span>
                                    <span wire:loading wire:target="sendTrfPdfToCustomer">Sending…</span>
                                </button>
                            @endif
                        @endif
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('submission-forms.instances.destroy', [$submissionForm->id, $instance->id]) }}" method="POST" class="request-view-actions-form request-view-actions-form--danger" onsubmit="return confirm('Delete this submission and all linked batches?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item dropdown-item-danger">
                                <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
                                <span>Delete submission</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($commercialEnquiry && $commercialEnquiry->hasCustomerFeedback())
        <div class="alert alert-warning mb-3">
            <strong><i class="mdi mdi-comment-alert-outline"></i> Customer requested quotation changes</strong>
            <div class="mt-2 mb-0" style="white-space: pre-wrap;">{{ $commercialEnquiry->customerFeedbackNotes() }}</div>
        </div>
    @endif

    <div class="workflow-board-panel mb-3">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-file-document-outline"></i> Captured request details</h5>
        </div>
        <div class="workflow-board-panel-body">
            @include('submission-forms.partials.simple-form-display-clinical', ['instance' => $instance, 'formData' => $formData])
        </div>
    </div>

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

    @if($attachmentInstances->isEmpty())
        @include('submission-forms.partials.sample-creation-actions', [
            'instance' => $instance,
            'linkedBatchesOutOfSyncWithForm' => $linkedBatchesOutOfSyncWithForm,
        ])
    @endif

    <div class="workflow-board-panel batch-tabs-panel">
        <div class="workflow-board-panel-body flush-top">
            <ul class="nav batch-nav-tabs mb-0" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'samples' ? 'active' : '' }}" wire:click="setTab('samples')">
                        <i class="mdi mdi-flask-outline"></i> Samples
                        <span class="badge">{{ count($this->sampleLines) }}</span>
                    </button>
                </li>
                @if($this->isTrfForm())
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $activeTab === 'attached' ? 'active' : '' }}" wire:click="setTab('attached')">
                            <i class="mdi mdi-paperclip"></i> Attached
                        </button>
                    </li>
                @endif
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
                @if($activeTab === 'samples')
                    @include('livewire.submission-forms.request-view.tabs.samples', [
                        'sampleLines' => $this->sampleLines,
                        'acceptanceForm' => $acceptanceForm,
                        'boardStatus' => $boardStatus,
                    ])
                @elseif($activeTab === 'attached')
                    @include('livewire.submission-forms.request-view.tabs.attached', [
                        'instance' => $instance,
                    ])
                @elseif($activeTab === 'notes')
                    @include('livewire.submission-forms.request-view.tabs.notes')
                @elseif($activeTab === 'attachments')
                    @include('livewire.submission-forms.request-view.tabs.attachments', [
                        'attachmentInstances' => $attachmentInstances,
                        'batchAttachments' => $batchAttachments,
                        'customAttachments' => $customAttachments,
                        'formMediaAttachments' => $formMediaAttachments,
                    ])
                @elseif($activeTab === 'custody')
                    @include('livewire.submission-forms.request-view.tabs.chain-of-custody', [
                        'custodyTimeline' => $this->custodyTimeline,
                    ])
                @endif
            </div>
        </div>
    </div>

    @livewire('sampleworkflow.process-enquiry-wizard')

    <div id="receive-sample-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="mdi mdi-package-variant-closed text-primary mr-2"></i>
                            Physical sample check-in
                        </h5>
                        <p class="text-muted small mb-0">Confirm samples arrived at reception against the accepted quotation.</p>
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
                        <div class="form-group">
                            <label for="clientPoNumber">Client PO number</label>
                            <input type="text" id="clientPoNumber" class="form-control" wire:model="clientPoNumber" @disabled($poSkipped)>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="poSkipped" wire:model.live="poSkipped">
                            <label class="form-check-label" for="poSkipped">Skip PO (non-credit / walk-in without PO)</label>
                        </div>
                        <div class="form-group mb-0">
                            <label for="advancePaymentReference">Advance payment reference (optional)</label>
                            <input type="text" id="advancePaymentReference" class="form-control" wire:model="advancePaymentReference">
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

    $wire.on('show-receive-sample-modal', () => {
        $('#receive-sample-modal').modal('show');
        setTimeout(function () {
            if (typeof window.initTrfSignaturePads === 'function') {
                window.initTrfSignaturePads();
            }
        }, 300);
    });
</script>
@endscript
