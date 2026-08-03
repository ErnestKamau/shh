<div class="container-fluid lab-surface-theme ls-admin-page ls-admin-page--size-only" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="mdi mdi-scale text-primary"></i>
                        Standards Management
                    </h2>
                    <p class="text-muted mb-0">Manage standards, standard values, and standard analytes</p>
                </div>
                <div class="btn-group">
                    <button wire:click="showCreateStandardModal" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add Standard
                    </button>
                    <button wire:click="showCreateStandardValueModal" class="btn btn-outline-primary">
                        <i class="mdi mdi-plus"></i> Add Standard Value
                    </button>
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
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Search</label>
                <input type="text" wire:model.live="search" class="form-control" placeholder="Search standards...">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label">Status</label>
                <select wire:model.live="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Standards Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Standards</h5>
                </div>
                <div class="card-body">
                    @if($standards->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Standard Analytes</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($standards as $standard)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary" style="color: white;">{{ $standard->code }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $standard->name }}</strong>
                                                @if($standard->main_standard)
                                                    <span class="badge bg-warning ms-2" style="color: white;">Main</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($standard->is_qc_standard)
                                                    <span class="badge bg-info" style="color: white;">QC Standard</span>
                                                @else
                                                    <span class="badge bg-primary" style="color: white;">Regular</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info" style="color: white;">{{ $standard->standardAnalytes->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $standard->status ? 'success' : 'danger' }}" style="color: white;">
                                                    {{ $standard->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="selectStandard('{{ $standard->id }}')"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="View Standard Analytes">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>
                                                    <button wire:click="showEditStandardModal('{{ $standard->id }}')"
                                                            class="btn btn-sm btn-outline-warning"
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteStandard('{{ $standard->id }}')"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this standard? This will also delete all associated standard analytes.')">
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
                            <i class="mdi mdi-scale text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No standards found</h5>
                            <p class="text-muted">Create your first standard to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Standard Analytes Section -->
    @if($showStandardAnalytes && $selectedStandard)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Standard Analytes</h5>
                        <button wire:click="showCreateStandardAnalyteModal" class="btn btn-sm btn-primary">
                            <i class="mdi mdi-plus"></i> Add Standard Analyte
                        </button>
                    </div>
                    <div class="card-body">
                        @if($standardAnalytes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr>
                                            <th>Analyte</th>
                                            <th>Standard Value</th>
                                            <th>Type</th>
                                            <th>Range/Value</th>
                                            <th>Comments</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($standardAnalytes as $standardAnalyte)
                                            <tr>
                                                <td>
                                                    <strong>{{ $standardAnalyte->analyte->name ?? 'N/A' }}</strong>
                                                    <br><small class="text-muted">{{ $standardAnalyte->analyte->code ?? '' }}</small>
                                                </td>
                                                <td>
                                                    {{ $standardAnalyte->standardValue->name ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    @if($standardAnalyte->standard_value_type === 'is_range')
                                                        <span class="badge bg-info" style="color: white;">Range</span>
                                                    @else
                                                        <span class="badge bg-primary" style="color: white;">Value</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($standardAnalyte->standard_value_type === 'is_range')
                                                        <strong>{{ $standardAnalyte->low }} - {{ $standardAnalyte->high }}</strong>
                                                    @else
                                                        <strong>{{ $standardAnalyte->standard_is_value }}</strong>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ Str::limit($standardAnalyte->comments, 50) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $standardAnalyte->is_active ? 'success' : 'danger' }}" style="color: white;">
                                                        {{ $standardAnalyte->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="showEditStandardAnalyteModal('{{ $standardAnalyte->id }}')"
                                                                class="btn btn-sm btn-outline-warning"
                                                                title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="deleteStandardAnalyte('{{ $standardAnalyte->id }}')"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this standard analyte?')">
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
                                <i class="mdi mdi-test-tube text-muted" style="font-size: 2rem;"></i>
                                <h6 class="text-muted mt-2">No standard analytes found</h6>
                                <p class="text-muted">Add standard analytes to this standard.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Standard Modal -->
    @if($showStandardModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingStandard ? 'pencil' : 'plus' }}"></i>
                            {{ $editingStandard ? 'Edit' : 'Create' }} Standard
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeStandardModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveStandard">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="standardForm.name" class="form-control @error('standardForm.name') is-invalid @enderror">
                                        @error('standardForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="standardForm.code" class="form-control @error('standardForm.code') is-invalid @enderror">
                                        @error('standardForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="standardForm.main_standard" class="form-check-input" id="main_standard">
                                            <label class="form-check-label" for="main_standard">Main Standard</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="standardForm.is_qc_standard" class="form-check-input" id="is_qc_standard">
                                            <label class="form-check-label" for="is_qc_standard">QC Standard</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="standardForm.status" class="form-check-input" id="standard_status">
                                            <label class="form-check-label" for="standard_status">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStandardModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStandard">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Standard Value Modal -->
    @if($showStandardValueModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingStandardValue ? 'pencil' : 'plus' }}"></i>
                            {{ $editingStandardValue ? 'Edit' : 'Create' }} Standard Value
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeStandardValueModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveStandardValue">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="standardValueForm.name" class="form-control @error('standardValueForm.name') is-invalid @enderror">
                                        @error('standardValueForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="standardValueForm.code" class="form-control @error('standardValueForm.code') is-invalid @enderror">
                                        @error('standardValueForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="standardValueForm.status" class="form-check-input" id="standard_value_status">
                                    <label class="form-check-label" for="standard_value_status">Active</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStandardValueModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStandardValue">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Standard Analyte Modal -->
    @if($showStandardAnalyteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingStandardAnalyte ? 'pencil' : 'plus' }}"></i>
                            {{ $editingStandardAnalyte ? 'Edit' : 'Create' }} Standard Analyte
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeStandardAnalyteModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveStandardAnalyte">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Analyte <span class="text-danger">*</span></label>
                                        <x-searchable-select
                                            wire:model="standardAnalyteForm.analyte_id"
                                            :options="collect($analytes)->map(fn($analyte) => ['id' => $analyte->id, 'name' => $analyte->name . ' (' . $analyte->code . ')'])"
                                            placeholder="Search analytes..."
                                            empty-label="Select Analyte"
                                            class="{{ $errors->has('standardAnalyteForm.analyte_id') ? 'is-invalid' : '' }}"
                                        />
                                        @error('standardAnalyteForm.analyte_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Standard Value</label>
                                        <x-searchable-select
                                            wire:model="standardAnalyteForm.standard_value_id"
                                            :options="collect($standardValues)->map(fn($value) => ['id' => $value->id, 'name' => $value->name . ' (' . $value->code . ')'])"
                                            placeholder="Search standard values..."
                                            empty-label="Select Standard Value"
                                        />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Value Type</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" wire:model="standardAnalyteForm.standard_value_type" value="is_range" class="form-check-input" id="is_range">
                                            <label class="form-check-label" for="is_range">Use Range</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" wire:model="standardAnalyteForm.standard_value_type" value="is_standard_value" class="form-check-input" id="is_standard_value">
                                            <label class="form-check-label" for="is_standard_value">Use Value</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($standardAnalyteForm['standard_value_type'] === 'is_range')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Low Value</label>
                                            <input type="text" wire:model="standardAnalyteForm.low" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">High Value</label>
                                            <input type="text" wire:model="standardAnalyteForm.high" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Value</label>
                                            <input type="text" wire:model="standardAnalyteForm.standard_is_value" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Value Type</label>
                                            <select wire:model="standardAnalyteForm.value_type" class="form-select">
                                                <option value="">Select Type</option>
                                                <option value="Max">Max</option>
                                                <option value="Min">Min</option>
                                                <option value="less_than">< (Less Than)</option>
                                                <option value="greater_than">> (Greater Than)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Expected Value</label>
                                        <input type="text" wire:model="standardAnalyteForm.expected_value" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Mean Value</label>
                                        <input type="text" wire:model="standardAnalyteForm.mean_value" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Relative Standard Deviation</label>
                                        <input type="text" wire:model="standardAnalyteForm.rel_std_dev" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tolerance 1</label>
                                        <input type="text" wire:model="standardAnalyteForm.tolerance_1" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tolerance 2</label>
                                        <input type="text" wire:model="standardAnalyteForm.tolerance_2" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Comments</label>
                                <textarea wire:model="standardAnalyteForm.comments" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Recommendations</label>
                                <textarea wire:model="standardAnalyteForm.recommendations" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="standardAnalyteForm.is_active" class="form-check-input" id="standard_analyte_active">
                                            <label class="form-check-label" for="standard_analyte_active">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="standardAnalyteForm.absolute_tolerance" class="form-check-input" id="absolute_tolerance">
                                            <label class="form-check-label" for="absolute_tolerance">Absolute Tolerance</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStandardAnalyteModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStandardAnalyte">
                            <i class="mdi mdi-content-save"></i> Save
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
    </style>
</div>