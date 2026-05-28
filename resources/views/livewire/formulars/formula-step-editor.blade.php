<div>
    <div class="container-fluid">
    <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0">
                                    <i class="mdi mdi-cogs text-primary"></i>
                                    Formula Steps Editor
                                </h2>
                                <p class="text-muted mb-0">{{ $formulaVersion->formula->name }} - Version {{ $formulaVersion->version_number }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('formulars.manage') }}" class="btn mr-2 btn-outline-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back to Formulas
                                </a>
                                <button wire:click="showTestFormulaModalInit" class="btn mr-2 btn-info">
                                    <i class="mdi mdi-play"></i> Test Formula
                                </button>
                                <button wire:click="showCreateStepModalInit" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add Step
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Alert (page only — modals show their own copy) -->
        @if($message && !$this->isAnyModalOpen())
            <div class="alert alert-{{ $this->messageAlertClass() }} alert-dismissible fade show" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" wire:click="dismissMessage"></button>
            </div>
        @endif

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="mb-0 text-muted">
                            <i class="mdi mdi-filter-variant"></i> Filter Options
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Search</label>
                                    <input type="text" wire:model.live="search" class="form-control modern-input" placeholder="Search by variable name, label, or description...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Type</label>
                                    <select wire:model.live="typeFilter" class="form-select modern-select no-select2">
                                        <option value="">All Types</option>
                                        <option value="input">Input</option>
                                        <option value="derived">Derived</option>
                                        <option value="lookup">Lookup</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Show Entries</label>
                                    <select wire:model.live="perPage" class="form-select modern-select no-select2">
                                        @foreach($perPageOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">&nbsp;</label>
                                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                        <i class="mdi mdi-refresh"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Steps List -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-body">
                        @if(count($steps) > 0)
                            <!-- Show Entries Info -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ count($steps) }} step(s)
                                    </span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="steps-table">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr>
                                            <th style="width: 40px;">
                                                <i class="mdi mdi-drag text-muted"></i>
                                            </th>
                                            <th style="width: 60px;">Step</th>
                                            <th>Variable Name</th>
                                            <th>Type</th>
                                            <th>Label</th>
                                            <th>Expression/Config</th>
                                            <th>Description</th>
                                            <th style="width: 200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-steps">
                                        @foreach($steps as $step)
                                            <tr class="sortable-row" data-step-id="{{ $step['id'] }}">
                                                <td class="drag-handle text-center">
                                                    <i class="mdi mdi-drag-vertical text-muted" style="cursor: move; font-size: 18px;"></i>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $step['step_number'] }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $step['variable_name'] }}</strong>
                                                </td>
                                                <td>
                                                    @php
                                                        $typeBadge = match($step['step_type']) {
                                                            'input' => 'success',
                                                            'derived' => 'info',
                                                            'parameter_result' => 'dark',
                                                            'static_text' => 'secondary',
                                                            'checkbox' => 'primary',
                                                            'custom_table' => 'warning',
                                                            default => 'warning',
                                                        };
                                                    @endphp
                                                    <span class="badge badge-{{ $typeBadge }}">
                                                        {{ ucfirst(str_replace('_', ' ', $step['step_type'])) }}
                                                    </span>
                                                </td>
                                                <td>{{ $step['label'] }}</td>
                                                <td>
                                                    @if($step['step_type'] === 'derived')
                                                        <code class="text-primary">{{ Str::limit($step['expression'], 50) }}</code>
                                                    @elseif($step['step_type'] === 'lookup')
                                                        <small class="text-muted">
                                                            Table: {{ $step['lookup_config']['lookup_table_id'] ?? 'N/A' }}
                                                        </small>
                                                    @elseif($step['step_type'] === 'parameter_result')
                                                        <small class="text-muted">
                                                            Analyte ID: {{ $step['analyte_id'] ?? 'N/A' }}
                                                        </small>
                                                    @elseif($step['step_type'] === 'static_text')
                                                        <small class="text-muted">{{ Str::limit($step['step_config']['content'] ?? '-', 50) }}</small>
                                                    @elseif($step['step_type'] === 'checkbox')
                                                        <small class="text-muted">{{ ucfirst($step['step_config']['options_mode'] ?? 'static') }} options</small>
                                                    @elseif($step['step_type'] === 'custom_table')
                                                        <small class="text-muted">{{ ucfirst($step['table_mode'] ?? 'dynamic') }} table</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ Str::limit($step['description'], 50) }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap">
                                                        @if(($step['step_type'] ?? '') === 'custom_table')
                                                        <button type="button"
                                                                wire:click="openConfigureTableModalForStep(@js($step['id']))"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                                title="Configure table">
                                                            <i class="mdi mdi-table-cog"></i>
                                                        </button>
                                                        @endif
                                                        <button type="button"
                                                                wire:click="showEditStepModalInit(@js($step['id']))"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                                title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button type="button"
                                                                wire:click="showDeleteStepModal(@js($step['id']))"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                                title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-cogs fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No steps defined</h5>
                                <p class="text-muted">Add steps to define your formula workflow.</p>
                                <button wire:click="showCreateStepModalInit" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add First Step
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Mandatory Fields Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    <i class="mdi mdi-text-box-check text-primary"></i>
                                    Mandatory Worksheet Fields
                                </h5>
                                <p class="text-muted small mb-0">Define required fields for worksheet execution</p>
                            </div>
                            <button wire:click="showCreateFieldModalInit" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Field
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="fm-placement-bar fm-placement-bar--inline mb-4" role="radiogroup" aria-label="Default placement for new mandatory fields">
                            <div class="fm-placement-bar__text">
                                <i class="mdi mdi-arrow-collapse-vertical text-primary" aria-hidden="true"></i>
                                <span class="fm-placement-bar__title">Form placement</span>
                                <span class="fm-placement-bar__desc text-muted">
                                    Default for new fields — appear
                                    <strong>{{ $mandatoryFieldsPlacement === 'top' ? 'above' : 'below' }}</strong>
                                    the sample table during batch capture.
                                </span>
                            </div>
                            <div class="fm-placement-options fm-placement-options--compact">
                                <label class="fm-placement-chip {{ $mandatoryFieldsPlacement === 'top' ? 'is-active' : '' }}" title="Above the sample table">
                                    <input type="radio" wire:model.live="mandatoryFieldsPlacement" value="top" class="fm-placement-input">
                                    <i class="mdi mdi-arrow-up-bold"></i>
                                    <span>Top</span>
                                </label>
                                <label class="fm-placement-chip {{ $mandatoryFieldsPlacement === 'bottom' ? 'is-active' : '' }}" title="Below the sample table">
                                    <input type="radio" wire:model.live="mandatoryFieldsPlacement" value="bottom" class="fm-placement-input">
                                    <i class="mdi mdi-arrow-down-bold"></i>
                                    <span>Bottom</span>
                                </label>
                            </div>
                        </div>

                        <!-- Search Bar -->
                        <div class="row mb-3">
                            <div class="col-md-10">
                                <input type="text" wire:model.live="fieldSearch" class="form-control modern-input" placeholder="Search by label, value name, or help text...">
                            </div>
                            <div class="col-md-2">
                                <button wire:click="clearFieldSearch" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>

                        @if(count($mandatoryFields) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr>
                                            <th style="width: 40px;">
                                                <i class="mdi mdi-drag text-muted"></i>
                                            </th>
                                            <th style="width: 60px;">Order</th>
                                            <th>Label</th>
                                            <th>Field Type</th>
                                            <th>Placement</th>
                                            <th>Value Name</th>
                                            <th>Required</th>
                                            <th>Configuration</th>
                                            <th style="width: 200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-fields">
                                        @foreach($mandatoryFields as $field)
                                            <tr class="sortable-row" data-field-id="{{ $field['id'] }}">
                                                <td class="drag-handle text-center">
                                                    <i class="mdi mdi-drag-vertical text-muted" style="cursor: move; font-size: 18px;"></i>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $field['order'] }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $field['label'] }}</strong>
                                                    @if($field['help_text'])
                                                        <br><small class="text-muted">{{ Str::limit($field['help_text'], 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $field['field_type'])) }}</span>
                                                </td>
                                                <td>
                                                    @php $placement = $field['form_placement'] ?? 'bottom'; @endphp
                                                    <span class="badge badge-{{ $placement === 'top' ? 'primary' : 'secondary' }}">
                                                        <i class="mdi mdi-arrow-{{ $placement === 'top' ? 'up' : 'down' }}-bold"></i>
                                                        {{ ucfirst($placement) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <code>{{ $field['field_value_name'] }}</code>
                                                </td>
                                                <td>
                                                    @if($field['is_required'])
                                                        <span class="badge badge-danger">Required</span>
                                                    @else
                                                        <span class="badge badge-secondary">Optional</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($field['field_type'] === 'checkbox')
                                                        @php
                                                            $opts = $field['field_options']['options'] ?? [];
                                                            $optCount = is_array($opts) ? count($opts) : 0;
                                                        @endphp
                                                        <span class="badge badge-primary">{{ $optCount }} {{ $optCount === 1 ? 'option' : 'options' }}</span>
                                                    @elseif($field['model_tied_to'])
                                                        <span class="badge badge-warning">{{ ucfirst($field['model_tied_to']) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="showEditFieldModalInit(@js($field['id']))" 
                                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="showDeleteFieldModal(@js($field['id']))" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-text-box-check fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No mandatory fields defined</h5>
                                <p class="text-muted">Add fields that users must fill when executing this formula worksheet.</p>
                                <button wire:click="showCreateFieldModalInit" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add First Field
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($showCreateStepModal)
            <div class="fs-modal show d-block formula-step-modal" tabindex="-1" wire:click.self="closeCreateStepModal">
                <div class="modal-dialog modal-lg modal-dialog-scrollable fs-modal-dialog" wire:click.stop>
                    <div class="modal-content fs-modal-content">
                        <div class="modal-header fs-modal-header">
                            <div>
                                <h5 class="modal-title mb-1">Create New Step</h5>
                                <p class="fs-modal-subtitle mb-0">Define how this step captures or calculates data in the formula pipeline.</p>
                            </div>
                            <button type="button" class="close fs-modal-close" wire:click="closeCreateStepModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body fs-modal-body">
                            @if($message)
                                <div class="alert alert-{{ $this->messageAlertClass() }} alert-dismissible fade show mb-3" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" wire:click="dismissMessage"></button>
                                </div>
                            @endif

                            <!-- Error Display -->
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <h6 class="alert-heading">
                                        <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                                    </h6>
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form id="create-formula-step-form" wire:submit.prevent="createStep" class="fs-step-form">
                                @include('livewire.formulars.partials.formula-step-form-fields', ['stepFormMode' => 'create'])
                            </form>
                        </div>
                        <div class="modal-footer fs-modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeCreateStepModal">Cancel</button>
                            <button type="submit" form="create-formula-step-form" class="btn btn-primary px-4">
                                <i class="mdi mdi-content-save-outline mr-1"></i> Create Step
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

<!-- Create Step Modal -->

<!-- Edit Step Modal -->
@if($showEditStepModal)
    <div class="fs-modal show d-block formula-step-modal" tabindex="-1" wire:click.self="closeEditStepModal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable fs-modal-dialog" wire:click.stop>
            <div class="modal-content fs-modal-content">
                <div class="modal-header fs-modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Edit Step</h5>
                        <p class="fs-modal-subtitle mb-0">Update how this step captures or calculates data in the formula pipeline.</p>
                    </div>
                    <button type="button" class="close fs-modal-close" wire:click="closeEditStepModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body fs-modal-body">
                    @if($message)
                        <div class="alert alert-{{ $this->messageAlertClass() }} alert-dismissible fade show mb-3" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading">
                                <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                            </h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="edit-formula-step-form" wire:submit.prevent="updateStep" class="fs-step-form">
                        @include('livewire.formulars.partials.formula-step-form-fields', ['stepFormMode' => 'edit'])
                    </form>
                </div>
                <div class="modal-footer fs-modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeEditStepModal">Cancel</button>
                    <button type="submit" form="edit-formula-step-form" class="btn btn-primary px-4">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Update Step
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Delete Step Modal -->
@if($showDeleteStepModal && $deletingStep)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-delete text-danger"></i>
                        Delete Step
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteStepModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-circle"></i>
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    
                    <div class="mb-3">
                        <h6>Step Details:</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Step Number:</strong> {{ $deletingStep->step_number }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Type:</strong> 
                                        <span class="badge badge-{{ $deletingStep->step_type === 'input' ? 'success' : ($deletingStep->step_type === 'derived' ? 'info' : 'warning') }}">
                                            {{ ucfirst($deletingStep->step_type) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Variable Name:</strong> {{ $deletingStep->variable_name }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Label:</strong> {{ $deletingStep->label }}
                                    </div>
                                </div>
                                @if($deletingStep->description)
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <strong>Description:</strong> {{ $deletingStep->description }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-muted">Are you sure you want to delete this step? This will remove it from the formula workflow.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteStepModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteStep">
                        <i class="mdi mdi-delete"></i> Delete Step
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Test Formula Modal -->
@if($showTestFormulaModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title">
                        <i class="mdi mdi-flask-outline"></i>
                        Test Formula Execution
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showTestFormulaModal', false)"></button>
                </div>
                <div class="modal-body">
                    @if($message)
                        <div class="alert alert-{{ $this->messageAlertClass() }} alert-dismissible fade show mb-3" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
                        </div>
                    @endif

                    @if(count($testInputs) > 0)
                        <div class="row">
                            <!-- Left Column - Inputs -->
                            <div class="col-md-5">
                                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                                    <div class="card-header bg-light border-0" style="border-radius: 12px 12px 0 0;">
                                        <h6 class="mb-0 text-primary">
                                            <i class="mdi mdi-calculator-variant"></i> Input Values
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach($testInputs as $variableName => $value)
                                            <div class="mb-3">
                                                <label for="testInput_{{ $variableName }}" class="form-label">
                                                    <strong>{{ $variableName }}</strong>
                                                </label>
                                                <input type="number" 
                                                       wire:model="testInputs.{{ $variableName }}" 
                                                       class="form-control modern-input" 
                                                       id="testInput_{{ $variableName }}"
                                                       step="any"
                                                       placeholder="Enter value...">
                                            </div>
                                        @endforeach
                                        
                                        <button wire:click="testFormula" class="btn btn-primary w-100 btn-modern">
                                            <i class="mdi mdi-play-circle"></i> Execute Formula
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column - Results -->
                            <div class="col-md-7">
                                @if(count($testResults) > 0)
                                    <!-- Warnings Section -->
                                    @if(isset($testExecutionData['warnings']) && count($testExecutionData['warnings']) > 0)
                                        <div class="alert alert-warning alert-modern mb-3" role="alert">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-alert-circle me-2" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <h6 class="alert-heading mb-2">
                                                        <strong>Lookup Warnings</strong>
                                                    </h6>
                                                    <ul class="mb-0">
                                                        @foreach($testExecutionData['warnings'] as $warning)
                                                            <li class="small">{{ $warning }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Execution Timeline -->
                                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                        <div class="card-header bg-success text-white border-0" style="border-radius: 12px 12px 0 0;">
                                            <h6 class="mb-0">
                                                <i class="mdi mdi-timeline-clock"></i> Execution Timeline
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Timeline Container -->
                                            <div class="timeline-container">
                                                @foreach($formulaVersion->formulaSteps as $index => $step)
                                                    @php
                                                        $stepResult = $testResults[$step->variable_name] ?? null;
                                                        $isLast = $index === count($formulaVersion->formulaSteps) - 1;
                                                    @endphp
                                                    
                                                    <div class="timeline-item mb-4">
                                                        <!-- Timeline Line -->
                                                        @if(!$isLast)
                                                            <div class="timeline-line"></div>
                                                        @endif
                                                        
                                                        <!-- Timeline Node -->
                                                        <div class="timeline-node timeline-node-{{ $step->step_type }}">
                                                            @if($step->step_type === 'input')
                                                                <i class="mdi mdi-keyboard"></i>
                                                            @elseif($step->step_type === 'derived')
                                                                <i class="mdi mdi-function"></i>
                                                            @elseif($step->step_type === 'lookup')
                                                                <i class="mdi mdi-table-search"></i>
                                                            @else
                                                                <i class="mdi mdi-cog"></i>
                                                            @endif
                                                        </div>
                                                        
                                                        <!-- Timeline Content -->
                                                        <div class="timeline-content">
                                                            <div class="timeline-header">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div>
                                                                        <span class="timeline-step-number">Step {{ $step->step_number }}</span>
                                                                        <h6 class="timeline-title mb-1">{{ $step->label }}</h6>
                                                                        <small class="timeline-variable">
                                                                            <code>{{ $step->variable_name }}</code>
                                                                        </small>
                                                                    </div>
                                                                    <div class="timeline-badge">
                                                                        <span class="badge badge-{{ $step->step_type === 'input' ? 'success' : ($step->step_type === 'derived' ? 'info' : ($step->step_type === 'lookup' ? 'warning' : 'dark')) }}">
                                                                            {{ ucfirst(str_replace('_', ' ', $step->step_type)) }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="timeline-result">
                                                                @if($stepResult === null)
                                                                    <div class="result-null">
                                                                        <i class="mdi mdi-alert-circle text-danger"></i>
                                                                        <span class="text-danger fw-bold">NULL (No Match)</span>
                                                                    </div>
                                                                @else
                                                                    <div class="result-success">
                                                                        <i class="mdi mdi-check-circle text-success"></i>
                                                                        <span class="result-value text-primary fw-bold">
                                                                            {{ is_numeric($stepResult) ? number_format($stepResult, 4) : $stepResult }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            
                                                            @if($step->step_type === 'lookup' && isset($step->lookup_config))
                                                                <div class="timeline-details">
                                                                    <small class="text-muted">
                                                                        <i class="mdi mdi-information"></i>
                                                                        Lookup Table: {{ $step->lookup_config['lookup_table_id'] ?? 'N/A' }}
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <!-- Final Result -->
                                            @if(isset($testExecutionData['final_result']))
                                                <div class="final-result p-4 mt-3 text-center" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px;">
                                                    <div class="text-white">
                                                        <small class="d-block mb-1" style="opacity: 0.9;">Final Result</small>
                                                        <h3 class="mb-0 fw-bold">
                                                            @if($testExecutionData['final_result'] === null)
                                                                <span class="badge badge-danger" style="font-size: 1.2rem;">NULL</span>
                                                            @else
                                                                {{ is_numeric($testExecutionData['final_result']) ? number_format($testExecutionData['final_result'], 4) : $testExecutionData['final_result'] }}
                                                            @endif
                                                        </h3>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="card border-0 shadow-sm text-center p-5" style="border-radius: 12px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                                        <i class="mdi mdi-flask-empty-outline text-muted mb-3" style="font-size: 4rem; opacity: 0.5;"></i>
                                        <h6 class="text-muted mb-2">Ready to Test</h6>
                                        <p class="text-muted small mb-0">Enter input values and click "Execute Formula" to see results</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="card border-0 shadow-sm mx-auto" style="max-width: 500px; border-radius: 12px;">
                                <div class="card-body p-5">
                                    <i class="mdi mdi-information-outline text-info mb-3" style="font-size: 4rem;"></i>
                                    <h5 class="mb-2">No Input Steps Found</h5>
                                    <p class="text-muted mb-0">This formula doesn't have any input steps to test with.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showTestFormulaModal', false)">
                        <i class="mdi mdi-close"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Deletion Blocked Modal -->
@if($showDeletionBlockedModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-alert-circle text-warning"></i>
                        Cannot Delete Variable
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeDeletionBlockedModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-circle"></i>
                        <strong>Deletion Blocked:</strong> {{ $deletionBlockedReason }}
                    </div>
                    
                    <p class="text-muted">To delete this variable, you must first remove its usage from the dependent steps.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDeletionBlockedModal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif

@include('livewire.formulars.partials.formula-mandatory-field-modal')

<!-- Delete Mandatory Field Modal -->
@if($showDeleteFieldModal && $deletingField)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-delete text-danger"></i>
                        Delete Mandatory Field
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteFieldModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-circle"></i>
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    
                    <div class="mb-3">
                        <h6>Field Details:</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Label:</strong> {{ $deletingField->label }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $deletingField->field_type)) }}
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Value Name:</strong> {{ $deletingField->field_value_name }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Required:</strong> {{ $deletingField->is_required ? 'Yes' : 'No' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-muted">Are you sure you want to delete this mandatory field?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteFieldModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteMandatoryField">
                        <i class="mdi mdi-delete"></i> Delete Field
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

    <style>
    #steps-table .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 3px;
        font-size: 12px;
    }

    #steps-table .rm-act-btn:last-child {
        margin-right: 0;
    }

    #steps-table .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    #steps-table .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    #steps-table .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    #steps-table .rm-act-btn--delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }

    .modal.show {
        display: block !important;
    }
    
    .modern-select {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%234b5563' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        padding: 0.75rem 2.5rem 0.75rem 1rem;
        appearance: none;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        position: relative;
    }
    
    .modern-select:hover {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: translateY(-1px);
    }
    
    .modern-select:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    
    .modern-select:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .modern-select option {
        padding: 0.5rem;
        background-color: #ffffff;
        color: #374151;
        font-weight: 500;
    }
    
    .modern-select option:hover {
        background-color: #f3f4f6;
    }
    
    .modern-select option:checked {
        background-color: #3b82f6;
        color: #ffffff;
    }
    
    .form-select-sm.modern-select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        font-size: 0.8125rem;
        background-size: 1em 1em;
        background-position: right 0.5rem center;
    }
    
    /* Modern styling for quick select (small) dropdowns */
    .modern-select-sm {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%234b5563' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1em 1em;
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        appearance: none;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #374151;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .modern-select-sm:hover {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: translateY(-1px);
    }
    
    .modern-select-sm:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    
    .modern-select-sm:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .modern-select-sm option {
        padding: 0.5rem;
        background-color: #ffffff;
        color: #374151;
        font-weight: 500;
    }
    
    .modern-select-sm option:hover {
        background-color: #f3f4f6;
    }
    
    .modern-select-sm option:checked {
        background-color: #3b82f6;
        color: #ffffff;
    }
    
    /* Quick select variable specific styling */
    .quick-select-variable {
        cursor: pointer;
    }
    
    .quick-select-variable:hover {
        cursor: pointer;
    }
    
    /* Modern input field styling */
    .modern-input {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .modern-input:hover {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .modern-input:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    
    .modern-input::placeholder {
        color: #9ca3af;
        font-weight: 400;
        font-style: italic;
    }
    
    /* Test Formula Modal Styling */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .btn-modern {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
    }
    
    .btn-modern:active {
        transform: translateY(0);
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }
    
    .alert-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-left: 4px solid #f59e0b;
    }
    
    .result-item {
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb !important;
    }
    
    .result-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6 !important;
    }
    
    .result-value {
        font-family: 'Courier New', monospace;
        letter-spacing: 0.5px;
    }
    
    .final-result {
        animation: pulse-success 2s ease-in-out;
    }
    
    @keyframes pulse-success {
        0%, 100% {
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
        }
        50% {
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.5);
        }
    }
    
    .badge-danger {
        background-color: #ef4444;
        color: white;
        animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    /* Timeline Styling */
    .timeline-container {
        position: relative;
        padding-left: 40px;
    }
    
    .timeline-item {
        position: relative;
        display: flex;
        align-items: flex-start;
    }
    
    .timeline-line {
        position: absolute;
        left: -35px;
        top: 40px;
        width: 2px;
        height: calc(100% + 20px);
        background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
        border-radius: 1px;
    }
    
    .timeline-node {
        position: absolute;
        left: -47px;
        top: 8px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        z-index: 2;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-node-input {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .timeline-node-derived {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .timeline-node-lookup {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    .timeline-node-parameter_result {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }
    
    .timeline-content {
        flex: 1;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .timeline-content:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }
    
    .timeline-step-number {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .timeline-title {
        color: #1f2937;
        font-weight: 600;
        margin: 4px 0;
    }
    
    .timeline-variable {
        color: #6b7280;
        font-family: 'Courier New', monospace;
    }
    
    .timeline-result {
        margin-top: 12px;
        padding: 8px 12px;
        border-radius: 8px;
        background: rgba(59, 130, 246, 0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
    }
    
    .result-success {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .result-null {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 8px;
    }
    
    .result-value {
        font-family: 'Courier New', monospace;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }
    
    .timeline-details {
        margin-top: 8px;
        padding: 4px 8px;
        background: rgba(107, 114, 128, 0.1);
        border-radius: 6px;
    }
    
    .timeline-badge {
        margin-left: auto;
    }
    
    /* Enhanced styling for form labels */
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-label.small {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #6b7280;
    }
    
    /* Input group styling */
    .input-group .modern-select {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: 0;
    }
    
    .input-group-text {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 2px solid #e5e7eb;
        border-right: 0;
        color: #6b7280;
        font-weight: 500;
        border-radius: 0.5rem 0 0 0.5rem;
    }
    
    .input-group .modern-select:focus + .input-group-text,
    .input-group .modern-select:focus ~ .input-group-text {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #3b82f6;
    }
    
    /* Card styling enhancements */
    .card.bg-light {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    
    .card.bg-white {
        background: #ffffff !important;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    
    /* Enhanced spacing and layout */
    .mb-3 {
        margin-bottom: 1.25rem !important;
    }
    
    .form-text {
        font-size: 0.8125rem;
        color: #6b7280;
        margin-top: 0.375rem;
        font-style: italic;
    }
    
    /* Badge styling enhancements */
    .badge {
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
    }
    
    .badge-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.6875rem;
    }
    
    /* List group styling */
    .list-group-item {
        border: none;
        padding: 0.75rem 0;
        background: transparent;
    }
    
    .list-group-item:not(:last-child) {
        border-bottom: 1px solid #f3f4f6;
    }
    
    /* Alert styling enhancements */
    .alert-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 1px solid #93c5fd;
        color: #1e40af;
    }
    
    .alert-info .alert-heading {
        color: #1e40af;
        font-weight: 600;
    }
    
    /* Button styling enhancements */
    .btn-outline-info {
        border: 2px solid #3b82f6;
        color: #3b82f6;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-outline-info:hover {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-color: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }
    
    /* Modal enhancements */
    .modal {
        overflow-y: auto;
    }
    
    .modal-dialog {
        max-height: 90vh;
        margin: 1.75rem auto;
        display: flex;
        flex-direction: column;
    }
    
    .modal-content {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        overflow: hidden;
    }

    /* Create step modal — modern form shell */
    .fs-modal {
        position: fixed;
        inset: 0;
        z-index: 1055;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
        overflow-y: auto;
    }

    .fs-modal-dialog {
        margin: auto;
        width: 100%;
        max-width: 920px;
    }

    .fs-modal-content {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.28);
        overflow: hidden;
    }

    .fs-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }

    .fs-modal-subtitle {
        font-size: 0.8rem;
        color: #64748b;
    }

    .fs-modal-close {
        flex-shrink: 0;
        margin: -0.25rem -0.25rem 0 0;
        padding: 0.25rem 0.5rem;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #64748b;
        opacity: 0.75;
        background: transparent;
        border: 0;
        cursor: pointer;
    }

    .fs-modal-close:hover {
        color: #0f172a;
        opacity: 1;
    }

    .fs-modal-body {
        padding: 1.25rem 1.5rem;
        background: #fff;
    }

    .fs-modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .fs-form-section {
        margin-bottom: 1.25rem;
        padding-bottom: 1.15rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .fs-form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .fs-form-section-title {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        margin: 0 0 0.75rem;
    }

    .fs-form-section-hint {
        font-size: 0.8rem;
        color: #94a3b8;
        margin: -0.35rem 0 0.75rem;
    }

    .fs-form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.35rem;
    }

    .fs-input,
    .fs-step-form .form-control,
    .fs-step-form .form-select {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .fs-input:focus,
    .fs-step-form .form-control:focus,
    .fs-step-form .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        outline: none;
    }

    .fs-step-type-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.65rem;
    }

    .fs-step-type-grid.is-invalid {
        padding: 0.35rem;
        border-radius: 0.65rem;
        border: 1px solid #dc3545;
    }

    .fs-step-type-tile {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
        margin: 0;
        padding: 0.85rem 0.9rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.65rem;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        text-align: left;
    }

    .fs-step-type-tile:hover {
        border-color: #93c5fd;
        background: #f8fafc;
    }

    .fs-step-type-tile.is-active {
        border-color: #3b82f6;
        background: linear-gradient(180deg, #ffffff 0%, #f0f7ff 100%);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .fs-step-type-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .fs-step-type-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        font-size: 1.1rem;
    }

    .fs-step-type-tile--input .fs-step-type-icon { background: #ecfdf5; color: #059669; }
    .fs-step-type-tile--derived .fs-step-type-icon { background: #eff6ff; color: #2563eb; }
    .fs-step-type-tile--lookup .fs-step-type-icon { background: #fffbeb; color: #d97706; }
    .fs-step-type-tile--result .fs-step-type-icon { background: #f5f3ff; color: #7c3aed; }

    .fs-step-type-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: #0f172a;
    }

    .fs-step-type-hint {
        font-size: 0.72rem;
        color: #64748b;
        line-height: 1.35;
    }

    .fs-form-scroll {
        overflow-y: auto;
        max-height: calc(90vh - 380px);
        padding-right: 0.25rem;
        margin-right: -0.25rem;
    }

    .fs-config-panel {
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #fafbfc;
    }

    .fs-config-panel-head {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
    }

    .fs-config-panel-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .fs-config-panel--derived .fs-config-panel-icon { background: #eff6ff; color: #2563eb; }
    .fs-config-panel--lookup .fs-config-panel-icon { background: #fffbeb; color: #d97706; }
    .fs-config-panel--result .fs-config-panel-icon { background: #f5f3ff; color: #7c3aed; }

    .fs-config-panel-body {
        padding: 1rem;
    }

    .fs-config-panel--checkbox .fs-config-panel-icon {
        background: #eff6ff;
        color: #2563eb;
    }

    .fs-checkbox-options-source {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 575px) {
        .fs-checkbox-options-source {
            grid-template-columns: 1fr;
        }
    }

    .fs-checkbox-source-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        padding: 0.65rem 0.85rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    }

    .fs-checkbox-source-option:hover {
        border-color: #93c5fd;
        background: #f8fafc;
    }

    .fs-checkbox-source-option.is-active {
        border-color: #3b82f6;
        background: #eff6ff;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .fs-checkbox-source-option-input {
        flex-shrink: 0;
        margin: 0;
        accent-color: #2563eb;
    }

    .fs-checkbox-source-option-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #334155;
        line-height: 1.3;
    }

    .fs-checkbox-source-option.is-active .fs-checkbox-source-option-label {
        color: #1d4ed8;
    }

    .fs-checkbox-option-add {
        display: flex;
        align-items: stretch;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .fs-checkbox-option-add-input {
        flex: 1 1 auto;
        min-width: 0;
    }

    .fs-checkbox-option-add-btn {
        flex: 0 0 auto;
        white-space: nowrap;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .fs-checkbox-options-list {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background: #fff;
        overflow: hidden;
    }

    .fs-checkbox-options-list-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.65rem 0.85rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .fs-checkbox-options-list-item:last-child {
        border-bottom: none;
    }

    .fs-checkbox-options-list-text {
        flex: 1 1 auto;
        font-size: 0.875rem;
        color: #334155;
        line-height: 1.45;
        word-break: break-word;
    }

    .fs-checkbox-options-list-remove {
        flex-shrink: 0;
        line-height: 1;
    }

    @media (min-width: 768px) {
        .fs-step-type-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 767px) {
        .fs-modal {
            padding: 0.5rem;
            align-items: flex-end;
        }

        .fs-form-scroll {
            max-height: calc(100vh - 360px);
        }
    }

    /* Step modals: keep header fields outside scroll so native select lists are not clipped */
    .formula-step-modal.modal,
    .formula-step-modal.fs-modal {
        overflow-y: auto;
    }

    .formula-step-modal .modal-dialog {
        overflow: visible;
        max-height: none;
    }

    .formula-step-modal .modal-content {
        overflow: visible;
        max-height: none;
    }

    .formula-step-modal .modal-body {
        overflow: visible;
        max-height: none;
    }

    .step-type-picker {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .step-type-picker.is-invalid {
        padding: 0.25rem;
        border-radius: 0.5rem;
        border: 1px solid #dc3545;
    }

    .step-type-option {
        flex: 1 1 calc(50% - 0.5rem);
        min-width: 7.5rem;
        margin: 0;
        padding: 0.5rem 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #fff;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    }

    .step-type-option:hover {
        border-color: #3b82f6;
        background: #f8fafc;
    }

    .step-type-option.active {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .step-type-option-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    @media (min-width: 576px) {
        .step-type-option {
            flex: 1 1 auto;
        }
    }

    .formula-step-modal-scroll {
        overflow-y: auto;
        max-height: calc(90vh - 320px);
        padding-right: 0.25rem;
        margin-right: -0.25rem;
    }

    .formula-step-modal .modal-body .mb-3:has(.modern-select.no-select2) {
        overflow: visible;
    }
    
    .modal-header {
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 1rem 1rem 0 0;
        flex-shrink: 0;
        padding: 1.5rem;
    }
    
    .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        max-height: calc(90vh - 120px); /* Subtract header and footer height */
    }
    
    .modal-footer {
        border-top: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 0 0 1rem 1rem;
        flex-shrink: 0;
        padding: 1rem 1.5rem;
    }
    
    .modal-title {
        font-weight: 600;
        color: #1f2937;
    }
    
    /* Custom scrollbar for modal body */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Ensure proper spacing in modal body */
    .modal-body .row {
        margin-bottom: 1rem;
    }
    
    .modal-body .card {
        margin-bottom: 1.5rem;
    }
    
    /* Modal backdrop improvements */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
    }
    
    /* Ensure modal is properly centered and responsive */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
            max-height: calc(100vh - 1rem);
        }
        
        .modal-content {
            max-height: calc(100vh - 1rem);
        }
        
        .modal-body {
            max-height: calc(100vh - 200px);
            padding: 1rem;
        }

        .formula-step-modal-scroll {
            max-height: calc(100vh - 340px);
        }
        
        .modal-header {
            padding: 1rem;
        }
        
        .modal-footer {
            padding: 0.75rem 1rem;
        }
    }
    
    /* Large screens - ensure modal doesn't get too wide */
    @media (min-width: 1200px) {
        .modal-dialog.modal-lg {
            max-width: 800px;
        }
    }
    
    /* Extra large screens */
    @media (min-width: 1400px) {
        .modal-dialog.modal-lg {
            max-width: 900px;
        }
    }
    
    /* Fix for modal show class */
    .modal.show .modal-dialog {
        transform: none;
    }
    
    /* Ensure form elements don't cause horizontal overflow */
    .modal-body .form-control,
    .modal-body .form-select,
    .modal-body .modern-select {
        max-width: 100%;
    }
    
    /* Fix for long text in modals */
    .modal-body .text-break {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    /* Ensure modal backdrop doesn't interfere with scrolling */
    .modal-backdrop.show {
        z-index: 1040;
    }
    
    .modal.show {
        z-index: 1050;
    }
    
    /* Fix for modal positioning */
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 3.5rem);
    }
    
    /* Ensure proper modal height calculation */
    .modal-dialog {
        height: auto;
        min-height: 0;
    }
    
    /* Fix for very tall content */
    .modal-body {
        min-height: 0;
        flex-shrink: 1;
    }
    
    /* Focus states for better accessibility */
    .modern-select:focus-visible {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }
    
    /* Disabled state styling */
    .modern-select:disabled {
        background: #f9fafb;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }
    
    /* Loading state (if needed) */
    .modern-select.loading {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'%3e%3ccircle cx='12' cy='12' r='10' stroke='%233b82f6' stroke-width='2' opacity='0.3'/%3e%3cpath d='M12 2a10 10 0 0 1 10 10' stroke='%233b82f6' stroke-width='2' stroke-linecap='round'/%3e%3c/svg%3e");
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Drag and Drop Styling */
    .sortable-row {
        transition: all 0.2s ease;
    }
    
    .sortable-row:hover {
        background-color: #f8f9fa !important;
    }
    
    .drag-handle:hover {
        background-color: #e9ecef;
        border-radius: 4px;
    }
    
    .drag-handle:hover i {
        color: #007bff !important;
    }
    
    /* SortableJS visual feedback */
    .sortable-ghost {
        opacity: 0.4;
        background-color: #c8e6c9 !important;
        border: 2px dashed #4caf50 !important;
    }
    
    .sortable-chosen {
        background-color: #e3f2fd !important;
        border: 2px solid #2196f3 !important;
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .sortable-drag {
        background-color: #fff3e0 !important;
        border: 2px solid #ff9800 !important;
        transform: rotate(2deg);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    /* Mandatory fields: placement + modal */
    .fm-placement-bar--inline {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 1rem;
        padding: 0.65rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background: #f8fafc;
    }

    @media (max-width: 767px) {
        .fm-placement-bar--inline {
            flex-wrap: wrap;
        }
    }

    .fm-placement-bar__text {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem 0.5rem;
        flex: 1 1 auto;
        min-width: 0;
    }

    .fm-placement-bar__title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
    }

    .fm-placement-bar__desc {
        font-size: 0.8125rem;
        line-height: 1.4;
    }

    .fm-placement-options--compact {
        display: flex;
        flex: 0 0 auto;
        gap: 0.35rem;
        margin-left: auto;
    }

    .fm-placement-chip {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin: 0;
        padding: 0.3rem 0.65rem;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #fff;
        font-size: 0.75rem;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
        white-space: nowrap;
    }

    .fm-placement-chip:hover {
        border-color: #93c5fd;
        color: #2563eb;
    }

    .fm-placement-chip.is-active {
        border-color: #3b82f6;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .fm-placement-chip i {
        font-size: 0.9rem;
        line-height: 1;
    }

    .fm-placement-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .fm-mandatory-modal .fm-placement-tag-select {
        display: block;
        width: 100%;
    }

    .fm-mandatory-modal .fm-placement-tag-select .tag-select-input {
        min-height: 38px;
        padding: 4px 10px;
        width: 100%;
    }

    .fm-mandatory-modal .fm-placement-tag-select .tag-dropdown {
        width: 100%;
        z-index: 1060;
    }

    .fm-mandatory-modal .tag-badge {
        background-color: #3b82f6;
        font-size: 0.8125rem;
        padding: 3px 10px;
    }

    .fm-mandatory-modal .fs-modal-dialog {
        max-width: 52rem;
    }

    .fm-field-hint {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.75rem;
        color: #64748b;
    }

    .fm-checkbox-card {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        padding: 0.65rem 0.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background: #fff;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .fm-field-type-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
    }

    @media (min-width: 768px) {
        .fm-field-type-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .fm-field-type-tile {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.2rem;
        margin: 0;
        padding: 0.75rem 0.85rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.65rem;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
        text-align: left;
    }

    .fm-field-type-tile:hover {
        border-color: #93c5fd;
    }

    .fm-field-type-tile.is-active {
        border-color: #3b82f6;
        background: #f0f7ff;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .fm-field-type-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .fm-field-type-icon {
        font-size: 1.15rem;
        color: #2563eb;
    }

    .fm-field-type-name {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #0f172a;
    }

    .fm-field-type-hint {
        font-size: 0.7rem;
        color: #64748b;
        line-height: 1.35;
    }

    .fm-choices-list__item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .fm-choices-list__order {
        flex-shrink: 0;
        width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eff6ff;
        color: #2563eb;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .fm-choices-list__remove {
        flex-shrink: 0;
        padding: 0.2rem 0.45rem;
    }

    .fm-choices-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 0.5rem;
        background: #f8fafc;
    }

    .fm-mandatory-card {
        border-radius: 12px;
    }

    /* Custom table configure wizard (ported from procedure editor) */
    .pw-table-config-modal.fs-modal .fs-modal-dialog {
        max-width: min(1200px, calc(100vw - 2rem));
    }

    .pw-table-config-modal .fs-modal-body.pw-table-wizard__body {
        padding: 0;
        max-height: min(70vh, 720px);
        overflow-y: auto;
    }

    .pw-table-wizard__header .modal-title {
        font-weight: 700;
        color: #0f172a;
    }

    .pw-table-wizard__stepper {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid #e2e8f0;
    }

    .pw-table-wizard__steps {
        display: flex;
        align-items: stretch;
        gap: 0;
        max-width: 100%;
    }

    .pw-table-wizard__step {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
        border: 1px solid transparent;
        border-radius: 10px;
        background: transparent;
        text-align: left;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .pw-table-wizard__step:hover:not(:disabled) {
        background: #f1f5f9;
    }

    .pw-table-wizard__step:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .pw-table-wizard__step--active {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .pw-table-wizard__step--done .pw-table-wizard__step-index {
        background: #dcfce7;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .pw-table-wizard__step-index {
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 700;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #64748b;
    }

    .pw-table-wizard__step--active .pw-table-wizard__step-index {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .pw-table-wizard__step-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .pw-table-wizard__step-hint {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
        margin-top: 0.1rem;
    }

    .pw-table-wizard__connector {
        flex: 0 0 1.5rem;
        align-self: center;
        height: 2px;
        background: #e2e8f0;
        margin: 0 0.15rem;
    }

    .pw-table-wizard__connector--done {
        background: #86efac;
    }

    .pw-table-wizard__body {
        background: #f8fafc;
    }

    .pw-table-wizard__panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin: 1.25rem 1.5rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .pw-table-wizard__panel-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .pw-table-wizard__panel-head h6 {
        font-weight: 700;
        color: #0f172a;
    }

    .pw-table-wizard__cta {
        flex-shrink: 0;
        white-space: nowrap;
    }

    .pw-table-wizard__empty {
        text-align: center;
        padding: 2.5rem 1.5rem;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        color: #64748b;
    }

    .pw-table-wizard__empty > i {
        font-size: 2.25rem;
        color: #94a3b8;
        display: block;
        margin-bottom: 0.75rem;
    }

    .pw-table-wizard__hint-box {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        color: #0c4a6e;
        font-size: 0.85rem;
    }

    .pw-table-wizard__hint-box--warn {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    .pw-modal-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        padding: 1rem;
    }

    .pw-modal-card-title {
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 0.85rem;
        display: flex;
        gap: 0.4rem;
        align-items: center;
    }

    .pw-table-wizard__inline-form {
        background: #f8fafc;
        border-style: dashed;
    }

    .pw-table-shell {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .pw-table-key {
        font-size: 0.78rem;
        color: #be185d;
        background: #fdf2f8;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
    }

    .pw-col-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .pw-col-type-badge--static {
        background: #ecfdf5;
        color: #047857;
    }

    .pw-col-type-badge--capture {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .pw-pill {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .pw-pill--yes {
        background: #dcfce7;
        color: #15803d;
    }

    .pw-pill--muted {
        background: #f1f5f9;
        color: #64748b;
    }

    .pw-table-wizard__col-actions {
        width: 88px;
        white-space: nowrap;
    }

    .pw-inline-column-form__grid > [class*="col-"] {
        margin-bottom: 1rem;
    }

    .pw-inline-column-form__actions .btn + .btn {
        margin-left: 0.5rem;
    }
    </style>
    
    <!-- SortableJS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle quick select for lookup key expressions
        document.addEventListener('change', function(e) {
            if (e.target.matches('.quick-select-variable')) {
                const targetInputId = e.target.getAttribute('data-target-input');
                const input = document.getElementById(targetInputId);
                
                if (input && e.target.value) {
                    // Set the value
                    input.value = e.target.value;
                    
                    // Trigger Livewire update
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    // Add visual feedback with green flash
                    input.style.borderColor = '#10b981';
                    input.style.backgroundColor = '#f0fdf4';
                    input.style.transition = 'all 0.3s ease';
                    
                    setTimeout(() => {
                        input.style.borderColor = '';
                        input.style.backgroundColor = '';
                    }, 1000);
                    
                    // Reset the select dropdown
                    e.target.value = '';
                }
            }
        });
        
        // Add smooth transitions to all modern-select elements
        const modernSelects = document.querySelectorAll('.modern-select');
        modernSelects.forEach(select => {
            // Add focus/blur effects
            select.addEventListener('focus', function() {
                this.style.transform = 'translateY(-1px)';
                this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
            });
            
            select.addEventListener('blur', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
            });
            
            // Add hover effects
            select.addEventListener('mouseenter', function() {
                if (!this.matches(':focus')) {
                    this.style.transform = 'translateY(-1px)';
                }
            });
            
            select.addEventListener('mouseleave', function() {
                if (!this.matches(':focus')) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });
        
        // Enhanced form validation feedback
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const selects = this.querySelectorAll('.modern-select');
                selects.forEach(select => {
                    if (select.hasAttribute('required') && !select.value) {
                        select.style.borderColor = '#ef4444';
                        select.style.backgroundColor = '#fef2f2';
                        select.focus();
                        e.preventDefault();
                    }
                });
            });
        });
        
        // Modal scrolling enhancements
        function initializeModalScrolling() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.classList.contains('formula-step-modal')) {
                    return;
                }

                const modalBody = modal.querySelector('.modal-body');
                if (modalBody) {
                    // Ensure modal body is scrollable
                    modalBody.style.overflowY = 'auto';
                    modalBody.style.maxHeight = 'calc(90vh - 120px)';
                    
                    // Add scroll indicator when content overflows
                    const checkScroll = () => {
                        if (modalBody.scrollHeight > modalBody.clientHeight) {
                            modalBody.style.borderBottom = '2px solid #e5e7eb';
                        } else {
                            modalBody.style.borderBottom = 'none';
                        }
                    };
                    
                    checkScroll();
                    modalBody.addEventListener('scroll', checkScroll);
                    window.addEventListener('resize', checkScroll);
                }
            });
        }
        
        // Initialize modal scrolling when modals are shown
        document.addEventListener('DOMContentLoaded', initializeModalScrolling);
        
        // Re-initialize when Livewire updates the DOM
        document.addEventListener('livewire:load', initializeModalScrolling);
        document.addEventListener('livewire:update', initializeModalScrolling);
        
        // Initialize drag and drop functionality
        initializeSortable();
    });
    
    // Initialize sortable when Livewire updates the DOM
    document.addEventListener('livewire:updated', () => {
        initializeSortable();
    });
    
    function initializeSortable() {
        // Initialize sortable for steps table
        const sortableElement = document.getElementById('sortable-steps');
        console.log('Initializing sortable on element:', sortableElement);
        
        if (sortableElement && typeof Sortable !== 'undefined') {
            console.log('Sortable library loaded, creating sortable instance');
            
            // Destroy existing sortable instance if it exists
            if (sortableElement.sortableInstance) {
                sortableElement.sortableInstance.destroy();
            }
            
            sortableElement.sortableInstance = Sortable.create(sortableElement, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    console.log('Drag ended, new index:', evt.newIndex, 'old index:', evt.oldIndex);
                    
                    const stepIds = Array.from(sortableElement.children).map(row => {
                        return parseInt(row.getAttribute('data-step-id'));
                    });
                    
                    console.log('New step order:', stepIds);
                    
                    // Send the new order to Livewire
                    @this.call('updateStepOrder', stepIds);
                }
            });
            
            console.log('Sortable instance created successfully');
        } else {
            console.error('Failed to initialize sortable:', {
                sortableElement: sortableElement,
                sortableLibrary: typeof Sortable
            });
        }

        // Initialize sortable for mandatory fields table
        const sortableFieldsElement = document.getElementById('sortable-fields');
        console.log('Initializing sortable on fields element:', sortableFieldsElement);
        
        if (sortableFieldsElement && typeof Sortable !== 'undefined') {
            console.log('Sortable library loaded, creating sortable instance for fields');
            
            // Destroy existing sortable instance if it exists
            if (sortableFieldsElement.sortableInstance) {
                sortableFieldsElement.sortableInstance.destroy();
            }
            
            sortableFieldsElement.sortableInstance = Sortable.create(sortableFieldsElement, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    console.log('Field drag ended, new index:', evt.newIndex, 'old index:', evt.oldIndex);
                    
                    const fieldIds = Array.from(sortableFieldsElement.children).map(row => {
                        return parseInt(row.getAttribute('data-field-id'));
                    });
                    
                    console.log('New field order:', fieldIds);
                    
                    // Send the new order to Livewire
                    @this.call('updateFieldOrder', fieldIds);
                }
            });
            
            console.log('Sortable instance for fields created successfully');
        } else {
            console.error('Failed to initialize sortable for fields:', {
                sortableFieldsElement: sortableFieldsElement,
                sortableLibrary: typeof Sortable
            });
        }
    }
    
    // Handle modal backdrop click to close
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal') && e.target.classList.contains('show')) {
            const closeButton = e.target.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }
    });
    </script>
    
    <style>
    /* Single-Select Searchable Dropdown Styling */
    .searchable-input-single {
        border: none;
        outline: none;
        box-shadow: none !important;
        padding: 4px 0;
        width: 100%;
    }
    
    .searchable-input-single:focus {
        border: none !important;
        box-shadow: none !important;
    }
    
    .single-select-container {
        position: relative;
        min-height: 45px;
        border: 1px solid #ced4da;
        border-radius: 12px;
        padding: 8px 40px 8px 12px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    
    .single-select-container:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
    }
    
    .single-select-container:has(.searchable-input-single:focus) {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .options-list {
        padding: 8px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .option-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }
    
    .option-item:hover {
        background: #f8f9fa;
    }
    
    .option-item.selected {
        background: rgba(0, 123, 255, 0.08);
        font-weight: 500;
    }
    
    .option-item i {
        font-size: 18px;
    }
    </style>

    @include('livewire.formulars.partials.formula-step-table-modals')
</div>