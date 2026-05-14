<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-sitemap text-primary"></i>
                                {{ __('equipment.disposal_approval_workflows') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('equipment.disposal_workflow_subtitle') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('equipment.asset-types.index') }}" class="btn btn-outline-primary me-2">
                                <i class="mdi mdi-format-list-bulleted-type"></i> {{ __('equipment.asset_types') }}
                            </a>
                            <a href="{{ route('equipment.asset-locations.index') }}" class="btn btn-outline-primary me-2">
                                <i class="mdi mdi-map-marker"></i> {{ __('equipment.asset_locations') }}
                            </a>
                            <a href="{{ route('equipment.disposal.workflow.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.create_workflow') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.search') }}</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('equipment.search_workflow_name') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.equipment_type') }}</label>
                                <div x-data="{ open: false }" @click.outside="open = false" class="tag-select-container position-relative">
                                    <div class="tag-select-input form-control d-flex align-items-center flex-wrap" @click="open = !open" style="gap:6px; min-height: 38px; height: auto; cursor: pointer;">
                                        @if($typeFilterName)
                                            <span class="tag-badge badge bg-primary text-white d-flex align-items-center rounded-pill py-1 px-2" style="font-weight: 500;">
                                                {{ $typeFilterName }}
                                                <i class="mdi mdi-close-circle ms-1" wire:click.stop="$set('typeFilter', ''); $set('typeFilterName', '')" style="cursor:pointer; font-size:14px;"></i>
                                            </span>
                                        @endif
                                        <input type="text" wire:model.live="typeFilterSearch" @click.stop="open = true" class="tag-input border-0 flex-grow-1" placeholder="{{ __('equipment.all_types') }}" autocomplete="off" style="outline: none; background: transparent; min-width: 60px;">
                                    </div>
                                    <div x-show="open" class="tag-dropdown position-absolute w-100 bg-white border border-top-0 shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; border-radius: 0 0 6px 6px;">
                                        @foreach($assetTypes as $type)
                                            <div class="tag-dropdown-item px-3 py-2" @click.stop="open = false; @this.set('typeFilter', {{ $type->id }})" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='transparent';">
                                                {{ $type->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.location') }}</label>
                                <div x-data="{ open: false }" @click.outside="open = false" class="tag-select-container position-relative">
                                    <div class="tag-select-input form-control d-flex align-items-center flex-wrap" @click="open = !open" style="gap:6px; min-height: 38px; height: auto; cursor: pointer;">
                                        @if($locationFilterName)
                                            <span class="tag-badge badge bg-primary text-white d-flex align-items-center rounded-pill py-1 px-2" style="font-weight: 500;">
                                                {{ $locationFilterName }}
                                                <i class="mdi mdi-close-circle ms-1" wire:click.stop="$set('locationFilter', ''); $set('locationFilterName', '')" style="cursor:pointer; font-size:14px;"></i>
                                            </span>
                                        @endif
                                        <input type="text" wire:model.live="locationFilterSearch" @click.stop="open = true" class="tag-input border-0 flex-grow-1" placeholder="{{ __('equipment.all_locations') }}" autocomplete="off" style="outline: none; background: transparent; min-width: 60px;">
                                    </div>
                                    <div x-show="open" class="tag-dropdown position-absolute w-100 bg-white border border-top-0 shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; border-radius: 0 0 6px 6px;">
                                        @foreach($locations as $location)
                                            <div class="tag-dropdown-item px-3 py-2" @click.stop="open = false; @this.set('locationFilter', {{ $location->id }})" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='transparent';">
                                                {{ $location->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflows List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle equipment-table">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 110px;">{{ __('equipment.actions') }}</th>
                                    <th>{{ __('equipment.name') }}</th>
                                    <th>{{ __('equipment.steps') }}</th>
                                    <th>{{ __('equipment.status') }}</th>
                                    <th>{{ __('equipment.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workflows as $workflow)
                                    <tr>
                                        <td class="equipment-actions-cell">
                                            <div class="d-flex gap-1">
                                                <button wire:click="toggleExpand('{{ $workflow->id }}')" class="rm-act-btn rm-act-btn--expand" title="{{ !empty($expandedWorkflows[$workflow->id]) ? 'Collapse' : 'Expand' }}">
                                                    <i class="mdi mdi-chevron-{{ !empty($expandedWorkflows[$workflow->id]) ? 'up' : 'down' }}"></i>
                                                </button>
                                                <a href="{{ route('equipment.disposal.workflow.edit', $workflow->id) }}" class="rm-act-btn rm-act-btn--edit" title="{{ __('equipment.edit') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button wire:click="deleteWorkflow({{ $workflow->id }})"
                                                        class="rm-act-btn rm-act-btn--delete"
                                                        onclick="return confirm('{{ __('equipment.confirm_delete_workflow') }}');"
                                                        title="{{ __('equipment.delete') }}">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $workflow->workflow_name }}</div>
                                            <small class="text-muted">{{ Str::limit($workflow->description, 50) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">
                                                {{ $workflow->steps->count() }} {{ __('equipment.steps') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       wire:click="toggleStatus({{ $workflow->id }})"
                                                       {{ $workflow->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $workflow->is_active ? __('equipment.active') : __('equipment.inactive') }}</label>
                                            </div>
                                        </td>
                                        <td>{{ $workflow->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                    @if(!empty($expandedWorkflows[$workflow->id]))
                                    <tr class="bg-light">
                                        <td colspan="5">
                                            <div class="p-4">
                                                <h6 class="fw-bold mb-4"><i class="mdi mdi-timeline-outline"></i> Workflow Timeline</h6>
                                                @if($workflow->steps->count())
                                                    <div class="workflow-timeline">
                                                    @foreach($workflow->steps as $index => $step)
                                                        <div class="timeline-item">
                                                            <div class="timeline-marker">
                                                                <div class="timeline-point"></div>
                                                                @if($index < $workflow->steps->count() - 1)
                                                                    <div class="timeline-line"></div>
                                                                @endif
                                                            </div>
                                                            <div class="timeline-content">
                                                                <div class="step-card">
                                                                    <div class="step-header">
                                                                        <h6 class="step-title">{{ $step->step_name }}</h6>
                                                                        <span class="status-badge {{ $step->is_required ? 'status-required' : 'status-optional' }}">
                                                                            {{ $step->is_required ? 'Required' : 'Optional' }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="step-footer">
                                                                        <div class="assignee-section">
                                                                            <span class="detail-badge {{ str_contains($step->assignee_type, 'User') ? 'user-badge' : 'role-badge' }}">
                                                                                {{ $step->assignee_name }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    </div>
                                                @else
                                                    <div class="alert alert-light border" style="border-left: 4px solid #0d6efd !important;"><i class="mdi mdi-information-outline"></i> No steps configured for this workflow yet.</div>
                                                @endif
                                            </div>
                                            <style>
                                                .workflow-timeline {
                                                    position: relative;
                                                    padding: 12px 0;
                                                }
                                                
                                                .timeline-item {
                                                    display: flex;
                                                    margin-bottom: 20px;
                                                }
                                                
                                                .timeline-marker {
                                                    position: relative;
                                                    flex-shrink: 0;
                                                    width: 40px;
                                                    display: flex;
                                                    flex-direction: column;
                                                    align-items: center;
                                                }
                                                
                                                .timeline-point {
                                                    width: 16px;
                                                    height: 16px;
                                                    border-radius: 50%;
                                                    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                                                    border: 2px solid #fff;
                                                    box-shadow: 0 0 0 2px #e9ecef;
                                                    z-index: 2;
                                                }
                                                
                                                .timeline-line {
                                                    flex: 1;
                                                    width: 2px;
                                                    background: linear-gradient(to bottom, #dee2e6, #e9ecef);
                                                    margin-top: 6px;
                                                }
                                                
                                                .timeline-content {
                                                    flex: 1;
                                                    margin-left: 20px;
                                                    margin-top: -6px;
                                                }
                                                
                                                .step-card {
                                                    background: #fff;
                                                    border: 1px solid #e5e7eb;
                                                    border-radius: 6px;
                                                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
                                                    transition: all 0.2s ease;
                                                }
                                                
                                                .step-card:hover {
                                                    box-shadow: 0 3px 10px rgba(13, 110, 253, 0.12);
                                                    border-color: #0d6efd;
                                                }
                                                
                                                .step-header {
                                                    display: flex;
                                                    justify-content: space-between;
                                                    align-items: center;
                                                    gap: 12px;
                                                    padding: 12px;
                                                    border-bottom: 1px solid #f0f0f0;
                                                }
                                                
                                                .step-title {
                                                    font-size: 14px;
                                                    font-weight: 600;
                                                    color: #212529;
                                                    margin: 0;
                                                    flex: 1;
                                                }
                                                
                                                .step-order {
                                                    font-size: 11px;
                                                    color: #6c757d;
                                                    font-weight: 500;
                                                }
                                                
                                                .status-badge {
                                                    font-size: 10px;
                                                    font-weight: 700;
                                                    padding: 4px 8px;
                                                    border-radius: 10px;
                                                    white-space: nowrap;
                                                    letter-spacing: 0.4px;
                                                    text-transform: uppercase;
                                                    flex-shrink: 0;
                                                    border: 1px solid transparent;
                                                }
                                                
                                                .status-required {
                                                    background: #fee;
                                                    color: #c33;
                                                    border: 1px solid #fcc;
                                                }
                                                
                                                .status-optional {
                                                    background: #f5f5f5;
                                                    color: #666;
                                                    border: 1px solid #e0e0e0;
                                                }
                                                
                                                .step-footer {
                                                    padding: 10px 12px;
                                                    display: flex;
                                                    align-items: center;
                                                    gap: 10px;
                                                    flex-wrap: wrap;
                                                }
                                                
                                                .assignee-section {
                                                    display: flex;
                                                    align-items: center;
                                                    gap: 6px;
                                                }
                                                
                                                .detail-badge {
                                                    font-size: 11px;
                                                    font-weight: 700;
                                                    padding: 4px 10px;
                                                    border-radius: 12px;
                                                    white-space: nowrap;
                                                    letter-spacing: 0.3px;
                                                    border: 1px solid transparent;
                                                }
                                                
                                                .user-badge {
                                                    background: #e7f1ff;
                                                    color: #004085;
                                                    border: 1px solid #b8daff;
                                                }
                                                
                                                .role-badge {
                                                    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                                                    color: #fff;
                                                    border: 1px solid #0a58ca;
                                                    box-shadow: 0 1px 3px rgba(13, 110, 253, 0.2);
                                                }
                                            </style>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-sitemap text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">{{ __('equipment.no_workflows_found') }}</h5>
                                            <p class="text-muted">{{ __('equipment.create_new_workflow_hint') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $workflows->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
