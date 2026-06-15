<div>
    <style>
        /* Modern Table Styles - Google-inspired */
        .modern-table-wrapper {
            background: #ffffff;
            border-radius: 8px;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }

        .modern-search-bar {
            background: #ffffff;
            border-bottom: 1px solid #e8eaed;
            padding: 16px 24px;
        }

        .modern-search-input {
            border: 1px solid #dadce0;
            border-radius: 24px;
            padding: 10px 16px 10px 44px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .modern-search-input:focus {
            outline: none;
            border-color: #6D0A0E;
            box-shadow: 0 0 0 3px rgba(109, 10, 14, 0.1);
        }

        .modern-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #5f6368;
            font-size: 18px;
        }

        .modern-filters-bar {
            background: #f8f9fa;
            border-bottom: 1px solid #e8eaed;
            padding: 12px 24px;
        }

        .modern-filter-btn {
            border: 1px solid #dadce0;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 500;
            color: #5f6368;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .modern-filter-btn:hover {
            background: #f1f3f4;
            border-color: #6D0A0E;
            color: #6D0A0E;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #ffffff;
        }

        .modern-table thead {
            background: #f8f9fa;
        }

        .modern-table thead th {
            padding: 12px 24px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #5f6368;
            border-bottom: 1px solid #e8eaed;
            white-space: nowrap;
            position: relative;
        }

        .modern-table thead th.sortable {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.2s ease;
        }

        .modern-table thead th.sortable:hover {
            background: #f1f3f4;
        }

        .modern-table thead th.sortable .sort-icon {
            margin-left: 8px;
            color: #6D0A0E;
            font-size: 14px;
        }

        .modern-table tbody tr {
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.15s ease;
        }

        .modern-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .modern-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .modern-table tbody tr:hover {
            background: rgba(109, 10, 14, 0.05) !important;
        }

        .modern-table tbody tr:last-child {
            border-bottom: none;
        }

        .modern-table tbody td {
            padding: 16px 24px;
            font-size: 14px;
            color: #202124;
            vertical-align: middle;
        }

        .modern-table tbody td:first-child {
            font-weight: 500;
            color: #6D0A0E;
        }

        .modern-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.5;
        }

        .modern-badge-success {
            background: #e6f4ea;
            color: #137333;
        }

        .modern-badge-info {
            background: rgba(109, 10, 14, 0.08);
            color: #6D0A0E;
        }

        .modern-badge-warning {
            background: #fef7e0;
            color: #ea8600;
        }

        .modern-badge-secondary {
            background: #f1f3f4;
            color: #5f6368;
        }

        .modern-badge-danger {
            background: #fce8e6;
            color: #c5221f;
        }

        .modern-badge-dark {
            background: #3c4043;
            color: #ffffff;
        }

        .modern-action-btn {
            border: none;
            background: transparent;
            color: #5f6368;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin: 0 4px;
            padding: 4px;
            font-size: 18px;
            cursor: pointer;
        }

        .modern-action-btn:hover {
            background: #f1f3f4;
            border-color: #6D0A0E;
            color: #6D0A0E;
            transform: scale(1.05);
        }

        .modern-action-btn.info:hover {
            background: rgba(109, 10, 14, 0.08);
            border-color: #6D0A0E;
            color: #6D0A0E;
        }

        .modern-action-btn.warning:hover {
            background: #fef7e0;
            border-color: #ea8600;
            color: #ea8600;
        }

        .modern-action-btn.danger:hover {
            background: #fce8e6;
            border-color: #c5221f;
            color: #c5221f;
        }

        .modern-empty-state {
            padding: 64px 24px;
            text-align: center;
        }

        .modern-empty-state-icon {
            font-size: 64px;
            color: #dadce0;
            margin-bottom: 16px;
        }

        .modern-empty-state-text {
            font-size: 16px;
            color: #5f6368;
            margin: 0;
        }

        .modern-pagination {
            padding: 16px 24px;
            border-top: 1px solid #e8eaed;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modern-pagination-info {
            font-size: 13px;
            color: #5f6368;
        }

        .modern-select {
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 6px 32px 6px 12px;
            font-size: 13px;
            background: #ffffff;
            color: #202124;
            transition: all 0.2s ease;
        }

        .modern-select:focus {
            outline: none;
            border-color: #6D0A0E;
            box-shadow: 0 0 0 3px rgba(109, 10, 14, 0.1);
        }

        .modern-modal {
            border-radius: 8px;
            border: none;
            box-shadow: 0 8px 10px 1px rgba(0,0,0,0.14), 0 3px 14px 2px rgba(0,0,0,0.12);
        }

        .modern-modal-header {
            border-bottom: 1px solid #e8eaed;
            padding: 20px 24px;
        }

        .modern-modal-body {
            padding: 24px;
        }

        .modern-modal-footer {
            border-top: 1px solid #e8eaed;
            padding: 16px 24px;
        }

        .modern-btn {
            border-radius: 4px;
            font-weight: 500;
            padding: 8px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .modern-btn:hover {
            box-shadow: 0 1px 2px 0 rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15);
        }

        /* Popover styling to ensure visibility */
        .popover {
            z-index: 1060 !important;
            max-width: 400px;
        }

        .popover .popover-body {
            padding: 12px 16px;
        }
    </style>

    <!-- Search and Filters -->
    <div class="modern-search-bar">
        <div class="row align-items-center">
            <div class="col-md-5 position-relative">
                <i class="mdi mdi-magnify modern-search-icon"></i>
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       class="modern-search-input" 
                       placeholder="Search audits by number, title, department...">
            </div>
            <div class="col-md-2">
                <select wire:model.live="perPage" class="modern-select form-control">
                    <option value="15">15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
            <div class="col-md-3">
                <button wire:click="$toggle('showFilters')" class="modern-filter-btn btn-block">
                    <i class="mdi mdi-filter-variant"></i> {{ $showFilters ? 'Hide' : 'Show' }} Filters
                </button>
            </div>
            <div class="col-md-2">
                @if($this->selectedCount > 0)
                <button wire:click="openWorkflowActionModal()" class="modern-filter-btn btn-block" style="background: #6D0A0E; color: white; border-color: #6D0A0E;">
                    <i class="mdi mdi-check-decagram"></i> Workflow Action ({{ $this->selectedCount }})
                </button>
                @endif
            </div>
        </div>

        @if($showFilters)
        <div class="modern-filters-bar mt-3">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Audit Type</label>
                    <select wire:model.live="filters.audit_type_id" class="modern-select form-control">
                        <option value="">All Types</option>
                        @foreach($auditTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Lead Auditor</label>
                    <select wire:model.live="filters.lead_auditor_id" class="modern-select form-control">
                        <option value="">All Auditors</option>
                        @foreach($auditors as $auditor)
                        <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">From Date</label>
                    <input wire:model.live="filters.date_from" type="date" class="modern-select form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">To Date</label>
                    <input wire:model.live="filters.date_to" type="date" class="modern-select form-control">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button wire:click="clearFilters" class="modern-filter-btn btn-block">
                        <i class="mdi mdi-filter-remove"></i> Clear
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Modern Table -->
    <div class="modern-table-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="modern-table" style="min-width: 1000px;">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">
                        <input type="checkbox" wire:model="selectAll" wire:change="toggleSelectAll" style="cursor: pointer;">
                    </th>
                    <th wire:click="sortBy('audit_number')" class="sortable">
                        Audit #
                        @if($sortField === 'audit_number')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>Title</th>
                    <th>Type</th>
                    <th wire:click="sortBy('scheduled_date')" class="sortable">
                        Scheduled Date
                        @if($sortField === 'scheduled_date')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>Lead Auditor</th>
                    <th wire:click="sortBy('status_name')" class="sortable">
                        Status
                        @if($sortField === 'status_name')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>Findings</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audits as $audit)
                @php
                    $currentStep = $audit->getCurrentWorkflowStep() ?? 1;
                    // Always use the actual status_name from the database (status-based, not step-based)
                    $stepName = $audit->status_name ?? 'Unknown';
                    // Determine badge class based on status
                    $statusBadgeClass = match($audit->status_name) {
                        'Closed' => 'success',
                        'Pending Closure' => 'warning',
                        'Cancelled' => 'danger',
                        'Scheduled' => 'secondary',
                        default => 'info'
                    };
                @endphp
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" 
                               wire:model.live="selectedAudits" 
                               value="{{ $audit->id }}"
                               style="cursor: pointer;">
                    </td>
                    <td>
                        <a href="{{ route('audit.audits.show', $audit->id) }}" style="color: #6D0A0E; text-decoration: none; font-weight: 500;">
                            {{ $audit->audit_number }}
                        </a>
                    </td>
                    <td>{{ Str::limit($audit->title, 50) }}</td>
                    <td>{{ $audit->auditType?->name ?? 'N/A' }}</td>
                    <td>{{ $audit->scheduled_date?->format('M d, Y') ?? '-' }}</td>
                    <td>{{ $audit->leadAuditor?->name ?? '-' }}</td>
                    <td>
                        @php
                            $findingsRequiringNC = $audit->findings->filter(function($f) {
                                return $f->findingCategory && $f->findingCategory->requires_capa && !$f->nonConformance;
                            });
                            // Check if we're at "Record Findings & NC" status and have findings requiring NC
                            $hasRequirements = $findingsRequiringNC->count() > 0 && in_array($audit->status_name, ['Record Findings & NC', 'Findings Review']);
                        @endphp
                        <span class="modern-badge modern-badge-{{ $statusBadgeClass }} status-hover-badge" 
                              data-toggle="popover"
                              data-placement="bottom"
                              data-trigger="hover"
                              data-html="true"
                              data-content-id="audit-details-{{ $audit->id }}"
                              data-container="body"
                              style="cursor: pointer;">
                            {{ $stepName }}
                        </span>
                        @if($hasRequirements)
                        <span class="badge badge-danger ml-1" title="{{ $findingsRequiringNC->count() }} finding(s) require NC">
                            <i class="mdi mdi-alert"></i> {{ $findingsRequiringNC->count() }}
                        </span>
                        @endif
                    </td>
                    <td>
                        <span class="modern-badge modern-badge-dark">{{ $audit->findings->count() }}</span>
                    </td>
                    <td style="text-align: center; white-space: nowrap;">
                        <a href="{{ route('audit.audits.show', $audit->id) }}" class="modern-action-btn info" title="View">
                            <i class="mdi mdi-eye" style="font-size: 18px;"></i>
                        </a>
                        @if($audit->status_name !== 'Closed')
                        <button wire:click="openWorkflowActionModal({{ $audit->id }})" class="modern-action-btn" style="color: #28a745;" title="Workflow Action">
                            <i class="mdi mdi-check-decagram" style="font-size: 18px;"></i>
                        </button>
                        @endif
                        <button wire:click="confirmDelete({{ $audit->id }})" class="modern-action-btn danger" title="Delete">
                            <i class="mdi mdi-delete" style="font-size: 18px;"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="modern-empty-state">
                        <i class="mdi mdi-file-document-outline modern-empty-state-icon"></i>
                        <p class="modern-empty-state-text">No audits found matching your criteria.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Hidden content for audit details popovers (completely hidden, outside table) -->
    <div style="display: none !important; visibility: hidden; position: absolute; left: -9999px; width: 0; height: 0; overflow: hidden;">
        @foreach($audits as $audit)
        <div id="audit-details-{{ $audit->id }}">
            @include('livewire.audit-module.partials.audit-details-popover', ['audit' => $audit])
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($audits->hasPages())
    <div class="modern-pagination">
        <div class="modern-pagination-info">
            Showing {{ $audits->firstItem() ?? 0 }} to {{ $audits->lastItem() ?? 0 }} of {{ $audits->total() }} results
        </div>
        <div>
            {{ $audits->links() }}
        </div>
    </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" style="font-weight: 500; font-size: 20px;">Confirm Delete</h5>
                    <button type="button" class="close" wire:click="$set('showDeleteModal', false)" style="opacity: 0.6;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <p style="color: #5f6368; margin: 0;">Are you sure you want to delete this audit? This action cannot be undone.</p>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn btn-secondary modern-btn" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger modern-btn" wire:click="deleteAudit">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Status Change Modal -->
    @if($showStatusModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" style="font-weight: 500; font-size: 20px;">Change Workflow Step</h5>
                    <button type="button" class="close" wire:click="$set('showStatusModal', false)" style="opacity: 0.6;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="form-group">
                        <label style="font-size: 13px; font-weight: 500; color: #5f6368; margin-bottom: 8px;">Workflow Step</label>
                        @if($currentWorkflowStep)
                            @php
                                $workflowSteps = getAuditWorkflowSteps();
                                $currentStepName = $workflowSteps[$currentWorkflowStep] ?? 'Unknown';
                            @endphp
                            <small class="text-muted d-block mb-2">Current Step: {{ $currentWorkflowStep }}. {{ $currentStepName }}</small>
                        @endif
                        <select wire:model="newStatus" class="modern-select form-control">
                            @foreach($availableWorkflowSteps as $stepNum => $stepName)
                                <option value="{{ $stepName }}" 
                                    @if($stepNum < $currentWorkflowStep) 
                                        style="color: #28a745;" 
                                        title="Previous step - can go back"
                                    @elseif($stepNum == $currentWorkflowStep)
                                        style="color: #6D0A0E; font-weight: 600;"
                                        title="Current step"
                                    @else
                                        style="color: #17a2b8;"
                                        title="Next step - can proceed forward"
                                    @endif>
                                    {{ $stepNum }}. {{ $stepName }}
                                    @if($stepNum < $currentWorkflowStep) 
                                        (Previous)
                                    @elseif($stepNum == $currentWorkflowStep)
                                        (Current)
                                    @else
                                        (Next)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted mt-1">
                            <i class="mdi mdi-information-outline"></i> You can move to previous steps (backwards) or the next step (forward) in the workflow.
                        </small>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size: 13px; font-weight: 500; color: #5f6368; margin-bottom: 8px;">Notes (Optional)</label>
                        <textarea wire:model="statusNotes" class="form-control" rows="3" style="border: 1px solid #dadce0; border-radius: 4px; padding: 8px 12px; font-size: 14px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn btn-secondary modern-btn" wire:click="$set('showStatusModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary modern-btn" wire:click="changeStatus">Update Workflow Step</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Workflow Action Modal -->
    @if($showWorkflowActionModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title" style="font-weight: 500; font-size: 20px;">
                        <i class="mdi mdi-check-decagram"></i> Workflow Action {{ $isBulkAction ? ' (Bulk)' : '' }}
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeWorkflowActionModal" style="opacity: 0.8;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="mdi mdi-information-outline" style="font-size: 24px; margin-right: 12px;"></i>
                        <div>
                            <strong>ISO Compliance Note:</strong> All workflow actions require documented remarks for audit trail purposes.
                            @if($isBulkAction)
                                <br><small class="text-muted">Selected: <strong>{{ count($selectedAudits) }}</strong> audit(s)</small>
                            @else
                                @php
                                    $audit = \App\Models\AuditModule\Audit::find($workflowActionAuditId);
                                @endphp
                                @if($audit)
                                <br><small class="text-muted">Current Status: <strong>{{ $audit->status_name }}</strong></small>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label font-weight-600">
                            Action <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="workflowAction" class="form-control modern-select" required style="border-radius: 4px;">
                            <option value="">Choose Action...</option>
                            @if(is_array($availableWorkflowActions) && count($availableWorkflowActions) > 0)
                                @foreach($availableWorkflowActions as $action)
                                    <option value="{{ $action->code ?? $action->id }}">
                                        @if(isset($action->icon))
                                            <i class="{{ $action->icon }}"></i>
                                        @endif
                                        {{ $action->name }}
                                        @if(isset($action->description))
                                            - {{ $action->description }}
                                        @endif
                                    </option>
                                @endforeach
                            @else
                                {{-- Fallback to hardcoded options if no dynamic actions available --}}
                                <option value="approve">Approve & Move to Next Step</option>
                                <option value="reject">Reject & Return</option>
                                <option value="return">Return for Correction</option>
                                <option value="hold">Hold / Suspend</option>
                            @endif
                        </select>
                        <small class="form-text text-muted">Select the action you want to perform on this audit.</small>
                    </div>

                    @if($selectedWorkflowActionRule && $selectedWorkflowActionRule->workflowAction && $workflowTargetStatusId)
                    @php
                        $targetStatus = \App\Models\AuditModule\AuditStatus::find($workflowTargetStatusId);
                        $targetType = $selectedWorkflowActionRule->target_type ?? 'specific';
                        
                        // Determine badge color based on target type
                        $badgeClass = 'success';
                        $badgeIcon = 'mdi-arrow-right';
                        if ($targetType === 'previous') {
                            $badgeClass = 'warning';
                            $badgeIcon = 'mdi-arrow-left';
                        } elseif ($targetType === 'current') {
                            $badgeClass = 'info';
                            $badgeIcon = 'mdi-pause';
                        } elseif ($targetType === 'next') {
                            $badgeClass = 'success';
                            $badgeIcon = 'mdi-arrow-right';
                        }
                    @endphp
                    <div class="form-group">
                        <label class="control-label font-weight-600">
                            Target Status
                        </label>
                        <div class="alert alert-{{ $badgeClass }} d-flex align-items-center mb-0" style="border-radius: 8px; border-left: 4px solid var(--{{ $badgeClass }}-color, #28a745);">
                            <i class="mdi {{ $badgeIcon }}" style="font-size: 24px; margin-right: 12px;"></i>
                            <div style="flex: 1;">
                                <strong>Audit will move to:</strong>
                                <div class="mt-1">
                                    <span class="badge badge-{{ $badgeClass }}" style="font-size: 0.9rem; padding: 6px 12px;">
                                        {{ $targetStatus ? $targetStatus->name : ($selectedWorkflowActionRule->target_status_name ?? 'N/A') }}
                                    </span>
                                </div>
                                @if($targetType === 'next')
                                    <small class="d-block mt-1 text-muted">
                                        <i class="mdi mdi-information-outline"></i> Moving to the next workflow step
                                    </small>
                                @elseif($targetType === 'previous')
                                    <small class="d-block mt-1 text-muted">
                                        <i class="mdi mdi-information-outline"></i> Returning to the previous workflow step
                                    </small>
                                @elseif($targetType === 'current')
                                    <small class="d-block mt-1 text-muted">
                                        <i class="mdi mdi-information-outline"></i> Status will remain unchanged
                                    </small>
                                @else
                                    <small class="d-block mt-1 text-muted">
                                        <i class="mdi mdi-information-outline"></i> Moving to specified status
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @elseif($selectedWorkflowActionRule && $selectedWorkflowActionRule->workflowAction)
                    <div class="form-group">
                        <label class="control-label font-weight-600">
                            Target Status
                        </label>
                        <div class="alert alert-info d-flex align-items-center mb-0" style="border-radius: 8px;">
                            <i class="mdi mdi-information-outline" style="font-size: 24px; margin-right: 12px;"></i>
                            <div>
                                <strong>Target status will be determined automatically</strong>
                                <small class="d-block mt-1 text-muted">Based on workflow configuration</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="control-label font-weight-600">
                            Remarks / Notes <span class="text-danger">*</span>
                        </label>
                        <textarea wire:model="workflowRemarks" class="form-control" rows="6" 
                                  placeholder="Enter detailed remarks for this action. This is required for ISO compliance and audit trail purposes..." 
                                  required style="border-radius: 4px;"></textarea>
                        <small class="form-text text-muted">
                            <i class="mdi mdi-alert-circle-outline"></i> 
                            Minimum 10 characters required. Document the reason for this action.
                        </small>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn btn-secondary modern-btn" wire:click="closeWorkflowActionModal" style="border-radius: 4px;">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success modern-btn" wire:click="submitWorkflowAction" style="border-radius: 4px;">
                        <i class="mdi mdi-check-circle"></i> Submit Action
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize popovers with proper settings
        function initPopovers() {
            $('.status-hover-badge[data-toggle="popover"]').each(function() {
                var $this = $(this);
                var contentId = $this.data('content-id');
                
                // Get content from hidden div
                var content = $('#' + contentId).html();
                
                // Initialize or update popover
                $this.popover({
                    container: 'body',
                    placement: 'bottom',
                    trigger: 'hover',
                    html: true,
                    content: content
                });
            });
        }
        
        // Initialize on page load
        initPopovers();
        
        // Re-initialize after Livewire updates
        document.addEventListener('livewire:load', function() {
            initPopovers();
        });
        
        document.addEventListener('livewire:update', function() {
            setTimeout(initPopovers, 100);
        });
    });
</script>
