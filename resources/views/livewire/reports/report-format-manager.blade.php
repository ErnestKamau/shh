<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-outline text-primary"></i>
                                Report Formats Management
                            </h2>
                            <p class="text-muted mb-0">Manage report formats for sample types</p>
                        </div>
                        <button wire:click="showCreateReportFormatModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Report Format
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
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search report formats...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select modern-select">
                                    <option value="">All Status</option>
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
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

    <!-- Report Formats Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Report Formats</h5>
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                            @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($this->reportFormats->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Sample Types</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->reportFormats as $reportFormat)
                                <tr>
                                    <td>
                                        <span class="badge p-2 bg-secondary">{{ $reportFormat->report_code }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $reportFormat->report_name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge p-2 bg-info">{{ $reportFormat->sampleTypes->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge p-2 bg-{{ $reportFormat->is_active ? 'success' : 'danger' }}">
                                            {{ $reportFormat->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('livewire.report-formats.builder', $reportFormat->id) }}"
                                                class="btn btn-sm mr-1 btn-outline-info"
                                                title="Builder">
                                                <i class="mdi mdi-table-cog"></i>
                                            </a>
                                            <button wire:click="showEditReportFormatModal({{ $reportFormat->id }})"
                                                class="btn btn-sm mr-1 btn-outline-warning"
                                                title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button wire:click="deleteReportFormat({{ $reportFormat->id }})"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this report format?')">
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
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3">
                                Showing {{ $this->reportFormats->firstItem() ?? 0 }} to {{ $this->reportFormats->lastItem() ?? 0 }} of {{ $this->reportFormats->total() }} entries
                            </span>
                        </div>
                        <div>
                            {{ $this->reportFormats->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="mdi mdi-file-document-outline text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No report formats found</h5>
                        <p class="text-muted">Create your first report format to get started.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Report Format Modal -->
    @if($showReportFormatModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingReportFormat ? 'pencil' : 'plus' }}"></i>
                        {{ $editingReportFormat ? 'Edit' : 'Create' }} Report Format
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeReportFormatModal"></button>
                </div>
                <div class="modal-body">
                    <!-- Error Display -->
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading">
                            <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                        </h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form wire:submit.prevent="saveReportFormat">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="reportFormatForm.report_name" class="form-control @error('reportFormatForm.report_name') is-invalid @enderror">
                                    @error('reportFormatForm.report_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="reportFormatForm.report_code" class="form-control @error('reportFormatForm.report_code') is-invalid @enderror">
                                    @error('reportFormatForm.report_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" wire:model="reportFormatForm.is_active" class="form-check-input" id="is_active">
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeReportFormatModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveReportFormat">
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
    </style>
</div>