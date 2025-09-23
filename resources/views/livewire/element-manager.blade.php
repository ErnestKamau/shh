<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-flask text-primary"></i>
                                Analysis Elements Management
                            </h2>
                            <p class="text-muted mb-0">Manage analysis elements for this analysis type</p>
                        </div>
                        <button wire:click="showCreateElementModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Element
                        </button>
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
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by analyte name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
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

    <!-- Elements Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($this->elements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Level</th>
                                        <th>Analyte</th>
                                        <th>Method</th>
                                        <th>Equipment</th>
                                        <th>Operator</th>
                                        <th>Reporting Unit</th>
                                        <th>LOD</th>
                                        <th>HOD</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->elements as $element)
                                        <tr>
                                            <td>
                                                <span class="badge badge-info">{{ $element->level }}</span>
                                            </td>
                                            <td>{{ $element->analyte->name ?? 'N/A' }}</td>
                                            <td>{{ $element->mmethod->name ?? $element->ltmethod->name ?? 'N/A' }}</td>
                                            <td>{{ $element->equipment->name ?? 'N/A' }}</td>
                                            <td>{{ $element->operator->name ?? 'N/A' }}</td>
                                            <td>{{ $element->reporting_unit }}</td>
                                            <td>{{ $element->lod ?? 'N/A' }}</td>
                                            <td>{{ $element->hod ?? 'N/A' }}</td>
                                            <td>
                                                @if($element->active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditElementModal({{ $element->id }})" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteElement({{ $element->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this element?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    Showing {{ $this->elements->firstItem() ?? 0 }} to {{ $this->elements->lastItem() ?? 0 }} of {{ $this->elements->total() }} entries
                                </span>
                                <div class="d-flex align-items-center">
                                    <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                    <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                        @foreach($perPageOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                {{ $this->elements->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No elements found</h5>
                            <p class="text-muted">Start by adding your first analysis element.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>