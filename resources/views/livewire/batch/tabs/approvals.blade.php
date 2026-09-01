@php
    $isVerificationStage = ($batch->status ?? '') === 'Sample Verification'
        || ($batch->prelim_batch_status ?? '') === 'Sample Verification';
    $approversHeading = $isVerificationStage ? 'Verification' : 'Approvers';
    $approvalDateLabel = $isVerificationStage ? 'Verification Date' : 'Approval Date';
    $approverColumnLabel = $isVerificationStage ? 'Verified' : 'Approver';
    $awaitingApprovalLabel = $isVerificationStage ? 'Awaiting Verification' : 'Awaiting Approval';
    $updateStatusTitle = $isVerificationStage ? 'Update Verification Status' : 'Update Approval Status';
    $editApproverTitle = $isVerificationStage ? 'Edit Verification' : 'Edit Approver';
    $deleteApproverTitle = $isVerificationStage ? 'Delete Verification' : 'Delete Approver';
    $emptyApproversHeading = $isVerificationStage ? 'No Verification Found' : 'No Approvers Found';
    $searchPlaceholder = $isVerificationStage
        ? 'Search by verification, title, status, or remark...'
        : 'Search by approver, title, status, or remark...';
    $selectApproverLabel = $isVerificationStage ? 'Select Verified' : 'Select Approver';
    $approverTitlePlaceholder = $isVerificationStage ? 'Verification title' : 'Approver title';
    $approverActionsLabel = $isVerificationStage ? 'Verification actions' : 'Approver actions';
    $editApproverButtonTitle = $isVerificationStage ? 'Edit verification' : 'Edit approver';
    $deleteApproverButtonTitle = $isVerificationStage ? 'Delete verification' : 'Delete approver';
    $isCheckedAnalystStage = in_array($batch->status ?? '', ['Sample Verification', 'Sample Approval'], true)
        || in_array($batch->prelim_batch_status ?? '', ['Sample Verification', 'Sample Approval'], true);
    $analystColumnLabel = $isCheckedAnalystStage ? 'Checked Analyst' : 'Analyst';
    $resultsSearchPlaceholder = $isCheckedAnalystStage
        ? 'Search by sample code, analysis type, report display, result, method, standard, checked analyst, or remark...'
        : 'Search by sample code, analysis type, report display, result, method, standard, analyst, or remark...';
@endphp
<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-account-check-outline"></i> {{ $approversHeading }}</h5>
            <div class="d-flex align-items-center gap-2">
                @if($this->canMoveToApproval())
                    @if($this->hasPendingDataCapture())
                        <button type="button"
                            class="btn btn-sm btn-action-sm"
                            disabled
                            title="Capture all results before moving to approval">
                            <i class="mdi mdi-subdirectory-arrow-right"></i> Move to Approval
                        </button>
                    @else
                        <button type="button"
                            class="btn btn-danger btn-sm btn-action-sm text-white"
                            wire:click="openMoveToApproval"
                            wire:loading.attr="disabled"
                            wire:target="openMoveToApproval"
                            title="Move this batch to Sample Approval">
                            <i class="mdi mdi-subdirectory-arrow-right"></i> Move to Approval
                        </button>
                    @endif
                @endif
                <label for="perPage" class="form-label mb-0 me-2 text-muted small">Show</label>
                <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm" style="width: auto; min-width: 4.5rem; border-radius: 6px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
            @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text"
                    wire:model.live="search"
                    class="form-control"
                    placeholder="{{ $searchPlaceholder }}">
            </div>

            @if($approvers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover workflow-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Actions</th>
                            <th>Status</th>
                            <th>{{ $approvalDateLabel }}</th>
                            <th>{{ $approverColumnLabel }}</th>
                            <th>Title</th>
                            <th>Remark</th>
                            <th>Lab Sections</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvers as $approver)
                        <tr wire:key="approver-{{ $approver->id }}" class="{{$approver->batch_status != $batch->status ? 'bg-light' : ''}}">
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap">
                                <div class="d-inline-flex align-items-center" style="gap: 0.5rem;" role="group" aria-label="{{ $approverActionsLabel }}">
                                    @if($canShowReviewActions)
                                    <button type="button"
                                        class="btn btn-sm btn-outline-info"
                                        title="View Results"
                                        wire:click="openResultsModal">
                                        <i class="mdi mdi-clipboard-text-outline"></i>
                                    </button>
                                    @can('laboratory.components.lab-reports.view')
                                    <a href="{{ route('generateTestRequestReport', ['batch_id' => $batch->id, 'mode' => 'preview', 'lang' => 'en']) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Preview Test Report"
                                        target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>
                                    @endcan
                                    @endif

                                    @if($approver->status == 0)
                                    @if($approver->user_id == auth()->id())
                                    {{-- Approve / Decline --}}
                                    <button type="button"
                                        class="btn btn-sm btn-outline-success"
                                        title="Approve / Decline"
                                        wire:click="openStatusModal('{{ $approver->id }}')">
                                        <i class="mdi mdi-thumb-up-outline"></i>
                                    </button>
                                    @endif

                                    {{-- Edit --}}
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        title="{{ $editApproverButtonTitle }}"
                                        wire:click="openEditModal('{{ $approver->id }}')">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>

                                    {{-- Delete --}}
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        title="{{ $deleteApproverButtonTitle }}"
                                        wire:click="openDeleteModal('{{ $approver->id }}')">
                                        <i class="mdi mdi-delete-outline"></i>
                                    </button>
                                    @elseif(!$canShowReviewActions)
                                    <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($approver->status == 0)
                                <span class="badge badge-primary badge-pill p-2">
                                    <i class="mdi mdi-decagram"></i> {{ $awaitingApprovalLabel }}
                                </span>
                                @elseif($approver->status == 1)
                                @if($approver->batch_status == "Sample Verification" && $approver->show_report == 1)
                                <span class="badge badge-success badge-pill p-2">
                                    <i class="mdi mdi-thumb-up-outline"></i> Checked
                                </span>
                                @else
                                <span class="badge badge-success badge-pill p-2">
                                    <i class="mdi mdi-thumb-up-outline"></i>
                                    {{ $approver->batch_status  == "Sample Verification"  ? 'Verified' : 'Authorized'}}
                                </span>
                                @endif
                                @elseif($approver->status == 3)
                                <span class="badge badge-warning badge-pill p-2">
                                    <i class="mdi mdi-keyboard-return"></i> Sent Back
                                </span>
                                @else
                                <span class="badge badge-danger badge-pill p-2">
                                    <i class="mdi mdi-decagram"></i> Declined
                                </span>
                                @endif
                            </td>
                            <td>{{ $approver->approval_date }}</td>
                            <td>
                                {{ $approver->approvername }}<br>
                                <small class="text-muted">{{ $approver->approver_type }}</small>
                            </td>
                            <td>{{ $approver->title }}</td>
                            <td>
                                <small>{{ $approver->remark }}</small>
                            </td>
                            <td>
                                @if($approver->lab_sections && count($approver->lab_sections) > 0)
                                @foreach($approver->lab_sections as $section)
                                <span class="badge badge-info"><i class="mdi mdi-flask"></i> {{ $section->name }}</span>
                                @endforeach
                                @else
                                <span class="badge badge-light">All Sections</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted">
                        Showing {{ $approvers->firstItem() ?? 0 }} to {{ $approvers->lastItem() ?? 0 }} of {{ $approvers->total() }} entries
                    </span>
                </div>
                <div>
                    {{ $approvers->links('pagination::bootstrap-4') }}
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover workflow-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Actions</th>
                            <th>Status</th>
                            <th>{{ $approvalDateLabel }}</th>
                            <th>{{ $approverColumnLabel }}</th>
                            <th>Title</th>
                            <th>Remark</th>
                            <th>Lab Sections</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center py-5 workflow-empty-state">
                                <i class="mdi mdi-account-check-outline text-muted" style="font-size: 48px;"></i>
                                <h6 class="mt-3 text-muted">{{ $emptyApproversHeading }}</h6>
                                <p class="text-muted mb-0"><small>
                                        @if($search)
                                        {{ $isVerificationStage ? 'No verified match your search criteria' : 'No approvers match your search criteria' }}
                                        @else
                                        {{ $isVerificationStage ? 'There are no verified records to display' : 'There are no approval records to display' }}
                                        @endif
                                    </small></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Approve / Decline Modal --}}
    @if($showStatusModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern">
                        <i class="mdi mdi-thumb-up-outline"></i> {{ $updateStatusTitle }}
                    </h5>
                    <button type="button" class="close" wire:click="$set('showStatusModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-modern">
                    <div class="form-group">
                        <label class="text-muted font-weight-bold small modal-label-small">Status</label>
                        <select class="form-control form-control-modern" wire:model="statusForm.status">
                            <option value="1">Approve</option>
                            <option value="2">Decline</option>
                            @php
                                $currentApp = $approvers->firstWhere('id', $currentApproverId);
                            @endphp
                            @if($currentApp && $currentApp->can_send_back_to_lab)
                            <option value="3">Send Back to Lab (Amendment)</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="text-muted font-weight-bold small modal-label-small">Remark</label>
                        <textarea class="form-control form-control-modern"
                            rows="3"
                            wire:model="statusForm.remark"
                            placeholder="Add an optional remark..."></textarea>
                    </div>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="$set('showStatusModal', false)">Close</button>
                    <button type="button" class="btn btn-primary-modern btn-sm" wire:click="updateApproverStatus">
                        <i class="mdi mdi-check"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit Approver Modal --}}
    @if($showEditModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern">
                        <i class="mdi mdi-pencil-outline"></i> {{ $editApproverTitle }}
                    </h5>
                    <button type="button" class="close" wire:click="$set('showEditModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-modern">
                    <div class="form-group">
                        <label class="text-muted font-weight-bold small modal-label-small">{{ $approverColumnLabel }}</label>
                        <select class="form-control form-control-modern" wire:model="editForm.user_id">
                            <option value="">{{ $selectApproverLabel }}</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('editForm.user_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="text-muted font-weight-bold small modal-label-small">Title</label>
                        <input type="text"
                            class="form-control form-control-modern"
                            wire:model="editForm.title"
                            placeholder="{{ $approverTitlePlaceholder }}">
                        @error('editForm.title') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="$set('showEditModal', false)">Close</button>
                    <button type="button" class="btn btn-primary-modern btn-sm" wire:click="saveApproverEdits">
                        <i class="mdi mdi-check"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Approver Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern">
                        <i class="mdi mdi-delete-outline"></i> {{ $deleteApproverTitle }}
                    </h5>
                    <button type="button" class="close" wire:click="$set('showDeleteModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-modern">
                    <p class="mb-0">
                        Are you sure you want to remove this {{ $isVerificationStage ? 'verification record' : 'approver' }} from batch
                        <strong>{{ $batch->batch_code }}</strong>?
                    </p>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm" wire:click="deleteApprover">
                        <i class="mdi mdi-delete-outline"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Checklist Required Modal --}}
    @if($showChecklistRequiredModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1060;">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 860px; width: 92vw;">
            <div class="modal-content modal-content-modern" style="max-height: calc(100vh - 3.5rem); overflow: hidden;">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern">
                        <i class="mdi mdi-clipboard-alert-outline"></i> Complete Checklist First
                    </h5>
                    <button type="button" class="close" wire:click="closeChecklistRequiredModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-modern" style="overflow-y: auto; max-height: calc(100vh - 12rem);">
                    <p class="mb-3">{{ $checklistRequiredMessage }}</p>

                    @if($checklistStageName)
                        <div style="border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; background: #fff;">
                            @livewire(
                                'sampleworkflow.approval-checklist',
                                ['sampleId' => (string) $batch->id, 'stageName' => $checklistStageName],
                                key('embedded-checklist-' . $batch->id . '-' . $checklistStageName)
                            )
                        </div>
                    @endif
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="closeChecklistRequiredModal">Done</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- View Results Modal --}}
    @if($showResultsModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050;">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 1100px; width: 94vw;">
            <div class="modal-content modal-content-modern" style="max-height: calc(100vh - 3.5rem); overflow: hidden;">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern">
                        <i class="mdi mdi-clipboard-text-outline"></i> Test Results — {{ $batch->batch_code }}
                    </h5>
                    <button type="button" class="close" wire:click="closeResultsModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-modern" style="overflow-y: auto; max-height: calc(100vh - 12rem);">
                    <div class="mb-3">
                        <input type="text"
                            wire:model.live.debounce.300ms="resultsSearch"
                            class="form-control form-control-modern"
                            placeholder="{{ $resultsSearchPlaceholder }}">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover workflow-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sample Code</th>
                                    <th>Analysis Type</th>
                                    <th>Report Display</th>
                                    <th>Result</th>
                                    <th>Unit</th>
                                    <th>Method</th>
                                    <th>Standard</th>
                                    <th>{{ $analystColumnLabel }}</th>
                                    <th>Remark</th>
                                    <th>Limit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batchResults as $result)
                                <tr wire:key="approval-result-{{ $result->id }}">
                                    <td class="font-weight-bold">{{ $result->sample->sample_code ?? 'N/A' }}</td>
                                    <td>{{ $result->analysis_type->name ?? 'N/A' }}</td>
                                    <td>{{ $result->analyte_code ?? 'N/A' }}</td>
                                    <td>
                                        <strong class="{{ $result->remark == 'FAIL' ? 'text-danger' : ($result->remark == 'PASS' ? 'text-success' : '') }}">
                                            {{ $result->result !== null && $result->result !== '' ? $result->result : '—' }}
                                        </strong>
                                    </td>
                                    <td>{{ $result->effectiveReportingUnitName() ?? '—' }}</td>
                                    <td>
                                        <small>{{ $result->ltmethod->name ?? $result->analysisMethod->name ?? '—' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $result->standard_name_display ?? '—' }}</small>
                                    </td>
                                    <td>{{ $result->operator->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($result->remark)
                                        <span class="badge badge-{{ $result->remark == 'FAIL' ? 'danger' : ($result->remark == 'PASS' ? 'success' : 'light') }}">
                                            {{ format_result_remark($result->remark) }}
                                        </span>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $result->standard_limit_display ?? $result->main_value ?? '—' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 workflow-empty-state">
                                        <i class="mdi mdi-clipboard-text-outline text-muted" style="font-size: 48px;"></i>
                                        <h6 class="mt-3 text-muted">No Results Found</h6>
                                        <p class="text-muted mb-0"><small>
                                            @if($resultsSearch)
                                            No results match your search criteria
                                            @else
                                            There are no captured results for this batch yet
                                            @endif
                                        </small></p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <span class="text-muted small mr-auto">
                        {{ $batchResults->count() }} result{{ $batchResults->count() === 1 ? '' : 's' }}
                    </span>
                    <button type="button" class="btn btn-secondary-modern btn-sm" wire:click="closeResultsModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>