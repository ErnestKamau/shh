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
                    <span class="workflow-status-chip {{ $statusChipClass }}">{{ ucfirst(str_replace('_', ' ', $instance->status)) }}</span>
                    <span class="priority-chip {{ $priorityChipClass }}">{{ ucfirst($instance->priority) }} priority</span>
                </div>
            </div>
            <div class="batch-header-actions">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                        <i class="mdi mdi-menu-down"></i> Actions
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        @unless($instance->isDraft())
                            <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" class="dropdown-item">
                                <i class="mdi mdi-pencil mr-2"></i> Edit information
                            </a>
                            @if($canCreateSamples && ($sampleStatus['status'] ?? '') === 'ready')
                                <a href="#" class="dropdown-item create-samples-btn" data-instance-id="{{ $instance->id }}">
                                    <i class="mdi mdi-flask mr-2"></i> Create batch
                                </a>
                            @endif
                            @if($instance->batches->isNotEmpty() && $linkedBatchesOutOfSyncWithForm)
                                <form method="POST" action="{{ route('submission-forms.instances.apply-to-batches', $instance->id) }}" class="d-inline w-100" onsubmit="return confirm('Update all linked batches from the current saved form data?');">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="mdi mdi-sync mr-2"></i> Apply form to linked batches
                                    </button>
                                </form>
                            @endif
                        @endunless
                        @if($instance->isDraft())
                            <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" class="dropdown-item">
                                <i class="mdi mdi-pencil mr-2"></i> Continue editing
                            </a>
                        @endif
                        @php $firstBatch = $instance->batches->first(); @endphp
                        @if($firstBatch)
                            <a href="{{ route('view-batch-details', ['batch' => $firstBatch->id, 'client' => 0, 'portal' => 0, 'status' => $firstBatch->status]) }}" class="dropdown-item">
                                <i class="mdi mdi-flask mr-2"></i> View sample batch
                            </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('submission-forms.instances.destroy', [$submissionForm->id, $instance->id]) }}" method="POST" onsubmit="return confirm('Delete this submission and all linked batches?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="mdi mdi-delete-empty mr-2"></i> Delete submission
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="stat-cards-row mb-3">
        <div class="stat-card">
            <div class="stat-card-label">Status</div>
            <div class="stat-card-content">
                <div class="stat-card-value">
                    <span class="stat-status-label">{{ ucfirst(str_replace('_', ' ', $instance->status)) }}</span>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Sample lines</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ count($this->sampleLines) }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-flask-outline"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Notes</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ $instance->notes->count() }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-comment-text-outline"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Linked batches</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ $instance->batches->count() }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-link-variant"></i></div>
            </div>
        </div>
    </div>

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
</div>
