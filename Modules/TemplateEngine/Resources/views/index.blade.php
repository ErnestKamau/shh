@extends('layouts.lab.layout.app', ['dataTable'=>true])

@section('content2')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-file-alt text-primary"></i>
                                Report Templates
                            </h2>
                            <p class="text-muted mb-0">Manage report templates and builders</p>
                        </div>
                        <a href="{{ route('templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Search and Filter Section -->
            <form method="GET" action="{{ route('templates.index') }}" id="searchForm" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted">Search</label>
                            <div class="input-group">
                                <input type="text" 
                                       name="search" 
                                       id="searchInput"
                                       class="form-control" 
                                       placeholder="Search by name, description, category..." 
                                       value="{{ request('search') }}"
                                       autocomplete="off">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted">Status</label>
                            <select name="status" id="statusFilter" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted">Category</label>
                            <select name="category" id="categoryFilter" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted">Per Page</label>
                            <select name="per_page" id="perPageFilter" class="form-control">
                                <option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted d-block">&nbsp;</label>
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                @if(request()->hasAny(['search', 'status', 'category', 'per_page']))
                                    <a href="{{ route('templates.index') }}" class="btn btn-secondary" title="Clear filters">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td><strong>{{ $template->name }}</strong></td>
                                    <td><span class="badge badge-info">{{ $template->category ?? 'N/A' }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $template->status === 'published' ? 'success' : ($template->status === 'archived' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($template->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $template->creator->name ?? 'N/A' }}</td>
                                    <td>{{ $template->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Metadata">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('templates.builder', $template->id) }}" class="btn btn-sm btn-outline-info" title="Builder">
                                                <i class="fas fa-tools"></i>
                                            </a>
                                            <a href="{{ route('templates.preview', $template->id) }}" class="btn btn-sm btn-outline-secondary" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Delete"
                                                    data-toggle="modal" 
                                                    data-target="#deleteTemplateModal"
                                                    data-template-id="{{ $template->id }}"
                                                    data-template-name="{{ $template->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $templates->firstItem() ?? 0 }} to {{ $templates->lastItem() ?? 0 }} of {{ $templates->total() }} templates
                        </div>
                        <div>
                            {{ $templates->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-alt text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No templates found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status', 'category']))
                            Try adjusting your search or filters.
                            <a href="{{ route('templates.index') }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                        @else
                            Create a new template to get started.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTemplateModal" tabindex="-1" role="dialog" aria-labelledby="deleteTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteTemplateModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Delete Template
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>
                    
                    <p class="mb-3">
                        You are about to delete the template: <strong id="templateNameDisplay"></strong>
                    </p>
                    
                    <div class="border-left border-danger pl-3 mb-3">
                        <h6 class="text-danger mb-2"><i class="fas fa-info-circle mr-1"></i> Consequences:</h6>
                        <ul class="mb-0 pl-3">
                            <li>All report fields and configurations will be permanently deleted</li>
                            <li>All report submissions associated with this template will be deleted</li>
                            <li>All uploaded images and files in submissions will be removed</li>
                            <li>This template will no longer be accessible to any users</li>
                            <li>Any links or references to this template will break</li>
                        </ul>
                    </div>
                    
                    <p class="text-muted small mb-0">
                        <i class="fas fa-shield-alt mr-1"></i> 
                        Are you absolutely sure you want to proceed with this deletion?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <form id="deleteTemplateForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i> Yes, Delete Template
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script2')
<script>
    (function() {
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const categoryFilter = document.getElementById('categoryFilter');
        const searchForm = document.getElementById('searchForm');

        // Live search with debouncing (500ms delay)
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    searchForm.submit();
                }, 500);
            });
        }

        // Auto-submit on filter change
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                searchForm.submit();
            });
        }

        if (categoryFilter) {
            categoryFilter.addEventListener('change', function() {
                searchForm.submit();
            });
        }

        // Auto-submit on per page change
        const perPageFilter = document.getElementById('perPageFilter');
        if (perPageFilter) {
            perPageFilter.addEventListener('change', function() {
                searchForm.submit();
            });
        }

        // Clear search on Escape key
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    searchForm.submit();
                }
            });
        }

        // Delete Template Modal Handler
        $(document).ready(function() {
            $('#deleteTemplateModal').on('show.bs.modal', function(event) {
                // Button that triggered the modal
                const button = $(event.relatedTarget);
                
                // Extract info from data-* attributes
                const templateId = button.data('template-id');
                const templateName = button.data('template-name');
                
                // Update modal content
                $('#templateNameDisplay').text(templateName);
                
                // Update form action
                const deleteForm = $('#deleteTemplateForm');
                deleteForm.attr('action', '{{ url("/form-templates") }}/' + templateId);
            });
        });
    })();
</script>
@endsection
