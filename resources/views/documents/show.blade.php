@extends('layouts.documents.layout.app')

@section('title2')
    <title>{{ $document->name }} - Imara LIMS</title>
@endsection

@section('content2')
<style>
    .document-header-card {
        background-color: #f8f9fa;
        border-bottom: 2px solid #eaeeef;
    }
    .info-label {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-size: 1rem;
        color: #343a40;
        margin-bottom: 1.25rem;
    }
    .action-buttons .btn {
        margin-bottom: 0.5rem;
    }
    .timeline-alt .timeline-item {
        margin-bottom: 1.5rem;
    }
    .badge-soft-success {
        background-color: rgba(10, 207, 151, 0.18);
        color: #0acf97;
    }
    .badge-soft-warning {
        background-color: rgba(255, 188, 0, 0.18);
        color: #ffbc00;
    }
    .badge-soft-info {
        background-color: rgba(57, 175, 209, 0.18);
        color: #39afd1;
    }
    .badge-soft-primary {
        background-color: rgba(114, 124, 245, 0.18);
        color: #727cf5;
    }
</style>

<main>
    <?php
    $items = array(
        array(
            'link' => '/home',
            'name' => 'Home',
            'icon' => null
        ),
        array(
            'link' => '/documents/dashboard',
            'name' => 'Documents',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $document->name,
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="row mb-3">
        <div class="col-12">
            <div class="card document-header-card mb-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <span class="avatar-title bg-primary-lighten text-primary rounded">
                                    <i class="mdi mdi-book-open-page-variant font-24"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="m-0 text-dark">{{ $document->name }}</h3>
                                <p class="text-muted mb-0 font-14">Document Number: <strong>{{ $document->document_number }}</strong> | Version <strong>{{ $document->version }}</strong></p>
                            </div>
                        </div>
                        <div class="action-buttons d-flex flex-wrap justify-content-end gap-2">
                            <a href="{{ $document->file_url }}" class="btn btn-dark" target="_blank">
                                <i class="mdi mdi-eye"></i> View File
                            </a>
                            <a href="{{ route('documents.create', ['parent_id' => $document->id]) }}" class="btn btn-info">
                                <i class="mdi mdi-content-copy"></i> New Version
                            </a>
                            <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-primary">
                                <i class="mdi mdi-pencil"></i> Edit
                            </a>
                            @if($document->is_published)
                                <form action="{{ route('documents.unpublish', $document->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to unpublish this document?')">
                                        <i class="mdi mdi-share-variant-off"></i> Unpublish
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('documents.publish', $document->id) }}" class="btn btn-success">
                                    <i class="mdi mdi-share-variant"></i> Publish
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Details Column -->
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4 bg-light p-2 rounded">Basic Information</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="info-label">Document Type</div>
                                <div class="info-value"><span class="badge bg-primary rounded-pill px-2 py-1">{{ $document->documentType->name }}</span></div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    @if($document->status === 'active')
                                        <span class="badge badge-soft-success font-13"><i class="mdi mdi-check-circle me-1"></i>Active</span>
                                    @elseif($document->status === 'draft')
                                        <span class="badge badge-soft-warning font-13"><i class="mdi mdi-pencil-circle me-1"></i>Draft</span>
                                    @else
                                        <span class="badge badge-soft-secondary font-13"><i class="mdi mdi-archive me-1"></i>Archived</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="info-label">Department</div>
                                <div class="info-value">{{ $document->department->name }}</div>
                            </div>
                            @if($document->validity_period)
                            <div class="mb-3">
                                <div class="info-label">Validity Period</div>
                                <div class="info-value">
                                    @if($document->isExpired())
                                        <span class="text-danger fw-bold"><i class="mdi mdi-alert-circle"></i> Expired on {{ $document->validity_period->format('M d, Y') }}</span>
                                    @elseif($document->isExpiringSoon())
                                        <span class="text-warning fw-bold"><i class="mdi mdi-clock-alert"></i> Expires on {{ $document->validity_period->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-success fw-bold"><i class="mdi mdi-calendar-check"></i> Valid until {{ $document->validity_period->format('M d, Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($document->description)
                    <div class="mt-2 mb-4">
                        <div class="info-label">Description</div>
                        <p class="text-muted bg-light p-3 rounded border border-light">{{ $document->description }}</p>
                    </div>
                    @endif

                    <h4 class="header-title mb-4 bg-light p-2 rounded">File Details</h4>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="info-label">File Name</div>
                            <div class="info-value text-break">
                                <i class="mdi mdi-file-outline text-muted me-1"></i> {{ $document->file_name }}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="info-label">File Type</div>
                            <div class="info-value">{{ strtoupper($document->file_type) }}</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="info-label">File Size</div>
                            <div class="info-value">{{ $document->file_size_formatted }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4 bg-light p-2 rounded">Publishing & Availability</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="info-label">Publishment Status</div>
                                <div class="info-value">
                                    @if($document->is_published)
                                        <span class="badge badge-soft-success font-13"><i class="mdi mdi-earth me-1"></i> Published</span>
                                    @else
                                        <span class="badge badge-soft-warning font-13"><i class="mdi mdi-lock me-1"></i> Not Published</span>
                                    @endif
                                </div>
                            </div>
                            @if($document->is_published)
                            <div class="mb-3">
                                <div class="info-label">Published By</div>
                                <div class="info-value">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2">
                                            <span class="avatar-title bg-secondary rounded-circle">{{ substr($document->publisher->name ?? 'N', 0, 1) }}</span>
                                        </div>
                                        <span>{{ $document->publisher->name ?? 'N/A' }}</span>
                                    </div>
                                    <small class="text-muted mt-1 d-block">{{ $document->published_at ? $document->published_at->format('M d, Y H:i') : 'N/A' }}</small>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            @if($document->is_published)
                                <div class="mb-3">
                                    <div class="info-label">Publish Scope</div>
                                    <div class="info-value">
                                        <span class="badge bg-info mb-1">{{ $document->publish_scope_label }}</span>
                                        
                                        @if($document->publish_scope === 'all_departments')
                                            <p class="text-muted font-13 mb-0">Accessible to everyone in all departments.</p>
                                        @elseif($document->publish_scope === 'all_roles')
                                            <p class="text-muted font-13 mb-0">Accessible to everyone with valid roles.</p>
                                        @elseif($document->publish_scope === 'department' && $document->publish_targets)
                                            @php $departments = \App\InventoryDepartment::whereIn('id', $document->publish_targets)->get(); @endphp
                                            <div class="mt-2">
                                                <p class="mb-1 font-13 text-muted">Specific Departments ({{ $departments->count() }}):</p>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($departments as $dept)
                                                        <span class="badge badge-soft-primary">{{ $dept->name }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif($document->publish_scope === 'role' && $document->publish_targets)
                                            @php $roles = \App\Models\Auth\Role::whereIn('id', $document->publish_targets)->get(); @endphp
                                            <div class="mt-2">
                                                <p class="mb-1 font-13 text-muted">Specific Roles ({{ $roles->count() }}):</p>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($roles as $role)
                                                        <span class="badge badge-soft-info">{{ $role->name }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif($document->publish_scope === 'mixed' && $document->publish_targets)
                                            @php
                                                $departments = \App\InventoryDepartment::whereIn('id', $document->publish_targets['departments'] ?? [])->get();
                                                $roles = \App\Models\Auth\Role::whereIn('id', $document->publish_targets['roles'] ?? [])->get();
                                            @endphp
                                            <div class="mt-2">
                                                @if($departments->count() > 0)
                                                    <p class="mb-1 font-13 mt-2 text-muted">Departments:</p>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($departments as $dept)
                                                            <span class="badge badge-soft-primary">{{ $dept->name }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if($roles->count() > 0)
                                                    <p class="mb-1 font-13 mt-2 text-muted">Roles:</p>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($roles as $role)
                                                            <span class="badge badge-soft-info">{{ $role->name }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments Card -->
            @if($document->attachments && $document->attachments->count() > 0)
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4 bg-light p-2 rounded"><i class="mdi mdi-paperclip me-1"></i> Attachments</h4>
                    <div class="table-responsive">
                        <table class="table table-borderless table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Uploaded By</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($document->attachments as $attachment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title bg-primary-lighten text-primary rounded">
                                                        <i class="mdi mdi-file-document-outline font-18"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h5 class="m-0 font-14">{{ $attachment->file_name }}</h5>
                                                    <span class="text-muted font-12">{{ strtoupper($attachment->file_type) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $attachment->file_size_formatted }}</td>
                                        <td>{{ $attachment->uploader->name ?? 'Unknown' }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('documents.attachments.download', $attachment->id) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                                    <i class="mdi mdi-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAttachment({{ $attachment->id }})" title="Delete">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Column -->
        <div class="col-xl-4 col-lg-5">
            <!-- Timeline details card -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4">Tracking Information</h4>
                    
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-calendar-plus text-primary font-20 me-3"></i>
                            <div>
                                <h5 class="mt-0 mb-1 font-14">Created On</h5>
                                <span class="text-muted font-13">{{ $document->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-account-plus text-primary font-20 me-3"></i>
                            <div>
                                <h5 class="mt-0 mb-1 font-14">Created By</h5>
                                <span class="text-muted font-13">{{ $document->creator->name ?? 'System' }}</span>
                            </div>
                        </li>
                        
                        @if($document->updater)
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-calendar-edit text-info font-20 me-3"></i>
                            <div>
                                <h5 class="mt-0 mb-1 font-14">Last Updated</h5>
                                <span class="text-muted font-13">{{ $document->updated_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-account-edit text-info font-20 me-3"></i>
                            <div>
                                <h5 class="mt-0 mb-1 font-14">Updated By</h5>
                                <span class="text-muted font-13">{{ $document->updater->name ?? 'System' }}</span>
                            </div>
                        </li>
                        @endif

                        <li class="mt-4 pt-3 border-top d-flex align-items-center">
                            @if($document->notifications_enabled)
                                <i class="mdi mdi-bell-ring text-success font-24 me-3"></i>
                                <div>
                                    <h5 class="mt-0 mb-1 font-14">Notifications Enabled</h5>
                                    @if($document->notificationFrequency)
                                        <span class="text-muted font-13">{{ $document->notificationFrequency->name }}</span>
                                    @else
                                        <span class="text-muted font-13">Active</span>
                                    @endif
                                </div>
                            @else
                                <i class="mdi mdi-bell-off text-secondary font-24 me-3"></i>
                                <div>
                                    <h5 class="mt-0 mb-1 font-14 text-muted">Notifications Disabled</h5>
                                    <span class="text-muted font-13">No automatic emails will be sent</span>
                                </div>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Version History -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4">Version History</h4>
                    
                    @if($versionHistory && $versionHistory->count() > 1)
                        <div class="timeline-alt pb-0" style="max-height: 350px; overflow-y: auto;">
                            @foreach($versionHistory as $version)
                                <div class="timeline-item">
                                    @if($version->id === $document->id)
                                        <i class="mdi mdi-circle bg-success-lighten text-success timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <span class="text-success fw-bold mb-1 d-block">{{ $version->name }}</span>
                                            <small class="text-muted">Version {{ $version->version }} | {{ $version->created_at->format('M d, Y') }}</small>
                                            <div class="mt-1 d-flex align-items-center gap-2">
                                                <span class="badge bg-success">Current</span>
                                                <small class="text-muted">by {{ $version->creator->name ?? 'Unknown' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <i class="mdi mdi-circle bg-secondary-lighten text-secondary timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="{{ route('documents.show', $version->id) }}" class="text-dark fw-semibold mb-1 d-block hover-primary">{{ $version->name }}</a>
                                            <small class="text-muted">Version {{ $version->version }} | {{ $version->created_at->format('M d, Y') }}</small>
                                            <div class="mt-1 d-flex align-items-center gap-2">
                                                @if($version->is_published)
                                                    <span class="badge badge-soft-success">Published</span>
                                                @else
                                                    <span class="badge badge-soft-warning">Draft</span>
                                                @endif
                                                <small class="text-muted">by {{ $version->creator->name ?? 'Unknown' }}</small>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="avatar-md mx-auto mb-3">
                                <span class="avatar-title bg-light text-muted rounded-circle" style="font-size: 24px;">
                                    <i class="mdi mdi-history"></i>
                                </span>
                            </div>
                            <h5 class="text-muted">No Version History</h5>
                            <p class="text-muted font-13 mb-0">This is the first and only version of this document.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Explore Links -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Quick Links</h4>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('documents.dashboard') }}" class="btn btn-outline-primary btn-block text-start">
                            <i class="mdi mdi-view-dashboard me-2"></i> Documents Dashboard
                        </a>
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-block text-start">
                            <i class="mdi mdi-format-list-bulleted me-2"></i> View All Documents
                        </a>
                        <a href="{{ route('documents.create') }}" class="btn btn-outline-success btn-block text-start">
                            <i class="mdi mdi-cloud-upload me-2"></i> Upload New Document
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Delete Attachment Confirmation Form -->
<form id="deleteAttachmentForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
    function deleteAttachment(attachmentId) {
        if (confirm('Are you certain you want to delete this attachment? This cannot be undone.')) {
            const form = document.getElementById('deleteAttachmentForm');
            form.action = `/documents/attachments/${attachmentId}`;
            form.submit();
        }
    }
</script>
@endsection

