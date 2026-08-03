<div class="container-fluid lab-panel-theme workflow-board-page procedure-editor-page">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="workflow-board-panel">
                <div class="workflow-board-panel-header">
                    <h5>
                        <i class="mdi mdi-format-list-numbered text-primary"></i>
                        Procedure Steps: {{ $worksheet->name }}
                    </h5>
                    <div class="d-flex align-items-center">
                        <button wire:click="showImportModalInit" class="btn btn-sm btn-outline-primary btn-action-sm mr-2">
                            <i class="mdi mdi-download"></i> Import Data
                        </button>
                        <button wire:click="create" class="btn btn-sm btn-primary btn-action-sm">
                            <i class="mdi mdi-plus"></i> Add Step
                        </button>
                    </div>
                </div>
                <div class="workflow-board-panel-body flush-top">
                    <p class="text-muted mb-0">{{ $worksheet->description }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($toastMessage)
        <div class="position-fixed" style="top: 80px; right: 20px; z-index: 2050;">
            <div class="alert alert-{{ $toastType }} alert-dismissible fade show shadow-sm mb-2" role="alert">
                <i class="mdi mdi-information-outline"></i> {{ $toastMessage }}
                <button type="button" class="close" aria-label="Close" wire:click="clearToast">
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
            <div class="workflow-board-panel">
                <div class="workflow-board-panel-body flush-top">
                    <nav class="pw-tabs" role="tablist" aria-label="Procedure configuration">
                        <button type="button"
                                class="pw-tab {{ $activeTab === 'steps' ? 'active' : '' }}"
                                id="steps-tab"
                                wire:click.prevent="setActiveTab('steps')"
                                role="tab"
                                aria-selected="{{ $activeTab === 'steps' ? 'true' : 'false' }}">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span>Steps</span>
                        </button>
                        <button type="button"
                                class="pw-tab {{ $activeTab === 'config' ? 'active' : '' }}"
                                id="configurable-fields-tab"
                                wire:click.prevent="setActiveTab('config')"
                                role="tab"
                                aria-selected="{{ $activeTab === 'config' ? 'true' : 'false' }}">
                            <i class="mdi mdi-tune-variant"></i>
                            <span>Configurable Fields</span>
                        </button>
                        <button type="button"
                                class="pw-tab {{ $activeTab === 'document_control' ? 'active' : '' }}"
                                id="document-control-tab"
                                wire:click.prevent="setActiveTab('document_control')"
                                role="tab"
                                aria-selected="{{ $activeTab === 'document_control' ? 'true' : 'false' }}">
                            <i class="mdi mdi-file-document-outline"></i>
                            <span>Document Control</span>
                        </button>
                        <button type="button"
                                class="pw-tab {{ $activeTab === 'test_kit' ? 'active' : '' }}"
                                id="test-kit-fields-tab"
                                wire:click.prevent="setActiveTab('test_kit')"
                                role="tab"
                                aria-selected="{{ $activeTab === 'test_kit' ? 'true' : 'false' }}">
                            <i class="mdi mdi-table-large"></i>
                            <span>Test Kit Fields</span>
                        </button>
                        <button type="button"
                                class="pw-tab {{ $activeTab === 'layout' ? 'active' : '' }}"
                                id="layout-tab"
                                wire:click.prevent="setActiveTab('layout')"
                                role="tab"
                                aria-selected="{{ $activeTab === 'layout' ? 'true' : 'false' }}">
                            <i class="mdi mdi-view-grid-outline"></i>
                            <span>Layout</span>
                        </button>
                    </nav>

                    <div class="tab-content pw-tab-content">
                        <div class="tab-pane fade {{ $activeTab === 'steps' ? 'show active' : '' }}" id="steps-tab-pane" role="tabpanel" aria-labelledby="steps-tab">
                            <div class="pw-callout">
                                <div class="pw-callout-icon"><i class="mdi mdi-lightbulb-on-outline"></i></div>
                                <div class="pw-callout-text">
                                    <strong>Tip</strong>
                                    <span>When entering or importing step values in the active worksheet, the system saves the logged-in user as the analyst for those steps.</span>
                                </div>
                            </div>

                            <div class="pw-toolbar d-flex flex-wrap align-items-center gap-2">
                                <div class="pw-search-wrap flex-grow-1" style="min-width: 200px;">
                                    <i class="mdi mdi-magnify pw-search-icon"></i>
                                    <input type="text" wire:model.live="search" class="form-control pw-input" placeholder="Search steps...">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="showCreateStepGroupModalInit">
                                    <i class="mdi mdi-folder-plus-outline"></i> Add step group
                                </button>
                            </div>

                            @if(count($stepGroups) > 0)
                            <div class="mb-3 d-flex flex-wrap gap-2">
                                @foreach($stepGroups as $grp)
                                <span class="tag-badge tag-badge--neutral">
                                    {{ $grp['title'] }}
                                    <button type="button" class="btn-tag-remove border-0 bg-transparent p-0 ml-1" wire:click="showEditStepGroupModalInit(@js($grp['id']))" title="Edit group"><i class="mdi mdi-pencil"></i></button>
                                    <button type="button" class="btn-tag-remove border-0 bg-transparent p-0" wire:click="showDeleteStepGroupModal(@js($grp['id']))" title="Delete group"><i class="mdi mdi-delete"></i></button>
                                </span>
                                @endforeach
                            </div>
                            @endif

                            @forelse($stepDisplayBlocks as $blockIndex => $block)
                            <div class="pw-step-group-block mb-4" wire:key="step-block-{{ $blockIndex }}">
                                @if(!empty($block['step_group']))
                                <div class="pw-step-group-block__header">
                                    <i class="mdi mdi-folder-outline"></i>
                                    <div>
                                        <strong>{{ $block['step_group']->title }}</strong>
                                        @if($block['step_group']->description)
                                        <div class="text-muted small">{{ $block['step_group']->description }}</div>
                                        @endif
                                    </div>
                                </div>
                                @elseif($block['type'] === 'scalar' && count($stepDisplayBlocks) > 1)
                                <div class="pw-step-group-block__header pw-step-group-block__header--muted">
                                    <i class="mdi mdi-format-list-bulleted"></i>
                                    <strong>Ungrouped steps</strong>
                                </div>
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover workflow-table mb-0">
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
                                        <tbody class="sortable-steps-group">
                                            @if($block['type'] === 'custom_table')
                                                @include('livewire.procedures.partials.procedure-step-row', ['stepItem' => $block['step']])
                                            @else
                                                @foreach($block['steps'] as $stepItem)
                                                    @include('livewire.procedures.partials.procedure-step-row', ['stepItem' => $stepItem])
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-5 text-muted">
                                <p class="mb-2">No steps found. Add your first step or create optional groups first.</p>
                                <button wire:click="create" class="btn btn-primary btn-sm"><i class="mdi mdi-plus"></i> Add step</button>
                            </div>
                            @endforelse

                            @if($steps->count() > 0)
                            <p class="text-muted small mt-3 mb-0">{{ $steps->count() }} step(s) total</p>
                            @endif
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
                                        <div class="d-flex align-items-center">
                                            <button type="button" wire:click="showCreateConfigSectionModalInit" class="btn btn-outline-primary mr-2">
                                                <i class="mdi mdi-folder-plus-outline"></i> Add section
                                            </button>
                                            <button wire:click="showCreateConfigFieldModalInit" class="btn btn-primary">
                                                <i class="mdi mdi-plus"></i> Add Field
                                            </button>
                                        </div>
                                    </div>
                                </div>
                <div class="workflow-board-panel-body p-4">
                                    @include('livewire.procedures.partials.config-fields-capture-layout', ['showSaveButton' => true])
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

                                    @if(count($configFieldDisplayBlocks) > 0)
                                        @foreach($configFieldDisplayBlocks as $cfgBlockIndex => $cfgBlock)
                                        <div class="pw-step-group-block mb-4" wire:key="cfg-block-{{ $cfgBlockIndex }}">
                                            @if($cfgBlock['section'])
                                            <div class="pw-step-group-block__header d-flex justify-content-between align-items-start">
                                                <div>
                                                    <i class="mdi mdi-folder-outline"></i>
                                                    <strong>{{ $cfgBlock['section']->title }}</strong>
                                                    @if($cfgBlock['section']->description)
                                                    <div class="text-muted small">{{ $cfgBlock['section']->description }}</div>
                                                    @endif
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="showEditConfigSectionModalInit(@js($cfgBlock['section']->id))" title="Edit section"><i class="mdi mdi-pencil"></i></button>
                                                    <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="showDeleteConfigSectionModal(@js($cfgBlock['section']->id))" title="Delete section"><i class="mdi mdi-delete"></i></button>
                                                </div>
                                            </div>
                                            @else
                                            <div class="pw-step-group-block__header pw-step-group-block__header--muted">
                                                <i class="mdi mdi-form-select"></i>
                                                <strong>Fields without section</strong>
                                            </div>
                                            @endif
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover workflow-table mb-0">
                                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                                        <tr>
                                                            <th style="width: 40px;"></th>
                                                            <th style="width: 60px;">Order</th>
                                                            <th>Label</th>
                                                            <th>Field Type</th>
                                                            <th>Value Name</th>
                                                            <th>Required</th>
                                                            <th style="width: 200px;">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sortable-config-fields-{{ $cfgBlockIndex }}">
                                                        @foreach($cfgBlock['fields'] as $field)
                                                        <tr class="sortable-row" data-config-field-id="{{ $field['id'] }}">
                                                            <td class="drag-handle text-center"><i class="mdi mdi-drag-vertical text-muted"></i></td>
                                                            <td><span class="badge badge-secondary">{{ $field['order'] }}</span></td>
                                                            <td>
                                                                <strong>{{ $field['label'] }}</strong>
                                                                @if($field['help_text'])
                                                                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($field['help_text'], 50) }}</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-info">{{ \App\Models\Procedures\ProcedureConfigField::getFieldTypes()[$field['field_type']] ?? $field['field_type'] }}</span>
                                                                @if($field['field_type'] === 'sample_select' && !empty($field['model_tied_to']))
                                                                <br><small class="text-muted">{{ $field['model_tied_to'] }}</small>
                                                                @endif
                                                            </td>
                                                            <td><code>{{ $field['field_value_name'] }}</code></td>
                                                            <td>
                                                                @if($field['is_required'])
                                                                <span class="badge badge-danger">Required</span>
                                                                @else
                                                                <span class="badge badge-secondary">Optional</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-wrap">
                                                                    <button wire:click="showEditConfigFieldModalInit(@js($field['id']))" class="btn btn-sm rm-act-btn rm-act-btn--edit" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                                                    <button wire:click="showDeleteConfigFieldModal(@js($field['id']))" class="btn btn-sm rm-act-btn rm-act-btn--delete" title="Delete"><i class="mdi mdi-delete"></i></button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endforeach
                                    @elseif(count($configFieldSections) > 0)
                                        <div class="alert alert-info">Sections exist but have no fields yet. Add fields and assign them to a section.</div>
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
                                <div class="pw-modal show d-block" tabindex="-1" wire:click.self="closeConfigFieldModal">
                                    <div class="modal-dialog modal-dialog-scrollable pw-modal-dialog" wire:click.stop>
                                        <div class="modal-content pw-modal-content">
                                            <div class="modal-header pw-modal-header">
                                                <div>
                                                    <h5 class="modal-title mb-1">
                                                        {{ $showEditConfigFieldModal ? 'Edit Configurable Field' : 'Add Configurable Field' }}
                                                    </h5>
                                                    <p class="text-muted small mb-0">Dynamic fields captured on the procedure worksheet.</p>
                                                </div>
                                                <button type="button" class="close pw-modal-close" wire:click="closeConfigFieldModal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body pw-modal-body">
                                                @include('livewire.procedures.partials.config-fields-capture-layout', ['showSaveButton' => false])
                                                <form id="config-field-form" wire:submit.prevent="{{ $showEditConfigFieldModal ? 'updateConfigField' : 'createConfigField' }}" class="pw-form">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Label <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="configFieldLabel" class="form-control pw-input">
                                                        @error('configFieldLabel') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Value Name (key) <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="configFieldValueName" class="form-control pw-input">
                                                        @error('configFieldValueName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Section <span class="text-muted fw-normal">(optional)</span></label>
                                                        @include('livewire.procedures.partials.config-search-select', [
                                                            'options' => $this->configSectionSelectOptions,
                                                            'wireModel' => 'configFieldSectionId',
                                                            'placeholder' => 'Select section...',
                                                            'emptyMessage' => 'No section matches your search.',
                                                            'inputRef' => 'sectionSearch',
                                                            'pickerKey' => 'config-field-section-' . count($configFieldSections) . '-' . ($showEditConfigFieldModal ? 'edit-' . ($editingConfigField?->id ?? '0') : 'create') . '-' . ($configFieldSectionId ?? 'none'),
                                                        ])
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Field Type <span class="text-danger">*</span></label>
                                                        @php
                                                            $configFieldTypeOptions = collect(\App\Models\Procedures\ProcedureConfigField::getFieldTypes())
                                                                ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
                                                                ->values()
                                                                ->all();
                                                        @endphp
                                                        @include('livewire.procedures.partials.config-search-select', [
                                                            'options' => $configFieldTypeOptions,
                                                            'wireModel' => 'configFieldType',
                                                            'placeholder' => 'Search field type...',
                                                            'emptyMessage' => 'No field type matches your search.',
                                                            'inputRef' => 'typeSearch',
                                                            'live' => true,
                                                            'pickerKey' => 'config-field-type-' . ($showEditConfigFieldModal ? 'edit-' . ($editingConfigField?->id ?? '0') : 'create') . '-' . $configFieldType,
                                                        ])
                                                        @error('configFieldType') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    @if(in_array($configFieldType, ['customer_select', 'lab_select'], true))
                                                    <div class="alert alert-light border small mb-3">
                                                        @if($configFieldType === 'customer_select')
                                                        <strong>Customer (from sample)</strong> — During capture, shows the CRM customer from the sample’s batch. Read-only; no extra configuration.
                                                        @else
                                                        <strong>Lab (from sample)</strong> — During capture, shows the lab linked on the sample record. Read-only; no extra configuration.
                                                        @endif
                                                    </div>
                                                    @endif
                                                    @if($configFieldType === 'sample_select')
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Column type <span class="text-danger">*</span></label>
                                                        <select class="form-control pw-input" wire:model.live="configFieldSampleDisplayMode">
                                                            <option value="direct">Normal column (direct)</option>
                                                            <option value="foreign_key">Foreign key → related table column</option>
                                                        </select>
                                                        @error('configFieldSampleDisplayMode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Sample column <span class="text-danger">*</span></label>
                                                        @include('livewire.procedures.partials.config-search-select', [
                                                            'options' => $this->configFieldSampleColumnOptions,
                                                            'wireModel' => 'configFieldSampleColumn',
                                                            'placeholder' => 'Search sample column...',
                                                            'emptyMessage' => 'No sample column matches your search.',
                                                            'inputRef' => 'sampleColumnSearch',
                                                            'live' => true,
                                                            'pickerKey' => 'config-field-sample-column-' . $configFieldSampleDisplayMode . '-' . $configFieldSampleColumn,
                                                        ])
                                                        @error('configFieldSampleColumn') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    @if($configFieldSampleDisplayMode === 'foreign_key' && $configFieldSampleColumn !== '' && count($this->configFieldSampleRelationOptions) > 0)
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">
                                                            Related display column
                                                            <span class="text-muted fw-normal">(for {{ \App\Services\Procedures\ProcedureConfigFieldSampleCatalog::foreignKeyLabel($configFieldSampleColumn) }})</span>
                                                        </label>
                                                        @php
                                                            $sampleRelationOptions = collect($this->configFieldSampleRelationOptions)
                                                                ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
                                                                ->prepend(['value' => '', 'label' => 'Use ID only'])
                                                                ->values()
                                                                ->all();
                                                        @endphp
                                                        @include('livewire.procedures.partials.config-search-select', [
                                                            'options' => $sampleRelationOptions,
                                                            'wireModel' => 'configFieldSampleRelationColumn',
                                                            'placeholder' => 'Search related field...',
                                                            'emptyMessage' => 'No related field matches your search.',
                                                            'inputRef' => 'sampleRelationSearch',
                                                            'pickerKey' => 'config-field-sample-relation-' . $configFieldSampleColumn . '-' . $configFieldSampleRelationColumn,
                                                        ])
                                                        <p class="text-muted small mb-0 mt-1">Pick a field from the linked record (e.g. product name instead of product ID).</p>
                                                    </div>
                                                    @elseif($configFieldSampleDisplayMode === 'foreign_key' && $configFieldSampleColumn !== '')
                                                    <p class="text-muted small mb-3">No related display columns were found for this foreign key — value falls back to ID.</p>
                                                    @endif
                                                    @endif
                                                    @if($configFieldType === 'dataset' || $configFieldType === 'dataset_multiselect')
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Dataset source <span class="text-danger">*</span></label>
                                                        @php
                                                            $datasetSourceOptions = collect(\App\Models\Procedures\ProcedureConfigField::getDatasetModels())
                                                                ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
                                                                ->prepend(['value' => '', 'label' => 'Select...'])
                                                                ->values()
                                                                ->all();
                                                        @endphp
                                                        @include('livewire.procedures.partials.config-search-select', [
                                                            'options' => $datasetSourceOptions,
                                                            'wireModel' => 'configFieldModelTiedTo',
                                                            'placeholder' => 'Search dataset source...',
                                                            'emptyMessage' => 'No dataset source matches your search.',
                                                            'inputRef' => 'datasetSourceSearch',
                                                            'pickerKey' => 'config-field-dataset-' . $configFieldModelTiedTo,
                                                        ])
                                                        @error('configFieldModelTiedTo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>

                                                    @if($configFieldModelTiedTo !== '')
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Column type <span class="text-danger">*</span></label>
                                                        <select class="form-control pw-input" wire:model.live="configFieldDatasetDisplayMode">
                                                            <option value="direct">Normal column (direct)</option>
                                                            <option value="foreign_key">Foreign key → related table column</option>
                                                        </select>
                                                        @error('configFieldDatasetDisplayMode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>

                                                    @if($configFieldDatasetDisplayMode === 'direct')
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Display column <span class="text-danger">*</span></label>
                                                        @php
                                                            $datasetDisplayColumns = collect($this->configFieldDatasetSourceColumnOptions)
                                                                ->map(fn ($option) => [
                                                                    'value' => (string) ($option['value'] ?? ''),
                                                                    'label' => (string) ($option['label'] ?? ($option['value'] ?? ''))
                                                                        . (!empty($option['type']) ? ' · ' . (string) $option['type'] : ''),
                                                                ])
                                                                ->prepend(['value' => '', 'label' => 'Select column...'])
                                                                ->values()
                                                                ->all();
                                                        @endphp
                                                        @include('livewire.procedures.partials.config-search-select', [
                                                            'options' => $datasetDisplayColumns,
                                                            'wireModel' => 'configFieldDatasetSourceColumn',
                                                            'placeholder' => 'Search source column...',
                                                            'emptyMessage' => 'No source column matches your search.',
                                                            'inputRef' => 'datasetDisplayColumnSearch',
                                                            'live' => true,
                                                            'pickerKey' => 'config-field-dataset-source-column-' . $configFieldModelTiedTo . '-' . $configFieldDatasetSourceColumn,
                                                        ])
                                                        @error('configFieldDatasetSourceColumn') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    @else
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Foreign key column <span class="text-danger">*</span></label>
                                                        @php
                                                            $datasetForeignKeys = collect($this->configFieldDatasetForeignKeyOptions)
                                                                ->map(fn ($option) => [
                                                                    'value' => (string) ($option['column'] ?? ''),
                                                                    'label' => (string) ($option['label'] ?? ''),
                                                                ])
                                                                ->prepend(['value' => '', 'label' => 'Select foreign key...'])
                                                                ->values()
                                                                ->all();
                                                        @endphp
                                                        @include('livewire.procedures.partials.config-search-select', [
                                                            'options' => $datasetForeignKeys,
                                                            'wireModel' => 'configFieldDatasetFkColumn',
                                                            'placeholder' => 'Search foreign keys...',
                                                            'emptyMessage' => 'No foreign key matches your search.',
                                                            'inputRef' => 'datasetForeignKeySearch',
                                                            'live' => true,
                                                            'pickerKey' => 'config-field-dataset-fk-column-' . $configFieldModelTiedTo . '-' . $configFieldDatasetFkColumn,
                                                        ])
                                                        @error('configFieldDatasetFkColumn') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>

                                                    @if($configFieldDatasetReferencedTable !== '')
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Referenced display column <span class="text-danger">*</span></label>
                                                        <p class="text-muted small mb-2">Related table: <code>{{ $configFieldDatasetReferencedTable }}</code></p>
                                                        @php
                                                            $datasetRefColumns = collect($this->configFieldDatasetReferencedColumnOptions)
                                                                ->map(fn ($option) => [
                                                                    'value' => (string) ($option['value'] ?? ''),
                                                                    'label' => (string) ($option['label'] ?? ($option['value'] ?? ''))
                                                                        . (!empty($option['type']) ? ' · ' . (string) $option['type'] : ''),
                                                                ])
                                                                ->prepend(['value' => '', 'label' => 'Select related display column...'])
                                                                ->values()
                                                                ->all();
                                                        @endphp
                                                        @include('livewire.procedures.partials.config-search-select', [
                                                            'options' => $datasetRefColumns,
                                                            'wireModel' => 'configFieldDatasetReferencedDisplayColumn',
                                                            'placeholder' => 'Search related display column...',
                                                            'emptyMessage' => 'No related display column matches your search.',
                                                            'inputRef' => 'datasetReferencedDisplaySearch',
                                                            'live' => true,
                                                            'pickerKey' => 'config-field-dataset-ref-column-' . $configFieldDatasetReferencedTable . '-' . $configFieldDatasetReferencedDisplayColumn,
                                                        ])
                                                        @error('configFieldDatasetReferencedDisplayColumn') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    @endif
                                                    @endif
                                                    @endif
                                                    @endif
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold">Order</label>
                                                            <input type="number" wire:model="configFieldOrder" class="form-control pw-input" min="1">
                                                        </div>
                                                        <div class="col-md-8 d-flex align-items-end">
                                                            <div class="form-check form-switch pw-switch mb-2">
                                                                <input type="checkbox" wire:model="configFieldIsRequired" class="form-check-input" id="configFieldIsRequired">
                                                                <label class="form-check-label fw-semibold" for="configFieldIsRequired">Required field</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-0 mt-3">
                                                        <label class="form-label fw-semibold">Help Text</label>
                                                        <textarea wire:model="configFieldHelpText" class="form-control pw-input" rows="2" placeholder="Optional guidance for analysts"></textarea>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer pw-modal-footer">
                                                <button type="button" class="btn btn-light" wire:click="closeConfigFieldModal">Cancel</button>
                                                <button type="submit" form="config-field-form" class="btn btn-primary px-4">
                                                    <i class="mdi mdi-content-save-outline me-1"></i>
                                                    {{ $showEditConfigFieldModal ? 'Save Changes' : 'Add Field' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($showDeleteConfigFieldModalOpen)
                                <div class="pw-modal show d-block" tabindex="-1" wire:click.self="dismissDeleteConfigFieldModal">
                                    <div class="modal-dialog pw-modal-dialog pw-modal-dialog--sm" wire:click.stop>
                                        <div class="modal-content pw-modal-content">
                                            <div class="modal-header pw-modal-header pw-modal-header--danger">
                                                <div>
                                                    <h5 class="modal-title mb-1">Delete Configurable Field</h5>
                                                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                                                </div>
                                                <button type="button" class="close pw-modal-close" wire:click="dismissDeleteConfigFieldModal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body pw-modal-body">
                                                <p class="mb-0 text-secondary">Are you sure you want to delete this configurable field?</p>
                                            </div>
                                            <div class="modal-footer pw-modal-footer">
                                                <button type="button" class="btn btn-light" wire:click="dismissDeleteConfigFieldModal">Cancel</button>
                                                <button type="button" class="btn btn-danger px-4" wire:click="deleteConfigField">
                                                    <i class="mdi mdi-delete-outline me-1"></i> Delete
                                                </button>
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
                                                <table class="table table-striped table-hover workflow-table">
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
                                                                    <button wire:click="showEditTestKitColumnModalInit(@js($column['id']))"
                                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                                        <i class="mdi mdi-pencil"></i>
                                                                    </button>
                                                                    <button wire:click="showDeleteTestKitColumnModal(@js($column['id']))"
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
                                <div class="pw-modal show d-block" tabindex="-1" wire:click.self="closeTestKitColumnModal">
                                    <div class="modal-dialog pw-modal-dialog" wire:click.stop>
                                        <div class="modal-content pw-modal-content">
                                            <div class="modal-header pw-modal-header">
                                                <div>
                                                    <h5 class="modal-title mb-1">
                                                        {{ $showEditTestKitColumnModal ? 'Edit Test Kit Column' : 'Add Test Kit Column' }}
                                                    </h5>
                                                    <p class="text-muted small mb-0">Define columns for test kit data on this procedure.</p>
                                                </div>
                                                <button type="button" class="close pw-modal-close" wire:click="closeTestKitColumnModal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body pw-modal-body">
                                                <form id="test-kit-column-form" wire:submit.prevent="{{ $showEditTestKitColumnModal ? 'updateTestKitColumn' : 'createTestKitColumn' }}" class="pw-form">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Label <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="testKitColumnLabel" class="form-control pw-input">
                                                        @error('testKitColumnLabel') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Key <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="testKitColumnKey" class="form-control pw-input">
                                                        @error('testKitColumnKey') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                                        <select wire:model="testKitColumnType" class="form-control pw-input modern-select">
                                                            <option value="string">Text</option>
                                                            <option value="number">Number</option>
                                                            <option value="date">Date</option>
                                                            <option value="boolean">Yes/No</option>
                                                        </select>
                                                        @error('testKitColumnType') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold">Order</label>
                                                            <input type="number" wire:model="testKitColumnOrder" class="form-control pw-input" min="1">
                                                        </div>
                                                        <div class="col-md-8 d-flex align-items-end">
                                                            <div class="form-check form-switch pw-switch mb-2">
                                                                <input type="checkbox" wire:model="testKitColumnIsRequired" class="form-check-input" id="testKitColumnIsRequired">
                                                                <label class="form-check-label fw-semibold" for="testKitColumnIsRequired">Required column</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-0 mt-3">
                                                        <label class="form-label fw-semibold">Help Text</label>
                                                        <textarea wire:model="testKitColumnHelpText" class="form-control pw-input" rows="2"></textarea>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer pw-modal-footer">
                                                <button type="button" class="btn btn-light" wire:click="closeTestKitColumnModal">Cancel</button>
                                                <button type="submit" form="test-kit-column-form" class="btn btn-primary px-4">
                                                    <i class="mdi mdi-content-save-outline me-1"></i>
                                                    {{ $showEditTestKitColumnModal ? 'Save Changes' : 'Add Column' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($showDeleteTestKitColumnModalOpen)
                                <div class="pw-modal show d-block" tabindex="-1" wire:click.self="dismissDeleteTestKitColumnModal">
                                    <div class="modal-dialog pw-modal-dialog pw-modal-dialog--sm" wire:click.stop>
                                        <div class="modal-content pw-modal-content">
                                            <div class="modal-header pw-modal-header pw-modal-header--danger">
                                                <div>
                                                    <h5 class="modal-title mb-1">Delete Test Kit Column</h5>
                                                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                                                </div>
                                                <button type="button" class="close pw-modal-close" wire:click="dismissDeleteTestKitColumnModal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body pw-modal-body">
                                                <p class="mb-0 text-secondary">Are you sure you want to delete this test kit column?</p>
                                            </div>
                                            <div class="modal-footer pw-modal-footer">
                                                <button type="button" class="btn btn-light" wire:click="dismissDeleteTestKitColumnModal">Cancel</button>
                                                <button type="button" class="btn btn-danger px-4" wire:click="deleteTestKitColumn">
                                                    <i class="mdi mdi-delete-outline me-1"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'layout' ? 'show active' : '' }}" id="layout-tab-pane" role="tabpanel" aria-labelledby="layout-tab">
                            @if($worksheetId)
                                <livewire:procedures.procedure-layout-editor
                                    :worksheetId="$worksheetId"
                                    :key="'procedure-layout-'.$worksheetId"
                                />
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="pw-modal show d-block" tabindex="-1" wire:click.self="cancelImport">
            <div class="modal-dialog modal-dialog-scrollable pw-modal-dialog" wire:click.stop>
                <div class="modal-content pw-modal-content">
                    <div class="modal-header pw-modal-header">
                        <div>
                            <h5 class="modal-title mb-1">Import Worksheet Data</h5>
                            <p class="text-muted small mb-0">Copy steps, fields, or test kit columns from another procedure.</p>
                        </div>
                        <button type="button" class="close pw-modal-close" wire:click="cancelImport" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pw-modal-body">
                        <form id="import-worksheet-form" wire:submit.prevent="importData" class="pw-form">
                            <div class="mb-4">
                                <label class="form-label">Source Worksheet <span class="text-danger">*</span></label>
                                @if($selectedImportWorksheetId && $selectedImportWorksheet = App\Models\Procedures\ProcedureWorksheet::find($selectedImportWorksheetId))
                                    <div class="position-relative">
                                        <input type="text" class="form-control" value="{{ $selectedImportWorksheet->name }}" readonly style="padding-right: 30px;">
                                        <i class="mdi mdi-close text-danger cursor-pointer" 
                                           wire:click="clearSelectedImportWorksheet"
                                           style="position: absolute; top: 10px; right: 10px; z-index: 10;"></i>
                                    </div>
                                @else
                                    <div class="position-relative">
                                        <input type="text" wire:model.live.debounce.300ms="importWorksheetSearch" class="form-control" placeholder="Search worksheet by name...">
                                        @if(strlen($importWorksheetSearch) > 1 && count($importableWorksheets) > 0)
                                            <div class="position-absolute w-100 bg-white border shadow rounded mt-1" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                                @foreach($importableWorksheets as $ws)
                                                    <div class="p-2 border-bottom cursor-pointer hover-bg-light" wire:click="selectImportWorksheet(@js($ws->id))">
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
                            
                            <div class="pw-callout pw-callout--compact mb-0">
                                <div class="pw-callout-icon"><i class="mdi mdi-information-outline"></i></div>
                                <div class="pw-callout-text"><span>Imported data will be appended to this procedure.</span></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer pw-modal-footer">
                        <button type="button" class="btn btn-light" wire:click="cancelImport">Cancel</button>
                        <button type="submit" form="import-worksheet-form" class="btn btn-primary px-4" {{ !$selectedImportWorksheetId ? 'disabled' : '' }}>
                            <i class="mdi mdi-download me-1"></i> Import Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="pw-modal show d-block" tabindex="-1" wire:click.self="cancel">
            <div class="modal-dialog modal-lg modal-dialog-scrollable pw-modal-dialog pw-modal-dialog--lg" wire:click.stop>
                <div class="modal-content pw-modal-content">
                    <div class="modal-header pw-modal-header">
                        <div>
                            <h5 class="modal-title mb-1">{{ $editingStepId ? 'Edit' : 'Add' }} Step</h5>
                            <p class="text-muted small mb-0">Define how analysts capture data for this procedure step.</p>
                        </div>
                        <button type="button" class="close pw-modal-close" wire:click="cancel" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pw-modal-body">
                        <form id="procedure-step-form" wire:submit.prevent="save" class="pw-form procedure-step-form">
                            <div class="form-section mb-4">
                                <label class="form-label fw-semibold">Step Description <span class="text-danger">*</span></label>
                                <input type="text" wire:model="step" class="form-control procedure-step-input" placeholder="e.g. Weigh sample, Record temperature...">
                                @error('step') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            @if(count($stepGroups) > 0)
                            <div class="form-section mb-4">
                                <label class="form-label fw-semibold">Step group <span class="text-muted fw-normal">(optional)</span></label>
                                <select wire:model="stepGroupId" class="form-control procedure-step-input modern-select">
                                    <option value="">No group — standalone step</option>
                                    @foreach($stepGroups as $grp)
                                    <option value="{{ $grp['id'] }}">{{ $grp['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="form-section mb-4">
                                <label class="form-label fw-semibold d-block mb-2">
                                    Value Type <span class="text-danger">*</span>
                                </label>
                                <div class="value-type-picker @error('value_type') is-invalid @enderror">
                                    @foreach([
                                        'text' => ['label' => 'Text', 'icon' => 'mdi-format-text'],
                                        'number' => ['label' => 'Number', 'icon' => 'mdi-numeric'],
                                        'date' => ['label' => 'Date', 'icon' => 'mdi-calendar'],
                                        'time' => ['label' => 'Time', 'icon' => 'mdi-clock-outline'],
                                        'datetime' => ['label' => 'Date & Time', 'icon' => 'mdi-calendar-clock'],
                                        'method_select' => ['label' => 'Method', 'icon' => 'mdi-flask-outline'],
                                        'equipment_select' => ['label' => 'Equipment', 'icon' => 'mdi-tools'],
                                        'custom_select' => ['label' => 'Custom List', 'icon' => 'mdi-playlist-edit'],
                                        'custom_table' => ['label' => 'Custom Table', 'icon' => 'mdi-table-large'],
                                        'static_text' => ['label' => 'Static Text', 'icon' => 'mdi-text-box-outline'],
                                    ] as $typeKey => $typeMeta)
                                        <label class="value-type-option {{ $value_type === $typeKey ? 'active' : '' }}">
                                            <input type="radio" class="value-type-option-input" wire:model.live="value_type" value="{{ $typeKey }}">
                                            <i class="mdi {{ $typeMeta['icon'] }}"></i>
                                            <span>{{ $typeMeta['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('value_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            @if($value_type === 'custom_table')
                                <div class="form-section mb-4">
                                    <label class="form-label fw-semibold d-block mb-2">Table mode <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <label class="lec-radio-card mb-0">
                                            <input type="radio" wire:model.live="table_mode" value="dynamic">
                                            <span>Dynamic rows</span>
                                        </label>
                                        <label class="lec-radio-card mb-0">
                                            <input type="radio" wire:model.live="table_mode" value="static">
                                            <span>Static rows</span>
                                        </label>
                                    </div>
                                    @error('table_mode') <div class="text-danger small">{{ $message }}</div> @enderror
                                    @if($table_mode === 'dynamic')
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-semibold">Row driver</label>
                                            <select class="form-control procedure-step-input" wire:model="row_driver">
                                                @foreach($rowDriverOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('row_driver') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="step-allow-manual-rows" wire:model="allow_manual_rows">
                                            <label class="custom-control-label" for="step-allow-manual-rows">Allow manual rows at capture</label>
                                        </div>
                                    @else
                                        <p class="text-muted small mb-0">After saving, use <strong>Configure table</strong> in the steps list to define columns and static body rows.</p>
                                    @endif
                                </div>
                            @endif

                            @if($value_type === 'custom_select')
                                <div class="form-section mb-4">
                                    <label class="form-label fw-semibold">Custom Options <span class="text-danger">*</span></label>
                                    <p class="text-muted small mb-2">Define the choices analysts can pick when completing this step.</p>
                                    <div class="input-group mb-2">
                                        <input type="text" wire:model="newSelectOption" wire:keydown.enter.prevent="addSelectOption"
                                               class="form-control procedure-step-input" placeholder="Type an option and press Add">
                                        <button type="button" class="btn btn-outline-primary" wire:click="addSelectOption">
                                            <i class="mdi mdi-plus"></i> Add
                                        </button>
                                    </div>
                                    @error('select_options') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                                    @if(count($select_options) > 0)
                                        <div class="custom-options-list">
                                            @foreach($select_options as $index => $option)
                                                <span class="tag-badge tag-badge--neutral">
                                                    {{ $option }}
                                                    <button type="button" class="btn-tag-remove" wire:click="removeSelectOption({{ $index }})" title="Remove">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-muted small fst-italic">No options added yet.</div>
                                    @endif
                                </div>
                            @endif

                            @if($value_type === 'static_text')
                                <div class="form-section mb-4">
                                    <label class="form-label fw-semibold">Static Text <span class="text-danger">*</span></label>
                                    <p class="text-muted small mb-2">This text is shown on the worksheet as read-only guidance or instructions. Analysts do not enter a value for this step.</p>
                                    <textarea
                                        wire:model="default_value"
                                        class="form-control procedure-step-input"
                                        rows="5"
                                        placeholder="Enter the text to display on the worksheet…"
                                    ></textarea>
                                    @error('default_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            @elseif($value_type !== 'custom_table')
                            <div class="form-section mb-4">
                                @php
                                    $defaultInputType = match($value_type) {
                                        'number' => 'number',
                                        'date' => 'date',
                                        'time' => 'time',
                                        'datetime' => 'datetime-local',
                                        default => 'text',
                                    };
                                    $scalarValueTypes = ['text', 'number', 'date', 'time', 'datetime'];
                                @endphp
                                <label class="form-label fw-semibold">
                                    Default Value
                                    @if($value_type === 'custom_select')
                                        <span class="text-muted fw-normal">(optional)</span>
                                    @endif
                                </label>

                                @if($value_type === 'method_select')
                                    <div class="position-relative" wire:click.outside="closeMethodDropdown">
                                        @if($selectedDefaultMethod)
                                            <div class="selected-pill">
                                                <span>{{ $selectedDefaultMethod->name }}@if($selectedDefaultMethod->code) ({{ $selectedDefaultMethod->code }})@endif</span>
                                                <button type="button" class="btn-pill-clear" wire:click="clearDefaultMethod"><i class="mdi mdi-close"></i></button>
                                            </div>
                                        @else
                                            <input type="text" wire:model.live="methodSearch" wire:focus="openMethodDropdown"
                                                   class="form-control procedure-step-input" placeholder="Search methods...">
                                            @if($showMethodDropdown && count($methods) > 0)
                                                <div class="search-dropdown">
                                                    @foreach($methods as $method)
                                                        <button type="button" class="search-dropdown-item" wire:click="selectDefaultMethod(@js($method->id))">
                                                            {{ $method->name }}@if($method->code) <span class="text-muted">({{ $method->code }})</span>@endif
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @elseif($value_type === 'equipment_select')
                                    <div class="position-relative" wire:click.outside="closeDefaultValueEquipmentDropdown">
                                        @if($selectedDefaultValueEquipment)
                                            <div class="selected-pill">
                                                <span>{{ $selectedDefaultValueEquipment->name }} ({{ $selectedDefaultValueEquipment->equipment_number }})</span>
                                                <button type="button" class="btn-pill-clear" wire:click="clearDefaultValueEquipment"><i class="mdi mdi-close"></i></button>
                                            </div>
                                        @else
                                            <input type="text" wire:model.live="defaultValueEquipmentSearch" wire:focus="openDefaultValueEquipmentDropdown"
                                                   class="form-control procedure-step-input" placeholder="Search equipment for default value...">
                                            @if($showDefaultValueEquipmentDropdown && count($defaultValueEquipments) > 0)
                                                <div class="search-dropdown">
                                                    @foreach($defaultValueEquipments as $eq)
                                                        <button type="button" class="search-dropdown-item" wire:click="selectDefaultValueEquipment(@js($eq->id))">
                                                            {{ $eq->name }} ({{ $eq->equipment_number }})
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @elseif($value_type === 'custom_select')
                                    <select wire:model="default_value" class="form-control procedure-step-input modern-select">
                                        <option value="">No default (analyst chooses)</option>
                                        @foreach($select_options as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif(in_array($value_type, $scalarValueTypes, true))
                                    <input type="{{ $defaultInputType }}"
                                           wire:model="default_value"
                                           class="form-control procedure-step-input"
                                           placeholder="Optional pre-filled value"
                                           @if($value_type === 'number') step="any" @endif>
                                @endif
                                @error('default_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            @endif

                            @if($value_type !== 'custom_table')
                            <div class="form-section-divider">
                                <span>Worksheet defaults</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Equipment</label>
                                    <div class="position-relative" wire:click.outside="closeEquipmentDropdown">
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
                                                   wire:click="clearDefaultEquipment"
                                                   style="position: absolute; top: 10px; right: 10px; z-index: 10;"></i>
                                            </div>
                                            @endif
                                        @else
                                            <input type="text" wire:model.live="equipmentSearch" wire:focus="openEquipmentDropdown" class="form-control" placeholder="Search equipment...">
                                            @if($showEquipmentDropdown && count($equipments) > 0)
                                                <div class="position-absolute w-100 bg-white border shadow rounded mt-1" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                                    @foreach($equipments as $eq)
                                                        <div class="p-2 border-bottom cursor-pointer hover-bg-light" wire:click="selectEquipment(@js($eq->id))">
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
                                    <div class="position-relative" wire:click.outside="closeAnalystDropdown">
                                        @if($selectedAnalyst)
                                            <div class="position-relative">
                                                <input type="text" class="form-control" value="{{ $selectedAnalyst->name }}" readonly style="padding-right: 30px;">
                                                <i class="mdi mdi-close text-danger cursor-pointer" 
                                                   wire:click="clearDefaultAnalyst"
                                                   style="position: absolute; top: 10px; right: 10px; z-index: 10;"></i>
                                            </div>
                                        @else
                                            <input type="text" wire:model.live="analystSearch" wire:focus="openAnalystDropdown" class="form-control" placeholder="Search analyst...">
                                            @if($showAnalystDropdown && count($analysts) > 0)
                                                <div class="position-absolute w-100 bg-white border shadow rounded mt-1" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                                    @foreach($analysts as $user)
                                                        <div class="p-2 border-bottom cursor-pointer hover-bg-light" wire:click="selectAnalyst(@js($user->id))">
                                                            {{ $user->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Measurands</label>
                                <div
                                    class="position-relative pw-combobox"
                                    x-data="{ open: @entangle('showMeasurandDropdown').live }"
                                    @click.outside="open = false"
                                >
                                    <div
                                        class="form-control pw-measurand-field d-flex flex-wrap align-items-center gap-1"
                                        @click="open = true; $nextTick(() => $refs.measurandSearchInput?.focus())"
                                    >
                                        @foreach($selectedMeasurands as $m)
                                            <span class="tag-badge tag-badge--info d-inline-flex align-items-center">
                                                {{ $m->name }}
                                                <button type="button" class="btn-tag-remove ms-1" wire:click.stop="removeMeasurand(@js($m->id))" title="Remove">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </span>
                                        @endforeach
                                        <input
                                            x-ref="measurandSearchInput"
                                            type="text"
                                            wire:model.live.debounce.200ms="measurandSearch"
                                            @focus="open = true"
                                            class="pw-measurand-input border-0 p-0 m-0"
                                            placeholder="{{ count($selectedMeasurands) > 0 ? 'Search more...' : 'Search measurands...' }}"
                                        >
                                    </div>

                                    <div
                                        x-show="open"
                                        x-cloak
                                        class="search-dropdown pw-measurand-dropdown"
                                    >
                                        @forelse($measurands as $m)
                                            <button
                                                type="button"
                                                class="search-dropdown-item d-flex align-items-center justify-content-between"
                                                wire:click.stop="toggleMeasurand(@js($m->id))"
                                                @click="open = false"
                                            >
                                                <span>{{ $m->name }}</span>
                                                @if(in_array($m->id, $default_measurand_ids))
                                                    <i class="mdi mdi-check text-success"></i>
                                                @endif
                                            </button>
                                        @empty
                                            <div class="search-dropdown-empty text-muted">
                                                {{ trim($measurandSearch) !== '' ? 'No measurands match your search.' : 'No measurands available.' }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            @if(count($selectedMeasurands) > 0 && $value_type !== 'static_text')
                                <div class="form-section mb-4">
                                    <label class="form-label fw-semibold">
                                        Default Values (per measurand)
                                        <span class="text-muted fw-normal small">(leave blank to keep empty)</span>
                                    </label>
                                    <div class="row g-2">
                                        @foreach($selectedMeasurands as $m)
                                            <div class="col-md-6">
                                                <label class="form-label small mb-1">{{ $m->name }}</label>
                                                @php
                                                    $measurandDefaultValue = $default_measurand_values[$m->id] ?? '';
                                                @endphp
                                                @if($value_type === 'method_select')
                                                    <select
                                                        class="form-control procedure-step-input"
                                                        wire:change="setDefaultMeasurandValue(@js($m->id), $event.target.value)"
                                                    >
                                                        <option value="" @selected($measurandDefaultValue === '')>—</option>
                                                        @foreach($methodOptionsList as $method)
                                                            <option value="{{ $method->id }}" @selected((string) $measurandDefaultValue === (string) $method->id)>{{ $method->name }}@if($method->code) ({{ $method->code }})@endif</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($value_type === 'equipment_select')
                                                    <select
                                                        class="form-control procedure-step-input"
                                                        wire:change="setDefaultMeasurandValue(@js($m->id), $event.target.value)"
                                                    >
                                                        <option value="" @selected($measurandDefaultValue === '')>—</option>
                                                        @foreach($equipmentOptionsList as $eq)
                                                            <option value="{{ $eq->id }}" @selected((string) $measurandDefaultValue === (string) $eq->id)>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($value_type === 'custom_select')
                                                    <select
                                                        class="form-control procedure-step-input"
                                                        wire:change="setDefaultMeasurandValue(@js($m->id), $event.target.value)"
                                                    >
                                                        <option value="" @selected($measurandDefaultValue === '')>—</option>
                                                        @foreach($select_options as $option)
                                                            <option value="{{ $option }}" @selected((string) $measurandDefaultValue === (string) $option)>{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    @php
                                                        $perMeasInputType = match($value_type) {
                                                            'number' => 'number',
                                                            'date' => 'date',
                                                            'time' => 'time',
                                                            'datetime' => 'datetime-local',
                                                            default => 'text',
                                                        };
                                                    @endphp
                                                    <input
                                                        type="{{ $perMeasInputType }}"
                                                        value="{{ $measurandDefaultValue }}"
                                                        wire:change="setDefaultMeasurandValue(@js($m->id), $event.target.value)"
                                                        class="form-control procedure-step-input"
                                                        @if($value_type === 'number') step="any" @endif
                                                    >
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @endif

                            <div class="form-section pw-step-options">
                                <div class="pw-step-options-head">
                                    <div class="pw-step-options-head-icon" aria-hidden="true">
                                        <i class="mdi mdi-tune-variant"></i>
                                    </div>
                                    <div>
                                        <h6 class="pw-step-options-heading">Step options</h6>
                                        <p class="pw-step-options-subheading">Control how this step behaves on the worksheet.</p>
                                    </div>
                                </div>

                                <ul class="pw-option-list">
                                    <li>
                                        <label class="pw-option-card" for="isActiveStep">
                                            <div class="pw-option-card-body">
                                                <span class="pw-option-icon pw-option-icon--active" aria-hidden="true">
                                                    <i class="mdi mdi-play-circle-outline"></i>
                                                </span>
                                                <div class="pw-option-copy">
                                                    <span class="pw-option-title">Active step</span>
                                                    <span class="pw-option-desc">Show and run this step when analysts complete the worksheet.</span>
                                                </div>
                                            </div>
                                            <span class="pw-option-toggle" aria-hidden="true">
                                                <input type="checkbox" wire:model="is_active" class="pw-switch-input" id="isActiveStep">
                                                <span class="pw-switch-track"></span>
                                            </span>
                                        </label>
                                    </li>
                                    @if($value_type !== 'custom_table' && $value_type !== 'static_text')
                                    <li>
                                        <label class="pw-option-card" for="isResultStep">
                                            <div class="pw-option-card-body">
                                                <span class="pw-option-icon pw-option-icon--result" aria-hidden="true">
                                                    <i class="mdi mdi-chart-line"></i>
                                                </span>
                                                <div class="pw-option-copy">
                                                    <span class="pw-option-title">Result step</span>
                                                    <span class="pw-option-desc">Values captured here are used when posting results.</span>
                                                </div>
                                            </div>
                                            <span class="pw-option-toggle" aria-hidden="true">
                                                <input type="checkbox" wire:model="is_result_step" class="pw-switch-input" id="isResultStep">
                                                <span class="pw-switch-track"></span>
                                            </span>
                                        </label>
                                    </li>
                                    @endif
                                    <li class="pw-option-stack">
                                        <label class="pw-option-card pw-option-card--logbook {{ $attracts_equipment_logbook ? 'is-expanded' : '' }}" for="attractsEquipmentLogbook">
                                            <div class="pw-option-card-body">
                                                <span class="pw-option-icon pw-option-icon--logbook" aria-hidden="true">
                                                    <i class="mdi mdi-book-open-page-variant-outline"></i>
                                                </span>
                                                <div class="pw-option-copy">
                                                    <span class="pw-option-title">Attracts equipment logbook</span>
                                                    <span class="pw-option-desc">Require logbook entries for selected instruments on this step.</span>
                                                </div>
                                            </div>
                                            <span class="pw-option-toggle" aria-hidden="true">
                                                <input type="checkbox" wire:model.live="attracts_equipment_logbook" class="pw-switch-input" id="attractsEquipmentLogbook">
                                                <span class="pw-switch-track"></span>
                                            </span>
                                        </label>

                                @if($attracts_equipment_logbook)
                                    <div class="pw-logbook-panel">
                                        <div class="pw-logbook-panel-head">
                                            <span class="pw-logbook-panel-icon" aria-hidden="true">
                                                <i class="mdi mdi-wrench-outline"></i>
                                            </span>
                                            <div>
                                                <span class="pw-logbook-panel-title">Logbook equipment</span>
                                                <span class="pw-logbook-panel-desc">Select one or more instruments linked to this step.</span>
                                            </div>
                                            <span class="pw-logbook-required">Required</span>
                                        </div>
                                        <div
                                            class="position-relative pw-combobox pw-logbook-combobox"
                                            x-data="{ open: @entangle('showLogbookEquipmentDropdown').live }"
                                            @click.outside="open = false"
                                        >
                                            <div
                                                class="pw-logbook-field d-flex flex-wrap align-items-center"
                                                @click="open = true; $nextTick(() => $refs.logbookEquipmentSearchInput?.focus())"
                                            >
                                                @foreach($selectedLogbookEquipments as $eq)
                                                    <span class="pw-logbook-chip">
                                                        <i class="mdi mdi-cog-outline" aria-hidden="true"></i>
                                                        <span>{{ $eq->name }}@if($eq->equipment_number)<span class="pw-logbook-chip-meta">({{ $eq->equipment_number }})</span>@endif</span>
                                                        <button type="button" class="pw-logbook-chip-remove" wire:click.stop="removeLogbookEquipment(@js($eq->id))" title="Remove" aria-label="Remove {{ $eq->name }}">
                                                            <i class="mdi mdi-close"></i>
                                                        </button>
                                                    </span>
                                                @endforeach
                                                <input
                                                    x-ref="logbookEquipmentSearchInput"
                                                    type="text"
                                                    wire:model.live.debounce.200ms="logbookEquipmentSearch"
                                                    @focus="open = true"
                                                    class="pw-logbook-search-input"
                                                    placeholder="{{ count($selectedLogbookEquipments) > 0 ? 'Add more equipment…' : 'Search equipment…' }}"
                                                >
                                            </div>
                                            <div
                                                x-show="open"
                                                x-cloak
                                                x-transition:enter="transition ease-out duration-150"
                                                x-transition:enter-start="opacity-0 translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                class="search-dropdown pw-measurand-dropdown pw-logbook-dropdown"
                                            >
                                                @forelse($logbookEquipments as $eq)
                                                    <button
                                                        type="button"
                                                        class="search-dropdown-item d-flex align-items-center justify-content-between {{ in_array((string) $eq->id, array_map('strval', $logbook_equipment_ids), true) ? 'is-selected' : '' }}"
                                                        wire:click.stop="toggleLogbookEquipment(@js($eq->id))"
                                                        @click="open = false"
                                                    >
                                                        <span class="d-flex align-items-center">
                                                            <i class="mdi mdi-cog-outline text-muted mr-2"></i>
                                                            {{ $eq->name }}@if($eq->equipment_number)<span class="text-muted small ml-1">({{ $eq->equipment_number }})</span>@endif
                                                        </span>
                                                        @if(in_array((string) $eq->id, array_map('strval', $logbook_equipment_ids), true))
                                                            <i class="mdi mdi-check-circle text-success"></i>
                                                        @endif
                                                    </button>
                                                @empty
                                                    <div class="search-dropdown-empty text-muted">
                                                        {{ trim($logbookEquipmentSearch) !== '' ? 'No equipment matches your search.' : 'No equipment available.' }}
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                        @error('logbook_equipment_ids')
                                            <div class="pw-logbook-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                                    </li>
                                </ul>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer pw-modal-footer">
                        <button type="button" class="btn btn-light" wire:click="cancel">Cancel</button>
                        <button type="submit" form="procedure-step-form" class="btn btn-primary px-4">
                            <i class="mdi mdi-content-save-outline me-1"></i> Save Step
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="pw-modal show d-block" tabindex="-1" wire:click.self="cancelDelete">
            <div class="modal-dialog pw-modal-dialog pw-modal-dialog--sm" wire:click.stop>
                <div class="modal-content pw-modal-content">
                    <div class="modal-header pw-modal-header pw-modal-header--danger">
                        <div>
                            <h5 class="modal-title mb-1">Delete Step</h5>
                            <p class="text-muted small mb-0">This action cannot be undone.</p>
                        </div>
                        <button type="button" class="close pw-modal-close" wire:click="cancelDelete" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pw-modal-body">
                        <p class="mb-0 text-secondary">Are you sure you want to delete this step? Associated worksheet values may be affected.</p>
                    </div>
                    <div class="modal-footer pw-modal-footer">
                        <button type="button" class="btn btn-light" wire:click="cancelDelete">Cancel</button>
                        <button type="button" class="btn btn-danger px-4" wire:click="deleteStep">
                            <i class="mdi mdi-delete-outline me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.procedures.partials.step-table-modals')
    @include('livewire.procedures.partials.procedure-section-group-modals')

    <style>
        .procedure-editor-page .pw-capture-layout-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }

        .procedure-editor-page .pw-capture-layout-panel__icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 10px;
            background: #e0f2fe;
            color: #0369a1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .procedure-editor-page {
            --pw-slate-50: #f8fafc;
            --pw-slate-100: #f1f5f9;
            --pw-slate-200: #e2e8f0;
            --pw-slate-500: #64748b;
            --pw-slate-700: #334155;
            --pw-slate-900: #0f172a;
            --pw-blue-500: #3b82f6;
            --pw-blue-600: #2563eb;
        }

        .procedure-editor-page .pw-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            padding: 1rem 1.25rem 0;
            background: var(--pw-slate-50);
            border-bottom: 1px solid var(--pw-slate-200);
        }

        .procedure-editor-page .pw-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            border: 1px solid transparent;
            border-radius: 0.5rem;
            background: transparent;
            color: var(--pw-slate-500);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .procedure-editor-page .pw-tab i {
            font-size: 1.1rem;
            opacity: 0.85;
        }

        .procedure-editor-page .pw-tab:hover {
            color: var(--pw-blue-600);
            background: #fff;
            border-color: var(--pw-slate-200);
        }

        .procedure-editor-page .pw-tab.active {
            color: var(--pw-blue-600);
            background: #fff;
            border-color: #bfdbfe;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .procedure-editor-page .pw-tab-content {
            padding: 1.25rem 1.5rem 1.5rem;
        }

        .procedure-editor-page .pw-callout {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
            border: 1px solid #dbeafe;
            border-radius: 0.65rem;
        }

        .procedure-editor-page .pw-callout--compact {
            padding: 0.65rem 0.85rem;
            margin-bottom: 0;
        }

        .procedure-editor-page .pw-callout-icon {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            background: #fff;
            color: var(--pw-blue-600);
            font-size: 1.15rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        .procedure-editor-page .pw-callout-text {
            font-size: 0.875rem;
            color: var(--pw-slate-700);
            line-height: 1.45;
        }

        .procedure-editor-page .pw-callout-text strong {
            display: block;
            color: var(--pw-slate-900);
            margin-bottom: 0.15rem;
        }

        .procedure-editor-page .pw-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .procedure-editor-page .pw-search-wrap {
            position: relative;
            flex: 1 1 280px;
            max-width: 420px;
        }

        .procedure-editor-page .pw-search-icon {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.15rem;
            pointer-events: none;
        }

        .procedure-editor-page .pw-search-wrap .pw-input {
            padding-left: 2.5rem;
        }

        .procedure-editor-page .pw-toolbar-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .procedure-editor-page .pw-toolbar-label {
            margin: 0;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--pw-slate-500);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .procedure-editor-page .pw-select-sm {
            width: auto;
            min-width: 4.5rem;
            border-radius: 0.5rem;
            border: 1px solid var(--pw-slate-200);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .procedure-editor-page .pw-modal {
            position: fixed;
            inset: 0;
            z-index: 1055;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
        }

        .procedure-editor-page .pw-modal-dialog {
            margin: 0 auto;
            max-width: 520px;
            width: 100%;
        }

        .procedure-editor-page .pw-modal-dialog--lg {
            max-width: 900px;
        }

        .procedure-editor-page .pw-modal-dialog--xl {
            max-width: 1200px;
        }

        .procedure-editor-page .pw-modal-dialog--sm {
            max-width: 440px;
        }

        .procedure-editor-page .pw-modal-content {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.28);
            overflow: hidden;
        }

        .procedure-editor-page .pw-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--pw-slate-200);
        }

        .procedure-editor-page .pw-modal-close {
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

        .procedure-editor-page .pw-modal-close:hover {
            color: #0f172a;
            opacity: 1;
        }

        .procedure-editor-page .pw-modal-header--danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
            border-bottom-color: #fecaca;
        }

        .procedure-editor-page .pw-modal-body {
            padding: 1.5rem;
            background: #fff;
        }

        .procedure-editor-page .pw-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            background: var(--pw-slate-50);
            border-top: 1px solid var(--pw-slate-200);
        }

        .procedure-editor-page .pw-input,
        .procedure-editor-page .procedure-step-input,
        .procedure-editor-page .pw-form .modern-select,
        .procedure-editor-page .procedure-step-form .modern-select {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            padding: 0.55rem 0.75rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .procedure-editor-page .pw-input:focus,
        .procedure-editor-page .procedure-step-input:focus,
        .procedure-editor-page .pw-form .modern-select:focus,
        .procedure-editor-page .procedure-step-form .modern-select:focus {
            border-color: var(--pw-blue-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .procedure-editor-page .pw-combobox .pw-measurand-field {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #fff;
        }

        .procedure-editor-page .pw-combobox:focus-within .pw-measurand-field {
            border-color: var(--pw-blue-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .procedure-editor-page .pw-switch .form-check-input:checked {
            background-color: var(--pw-blue-500);
            border-color: var(--pw-blue-500);
        }

        /* Step option toggles (iOS-style cards) */
        .procedure-editor-page .pw-switch-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            margin: 0;
            pointer-events: none;
        }

        .procedure-editor-page .pw-option-toggle {
            position: relative;
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
        }

        .procedure-editor-page .pw-switch-track {
            position: relative;
            display: block;
            width: 2.75rem;
            height: 1.5rem;
            background: #cbd5e1;
            border-radius: 999px;
            transition: background-color 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s ease;
        }

        .procedure-editor-page .pw-switch-track::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 1.125rem;
            height: 1.125rem;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
            transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .procedure-editor-page .pw-switch-input:checked + .pw-switch-track {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }

        .procedure-editor-page .pw-switch-input:checked + .pw-switch-track::after {
            transform: translateX(1.25rem);
        }

        .procedure-editor-page .pw-switch-input:focus-visible + .pw-switch-track {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.28);
        }

        .procedure-editor-page .pw-step-options {
            margin-bottom: 0;
            padding: 1.15rem 1.2rem 1.2rem;
            background: linear-gradient(165deg, #f8fafc 0%, #ffffff 55%, #f1f5f9 100%);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 0.875rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .procedure-editor-page .pw-step-options-head {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        }

        .procedure-editor-page .pw-step-options-head-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.55rem;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: var(--pw-blue-600);
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .procedure-editor-page .pw-step-options-heading {
            margin: 0 0 0.15rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--pw-slate-900);
            letter-spacing: -0.01em;
        }

        .procedure-editor-page .pw-step-options-subheading {
            margin: 0;
            font-size: 0.8rem;
            color: var(--pw-slate-500);
            line-height: 1.4;
        }

        .procedure-editor-page .pw-option-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .procedure-editor-page .pw-option-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            margin: 0;
            padding: 0.8rem 0.9rem;
            background: #fff;
            border: 1px solid var(--pw-slate-200);
            border-radius: 0.65rem;
            cursor: pointer;
            user-select: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.12s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }

        .procedure-editor-page .pw-option-card:hover {
            border-color: #bfdbfe;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);
        }

        .procedure-editor-page .pw-option-card:has(.pw-switch-input:checked),
        .procedure-editor-page .pw-option-card.is-expanded {
            border-color: rgba(59, 130, 246, 0.35);
            background: linear-gradient(90deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.06);
        }

        .procedure-editor-page .pw-option-stack {
            display: flex;
            flex-direction: column;
        }

        .procedure-editor-page .pw-option-card--logbook.is-expanded {
            border-bottom: none;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        .procedure-editor-page .pw-option-card-body {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            min-width: 0;
            flex: 1;
        }

        .procedure-editor-page .pw-option-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .procedure-editor-page .pw-option-icon--active {
            background: #ecfdf5;
            color: #059669;
        }

        .procedure-editor-page .pw-option-icon--result {
            background: #fffbeb;
            color: #d97706;
        }

        .procedure-editor-page .pw-option-icon--logbook {
            background: #eff6ff;
            color: #2563eb;
        }

        .procedure-editor-page .pw-option-copy {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .procedure-editor-page .pw-option-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--pw-slate-900);
            line-height: 1.25;
        }

        .procedure-editor-page .pw-option-desc {
            font-size: 0.75rem;
            color: var(--pw-slate-500);
            line-height: 1.4;
        }

        .procedure-editor-page .pw-logbook-panel {
            margin-top: 0;
            padding: 1rem;
            background: #fff;
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-top: none;
            border-radius: 0 0 0.65rem 0.65rem;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.06);
        }

        .procedure-editor-page .pw-logbook-panel-head {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            margin-bottom: 0.75rem;
        }

        .procedure-editor-page .pw-logbook-panel-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 0.45rem;
            background: #eff6ff;
            color: var(--pw-blue-600);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .procedure-editor-page .pw-logbook-panel-title {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--pw-slate-900);
        }

        .procedure-editor-page .pw-logbook-panel-desc {
            display: block;
            font-size: 0.75rem;
            color: var(--pw-slate-500);
            margin-top: 0.1rem;
        }

        .procedure-editor-page .pw-logbook-required {
            margin-left: auto;
            flex-shrink: 0;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #dc2626;
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 0.2rem 0.45rem;
            border-radius: 999px;
        }

        .procedure-editor-page .pw-logbook-field {
            min-height: 2.65rem;
            padding: 0.4rem 0.55rem;
            background: var(--pw-slate-50);
            border: 1px solid var(--pw-slate-200);
            border-radius: 0.55rem;
            gap: 0.35rem;
            cursor: text;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .procedure-editor-page .pw-logbook-field:focus-within {
            background: #fff;
            border-color: var(--pw-blue-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .procedure-editor-page .pw-logbook-search-input {
            flex: 1 1 140px;
            min-width: 120px;
            border: 0;
            outline: none;
            background: transparent;
            font-size: 0.875rem;
            color: var(--pw-slate-900);
            padding: 0.2rem 0.25rem;
        }

        .procedure-editor-page .pw-logbook-search-input::placeholder {
            color: #94a3b8;
        }

        .procedure-editor-page .pw-logbook-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.28rem 0.45rem 0.28rem 0.5rem;
            background: #fff;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 500;
            color: #1e40af;
            box-shadow: 0 1px 2px rgba(59, 130, 246, 0.06);
        }

        .procedure-editor-page .pw-logbook-chip-meta {
            color: #64748b;
            font-weight: 400;
        }

        .procedure-editor-page .pw-logbook-chip-remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.1rem;
            height: 1.1rem;
            padding: 0;
            border: none;
            border-radius: 50%;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            line-height: 1;
            transition: color 0.15s ease, background 0.15s ease;
        }

        .procedure-editor-page .pw-logbook-chip-remove:hover {
            color: #dc2626;
            background: #fee2e2;
        }

        .procedure-editor-page .pw-logbook-dropdown .search-dropdown-item.is-selected {
            background: #eff6ff;
        }

        .procedure-editor-page .pw-logbook-error {
            margin-top: 0.5rem;
            font-size: 0.78rem;
            color: #dc2626;
        }

        .procedure-editor-page .tag-badge--warning {
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }

        .procedure-editor-page .workflow-table thead th {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--pw-slate-500);
            border-bottom: 1px solid var(--pw-slate-200);
        }

        .procedure-editor-page .workflow-table .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .procedure-editor-page .workflow-table .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .procedure-editor-page .workflow-table .rm-act-btn--edit:hover {
            background: #dbeafe;
        }

        .procedure-editor-page .workflow-table .rm-act-btn--delete {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .procedure-editor-page .workflow-table .rm-act-btn--delete:hover {
            background: #fee2e2;
        }

        .procedure-editor-page .tag-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 500;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .procedure-editor-page .tag-badge--neutral {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        .procedure-editor-page .tag-badge--info {
            background: #ecfeff;
            color: #0e7490;
            border-color: #a5f3fc;
        }

        .procedure-editor-page .tag-badge--success {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }

        .cursor-pointer { cursor: pointer; }
        .hover-bg-light:hover { background-color: #f8fafc; }

        .value-type-picker {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr));
            gap: 0.5rem;
        }

        .value-type-picker.is-invalid {
            padding: 0.35rem;
            border-radius: 0.5rem;
            border: 1px solid #dc3545;
        }

        .value-type-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            margin: 0;
            padding: 0.65rem 0.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.65rem;
            background: #fff;
            font-size: 0.78rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: center;
            min-height: 4.25rem;
        }

        .value-type-option i {
            font-size: 1.25rem;
            color: #64748b;
        }

        .value-type-option:hover {
            border-color: #93c5fd;
            background: #f8fafc;
        }

        .value-type-option.active {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .value-type-option.active i {
            color: #2563eb;
        }

        .value-type-option-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .form-section-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.25rem 0 1rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .form-section-divider::before,
        .form-section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .selected-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.55rem 0.75rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 0.5rem;
            color: #166534;
            font-weight: 500;
        }

        .btn-pill-clear {
            border: none;
            background: transparent;
            color: #b91c1c;
            padding: 0;
            line-height: 1;
            cursor: pointer;
        }

        .search-dropdown {
            position: absolute;
            z-index: 1050;
            width: 100%;
            margin-top: 0.25rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
            max-height: 220px;
            overflow-y: auto;
        }

        .search-dropdown-item {
            display: block;
            width: 100%;
            text-align: left;
            border: none;
            background: transparent;
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .search-dropdown-item:hover {
            background: #f8fafc;
        }

        .search-dropdown-item:last-child {
            border-bottom: none;
        }

        .procedure-editor-page .search-dropdown-empty {
            padding: 0.75rem 0.85rem;
            font-size: 0.875rem;
        }

        .procedure-editor-page .pw-measurand-field {
            min-height: 2.5rem;
            cursor: text;
            padding: 0.35rem 0.5rem;
        }

        .procedure-editor-page .pw-measurand-input {
            outline: none;
            flex: 1 1 120px;
            min-width: 120px;
            background: transparent;
            font-size: 0.875rem;
        }

        .procedure-editor-page .config-search-picker--open {
            z-index: 1070;
        }

        .procedure-editor-page .pw-measurand-dropdown {
            position: absolute;
            z-index: 1060;
            width: 100%;
            margin-top: 0.25rem;
            max-height: 220px;
            overflow-y: auto;
        }

        [x-cloak] {
            display: none !important;
        }

        .custom-options-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .procedure-step-form .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 500;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-tag-remove {
            border: none;
            background: transparent;
            padding: 0;
            color: #94a3b8;
            line-height: 1;
            cursor: pointer;
        }

        .btn-tag-remove:hover {
            color: #dc2626;
        }

        .procedure-editor-page .pw-step-group-block {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
            background: #fff;
        }

        .procedure-editor-page .pw-step-group-block__header {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .procedure-editor-page .pw-step-group-block__header--muted {
            color: #64748b;
        }

        .pw-table-config-modal .pw-modal-content,
        .pw-step-column-modal .pw-modal-content {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.16);
            overflow: hidden;
        }

        .pw-table-config-modal .pw-modal-header,
        .pw-step-column-modal .pw-modal-header {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e2e8f0;
        }

        .pw-modal-section h6 {
            font-weight: 700;
            color: #0f172a;
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

        .procedure-editor-page .pw-inline-column-form__grid > [class*="col-"] {
            margin-bottom: 1.25rem;
            padding-top: 0.15rem;
            padding-bottom: 0.15rem;
        }

        .procedure-editor-page .pw-inline-column-form .form-label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #334155;
        }

        .procedure-editor-page .pw-inline-column-form .form-control {
            min-height: 2.5rem;
        }

        .procedure-editor-page .pw-inline-column-form__actions .btn + .btn {
            margin-left: 0.5rem;
        }

        .procedure-editor-page .pw-inline-row-form__grid > [class*="col-"] {
            margin-bottom: 0.75rem;
        }

        .procedure-editor-page .pw-inline-row-form__actions .btn + .btn {
            margin-left: 0.5rem;
        }

        .pw-table-shell {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .pw-modern-table {
            margin-bottom: 0;
        }

        .pw-modern-table thead th {
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
            font-size: 0.82rem;
        }

        .pw-modern-table tbody td {
            vertical-align: middle;
            border-color: #eef2f7;
        }

        .pw-table-config-modal .pw-modern-table .rm-act-btn,
        .pw-table-config-modal .workflow-table .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .pw-table-config-modal .pw-modern-table .rm-act-btn--edit,
        .pw-table-config-modal .workflow-table .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .pw-table-config-modal .pw-modern-table .rm-act-btn--edit:hover,
        .pw-table-config-modal .workflow-table .rm-act-btn--edit:hover {
            background: #dbeafe;
        }

        .pw-table-config-modal .pw-modern-table .rm-act-btn--delete,
        .pw-table-config-modal .workflow-table .rm-act-btn--delete {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .pw-table-config-modal .pw-modern-table .rm-act-btn--delete:hover,
        .pw-table-config-modal .workflow-table .rm-act-btn--delete:hover {
            background: #fee2e2;
        }

        .pw-table-config-modal .pw-static-table-cell--boolean {
            text-align: center;
        }

        .pw-table-config-modal .pw-static-table-cell-checkbox {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
        }

        .pw-table-config-modal .pw-static-table-cell-checkbox .form-check-input {
            float: none;
            position: static;
            width: 1.1rem;
            height: 1.1rem;
            margin: 0;
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

        .pw-table-wizard__table-wrap {
            margin-top: 0.25rem;
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

        .pw-table-wizard__hint-box > i {
            font-size: 1.1rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .pw-table-wizard__inline-form {
            background: #f8fafc;
            border-style: dashed;
        }

        .pw-table-wizard__preview-wrap {
            background: #fff;
        }

        .pw-table-wizard__preview-table thead th {
            vertical-align: bottom;
        }

        .pw-table-wizard__summary {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .pw-table-wizard__summary-item {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.85rem;
            background: #f1f5f9;
            border-radius: 999px;
            font-size: 0.82rem;
            color: #475569;
        }

        .pw-table-wizard__summary-item > i {
            color: #64748b;
        }

        .pw-table-wizard__footer {
            background: #fff;
            border-top: 1px solid #e2e8f0;
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
            border: 1px solid #a7f3d0;
        }

        .pw-col-type-badge--capture {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .pw-col-type-badge--readonly {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .pw-col-type-badge--xs {
            font-size: 0.68rem;
            padding: 0.12rem 0.4rem;
            font-weight: 500;
        }

        .pw-pill {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .pw-pill--yes {
            background: #fef3c7;
            color: #b45309;
        }

        .pw-pill--muted {
            background: #f1f5f9;
            color: #64748b;
        }

        .pw-preview-capture-placeholder {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            color: #94a3b8;
            font-style: italic;
        }

        .pw-table-wizard__col-actions {
            width: 1%;
            white-space: nowrap;
        }

        .pw-table-wizard__action-btns {
            gap: 0.25rem;
        }

        .pw-table-wizard__action-btns .rm-act-btn {
            flex-shrink: 0;
        }

        .procedure-editor-page .pw-custom-table-step-row {
            cursor: pointer;
        }

        .procedure-editor-page .pw-custom-table-step-row:hover {
            background-color: #f8fafc;
        }

        .procedure-editor-page .pw-custom-table-step-row--expanded {
            background-color: #eff6ff;
        }

        .procedure-editor-page .pw-step-expand-btn {
            color: #2563eb;
            line-height: 1;
            min-width: 1.25rem;
        }

        .procedure-editor-page .pw-step-expand-btn:hover {
            color: #1d4ed8;
        }

        .procedure-editor-page .pw-step-expand-btn .mdi {
            font-size: 1.35rem;
        }

        .procedure-editor-page .pw-custom-table-step-preview-row > td {
            background: #f8fafc;
            border-top: none !important;
            box-shadow: inset 0 3px 6px -4px rgba(15, 23, 42, 0.08);
        }

        .procedure-editor-page .pw-step-table-preview {
            padding: 0.75rem 1rem 1rem 2.5rem;
        }

        .procedure-editor-page .pw-step-table-preview__table {
            background: #fff;
            border-radius: 8px;
        }

        .procedure-editor-page .pw-step-row-actions {
            gap: 0.25rem;
        }

        .procedure-editor-page .workflow-table .rm-act-btn--view {
            border: 1px solid #c7d2fe;
            color: #4338ca;
            background: #eef2ff;
        }

        .procedure-editor-page .workflow-table .rm-act-btn--view:hover {
            background: #e0e7ff;
        }

        @media (max-width: 768px) {
            .pw-table-wizard__steps {
                flex-direction: column;
            }

            .pw-table-wizard__connector {
                display: none;
            }

            .pw-table-wizard__panel-head {
                flex-direction: column;
            }
        }

        .procedure-editor-page .drag-handle:hover {
            background-color: #f8fafc;
            cursor: move;
            border-radius: 0.35rem;
        }

        .procedure-editor-page .sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef;
        }

        .procedure-editor-page .sortable-chosen {
            background-color: #f8fafc;
        }

        .procedure-editor-page .sortable-drag {
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
        }

        @media (max-width: 576px) {
            .procedure-editor-page .pw-modal {
                padding: 0.75rem;
                align-items: flex-end;
            }

            .procedure-editor-page .pw-modal-dialog--lg,
            .procedure-editor-page .pw-modal-dialog--xl {
                max-width: 100%;
            }

            .procedure-editor-page .pw-tabs {
                padding: 0.75rem 0.75rem 0;
            }

            .procedure-editor-page .pw-tab span {
                display: none;
            }

            .procedure-editor-page .pw-tab {
                padding: 0.5rem 0.65rem;
            }
        }
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

        function collectOrderedIds(pane, rowSelector, attr) {
            if (!pane) {
                return [];
            }
            return Array.from(pane.querySelectorAll(rowSelector)).map(function (row) {
                return row.getAttribute(attr);
            }).filter(Boolean);
        }

        function callLivewireOnPane(pane, method, payload) {
            if (!pane || !window.Livewire) {
                return;
            }
            var host = pane.closest('[wire\\:id]');
            var wireId = host ? host.getAttribute('wire:id') : null;
            if (!wireId) {
                return;
            }
            var component = window.Livewire.find(wireId);
            if (component) {
                component.call(method, payload);
            }
        }

        function initializeStepSortable() {
            if (typeof Sortable === 'undefined') {
                return;
            }
            var pane = document.getElementById('steps-tab-pane');
            document.querySelectorAll('#steps-tab-pane tbody.sortable-steps-group').forEach(function (tbody) {
                if (tbody.sortableInstance) {
                    tbody.sortableInstance.destroy();
                }
                tbody.sortableInstance = Sortable.create(tbody, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    group: 'procedure-steps',
                    onEnd: function () {
                        var stepIds = collectOrderedIds(pane, 'tr.sortable-row[data-step-id]', 'data-step-id');
                        callLivewireOnPane(pane, 'updateStepOrder', stepIds);
                    }
                });
            });
        }

        function initializeConfigFieldSortable() {
            if (typeof Sortable === 'undefined') {
                return;
            }
            var pane = document.getElementById('configurable-fields-tab-pane');
            document.querySelectorAll('#configurable-fields-tab-pane tbody[id^="sortable-config-fields"]').forEach(function (tbody) {
                if (tbody.sortableInstance) {
                    tbody.sortableInstance.destroy();
                }
                tbody.sortableInstance = Sortable.create(tbody, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    group: 'procedure-config-fields',
                    onEnd: function () {
                        var fieldIds = collectOrderedIds(pane, 'tr.sortable-row[data-config-field-id]', 'data-config-field-id');
                        callLivewireOnPane(pane, 'updateConfigFieldOrder', fieldIds);
                    }
                });
            });
        }

        function configSearchSelectFromEl() {
            return {
                open: false,
                search: '',
                selected: '',
                options: [],
                wireModel: '',
                placeholderText: 'Search...',
                emptyText: 'No matches found.',
                pickerId: '',
                init() {
                    var self = this;
                    this.loadConfigFromElement();
                    this.refreshFromWire();
                    this.$nextTick(function () {
                        self.loadConfigFromElement();
                        self.refreshFromWire();
                    });
                    this._onDocumentMouseDown = function (event) {
                        if (!self.open) {
                            return;
                        }
                        if (!self.$el.contains(event.target)) {
                            self.close();
                        }
                    };
                    this._onPickerOpen = function (event) {
                        if (event.detail !== self.pickerId) {
                            self.close();
                        }
                    };
                    window.addEventListener('config-search-picker-open', this._onPickerOpen);
                },
                loadConfigFromElement() {
                    try {
                        this.options = JSON.parse(this.$el.getAttribute('data-picker-options') || '[]');
                    } catch (e) {
                        this.options = [];
                    }
                    this.wireModel = this.$el.getAttribute('data-wire-model') || '';
                    this.placeholderText = this.$el.getAttribute('data-placeholder') || 'Search...';
                    this.emptyText = this.$el.getAttribute('data-empty-message') || 'No matches found.';
                    this.pickerId = this.$el.getAttribute('wire:key') || '';
                },
                readWireValue() {
                    var wireValue = this.$wire.get(this.wireModel);
                    return wireValue === null || wireValue === undefined ? '' : String(wireValue);
                },
                refreshFromWire() {
                    this.selected = this.readWireValue();
                    this.syncSearchFromSelected();
                },
                selectedLabel() {
                    var found = this.options.find(function (option) {
                        return String(option.value) === String(this.selected);
                    }.bind(this));
                    return found ? found.label : '';
                },
                filteredOptions() {
                    var query = String(this.search || '').toLowerCase().trim();
                    if (query === '') {
                        return this.options;
                    }
                    return this.options.filter(function (option) {
                        return String(option.label).toLowerCase().includes(query);
                    });
                },
                bindOutsideClose() {
                    document.addEventListener('mousedown', this._onDocumentMouseDown, true);
                },
                unbindOutsideClose() {
                    document.removeEventListener('mousedown', this._onDocumentMouseDown, true);
                },
                openDropdown(refName) {
                    window.dispatchEvent(new CustomEvent('config-search-picker-open', { detail: this.pickerId }));
                    // Show full list on open; filtering starts only after user types.
                    this.search = '';
                    this.open = true;
                    this.bindOutsideClose();
                    var self = this;
                    this.$nextTick(function () {
                        if (self.$refs && self.$refs[refName]) {
                            self.$refs[refName].focus();
                        }
                    });
                },
                close() {
                    this.open = false;
                    this.unbindOutsideClose();
                    this.syncSearchFromSelected();
                },
                choose(value) {
                    var normalized = value === null || value === undefined ? '' : String(value);
                    this.selected = normalized;
                    this.$wire.set(this.wireModel, normalized);
                    this.close();
                },
                syncSearchFromSelected() {
                    this.search = this.selectedLabel() || '';
                },
            };
        }

    </script>
</div>
