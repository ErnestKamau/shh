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
                                <a href="{{ route('formulars.manage') }}" class="btn btn-outline-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back to Formulas
                                </a>
                                <button wire:click="showTestFormulaModalInit" class="btn btn-info">
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

        <!-- Message Alert -->
        @if($message)
            <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
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
                                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search by variable name, label, or description...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Type</label>
                                    <select wire:model.live="typeFilter" class="form-select">
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
                                    <select wire:model.live="perPage" class="form-select">
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
                                                    <span class="badge badge-{{ $step['step_type'] === 'input' ? 'success' : ($step['step_type'] === 'derived' ? 'info' : 'warning') }}">
                                                        {{ ucfirst($step['step_type']) }}
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
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ Str::limit($step['description'], 50) }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="showEditStepModalInit({{ $step['id'] }})" 
                                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="showDeleteStepModal({{ $step['id'] }})" 
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
                                <i class="mdi mdi-cogs fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No steps defined</h5>
                                <p class="text-muted">Add steps to define your formula workflow.</p>
                                <button wire:click="showCreateStepModal" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add First Step
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @if($showCreateStepModal)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="mdi mdi-plus"></i>
                                Create New Step
                            </h5>
                            <button type="button" class="btn-close" wire:click="$set('showCreateStepModal', false)"></button>
                        </div>
                        <div class="modal-body">
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
                            
                            <form wire:submit.prevent="createStep">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="stepNumber" class="form-label">Step Number *</label>
                                            <input type="number" wire:model="stepNumber" class="form-control @error('stepNumber') is-invalid @enderror" id="stepNumber" required min="1">
                                            @error('stepNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="stepType" class="form-label">Step Type *</label>
                                            <select wire:model.live="stepType" class="form-select modern-select @error('stepType') is-invalid @enderror" id="stepType" required>
                                                <option value="">Select Step Type</option>
                                                <option value="input">Input</option>
                                                <option value="derived">Derived</option>
                                                <option value="lookup">Lookup</option>
                                            </select>
                                            @error('stepType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="variableName" class="form-label">Variable Name *</label>
                                            <input type="text" wire:model="variableName" class="form-control @error('variableName') is-invalid @enderror" id="variableName" required>
                                            @error('variableName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="label" class="form-label">Label *</label>
                                            <input type="text" wire:model="label" class="form-control @error('label') is-invalid @enderror" id="label" required>
                                            @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
        
                                @if($stepType === 'derived')
                                    <!-- Derived Step Configuration -->
                                    <div class="card bg-light mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0 text-muted">
                                                <i class="mdi mdi-function"></i> Expression Configuration
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="expression" class="form-label">Expression *</label>
                                                <div class="input-group">
                                                    <textarea wire:model="expression" class="form-control @error('expression') is-invalid @enderror" id="expression" rows="3" required placeholder="e.g., (temperature * 1.8) + 32"></textarea>
                                                    <button type="button" wire:click="validateExpression" class="btn btn-outline-info">
                                                        <i class="mdi mdi-check"></i> Validate
                                                    </button>
                                                </div>
                                                @error('expression') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                <div class="form-text">Use variable names from previous steps in your expression</div>
                                            </div>
                                            
                                            <!-- Available Variables Reference -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="text-muted mb-2">Available Variables</h6>
                                                    @if(count($availableVariables) > 0)
                                                        <div class="list-group list-group-flush">
                                                            @foreach($availableVariables as $var)
                                                                <div class="list-group-item px-0 py-1 border-0">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <code class="text-primary">{{ $var['name'] }}</code>
                                                                            <small class="text-muted d-block">{{ $var['label'] }}</small>
                                                                        </div>
                                                                        <span class="badge badge-{{ $var['type'] === 'input' ? 'success' : ($var['type'] === 'derived' ? 'info' : 'warning') }} badge-sm">
                                                                            {{ ucfirst($var['type']) }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-muted small">No variables available from previous steps</p>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-muted mb-2">Global Constants</h6>
                                                    @if(count($globalVariables) > 0)
                                                        <div class="list-group list-group-flush">
                                                            @foreach($globalVariables as $constant)
                                                                <div class="list-group-item px-0 py-1 border-0">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <code class="text-success">{{ $constant->name }}</code>
                                                                            <small class="text-muted d-block">{{ $constant->description }}</small>
                                                                        </div>
                                                                        <span class="badge badge-secondary badge-sm">
                                                                            {{ $constant->value }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-muted small">No global constants defined</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
        
                                @if($stepType === 'lookup')
                                    <!-- Lookup Step Configuration -->
                                    <div class="card bg-light mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0 text-muted">
                                                <i class="mdi mdi-table-search"></i> Lookup Configuration
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="lookupTableId" class="form-label">Lookup Table *</label>
                                                <select wire:model.live="lookupTableId" class="form-select modern-select @error('lookupTableId') is-invalid @enderror" id="lookupTableId" required>
                                                    <option value="">Select a lookup table</option>
                                                    @foreach($lookupTables as $table)
                                                        <option value="{{ $table->id }}">{{ $table->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('lookupTableId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            @if($lookupTableId)
                                                @php
                                                    $selectedTable = $lookupTables->firstWhere('id', $lookupTableId);
                                                @endphp
                                                @if($selectedTable)
                                                    <div class="alert alert-info">
                                                        <h6 class="alert-heading">
                                                            <i class="mdi mdi-information"></i> Table Information
                                                        </h6>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <strong>Key Columns:</strong>
                                                                @foreach($selectedTable->key_columns as $column)
                                                                    <span class="badge badge-secondary me-1">{{ $column }}</span>
                                                                @endforeach
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Value Column:</strong>
                                                                <span class="badge badge-primary">{{ $selectedTable->value_column }}</span>
                                                            </div>
                                                        </div>
                                                        @if($selectedTable->key_label || $selectedTable->value_label)
                                                            <div class="row mt-2">
                                                                <div class="col-md-6">
                                                                    @if($selectedTable->key_label)
                                                                        <strong>Key Label:</strong> {{ $selectedTable->key_label }}
                                                                    @endif
                                                                </div>
                                                                <div class="col-md-6">
                                                                    @if($selectedTable->value_label)
                                                                        <strong>Value Label:</strong> {{ $selectedTable->value_label }}
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                @if(!empty($lookupConfig))
                                                    <div class="mb-3">
                                                        <label class="form-label">Key Configuration</label>
                                                        <div class="card bg-white">
                                                            <div class="card-body">
                                                                @foreach($lookupConfig['key_expressions'] as $key => $value)
                                                                    <div class="mb-3">
                                                                        <label class="form-label small">
                                                                            {{ $selectedTable->key_label ?: $key }}
                                                                            <span class="text-muted">({{ $key }})</span>
                                                                        </label>
                                                                        <div class="row">
                                                                            <div class="col-md-8">
                                                                                <div class="input-group">
                                                                                    <span class="input-group-text">
                                                                                        <i class="mdi mdi-key"></i>
                                                                                    </span>
                                                                                    <input type="text" 
                                                                                           wire:model="lookupConfig.key_expressions.{{ $key }}" 
                                                                                           class="form-control" 
                                                                                           placeholder="Expression or value (e.g., temperature, {{ $key }})">
                                                                                </div>
                                                                                <div class="form-text">Use variable names or direct values</div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <label class="form-label small text-muted">Quick Select</label>
                                                                                <select class="form-select form-select-sm" 
                                                                                        onchange="document.querySelector('input[wire\\:model=\"lookupConfig.key_expressions.{{ $key }}\"]').value = this.value">
                                                                                    <option value="">Select Variable</option>
                                                                                    @foreach($availableVariables as $varName => $varData)
                                                                                        <option value="{{ $varName }}">{{ $varData['label'] }} ({{ $varName }})</option>
                                                                                    @endforeach
                                                                                    @foreach($globalVariables as $constant)
                                                                                        <option value="{{ $constant->name }}">{{ $constant->name }} ({{ $constant->value }})</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
        
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description" rows="2"></textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showCreateStepModal', false)">Cancel</button>
                            <button type="button" class="btn btn-primary" wire:click="createStep">Create Step</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

<!-- Create Step Modal -->

<!-- Edit Step Modal -->
@if($showEditStepModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i>
                        Edit Step
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showEditStepModal', false)"></button>
                </div>
                <div class="modal-body">
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
                    
                    <form wire:submit.prevent="updateStep">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editStepNumber" class="form-label">Step Number *</label>
                                    <input type="number" wire:model="stepNumber" class="form-control @error('stepNumber') is-invalid @enderror" id="editStepNumber" required min="1">
                                    @error('stepNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editStepType" class="form-label">Step Type *</label>
                                    <select wire:model.live="stepType" class="form-select modern-select @error('stepType') is-invalid @enderror" id="editStepType" required>
                                        <option value="">Select Step Type</option>
                                        <option value="input">Input</option>
                                        <option value="derived">Derived</option>
                                        <option value="lookup">Lookup</option>
                                    </select>
                                    @error('stepType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editVariableName" class="form-label">Variable Name *</label>
                                    <input type="text" wire:model="variableName" class="form-control @error('variableName') is-invalid @enderror" id="editVariableName" required>
                                    @error('variableName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editLabel" class="form-label">Label *</label>
                                    <input type="text" wire:model="label" class="form-control @error('label') is-invalid @enderror" id="editLabel" required>
                                    @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        @if($stepType === 'derived')
                            <!-- Derived Step Configuration -->
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0 text-muted">
                                        <i class="mdi mdi-function"></i> Expression Configuration
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="editExpression" class="form-label">Expression *</label>
                                        <div class="input-group">
                                            <textarea wire:model="expression" class="form-control @error('expression') is-invalid @enderror" id="editExpression" rows="3" required placeholder="e.g., (temperature * 1.8) + 32"></textarea>
                                            <button type="button" wire:click="validateExpression" class="btn btn-outline-info">
                                                <i class="mdi mdi-check"></i> Validate
                                            </button>
                                        </div>
                                        @error('expression') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-text">Use variable names from previous steps in your expression</div>
                                    </div>
                                    
                                    <!-- Available Variables Reference -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2">Available Variables</h6>
                                            @if(count($availableVariables) > 0)
                                                <div class="list-group list-group-flush">
                                                    @foreach($availableVariables as $var)
                                                        <div class="list-group-item px-0 py-1 border-0">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <code class="text-primary">{{ $var['name'] }}</code>
                                                                    <small class="text-muted d-block">{{ $var['label'] }}</small>
                                                                </div>
                                                                <span class="badge badge-{{ $var['type'] === 'input' ? 'success' : ($var['type'] === 'derived' ? 'info' : 'warning') }} badge-sm">
                                                                    {{ ucfirst($var['type']) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted small">No variables available from previous steps</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2">Global Constants</h6>
                                            @if(count($globalVariables) > 0)
                                                <div class="list-group list-group-flush">
                                                    @foreach($globalVariables as $constant)
                                                        <div class="list-group-item px-0 py-1 border-0">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <code class="text-success">{{ $constant->name }}</code>
                                                                    <small class="text-muted d-block">{{ $constant->description }}</small>
                                                                </div>
                                                                <span class="badge badge-secondary badge-sm">
                                                                    {{ $constant->value }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted small">No global constants defined</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($stepType === 'lookup')
                            <!-- Lookup Step Configuration -->
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0 text-muted">
                                        <i class="mdi mdi-table-search"></i> Lookup Configuration
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="editLookupTableId" class="form-label">Lookup Table *</label>
                                        <select wire:model.live="lookupTableId" class="form-select modern-select @error('lookupTableId') is-invalid @enderror" id="editLookupTableId" required>
                                            <option value="">Select a lookup table</option>
                                            @foreach($lookupTables as $table)
                                                <option value="{{ $table->id }}">{{ $table->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('lookupTableId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    @if($lookupTableId)
                                        @php
                                            $selectedTable = $lookupTables->firstWhere('id', $lookupTableId);
                                        @endphp
                                        @if($selectedTable)
                                            <div class="alert alert-info">
                                                <h6 class="alert-heading">
                                                    <i class="mdi mdi-information"></i> Table Information
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Key Columns:</strong>
                                                        @foreach($selectedTable->key_columns as $column)
                                                            <span class="badge badge-secondary me-1">{{ $column }}</span>
                                                        @endforeach
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Value Column:</strong>
                                                        <span class="badge badge-primary">{{ $selectedTable->value_column }}</span>
                                                    </div>
                                                </div>
                                                @if($selectedTable->key_label || $selectedTable->value_label)
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            @if($selectedTable->key_label)
                                                                <strong>Key Label:</strong> {{ $selectedTable->key_label }}
                                                            @endif
                                                        </div>
                                                        <div class="col-md-6">
                                                            @if($selectedTable->value_label)
                                                                <strong>Value Label:</strong> {{ $selectedTable->value_label }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        @if(!empty($lookupConfig))
                                            <div class="mb-3">
                                                <label class="form-label">Key Configuration</label>
                                                <div class="card bg-white">
                                                    <div class="card-body">
                                                        @foreach($lookupConfig['key_expressions'] as $key => $value)
                                                            <div class="mb-3">
                                                                <label class="form-label small">
                                                                    {{ $selectedTable->key_label ?: $key }}
                                                                    <span class="text-muted">({{ $key }})</span>
                                                                </label>
                                                                <div class="row">
                                                                    <div class="col-md-8">
                                                                        <div class="input-group">
                                                                            <span class="input-group-text">
                                                                                <i class="mdi mdi-key"></i>
                                                                            </span>
                                                                            <input type="text" 
                                                                                   wire:model="lookupConfig.key_expressions.{{ $key }}" 
                                                                                   class="form-control" 
                                                                                   placeholder="Expression or value (e.g., temperature, {{ $key }})">
                                                                        </div>
                                                                        <div class="form-text">Use variable names or direct values</div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label small text-muted">Quick Select</label>
                                                                        <select class="form-select form-select-sm" 
                                                                                onchange="document.querySelector('input[wire\\:model=\"lookupConfig.key_expressions.{{ $key }}\"]').value = this.value">
                                                                            <option value="">Select Variable</option>
                                                                            @foreach($availableVariables as $varName => $varData)
                                                                                <option value="{{ $varName }}">{{ $varData['label'] }} ({{ $varName }})</option>
                                                                            @endforeach
                                                                            @foreach($globalVariables as $constant)
                                                                                <option value="{{ $constant->name }}">{{ $constant->name }} ({{ $constant->value }})</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="editDescription" rows="2"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showEditStepModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="updateStep">Update Step</button>
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
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-play text-info"></i>
                        Test Formula Execution
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showTestFormulaModal', false)"></button>
                </div>
                <div class="modal-body">
                    @if(count($testInputs) > 0)
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Input Values</h6>
                                @foreach($testInputs as $variableName => $value)
                                    <div class="mb-3">
                                        <label for="testInput_{{ $variableName }}" class="form-label">{{ $variableName }}</label>
                                        <input type="number" 
                                               wire:model="testInputs.{{ $variableName }}" 
                                               class="form-control" 
                                               id="testInput_{{ $variableName }}"
                                               step="any">
                                    </div>
                                @endforeach
                                
                                <button wire:click="testFormula" class="btn btn-primary">
                                    <i class="mdi mdi-play"></i> Execute Formula
                                </button>
                            </div>
                            
                            <div class="col-md-6">
                                @if(count($testResults) > 0)
                                    <h6 class="mb-3">Execution Results</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            @foreach($testResults as $variableName => $value)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold">{{ $variableName }}:</span>
                                                    <span class="text-primary">{{ is_numeric($value) ? number_format($value, 4) : $value }}</span>
                                                </div>
                                            @endforeach
                                            
                                            @if(isset($testExecutionData['final_result']))
                                                <hr>
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-bold text-success">Final Result:</span>
                                                    <span class="text-success fw-bold">{{ is_numeric($testExecutionData['final_result']) ? number_format($testExecutionData['final_result'], 4) : $testExecutionData['final_result'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="mdi mdi-play-circle-outline fa-3x mb-3"></i>
                                        <p>Enter input values and click "Execute Formula" to see results</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-information text-info fa-3x mb-3"></i>
                            <h5>No Input Steps Found</h5>
                            <p class="text-muted">This formula doesn't have any input steps to test with.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showTestFormulaModal', false)">Close</button>
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

    <style>
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
    </style>
    
    <!-- SortableJS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle quick select for lookup key expressions
        document.addEventListener('change', function(e) {
            if (e.target.matches('select[onchange*="lookupConfig.key_expressions"]')) {
                const input = e.target.closest('.row').querySelector('input[wire\\:model*="lookupConfig.key_expressions"]');
                if (input && e.target.value) {
                    input.value = e.target.value;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    // Add visual feedback
                    input.style.borderColor = '#10b981';
                    input.style.backgroundColor = '#f0fdf4';
                    setTimeout(() => {
                        input.style.borderColor = '';
                        input.style.backgroundColor = '';
                    }, 1000);
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
        
        // Add loading state to selects when they change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('modern-select')) {
                e.target.classList.add('loading');
                setTimeout(() => {
                    e.target.classList.remove('loading');
                }, 500);
            }
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
</div>