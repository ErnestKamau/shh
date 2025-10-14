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
                                    <i class="mdi mdi-format-list-numbered text-primary"></i>
                                    Sequence Stages Editor
                                </h2>
                                <p class="text-muted mb-0">{{ $version->methodSequence->name }} - Version {{ $version->version_number }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('method-sequences.manage') }}" class="btn mr-2 btn-outline-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back to Sequences
                                </a>
                                <button wire:click="showCreateStageModal" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add Stage
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

        <!-- Stages List -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-body">
                        @if($stages->count() > 0)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $stages->count() }} stage(s)
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Drag and Drop Helper Text -->
                            <div class="alert alert-info mb-3" style="border-radius: 12px; border-left: 4px solid #17a2b8;">
                                <i class="mdi mdi-information-outline"></i>
                                <strong>Tip:</strong> Drag and drop rows using the <i class="mdi mdi-drag-vertical"></i> handle to rearrange the stage order.
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr>
                                            <th style="width: 60px;">
                                                <i class="mdi mdi-drag text-muted"></i>
                                            </th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Duration</th>
                                            <th>Result Stage</th>
                                            <th>Equipment</th>
                                            <th style="width: 250px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-stages">
                                        @foreach($stages as $stage)
                                            <tr class="sortable-row {{ $stage->is_result_stage ? 'result-stage' : '' }}" data-stage-id="{{ $stage->id }}">
                                                <td class="drag-handle text-center">
                                                    <i class="mdi mdi-drag-vertical text-muted" style="cursor: move; font-size: 18px;"></i>
                                                </td>
                                                <td>
                                                    <strong>{{ $stage->name }}</strong>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ Str::limit($stage->description, 50) }}</span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $stage->duration_range }}</small>
                                                </td>
                                                <td>
                                                    @if($stage->is_result_stage)
                                                        <span class="badge badge-success">Yes</span>
                                                    @else
                                                        <span class="badge badge-secondary">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ count($stage->equipment_ids ?? []) }} item(s)</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="showEditStageModal({{ $stage->id }})" 
                                                                class="btn btn-sm btn-outline-primary mr-2" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="cloneStage({{ $stage->id }})" 
                                                                class="btn btn-sm btn-outline-info mr-2" title="Clone">
                                                            <i class="mdi mdi-content-copy"></i>
                                                        </button>
                                                        <button wire:click="deleteStage({{ $stage->id }})" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this stage?')">
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
                                <i class="mdi mdi-format-list-numbered fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No stages found</h5>
                                <p class="text-muted">Add your first stage to get started.</p>
                                <button wire:click="showCreateStageModal" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add Stage
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- -------------------------------MODALS------------------------------- --}}
    
    <!-- Create/Edit Stage Modal -->
    @if($showStageModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingStage ? 'pencil' : 'plus' }}"></i>
                            {{ $editingStage ? 'Edit' : 'Create' }} Stage
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showStageModal', false)"></button>
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
                        
                        <form wire:submit.prevent="saveStage">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="stageName" class="form-label">Stage Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="stageName" class="form-control @error('stageName') is-invalid @enderror" id="stageName" required>
                                        @error('stageName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="stageOrder" class="form-label">Order <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="stageOrder" class="form-control @error('stageOrder') is-invalid @enderror" id="stageOrder" min="1" required>
                                        @error('stageOrder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="stageDescription" class="form-label">Description</label>
                                <textarea wire:model="stageDescription" class="form-control @error('stageDescription') is-invalid @enderror" id="stageDescription" rows="2"></textarea>
                                @error('stageDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Duration (hours)</label>
                                        <input type="number" step="0.01" wire:model="duration" class="form-control @error('duration') is-invalid @enderror" id="duration" min="0">
                                        @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="safeDuration" class="form-label">Safe Duration (hours)</label>
                                        <input type="number" step="0.01" wire:model="safeDuration" class="form-control @error('safeDuration') is-invalid @enderror" id="safeDuration" min="0">
                                        @error('safeDuration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="form-text text-muted">Must be ≤ Duration</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="equipmentSearch" class="form-label fw-bold">
                                    <i class="mdi mdi-toolbox text-info"></i> Equipment
                                </label>
                                <div class="searchable-select-container">
                                    <input type="text" 
                                           wire:model.live="equipmentSearch" 
                                           wire:keyup="searchEquipments"
                                           class="form-control @error('selectedEquipments') is-invalid @enderror" 
                                           id="equipmentSearch"
                                           placeholder="Type to search equipment..."
                                           autocomplete="off"
                                           style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                    
                                    @if($showEquipmentDropdown && $filteredEquipments->count() > 0)
                                        <div class="searchable-dropdown">
                                            @foreach($filteredEquipments as $equipment)
                                                <div class="dropdown-item" 
                                                     wire:click="selectEquipment({{ $equipment->id }}, '{{ $equipment->name }}')"
                                                     style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                    {{ $equipment->name }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if(count($selectedEquipments) > 0)
                                        <div class="selected-items mt-2">
                                            @foreach($selectedEquipments as $equipment)
                                                <span class="badge bg-info me-1 mb-1">
                                                    {{ $equipment['name'] }}
                                                    <i class="mdi mdi-close-circle ms-1" 
                                                       wire:click="removeEquipment({{ $equipment['id'] }})" 
                                                       style="cursor: pointer;"></i>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <small class="form-text text-muted">
                                    <i class="mdi mdi-information-outline"></i> Select one or more equipment items
                                </small>
                                @error('selectedEquipments') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="mediaSearch" class="form-label fw-bold">
                                    <i class="mdi mdi-beaker text-warning"></i> Medias
                                </label>
                                <div class="searchable-select-container">
                                    <input type="text" 
                                           wire:model.live="mediaSearch" 
                                           wire:keyup="searchMedias"
                                           class="form-control @error('selectedMedias') is-invalid @enderror" 
                                           id="mediaSearch"
                                           placeholder="Type to search medias..."
                                           autocomplete="off"
                                           style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                    
                                    @if($showMediaDropdown && $filteredMedias->count() > 0)
                                        <div class="searchable-dropdown">
                                            @foreach($filteredMedias as $media)
                                                <div class="dropdown-item" 
                                                     wire:click="selectMedia({{ $media->id }}, '{{ $media->name }}')"
                                                     style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                    {{ $media->name }}
                                                    <small class="text-muted d-block">{{ $media->category->name ?? 'Unknown Category' }}</small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if(count($selectedMedias) > 0)
                                        <div class="selected-items mt-2">
                                            @foreach($selectedMedias as $media)
                                                <span class="badge bg-warning me-1 mb-1">
                                                    {{ $media['name'] }}
                                                    <i class="mdi mdi-close-circle ms-1" 
                                                       wire:click="removeMedia({{ $media['id'] }})" 
                                                       style="cursor: pointer;"></i>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <small class="form-text text-muted">
                                    <i class="mdi mdi-information-outline"></i> Select one or more media items
                                </small>
                                @error('selectedMedias') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="controlSearch" class="form-label fw-bold">
                                    <i class="mdi mdi-test-tube text-danger"></i> Controls
                                </label>
                                <div class="searchable-select-container">
                                    <input type="text" 
                                           wire:model.live="controlSearch" 
                                           wire:keyup="searchControls"
                                           class="form-control @error('selectedControls') is-invalid @enderror" 
                                           id="controlSearch"
                                           placeholder="Type to search controls..."
                                           autocomplete="off"
                                           style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                    
                                    @if($showControlDropdown && $filteredControls->count() > 0)
                                        <div class="searchable-dropdown">
                                            @foreach($filteredControls as $control)
                                                <div class="dropdown-item" 
                                                     wire:click="selectControl({{ $control->id }}, '{{ $control->name }}')"
                                                     style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                    {{ $control->name }}
                                                    <small class="text-muted d-block">{{ $control->category->name ?? 'Unknown Category' }}</small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if(count($selectedControls) > 0)
                                        <div class="selected-items mt-2">
                                            @foreach($selectedControls as $control)
                                                <span class="badge bg-danger me-1 mb-1">
                                                    {{ $control['name'] }}
                                                    <i class="mdi mdi-close-circle ms-1" 
                                                       wire:click="removeControl({{ $control['id'] }})" 
                                                       style="cursor: pointer;"></i>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <small class="form-text text-muted">
                                    <i class="mdi mdi-information-outline"></i> Select one or more control items
                                </small>
                                @error('selectedControls') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="isResultStage" class="form-check-input" id="isResultStage">
                                            <label class="form-check-label" for="isResultStage">
                                                Is Result Stage
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Check if this stage is where results are read</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="failMoveNextStage" class="form-check-input" id="failMoveNextStage">
                                            <label class="form-check-label" for="failMoveNextStage">
                                                Fail Move to Next Stage
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">If result is fail, move to next stage</small>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showStageModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStage">
                            <i class="mdi mdi-content-save"></i> {{ $editingStage ? 'Update' : 'Create' }} Stage
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
    
    .modal-dialog-scrollable {
        max-height: calc(100vh - 3.5rem);
    }
    
    .modal-dialog-scrollable .modal-content {
        max-height: calc(100vh - 3.5rem);
        overflow: hidden;
    }
    
    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 200px);
    }
    
    tr.result-stage {
        background-color: #e7f4e7 !important;
    }
    
    /* Modern Multi-Select Styling */
    .modern-select-multi {
        border-radius: 8px;
        border: 2px solid #e3e6f0;
        transition: all 0.3s ease;
        min-height: 120px;
    }
    
    .modern-select-multi:focus {
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15) !important;
        outline: none;
    }
    
    .modern-select-multi:hover {
        border-color: #007bff !important;
    }
    
    /* Enhanced label styling */
    .form-label.fw-bold {
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-label i {
        margin-right: 5px;
    }
    
    .form-text i {
        font-size: 0.875rem;
    }
    
    /* Searchable Select Styles */
    .searchable-select-container {
        position: relative;
    }
    
    .searchable-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #007bff;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .searchable-dropdown .dropdown-item {
        padding: 10px 15px;
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s;
    }
    
    .searchable-dropdown .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .searchable-dropdown .dropdown-item:last-child {
        border-bottom: none;
    }
    
    .selected-items .badge {
        font-size: 0.85rem;
        padding: 6px 10px;
        display: inline-flex;
        align-items: center;
    }
    
    /* Drag and Drop Styling */
    .sortable-row {
        transition: all 0.2s ease;
    }

    .sortable-row:hover {
        background-color: #f8f9fa !important;
    }

    .drag-handle {
        cursor: move;
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
        console.log('Method Sequence Stage Editor script loaded');
        
        document.addEventListener('livewire:init', () => {
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.searchable-select-container')) {
                    if (typeof @this !== 'undefined') {
                        @this.set('showEquipmentDropdown', false);
                        @this.set('showMediaDropdown', false);
                        @this.set('showControlDropdown', false);
                    }
                }
            });
            
            // Initialize drag and drop functionality
            initializeSortable();
        });
        
        // Re-initialize sortable when Livewire updates the DOM
        document.addEventListener('livewire:updated', () => {
            initializeSortable();
        });
        
        function initializeSortable() {
            const sortableElement = document.getElementById('sortable-stages');
            
            if (sortableElement && typeof Sortable !== 'undefined') {
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
                        const stageIds = Array.from(sortableElement.children).map(row => {
                            return parseInt(row.getAttribute('data-stage-id'));
                        });
                        
                        // Send the new order to Livewire
                        @this.call('updateStageOrder', stageIds);
                    }
                });
            }
        }
    </script>
</div>

