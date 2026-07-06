<div>
    <style>
        .modern-table-wrapper {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
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
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
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
            background: #ffeaea !important;
        }

        .modern-table tbody td {
            padding: 16px 24px;
            font-size: 14px;
            color: #202124;
            vertical-align: middle;
        }

        .modern-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .modern-action-btn {
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
            margin-right: 4px;
            background: transparent;
        }

        .modern-action-btn:hover {
            background: #f1f3f4;
            border-color: #dc3545;
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

        .modern-pagination {
            padding: 16px 24px;
            border-top: 1px solid #e8eaed;
            background: #ffffff;
        }

        /* Modal Scrollable Styles */
        .modal-dialog-scrollable {
            max-height: 90vh;
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-dialog-scrollable .modal-header {
            flex-shrink: 0;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(90vh - 120px);
            flex: 1;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
        }
    </style>

    <div class="modern-table-wrapper">
        <div class="modern-search-bar">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="position-relative">
                        <i class="mdi mdi-magnify modern-search-icon"></i>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control modern-search-input" placeholder="Search workflow action rules...">
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <button wire:click="openModal()" class="btn btn-primary" style="border-radius: 20px; padding: 8px 20px;">
                        <i class="mdi mdi-plus-circle"></i> Add Rule
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>From Status</th>
                        <th>Target Type</th>
                        <th>Target Status</th>
                        <th>Validation Conditions</th>
                        <th>Validate Progression</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                    <tr>
                        <td>
                            <strong>{{ $rule->workflowAction->name ?? 'N/A' }}</strong>
                            @if($rule->workflowAction && $rule->workflowAction->icon)
                                <i class="{{ $rule->workflowAction->icon }}"></i>
                            @endif
                        </td>
                        <td>
                            @if($rule->fromStatus)
                                <span class="modern-badge" style="background: #e3f2fd; color: #1976d2;">
                                    {{ $rule->fromStatus->name }}
                                </span>
                            @elseif($rule->from_status_name)
                                <span class="modern-badge" style="background: #f5f5f5; color: #5f6368;">
                                    {{ $rule->from_status_name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="modern-badge" style="background: #fff3cd; color: #856404;">
                                {{ ucfirst($rule->target_type) }}
                            </span>
                        </td>
                        <td>
                            @if($rule->targetStatus)
                                <span class="modern-badge" style="background: #d4edda; color: #155724;">
                                    {{ $rule->targetStatus->name }}
                                </span>
                            @elseif($rule->target_type === 'next')
                                <span class="text-muted">Next Workflow Step</span>
                            @elseif($rule->target_type === 'current')
                                <span class="text-muted">Current Status</span>
                            @elseif($rule->target_type === 'previous')
                                <span class="text-muted">Previous Step</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="max-width: 350px;">
                            @if($rule->conditions && is_array($rule->conditions))
                                @php
                                    $activeConditions = array_filter($rule->conditions, function($v) {
                                        return $v !== null && $v !== false && $v !== 0 && $v !== '';
                                    });
                                @endphp
                                @if(count($activeConditions) > 0)
                                    <div class="d-flex flex-wrap gap-1" style="max-height: 120px; overflow-y: auto;">
                                        @foreach($activeConditions as $key => $value)
                                            <span class="modern-badge" style="background: #e3f2fd; color: #1976d2; font-size: 0.7rem; padding: 4px 10px; margin-bottom: 4px; display: inline-block;" title="{{ ucwords(str_replace('_', ' ', $key)) }}">
                                                <i class="mdi mdi-check-circle" style="font-size: 0.65rem; margin-right: 4px;"></i>
                                                {{ ucwords(str_replace('_', ' ', substr($key, 0, 25))) }}
                                                @if(is_numeric($value) && $value > 0)
                                                    <strong style="margin-left: 4px;">: {{ $value }}</strong>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                    <small class="text-muted" style="font-size: 0.65rem;">{{ count($activeConditions) }} condition(s)</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($rule->validate_progression)
                                <span class="modern-badge" style="background: #fee; color: #c33;">Yes</span>
                            @else
                                <span class="modern-badge" style="background: #f5f5f5; color: #5f6368;">No</span>
                            @endif
                        </td>
                        <td>
                            @if($rule->is_active)
                                <span class="modern-badge" style="background: #d4edda; color: #155724;">Active</span>
                            @else
                                <span class="modern-badge" style="background: #f8d7da; color: #721c24;">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <button wire:click="openModal('{{ $rule->id }}')" class="modern-action-btn" title="Edit" style="color: #d97706;">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button wire:click="delete('{{ $rule->id }}')" wire:confirm="Are you sure you want to delete this rule?" class="modern-action-btn" title="Delete" style="color: #c33;">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="modern-empty-state">
                            <i class="mdi mdi-information-outline"></i>
                            <p>No workflow action rules found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="modern-pagination">
            {{ $rules->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 8px; max-height: 90vh; display: flex; flex-direction: column;">
                <div class="modal-header" style="background: #1a73e8; color: white; border-radius: 8px 8px 0 0; flex-shrink: 0;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $isEdit ? 'pencil' : 'plus-circle' }}"></i>
                        {{ $isEdit ? 'Edit' : 'Create' }} Workflow Action Rule
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeModal" style="opacity: 0.8;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px; overflow-y: auto; flex: 1; max-height: calc(90vh - 120px);">
                    <div class="form-group">
                        <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                            Workflow Action <span class="text-danger">*</span>
                        </label>
                        <select wire:model="workflow_action_id" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;" required>
                            <option value="">Select Action...</option>
                            @foreach($workflowActions as $action)
                                <option value="{{ $action->id }}">
                                    @if($action->icon)
                                        <i class="{{ $action->icon }}"></i>
                                    @endif
                                    {{ $action->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('workflow_action_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                                    From Status (ID)
                                </label>
                                <select wire:model="from_status_id" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                    <option value="">Select Status...</option>
                                    @foreach($auditStatuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select a status from the list</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                                    From Status (Name)
                                </label>
                                <input wire:model="from_status_name" type="text" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;" placeholder="Or enter status name">
                                <small class="text-muted">Or enter status name directly</small>
                                @error('from_status_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                            Target Type <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="target_type" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;" required>
                            <option value="next">Next Workflow Step</option>
                            <option value="specific">Specific Status</option>
                            <option value="current">Current Status (Stay)</option>
                            <option value="previous">Previous Step</option>
                        </select>
                        <small class="text-muted">What should happen when this action is performed?</small>
                    </div>

                    @if($target_type === 'specific')
                    <div class="form-group">
                        <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                            Target Status <span class="text-danger">*</span>
                        </label>
                        <select wire:model="target_status_id" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;" required>
                            <option value="">Select Target Status...</option>
                            @foreach($auditStatuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                        @error('target_status_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="validate_progression" type="checkbox" class="custom-control-input" id="validateProgression">
                                    <label class="custom-control-label" for="validateProgression" style="color: #5f6368;">
                                        Validate Workflow Progression
                                    </label>
                                </div>
                                <small class="text-muted">Check if workflow step progression should be validated</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                                    Order Index
                                </label>
                                <input wire:model="order_index" type="number" min="0" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                            Success Message
                        </label>
                        <textarea wire:model="success_message" class="form-control" rows="2" style="border-radius: 8px; border: 1px solid #dadce0;" placeholder="Custom success message (optional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                            Error Message
                        </label>
                        <textarea wire:model="error_message" class="form-control" rows="2" style="border-radius: 8px; border: 1px solid #dadce0;" placeholder="Custom error message (optional)"></textarea>
                    </div>

                    <!-- Validation Conditions Section -->
                    <div class="card mt-3" style="border: 1px solid #e8eaed; border-radius: 8px;">
                        <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e8eaed;">
                            <h6 class="mb-0" style="font-weight: 600; color: #5f6368;">
                                <i class="mdi mdi-check-circle-outline"></i> Validation Conditions
                            </h6>
                            <small class="text-muted">Configure what must be validated before this action can be performed</small>
                        </div>
                        <div class="card-body">
                            <!-- Findings Validations -->
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_findings" type="checkbox" class="custom-control-input" id="requireFindings">
                                    <label class="custom-control-label" for="requireFindings" style="color: #5f6368;">
                                        Require Findings - At least one finding must be recorded
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                                    Minimum Findings Count
                                </label>
                                <input wire:model="min_findings_count" type="number" min="0" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                <small class="text-muted">Minimum number of findings required (0 = no minimum)</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_nc_for_findings" type="checkbox" class="custom-control-input" id="requireNcForFindings">
                                    <label class="custom-control-label" for="requireNcForFindings" style="color: #5f6368;">
                                        Require NC for Findings - All findings requiring NC must have NCs raised
                                    </label>
                                </div>
                            </div>

                            <!-- Root Cause Analysis Validations -->
                            <hr style="margin: 16px 0; border-color: #e8eaed;">
                            <h6 style="font-weight: 600; color: #5f6368; margin-bottom: 12px;">Root Cause Analysis</h6>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_rca_for_all_ncs" type="checkbox" class="custom-control-input" id="requireRcaForAllNcs">
                                    <label class="custom-control-label" for="requireRcaForAllNcs" style="color: #5f6368;">
                                        Require RCA for All NCs - All non-conformances must have root cause analysis
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_rca_approved" type="checkbox" class="custom-control-input" id="requireRcaApproved">
                                    <label class="custom-control-label" for="requireRcaApproved" style="color: #5f6368;">
                                        Require RCA Approved - All root cause analyses must be approved
                                    </label>
                                </div>
                            </div>

                            <!-- CAPA Validations -->
                            <hr style="margin: 16px 0; border-color: #e8eaed;">
                            <h6 style="font-weight: 600; color: #5f6368; margin-bottom: 12px;">Corrective Actions (CAPA)</h6>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_capa_for_all_ncs" type="checkbox" class="custom-control-input" id="requireCapaForAllNcs">
                                    <label class="custom-control-label" for="requireCapaForAllNcs" style="color: #5f6368;">
                                        Require CAPA for All NCs - All non-conformances must have corrective actions assigned
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                                    Minimum CAPA per NC
                                </label>
                                <input wire:model="min_capa_per_nc" type="number" min="0" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                <small class="text-muted">Minimum number of CAPAs required per NC (0 = no minimum)</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_capa_owners" type="checkbox" class="custom-control-input" id="requireCapaOwners">
                                    <label class="custom-control-label" for="requireCapaOwners" style="color: #5f6368;">
                                        Require CAPA Owners - All corrective actions must have assigned owners
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_capa_due_dates" type="checkbox" class="custom-control-input" id="requireCapaDueDates">
                                    <label class="custom-control-label" for="requireCapaDueDates" style="color: #5f6368;">
                                        Require CAPA Due Dates - All corrective actions must have due dates
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_capa_implemented" type="checkbox" class="custom-control-input" id="requireCapaImplemented">
                                    <label class="custom-control-label" for="requireCapaImplemented" style="color: #5f6368;">
                                        Require CAPA Implemented - All corrective actions must be implemented
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="require_capa_verified" type="checkbox" class="custom-control-input" id="requireCapaVerified">
                                    <label class="custom-control-label" for="requireCapaVerified" style="color: #5f6368;">
                                        Require CAPA Verified - All corrective actions must be verified
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Validation Conditions Section -->
                    @if(count($availableValidationOptions) > 0)
                    <div class="card mt-3" style="border: 1px solid #e8eaed; border-radius: 8px;">
                        <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e8eaed;">
                            <h6 class="mb-0" style="font-weight: 600; color: #5f6368;">
                                <i class="mdi mdi-auto-fix"></i> Dynamic Validation Conditions
                            </h6>
                            <small class="text-muted">Automatically discovered from audit models</small>
                        </div>
                        <div class="card-body">
                            @foreach($availableValidationOptions as $category => $options)
                                <div class="mb-4">
                                    <h6 style="font-weight: 600; color: #5f6368; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #e8eaed;">
                                        <i class="mdi mdi-folder-outline"></i> {{ $category }}
                                    </h6>
                                    @foreach($options as $option)
                                        @if($option['type'] === 'boolean')
                                            <div class="form-group mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input 
                                                        wire:model="dynamicConditions.{{ $option['key'] }}" 
                                                        type="checkbox" 
                                                        class="custom-control-input" 
                                                        id="dynamic_{{ $option['key'] }}">
                                                    <label class="custom-control-label" for="dynamic_{{ $option['key'] }}" style="color: #5f6368;">
                                                        <strong>{{ $option['label'] }}</strong>
                                                        @if(isset($option['description']))
                                                            <br><small class="text-muted">{{ $option['description'] }}</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @elseif($option['type'] === 'integer')
                                            <div class="form-group mb-2">
                                                <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">
                                                    {{ $option['label'] }}
                                                </label>
                                                <input 
                                                    wire:model="dynamicConditions.{{ $option['key'] }}" 
                                                    type="number" 
                                                    min="0" 
                                                    class="form-control" 
                                                    style="border-radius: 8px; border: 1px solid #dadce0;"
                                                    placeholder="Enter minimum count">
                                                @if(isset($option['description']))
                                                    <small class="text-muted">{{ $option['description'] }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @if(!$loop->last)
                                    <hr style="margin: 16px 0; border-color: #e8eaed;">
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="form-group mt-3">
                        <div class="custom-control custom-checkbox">
                            <input wire:model="is_active" type="checkbox" class="custom-control-input" id="isActive">
                            <label class="custom-control-label" for="isActive" style="color: #5f6368;">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e8eaed; padding: 16px 24px; flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal" style="border-radius: 8px;">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="save" style="border-radius: 8px;">
                        <i class="mdi mdi-check-circle"></i> {{ $isEdit ? 'Update' : 'Create' }} Rule
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

@section('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('notify', (data) => {
            const type = data[0].type || 'info';
            const message = data[0].message || '';
            
            // You can integrate with your notification system here
            alert(message);
        });
    });
</script>
@endsection
