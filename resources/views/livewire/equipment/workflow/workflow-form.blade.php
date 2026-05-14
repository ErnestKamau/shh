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
                                {{ $workflowId ? __('equipment.edit_workflow') : __('equipment.create_workflow') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('equipment.create_new_workflow_hint') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('equipment.disposal.workflow.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                                <i class="mdi mdi-arrow-left"></i> {{ __('equipment.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <form wire:submit.prevent="save">
                        
                        <!-- Basic Info -->
                        <h5 class="text-primary mb-3">{{ __('equipment.basic_information') }}</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold required">{{ __('equipment.workflow_name') }}</label>
                                    <input type="text" wire:model="workflow_name" class="form-control @error('workflow_name') is-invalid @enderror" placeholder="{{ __('equipment.workflow_name_example') }}">
                                    @error('workflow_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">{{ __('equipment.active_status') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="isActiveSwitch">
                                        <label class="form-check-label" for="isActiveSwitch">{{ __('equipment.enable_workflow') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">{{ __('equipment.description') }}</label>
                                    <textarea wire:model="description" class="form-control" rows="2" placeholder="{{ __('equipment.workflow_purpose_placeholder') }}"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Criteria -->
                        <h5 class="text-primary mb-3">{{ __('equipment.application_criteria') }}</h5>
                        <p class="text-muted small">{{ __('equipment.create_new_workflow_hint') }}</p>
                        <div class="row mb-4 bg-light p-3 rounded mx-0">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">{{ __('equipment.equipment_type') }}</label>
                                    <div x-data="{ open: false }" @click.outside="open = false" class="tag-select-container position-relative">
                                        <div class="tag-select-input form-control d-flex align-items-center flex-wrap" @click="open = !open" style="gap:6px; min-height: 38px; height: auto; cursor: pointer;">
                                            @if($selectedAssetTypeName)
                                                <span class="tag-badge badge bg-primary text-white d-flex align-items-center rounded-pill py-1 px-2" style="font-weight: 500;">
                                                    {{ $selectedAssetTypeName }}
                                                    <i class="mdi mdi-close-circle ms-1" wire:click.stop="clearAssetType" style="cursor:pointer; font-size:14px;"></i>
                                                </span>
                                            @endif
                                            <input type="text"
                                                   wire:model.live="assetTypeSearch"
                                                   @click.stop="open = true"
                                                   class="tag-input border-0 flex-grow-1"
                                                   placeholder="{{ $selectedAssetTypeName ? '' : __('equipment.all_types') }}"
                                                   autocomplete="off" style="outline: none; background: transparent; min-width: 60px;">
                                        </div>
                                        <div x-show="open" class="tag-dropdown position-absolute w-100 bg-white border border-top-0 shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; border-radius: 0 0 6px 6px;">
                                            @forelse($filteredAssetTypes as $type)
                                                <div class="tag-dropdown-item px-3 py-2" @click.stop="open = false; $wire.selectAssetType({{ json_encode($type->id) }}, {{ json_encode($type->asset_code . ' - ' . $type->descripton) }})" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='transparent';">
                                                    {{ $type->asset_code }} - {{ $type->descripton }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted px-3 py-2">No types found</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">{{ __('equipment.location') }}</label>
                                    <div x-data="{ open: false }" @click.outside="open = false" class="tag-select-container position-relative">
                                        <div class="tag-select-input form-control d-flex align-items-center flex-wrap" @click="open = !open" style="gap:6px; min-height: 38px; height: auto; cursor: pointer;">
                                            @if($selectedLocationName)
                                                <span class="tag-badge badge bg-primary text-white d-flex align-items-center rounded-pill py-1 px-2" style="font-weight: 500;">
                                                    {{ $selectedLocationName }}
                                                    <i class="mdi mdi-close-circle ms-1" wire:click.stop="clearLocation" style="cursor:pointer; font-size:14px;"></i>
                                                </span>
                                            @endif
                                            <input type="text"
                                                   wire:model.live="locationSearch"
                                                   @click.stop="open = true"
                                                   class="tag-input border-0 flex-grow-1"
                                                   placeholder="{{ $selectedLocationName ? '' : __('equipment.all_locations') }}"
                                                   autocomplete="off" style="outline: none; background: transparent; min-width: 60px;">
                                        </div>
                                        <div x-show="open" class="tag-dropdown position-absolute w-100 bg-white border border-top-0 shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; border-radius: 0 0 6px 6px;">
                                            @forelse($filteredLocations as $location)
                                                <div class="tag-dropdown-item px-3 py-2" @click.stop="open = false; $wire.selectLocation({{ json_encode($location->id) }}, {{ json_encode($location->name) }})" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='transparent';">
                                                    {{ $location->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted px-3 py-2">No locations found</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Steps -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-primary mb-0">{{ __('equipment.steps') }}</h5>
                            <button type="button" wire:click="addStep" class="btn btn-sm btn-success">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_step') }}
                            </button>
                        </div>

                        <div class="workflow-steps">
                            @foreach($steps as $index => $step)
                                <div wire:key="step-{{ $index }}" class="card mb-3 border border-secondary" style="border-left: 5px solid #0d6efd !important; overflow: visible;">
                                    <div class="card-body" style="overflow: visible;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-title fw-bold mb-0">
                                                <span class="badge bg-primary rounded-circle me-2">{{ $index + 1 }}</span>
                                                {{ __('equipment.step') }} {{ $index + 1 }}
                                            </h6>
                                            <div class="btn-group">
                                                <button type="button" wire:click="moveStepUp({{ $index }})" class="btn btn-sm btn-outline-secondary" @if($index === 0) disabled @endif>
                                                    <i class="mdi mdi-arrow-up"></i>
                                                </button>
                                                <button type="button" wire:click="moveStepDown({{ $index }})" class="btn btn-sm btn-outline-secondary" @if($index === count($steps) - 1) disabled @endif>
                                                    <i class="mdi mdi-arrow-down"></i>
                                                </button>
                                                <button type="button" wire:click="removeStep({{ $index }})" class="btn btn-sm btn-outline-danger">
                                                    <i class="mdi mdi-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row" style="overflow: visible;">
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="form-label small fw-bold required">{{ __('equipment.step_name') }}</label>
                                                    <input type="text" wire:model="steps.{{ $index }}.step_name" class="form-control form-control-sm" placeholder="{{ __('equipment.step_name_example') }}">
                                                    @error('steps.'.$index.'.step_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3" style="overflow: visible; position: relative;">
                                                <div class="form-group mb-2">
                                                    <label class="form-label small fw-bold required">{{ __('equipment.assignee_type') }}</label>
                                                    <div x-data="{ open: false }" @click.outside="open = false" class="tag-select-container position-relative">
                                                        <div class="tag-select-input form-control form-control-sm d-flex align-items-center flex-wrap" @click="open = !open" style="gap:4px; min-height: 31px; height: auto; cursor: pointer;">
                                                            @forelse($step['role_names'] ?? [] as $roleName)
                                                                <span class="badge bg-primary text-white d-flex align-items-center rounded-pill py-1 px-2" style="font-weight: 500; font-size: 0.75rem;">
                                                                    {{ $roleName }}
                                                                    <i class="mdi mdi-close-circle ms-1" wire:click.stop="removeStepRole({{ $index }}, {{ json_encode($step['role_ids'][$loop->index] ?? '') }})" style="cursor:pointer; font-size:12px;"></i>
                                                                </span>
                                                            @empty
                                                                <span class="text-muted small">Select Groups...</span>
                                                            @endforelse
                                                        </div>
                                                        <div x-show="open" class="tag-dropdown position-absolute w-100 bg-white border border-top-0 shadow" style="z-index: 9999; max-height: 200px; overflow-y: auto; border-radius: 0 0 6px 6px; top: 100%; left: 0;">
                                                            @forelse($roles as $role)
                                                                <div class="tag-dropdown-item px-3 py-2 small" @click.stop="open = false; $wire.selectStepRole({{ json_encode($index) }}, {{ json_encode($role->id) }}, {{ json_encode($role->name) }})" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='transparent';">
                                                                    {{ $role->name }}
                                                                </div>
                                                            @empty
                                                                <div class="tag-dropdown-item text-muted px-3 py-2 small">No groups found</div>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3" style="overflow: visible; position: relative;">
                                                <div class="form-group mb-2">
                                                    <label class="form-label small fw-bold required">{{ __('equipment.assignee') }}</label>
                                                    <div x-data="{ open: false }" @click.outside="open = false" class="tag-select-container position-relative">
                                                        <div class="tag-select-input form-control form-control-sm d-flex align-items-center flex-wrap" @click="open = !open" style="gap:4px; min-height: 31px; height: auto; cursor: pointer;">
                                                            @forelse($step['assignee_names'] ?? [] as $assigneeName)
                                                                <span class="badge bg-success text-white d-flex align-items-center rounded-pill py-1 px-2" style="font-weight: 500; font-size: 0.75rem;">
                                                                    {{ $assigneeName }}
                                                                    <i class="mdi mdi-close-circle ms-1" wire:click.stop="removeStepAssignee({{ $index }}, {{ json_encode($step['assignee_ids'][$loop->index] ?? '') }})" style="cursor:pointer; font-size:12px;"></i>
                                                                </span>
                                                            @empty
                                                                <span class="text-muted small">{{ count($step['role_ids'] ?? []) > 0 ? 'Select Assignees...' : 'Select Groups First...' }}</span>
                                                            @endforelse
                                                        </div>
                                                        @if(count($step['role_ids'] ?? []) > 0)
                                                            <div x-show="open" class="tag-dropdown position-absolute w-100 bg-white border border-top-0 shadow" style="z-index: 9999; max-height: 200px; overflow-y: auto; border-radius: 0 0 6px 6px; top: 100%; left: 0;">
                                                                <div class="tag-dropdown-item px-3 py-2 small fw-bold text-success" @click.stop="open = false; $wire.selectStepAssignee({{ json_encode($index) }}, {{ json_encode('all') }}, {{ json_encode('All Users') }})" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f0f0f0';" onmouseout="this.style.backgroundColor='transparent';">
                                                                    ☑ All Users in Groups
                                                                </div>
                                                                @foreach($users->filter(function($u) use ($step) { $roleIds = $step['role_ids'] ?? []; return !empty($roleIds) && $u->roles->whereIn('id', $roleIds)->count() > 0; }) as $user)
                                                                    <div class="tag-dropdown-item px-3 py-2 small" @click.stop="open = false; $wire.selectStepAssignee({{ json_encode($index) }}, {{ json_encode($user->id) }}, {{ json_encode($user->name) }})" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='transparent';">
                                                                        {{ $user->name }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @error('steps.'.$index.'.assignee_ids') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group mb-2">
                                                    <label class="form-label small fw-bold">{{ __('equipment.required') }}?</label>
                                                    <div class="form-check mt-1">
                                                        <input type="checkbox" wire:model="steps.{{ $index }}.is_required" class="form-check-input" checked>
                                                        <label class="form-check-label small">{{ __('equipment.yes') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if(count($steps) === 0)
                                <div class="alert alert-warning text-center">
                                    {{ __('equipment.create_new_workflow_hint') }}
                                </div>
                            @endif
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('equipment.disposal.workflow.index') }}" class="btn btn-secondary me-2">{{ __('equipment.cancel') }}</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> {{ __('equipment.save_workflow') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
