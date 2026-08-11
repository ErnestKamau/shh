<div class="container-fluid lab-surface-theme ls-admin-page element-manager-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-flask text-primary"></i>
                                Analysis Parameter Management
                            </h2>
                            <p class="text-muted mb-0">Manage analysis parameters for this analysis type</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center element-header-actions">
                            <button type="button" wire:click="openImportModal" class="btn btn-outline-info element-add-btn">
                                <i class="mdi mdi-file-upload-outline"></i> Import / Template
                            </button>
                            <button wire:click="showCreateElementModal" class="btn btn-outline-primary element-add-btn">
                                <i class="mdi mdi-plus"></i> Add Parameter
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-success element-add-btn"
                                wire:click="$dispatch('open-sync-worksheets-modal')"
                            >
                                <i class="mdi mdi-sync"></i> Sync Worksheets
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert element-manager-page-alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Disclaimer Alert -->
    @if($this->analysisType && $this->analysisType->has_no_result)
        <div class="alert alert-warning fade show" role="alert">
            <i class="mdi mdi-alert-circle"></i> <strong>Disclaimer:</strong> This Analysis has been marked as <strong>Has No Result Captured</strong>.
        </div>
    @endif

    <!-- View Tabs -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 quotation-stage-tabs-card">
                <div class="quotation-stage-tabs px-3 px-md-4 py-3">
                    <button
                        type="button"
                        class="quotation-stage-tab {{ $activeTab === 'parameters' ? 'is-active' : '' }}"
                        wire:click="switchTab('parameters')"
                    >
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <span>Parameters</span>
                        <span class="quotation-stage-tab__count">{{ $this->parametersTabCount }}</span>
                    </button>
                    <button
                        type="button"
                        class="quotation-stage-tab {{ $activeTab === 'parameter_groups' ? 'is-active' : '' }}"
                        wire:click="switchTab('parameter_groups')"
                    >
                        <i class="mdi mdi-folder-outline"></i>
                        <span>Parameter Groups</span>
                        <span class="quotation-stage-tab__count">{{ $this->parameterGroupsTabCount }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by analyte name...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Type of Analysis</label>
                                <select wire:model.live="typeOfAnalysisFilter" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($this->filterTypesOfAnalysis as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Parameter Group</label>
                                <select wire:model.live="parameterGroupFilter" class="form-select">
                                    <option value="">All Groups</option>
                                    <option value="none">Ungrouped</option>
                                    @foreach($this->filterParameterGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <div class="tag-select-container status-filter-container">
                                    <div class="tag-select-input status-filter-input">
                                        <select wire:model.live="statusFilter" class="form-select tag-select-native">
                                            <option value="">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
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

    @if($activeTab === 'parameters')
        <!-- Parameters Table -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-body">
                        @if(count($selectedElementIds) > 0)
                            <div class="d-flex flex-wrap align-items-center mb-3 p-3 bg-light rounded">
                                <span class="text-muted mr-3">{{ count($selectedElementIds) }} selected</span>
                                <button type="button" class="btn btn-sm btn-primary mr-2" wire:click="openBulkAssignModal">
                                    <i class="mdi mdi-folder-move-outline"></i> Assign to Group
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('selectedElementIds', [])">
                                    Clear selection
                                </button>
                            </div>
                        @endif

                        @if($this->elements->count() > 0)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $this->elements->firstItem() ?? 0 }} to {{ $this->elements->lastItem() ?? 0 }} of {{ $this->elements->total() }} entries
                                    </span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                    <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                        @foreach($perPageOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover elements-data-table" id="elements-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;" class="text-center">
                                                @php
                                                    $pageIds = $this->elements->pluck('id')->map(fn ($id) => (string) $id)->all();
                                                    $allPageSelected = $pageIds !== [] && count(array_intersect($pageIds, $selectedElementIds)) === count($pageIds);
                                                @endphp
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    wire:click="toggleSelectAllOnPage"
                                                    @checked($allPageSelected)
                                                >
                                            </th>
                                            <th class="em-actions-col" style="min-width: 132px;">
                                                <span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 12px; font-weight: 600;">
                                                    <i class="mdi mdi-drag-vertical" title="Drag to reorder"></i>
                                                    <span>Actions</span>
                                                </span>
                                            </th>
                                            <th>Level</th>
                                            <th>Analyte</th>
                                            <th>Type of Analysis</th>
                                            <th>Parameter Group</th>
                                            <th>Method</th>
                                            <th>Equipment</th>
                                            <th>Operator</th>
                                            <th>Reporting Unit</th>
                                            <th>LOD</th>
                                            <th>LOQ</th>
                                            <th title="Turnaround time (days)">TAT</th>
                                            <th>Calculated</th>
                                            <th>Method Sequence</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-elements">
                                        @foreach($this->elements as $element)
                                            @include('livewire.analysis.partials.element-table-row', [
                                                'element' => $element,
                                                'showCheckbox' => true,
                                                'showDragHandle' => true,
                                                'selectedElementIds' => $selectedElementIds,
                                            ])
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $this->elements->links() }}
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No parameters found</h5>
                                <p class="text-muted">Start by adding your first analysis parameter.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        @php
            $groupedView = $this->groupedElementsView;
            $groupBlocks = $groupedView['blocks'];
        @endphp

        <!-- Parameter Groups View -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-body">
                        @if(count($groupBlocks) > 0)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">{{ count($groupBlocks) }} group(s) · {{ collect($groupBlocks)->sum(fn ($block) => $block['elements']->count()) }} grouped parameter(s)</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover elements-data-table" id="elements-grouped-table">
                                    <thead>
                                        <tr>
                                            <th class="em-actions-col" style="min-width: 100px;">Actions</th>
                                            <th>Level</th>
                                            <th>Analyte</th>
                                            <th>Type of Analysis</th>
                                            <th>Parameter Group</th>
                                            <th>Method</th>
                                            <th>Equipment</th>
                                            <th>Operator</th>
                                            <th>Reporting Unit</th>
                                            <th>LOD</th>
                                            <th>LOQ</th>
                                            <th title="Turnaround time (days)">TAT</th>
                                            <th>Calculated</th>
                                            <th>Method Sequence</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($groupBlocks as $block)
                                            @php
                                                $group = $block['group'];
                                                $isExpanded = in_array($group->id, $expandedGroupIds, true);
                                            @endphp
                                            <tr class="element-parameter-group-row" wire:key="param-group-header-{{ $group->id }}">
                                                <td colspan="15" class="element-parameter-group-cell">
                                                    <button
                                                        type="button"
                                                        class="btn btn-link p-0 text-decoration-none element-parameter-group-toggle"
                                                        wire:click="toggleGroup('{{ $group->id }}')"
                                                    >
                                                        <i class="mdi mdi-chevron-{{ $isExpanded ? 'down' : 'right' }}"></i>
                                                        <span class="element-parameter-group-label">{{ $group->name }}</span>
                                                        <span class="badge bg-light text-dark border ml-1">{{ $block['elements']->count() }}</span>
                                                    </button>
                                                </td>
                                            </tr>
                                            @if($isExpanded)
                                                @foreach($block['elements'] as $element)
                                                    @include('livewire.analysis.partials.element-table-row', [
                                                        'element' => $element,
                                                        'showCheckbox' => false,
                                                        'showDragHandle' => false,
                                                        'selectedElementIds' => [],
                                                    ])
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-folder-plus-outline text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No parameter groups yet</h5>
                                <p class="text-muted mb-3">
                                    Go to the <strong>Parameters</strong> tab, select parameters, then use <strong>Assign to Group</strong>.
                                </p>
                                <button type="button" class="btn btn-primary btn-sm" wire:click="switchTab('parameters')">
                                    <i class="mdi mdi-format-list-bulleted"></i> Go to Parameters
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Assign Modal -->
    @if($showBulkAssignModal)
        <div class="modal fade show d-block element-manager-modal" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-folder-move-outline"></i> Assign to Parameter Group
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeBulkAssignModal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Assign {{ count($selectedElementIds) }} selected parameter(s) to a group.</p>

                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Action</label>
                            <select wire:model.live="bulkAssignMode" class="form-select">
                                <option value="existing">Assign to existing group</option>
                                <option value="new">Create new group</option>
                                <option value="remove">Remove from group</option>
                            </select>
                        </div>

                        @if($bulkAssignMode === 'existing')
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Parameter group</label>
                                <select wire:model="bulkAssignGroupId" class="form-select @error('bulkAssignGroupId') is-invalid @enderror">
                                    <option value="">Select a group...</option>
                                    @foreach($this->activeParameterGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                @error('bulkAssignGroupId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        @elseif($bulkAssignMode === 'new')
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">New group name</label>
                                <input type="text" wire:model="bulkAssignNewGroupName" class="form-control @error('bulkAssignNewGroupName') is-invalid @enderror" placeholder="e.g. Contaminants">
                                @error('bulkAssignNewGroupName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                Selected parameters will be removed from their current groups.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkAssignModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="applyBulkAssign">
                            <i class="mdi mdi-content-save"></i> Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Parameter Modal -->
    @if($showElementModal)
        <div class="modal fade show d-block element-manager-modal" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content" style="overflow: visible;">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingElement ? 'pencil' : 'plus' }}"></i>
                            {{ $editingElement ? 'Edit' : 'Create' }} Analysis Parameter
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeElementModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form id="analysis-element-form" wire:submit.prevent="saveElement">
                            @if($errors->any())
                                <div class="alert alert-danger mb-3" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif
                            @if($this->analysisType->procedureWorksheet)
                                <div class="row mb-3">
                                    <div class="col-12">
                                         <div class="alert alert-info border-info">
                                             <div class="d-flex align-items-center">
                                                 <i class="mdi mdi-file-document-outline fs-4 me-2"></i>
                                                 <div>
                                                     <h6 class="mb-0">Procedure Worksheet Assigned</h6>
                                                     <small>Parameters will be linked to <strong>{{ $this->analysisType->procedureWorksheet->name }}</strong></small>
                                                 </div>
                                             </div>
                                         </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-flask-outline text-primary"></i> Analyte <span class="text-danger">*</span>
                                        </label>
                                        <div class="tag-select-container" wire:click="openAnalyteDropdown">
                                            <div class="tag-select-input">
                                                <!-- Display selected analyte -->
                                                @if($selectedAnalyteName)
                                                    <span class="tag-badge">
                                                        {{ $selectedAnalyteName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearAnalyte"></i>
                                                    </span>
                                                @else
                                                    <input type="text" 
                                                           wire:model.live.debounce.250ms="analyteSearch" 
                                                           wire:focus="openAnalyteDropdown"
                                                           class="tag-input" 
                                                           placeholder="Search analytes..."
                                                           autocomplete="off">
                                                @endif
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showAnalyteDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($filteredAnalytes as $analyte)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectAnalyte('{{ $analyte->id }}')">
                                                            {{ $analyte->name }} ({{ $analyte->code }})
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No analytes found</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.analyte_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-shape-outline text-primary"></i> Type of Analysis
                                        </label>
                                        <div class="tag-select-container" wire:click="openTypeOfAnalysisDropdown">
                                            <div class="tag-select-input">
                                                @if($selectedTypeOfAnalysisName)
                                                    <span class="tag-badge">
                                                        {{ $selectedTypeOfAnalysisName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearTypeOfAnalysis"></i>
                                                    </span>
                                                @else
                                                    <input type="text"
                                                           wire:model.live.debounce.250ms="typeOfAnalysisSearch"
                                                           wire:focus="openTypeOfAnalysisDropdown"
                                                           class="tag-input"
                                                           placeholder="Search types of analysis..."
                                                           autocomplete="off">
                                                @endif
                                            </div>

                                            @if($showTypeOfAnalysisDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($filteredTypesOfAnalysis as $type)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectTypeOfAnalysis('{{ $type->id }}')">
                                                            {{ $type->name }}
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No types of analysis found</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.type_of_analysis_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-test-tube text-success"></i> Method
                                        </label>
                                        <div class="tag-select-container" wire:click="openMethodDropdown">
                                            <div class="tag-select-input">
                                                <!-- Display selected method -->
                                                @if($selectedMethodName)
                                                    <span class="tag-badge">
                                                        {{ $selectedMethodName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearMethod"></i>
                                                    </span>
                                                @else
                                                    <input type="text" 
                                                           wire:model.live.debounce.250ms="methodSearch" 
                                                           wire:focus="openMethodDropdown"
                                                           class="tag-input" 
                                                           placeholder="Search methods..."
                                                           autocomplete="off">
                                                @endif
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showMethodDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($filteredMethods as $method)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectMethod('{{ $method->id }}')">
                                                            {{ $method->name }}
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No methods found</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.method') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-account text-info"></i> Operator
                                        </label>
                                        <div class="tag-select-container" wire:click="openOperatorDropdown">
                                            <div class="tag-select-input">
                                                @if($selectedOperatorName)
                                                    <span class="tag-badge">
                                                        {{ $selectedOperatorName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearOperator"></i>
                                                    </span>
                                                @else
                                                    <input type="text" 
                                                           wire:model.live.debounce.250ms="operatorSearch" 
                                                           wire:focus="openOperatorDropdown"
                                                           class="tag-input" 
                                                           placeholder="Search operators..."
                                                           autocomplete="off">
                                                @endif
                                            </div>
                                            
                                            @if($showOperatorDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($filteredOperators as $operator)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectOperator('{{ $operator->id }}')">
                                                            {{ $operator->name }}
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No operators found</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.operator_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-cog text-warning"></i> Equipment
                                        </label>
                                        <div class="tag-select-container" wire:click="openEquipmentDropdown">
                                            <div class="tag-select-input">
                                                @if($selectedEquipmentName)
                                                    <span class="tag-badge">
                                                        {{ $selectedEquipmentName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearEquipment"></i>
                                                    </span>
                                                @else
                                                    <input type="text" 
                                                           wire:model.live.debounce.250ms="equipmentSearch" 
                                                           wire:focus="openEquipmentDropdown"
                                                           class="tag-input" 
                                                           placeholder="Search equipment..."
                                                           autocomplete="off">
                                                @endif
                                            </div>
                                            
                                            @if($showEquipmentDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($filteredEquipment as $equipment)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectEquipment('{{ $equipment->id }}')">
                                                            {{ $equipment->name }}
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No equipment found</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.equipment_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-scale-balance text-primary"></i> Reporting Unit
                                        </label>
                                        <div class="tag-select-container" wire:click="openReportingUnitDropdown">
                                            <div class="tag-select-input">
                                                <!-- Display selected unit -->
                                                @if($elementForm['reporting_unit'])
                                                    <span class="tag-badge">
                                                        {{ $elementForm['reporting_unit'] }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('elementForm.reporting_unit', '')"></i>
                                                    </span>
                                                @else
                                                    <input type="text" 
                                                           wire:model.live.debounce.250ms="reportingUnitSearch" 
                                                           wire:focus="openReportingUnitDropdown"
                                                           class="tag-input" 
                                                           placeholder="Search reporting units..."
                                                           autocomplete="off">
                                                @endif
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            @if($showReportingUnitDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($filteredReportingUnits as $unit)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectReportingUnit('{{ $unit->name }}')">
                                                            {{ $unit->name }}
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No reporting units found</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.reporting_unit') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Decimal Places</label>
                                        <input type="number" wire:model="elementForm.decimal_places" class="form-control" min="0" max="10">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Level</label>
                                        <input type="number" wire:model="elementForm.level" class="form-control" min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">LOD (Limit of Detection)</label>
                                        <input type="number" step="0.0000001" wire:model="elementForm.lod" class="form-control" placeholder="Limit of Detection">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">LOQ (Limit of Quantification)</label>
                                        <input type="number" step="0.0000001" wire:model="elementForm.hod" class="form-control" placeholder="Limit of Quantification">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">TAT (days)</label>
                                        <input type="number" wire:model="elementForm.reporting_time" class="form-control" min="0" placeholder="e.g., 7">
                                        <small class="form-text text-muted">Leave blank or 0 to inherit the analysis type TAT.</small>
                                        @error('elementForm.reporting_time') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label class="form-label">
                                            <i class="mdi mdi-view-grid-outline text-primary"></i> Lab Section
                                        </label>
                                        <div class="tag-select-container" wire:click="openLabSectionDropdown">
                                            <div class="tag-select-input">
                                                @if($selectedLabSectionName)
                                                    <span class="tag-badge">
                                                        {{ $selectedLabSectionName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearLabSection"></i>
                                                    </span>
                                                @endif
                                                <input type="text"
                                                       wire:model.live.debounce.250ms="labSectionSearch"
                                                       wire:focus="openLabSectionDropdown"
                                                       class="tag-input"
                                                       placeholder="{{ $selectedLabSectionName ? '' : 'Search lab sections...' }}"
                                                       autocomplete="off"
                                                       @if($selectedLabSectionName) style="min-width: 8rem;" @endif>
                                            </div>
                                            @if($showLabSectionDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($filteredLabSections as $section)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectLabSection('{{ $section->id }}')">
                                                            {{ $section->code ? $section->code.' — ' : '' }}{{ $section->name }}
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No lab sections found</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('elementForm.lab_section_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Defaults from the Analysis Type lab section.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Significant Figures</label>
                                        <input type="number" wire:model="elementForm.significant_figures" class="form-control" min="1" max="10">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.active" class="form-check-input" id="element_active">
                                            <label class="form-check-label" for="element_active">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.non_detectable" class="form-check-input" id="non_detectable">
                                            <label class="form-check-label" for="non_detectable">Non-detectable</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.non_accredited" class="form-check-input" id="non_accredited">
                                            <label class="form-check-label" for="non_accredited">Non-accredited</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.sub_contracted" class="form-check-input" id="sub_contracted">
                                            <label class="form-check-label" for="sub_contracted">Sub-contracted</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.show_on_report" class="form-check-input" id="show_on_report">
                                            <label class="form-check-label" for="show_on_report">Show on Report</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="elementForm.remark_is_manual" class="form-check-input" id="remark_is_manual">
                                            <label class="form-check-label" for="remark_is_manual">Remark is Manual</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Worksheet Calculation Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary">
                                                <i class="mdi mdi-calculator"></i> Worksheet Calculation
                                            </h6>
                                            <div class="form-group mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           wire:model.live="elementForm.result_is_calculated" 
                                                           class="form-check-input" 
                                                           id="result_is_calculated">
                                                    <label class="form-check-label" for="result_is_calculated">
                                                        Result is Calculated
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            @if($elementForm['result_is_calculated'] ?? false)
                                                <div class="form-group mb-3">
                                                    <label class="form-label">
                                                        <i class="mdi mdi-calculator text-primary"></i> Formulars
                                                    </label>
                                                    <div class="tag-select-container" wire:click="openFormularDropdown">
                                                        <div class="tag-select-input">
                                                            <!-- Display selected formular -->
                                                            @if($selectedFormularName)
                                                                <span class="tag-badge">
                                                                    {{ $selectedFormularName }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearFormular"></i>
                                                                </span>
                                                            @endif
                                                            
                                                            <!-- Search Input -->
                                                            <input type="text" 
                                                                   wire:model.live.debounce.250ms="formularSearch" 
                                                                   wire:focus="openFormularDropdown"
                                                                   class="tag-input" 
                                                                   placeholder="{{ $selectedFormularName ? '' : 'Search formulars...' }}"
                                                                   autocomplete="off">
                                                        </div>
                                                        
                                                        <!-- Dropdown -->
                                                        @if($showFormularDropdown)
                                                            <div class="tag-dropdown">
                                                                @forelse($filteredFormulars as $formular)
                                                                    <div class="tag-dropdown-item" wire:click.stop="selectFormular('{{ $formular->id }}')">
                                                                        {{ $formular->name }}
                                                                    </div>
                                                                @empty
                                                                    <div class="tag-dropdown-item text-muted">No formulars found</div>
                                                                @endforelse
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @error('elementForm.formular_id') 
                                                        <span class="text-danger">{{ $message }}</span> 
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        Select the formular to use for calculating this result
                                                    </small>
                                                </div>
                                            @endif

                                            <div class="form-group mb-0 mt-3">
                                                <label class="form-label">
                                                    <i class="mdi mdi-table-edit text-primary"></i> Log entry worksheet
                                                </label>
                                                <div class="tag-select-container" wire:click="openLogEntryWorksheetDropdown">
                                                    <div class="tag-select-input">
                                                        @if($selectedLogEntryWorksheetName)
                                                            <span class="tag-badge">
                                                                {{ $selectedLogEntryWorksheetName }}
                                                                <i class="mdi mdi-close-circle" wire:click.stop="clearLogEntryWorksheet"></i>
                                                            </span>
                                                        @endif
                                                        <input type="text"
                                                               wire:model.live.debounce.250ms="logEntryWorksheetSearch"
                                                               wire:focus="openLogEntryWorksheetDropdown"
                                                               class="tag-input"
                                                               placeholder="{{ $selectedLogEntryWorksheetName ? '' : 'Search log entry worksheets...' }}"
                                                               autocomplete="off">
                                                    </div>
                                                    @if($showLogEntryWorksheetDropdown)
                                                        <div class="tag-dropdown">
                                                            @forelse($filteredLogEntryWorksheets as $lew)
                                                                <div class="tag-dropdown-item" wire:click.stop="selectLogEntryWorksheet('{{ $lew->id }}')">
                                                                    {{ $lew->name }}
                                                                </div>
                                                            @empty
                                                                <div class="tag-dropdown-item text-muted">No log entry worksheets found</div>
                                                            @endforelse
                                                        </div>
                                                    @endif
                                                </div>
                                                @error('elementForm.log_entry_worksheet_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                <small class="form-text text-muted">Optional dynamic log table for batch worksheet capture.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Stage header workflow -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary">
                                                <i class="mdi mdi-timeline-check"></i> Stage Headers
                                            </h6>
                                            <div class="form-group mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                           wire:model.live="elementForm.has_method_sequence"
                                                           class="form-check-input"
                                                           id="has_method_sequence">
                                                    <label class="form-check-label" for="has_method_sequence">
                                                        Use stage header workflow
                                                    </label>
                                                </div>
                                            </div>

                                            @if($elementForm['has_method_sequence'] ?? false)
                                                <div class="form-group mb-3 element-stage-header-field">
                                                    <label class="form-label" for="element-stage-header-select">Stage header</label>
                                                    <x-searchable-select
                                                        wire:model.live="elementForm.stage_header_id"
                                                        :options="collect($stageHeaders)->map(fn($stageHeader) => ['id' => $stageHeader->id, 'name' => $stageHeader->name])"
                                                        placeholder="Search stage headers..."
                                                        empty-label="Select stage header..."
                                                        wire:key="stage-headers-{{ $elementForm['analyte_id'] ?? '0' }}-{{ $elementForm['method'] ?? '0' }}-{{ count($stageHeaders ?? []) }}"
                                                    />
                                                    @error('elementForm.stage_header_id')
                                                        <span class="text-danger d-block">{{ $message }}</span>
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        Available stage headers for the selected analyte and method.
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeElementModal">Cancel</button>
                        <button type="submit" form="analysis-element-form" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveElement">
                            <i class="mdi mdi-content-save"></i> Save Parameter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Parameters Modal -->
    @if($showImportModal)
        <div class="modal fade show d-block element-manager-modal" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-upload-outline"></i> Import / Template
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeImportModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <i class="mdi mdi-information-outline"></i>
                            Download the template, fill it in, then upload to import parameters into
                            <strong>{{ $this->analysisType->name ?? 'this analysis type' }}</strong>.
                            <div class="mt-2 small">
                                <strong>Required:</strong>
                                <code>parameter</code>, <code>method</code>
                                &nbsp;·&nbsp;
                                <strong>Optional:</strong>
                                <code>type_of_analysis</code>, <code>parameter_group</code>,
                                <code>reporting_unit</code>, <code>accredited</code>
                                (1/Yes = accredited),
                                <code>lab_section</code>, <code>operator</code>,
                                <code>tat</code>, <code>equipment</code>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-secondary" wire:click="downloadImportTemplate" wire:loading.attr="disabled" wire:target="downloadImportTemplate">
                                <span wire:loading.remove wire:target="downloadImportTemplate">
                                    <i class="mdi mdi-download"></i> Download Template
                                </span>
                                <span wire:loading wire:target="downloadImportTemplate">
                                    <span class="spinner-border spinner-border-sm" role="status"></span> Preparing...
                                </span>
                            </button>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label fw-bold" for="parameter-import-file">Excel file (.xlsx / .xls)</label>
                            <input
                                id="parameter-import-file"
                                type="file"
                                class="form-control @error('importFile') is-invalid @enderror"
                                wire:model="importFile"
                                accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                            >
                            @error('importFile')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div wire:loading wire:target="importFile" class="small text-muted mt-2">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Uploading file...
                            </div>
                            @if($importFile)
                                <div class="small text-success mt-2">
                                    <i class="mdi mdi-check-circle"></i> {{ $importFile->getClientOriginalName() }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeImportModal" wire:loading.attr="disabled" wire:target="importParameters">
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="importParameters"
                            wire:loading.attr="disabled"
                            wire:target="importParameters"
                        >
                            <span wire:loading.remove wire:target="importParameters">
                                <i class="mdi mdi-upload"></i> Import
                            </span>
                            <span wire:loading wire:target="importParameters">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Importing...
                            </span>
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

.element-add-btn {
    border-radius: 8px;
    padding: 0.48rem 1rem;
}

.element-header-actions {
    gap: 0.75rem;
}

/* Parameters table: gray header, white body rows */
#elements-table.elements-data-table {
    margin-bottom: 0;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
}

#elements-table.elements-data-table thead th {
    background-color: rgba(0, 0, 0, 0.03);
    color: #495057;
    font-weight: 600;
    font-size: 0.875rem;
    border-bottom: 1px solid #dee2e6;
    border-top: none;
    vertical-align: middle;
    padding: 0.75rem 0.65rem;
}

#elements-table.elements-data-table tbody tr {
    background-color: #fff !important;
}

#elements-table.elements-data-table tbody tr:hover {
    background-color: #f8f9fa !important;
}

#elements-table.elements-data-table tbody td {
    background-color: inherit;
    border-color: #e9ecef;
    vertical-align: middle;
}

/* Actions column: drag + rm-act-btn (matches personnel role manager) */
#elements-table.elements-data-table .em-actions-cell {
    white-space: nowrap;
    vertical-align: middle;
}

#elements-table.elements-data-table .em-actions-inner {
    gap: 6px;
}

#elements-table.elements-data-table .rm-act-btn {
    border-radius: 7px;
    padding: 4px 8px;
    margin-right: 0;
    font-size: 12px;
}

#elements-table.elements-data-table .rm-act-btn--edit {
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    background: #eff6ff;
}

#elements-table.elements-data-table .rm-act-btn--edit:hover {
    background: #dbeafe;
    border-color: #93c5fd;
}

#elements-table.elements-data-table .rm-act-btn--delete {
    border: 1px solid #fecdd3;
    color: #e11d48;
    background: #fff5f7;
}

#elements-table.elements-data-table .rm-act-btn--delete:hover {
    background: #ffe4e6;
    border-color: #fda4af;
}

/* Soft pills: level, calculated, method sequence */
.em-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.25;
    border: 1px solid transparent;
}

.em-pill .mdi {
    font-size: 14px;
    line-height: 1;
}

.em-pill--level {
    background: #eff6ff;
    color: #1d40af;
    border-color: #bfdbfe;
}

.em-pill--calc.em-pill--on {
    background: #ecfeff;
    color: #0e7490;
    border-color: #a5f3fc;
}

.em-pill--calc.em-pill--off {
    background: #f8fafc;
    color: #64748b;
    border-color: #e2e8f0;
}

.em-pill--sequence.em-pill--on {
    background: #f5f3ff;
    color: #5b21b6;
    border-color: #ddd6fe;
}

.em-pill--sequence.em-pill--off {
    background: #f8fafc;
    color: #64748b;
    border-color: #e2e8f0;
}

.em-pill--ver {
    margin-top: 4px;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
}

.em-pill--ver-active {
    background: #ecfdf5;
    color: #166534;
    border-color: #bbf7d0;
}

.em-pill--ver-latest {
    background: #fffbeb;
    color: #b45309;
    border-color: #fde68a;
}

.em-pill--status-active {
    background: #ecfdf5;
    color: #166534;
    border-color: #bbf7d0;
}

.em-pill--status-inactive {
    background: #f8fafc;
    color: #64748b;
    border-color: #e2e8f0;
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden;
}

/* Ensure modal is properly positioned and scrollable */
.element-manager-modal.modal {
    overflow-y: auto;
}

.element-manager-modal .modal-dialog-scrollable {
    max-height: calc(100vh - 1rem);
    overflow-y: auto;
    overflow-x: visible;
}

.element-manager-modal .modal-dialog-scrollable .modal-content {
    overflow: visible;
}

/* Visible overflow so Stage header / tag dropdowns are not clipped */
.element-manager-modal .modal-dialog-scrollable .modal-body {
    overflow: visible;
    max-height: none;
}

/* Stage header list must stack above modal body content */
.element-manager-modal .element-stage-header-field {
    position: relative;
    z-index: 1060;
    overflow: visible;
}

.element-manager-modal .element-stage-header-field .searchable-dropdown-wrapper {
    position: relative;
    z-index: 1060;
    overflow: visible;
}

.element-manager-modal .element-stage-header-field .dropdown-list {
    z-index: 2000 !important;
    position: absolute;
}

.element-manager-modal .tag-dropdown {
    z-index: 2000;
}

/* Smooth scrolling for modal content */
.modal-body {
    scroll-behavior: smooth;
}

/* Ensure modal backdrop doesn't interfere with scrolling */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0,0,0,0.5);
}
</style>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('livewire:init', () => {
    // Prevent body scroll when modal opens
    Livewire.on('element-modal-opened', () => {
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
    });
    
    // Restore body scroll when modal closes
    Livewire.on('element-modal-closed', () => {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    });

    Livewire.on('worksheets-synced', () => {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';

        window.requestAnimationFrame(() => {
            const alert = document.querySelector('.element-manager-page-alert');
            if (alert) {
                alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });
    
    // Initialize drag and drop functionality
    initializeSortable();
});

// Initialize sortable when Livewire updates the DOM
document.addEventListener('livewire:updated', () => {
    initializeSortable();
});

function initializeSortable() {
    const sortableElement = document.getElementById('sortable-elements');
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
                
                const elementIds = Array.from(sortableElement.children).map(row => {
                    return row.getAttribute('data-element-id');
                }).filter(Boolean);
                
                console.log('New element order:', elementIds);
                
                // Send the new order to Livewire
                @this.call('updateElementOrder', elementIds);
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
// Utility: Markdown formatting with marked, always safe
function formatMessage(text) {
    if (!text) return '';
    try {
        if (window.marked) {
            const rawHtml = window.marked.parse(text || '');
            return DOMPurify.sanitize(rawHtml);
        }
        return text || '';
    } catch (e) {
        return text || '';
    }
}

// Close ElementManager tag-select dropdowns when clicking outside (this component only).
document.addEventListener('click', function (e) {
    if (e.target.closest('.tag-select-container')) {
        return;
    }

    // Do not interfere with Livewire action buttons (e.g. Save/Cancel).
    if (e.target.closest('.modal-footer, .btn, [wire\\:click], [wire\\:submit]')) {
        return;
    }

    // Do not interfere with the nested Sync Worksheets modal or its actions.
    if (e.target.closest('.sync-worksheets-modal')) {
        return;
    }

    const dropdownProperties = [
        'showAnalyteDropdown',
        'showMethodDropdown',
        'showEquipmentDropdown',
        'showOperatorDropdown',
        'showRemedyHeaderDropdown',
        'showFormularDropdown',
        'showMethodSequenceDropdown',
        'showReportingUnitDropdown',
        'showLogEntryWorksheetDropdown',
        'showLabSectionDropdown',
    ];

    const anyOpen = dropdownProperties.some(function (property) {
        return Boolean(@this.get(property));
    });

    if (anyOpen) {
        @this.call('closeAllDropdowns');
    }
});
</script>

<style>
/* Modern Select Styling */
.modern-select {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #495057;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    position: relative;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.modern-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    background-color: #ffffff;
    outline: none;
}

.modern-select:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
}

.modern-select option {
    padding: 10px 16px;
    font-weight: 500;
    color: #495057;
}

.modern-select option:hover {
    background-color: #f8f9fa;
}

/* Custom dropdown arrow */
.modern-select {
    background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: left center, right 12px center;
    background-repeat: no-repeat, no-repeat;
    background-size: 100% 100%, 16px 16px;
    padding-right: 40px;
}

/* Invalid state styling */
.modern-select.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.modern-select.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Drag and Drop Styling */
#elements-table.elements-data-table tbody tr.sortable-row {
    transition: background-color 0.2s ease;
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


/* Tag-select density from layouts.partials.tag-select-styles */

.status-filter-input {
    padding: 0 0.45rem;
}

.tag-select-native {
    border: none;
    box-shadow: none;
    background-color: transparent;
    padding: 0.35rem 1.75rem 0.35rem 0;
    min-height: calc(var(--ls-control-h, 34px) - 2px);
    font-size: var(--ls-text-base, 0.8125rem);
}

.tag-select-native:focus {
    border: none;
    box-shadow: none;
    background-color: transparent;
}

.dropdown-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    transition: transform 0.3s ease;
    pointer-events: none;
    color: #6c757d;
}

.dropdown-arrow.rotated {
    transform: translateY(-50%) rotate(180deg);
}

.dropdown-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    margin-top: 4px;
    z-index: 1050;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    max-height: 300px;
    overflow: hidden;
}

.searchable-dropdown-wrapper {
    position: relative;
}

.no-results {
    padding: 20px;
    text-align: center;
    color: #6c757d;
}

.no-results i {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
}

.element-manager-page .quotation-stage-tabs-card {
    border-radius: 12px;
    overflow: hidden;
}

.element-manager-page .quotation-stage-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.element-manager-page .quotation-stage-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.5rem 0.9rem;
    border-radius: 8px;
    border: 1px solid var(--ls-color-border, #e2e8f0);
    background: #fff;
    color: var(--ls-color-muted, #64748b);
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.element-manager-page .quotation-stage-tab:hover {
    border-color: var(--color-primary-border-soft, #e2b4b4);
    color: var(--color-primary, #6D0A0E);
}

.element-manager-page .quotation-stage-tab.is-active {
    border-color: var(--color-primary, #6D0A0E);
    color: var(--color-primary, #6D0A0E);
    background: var(--color-primary-soft, #f8ecec);
    box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
}

.element-manager-page .quotation-stage-tab__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.35rem;
    padding: 2px 6px;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 700;
    background: #e2e8f0;
    color: #475569;
}

.element-manager-page .quotation-stage-tab.is-active .quotation-stage-tab__count {
    background: rgba(109, 10, 14, 0.12);
    color: var(--color-primary, #6D0A0E);
}

.element-parameter-group-row {
    background-color: #f8fafc;
}

.element-parameter-group-cell {
    padding: 0.65rem 0.75rem !important;
    border-top: 2px solid #e2e8f0;
}

.element-parameter-group-toggle {
    color: #1e293b !important;
    font-weight: 600;
    font-size: 0.95rem;
}

.element-parameter-group-label {
    margin-left: 0.15rem;
}
</style>

    @livewire('analysis.sync-worksheets-modal', ['analysisTypeId' => $analysisTypeId], key('sync-worksheets-'.$analysisTypeId))
</div>