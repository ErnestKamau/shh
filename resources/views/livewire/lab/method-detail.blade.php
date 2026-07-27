<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-cogs text-primary"></i>
                                Analysis Method Detail
                            </h2>
                            <p class="text-muted mb-0">
                                {{ $method->name }} | {{ $method->methodtype->value ?? 'Analysis Method' }}
                            </p>
                        </div>
                        <a href="{{ route('analysis-methods') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left"></i> Back to List
                        </a>
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
                            <label class="form-label fw-bold">Number <span class="text-danger">*</span></label>
                            <input type="text" 
                                   wire:model="methodForm.code" 
                                   class="form-control @error('methodForm.code') is-invalid @enderror" 
                                   placeholder="Analysis Method Number...">
                            @error('methodForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea wire:model="methodForm.description" 
                                      class="form-control @error('methodForm.description') is-invalid @enderror" 
                                      rows="4"
                                      placeholder="Description (optional)..."></textarea>
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
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-content-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Analytes -->
        <div class="col-md-8">
            <div class="card shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4">
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
                </div>
            </div>
        </div>
    </div>
</div>


