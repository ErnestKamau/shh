<div>
    <style>
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
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-soft-10);
        }

        .modern-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #5f6368;
            font-size: 18px;
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
            background: #e8f0fe !important;
        }

        .modern-table tbody td {
            padding: 16px 24px;
            font-size: 14px;
            color: #202124;
            vertical-align: middle;
        }

        .modern-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .modern-action-btn {
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
            margin-right: 4px;
        }

        .modern-action-btn:hover {
            background: #f1f3f4;
            border-color: var(--color-primary);
        }

        .btn-modern {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s ease;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .modern-empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .modern-empty-state i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 16px;
        }

        .modern-empty-state p {
            color: #6c757d;
            font-size: 1rem;
            margin: 0;
        }

        .modern-pagination {
            padding: 16px 24px;
            border-top: 1px solid #e8eaed;
            background: #ffffff;
        }

        .modal-dialog-scrollable {
            max-height: 90vh;
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-dialog-scrollable .modal-content form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: inherit;
        }

        .modal-dialog-scrollable .modal-header,
        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(90vh - 120px);
            flex: 1;
        }
    </style>

    <!-- Search Bar -->
    <div class="modern-search-bar">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="mdi mdi-magnify modern-search-icon"></i>
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           class="modern-search-input" 
                           placeholder="Search...">
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button wire:click="openModal()" class="btn btn-modern btn-primary">
                    <i class="mdi mdi-plus"></i> Add New
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Name</th>
                    @if($type === 'audit_types' || $type === 'finding_categories' || $type === 'audit_statuses' || $type === 'workflow_actions' || $type === 'severity_scales' || $type === 'likelihood_scales' || $type === 'verification_results')
                    <th>Code</th>
                    @endif
                    @if($type === 'audit_statuses')
                    <th>Workflow Step</th>
                    <th>Order</th>
                    @elseif($type === 'verification_results')
                    <th>Next Workflow Step</th>
                    <th>Requires Reopen</th>
                    @elseif($type === 'workflow_actions')
                    <th>Icon</th>
                    <th>Requires Remarks</th>
                    <th>Requires Target</th>
                    @elseif($type === 'finding_categories')
                    <th>Severity</th>
                    <th>Requires CAPA</th>
                    @elseif($type === 'risk_levels' || $type === 'severity_scales' || $type === 'likelihood_scales')
                    <th>Score</th>
                    <th>Color</th>
                    <th>Order</th>
                    @endif
                    <th>Description</th>
                    <th>Status</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td style="font-weight: 500; color: #202124;">
                        <strong>{{ $item->name }}</strong>
                        @if($type === 'verification_results' && $item->color_code)
                        <span class="badge ml-2" style="background-color: {{ $item->color_code }}; width: 20px; height: 20px; display: inline-block; border-radius: 50%;"></span>
                        @endif
                    </td>
                    @if($type === 'audit_types' || $type === 'finding_categories' || $type === 'audit_statuses' || $type === 'workflow_actions' || $type === 'severity_scales' || $type === 'likelihood_scales' || $type === 'verification_results')
                    <td style="color: #5f6368;"><code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 0.875rem;">{{ $item->code ?? '-' }}</code></td>
                    @endif
                    @if($type === 'verification_results')
                    <td style="color: #5f6368;">
                        @php
                            $workflowSteps = getAuditWorkflowSteps();
                        @endphp
                        @if($item->next_workflow_step)
                        @php
                            $stepName = $item->next_workflow_step == 8 ? 'N/A' : ($workflowSteps[$item->next_workflow_step] ?? 'N/A');
                        @endphp
                        <span class="modern-badge" style="background: var(--color-primary-soft); color: var(--color-primary); font-weight: 600;">Step {{ $item->next_workflow_step }}: {{ $stepName }}</span>
                        @else
                        <span class="text-muted">Not configured</span>
                        @endif
                    </td>
                    <td>
                        @if($item->requires_reopen)
                        <span class="modern-badge" style="background: #fff3cd; color: #856404;">Yes</span>
                        @else
                        <span class="modern-badge" style="background: #f5f5f5; color: #5f6368;">No</span>
                        @endif
                    </td>
                    @elseif($type === 'workflow_actions')
                    <td style="color: #5f6368;">
                        @if($item->icon)
                        <i class="{{ $item->icon }}"></i> {{ $item->icon }}
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($item->requires_remarks)
                        <span class="modern-badge" style="background: #fee; color: #c33;">Yes</span>
                        @else
                        <span class="modern-badge" style="background: #f5f5f5; color: #5f6368;">No</span>
                        @endif
                    </td>
                    <td>
                        @if($item->requires_target_status)
                        <span class="modern-badge" style="background: #fee; color: #c33;">Yes</span>
                        @else
                        <span class="modern-badge" style="background: #f5f5f5; color: #5f6368;">No</span>
                        @endif
                    </td>
                    @elseif($type === 'audit_statuses')
                    <td style="color: #5f6368;">
                        @if($item->workflow_step)
                        <span class="modern-badge" style="background: var(--color-primary-soft); color: var(--color-primary); font-weight: 600;">Step {{ $item->workflow_step }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td style="color: #5f6368;">{{ $item->order_index ?? '-' }}</td>
                    @elseif($type === 'finding_categories')
                    <td style="color: #5f6368;">{{ $item->severity ?? '-' }}</td>
                    <td>
                        @if($item->requires_capa)
                        <span class="modern-badge" style="background: #fee; color: #c33;">Yes</span>
                        @else
                        <span class="modern-badge" style="background: #f5f5f5; color: #5f6368;">No</span>
                        @endif
                    </td>
                    @elseif($type === 'risk_levels')
                    <td>
                        <span class="modern-badge" style="background-color: {{ $item->color_code ?? $item->color ?? '#6c757d' }}; color: white;">
                            {{ $item->color_code ?? $item->color ?? 'N/A' }}
                        </span>
                    </td>
                    <td style="color: #5f6368;">{{ $item->severity_score ?? $item->score ?? '-' }}</td>
                    @elseif($type === 'severity_scales' || $type === 'likelihood_scales')
                    <td style="color: #5f6368; font-weight: 600;">{{ $item->score ?? '-' }}</td>
                    <td>
                        <span class="modern-badge" style="background-color: {{ $item->color_code ?? '#6c757d' }}; color: white;">
                            {{ $item->color_code ?? 'N/A' }}
                        </span>
                    </td>
                    <td style="color: #5f6368;">{{ $item->order_index ?? '-' }}</td>
                    @endif
                    <td style="color: #5f6368;">{{ Str::limit($item->description ?? '-', 50) }}</td>
                    <td>
                        <span class="modern-badge {{ $item->is_active ? 'badge-success' : 'badge-secondary' }}" style="background: {{ $item->is_active ? '#e6f4ea' : '#f5f5f5' }}; color: {{ $item->is_active ? '#137333' : '#5f6368' }};">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button wire:click="openModal('{{ $item->id }}')" class="modern-action-btn" title="Edit" style="color: #d97706;">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button wire:click="toggleActive('{{ $item->id }}')" class="modern-action-btn" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}" style="color: {{ $item->is_active ? '#5f6368' : '#137333' }};">
                                <i class="mdi mdi-{{ $item->is_active ? 'close' : 'check' }}"></i>
                            </button>
                            <button wire:click="delete('{{ $item->id }}')" wire:confirm="Are you sure you want to delete '{{ $item->name }}'? This action cannot be undone." class="modern-action-btn" title="Delete" style="color: #c33;">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="modern-empty-state">
                        <i class="mdi mdi-cog-outline"></i>
                        <p>No items found. Click "Add New" to create one.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="modern-pagination">
        {{ $items->links() }}
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header {{ $isEdit ? 'bg-primary' : 'bg-success' }} text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $isEdit ? 'pencil' : 'plus' }}"></i> {{ $isEdit ? 'Edit' : 'Add' }} {{ $typeTitle }}
                        </h5>
                        <button type="button" class="close text-white" wire:click="$set('showModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($type === 'audit_types' || $type === 'audit_statuses' || $type === 'workflow_actions' || $type === 'severity_scales' || $type === 'likelihood_scales' || $type === 'verification_results')
                        <div class="form-group">
                            <label>Code <span class="text-danger">*</span></label>
                            <input wire:model="code" type="text" class="form-control @error('code') is-invalid @enderror" placeholder="e.g., {{ $type === 'severity_scales' ? 'SEV-1' : ($type === 'likelihood_scales' ? 'LIK-1' : ($type === 'verification_results' ? 'EFF, NOTEFF, PART' : 'INT, EXT, ACC')) }}" maxlength="{{ $type === 'workflow_actions' || $type === 'verification_results' ? '50' : ($type === 'severity_scales' || $type === 'likelihood_scales' ? '20' : '10') }}" required>
                            <small class="text-muted">Unique code (max {{ $type === 'workflow_actions' || $type === 'verification_results' ? '50' : ($type === 'severity_scales' || $type === 'likelihood_scales' ? '20' : '10') }} characters). Will be auto-generated from name if left empty.</small>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif

                        @if($type === 'workflow_actions')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Icon Class</label>
                                    <input wire:model="icon" type="text" class="form-control" placeholder="e.g., mdi-check-decagram" style="border-radius: 8px; border: 1px solid #dadce0;">
                                    <small class="text-muted">Material Design Icons class name</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Color Code</label>
                                    <input wire:model="color_code" type="color" class="form-control" style="height: 40px; border-radius: 8px; border: 1px solid #dadce0;">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Badge Class</label>
                            <select wire:model="badge_class" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                <option value="primary">Primary</option>
                                <option value="success">Success</option>
                                <option value="danger">Danger</option>
                                <option value="warning">Warning</option>
                                <option value="info">Info</option>
                                <option value="secondary">Secondary</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input wire:model="requires_remarks" type="checkbox" class="custom-control-input" id="requiresRemarks">
                                        <label class="custom-control-label" for="requiresRemarks" style="color: #5f6368;">Requires Remarks</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Min Remarks Length</label>
                                    <input wire:model="min_remarks_length" type="number" min="0" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input wire:model="requires_target_status" type="checkbox" class="custom-control-input" id="requiresTargetStatus">
                                <label class="custom-control-label" for="requiresTargetStatus" style="color: #5f6368;">Requires Target Status Selection</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Order Index</label>
                            <input wire:model="order_index" type="number" min="0" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                        </div>
                        @endif

                        @if($type === 'audit_statuses')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Workflow Step</label>
                                    <select wire:model="workflow_step" class="form-control @error('workflow_step') is-invalid @enderror" style="border-radius: 8px; border: 1px solid #dadce0;">
                                        <option value="">None (Not in workflow)</option>
                                        @foreach(getAuditWorkflowSteps() as $stepNum => $stepName)
                                            <option value="{{ $stepNum }}">Step {{ $stepNum }} - {{ $stepName }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select the workflow step this status belongs to</small>
                                    @error('workflow_step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Order Index</label>
                                    <input wire:model="order_index" type="number" min="0" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                    <small class="text-muted">Display order (lower numbers appear first)</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Color Code</label>
                            <input wire:model="color_code" type="color" class="form-control" style="height: 40px; border-radius: 8px; border: 1px solid #dadce0;">
                            <small class="text-muted">Color for status badge/display</small>
                        </div>
                        @endif

                        @if($type === 'finding_categories')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Code</label>
                                    <input wire:model="code" type="text" class="form-control" placeholder="e.g., NC, OBS" style="border-radius: 8px; border: 1px solid #dadce0;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Severity</label>
                                    <select wire:model="severity" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                        <option value="">Select...</option>
                                        <option value="Minor">Minor</option>
                                        <option value="Major">Major</option>
                                        <option value="Critical">Critical</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input wire:model="requires_capa" type="checkbox" class="custom-control-input" id="requiresCapa">
                                <label class="custom-control-label" for="requiresCapa" style="color: #5f6368;">Requires Corrective Action</label>
                            </div>
                        </div>
                        @endif

                        @if($type === 'risk_levels')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Color</label>
                                    <input wire:model="color" type="color" class="form-control" style="height: 40px; border-radius: 8px; border: 1px solid #dadce0;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Score <span class="text-danger">*</span></label>
                                    <input wire:model="score" type="number" min="0" max="100" class="form-control @error('score') is-invalid @enderror" style="border-radius: 8px; border: 1px solid #dadce0;">
                                    @error('score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Criteria</label>
                            <textarea wire:model="criteria" class="form-control" rows="2" placeholder="Criteria for assigning this level" style="border-radius: 8px; border: 1px solid #dadce0;"></textarea>
                        </div>
                        @endif

                        @if($type === 'severity_scales' || $type === 'likelihood_scales')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Score <span class="text-danger">*</span></label>
                                    <input wire:model="score" type="number" min="1" max="100" class="form-control @error('score') is-invalid @enderror" placeholder="1-100" style="border-radius: 8px; border: 1px solid #dadce0;">
                                    <small class="text-muted">Numeric score for calculation</small>
                                    @error('score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Color Code</label>
                                    <input wire:model="color_code" type="color" class="form-control" style="height: 40px; border-radius: 8px; border: 1px solid #dadce0;">
                                    <small class="text-muted">Color for UI display</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Order Index</label>
                                    <input wire:model="order_index" type="number" min="0" class="form-control" placeholder="0" style="border-radius: 8px; border: 1px solid #dadce0;">
                                    <small class="text-muted">Display order (lower first)</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($type === 'verification_results')
                        <div class="form-group">
                            <label>Color Code</label>
                            <input wire:model="color_code" type="color" class="form-control" style="width: 100px;" value="#17a2b8">
                        </div>
                        <div class="form-group">
                            <label>Next Workflow Step <span class="text-danger">*</span></label>
                            @php
                                $workflowSteps = getAuditWorkflowSteps();
                            @endphp
                            <select wire:model="next_workflow_step" class="form-control @error('next_workflow_step') is-invalid @enderror" required>
                                <option value="">Select workflow step...</option>
                                @foreach($workflowSteps as $stepNum => $stepName)
                                <option value="{{ $stepNum }}">Step {{ $stepNum }}: {{ $stepName }}</option>
                                @endforeach
                                <option value="8">Step 8: N/A</option>
                            </select>
                            <small class="text-muted">This determines where the audit workflow moves when this result is selected</small>
                            @error('next_workflow_step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input wire:model="requires_reopen" type="checkbox" class="form-check-input" id="requiresReopen" value="1">
                                <label class="form-check-label" for="requiresReopen">
                                    Requires Reopen
                                </label>
                            </div>
                        </div>
                        @endif

                        @if($type === 'rca_methods')
                        <div class="form-group">
                            <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Template (JSON)</label>
                            <textarea wire:model="template" class="form-control" rows="4" placeholder='{"steps": ["Why 1", "Why 2", ...]}' style="border-radius: 8px; border: 1px solid #dadce0;"></textarea>
                            <small class="text-muted">Optional JSON template for the analysis method</small>
                        </div>
                        @endif

                        <div class="form-group">
                            <label>Description</label>
                            <textarea wire:model="description" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input wire:model="is_active" type="checkbox" class="form-check-input" id="isActive" value="1" checked>
                                <label class="form-check-label" for="isActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn {{ $isEdit ? 'btn-primary' : 'btn-success' }}">
                            <i class="mdi mdi-content-save"></i> {{ $isEdit ? 'Save Changes' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>
