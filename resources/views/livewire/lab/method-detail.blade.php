<div class="container-fluid">
    <!-- Header with breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('lab-home') }}">Lab Management</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('analysis-methods') }}">Methods</a></li>
                            <li class="breadcrumb-item active">{{ $method->name }}</li>
                        </ol>
                    </nav>
                    <h2 class="mb-0">
                        <i class="mdi mdi-cogs text-primary"></i>
                        {{ $method->name }}
                        <small class="text-muted"> | {{ $method->methodtype->value ?? 'Analysis Method' }}</small>
                    </h2>
                </div>
                <a href="{{ route('analysis-methods') }}" class="btn btn-outline-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back to List
                </a>
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

    <div class="row">
        <!-- Left Column - Edit Form -->
        <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">
                        <i class="mdi mdi-pencil-outline text-primary"></i> Edit Analysis Method
                    </h5>
                    
                    <form wire:submit.prevent="updateMethod">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   wire:model="methodForm.name" 
                                   class="form-control @error('methodForm.name') is-invalid @enderror" 
                                   placeholder="Analysis Method Name...">
                            @error('methodForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                            <input type="text" 
                                   wire:model="methodForm.code" 
                                   class="form-control @error('methodForm.code') is-invalid @enderror" 
                                   placeholder="Analysis Method Code...">
                            @error('methodForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea wire:model="methodForm.description" 
                                      class="form-control @error('methodForm.description') is-invalid @enderror" 
                                      rows="4"
                                      placeholder="Description..."></textarea>
                            @error('methodForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check mb-2">
                                <input type="checkbox" 
                                       wire:model="methodForm.active" 
                                       class="form-check-input" 
                                       id="active">
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" 
                                       wire:model="methodForm.is_sampling_method" 
                                       class="form-check-input" 
                                       id="is_sampling_method">
                                <label class="form-check-label" for="is_sampling_method">Is Sampling Method</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" 
                                       wire:model="methodForm.is_ltm" 
                                       class="form-check-input" 
                                       id="is_ltm">
                                <label class="form-check-label" for="is_ltm">Is Laboratory Test Method</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-content-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Tabs -->
        <div class="col-md-8">
            <div class="card shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'analytes' ? 'active' : '' }}" 
                               wire:click="switchTab('analytes')"
                               style="cursor: pointer;">
                                <i class="mdi mdi-flask"></i> Analytes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'reagents' ? 'active' : '' }}" 
                               wire:click="switchTab('reagents')"
                               style="cursor: pointer;">
                                <i class="mdi mdi-test-tube"></i> Reagents
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <!-- Analytes Tab -->
                    @if($activeTab === 'analytes')
                        <div>
                            <h5 class="mb-3">
                                <i class="mdi mdi-flask text-primary"></i> Analytes
                            </h5>
                            
                            @php
                                $analytes = $method->analytes();
                            @endphp
                            
                            @if(count($analytes) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th>Common Name</th>
                                                <th>Decimal Places</th>
                                                <th>Reporting Unit</th>
                                                <th>Equipment</th>
                                                <th>Analysis Type</th>
                                                <th>Active</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytes as $analyte)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $analyte->analyte->code ?? '' }}</td>
                                                    <td>{{ $analyte->analyte->name ?? '' }}</td>
                                                    <td>{{ $analyte->analyte->common_name ?? '-' }}</td>
                                                    <td>{{ $analyte->decimal_places ?? $analyte->analyte->decimal_places }}</td>
                                                    <td>{{ $analyte->reporting_unit ?? $analyte->analyte->reporting_unit ?? '' }}</td>
                                                    <td>{{ $analyte->equipment->name ?? '-' }}</td>
                                                    <td>{{ $analyte->analysis_type->name ?? '-' }}</td>
                                                    <td>
                                                        @if($analyte->active)
                                                            <span class="badge badge-success">Active</span>
                                                        @else
                                                            <span class="badge badge-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No analytes linked to this method yet.</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Reagents Tab -->
                    @if($activeTab === 'reagents')
                        <div>
                            <h5 class="mb-3">
                                <i class="mdi mdi-test-tube text-primary"></i> Reagents
                            </h5>
                            
                            <!-- Add Reagent Form -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title">Add Reagent</h6>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group mb-3">
                                                <label class="form-label small">Reagent</label>
                                                <div style="position: relative;">
                                                    <input type="text" 
                                                           wire:model.live="reagentSearch"
                                                           wire:keyup="searchReagents"
                                                           class="form-control form-control-sm" 
                                                           placeholder="Search reagents..."
                                                           autocomplete="off">
                                                    
                                                    @if($showReagentDropdown && count($filteredReagents) > 0)
                                                        <div class="dropdown-results">
                                                            @foreach($filteredReagents as $reagent)
                                                                <div class="dropdown-item" 
                                                                     wire:click="selectReagent({{ $reagent['id'] }}, '{{ $reagent['name'] }}', '{{ $reagent['unit'] }}')"
                                                                     style="cursor: pointer;">
                                                                    {{ $reagent['name'] }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label class="form-label small">Reporting Unit</label>
                                                <input type="text" 
                                                       wire:model="pendingReagent.unit"
                                                       class="form-control form-control-sm" 
                                                       placeholder="Unit"
                                                       readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-3">
                                                <label class="form-label small">Quantity</label>
                                                <input type="number" 
                                                       wire:model="pendingReagent.volume"
                                                       step="0.01"
                                                       class="form-control form-control-sm" 
                                                       placeholder="Qty">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-3">
                                                <label class="form-label small">&nbsp;</label>
                                                <button wire:click="addReagent" 
                                                        class="btn btn-sm btn-primary w-100"
                                                        type="button">
                                                    <i class="mdi mdi-plus"></i> Add
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reagents Table -->
                            @if(count($reagents) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Reagent</th>
                                                <th>Reporting Unit</th>
                                                <th>Quantity</th>
                                                <th width="80">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reagents as $reagent)
                                                <tr>
                                                    <td>{{ $reagent['reagent_name'] }}</td>
                                                    <td>{{ $reagent['reagent_unit'] }}</td>
                                                    <td>{{ $reagent['quantity'] }}</td>
                                                    <td>
                                                        <button wire:click="deleteReagent({{ $reagent['id'] }})" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Remove this reagent?')">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="mdi mdi-test-tube-empty text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No reagents added yet. Use the form above to add reagents.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #737373;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: #007bff;
}

.nav-tabs .nav-link.active {
    border-bottom: 2px solid #007bff;
    color: #007bff;
    background: none;
}

.dropdown-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dropdown-results .dropdown-item {
    padding: 8px 12px;
    cursor: pointer;
}

.dropdown-results .dropdown-item:hover {
    background: #f3f4f6;
}
</style>

<script>
document.addEventListener('click', function(e) {
    if (!e.target.closest('[wire\\:model\\.live="reagentSearch"]')) {
        @this.set('showReagentDropdown', false);
    }
});
</script>

