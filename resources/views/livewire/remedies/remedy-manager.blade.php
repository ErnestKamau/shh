<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-medical-bag text-primary"></i>
                                Remedies Management
                            </h2>
                            <p class="text-muted mb-0">Manage treatment options and remedy protocols</p>
                        </div>
                        <button wire:click="showCreateRemedyHeaderModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Remedy
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
            <div class="card">
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search remedies...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Per Page</label>
                                <select wire:model.live="perPage" class="form-select">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Remedies Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Remedies</h5>
                </div>
                <div class="card-body">
                    @if($remedyHeaders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Details Count</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($remedyHeaders as $remedyHeader)
                                        <tr>
                                            <td>
                                                <strong>{{ $remedyHeader->name }}</strong>
                                            </td>
                                            <td>
                                                {{ $remedyHeader->description ? \Illuminate\Support\Str::limit($remedyHeader->description, 50) : '-' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-info" style="color: white;">{{ $remedyHeader->remedy_details_count }}</span>
                                            </td>
                                            <td>
                                                {{ $remedyHeader->created_at->format('M d, Y') }}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('remedies.details', $remedyHeader->id) }}" 
                                                       class="btn btn-sm mr-1 btn-outline-primary" 
                                                       title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button wire:click="showEditRemedyHeaderModal({{ $remedyHeader->id }})" 
                                                            class="btn btn-sm mr-1 btn-outline-warning" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="cloneRemedyHeader({{ $remedyHeader->id }})" 
                                                            class="btn btn-sm mr-1 btn-outline-info" 
                                                            title="Clone"
                                                            onclick="return confirm('Are you sure you want to clone this remedy?')">
                                                        <i class="mdi mdi-content-copy"></i>
                                                    </button>
                                                    <button wire:click="deleteRemedyHeader({{ $remedyHeader->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this remedy? This action cannot be undone.')">
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
                                Showing {{ $remedyHeaders->firstItem() }} to {{ $remedyHeaders->lastItem() }} 
                                of {{ $remedyHeaders->total() }} results
                            </div>
                            <div>
                                {{ $remedyHeaders->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-medical-bag text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">No Remedies Found</h4>
                            <p class="text-muted">Start by creating your first remedy.</p>
                            <button wire:click="showCreateRemedyHeaderModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Remedy
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Remedy Header Modal -->
    @if($showRemedyHeaderModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingRemedyHeader ? 'pencil' : 'plus' }}"></i>
                            {{ $editingRemedyHeader ? 'Edit' : 'Create' }} Remedy
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeRemedyHeaderModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveRemedyHeader">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Remedy Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               wire:model="remedyHeaderForm.name" 
                                               class="form-control @error('remedyHeaderForm.name') is-invalid @enderror" 
                                               placeholder="Enter remedy name...">
                                        @error('remedyHeaderForm.name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea wire:model="remedyHeaderForm.description" 
                                                  class="form-control @error('remedyHeaderForm.description') is-invalid @enderror" 
                                                  rows="3" 
                                                  placeholder="Enter remedy description..."></textarea>
                                        @error('remedyHeaderForm.description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeRemedyHeaderModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveRemedyHeader">
                            <i class="mdi mdi-content-save"></i> {{ $editingRemedyHeader ? 'Update' : 'Create' }} Remedy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
