<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-format-list-bulleted-type text-primary"></i>
                                Lab Inventory Categories
                            </h2>
                            <p class="text-muted mb-0">Manage lab inventory categories and monitor stock levels</p>
                        </div>
                        <button wire:click="showCreateCategoryModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Category
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
                        <div class="col-md-9">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name or description...">
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

    <!-- Categories Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->categories->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->categories->firstItem() ?? 0 }} to {{ $this->categories->lastItem() ?? 0 }} of {{ $this->categories->total() }} entries
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
                            <table class="table table-striped table-hover" id="categories-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 100px;">Actions</th>
                                        <th style="width: 150px;">Image</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Available</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->categories as $category)
                                        <tr>
                                            <td>
                                                <div class="d-flex">
                                                    <button wire:click="showEditCategoryModal({{ $category->id }})"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteCategory({{ $category->id }})"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this category?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                @if($category->image)
                                                    <img src="{{ $category->image }}" class="img-thumbnail" style="width: 125px; height: 125px; object-fit: cover;" alt="{{ $category->name }}">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 125px; height: 125px;">
                                                        <i class="mdi mdi-image-off text-muted" style="font-size: 2rem;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $category->name }}</strong>
                                            </td>
                                            <td>{{ $category->description }}</td>
                                            <td>
                                                @php
                                                    $available = $category->available();
                                                @endphp
<div>
                                                    <strong>{{ number_format($available['available']) }}</strong>
                                                </div>
                                                <small class="text-muted">
                                                    (+{{ number_format($available['pending']) }} pending)
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->categories->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-format-list-bulleted-type text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No categories found</h5>
                            <p class="text-muted">Start by adding your first lab inventory category.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    @if($showCategoryModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingCategory ? 'pencil' : 'plus' }}"></i>
                            {{ $editingCategory ? 'Edit' : 'Create' }} Category
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCategoryModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveCategory">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               wire:model="categoryForm.name" 
                                               class="form-control @error('categoryForm.name') is-invalid @enderror" 
                                               placeholder="Enter category name">
                                        @error('categoryForm.name') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea wire:model="categoryForm.description" 
                                                  class="form-control @error('categoryForm.description') is-invalid @enderror" 
                                                  rows="3"
                                                  placeholder="Enter category description"></textarea>
                                        @error('categoryForm.description') 
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
                                        @elseif($editingCategory && $categoryForm['image'])
                                            <div class="mt-2">
                                                <p class="text-muted">Current Image:</p>
                                                <img src="{{ $categoryForm['image'] }}" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCategoryModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveCategory">
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

    /* Prevent body scroll when modal is open */
    body.modal-open {
        overflow: hidden;
    }

    /* Ensure modal is properly positioned and scrollable */
    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 200px);
    }

    /* Smooth scrolling for modal content */
    .modal-body {
        scroll-behavior: smooth;
    }

    /* Ensure modal backdrop doesn't interfere with scrolling */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,0.5);
    }

    /* Table styling improvements */
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

    #categories-table .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 3px;
        font-size: 12px;
    }

    #categories-table .rm-act-btn:last-child {
        margin-right: 0;
    }

    #categories-table .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    #categories-table .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    #categories-table .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    #categories-table .rm-act-btn--delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        // Prevent body scroll when modal opens
        Livewire.on('category-modal-opened', () => {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        });
        
        // Restore body scroll when modal closes
        Livewire.on('category-modal-closed', () => {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
</div>
