<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h2 class="mb-0">
                        <i class="mdi mdi-finance text-primary"></i>
                        Lab Stock Movement
                    </h2>
                    <p class="text-muted mb-0">Track stock in/out movements for lab subcategories</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, category, or description...">
                        </div>
                        <div class="col-md-2">
                            <button wire:click="clearSearch" class="btn btn-outline-secondary w-100">
                                <i class="mdi mdi-refresh"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Categories Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->subCategories->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->subCategories->firstItem() ?? 0 }} to {{ $this->subCategories->lastItem() ?? 0 }} of {{ $this->subCategories->total() }} entries
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
                            <table class="table table-striped table-hover" id="movements-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">No</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th style="width: 150px;">Image</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->subCategories as $subCategory)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <a href="{{ route('solution-movement-show', ['id' => $subCategory->id]) }}" class="text-decoration-none">
                                                    <strong>{{ $subCategory->name }}</strong>
                                                </a>
                                            </td>
                                            <td>{{ $subCategory->category->name ?? 'N/A' }}</td>
                                            <td>{{ $subCategory->description }}</td>
                                            <td>
                                                @if($subCategory->image)
                                                    <img src="{{ $subCategory->image }}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;" alt="{{ $subCategory->name }}">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                                        <i class="mdi mdi-image-off text-muted" style="font-size: 2rem;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('solution-movement-show', ['id' => $subCategory->id]) }}" 
                                                   class="btn btn-sm btn-outline-success" 
                                                   title="View Stock Movement">
                                                    <i class="mdi mdi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->subCategories->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-finance text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No sub-categories found</h5>
                            <p class="text-muted">No active lab sub-categories available for stock movement tracking.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .img-thumbnail {
        border-radius: 8px;
    }
    </style>
</div>
