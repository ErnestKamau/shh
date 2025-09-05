@extends('layouts.lab.layout.app')

@section('title2')
<title>Certificate Templates | Lab Management</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('lab-home'),
            'name' => 'Lab Management',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Certificate Templates',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- Filter Section -->
    <div class="bg-light p-3 mb-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                <label for="submission-form-filter" class="form-label">Filter by Submission Form:</label>
                <select id="submission-form-filter" class="form-control" onchange="filterBySubmissionForm(this.value)">
                    <option value="">All Submission Forms</option>
                    @foreach($submissionForms as $form)
                        <option value="{{ $form->id }}" {{ $submissionFormId == $form->id ? 'selected' : '' }}>
                            {{ $form->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-primary">
                    <i class="mdi mdi-file-document-edit"></i> Manage Submission Forms
                </a>
            </div>
        </div>
    </div>

    <div class="bg-light p-4">
        <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-certificate"></i> Certificate Templates
                            </h5>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('certificate-templates.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Create Template
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="templates-table" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Submission Form</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Sections</th>
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
                                        <span class="badge badge-secondary">{{ $template->reports_count }}</span>
                                    </td>
                                    <td>{{ $template->creator->name ?? 'Unknown' }}</td>
                                    <td>{{ $template->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('certificate-templates.show', $template) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ route('certificate-templates.builder', $template) }}" 
                                               class="btn btn-sm btn-outline-success" title="Builder">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <a href="{{ route('certificate-templates.edit', $template) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="mdi mdi-edit"></i>
                                            </a>
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
                                                    <button class="dropdown-item toggle-published" 
                                                            data-id="{{ $template->id }}" 
                                                            data-published="{{ $template->is_published }}">
                                                        <i class="mdi mdi-{{ $template->is_published ? 'eye-off' : 'eye' }}"></i> 
                                                        {{ $template->is_published ? 'Unpublish' : 'Publish' }}
                                                    </button>
                                                    <button class="dropdown-item toggle-active" 
                                                            data-id="{{ $template->id }}" 
                                                            data-active="{{ $template->is_active }}">
                                                        <i class="mdi mdi-{{ $template->is_active ? 'pause' : 'play' }}"></i> 
                                                        {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="{{ route('certificate-templates.duplicate', $template) }}"
                                                       onclick="return confirm('Are you sure you want to duplicate this template?')">
                                                        <i class="mdi mdi-content-copy"></i> Duplicate
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <button class="dropdown-item text-danger delete-template" 
                                                            data-id="{{ $template->id }}" 
                                                            data-name="{{ $template->name }}">
                                                        <i class="mdi mdi-delete"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the template "<span id="template-name"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
    </div>
</main>
@endsection

@push('styles')
<style>
    .table th {
        background-color: #f8f9fa;
        border-top: none;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endpush

@push('script2')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#templates-table').DataTable({
        "order": [[ 7, "desc" ]], // Sort by created_at desc
        "pageLength": 25,
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'excel',
                text: '<i class="mdi mdi-file-excel"></i> Export Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="mdi mdi-file-pdf"></i> Export PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="mdi mdi-printer"></i> Print',
                className: 'btn btn-info btn-sm'
            }
        ],
        "columnDefs": [
            { "orderable": false, "targets": 8 } // Actions column
        ]
    });

    // Toggle Published Status
    $('.toggle-published').click(function() {
        const templateId = $(this).data('id');
        const isPublished = $(this).data('published');
        const button = $(this);
        
        $.ajax({
            url: `/certificate-templates/${templateId}/toggle-published`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update the badge
                    const badge = button.closest('tr').find('.badge-success, .badge-secondary');
                    if (response.is_published) {
                        badge.removeClass('badge-secondary').addClass('badge-success').text('Published');
                        button.find('i').removeClass('mdi-eye').addClass('mdi-eye-off');
                        button.html('<i class="mdi mdi-eye-off"></i> Unpublish');
                        button.data('published', true);
                    } else {
                        badge.removeClass('badge-success').addClass('badge-secondary').text('Draft');
                        button.find('i').removeClass('mdi-eye-off').addClass('mdi-eye');
                        button.html('<i class="mdi mdi-eye"></i> Publish');
                        button.data('published', false);
                    }
                    
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the template status.');
            }
        });
    });

    // Toggle Active Status
    $('.toggle-active').click(function() {
        const templateId = $(this).data('id');
        const isActive = $(this).data('active');
        const button = $(this);
        
        $.ajax({
            url: `/certificate-templates/${templateId}/toggle-active`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update the badge
                    const badge = button.closest('tr').find('.badge-primary, .badge-warning');
                    if (response.is_active) {
                        badge.removeClass('badge-warning').addClass('badge-primary').text('Active');
                        button.find('i').removeClass('mdi-play').addClass('mdi-pause');
                        button.html('<i class="mdi mdi-pause"></i> Deactivate');
                        button.data('active', true);
                    } else {
                        badge.removeClass('badge-primary').addClass('badge-warning').text('Inactive');
                        button.find('i').removeClass('mdi-pause').addClass('mdi-play');
                        button.html('<i class="mdi mdi-play"></i> Activate');
                        button.data('active', false);
                    }
                    
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the template status.');
            }
        });
    });

    // Delete Template
    $('.delete-template').click(function() {
        const templateId = $(this).data('id');
        const templateName = $(this).data('name');
        
        $('#template-name').text(templateName);
        $('#delete-form').attr('action', `/certificate-templates/${templateId}`);
        $('#deleteModal').modal('show');
    });
});

// Filter by submission form
function filterBySubmissionForm(submissionFormId) {
    const url = new URL(window.location);
    if (submissionFormId) {
        url.searchParams.set('submission_form_id', submissionFormId);
    } else {
        url.searchParams.delete('submission_form_id');
    }
    window.location.href = url.toString();
}
</script>
@endpush
