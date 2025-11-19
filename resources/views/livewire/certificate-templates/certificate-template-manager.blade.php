<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-certificate text-primary"></i>
                                Certificate Templates Management
                            </h2>
                            <p class="text-muted mb-0">Manage certificate templates for submission forms</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('certificate-templates.create') }}" class="btn btn-sm mr-2 btn-primary">
                                <i class="mdi mdi-plus"></i> Create Template
                            </a>
                            <a href="{{ route('submission-forms.index') }}" class="btn btn-sm btn-outline-primary" title="Manage Submission Forms">
                                <i class="mdi mdi-file-document-edit"></i> Manage Submission Forms
                            </a>
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
                                <input type="text" wire:model.live="search" class="modern-input" placeholder="Search by name or description...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Submission Form</label>
                                <select wire:model.live="submissionFormFilter" class="modern-select">
                                    <option value="">All Submission Forms</option>
                                    @foreach($submissionForms as $form)
                                        <option value="{{ $form->id }}">{{ $form->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="modern-select">
                                    <option value="">All Status</option>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button wire:click="clearFilters" class="btn btn-outline-secondary flex-fill">
                                        <i class="mdi mdi-refresh"></i> Clear
                                    </button>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($templates->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $templates->firstItem() ?? 0 }} to {{ $templates->lastItem() ?? 0 }} of {{ $templates->total() }} entries
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
                            <table class="table table-striped table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Submission Form</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Sections</th>
                                        <th>Holders</th>
                                        <th>Elements</th>
                                        <th>Reports</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                        <tr>
                                            <td>{{ $template->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">{{ $template->name }}</h6>
                                                        <small class="text-muted">v{{ $template->version }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('submission-forms.show', $template->submissionForm) }}" class="text-decoration-none">
                                                    <i class="mdi mdi-file-document-edit"></i> {{ $template->submissionForm->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $template->description }}">
                                                    {{ $template->description ?: 'No description' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-{{ $template->is_published ? 'success' : 'secondary' }} mb-1">
                                                        {{ $template->is_published ? 'Published' : 'Draft' }}
                                                    </span>
                                                    <span class="badge badge-{{ $template->is_active ? 'primary' : 'warning' }}">
                                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $template->sections_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $template->sections->sum(function($s) { return $s->elementHolders->count(); }) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">{{ $template->elements_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $template->reports_count }}</span>
                                            </td>
                                            <td>{{ $template->creator->name ?? 'Unknown' }}</td>
                                            <td>{{ $template->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('certificate-templates.show', $template) }}" 
                                                       class="btn btn-sm btn-outline-primary mr-2" title="View">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('certificate-templates.builder', $template) }}" 
                                                       class="btn btn-sm btn-outline-success mr-2" title="Builder">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('certificate-templates.edit', $template) }}" 
                                                       class="btn btn-sm btn-outline-warning mr-2" title="Edit">
                                                        <i class="mdi mdi-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger mr-2" 
                                                            wire:click="openDeleteModal({{ $template->id }})"
                                                            title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="mdi mdi-dots-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="{{ route('certificate-templates.preview', $template) }}">
                                                                <i class="mdi mdi-eye-outline"></i> Preview
                                                            </a>
                                                            <a class="dropdown-item" href="{{ route('certificate-templates.pdf-preview', $template) }}" target="_blank">
                                                                <i class="mdi mdi-file-pdf"></i> PDF Preview
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <button class="dropdown-item" 
                                                                    wire:click="togglePublished({{ $template->id }})">
                                                                <i class="mdi mdi-{{ $template->is_published ? 'eye-off' : 'eye' }}"></i> 
                                                                {{ $template->is_published ? 'Unpublish' : 'Publish' }}
                                                            </button>
                                                            <button class="dropdown-item" 
                                                                    wire:click="toggleActive({{ $template->id }})">
                                                                <i class="mdi mdi-{{ $template->is_active ? 'pause' : 'play' }}"></i> 
                                                                {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item" href="{{ route('certificate-templates.duplicate', $template) }}"
                                                               onclick="return confirm('Are you sure you want to duplicate this template?')">
                                                                <i class="mdi mdi-content-copy"></i> Duplicate
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $templates->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-certificate-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No templates found</h5>
                            <p class="text-muted">Start by creating your first certificate template.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the template "<strong>{{ $templateToDeleteName }}</strong>"?</p>
                        <p class="text-danger"><small>This action cannot be undone. All associated data will be deleted.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteTemplate">
                            <i class="mdi mdi-delete"></i> Delete
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
    
        .btn-group .btn {
            margin-right: 5px;
        }
    
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    
        .btn-group .btn-group {
            margin-left: 5px;
        }
    
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Modern Input Styling */
        .modern-input {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            width: 100%;
            background-color: #ffffff;
        }

        .modern-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background-color: #ffffff;
            outline: none;
        }

        .modern-input:hover {
            border-color: #007bff;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
        }

        .modern-input::placeholder {
            color: #6c757d;
            opacity: 0.7;
        }

        /* Modern Select Styling */
        .modern-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 40px 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px 16px;
            cursor: pointer;
        }

        .modern-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background-color: #ffffff;
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
            background-color: #ffffff;
        }

        .modern-select option:hover {
            background-color: #f8f9fa;
        }

        .modern-select option:checked {
            background-color: #007bff;
            color: #ffffff;
        }

        /* Invalid state styling */
        .modern-select.is-invalid,
        .modern-input.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .modern-select.is-invalid:focus,
        .modern-input.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
</div>

