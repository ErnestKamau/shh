<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="p-1 mb-1">
                <i class="mdi mdi-tag"></i> Ticket Categories
            </h2>
            <p class="text-muted mb-0">Manage your ticket categories</p>
        </div>
        <div>
            <button class="btn btn-primary mr-2" wire:click="openCreateModal">
                <i class="mdi mdi-plus"></i> Add Category
            </button>
            <a href="{{ route('tickets.create') }}" class="btn btn-outline-primary">
                <i class="mdi mdi-plus"></i> Create New Ticket
            </a>
        </div>
    </div>

    @error('toggle')
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="toggle-error-alert">
            <i class="mdi mdi-alert-circle"></i> {{ $message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            (function() {
                var alert = document.getElementById('toggle-error-alert');
                if (alert) {
                    setTimeout(function() {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            if (alert && alert.parentNode) {
                                alert.remove();
                            }
                        }, 500);
                    }, 5000);
                }
            })();
        </script>
    @enderror

    <div class="card">
        <div class="card-body">
            @if($categories->count() > 0)
            <div class="row">
                @foreach($categories as $category)
                <div class="col-md-4 mb-3">
                    <div class="card border {{ !$category->active ? 'bg-light' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <i class="mdi mdi-tag"></i> {{ $category->name }}
                                </h5>
                                <div>
                                    @if($category->active)
                                    <span class="badge badge-success">Active</span>
                                    @else
                                    <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            @if($category->description)
                            <p class="card-text text-muted">{{ $category->description }}</p>
                            @endif
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-info" 
                                    wire:click="openEditModal({{ $category->id }})">
                                    <i class="mdi mdi-pencil"></i> Edit
                                </button>
                                <button wire:click="toggleStatus({{ $category->id }})" 
                                    class="btn btn-sm {{ $category->active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleStatus({{ $category->id }})">
                                    <span wire:loading.remove wire:target="toggleStatus({{ $category->id }})">
                                        <i class="mdi mdi-{{ $category->active ? 'close-circle' : 'check-circle' }}"></i> 
                                        {{ $category->active ? 'Deactivate' : 'Activate' }}
                                    </span>
                                    <span wire:loading wire:target="toggleStatus({{ $category->id }})">
                                        <i class="mdi mdi-loading mdi-spin"></i>
                                    </span>
                                </button>
                                @if($category->tickets()->count() == 0)
                                <button wire:click="openDeleteModal({{ $category->id }})" 
                                    class="btn btn-sm btn-outline-danger">
                                    <i class="mdi mdi-delete"></i> Delete
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-info text-center">
                <i class="mdi mdi-information" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No categories available</h5>
                <p>Create your first category to get started.</p>
                <button class="btn btn-primary mt-2" wire:click="openCreateModal">
                    <i class="mdi mdi-plus"></i> Add Category
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Create Category Modal -->
    @if($showCreateModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="store">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="close" wire:click="closeModals">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="form.name" class="form-control" 
                                placeholder="e.g., Bug Report, Feature Request, Technical Issue" required maxlength="255">
                            @error('form.name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea wire:model="form.description" rows="3" class="form-control" 
                                placeholder="Brief description of this category..." maxlength="1000"></textarea>
                            @error('form.description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="store">
                                <i class="mdi mdi-check"></i> Create Category
                            </span>
                            <span wire:loading wire:target="store">
                                <i class="mdi mdi-loading mdi-spin"></i> Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Edit Category Modal -->
    @if($showEditModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="update">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Category</h5>
                        <button type="button" class="close" wire:click="closeModals">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="form.name" class="form-control" required maxlength="255">
                            @error('form.name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea wire:model="form.description" rows="3" class="form-control" maxlength="1000"></textarea>
                            @error('form.description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="update">
                                <i class="mdi mdi-check"></i> Update Category
                            </span>
                            <span wire:loading wire:target="update">
                                <i class="mdi mdi-loading mdi-spin"></i> Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Delete Category Modal -->
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Category</h5>
                    <button type="button" class="close" wire:click="closeModals">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this category? This action cannot be undone.</p>
                    @error('delete')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="delete">
                            <i class="mdi mdi-delete"></i> Delete
                        </span>
                        <span wire:loading wire:target="delete">
                            <i class="mdi mdi-loading mdi-spin"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

