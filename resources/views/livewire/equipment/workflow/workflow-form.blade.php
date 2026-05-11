<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="mdi mdi-sitemap text-primary"></i>
                            {{ $workflowId ? __('equipment.edit_workflow') : __('equipment.create_workflow') }}
                        </h2>
                        <a href="{{ route('equipment.disposal.workflow.index') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left"></i> {{ __('equipment.back_to_list') }}
                        </a>
                    </div>
                </div>
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
                                    <select wire:model="equipment_type_id" class="form-select">
                                        <option value="">{{ __('equipment.all_types') }}</option>
                                        @foreach($assetTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">{{ __('equipment.location') }}</label>
                                    <select wire:model="location_id" class="form-select">
                                        <option value="">{{ __('equipment.all_locations') }}</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
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
                                <div class="card mb-3 border border-secondary" style="border-left: 5px solid #0d6efd !important;">
                                    <div class="card-body">
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

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="form-label small fw-bold required">{{ __('equipment.step_name') }}</label>
                                                    <input type="text" wire:model="steps.{{ $index }}.step_name" class="form-control form-control-sm" placeholder="{{ __('equipment.step_name_example') }}">
                                                    @error('steps.'.$index.'.step_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <label class="form-label small fw-bold required">{{ __('equipment.assignee_type') }}</label>
                                                    <select wire:model.live="steps.{{ $index }}.assignee_type" class="form-select form-select-sm">
                                                        <option value="App\User">{{ __('equipment.specific_user') }}</option>
                                                        <option value="Spatie\Permission\Models\Role">{{ __('equipment.role') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <label class="form-label small fw-bold required">{{ __('equipment.assignee') }}</label>
                                                    <select wire:model="steps.{{ $index }}.assignee_id" class="form-select form-select-sm">
                                                        <option value="">{{ __('equipment.select_option') }}</option>
                                                        @if($step['assignee_type'] === 'App\User')
                                                            @foreach($users as $user)
                                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                            @endforeach
                                                        @else
                                                            @foreach($roles as $role)
                                                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @error('steps.'.$index.'.assignee_id') <span class="text-danger small">{{ $message }}</span> @enderror
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
