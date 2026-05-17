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
                                                    <span class="badge badge-{{ $step['step_type'] === 'input' ? 'success' : ($step['step_type'] === 'derived' ? 'info' : ($step['step_type'] === 'parameter_result' ? 'dark' : 'warning')) }}">
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
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ Str::limit($step['description'], 50) }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="showEditStepModalInit(@js($step['id']))" 
                                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="showDeleteStepModal(@js($step['id']))" 
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
                                            <th>Value Name</th>
                                            <th>Required</th>
                                            <th>Dataset Model</th>
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
                                                    @if($field['model_tied_to'])
                                                        <span class="badge badge-warning">{{ ucfirst($field['model_tied_to']) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
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
            <div class="modal fade show d-block formula-step-modal" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
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
                            
                            <form wire:submit.prevent="createStep">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="stepNumber" class="form-label">Step Number *</label>
                                            <input type="number" wire:model="stepNumber" class="form-control @error('stepNumber') is-invalid @enderror" id="stepNumber" required min="1">
                                            @error('stepNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label d-block"><i class="mdi mdi-format-list-bulleted-type text-primary"></i> Step Type *</label>
                                            <div class="step-type-picker @error('stepType') is-invalid @enderror" id="stepType">
                                                @foreach($this->stepTypeOptions as $value => $label)
                                                    <label class="step-type-option {{ $stepType === $value ? 'active' : '' }}">
                                                        <input type="radio"
                                                               wire:model.live="stepType"
                                                               value="{{ $value }}"
                                                               class="step-type-option-input"
                                                               name="create_step_type">
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('stepType') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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

                                <div class="formula-step-modal-scroll">
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
                                        <label for="lookupTableId" class="form-label"><i class="mdi mdi-table-search text-primary"></i> Lookup Table *</label>
                                        <select wire:model.live="lookupTableId" class="form-select modern-select no-select2 @error('lookupTableId') is-invalid @enderror" id="lookupTableId" required>
                                            <option value="">Select Lookup Table</option>
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
                                                    <div class="alert alert-{{ $selectedTable->lookup_type === 'range_based' ? 'warning' : 'info' }}">
                                                        <h6 class="alert-heading">
                                                            <i class="mdi mdi-information"></i> Table Information
                                                        </h6>
                                                        <div class="mb-2">
                                                            <strong>Type:</strong>
                                                            @if($selectedTable->lookup_type === 'range_based')
                                                                <span class="badge badge-warning">
                                                                    <i class="mdi mdi-chart-line"></i> Range-Based
                                                                </span>
                                                                <br><small class="text-muted">Variable: {{ $selectedTable->range_variable_name }}</small>
                                                            @else
                                                                <span class="badge badge-primary">
                                                                    <i class="mdi mdi-key"></i> Key-Value Comparison
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <strong>{{ $selectedTable->lookup_type === 'range_based' ? 'Range Keys:' : 'Key Columns:' }}</strong>
                                                                @foreach($selectedTable->key_columns as $column)
                                                                    <span class="badge badge-secondary me-1">{{ $column }}</span>
                                                                @endforeach
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Value Column:</strong>
                                                                <span class="badge badge-primary">{{ $selectedTable->value_column }}</span>
                                                                @if($selectedTable->value_interpretation_column)
                                                                    <br><small>+ {{ $selectedTable->value_interpretation_column }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if($selectedTable && $selectedTable->lookup_type === 'range_based')
                                                    <!-- Range-Based Configuration -->
                                                    <div class="alert alert-warning mb-3">
                                                        <i class="mdi mdi-information"></i>
                                                        <strong>Range-Based Lookup:</strong> Select which variable to check against the ranges.
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Variable to Check *</label>
                                                        <select wire:model="lookupConfig.range_variable" class="form-select modern-select no-select2">
                                                            <option value="">Select Variable...</option>
                                                            @foreach($availableVariables as $varName => $varData)
                                                                <option value="{{ $varName }}">{{ $varData['label'] }} ({{ $varName }})</option>
                                                            @endforeach
                                                        </select>
                                                        <small class="text-muted">
                                                            Select which formula variable to check against ranges in "{{ $selectedTable->name }}"
                                                            <br>(Expected range variable: <code>{{ $selectedTable->range_variable_name }}</code>)
                                                        </small>
                                                    </div>
                                                    
                                                    @if($selectedTable->value_interpretation_column)
                                                        <div class="mb-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" 
                                                                       wire:model="lookupConfig.return_interpretation" 
                                                                       class="form-check-input" 
                                                                       id="returnInterpretation">
                                                                <label class="form-check-label" for="returnInterpretation">
                                                                    Return Interpretation Text
                                                                    <span class="badge badge-info">{{ $selectedTable->value_interpretation_column }}</span>
                                                                </label>
                                                            </div>
                                                            <small class="text-muted">
                                                                If checked, returns the text interpretation instead of the numeric value
                                                            </small>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Preview -->
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <h6 class="text-muted">Preview</h6>
                                                            <code>
                                                                IF {{ $lookupConfig['range_variable'] ?? 'variable' }} is in range → 
                                                                Return {{ ($lookupConfig['return_interpretation'] ?? false) ? 'interpretation' : 'value' }}
                                                            </code>
                                                        </div>
                                                    </div>
                                                @elseif(!empty($lookupConfig))
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
                                                                                           class="form-control lookup-key-input" 
                                                                                           id="lookup-key-{{ $key }}-create"
                                                                                           data-key="{{ $key }}"
                                                                                           placeholder="Expression or value (e.g., temperature, {{ $key }})">
                                                                                </div>
                                                                                <div class="form-text">Use variable names or direct values</div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <label class="form-label small text-muted">Quick Select</label>
                                                                                <select class="form-select form-select-sm modern-select-sm quick-select-variable" 
                                                                                        data-target-input="lookup-key-{{ $key }}-create">
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

                                @if($stepType === 'parameter_result')
                                    <!-- Parameter Result Step Configuration -->
                                    <div class="card bg-light mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0 text-muted">
                                                <i class="mdi mdi-flask"></i> Analyte Selection
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                    <div class="mb-3">
                                        <label for="analyteId" class="form-label"><i class="mdi mdi-flask text-primary"></i> Select Analyte *</label>
                                        <select wire:model.live="analyteId" class="form-select modern-select no-select2 @error('analyteId') is-invalid @enderror" id="analyteId" required>
                                            <option value="">Select Analyte</option>
                                            @foreach($analytes as $analyte)
                                                <option value="{{ $analyte->id }}">{{ $analyte->name }} ({{ $analyte->code }})</option>
                                            @endforeach
                                        </select>
                                        @error('analyteId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                        </div>
                                    </div>
                                @endif
        
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description" rows="2"></textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
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
    <div class="modal fade show d-block formula-step-modal" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
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
                    
                    <form wire:submit.prevent="updateStep">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editStepNumber" class="form-label">Step Number *</label>
                                    <input type="number" wire:model="stepNumber" class="form-control @error('stepNumber') is-invalid @enderror" id="editStepNumber" required min="1">
                                    @error('stepNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label d-block"><i class="mdi mdi-format-list-bulleted-type text-primary"></i> Step Type *</label>
                                    <div class="step-type-picker @error('stepType') is-invalid @enderror" id="editStepType">
                                        @foreach($this->stepTypeOptions as $value => $label)
                                            <label class="step-type-option {{ $stepType === $value ? 'active' : '' }}">
                                                <input type="radio"
                                                       wire:model.live="stepType"
                                                       value="{{ $value }}"
                                                       class="step-type-option-input"
                                                       name="edit_step_type">
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('stepType') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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

                        <div class="formula-step-modal-scroll">
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
                                        <select wire:model.live="lookupTableId" class="form-select modern-select no-select2 @error('lookupTableId') is-invalid @enderror" id="editLookupTableId" required>
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
                                            <div class="alert alert-{{ $selectedTable->lookup_type === 'range_based' ? 'warning' : 'info' }}">
                                                <h6 class="alert-heading">
                                                    <i class="mdi mdi-information"></i> Table Information
                                                </h6>
                                                <div class="mb-2">
                                                    <strong>Type:</strong>
                                                    @if($selectedTable->lookup_type === 'range_based')
                                                        <span class="badge badge-warning">
                                                            <i class="mdi mdi-chart-line"></i> Range-Based
                                                        </span>
                                                        <br><small class="text-muted">Variable: {{ $selectedTable->range_variable_name }}</small>
                                                    @else
                                                        <span class="badge badge-primary">
                                                            <i class="mdi mdi-key"></i> Key-Value Comparison
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>{{ $selectedTable->lookup_type === 'range_based' ? 'Range Keys:' : 'Key Columns:' }}</strong>
                                                        @foreach($selectedTable->key_columns as $column)
                                                            <span class="badge badge-secondary me-1">{{ $column }}</span>
                                                        @endforeach
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Value Column:</strong>
                                                        <span class="badge badge-primary">{{ $selectedTable->value_column }}</span>
                                                        @if($selectedTable->value_interpretation_column)
                                                            <br><small>+ {{ $selectedTable->value_interpretation_column }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($selectedTable && $selectedTable->lookup_type === 'range_based')
                                            <!-- Range-Based Configuration -->
                                            <div class="alert alert-warning mb-3">
                                                <i class="mdi mdi-information"></i>
                                                <strong>Range-Based Lookup:</strong> Select which variable to check against the ranges.
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Variable to Check *</label>
                                                <select wire:model="lookupConfig.range_variable" class="form-select modern-select no-select2">
                                                    <option value="">Select Variable...</option>
                                                    @foreach($availableVariables as $varName => $varData)
                                                        <option value="{{ $varName }}">{{ $varData['label'] }} ({{ $varName }})</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">
                                                    Select which formula variable to check against ranges in "{{ $selectedTable->name }}"
                                                    <br>(Expected range variable: <code>{{ $selectedTable->range_variable_name }}</code>)
                                                </small>
                                            </div>
                                            
                                            @if($selectedTable->value_interpretation_column)
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               wire:model="lookupConfig.return_interpretation" 
                                                               class="form-check-input" 
                                                               id="editReturnInterpretation">
                                                        <label class="form-check-label" for="editReturnInterpretation">
                                                            Return Interpretation Text
                                                            <span class="badge badge-info">{{ $selectedTable->value_interpretation_column }}</span>
                                                        </label>
                                                    </div>
                                                    <small class="text-muted">
                                                        If checked, returns the text interpretation instead of the numeric value
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <!-- Preview -->
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="text-muted">Preview</h6>
                                                    <code>
                                                        IF {{ $lookupConfig['range_variable'] ?? 'variable' }} is in range → 
                                                        Return {{ ($lookupConfig['return_interpretation'] ?? false) ? 'interpretation' : 'value' }}
                                                    </code>
                                                </div>
                                            </div>
                                        @elseif(!empty($lookupConfig))
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
                                                                                   class="form-control lookup-key-input" 
                                                                                   id="lookup-key-{{ $key }}-edit"
                                                                                   data-key="{{ $key }}"
                                                                                   placeholder="Expression or value (e.g., temperature, {{ $key }})">
                                                                        </div>
                                                                        <div class="form-text">Use variable names or direct values</div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label small text-muted">Quick Select</label>
                                                                        <select class="form-select form-select-sm modern-select-sm quick-select-variable" 
                                                                                data-target-input="lookup-key-{{ $key }}-edit">
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

                        @if($stepType === 'parameter_result')
                            <!-- Parameter Result Step Configuration -->
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0 text-muted">
                                        <i class="mdi mdi-flask"></i> Analyte Selection
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="editAnalyteId" class="form-label">Select Analyte *</label>
                                        <select wire:model.live="analyteId" class="form-select modern-select no-select2 @error('analyteId') is-invalid @enderror" id="editAnalyteId" required>
                                            <option value="">Select Analyte</option>
                                            @foreach($analytes as $analyte)
                                                <option value="{{ $analyte->id }}">{{ $analyte->name }} ({{ $analyte->code }})</option>
                                            @endforeach
                                        </select>
                                        @error('analyteId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="editDescription" rows="2"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
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

<!-- Create Mandatory Field Modal -->
@if($showCreateFieldModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-plus"></i>
                        Create Mandatory Field
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showCreateFieldModal', false)"></button>
                </div>
                <div class="modal-body">
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

                    <form wire:submit.prevent="createMandatoryField">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fieldLabel" class="form-label">Label *</label>
                                    <input type="text" wire:model="fieldLabel" class="form-control @error('fieldLabel') is-invalid @enderror" id="fieldLabel" required>
                                    @error('fieldLabel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fieldValueName" class="form-label">Value Name *</label>
                                    <input type="text" wire:model="fieldValueName" class="form-control @error('fieldValueName') is-invalid @enderror" id="fieldValueName" required>
                                    @error('fieldValueName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Used as key when storing field values (e.g., equipment_id, test_date)</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fieldType" class="form-label">Field Type *</label>
                                    <select wire:model.live="fieldType" class="form-select modern-select no-select2 @error('fieldType') is-invalid @enderror" id="fieldType" required>
                                        <option value="">Select Field Type</option>
                                        <option value="input">Text Input</option>
                                        <option value="datetime">Date & Time</option>
                                        <option value="date">Date</option>
                                        <option value="dataset_related">Dataset Related</option>
                                    </select>
                                    @error('fieldType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fieldOrder" class="form-label">Order</label>
                                    <input type="number" wire:model="fieldOrder" class="form-control @error('fieldOrder') is-invalid @enderror" id="fieldOrder" min="1">
                                    @error('fieldOrder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        @if($fieldType === 'dataset_related')
                            <div class="mb-3">
                                <label for="fieldModelTiedTo" class="form-label">Dataset Model *</label>
                                <select wire:model="fieldModelTiedTo" class="form-select modern-select no-select2 @error('fieldModelTiedTo') is-invalid @enderror" id="fieldModelTiedTo" required>
                                    <option value="">Select Dataset Model</option>
                                    <option value="equipments">Equipments</option>
                                    <option value="users">Users</option>
                                    <option value="methods">Methods</option>
                                </select>
                                @error('fieldModelTiedTo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="fieldHelpText" class="form-label">Help Text</label>
                            <textarea wire:model="fieldHelpText" class="form-control @error('fieldHelpText') is-invalid @enderror" id="fieldHelpText" rows="2" placeholder="Optional help text to guide users"></textarea>
                            @error('fieldHelpText') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" wire:model="fieldIsRequired" class="form-check-input" id="fieldIsRequired">
                                <label class="form-check-label" for="fieldIsRequired">
                                    This field is required
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showCreateFieldModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="createMandatoryField">Create Field</button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Edit Mandatory Field Modal -->
@if($showEditFieldModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i>
                        Edit Mandatory Field
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showEditFieldModal', false)"></button>
                </div>
                <div class="modal-body">
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

                    <form wire:submit.prevent="updateMandatoryField">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editFieldLabel" class="form-label">Label *</label>
                                    <input type="text" wire:model="fieldLabel" class="form-control @error('fieldLabel') is-invalid @enderror" id="editFieldLabel" required>
                                    @error('fieldLabel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editFieldValueName" class="form-label">Value Name *</label>
                                    <input type="text" wire:model="fieldValueName" class="form-control @error('fieldValueName') is-invalid @enderror" id="editFieldValueName" required>
                                    @error('fieldValueName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Used as key when storing field values</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editFieldType" class="form-label">Field Type *</label>
                                    <select wire:model.live="fieldType" class="form-select modern-select no-select2 @error('fieldType') is-invalid @enderror" id="editFieldType" required>
                                        <option value="">Select Field Type</option>
                                        <option value="input">Text Input</option>
                                        <option value="datetime">Date & Time</option>
                                        <option value="date">Date</option>
                                        <option value="dataset_related">Dataset Related</option>
                                    </select>
                                    @error('fieldType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editFieldOrder" class="form-label">Order</label>
                                    <input type="number" wire:model="fieldOrder" class="form-control @error('fieldOrder') is-invalid @enderror" id="editFieldOrder" min="1">
                                    @error('fieldOrder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        @if($fieldType === 'dataset_related')
                            <div class="mb-3">
                                <label for="editFieldModelTiedTo" class="form-label">Dataset Model *</label>
                                <select wire:model="fieldModelTiedTo" class="form-select modern-select no-select2 @error('fieldModelTiedTo') is-invalid @enderror" id="editFieldModelTiedTo" required>
                                    <option value="">Select Dataset Model</option>
                                    <option value="equipments">Equipments</option>
                                    <option value="users">Users</option>
                                    <option value="methods">Methods</option>
                                </select>
                                @error('fieldModelTiedTo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="editFieldHelpText" class="form-label">Help Text</label>
                            <textarea wire:model="fieldHelpText" class="form-control @error('fieldHelpText') is-invalid @enderror" id="editFieldHelpText" rows="2" placeholder="Optional help text to guide users"></textarea>
                            @error('fieldHelpText') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" wire:model="fieldIsRequired" class="form-check-input" id="editFieldIsRequired">
                                <label class="form-check-label" for="editFieldIsRequired">
                                    This field is required
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showEditFieldModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="updateMandatoryField">Update Field</button>
                </div>
            </div>
        </div>
    </div>
@endif

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

    /* Step modals: keep header fields outside scroll so native select lists are not clipped */
    .formula-step-modal.modal {
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
</div>