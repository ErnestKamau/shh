<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-format-list-bulleted text-primary"></i>
                                Stock Management
                            </h2>
                            <p class="text-muted mb-0">Manage lab sub-categories and reagent items</p>
                        </div>
                        <button wire:click="showCreateSubCategoryModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Item
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
                        <div class="col-md-4">
                            <div class="form-group mb-3 mb-md-0">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name or description...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3 mb-md-0">
                                <label class="form-label fw-bold">Category</label>
                                <x-searchable-select
                                    wire:model.live="categoryFilter"
                                    :options="collect($categories)->map(fn($category) => ['id' => $category->id, 'name' => $category->name])"
                                    placeholder="Search categories..."
                                    empty-label="All Categories"
                                />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3 mb-md-0">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3 mb-md-0">
                                <label for="perPage" class="form-label fw-bold">Show</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100" title="Clear filters">
                                    <i class="mdi mdi-refresh"></i>
                                </button>
                            </div>
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
                        <div class="d-flex align-items-center mb-3">
                            <span class="text-muted">
                                Showing {{ $this->subCategories->firstItem() ?? 0 }} to {{ $this->subCategories->lastItem() ?? 0 }} of {{ $this->subCategories->total() }} entries
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="subcategories-table" style="width: 120%;">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 200px;">Actions</th>
                                        <th style="width: 150px;">Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Rate</th>
                                        <th>Unit Measure</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->subCategories as $subCategory)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-wrap">
                                                    <button wire:click="showEditSubCategoryModal('{{ $subCategory->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <a href="{{ route('show_lab_sub_category', ['id' => $subCategory->id]) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                       title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('solutions-preparation-index', ['solutionFilter' => $subCategory->id]) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--muted"
                                                       title="Preparations">
                                                        <i class="mdi mdi-clipboard-list-outline"></i>
                                                    </a>
                                                    <button wire:click="cloneSubCategory('{{ $subCategory->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--muted"
                                                            title="Clone"
                                                            onclick="return confirm('Are you sure you want to clone this sub-category?')">
                                                        <i class="mdi mdi-content-duplicate"></i>
                                                    </button>
                                                    <button wire:click="deleteSubCategory('{{ $subCategory->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this sub-category?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                @if($subCategory->image)
                                                    <img src="{{ $subCategory->image }}" class="img-thumbnail" style="width: 125px; height: 65px; object-fit: cover;" alt="{{ $subCategory->name }}">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 125px; height: 65px;">
                                                        <i class="mdi mdi-image-off text-muted" style="font-size: 2rem;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td><strong>{{ $subCategory->name }}</strong></td>
                                            <td>{{ $subCategory->category->name ?? 'N/A' }}</td>
                                            <td>{{ $subCategory->rate }}</td>
                                            <td>{{ $subCategory->reportingUnit->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($subCategory->active)
                                                    <span class="badge badge-success">
                                                        <i class="mdi mdi-check-circle"></i> Active
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        <i class="mdi mdi-close-circle"></i> Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $subCategory->description }}</td>
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
                            <i class="mdi mdi-format-list-bulleted text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No sub-categories found</h5>
                            <p class="text-muted">Start by adding your first lab sub-category.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Category Modal -->
    @if($showSubCategoryModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingSubCategory ? 'pencil' : 'plus' }}"></i>
                            {{ $editingSubCategory ? 'Edit' : 'Add' }} Solution/Reagent Preparation Configuration
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeSubCategoryModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveSubCategory">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               wire:model="subCategoryForm.name" 
                                               class="form-control @error('subCategoryForm.name') is-invalid @enderror" 
                                               placeholder="Enter sub-category name">
                                        @error('subCategoryForm.name') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea wire:model="subCategoryForm.description" 
                                                  class="form-control @error('subCategoryForm.description') is-invalid @enderror" 
                                                  rows="3"
                                                  placeholder="Enter sub-category description"></textarea>
                                        @error('subCategoryForm.description') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-shape text-primary"></i> Category <span class="text-danger">*</span></label>
                                        <x-searchable-select
                                            wire:model.live="subCategoryForm.category_id"
                                            :options="collect($categories)->map(fn($c) => ['id' => $c->id, 'name' => $c->name])"
                                            placeholder="Search categories..."
                                            empty-label="Select category..."
                                        />
                                        @error('subCategoryForm.category_id') 
                                            <span class="text-danger">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><i class="mdi mdi-ruler text-primary"></i> Unit of Measure <span class="text-danger">*</span></label>
                                        <x-searchable-select
                                            wire:model.live="subCategoryForm.reporting_unit"
                                            :options="collect($reportingUnits)->map(fn($u) => ['id' => $u->id, 'name' => $u->name])"
                                            placeholder="Search units..."
                                            empty-label="Select unit..."
                                        />
                                        @error('subCategoryForm.reporting_unit') 
                                            <span class="text-danger">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Rate</label>
                                        <input type="text" 
                                               wire:model="subCategoryForm.rate" 
                                               class="form-control @error('subCategoryForm.rate') is-invalid @enderror" 
                                               placeholder="Enter rate of production">
                                        @error('subCategoryForm.rate') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" 
                                               wire:model="imageUpload" 
                                               class="form-control @error('imageUpload') is-invalid @enderror"
                                               accept="image/*">
                                        @error('imageUpload') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                        
                                        @if ($imageUpload)
                                            <div class="mt-2">
                                                <p class="text-muted">Preview:</p>
                                                <img src="{{ $imageUpload->temporaryUrl() }}" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        @elseif($editingSubCategory && $subCategoryForm['image'])
                                            <div class="mt-2">
                                                <p class="text-muted">Current Image:</p>
                                                <img src="{{ $subCategoryForm['image'] }}" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeSubCategoryModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveSubCategory">
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

    body.modal-open {
        overflow: hidden;
    }

    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 200px);
    }

    .modal-body {
        scroll-behavior: smooth;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .img-thumbnail {
        border-radius: 8px;
    }

    .btn-close {
        background: transparent;
        border: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        opacity: .5;
    }

    .btn-close:hover {
        opacity: .75;
    }

    #subcategories-table .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 3px;
        font-size: 12px;
    }

    #subcategories-table .rm-act-btn:last-child {
        margin-right: 0;
    }

    #subcategories-table .rm-act-btn--view {
        border: 1px solid #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    #subcategories-table .rm-act-btn--view:hover {
        background: #dcfce7;
        border-color: #86efac;
    }

    #subcategories-table .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    #subcategories-table .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    #subcategories-table .rm-act-btn--muted {
        border: 1px solid #e2e8f0;
        color: #475569;
        background: #f8fafc;
    }

    #subcategories-table .rm-act-btn--muted:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    #subcategories-table .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    #subcategories-table .rm-act-btn--delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('subcategory-modal-opened', () => {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        });
        
        Livewire.on('subcategory-modal-closed', () => {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
</div>
