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
                                {{ $remedyHeader->name }} - Details
                            </h2>
                            <p class="text-muted mb-0">{{ $remedyHeader->description }}</p>
                        </div>
                        <div>
                            <a href="{{ route('remedies.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="mdi mdi-arrow-left"></i> Back to Remedies
                            </a>
                            <button wire:click="showCreateRemedyDetailModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Antibiotic
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
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search antibiotics...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Sensitivity</label>
                                <select wire:model.live="sensitivityFilter" class="form-select modern-select">
                                    <option value="">All Sensitivities</option>
                                    <option value="Sensitive">Sensitive</option>
                                    <option value="Resistant">Resistant</option>
                                    <option value="Intermediate">Intermediate</option>
                                </select>
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
                        <div class="col-md-2">
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

    <!-- Remedy Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="mdi mdi-pill"></i> Remedy Details
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($remedyDetails->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Antibiotic</th>
                                        <th>Sensitivity</th>
                                        <th>Dimension</th>
                                        <th>Comments</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($remedyDetails as $remedyDetail)
                                        <tr>
                                            <td>
                                                <strong>{{ $remedyDetail->antibiotic }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $sensitivityClass = match($remedyDetail->sensitivity) {
                                                        'Sensitive' => 'bg-success',
                                                        'Resistant' => 'bg-danger',
                                                        'Intermediate' => 'bg-warning',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge p-2 {{ $sensitivityClass }}">{{ $remedyDetail->sensitivity }}</span>
                                            </td>
                                            <td>
                                                {{ $remedyDetail->dimension ?: '-' }}
                                            </td>
                                            <td>
                                                {{ $remedyDetail->comments ? \Illuminate\Support\Str::limit($remedyDetail->comments, 50) : '-' }}
                                            </td>
                                            <td>
                                                {{ $remedyDetail->created_at->format('M d, Y') }}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditRemedyDetailModal({{ $remedyDetail->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-2" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteRemedyDetail({{ $remedyDetail->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this remedy detail? This action cannot be undone.')">
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
                                Showing {{ $remedyDetails->firstItem() }} to {{ $remedyDetails->lastItem() }} 
                                of {{ $remedyDetails->total() }} results
                            </div>
                            <div>
                                {{ $remedyDetails->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-pill text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">No Remedy Details Found</h4>
                            <p class="text-muted">Start by adding antibiotics to this remedy.</p>
                            <button wire:click="showCreateRemedyDetailModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Add Antibiotic
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Remedy Detail Modal -->
    @if($showRemedyDetailModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingRemedyDetail ? 'pencil' : 'plus' }}"></i>
                            {{ $editingRemedyDetail ? 'Edit' : 'Add' }} Antibiotic
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeRemedyDetailModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <form wire:submit.prevent="saveRemedyDetail">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Antibiotic <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               wire:model="remedyDetailForm.antibiotic" 
                                               class="form-control @error('remedyDetailForm.antibiotic') is-invalid @enderror" 
                                               placeholder="Enter antibiotic name...">
                                        @error('remedyDetailForm.antibiotic')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Sensitivity <span class="text-danger">*</span></label>
                                        <select wire:model="remedyDetailForm.sensitivity" 
                                                class="form-select modern-select @error('remedyDetailForm.sensitivity') is-invalid @enderror">
                                            <option value="">Select Sensitivity</option>
                                            <option value="Sensitive">Sensitive</option>
                                            <option value="Resistant">Resistant</option>
                                            <option value="Intermediate">Intermediate</option>
                                        </select>
                                        @error('remedyDetailForm.sensitivity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Dimension</label>
                                        <input type="text" 
                                               wire:model="remedyDetailForm.dimension" 
                                               class="form-control @error('remedyDetailForm.dimension') is-invalid @enderror" 
                                               placeholder="e.g., dosage unit, formulation, strength...">
                                        @error('remedyDetailForm.dimension')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Comments</label>
                                        <textarea wire:model="remedyDetailForm.comments" 
                                                  class="form-control @error('remedyDetailForm.comments') is-invalid @enderror" 
                                                  rows="3" 
                                                  placeholder="Enter additional comments..."></textarea>
                                        @error('remedyDetailForm.comments')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeRemedyDetailModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveRemedyDetail">
                            <i class="mdi mdi-content-save"></i> {{ $editingRemedyDetail ? 'Update' : 'Add' }} Antibiotic
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
        Livewire.on('remedy-detail-modal-opened', () => {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        });
        
        // Restore body scroll when modal closes
        Livewire.on('remedy-detail-modal-closed', () => {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
</div>
