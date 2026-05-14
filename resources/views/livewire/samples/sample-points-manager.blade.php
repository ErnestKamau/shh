<div class="container-fluid">
    <div class="row mb-4 customer-tab-filters">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-map-marker text-primary"></i>
                                {{ $customer->sample_point_configurable_name ?: 'Sample Points' }} Management
                            </h2>
                            <p class="text-muted mb-0">Manage {{ strtolower($customer->sample_point_configurable_name ?: 'sample points') }} for: <strong>{{ $customer->name }}</strong></p>
                        </div>
                        <button wire:click="showCreateSamplePointModal" class="btn btn-outline-primary pricelist-action-btn">
                            <i class="mdi mdi-plus"></i> Add {{ $customer->sample_point_configurable_name ?: 'Sample Point' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-8">
            <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, code, or description...">
        </div>
        <div class="col-md-2">
            <select wire:model.live="statusFilter" class="form-control sp-status-select">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div class="col-md-2">
            <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">Clear</button>
        </div>
    </div>

    <div class="card" style="border-radius: 15px;">
        <div class="card-body">
            @if($this->samplePoints->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->samplePoints as $samplePoint)
                                <tr>
                                    <td>{{ $samplePoint->name ?? $samplePoint->display_name ?? '-' }}</td>
                                    <td>{{ $samplePoint->code ?? '-' }}</td>
                                    <td>{{ $samplePoint->description ?? $samplePoint->gps ?? '-' }}</td>
                                    <td>{{ $samplePoint->unit->name ?? '-' }}</td>
                                    <td>
                                        @if((int) $samplePoint->active === 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <button wire:click="showEditSamplePointModal('{{ $samplePoint->id }}')" class="btn btn-sm rm-act-btn rm-act-btn--edit" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button wire:click="deleteSamplePoint('{{ $samplePoint->id }}')" class="btn btn-sm rm-act-btn rm-act-btn--delete" title="Delete" onclick="return confirm('Are you sure you want to delete this sample point?')">
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
                <div class="text-center py-4">
                    <i class="mdi mdi-map-marker text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No {{ strtolower($customer->sample_point_configurable_name ?: 'sample points') }} found</h5>
                </div>
            @endif
        </div>
    </div>

    @if($showSamplePointModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingSamplePoint ? 'pencil' : 'plus' }}"></i>
                            {{ $editingSamplePoint ? 'Edit' : 'Create' }} {{ $customer->sample_point_configurable_name ?: 'Sample Point' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeSamplePointModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Unit <span class="text-danger">*</span></label>
                                <div class="tag-select-container @error('samplePointForm.unit_id') is-invalid @enderror"
                                    wire:click="$set('showUnitDropdown', true)"
                                    wire:click.outside="$set('showUnitDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedUnit)
                                            <span class="tag-badge">
                                                {{ $this->selectedUnit->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearUnit"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                            wire:model.live.debounce.200ms="unitSearch"
                                            class="tag-input"
                                            placeholder="{{ $this->selectedUnit ? $this->selectedUnit->name : 'Select unit' }}"
                                            autocomplete="off">
                                    </div>

                                    @if($showUnitDropdown)
                                        <div class="tag-dropdown">
                                            @if(count($this->filteredUnits) > 0)
                                                @foreach($this->filteredUnits as $unit)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectUnit('{{ $unit->id }}')">
                                                        {{ $unit->name }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="tag-dropdown-item text-muted">No units found</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @error('samplePointForm.unit_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="samplePointForm.name" class="form-control" placeholder="Sample point name">
                                @error('samplePointForm.name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" wire:model="samplePointForm.code" class="form-control" placeholder="Optional code">
                                @error('samplePointForm.code') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" wire:model="samplePointForm.active" class="form-check-input" id="sp-active">
                                    <label class="form-check-label" for="sp-active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea wire:model="samplePointForm.description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                                @error('samplePointForm.description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeSamplePointModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveSamplePoint">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .rm-act-btn:last-child {
            margin-right: 0;
        }

        .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .rm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .rm-act-btn--delete {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .rm-act-btn--delete:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .pricelist-action-btn {
            border-radius: 10px;
            min-height: 42px;
            font-weight: 600;
            padding-left: 16px;
            padding-right: 16px;
        }

        .tag-select-container {
            position: relative;
            width: 100%;
        }

        .tag-select-input {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            background-color: #fff;
        }

        .tag-input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 120px;
            font-size: 0.9rem;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f2f5;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 0.85rem;
        }

        .tag-badge i {
            cursor: pointer;
        }

        .tag-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-height: 220px;
            overflow-y: auto;
            z-index: 1100;
        }

        .tag-dropdown-item {
            padding: 8px 10px;
            cursor: pointer;
        }

        .tag-dropdown-item:hover {
            background: #f8f9fa;
        }

        .tag-select-container.is-invalid .tag-select-input {
            border-color: #dc3545;
        }

        .sp-status-select {
            min-width: 170px;
            padding-right: 2.2rem;
        }
    </style>
</div>
