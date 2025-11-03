<div class="container-fluid">
    <style>
        .submissions-table tbody tr {
            background-color: white !important;
        }
        .submissions-table tbody tr:hover {
            background-color: #f8f9fa !important;
        }
        .submissions-table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .submissions-table td {
            vertical-align: middle;
            padding: 0.75rem 0.5rem;
        }
        #formSelect:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1) !important;
            outline: none;
        }
        #formSelect:hover {
            border-color: #667eea;
        }
        #formSelect option {
            padding: 0.75rem;
            font-size: 0.95rem;
        }
        #formSelect option:hover {
            background-color: #f7fafc;
        }
        @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(-10px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
    </style>

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

    <!-- Submissions Table with Integrated Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
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
                    
                    <!-- Integrated Filter Row -->
                   
                </div>
                <div class="card-body">
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="form-label fw-bold small">Search</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="searchTerm" 
                                           class="form-control form-control-sm" 
                                           placeholder="Search by form number, title...">
                                    <div wire:loading wire:target="searchTerm" class="position-absolute top-50 end-0 translate-middle-y me-2">
                                        <i class="mdi mdi-loading mdi-spin text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="form-label fw-bold small">Status</label>
                                <select wire:model.live="statusFilter" class="form-select form-select-sm">
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
                            <div class="form-group mb-2">
                                <label class="form-label fw-bold small">Priority</label>
                                <select wire:model.live="priorityFilter" class="form-select form-select-sm">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="form-label fw-bold small">Form Type</label>
                                <select wire:model.live="formTypeFilter" class="form-select form-select-sm">
                                    <option value="">All Forms</option>
                                    @foreach($availableForms as $form)
                                        <option value="{{ $form->id }}">{{ $form->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="form-label fw-bold small">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                    @if($instances->count() > 0)
                        <div class="table-responsive mt-5">
                            <table class="table table-hover mb-0 submissions-table" style="font-size: 0.875rem;">
                                <thead class="table-dark">
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
                                                        <a href="{{ route('submission-forms.instances.fill-sample', [$instance->submissionForm, $instance]) }}" 
                                                           class="btn btn-sm btn-primary mr-2" 
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
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                    <div class="modal-header border-0" style="padding: 2rem 2rem 1rem 2rem; background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 249, 250, 0.8) 100%); border-radius: 20px 20px 0 0;">
                        <h5 class="modal-title fw-bold" style="font-size: 1.5rem; color: #495057;">
                            <i class="mdi mdi-file-document-plus me-2"></i> Select Submission Form
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCaptureModal"></button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        @if($availableForms->count() > 0)
                            <div class="form-group mb-4">
                                <label for="formSelect" class="form-label fw-bold mb-3" style="color: #2d3748; font-size: 1rem;">
                                    <i class="mdi mdi-format-list-bulleted text-primary"></i> Choose a Form
                                </label>
                                <div class="position-relative">
                                    <select class="form-select form-select-lg" 
                                            id="formSelect" 
                                            wire:model.live="selectedFormId"
                                            required
                                            style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 0.875rem 1rem; font-size: 1rem; transition: all 0.3s ease;">
                                        <option value="" style="color: #a0aec0;">Select a submission form...</option>
                                        @foreach($availableForms as $form)
                                            <option value="{{ $form->id }}" style="padding: 0.5rem;">{{ $form->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3" wire:loading wire:target="selectedFormId">
                                        <i class="mdi mdi-loading mdi-spin text-primary" style="font-size: 1.25rem;"></i>
                                    </div>
                                </div>
                               
                            </div>

                            <!-- Form Preview -->
                            @if($selectedFormPreview)
                                <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #f6f8fb 0%, #ffffff 100%); animation: fadeIn 0.3s ease-in;">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-shrink-0 mr-3">
                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                    <i class="mdi mdi-file-document" style="font-size: 1.75rem;"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="card-title mb-2 fw-bold" style="color: #2d3748; font-size: 1.25rem;">
                                                    {{ $selectedFormPreview->name }}
                                                </h6>
                                                <p class="card-text text-muted mb-3" style="font-size: 0.95rem; line-height: 1.6;">
                                                    {{ $selectedFormPreview->description ?: 'No description available' }}
                                                </p>
                                                <div class="d-flex flex-wrap gap-3">
                                                    <small class="d-flex align-items-center" style="color: #667eea; font-weight: 500;">
                                                        <i class="mdi mdi-file-document me-1" style="font-size: 1.1rem;"></i>
                                                        <span>{{ $selectedFormPreview->sections_count }} sections</span>
                                                    </small>
                                                    <small class="d-flex align-items-center" style="color: #667eea; font-weight: 500;">
                                                        <i class="mdi mdi-account me-1" style="font-size: 1.1rem;"></i>
                                                        <span>{{ $selectedFormPreview->creator->name ?? 'Unknown' }}</span>
                                                    </small>
                                                    <small class="d-flex align-items-center" style="color: #667eea; font-weight: 500;">
                                                        <i class="mdi mdi-calendar me-1" style="font-size: 1.1rem;"></i>
                                                        <span>{{ $selectedFormPreview->created_at->format('M d, Y') }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert border-0 text-center py-5" style="background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); border-radius: 15px;">
                                <i class="mdi mdi-information-outline text-info" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0 fw-semibold" style="color: #0369a1; font-size: 1.1rem;">No published submission forms available.</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0" style="padding: 1rem 2rem 2rem 2rem; background-color: #f8fafc; border-radius: 0 0 20px 20px;">
                        <button type="button" class="btn btn-sm px-4" wire:click="closeCaptureModal" style="border-radius: 10px; border: 2px solid #e2e8f0; background: white; color: #4a5568; font-weight: 500; transition: all 0.3s ease;">
                            <i class="mdi mdi-close me-1"></i> Cancel
                        </button>
                        <button type="button" 
                                class="btn btn-primary btn-sm px-4" 
                                wire:click="createFormInstance"
                                @if(!$selectedFormId) disabled @endif
                                wire:loading.attr="disabled"
                                wire:target="createFormInstance"
                                style="border-radius: 10px; border: none; color: white; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s ease;">
                            <span wire:loading.remove wire:target="createFormInstance">
                                <i class="mdi mdi-arrow-right me-1"></i> Create & Continue
                            </span>
                            <span wire:loading wire:target="createFormInstance">
                                <i class="mdi mdi-loading mdi-spin me-1"></i> Creating...
                            </span>
                        </button>
                        <style>
                            .modal-footer .btn:hover:not(:disabled) {
                                transform: translateY(-2px);
                                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
                            }
                            .modal-footer .btn:disabled {
                                opacity: 0.6;
                                cursor: not-allowed;
                            }
                            .modal-footer .btn-lg:first-child:hover {
                                background-color: #f7fafc;
                                border-color: #cbd5e0;
                            }
                        </style>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

