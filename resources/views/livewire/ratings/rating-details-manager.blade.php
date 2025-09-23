<div>
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">{{ $ratingHeader->name }} - Rating Details</h2>
                        <p class="text-muted mb-0">{{ $ratingHeader->description ?: 'Manage rating details for this rating system' }}</p>
                    </div>
                    <div>
                        <a href="{{ route('ratings.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="mdi mdi-arrow-left"></i> Back to Headers
                        </a>
                        <button wire:click="showCreateRatingDetailModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Rating Detail
                        </button>
                    </div>
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search rating details...">
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

        <!-- Rating Details Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if($ratingDetails->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Key</th>
                                            <th>Label</th>
                                            <th>Interpretation</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ratingDetails as $detail)
                                        <tr>
                                            <td>
                                                <span class="badge badge-primary">{{ $detail->key }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $detail->label }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ Str::limit($detail->interpretation, 100) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $detail->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditRatingDetailModal({{ $detail->id }})" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteRatingDetail({{ $detail->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this rating detail?')">
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
                                    Showing {{ $ratingDetails->firstItem() }} to {{ $ratingDetails->lastItem() }} 
                                    of {{ $ratingDetails->total() }} results
                                </div>
                                <div>
                                    {{ $ratingDetails->links() }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-star-outline fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No rating details found</h5>
                                <p class="text-muted">Get started by creating your first rating detail for {{ $ratingHeader->name }}.</p>
                                <button wire:click="showCreateRatingDetailModal" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add Rating Detail
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Detail Modal -->
    @if($showRatingDetailModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editingRatingDetail ? 'Edit Rating Detail' : 'Create Rating Detail' }}
                    </h5>
                    <button type="button" class="close" wire:click="$set('showRatingDetailModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveRatingDetail">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="key">Key <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           wire:model="ratingDetailForm.key" 
                                           class="form-control @error('ratingDetailForm.key') is-invalid @enderror" 
                                           id="key" 
                                           placeholder="e.g., A+, PASS, HIGH, 1-5">
                                    @error('ratingDetailForm.key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Symbol or shorthand for the rating</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="label">Label <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           wire:model="ratingDetailForm.label" 
                                           class="form-control @error('ratingDetailForm.label') is-invalid @enderror" 
                                           id="label" 
                                           placeholder="e.g., Excellent, Safe, Critical">
                                    @error('ratingDetailForm.label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Short description of the rating</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="interpretation">Interpretation <span class="text-danger">*</span></label>
                            <textarea wire:model="ratingDetailForm.interpretation" 
                                      class="form-control @error('ratingDetailForm.interpretation') is-invalid @enderror" 
                                      id="interpretation" 
                                      rows="4" 
                                      placeholder="Detailed explanation of what this rating means"></textarea>
                            @error('ratingDetailForm.interpretation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Detailed explanation of what this rating means</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showRatingDetailModal', false)">
                        Cancel
                    </button>
                    <button type="button" 
                            class="btn btn-primary" 
                            wire:click="saveRatingDetail"
                            wire:loading.attr="disabled"
                            wire:target="saveRatingDetail">
                        <span wire:loading.remove wire:target="saveRatingDetail">
                            {{ $editingRatingDetail ? 'Update' : 'Create' }}
                        </span>
                        <span wire:loading wire:target="saveRatingDetail">
                            <i class="mdi mdi-loading mdi-spin"></i> Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
