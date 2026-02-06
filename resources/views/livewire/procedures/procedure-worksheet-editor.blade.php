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

                            <button wire:click="create" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Step
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                        </td>
                                        <td>{{ $stepItem->equipment->name ?? '-' }}</td>
                                        <td>{{ $stepItem->analyst->name ?? '-' }}</td>
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
                                        <td colspan="6" class="text-center py-4">No steps found. Add your first step!</td>
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
            </div>
        </div>
    </div>

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
                                    <label class="form-label">Default Equipment</label>
                                    <div class="position-relative" wire:click.outside="$set('showEquipmentDropdown', false)">
                                        @if($selectedEquipment)
                                            <div class="position-relative">
                                                <input type="text" class="form-control" value="{{ $selectedEquipment->name }} ({{ $selectedEquipment->equipment_number }})" readonly style="padding-right: 30px;">
                                                <i class="mdi mdi-close text-danger cursor-pointer" 
                                                   wire:click="$set('default_equipment_id', null)"
                                                   style="position: absolute; top: 10px; right: 10px; z-index: 10;"></i>
                                            </div>
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
            initializeSortable();
        });

        document.addEventListener('livewire:updated', () => {
            initializeSortable();
        });

        function initializeSortable() {
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
                        const stepIds = Array.from(sortableElement.children).map(row => {
                            return parseInt(row.getAttribute('data-step-id'));
                        });
                        @this.call('updateStepOrder', stepIds);
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
