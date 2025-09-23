<div>
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Rating Headers Management</h2>
                        <p class="text-muted mb-0">Manage rating systems and their categories</p>
                    </div>
                    <button wire:click="showCreateRatingHeaderModal" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add Rating Header
                    </button>
                </div>
            </div>
        </div>

        <!-- Message Display -->
        @if($message)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                    {{ $message }}
                    <button type="button" class="close" wire:click="dismissMessage">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Search and Filters -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search rating headers...">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="mdi mdi-magnify"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <select wire:model.live="perPage" class="form-control">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }} per page</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Rating Headers Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if($ratingHeaders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
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
                                                <span class="badge badge-info">{{ $header->rating_details_count }} details</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $header->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('ratings.details', $header->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Manage Details">
                                                        <i class="mdi mdi-cog"></i>
                                                    </a>
                                                    <button wire:click="showEditRatingHeaderModal({{ $header->id }})" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteRatingHeader({{ $header->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this rating header? This will also delete all associated rating details.')">
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
                            <div class="text-center py-5">
                                <i class="mdi mdi-star-outline fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No rating headers found</h5>
                                <p class="text-muted">Get started by creating your first rating header.</p>
                                <button wire:click="showCreateRatingHeaderModal" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add Rating Header
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Header Modal -->
    @if($showRatingHeaderModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editingRatingHeader ? 'Edit Rating Header' : 'Create Rating Header' }}
                    </h5>
                    <button type="button" class="close" wire:click="$set('showRatingHeaderModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
                    <button type="button" class="btn btn-secondary" wire:click="$set('showRatingHeaderModal', false)">
                        Cancel
                    </button>
                    <button type="button" 
                            class="btn btn-primary" 
                            wire:click="saveRatingHeader"
                            wire:loading.attr="disabled"
                            wire:target="saveRatingHeader">
                        <span wire:loading.remove wire:target="saveRatingHeader">
                            {{ $editingRatingHeader ? 'Update' : 'Create' }}
                        </span>
                        <span wire:loading wire:target="saveRatingHeader">
                            <i class="mdi mdi-loading mdi-spin"></i> Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>