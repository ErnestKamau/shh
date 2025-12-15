<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-sitemap text-primary"></i>
                                Disposal Approval Workflows
                            </h2>
                            <p class="text-muted mb-0">Manage approval chains for equipment disposal</p>
                        </div>
                        <div>
                            <a href="{{ route('equipment.asset-types.index') }}" class="btn btn-outline-primary me-2">
                                <i class="mdi mdi-format-list-bulleted-type"></i> Asset Types
                            </a>
                            <a href="{{ route('equipment.asset-locations.index') }}" class="btn btn-outline-primary me-2">
                                <i class="mdi mdi-map-marker"></i> Asset Locations
                            </a>
                            <a href="{{ route('equipment.disposal.workflow.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Create Workflow
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search workflow name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Equipment Type</label>
                                <select wire:model.live="typeFilter" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($assetTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Location</label>
                                <select wire:model.live="locationFilter" class="form-select">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflows List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Criteria</th>
                                    <th>Steps</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workflows as $workflow)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $workflow->workflow_name }}</div>
                                            <small class="text-muted">{{ Str::limit($workflow->description, 50) }}</small>
                                        </td>
                                        <td>
                                            @if($workflow->equipmentType)
                                                <div class="badge bg-info text-dark mb-1">Type: {{ $workflow->equipmentType->name }}</div>
                                            @else
                                                <div class="badge bg-light text-dark border mb-1">Type: Any</div>
                                            @endif
                                            <br>
                                            @if($workflow->location)
                                                <div class="badge bg-info text-dark">Location: {{ $workflow->location->name }}</div>
                                            @else
                                                <div class="badge bg-light text-dark border">Location: Any</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">
                                                {{ $workflow->steps->count() }} Steps
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       wire:click="toggleStatus({{ $workflow->id }})"
                                                       {{ $workflow->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $workflow->is_active ? 'Active' : 'Inactive' }}</label>
                                            </div>
                                        </td>
                                        <td>{{ $workflow->created_at->format('Y-m-d') }}</td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="{{ route('equipment.disposal.workflow.edit', $workflow->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button wire:click="deleteWorkflow({{ $workflow->id }})" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this workflow?');">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-sitemap text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">No Workflows Found</h5>
                                            <p class="text-muted">Create a new workflow to get started.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $workflows->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
