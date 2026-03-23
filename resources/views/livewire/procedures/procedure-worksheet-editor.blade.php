<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-format-list-numbered text-primary"></i>
                                Procedure Steps: {{ $worksheet->name }}
                            </h2>
                            <p class="text-muted mb-0">{{ $worksheet->description }}</p>
                        </div>
                        <div>
                            <button wire:click="showImportModalInit" class="btn btn-outline-primary mr-2">
                                <i class="mdi mdi-download"></i> Import Data
                            </button>
                            <button wire:click="create" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Step
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($toastMessage)
        <div class="position-fixed" style="top: 80px; right: 20px; z-index: 2050;">
            <div class="alert alert-{{ $toastType }} alert-dismissible fade show shadow-sm mb-2" role="alert">
                <i class="mdi mdi-information-outline"></i> {{ $toastMessage }}
                <button type="button" class="close" aria-label="Close" wire:click="$set('toastMessage', '')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Message Alert -->
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Content -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'steps' ? 'active' : '' }}"
                               id="steps-tab"
                               href="#"
                               wire:click.prevent="$set('activeTab', 'steps')"
                               role="tab"
                               aria-controls="steps-tab-pane"
                               aria-selected="{{ $activeTab === 'steps' ? 'true' : 'false' }}">
                                Steps
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'config' ? 'active' : '' }}"
                               id="configurable-fields-tab"
                               href="#"
                               wire:click.prevent="$set('activeTab', 'config')"
                               role="tab"
                               aria-controls="configurable-fields-tab-pane"
                               aria-selected="{{ $activeTab === 'config' ? 'true' : 'false' }}">
                                Configurable Fields
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'document_control' ? 'active' : '' }}"
                               id="document-control-tab"
                               href="#"
                               wire:click.prevent="$set('activeTab', 'document_control')"
                               role="tab"
                               aria-controls="document-control-tab-pane"
                               aria-selected="{{ $activeTab === 'document_control' ? 'true' : 'false' }}">
                                Document Control
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'test_kit' ? 'active' : '' }}"
                               id="test-kit-fields-tab"
                               href="#"
                               wire:click.prevent="$set('activeTab', 'test_kit')"
                               role="tab"
                               aria-controls="test-kit-fields-tab-pane"
                               aria-selected="{{ $activeTab === 'test_kit' ? 'true' : 'false' }}">
                                Test Kit Fields
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade {{ $activeTab === 'steps' ? 'show active' : '' }}" id="steps-tab-pane" role="tabpanel" aria-labelledby="steps-tab">
                            <div class="alert alert-info py-2 px-3 mb-3 rounded" style="font-size:0.85rem;">
                                <i class="mdi mdi-information-outline mr-1"></i>
                                <strong>Tip:</strong> When entering or importing step values in the active worksheet, the system will automatically save the logged-in user as the analyst for those steps.
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="col-md-4">
                                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search steps...">
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0 me-2 text-muted">Show:</label>
                                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="75">75</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr>
                                            <th style="width: 50px;">Order</th>
                                            <th>Step</th>
                                            <th>Default Measurands</th>
                                            <th>Default Equipment</th>
                                            <th>Default Analyst</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-steps">
                                        @forelse($steps as $stepItem)
                                            <tr class="sortable-row" data-step-id="{{ $stepItem->id }}">
                                                <td class="drag-handle text-center">
                                                    <i class="mdi mdi-drag-vertical text-muted" style="cursor: move; font-size: 18px;"></i>
                                                    <span class="text-muted small ms-1">{{ $stepItem->order }}</span>
                                                </td>
                                                <td>{{ $stepItem->step }}</td>
                                                <td>
                                                    @if($stepItem->measurands && $stepItem->measurands->count() > 0)
                                                        <div class="d-flex flex-wrap">
                                                            @foreach($stepItem->measurands as $measurand)
                                                                <span class="badge badge-info p-2 mr-1 mb-1">{{ $measurand->name }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $equipmentDisplay = $stepItem->equipment
                                                            ? $stepItem->equipment
                                                                ->map(function ($equipment) {
                                                                    $number = $equipment->equipment_number ?? null;
                                                                    return $number
                                                                        ? "{$equipment->name} ({$number})"
                                                                        : $equipment->name;
                                                                })
                                                                ->filter()
                                                                ->join(', ')
                                                            : '';
                                                    @endphp
                                                    {{ $equipmentDisplay !== '' ? $equipmentDisplay : '-' }}
                                                </td>
                                                <td>
                                                    @php
                                                        $analystNames = $stepItem->analysts?->pluck('name')->filter()->join(', ');
                                                    @endphp
                                                    {{ $analystNames !== '' ? $analystNames : '-' }}
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $stepItem->is_active ? 'success' : 'secondary' }}">
                                                        {{ $stepItem->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="edit({{ $stepItem->id }})" class="btn btn-sm btn-outline-primary mr-2" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="confirmDelete({{ $stepItem->id }})" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">No steps found. Add your first step!</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <span class="text-muted">
                                        Showing {{ $steps->firstItem() ?? 0 }} to {{ $steps->lastItem() ?? 0 }} of {{ $steps->total() }} entries
                                    </span>
                                </div>
                                <div>
                                    {{ $steps->links() }}
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'config' ? 'show active' : '' }}" id="configurable-fields-tab-pane" role="tabpanel" aria-labelledby="configurable-fields-tab">
                            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-text-box-check text-primary"></i>
                                                Configurable Procedure Fields
                                            </h5>
                                            <p class="text-muted small mb-0">Define dynamic fields like Date Recorded or Temperature for this procedure.</p>
                                        </div>
                                        <button wire:click="showCreateConfigFieldModalInit" class="btn btn-primary">
                                            <i class="mdi mdi-plus"></i> Add Field
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row mb-3">
                                        <div class="col-md-10">
                                            <input type="text" wire:model.live="configFieldSearch" class="form-control" placeholder="Search by label or value name...">
                                        </div>
                                        <div class="col-md-2">
                                            <button wire:click="clearConfigFieldSearch" class="btn btn-outline-secondary w-100">
                                                <i class="mdi mdi-refresh"></i> Clear
                                            </button>
                                        </div>
                                    </div>

                                    @if(count($configFields) > 0)
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
                                                        <th style="width: 200px;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sortable-config-fields">
                                                    @foreach($configFields as $field)
                                                        <tr class="sortable-row" data-config-field-id="{{ $field['id'] }}">
                                                            <td class="drag-handle text-center">
                                                                <i class="mdi mdi-drag-vertical text-muted" style="cursor: move; font-size: 18px;"></i>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-secondary">{{ $field['order'] }}</span>
                                                            </td>
                                                            <td>
                                                                <strong>{{ $field['label'] }}</strong>
                                                                @if($field['help_text'])
                                                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($field['help_text'], 50) }}</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-info">{{ ucfirst($field['field_type']) }}</span>
                                                                @if(in_array($field['field_type'] ?? '', ['dataset', 'dataset_multiselect']) && !empty($field['model_tied_to']))
                                                                    <br><small class="text-muted">{{ \App\Models\Procedures\ProcedureConfigField::getDatasetModels()[$field['model_tied_to']] ?? $field['model_tied_to'] }}</small>
                                                                @endif
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
                                                                <div class="btn-group" role="group">
                                                                    <button wire:click="showEditConfigFieldModalInit({{ $field['id'] }})"
                                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                                        <i class="mdi mdi-pencil"></i>
                                                                    </button>
                                                                    <button wire:click="showDeleteConfigFieldModal({{ $field['id'] }})"
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
                                            <h5 class="text-muted">No configurable fields defined</h5>
                                            <p class="text-muted">Add fields like Date Recorded or Temperature to capture additional metadata.</p>
                                            <button wire:click="showCreateConfigFieldModalInit" class="btn btn-primary">
                                                <i class="mdi mdi-plus"></i> Add First Field
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($showCreateConfigFieldModal || $showEditConfigFieldModal)
                                <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    {{ $showEditConfigFieldModal ? 'Edit Configurable Field' : 'Add Configurable Field' }}
                                                </h5>
                                                <button type="button" class="btn-close" wire:click="closeConfigFieldModal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form wire:submit.prevent="{{ $showEditConfigFieldModal ? 'updateConfigField' : 'createConfigField' }}">
                                                    <div class="mb-3">
                                                        <label class="form-label">Label <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="configFieldLabel" class="form-control">
                                                        @error('configFieldLabel') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Value Name (key) <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="configFieldValueName" class="form-control">
                                                        @error('configFieldValueName') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Field Type <span class="text-danger">*</span></label>
                                                        <select wire:model.live="configFieldType" class="form-control modern-select">
                                                            <option value="input">Text</option>
                                                            <option value="number">Number</option>
                                                            <option value="checkbox">Checkbox</option>
                                                            <option value="textarea">Textarea</option>
                                                            <option value="date">Date</option>
                                                            <option value="datetime">Date &amp; Time</option>
                                                            <option value="dataset">Dataset (select from list)</option>
                                                            <option value="dataset_multiselect">Dataset (multi-select)</option>
                                                        </select>
                                                        @error('configFieldType') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                    @if($configFieldType === 'dataset' || $configFieldType === 'dataset_multiselect')
                                                    <div class="mb-3">
                                                        <label class="form-label">Dataset source <span class="text-danger">*</span></label>
                                                        <select wire:model="configFieldModelTiedTo" class="form-control modern-select">
                                                            <option value="">Select...</option>
                                                            @foreach(\App\Models\Procedures\ProcedureConfigField::getDatasetModels() as $value => $label)
                                                                <option value="{{ $value }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('configFieldModelTiedTo') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                    @endif
                                                    <div class="mb-3">
                                                        <label class="form-label">Order</label>
                                                        <input type="number" wire:model="configFieldOrder" class="form-control" min="1">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Help Text</label>
                                                        <textarea wire:model="configFieldHelpText" class="form-control" rows="2"></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input type="checkbox" wire:model="configFieldIsRequired" class="form-check-input" id="configFieldIsRequired">
                                                            <label class="form-check-label" for="configFieldIsRequired">Required</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <button type="button" class="btn btn-secondary mr-2" wire:click="closeConfigFieldModal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            {{ $showEditConfigFieldModal ? 'Save Changes' : 'Add Field' }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($showDeleteConfigFieldModalOpen)
                                <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Configurable Field</h5>
                                                <button type="button" class="btn-close" wire:click="$set('showDeleteConfigFieldModalOpen', false)"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this configurable field? This action cannot be undone.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteConfigFieldModalOpen', false)">Cancel</button>
                                                <button type="button" class="btn btn-danger" wire:click="deleteConfigField">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'document_control' ? 'show active' : '' }}" id="document-control-tab-pane" role="tabpanel" aria-labelledby="document-control-tab">
                            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                                    <h5 class="mb-0">
                                        <i class="mdi mdi-file-document-outline text-primary"></i>
                                        Document Control
                                    </h5>
                                    <p class="text-muted small mb-0">Worksheet-level document control (e.g. for reports and compliance).</p>
                                </div>
                                <div class="card-body p-4">
                                    <form wire:submit.prevent="saveDocumentControl">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Document Control No</label>
                                                <input type="text" wire:model="documentControlNo" class="form-control" placeholder="e.g. DOC-001">
                                                @error('documentControlNo') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Revision</label>
                                                <input type="text" wire:model="revision" class="form-control" placeholder="e.g. 1.0">
                                                @error('revision') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Issue Date</label>
                                                <input type="date" wire:model="issueDate" class="form-control">
                                                @error('issueDate') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="mdi mdi-content-save"></i> Save Document Control
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'test_kit' ? 'show active' : '' }}" id="test-kit-fields-tab-pane" role="tabpanel" aria-labelledby="test-kit-fields-tab">
                            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-table text-primary"></i>
                                                Test Kit Table Columns
                                            </h5>
                                            <p class="text-muted small mb-0">Define columns for the test kit table for this procedure.</p>
                                        </div>
                                        <button wire:click="showCreateTestKitColumnModalInit" class="btn btn-primary">
                                            <i class="mdi mdi-plus"></i> Add Column
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row mb-3">
                                        <div class="col-md-10">
                                            <input type="text" wire:model.live="testKitColumnSearch" class="form-control" placeholder="Search by label, key, or help text...">
                                        </div>
                                        <div class="col-md-2">
                                            <button wire:click="clearTestKitColumnSearch" class="btn btn-outline-secondary w-100">
                                                <i class="mdi mdi-refresh"></i> Clear
                                            </button>
                                        </div>
                                    </div>

                                    @if(count($testKitColumns) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                                    <tr>
                                                        <th style="width: 40px;">
                                                            <i class="mdi mdi-drag text-muted"></i>
                                                        </th>
                                                        <th style="width: 60px;">Order</th>
                                                        <th>Label</th>
                                                        <th>Key</th>
                                                        <th>Type</th>
                                                        <th>Required</th>
                                                        <th style="width: 200px;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sortable-test-kit-columns">
                                                    @foreach($testKitColumns as $column)
                                                        <tr class="sortable-row" data-test-kit-column-id="{{ $column['id'] }}">
                                                            <td class="drag-handle text-center">
                                                                <i class="mdi mdi-drag-vertical text-muted" style="cursor: move; font-size: 18px;"></i>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-secondary">{{ $column['order'] }}</span>
                                                            </td>
                                                            <td>
                                                                <strong>{{ $column['label'] }}</strong>
                                                                @if($column['help_text'])
                                                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($column['help_text'], 50) }}</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <code>{{ $column['key'] }}</code>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-info">{{ ucfirst($column['type']) }}</span>
                                                            </td>
                                                            <td>
                                                                @if($column['is_required'])
                                                                    <span class="badge badge-danger">Required</span>
                                                                @else
                                                                    <span class="badge badge-secondary">Optional</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <button wire:click="showEditTestKitColumnModalInit({{ $column['id'] }})"
                                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                                        <i class="mdi mdi-pencil"></i>
                                                                    </button>
                                                                    <button wire:click="showDeleteTestKitColumnModal({{ $column['id'] }})"
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
                                            <i class="mdi mdi-table-large fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No test kit columns defined</h5>
                                            <p class="text-muted">Add columns to structure your test kit data for this procedure.</p>
                                            <button wire:click="showCreateTestKitColumnModalInit" class="btn btn-primary">
                                                <i class="mdi mdi-plus"></i> Add First Column
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($showCreateTestKitColumnModal || $showEditTestKitColumnModal)
                                <div class="modal fade show d-block"
                                     tabindex="-1"
                                     style="background-color: rgba(0,0,0,0.5);"
                                     wire:click.self="closeTestKitColumnModal">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    {{ $showEditTestKitColumnModal ? 'Edit Test Kit Column' : 'Add Test Kit Column' }}
                                                </h5>
                                                <button type="button" class="btn-close" wire:click="closeTestKitColumnModal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form wire:submit.prevent="{{ $showEditTestKitColumnModal ? 'updateTestKitColumn' : 'createTestKitColumn' }}">
                                                    <div class="mb-3">
                                                        <label class="form-label">Label <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="testKitColumnLabel" class="form-control">
                                                        @error('testKitColumnLabel') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Key <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="testKitColumnKey" class="form-control">
                                                        @error('testKitColumnKey') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                                        <select wire:model="testKitColumnType" class="form-control modern-select">
                                                            <option value="string">Text</option>
                                                            <option value="number">Number</option>
                                                            <option value="date">Date</option>
                                                            <option value="boolean">Yes/No</option>
                                                        </select>
                                                        @error('testKitColumnType') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Order</label>
                                                        <input type="number" wire:model="testKitColumnOrder" class="form-control" min="1">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Help Text</label>
                                                        <textarea wire:model="testKitColumnHelpText" class="form-control" rows="2"></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input type="checkbox" wire:model="testKitColumnIsRequired" class="form-check-input" id="testKitColumnIsRequired">
                                                            <label class="form-check-label" for="testKitColumnIsRequired">Required</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <button type="button" class="btn btn-secondary mr-2" wire:click="closeTestKitColumnModal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            {{ $showEditTestKitColumnModal ? 'Save Changes' : 'Add Column' }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($showDeleteTestKitColumnModalOpen)
                                <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Test Kit Column</h5>
                                                <button type="button" class="btn-close" wire:click="$set('showDeleteTestKitColumnModalOpen', false)"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this test kit column? This action cannot be undone.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteTestKitColumnModalOpen', false)">Cancel</button>
                                                <button type="button" class="btn btn-danger" wire:click="deleteTestKitColumn">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Worksheet Data</h5>
                        <button type="button" class="btn-close" wire:click="cancelImport"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="importData">
                            <div class="mb-4">
                                <label class="form-label">Source Worksheet <span class="text-danger">*</span></label>
                                @if($selectedImportWorksheetId && $selectedImportWorksheet = App\Models\Procedures\ProcedureWorksheet::find($selectedImportWorksheetId))
                                    <div class="position-relative">
                                        <input type="text" class="form-control" value="{{ $selectedImportWorksheet->name }}" readonly style="padding-right: 30px;">
                                        <i class="mdi mdi-close text-danger cursor-pointer" 
                                           wire:click="$set('selectedImportWorksheetId', null)"
                                           style="position: absolute; top: 10px; right: 10px; z-index: 10;"></i>
                                    </div>
                                @else
                                    <div class="position-relative">
                                        <input type="text" wire:model.live.debounce.300ms="importWorksheetSearch" class="form-control" placeholder="Search worksheet by name...">
                                        @if(strlen($importWorksheetSearch) > 1 && count($importableWorksheets) > 0)
                                            <div class="position-absolute w-100 bg-white border shadow rounded mt-1" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                                @foreach($importableWorksheets as $ws)
                                                    <div class="p-2 border-bottom cursor-pointer hover-bg-light" wire:click="$set('selectedImportWorksheetId', {{ $ws->id }})">
                                                        {{ $ws->name }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif(strlen($importWorksheetSearch) > 1)
                                            <div class="position-absolute w-100 bg-white border shadow rounded mt-1 p-2 text-muted" style="z-index: 1000;">
                                                No worksheets found matching "{{ $importWorksheetSearch }}".
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @error('selectedImportWorksheetId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block text-muted text-uppercase small font-weight-bold">Select Data to Import</label>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" wire:model="importTypeSteps" id="importTypeSteps">
                                    <label class="form-check-label font-weight-bold" for="importTypeSteps">
                                        Procedure Steps
                                    </label>
                                    <div class="text-muted small">Imports all procedure steps including measurands, equipment, and analyst defaults.</div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" wire:model="importTypeConfig" id="importTypeConfig">
                                    <label class="form-check-label font-weight-bold" for="importTypeConfig">
                                        Configurable Fields
                                    </label>
                                    <div class="text-muted small">Imports dynamic fields like Date Recorded or Temperature.</div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" wire:model="importTypeTestKit" id="importTypeTestKit">
                                    <label class="form-check-label font-weight-bold" for="importTypeTestKit">
                                        Test Kit Columns
                                    </label>
                                    <div class="text-muted small">Imports configuration for the test kit table.</div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info py-2 mb-0" style="font-size: 0.85rem;">
                                <i class="mdi mdi-information-outline mr-1"></i> Imported data will be appended.
                            </div>

                            <div class="text-right mt-4">
                                <button type="button" class="btn btn-secondary mr-2" wire:click="cancelImport">Cancel</button>
                                <button type="submit" class="btn btn-primary" {{ !$selectedImportWorksheetId ? 'disabled' : '' }}>
                                    <i class="mdi mdi-download mr-1"></i> Import Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingStepId ? 'Edit' : 'Add' }} Step</h5>
                        <button type="button" class="btn-close" wire:click="cancel"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label class="form-label">Step Description <span class="text-danger">*</span></label>
                                <input type="text" wire:model="step" class="form-control">
                                @error('step') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Value Type <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model.live="value_type" class="form-control modern-select">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="time">Time</option>
                                        <option value="datetime">Date &amp; Time</option>
                                        <option value="date">Date</option>
                                    </select>
                                    @error('value_type') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    @php
                                        $defaultInputType = match($value_type) {
                                            'number' => 'number',
                                            'date' => 'date',
                                            'time' => 'time',
                                            'datetime' => 'datetime-local',
                                            default => 'text',
                                        };
                                    @endphp
                                    <label class="form-label">Default Value</label>
                                    <input type="{{ $defaultInputType }}"
                                           wire:model="default_value"
                                           class="form-control"
                                           @if($value_type === 'number') step="any" @endif
                                    >
                                    @error('default_value') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Equipment</label>
                                    <div class="position-relative" wire:click.outside="$set('showEquipmentDropdown', false)">
                                        @if($selectedEquipment)
                                            @php
                                                $equipmentModel = $selectedEquipment instanceof \Illuminate\Support\Collection
                                                    ? $selectedEquipment->first()
                                                    : $selectedEquipment;
                                            @endphp
                                            @if($equipmentModel)
                                            <div class="position-relative">
                                                <input type="text" class="form-control" value="{{ $equipmentModel->name }} ({{ $equipmentModel->equipment_number }})" readonly style="padding-right: 30px;">
                                                <i class="mdi mdi-close text-danger cursor-pointer" 
                                                   wire:click="$set('default_equipment_id', null)"
                                                   style="position: absolute; top: 10px; right: 10px; z-index: 10;"></i>
                                            </div>
                                            @endif
                                        @else
                                            <input type="text" wire:model.live="equipmentSearch" wire:focus="$set('showEquipmentDropdown', true)" class="form-control" placeholder="Search equipment...">
                                            @if($showEquipmentDropdown && count($equipments) > 0)
                                                <div class="position-absolute w-100 bg-white border shadow rounded mt-1" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                                    @foreach($equipments as $eq)
                                                        <div class="p-2 border-bottom cursor-pointer hover-bg-light" wire:click="selectEquipment({{ $eq->id }})">
                                                            {{ $eq->name }} ({{ $eq->equipment_number }})
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Analyst</label>
                                    <div class="position-relative" wire:click.outside="$set('showAnalystDropdown', false)">
                                        @if($selectedAnalyst)
                                            <div class="position-relative">
                                                <input type="text" class="form-control" value="{{ $selectedAnalyst->name }}" readonly style="padding-right: 30px;">
                                                <i class="mdi mdi-close text-danger cursor-pointer" 
                                                   wire:click="$set('default_analyst_id', null)"
                                                   style="position: absolute; top: 10px; right: 10px; z-index: 10;"></i>
                                            </div>
                                        @else
                                            <input type="text" wire:model.live="analystSearch" wire:focus="$set('showAnalystDropdown', true)" class="form-control" placeholder="Search analyst...">
                                            @if($showAnalystDropdown && count($analysts) > 0)
                                                <div class="position-absolute w-100 bg-white border shadow rounded mt-1" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                                    @foreach($analysts as $user)
                                                        <div class="p-2 border-bottom cursor-pointer hover-bg-light" wire:click="selectAnalyst({{ $user->id }})">
                                                            {{ $user->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3" wire:click.outside="$set('showMeasurandDropdown', false)">
                                <label class="form-label">Default Measurands</label>
                                <div class="position-relative">
                                    <div class="form-control d-flex flex-wrap align-items-center" style="min-height: 38px; cursor: text;" onclick="document.getElementById('measurandSearchInput').focus()">
                                        @foreach($selectedMeasurands as $m)
                                            <span class="badge bg-light text-dark border d-flex align-items-center p-2 mr-2 mb-1" style="border-radius: 4px;">
                                                {{ $m->name }}
                                                <i class="mdi mdi-close ml-2 cursor-pointer text-danger" wire:click.stop="removeMeasurand({{ $m->id }})"></i>
                                            </span>
                                        @endforeach
                                        <input id="measurandSearchInput" type="text" wire:model.live="measurandSearch" wire:focus="$set('showMeasurandDropdown', true)" class="border-0 p-0 m-0" style="outline: none; flex: 1; min-width: 100px; background: transparent;" placeholder="{{ count($selectedMeasurands) > 0 ? '' : 'Search measurands...' }}">
                                    </div>
                                    
                                    @if($showMeasurandDropdown && count($measurands) > 0)
                                        <div class="position-absolute w-100 bg-white border shadow rounded mt-1" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                            @foreach($measurands as $m)
                                                <div class="p-2 border-bottom cursor-pointer hover-bg-light" wire:click="toggleMeasurand({{ $m->id }})">
                                                    {{ $m->name }}
                                                    @if(in_array($m->id, $default_measurand_ids))
                                                        <i class="mdi mdi-check text-success float-end"></i>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if(count($selectedMeasurands) > 0)
                                <div class="mb-3">
                                    @php
                                        $perMeasInputType = match($value_type) {
                                            'number' => 'number',
                                            'date' => 'date',
                                            'time' => 'time',
                                            'datetime' => 'datetime-local',
                                            default => 'text',
                                        };
                                    @endphp
                                    <label class="form-label">
                                        Default Values (per measurand)
                                        <span class="text-muted" style="font-size: 0.85em;">
                                            (leave blank to keep it empty)
                                        </span>
                                    </label>
                                    <div class="row g-2">
                                        @foreach($selectedMeasurands as $m)
                                            <div class="col-md-6">
                                                <label class="form-label small mb-1">{{ $m->name }}</label>
                                                <input
                                                    type="{{ $perMeasInputType }}"
                                                    wire:model="default_measurand_values.{{ $m->id }}"
                                                    class="form-control"
                                                    @if($value_type === 'number') step="any" @endif
                                                >
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="is_active" class="form-check-input" id="isActiveStep">
                                    <label class="form-check-label" for="isActiveStep">Active</label>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="button" class="btn btn-secondary mr-2" wire:click="cancel">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Step</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Step</h5>
                        <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this step? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteStep">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <style>
        .cursor-pointer { cursor: pointer; }
        .hover-bg-light:hover { background-color: #f8f9fa; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            initializeStepSortable();
            initializeConfigFieldSortable();
        });

        document.addEventListener('livewire:updated', () => {
            initializeStepSortable();
            initializeConfigFieldSortable();
        });

        function initializeStepSortable() {
            const sortableElement = document.getElementById('sortable-steps');
            if (sortableElement && typeof Sortable !== 'undefined') {
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
                        var stepIds = Array.from(sortableElement.children).map(function (row) {
                            return parseInt(row.getAttribute('data-step-id'));
                        });
                        if (window.livewire && window.livewire.find) {
                            var component = window.livewire.find(sortableElement.getAttribute('wire:id'));
                            if (component) {
                                component.call('updateStepOrder', stepIds);
                            }
                        }
                    }
                });
            }
        }

        function initializeConfigFieldSortable() {
            const sortableFieldsElement = document.getElementById('sortable-config-fields');
            if (sortableFieldsElement && typeof Sortable !== 'undefined') {
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
                        var fieldIds = Array.from(sortableFieldsElement.children).map(function (row) {
                            return parseInt(row.getAttribute('data-config-field-id'));
                        });
                        if (window.livewire && window.livewire.find) {
                            var component = window.livewire.find(sortableFieldsElement.getAttribute('wire:id'));
                            if (component) {
                                component.call('updateConfigFieldOrder', fieldIds);
                            }
                        }
                    }
                });
            }
        }
    </script>
    
    <style>
        .drag-handle:hover {
            background-color: #f8f9fa;
            cursor: move;
        }
        .sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef;
        }
        .sortable-chosen {
            background-color: #f8f9fa;
        }
        .sortable-drag {
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</div>
