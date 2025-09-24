<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-star text-primary"></i>
                                Report Key Configurations
                            </h2>
                            <p class="text-muted mb-0">Manage report key configurations and their ratings</p>
                        </div>
                        <button wire:click="showCreateRatingHeaderModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Key Configuration
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search key configurations...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Per Page</label>
                                <select wire:model.live="perPage" class="form-select modern-select">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
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

    <!-- Rating Headers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="mdi mdi-star-outline"></i> Key Configurations
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($ratingHeaders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Rating Details</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                    <tbody>
                                        @foreach($ratingHeaders as $header)
                                        <tr>
                                            <td>
                                                <strong>{{ $header->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $header->description ? Str::limit($header->description, 50) : 'No description' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info p-2">{{ $header->rating_details_count }} details</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $header->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('ratings.details', $header->id) }}" 
                                                       class="btn btn-sm mr-2 btn-outline-primary" 
                                                       title="Manage Details">
                                                        <i class="mdi mdi-cog"></i>
                                                    </a>
                                                    <button wire:click="showEditRatingHeaderModal({{ $header->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-2" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteRatingHeader({{ $header->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this report key configuration? This will also delete all associated rating details.')">
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
                                <div>
                                    Showing {{ $ratingHeaders->firstItem() }} to {{ $ratingHeaders->lastItem() }} 
                                    of {{ $ratingHeaders->total() }} results
                                </div>
                                <div>
                                    {{ $ratingHeaders->links() }}
                                </div>
                            </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-star-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No key configurations found</h5>
                            <p class="text-muted">Get started by creating your first key configuration.</p>
                            <button wire:click="showCreateRatingHeaderModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Report Key Configuration
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Header Modal -->
    @if($showRatingHeaderModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingRatingHeader ? 'pencil' : 'plus' }}"></i>
                            {{ $editingRatingHeader ? 'Edit' : 'Create' }} Report Key Configuration
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeRatingHeaderModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form wire:submit.prevent="saveRatingHeader">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   wire:model="ratingHeaderForm.name" 
                                   class="form-control @error('ratingHeaderForm.name') is-invalid @enderror" 
                                   id="name" 
                                   placeholder="e.g., Ecoli Rating, Salmonella Rating">
                            @error('ratingHeaderForm.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea wire:model="ratingHeaderForm.description" 
                                      class="form-control @error('ratingHeaderForm.description') is-invalid @enderror" 
                                      id="description" 
                                      rows="3" 
                                      placeholder="Optional description of this rating system"></textarea>
                            @error('ratingHeaderForm.description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeRatingHeaderModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveRatingHeader">
                            <i class="mdi mdi-content-save"></i> {{ $editingRatingHeader ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    /* Modern Select Styling */
    .modern-select {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        background: #ffffff;
        outline: none;
    }

    .modern-select:hover {
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
    }

    .modern-select option {
        padding: 10px 16px;
        font-weight: 500;
        color: #495057;
    }

    .modern-select option:hover {
        background-color: #f8f9fa;
    }

    /* Custom dropdown arrow */
    .modern-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 40px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Invalid state styling */
    .modern-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .modern-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    /* Modal styling improvements */
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
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        // Prevent body scroll when modal opens
        Livewire.on('rating-header-modal-opened', () => {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        });
        
        // Restore body scroll when modal closes
        Livewire.on('rating-header-modal-closed', () => {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
</div>