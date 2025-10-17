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
                        <div>
                            <button wire:click="toggleFilters" class="btn btn-outline-warning me-2">
                                <i class="mdi mdi-filter-variant"></i> Filter
                            </button>
                            <button wire:click="showCreateSubCategoryModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Item
                            </button>
                        </div>
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
    @if($showFilters)
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name or description...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Filter by Category</label>
                                <div class="row">
                                    @foreach($categories as $category)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="checkbox" wire:model.live="selectedCategories" value="{{ $category->id }}" class="form-check-input" id="cat_{{ $category->id }}">
                                                <label class="form-check-label" for="cat_{{ $category->id }}">
                                                    {{ $category->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button wire:click="clearFilters" class="btn btn-outline-secondary">
                                <i class="mdi mdi-refresh"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                            <table class="table table-striped table-hover" id="subcategories-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">No</th>
                                        <th style="width: 150px;">Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Rate</th>
                                        <th>Unit Measure</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                        <th style="width: 250px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->subCategories as $subCategory)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
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
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditSubCategoryModal({{ $subCategory->id }})" 
                                                            class="btn btn-sm btn-outline-primary" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <a href="{{ route('show_lab_sub_category', ['id' => $subCategory->id]) }}" 
                                                       class="btn btn-sm btn-outline-success" 
                                                       title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button wire:click="cloneSubCategory({{ $subCategory->id }})" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            title="Clone"
                                                            onclick="return confirm('Are you sure you want to clone this sub-category?')">
                                                        <i class="mdi mdi-content-duplicate"></i>
                                                    </button>
                                                    <button wire:click="deleteSubCategory({{ $subCategory->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this sub-category?')">
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
                            {{ $editingSubCategory ? 'Edit' : 'Add' }} Sub-Category
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
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <select wire:model="subCategoryForm.category_id" 
                                                class="form-select @error('subCategoryForm.category_id') is-invalid @enderror">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('subCategoryForm.category_id') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                                        <select wire:model="subCategoryForm.reporting_unit" 
                                                class="form-select @error('subCategoryForm.reporting_unit') is-invalid @enderror">
                                            <option value="">Select Unit of Measure</option>
                                            @foreach($reportingUnits as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('subCategoryForm.reporting_unit') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
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
