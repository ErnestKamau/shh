<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-multiple text-primary"></i>
                                Sample Submission Forms
                            </h2>
                            <p class="text-muted mb-0">Manage and track all sample submission forms</p>
                        </div>
                        <button wire:click="openCaptureModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Capture Samples
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType }} alert-dismissible fade show" role="alert">
            <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }}"></i>
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Section -->
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
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" 
                                       wire:model.live.debounce.300ms="searchTerm" 
                                       class="form-control" 
                                       placeholder="Search by form number, title...">
                                <div wire:loading wire:target="searchTerm" class="text-muted small mt-1">
                                    <i class="mdi mdi-loading mdi-spin"></i> Searching...
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="in_review">In Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Priority</label>
                                <select wire:model.live="priorityFilter" class="form-select">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Form Type</label>
                                <select wire:model.live="formTypeFilter" class="form-select">
                                    <option value="">All Forms</option>
                                    @foreach($availableForms as $form)
                                        <option value="{{ $form->id }}">{{ $form->name }}</option>
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

    <!-- Submissions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header d-flex justify-content-between align-items-center bg-light" 
                     style="border-radius: 15px 15px 0 0;">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-format-list-bulleted"></i> All Submissions
                    </h5>
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body p-0 position-relative">
                    <!-- Loading Overlay -->
                    <div wire:loading.delay wire:target="searchTerm, statusFilter, priorityFilter, formTypeFilter, perPage" 
                         class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center"
                         style="background: rgba(255,255,255,0.8); z-index: 10; min-height: 200px;">
                        <div class="text-center">
                            <i class="mdi mdi-loading mdi-spin" style="font-size: 3rem; color: #0d6efd;"></i>
                            <p class="mt-2 text-muted">Loading...</p>
                        </div>
                    </div>

                    @if($instances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Form Number</th>
                                        <th>Form Name</th>
                                        <th>Title</th>
                                        <th>Submitted By</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Submitted</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($instances as $instance)
                                        <tr>
                                            <td>
                                                <strong>{{ $instance->form_number }}</strong>
                                            </td>
                                            <td>
                                                {{ $instance->submissionForm->name }}
                                            </td>
                                            <td>
                                                {{ $instance->title ?: 'Untitled' }}
                                            </td>
                                            <td>
                                                {{ $instance->submittedBy->name ?? 'Unknown' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $this->getStatusBadgeColor($instance->status) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $this->getPriorityBadgeColor($instance->priority) }}">
                                                    {{ ucfirst($instance->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($instance->submitted_at)
                                                    {{ $instance->submitted_at->format('M d, Y H:i') }}
                                                @else
                                                    <span class="text-muted">Not submitted</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($instance->due_date)
                                                    {{ $instance->due_date->format('M d, Y') }}
                                                    @if($instance->due_date < now() && $instance->status === 'draft')
                                                        <i class="mdi mdi-alert text-danger" title="Overdue"></i>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No due date</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($instance->status === 'draft')
                                                        <a href="{{ route('submission-forms.instances.fill', [$instance->submissionForm, $instance]) }}" 
                                                           class="btn btn-sm btn-primary" 
                                                           title="Edit Draft">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                        <button wire:click="deleteInstance({{ $instance->id }})"
                                                                wire:confirm="Are you sure you want to delete this draft?"
                                                                class="btn btn-sm btn-danger" 
                                                                title="Delete Draft">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    @else
                                                        <a href="{{ route('submission-forms.instances.show', [$instance->submissionForm, $instance]) }}" 
                                                           class="btn btn-sm btn-info" 
                                                           title="View">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $instances->firstItem() }} to {{ $instances->lastItem() }} 
                                    of {{ $instances->total() }} results
                                </small>
                            </div>
                            <div>
                                {{ $instances->links() }}
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="mdi mdi-file-document-outline" style="font-size: 5rem; color: #ccc;"></i>
                            <h4 class="mt-3 text-muted">No Submissions Found</h4>
                            <p class="text-muted">
                                @if($searchTerm || $statusFilter || $priorityFilter || $formTypeFilter)
                                    No submissions match your current filters. Try adjusting your search criteria.
                                @else
                                    No submissions have been created yet.
                                @endif
                            </p>
                            <button wire:click="openCaptureModal" class="btn btn-primary mt-3">
                                <i class="mdi mdi-plus"></i> Create First Submission
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Capture Samples Modal -->
    @if($showCaptureModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-document-plus"></i> Select Submission Form
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCaptureModal"></button>
                    </div>
                    <div class="modal-body">
                        @if($availableForms->count() > 0)
                            <div class="form-group mb-3">
                                <label for="formSelect" class="control-label">Choose a Form:</label>
                                <select class="form-select" 
                                        id="formSelect" 
                                        wire:model.live="selectedFormId"
                                        required>
                                    <option value="">Select a submission form...</option>
                                    @foreach($availableForms as $form)
                                        <option value="{{ $form->id }}">{{ $form->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Form Preview -->
                            @if($selectedFormPreview)
                                <div class="card mt-3 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $selectedFormPreview->name }}</h6>
                                        <p class="card-text text-muted">
                                            {{ $selectedFormPreview->description ?: 'No description available' }}
                                        </p>
                                        <small class="text-info d-block">
                                            <i class="mdi mdi-file-document"></i> 
                                            <span>{{ $selectedFormPreview->sections_count }} sections</span> | 
                                            <i class="mdi mdi-account"></i> 
                                            <span>Created by {{ $selectedFormPreview->creator->name ?? 'Unknown' }}</span> | 
                                            <i class="mdi mdi-calendar"></i> 
                                            <span>{{ $selectedFormPreview->created_at->format('M d, Y') }}</span>
                                        </small>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info text-center">
                                <i class="mdi mdi-information"></i> No published submission forms available.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCaptureModal">
                            Cancel
                        </button>
                        <button type="button" 
                                class="btn btn-primary" 
                                wire:click="createFormInstance"
                                @if(!$selectedFormId) disabled @endif
                                wire:loading.attr="disabled"
                                wire:target="createFormInstance">
                            <span wire:loading.remove wire:target="createFormInstance">
                                <i class="mdi mdi-arrow-right"></i> Create & Continue
                            </span>
                            <span wire:loading wire:target="createFormInstance">
                                <i class="mdi mdi-loading mdi-spin"></i> Creating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

