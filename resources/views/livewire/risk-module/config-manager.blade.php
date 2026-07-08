<div>
    @php
        $riskWorkflowStepDefinitions = function_exists('getRiskWorkflowStepDefinitions')
            ? getRiskWorkflowStepDefinitions()
            : [
                1 => 'Identified',
                2 => 'Under Assessment',
                3 => 'Under Evaluation',
                4 => 'Treatment Planning',
                5 => 'Treatment Implementation',
                6 => 'Under Monitoring',
                7 => 'Closed',
            ];
    @endphp
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
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
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
            background: #ffeaea !important;
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
                <button wire:click="openModal()" class="btn btn-primary">
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
                    @if($type === 'risk_statuses' || $type === 'risk_categories' || $type === 'risk_sources' || $type === 'treatment_types' || $type === 'likelihood_scales' || $type === 'severity_scales')
                    <th>Code</th>
                    @endif
                    @if($type === 'risk_statuses')
                    <th>Workflow Step</th>
                    <th>Order</th>
                    <th>Color</th>
                    @endif
                    @if($type === 'likelihood_scales' || $type === 'severity_scales')
                    <th>Score</th>
                    <th>Order</th>
                    <th>Color</th>
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
                    </td>
                    @if($type === 'risk_statuses' || $type === 'risk_categories' || $type === 'risk_sources' || $type === 'treatment_types' || $type === 'likelihood_scales' || $type === 'severity_scales')
                    <td style="color: #5f6368;"><code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 0.875rem;">{{ $item->code ?? '-' }}</code></td>
                    @endif
                    @if($type === 'risk_statuses')
                    <td style="color: #5f6368;">
                        @if($item->workflow_step)
                        <span class="modern-badge" style="background: #e3f2fd; color: #1976d2; font-weight: 600;">
                            Step {{ $item->workflow_step }} - {{ $riskWorkflowStepDefinitions[(int) $item->workflow_step] ?? 'Workflow Step' }}
                        </span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td style="color: #5f6368;">{{ $item->order_index ?? '-' }}</td>
                    <td>
                        @if($item->color_code)
                        <span class="modern-badge" style="background-color: {{ $item->color_code }}; color: white;">
                            {{ $item->color_code }}
                        </span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    @endif
                    @if($type === 'likelihood_scales' || $type === 'severity_scales')
                    <td>
                        <span class="modern-badge badge-info" style="font-size: 0.9rem;">
                            {{ $item->score }}
                        </span>
                    </td>
                    <td style="color: #5f6368;">{{ $item->order_index ?? '-' }}</td>
                    <td>
                        @if($item->color_code)
                        <span class="modern-badge" style="background-color: {{ $item->color_code }}; color: white;">
                            {{ $item->color_code }}
                        </span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
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
        <div class="modal-dialog modal-lg" role="document">
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

                        @if($type === 'risk_statuses' || $type === 'risk_categories' || $type === 'risk_sources' || $type === 'treatment_types' || $type === 'likelihood_scales' || $type === 'severity_scales')
                        <div class="form-group">
                            <label>Code <span class="text-danger">*</span></label>
                            <input wire:model="code" type="text" class="form-control @error('code') is-invalid @enderror" placeholder="Will be auto-generated if left empty" maxlength="{{ ($type === 'likelihood_scales' || $type === 'severity_scales') ? 50 : 10 }}">
                            <small class="text-muted">Unique code (max {{ ($type === 'likelihood_scales' || $type === 'severity_scales') ? 50 : 10 }} chars). Will be auto-generated from name if left empty.</small>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif

                        @if($type === 'risk_statuses')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Workflow Step</label>
                                    <select wire:model="workflow_step" class="form-control @error('workflow_step') is-invalid @enderror">
                                        <option value="">None (Not in workflow)</option>
                                        @foreach($riskWorkflowStepDefinitions as $stepNumber => $stepLabel)
                                        <option value="{{ $stepNumber }}">Step {{ $stepNumber }} - {{ $stepLabel }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select the workflow step this status maps to (steps are fixed workflow phases).</small>
                                    @error('workflow_step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Order Index</label>
                                    <input wire:model="order_index" type="number" min="0" class="form-control">
                                    <small class="text-muted">Display order (lower numbers appear first)</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Color Code</label>
                                    <input wire:model="color_code" type="color" class="form-control" style="height: 40px;">
                                    <small class="text-muted">Color for status badge/display</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($type === 'likelihood_scales' || $type === 'severity_scales')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Score <span class="text-danger">*</span></label>
                                    <input wire:model="score" type="number" min="1" max="10" class="form-control @error('score') is-invalid @enderror" required>
                                    <small class="text-muted">Numeric score (1-10)</small>
                                    @error('score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Order Index</label>
                                    <input wire:model="order_index" type="number" min="0" class="form-control">
                                    <small class="text-muted">Display order</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Color Code</label>
                                    <input wire:model="color_code" type="color" class="form-control" style="height: 40px;">
                                    <small class="text-muted">Color for badge</small>
                                </div>
                            </div>
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


