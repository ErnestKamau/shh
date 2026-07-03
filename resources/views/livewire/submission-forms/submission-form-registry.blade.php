<div class="container-fluid px-0">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-form-select text-primary"></i>
                                Submission Forms
                            </h2>
                            <p class="text-muted mb-0">Design, publish, and manage laboratory submission form templates</p>
                        </div>
                        <a href="{{ route('submission-forms.create') }}" class="btn btn-outline-primary" style="border-radius: 9px;">
                            <i class="mdi mdi-plus"></i> Create Form
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <div id="submission-forms-registry-filters" class="submission-forms-registry-filters">
                    <div class="row align-items-start">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <div class="position-relative">
                                    <input type="text"
                                           wire:model.live.debounce.300ms="search"
                                           class="form-control"
                                           placeholder="Search by name or description...">
                                    <div wire:loading wire:target="search" class="position-absolute top-50 end-0 translate-middle-y me-2">
                                        <i class="mdi mdi-loading mdi-spin text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <div class="tag-select-container status-filter-container">
                                    <div class="tag-select-input status-filter-input">
                                        <select wire:model.live="statusFilter" class="form-control tag-select-native">
                                            <option value="">All Status</option>
                                            <option value="published">Published</option>
                                            <option value="draft">Draft</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Created By</label>
                                <div class="tag-select-container creator-filter-container">
                                    <div class="tag-select-input creator-filter-input">
                                        <select wire:model.live="creatorId" class="form-control tag-select-native">
                                            <option value="">All creators</option>
                                            @foreach($this->creatorOptions as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button type="button" wire:click="clearFilters" class="btn btn-outline-secondary w-100">
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

    <!-- Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->forms->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->forms->firstItem() ?? 0 }} to {{ $this->forms->lastItem() ?? 0 }} of {{ $this->forms->total() }} entries
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="sfRegistryPerPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                <select wire:model.live="perPage" id="sfRegistryPerPage" class="form-select form-select-sm" style="width: auto;">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover submission-forms-registry-data-table" id="submission-forms-registry-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th class="text-center">Submission Start No.</th>
                                        <th>Lab Sections</th>
                                        <th class="text-center">Sections</th>
                                        <th class="text-center">Instances</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Created</th>
                                        <th class="submission-forms-registry-actions-col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->forms as $form)
                                        <tr wire:key="sf-registry-{{ $form->id }}">
                                            <td>
                                                <strong>{{ $form->name }}</strong>
                                                <br>
                                                <small class="text-muted">v{{ $form->version }}</small>
                                            </td>
                                            <td>
                                                <div style="max-width: 200px;">
                                                    {{ \Illuminate\Support\Str::limit($form->description, 100) }}
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $form->start_submission_number }}</td>
                                            <td>
                                                @foreach($form->sampleAnalysisStages as $stage)
                                                    <span class="badge badge-outline-primary mb-1">{{ $stage->name }}</span>
                                                @endforeach
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $form->sections_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ $form->instances_count }}</span>
                                            </td>
                                            <td>
<div>
                                                    @if($form->is_published)
                                                        <span class="badge badge-success">Published</span>
                                                    @else
                                                        <span class="badge badge-warning">Draft</span>
                                                    @endif
                                                </div>
                                                <div class="mt-1">
                                                    @if($form->is_active)
                                                        <span class="badge badge-outline-success">Active</span>
                                                    @else
                                                        <span class="badge badge-outline-danger">Inactive</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $form->creator->name ?? 'Unknown' }}</td>
                                            <td><small>{{ $form->created_at->format('M d, Y') }}</small></td>
                                            <td class="submission-forms-registry-actions-cell">
                                                <div class="d-flex align-items-center flex-nowrap sf-registry-actions-inner">
                                                    <a href="{{ route('submission-forms.show', $form) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                       title="View">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('submission-forms.edit', $form) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                       title="Edit">
                                                        <i class="mdi mdi-pencil-outline"></i>
                                                    </a>
                                                    <a href="{{ route('submission-forms.preview', $form) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--muted"
                                                       title="Preview">
                                                        <i class="mdi mdi-eye-outline"></i>
                                                    </a>
                                                    <form method="POST"
                                                          action="{{ route('submission-forms.clone', $form) }}"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to clone this form?')">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--muted"
                                                                title="Clone">
                                                            <i class="mdi mdi-content-copy"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST"
                                                          action="{{ route('submission-forms.toggle-published', $form) }}"
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--muted"
                                                                title="{{ $form->is_published ? 'Unpublish' : 'Publish' }}">
                                                            <i class="mdi {{ $form->is_published ? 'mdi-eye-off' : 'mdi-publish' }}"></i>
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('submission-forms.export', $form) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--muted"
                                                       title="Export">
                                                        <i class="mdi mdi-download"></i>
                                                    </a>
                                                    <form method="POST"
                                                          action="{{ route('submission-forms.destroy', $form) }}"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this form? This action cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                                title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->forms->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-form-select text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">No submission forms found</h4>
                            <p class="text-muted">
                                @if($search !== '' || $statusFilter !== '' || $creatorId !== '')
                                    Try adjusting your filters or
                                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline" wire:click="clearFilters">clear filters</button>.
                                @else
                                    Get started by creating your first submission form.
                                @endif
                            </p>
                            <a href="{{ route('submission-forms.create') }}" class="btn btn-primary mt-3">
                                <i class="mdi mdi-plus"></i> Create Your First Form
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Tag select (match resources/views/livewire/analysis/element-manager.blade.php filters + native select) */
    #submission-forms-registry-filters .tag-select-container {
        position: relative;
        cursor: pointer;
        width: 100%;
    }

    #submission-forms-registry-filters .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-height: 42px;
        padding: 6px 12px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    #submission-forms-registry-filters .tag-select-input:hover {
        border-color: var(--color-primary);
    }

    #submission-forms-registry-filters .tag-select-input:focus-within {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.2rem var(--color-primary-focus);
        outline: none;
    }

    #submission-forms-registry-filters .status-filter-input,
    #submission-forms-registry-filters .creator-filter-input {
        padding: 0 12px;
    }

    #submission-forms-registry-filters .tag-select-native.form-control,
    #submission-forms-registry-filters .tag-select-native.form-select,
    #submission-forms-registry-filters select.tag-select-native {
        width: 100%;
        border: none !important;
        box-shadow: none !important;
        background-color: transparent;
        padding: 10px 2rem 10px 0;
        min-height: 42px;
        line-height: 1.25;
    }

    #submission-forms-registry-filters .tag-select-native.form-control:focus,
    #submission-forms-registry-filters .tag-select-native.form-select:focus,
    #submission-forms-registry-filters select.tag-select-native:focus {
        border: none !important;
        box-shadow: none !important;
        background-color: transparent;
        outline: none;
    }

    #submission-forms-registry-table.submission-forms-registry-data-table tbody tr {
        background-color: #fff !important;
    }

    #submission-forms-registry-table.submission-forms-registry-data-table tbody tr:hover {
        background-color: #f8f9fa !important;
    }

    #submission-forms-registry-table.submission-forms-registry-data-table tbody td {
        background-color: inherit;
        border-color: #e9ecef;
        vertical-align: middle;
    }

    #submission-forms-registry-table .submission-forms-registry-actions-cell {
        white-space: nowrap;
        vertical-align: middle;
    }

    #submission-forms-registry-table .sf-registry-actions-inner {
        gap: 6px;
    }

    #submission-forms-registry-table .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 0;
        font-size: 12px;
    }

    #submission-forms-registry-table .rm-act-btn--view {
        border: 1px solid #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    #submission-forms-registry-table .rm-act-btn--view:hover {
        background: #dcfce7;
        border-color: #86efac;
    }

    #submission-forms-registry-table .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    #submission-forms-registry-table .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    #submission-forms-registry-table .rm-act-btn--delete {
        border: 1px solid #fecdd3;
        color: #e11d48;
        background: #fff5f7;
    }

    #submission-forms-registry-table .rm-act-btn--delete:hover {
        background: #ffe4e6;
        border-color: #fda4af;
    }

    #submission-forms-registry-table .rm-act-btn--muted {
        border: 1px solid #e2e8f0;
        color: #475569;
        background: #f8fafc;
    }

    #submission-forms-registry-table .rm-act-btn--muted:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    /* Match previous inline forms + buttons alignment */
    #submission-forms-registry-table .sf-registry-actions-inner form {
        margin: 0;
    }
    </style>
</div>
