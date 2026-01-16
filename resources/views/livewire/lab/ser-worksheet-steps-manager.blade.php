<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-format-list-checks text-primary"></i>
                                SER Worksheet Steps
                            </h2>
                            <p class="text-muted mb-0">Manage steps for SER Worksheets</p>
                        </div>
                        <button wire:click="create" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Step
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Search steps...">
                        <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Step</th>
                                    <th>Default Measurands</th>
                                    <th>Default Equipment</th>
                                    <th>Default Analyst</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($steps as $step)
                                    <tr>
                                        <td>{{ $step->step }}</td>
                                        <td>
                                            @php
                                                $measurands = \App\ReportingUnit::whereIn('id', $step->default_measurand_ids ?? [])->pluck('name');
                                            @endphp
                                            @foreach($measurands as $name)
                                                <span class="badge bg-info text-white me-1 p-2">{{ $name }}</span>
                                            @endforeach
                                            @if($measurands->isEmpty())
                                                <span class="text-muted text-small p-2">None</span>
                                            @endif
                                        </td>
                                        <td>{{ $step->equipment->name ?? '-' }}</td>
                                        <td>{{ $step->analyst->name ?? '-' }}</td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" wire:click="toggleActive({{ $step->id }})" {{ $step->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <button wire:click="edit({{ $step->id }})" class="btn btn-sm btn-outline-warning">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button wire:click="confirmDelete({{ $step->id }})" class="btn btn-sm btn-outline-danger">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No steps found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $steps->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
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
                                <label class="form-label">Step Description</label>
                                <input type="text" wire:model="step" class="form-control">
                                @error('step') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Equipment</label>
                                    <div class="position-relative" wire:click.outside="$set('showEquipmentDropdown', false)">
                                        <div class="form-control d-flex flex-wrap align-items-center gap-1" style="min-height: 38px; height: auto; cursor: text;" wire:click="$set('showEquipmentDropdown', true)">
                                            @if($selectedEquipment)
                                                <span class="badge badge-light text-dark p-2 border" style="font-size: 0.9em;">
                                                    {{ $selectedEquipment->name }}
                                                    <i class="mdi mdi-close ms-1" style="cursor: pointer;" wire:click.stop="$set('default_equipment_id', null)"></i>
                                                </span>
                                            @endif
                                            <input type="text" class="border-0 shadow-none flex-grow-1" style="min-width: 150px; outline: none; padding: 0;" placeholder="{{ $selectedEquipment ? '' : 'Search equipment...' }}" wire:model.live="equipmentSearch">
                                        </div>
                                        @if($showEquipmentDropdown)
                                            <ul class="list-group position-absolute w-100 shadow" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                                @foreach($equipments as $eq)
                                                    <li class="list-group-item list-group-item-action" wire:click="selectEquipment({{ $eq->id }})">
                                                        {{ $eq->name }} ({{ $eq->equipment_number }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    @error('default_equipment_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Analyst</label>
                                    <div class="position-relative" wire:click.outside="$set('showAnalystDropdown', false)">
                                        <div class="form-control d-flex flex-wrap align-items-center gap-1" style="min-height: 38px; height: auto; cursor: text;" wire:click="$set('showAnalystDropdown', true)">
                                            @if($selectedAnalyst)
                                                <span class="badge badge-light text-dark p-2 border" style="font-size: 0.9em;">
                                                    {{ $selectedAnalyst->name }}
                                                    <i class="mdi mdi-close ms-1" style="cursor: pointer;" wire:click.stop="$set('default_analyst_id', null)"></i>
                                                </span>
                                            @endif
                                            <input type="text" class="border-0 shadow-none flex-grow-1" style="min-width: 150px; outline: none; padding: 0;" placeholder="{{ $selectedAnalyst ? '' : 'Search analyst...' }}" wire:model.live="analystSearch">
                                        </div>
                                        @if($showAnalystDropdown)
                                            <ul class="list-group position-absolute w-100 shadow" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                                @foreach($analysts as $an)
                                                    <li class="list-group-item list-group-item-action" wire:click="selectAnalyst({{ $an->id }})">
                                                        {{ $an->name }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    @error('default_analyst_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Default Measurands</label>
                                <div class="position-relative" wire:click.outside="$set('showMeasurandDropdown', false)">
                                    <div class="form-control d-flex flex-wrap align-items-center gap-1" style="min-height: 38px; height: auto; cursor: text;" wire:click="$set('showMeasurandDropdown', true)">
                                        @foreach($selectedMeasurands as $measurand)
                                            <span class="badge badge-light text-dark p-2 border" style="font-size: 0.9em;">
                                                {{ $measurand->name }}
                                                <i class="mdi mdi-close ms-1" style="cursor: pointer;" wire:click.stop="removeMeasurand({{ $measurand->id }})"></i>
                                            </span>
                                        @endforeach
                                        <input type="text" class="border-0 shadow-none flex-grow-1" style="min-width: 150px; outline: none; padding: 0;" placeholder="{{ count($selectedMeasurands) > 0 ? '' : 'Search measurands to add...' }}" wire:model.live="measurandSearch">
                                    </div>
                                    
                                    @if($showMeasurandDropdown && count($measurands) > 0)
                                        <ul class="list-group position-absolute w-100 shadow" style="z-index: 1000; max-height: 200px; overflow-y: auto; margin-top: 2px;">
                                            @foreach($measurands as $m)
                                                <li class="list-group-item list-group-item-action {{ in_array($m->id, $default_measurand_ids) ? 'active' : '' }}" wire:click.stop="toggleMeasurand({{ $m->id }})">
                                                    {{ $m->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="isActiveSwitch" wire:model="is_active">
                                <label class="form-check-label" for="isActiveSwitch">Active</label>
                            </div>
                        
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary mr-2" wire:click="cancel">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
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
                        <h5 class="modal-title text-danger">Delete Confirmation</h5>
                        <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i> Are you sure you want to delete this step? This will set it to inactive.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteStep">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
