@extends('layouts.documents.layout.app')

@section('title2')
    <title>Create Document - Imara LIMS</title>
@endsection

@section('content2')
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
            'name' => 'Create Document',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="p-1">
        <i class="mdi mdi-book-open-page-variant text-deep-orange"></i> Create Document
    </h2><br>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="mdi mdi-form-textbox"></i> Document Information
                        </h5>
                        <div class="d-flex" style="gap: 1rem;">
                            <button type="button" class="btn btn-outline-primary btn-lg" onclick="showSingleUpload()" id="single-btn">
                                <i class="mdi mdi-file-plus"></i> Single Upload
                            </button>
                            <button type="button" class="btn btn-outline-success btn-lg" onclick="showBulkUpload()" id="bulk-btn">
                                <i class="mdi mdi-upload-multiple"></i> Bulk Import
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Single Upload Form -->
                    <div id="single-upload-form">
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                            
                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-information"></i> Basic Information
                                    </h6>
                                </div>
                                
                            <div class="col-md-6">
                                <div class="mb-3">
                                        <label for="name" class="form-label fw-bold">Document Name *</label>
                                        <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name') }}" 
                                               placeholder="Enter document name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label fw-bold">Location *</label>
                                    <select class="form-select form-select-lg @error('location') is-invalid @enderror" 
                                            id="location" name="location" required>
                                        <option value="">Select Document Type or Folder...</option>
                                        @foreach($folderTree as $node)
                                            <option value="{{ $node->id }}" {{ old('location') == $node->id ? 'selected' : '' }} class="{{ isset($node->is_type) && $node->is_type ? 'fw-bold' : '' }}" data-type-id="{{ $node->document_type_id }}">
                                                {!! $node->name !!}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                        </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                        <label for="version" class="form-label fw-bold">Version *</label>
                                        <input type="number" class="form-control form-control-lg @error('version') is-invalid @enderror" 
                                               id="version" name="version" value="{{ old('version', '1') }}" min="0.1" step="0.1" 
                                               placeholder="Enter version" required>
                                    @error('version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                        <label for="validity_period" class="form-label fw-bold">Validity Period</label>
                                        <input type="date" class="form-control form-control-lg @error('validity_period') is-invalid @enderror" 
                                               id="validity_period" name="validity_period" 
                                               value="{{ old('validity_period') }}">
                                    @error('validity_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-bold">Status *</label>
                                        <select class="form-select form-select-lg @error('status') is-invalid @enderror" 
                                                id="status" name="status" required>
                                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                            </div>
                        </div>
                            </div>

                            <!-- Expiry Notifications and Description -->
                            <div class="row mb-4">
                                <!-- Expiry Notifications -->
                                    <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-bell-ring"></i> Expiry Notifications
                                    </h6>
                                    
                                        <div class="mb-3">
                                            <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notifications_enabled" 
                                                   name="notifications_enabled" value="1" 
                                                   {{ old('notifications_enabled', '1') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="notifications_enabled">
                                                Enable expiry notifications
                                                </label>
                                    </div>
                                </div>

                                    <div id="notification-settings" style="display: {{ old('notifications_enabled', '1') ? 'block' : 'none' }};">
                                        <div class="mb-3">
                                            <label for="notification_frequency_id" class="form-label fw-bold">Notification Frequency</label>
                                            <select class="form-select form-select-lg @error('notification_frequency_id') is-invalid @enderror" 
                                                    id="notification_frequency_id" name="notification_frequency_id">
                                                <option value="">Select Frequency...</option>
                                                @foreach($notificationFrequencies as $frequency)
                                                    <option value="{{ $frequency->id }}" {{ old('notification_frequency_id') == $frequency->id ? 'selected' : '' }}>
                                                        {{ $frequency->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('notification_frequency_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                    </div>
                                    
                                        <div class="mb-3">
                                            <label for="notification_days_before_expiry" class="form-label fw-bold">Days Before Expiry</label>
                                            <input type="number" class="form-control form-control-lg @error('notification_days_before_expiry') is-invalid @enderror" 
                                                   id="notification_days_before_expiry" name="notification_days_before_expiry" 
                                                   value="{{ old('notification_days_before_expiry', '30') }}" min="1" max="365" 
                                                   placeholder="Enter days">
                                            @error('notification_days_before_expiry')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-text"></i> Description
                                    </h6>
                                    
                                <div class="mb-3">
                                        <label for="description" class="form-label fw-bold">Document Description</label>
                                        <textarea class="form-control form-control-lg @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="6" 
                                                  placeholder="Enter document description">{{ old('description') }}</textarea>
                                        @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                            <!-- Document Publishing -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-share-variant"></i> Document Publishing
                                    </h6>
                            </div>
                                
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                        <label for="publish_scope" class="form-label fw-bold">Publish To</label>
                                        <select class="form-select form-select-lg @error('publish_scope') is-invalid @enderror" 
                                                    id="publish_scope" name="publish_scope">
                                                <option value="">Not Published (Draft)</option>
                                                <option value="role" {{ old('publish_scope') == 'role' ? 'selected' : '' }}>Specific Roles</option>
                                                <option value="department" {{ old('publish_scope') == 'department' ? 'selected' : '' }}>Specific Departments</option>
                                                <option value="all_departments" {{ old('publish_scope') == 'all_departments' ? 'selected' : '' }}>All Departments</option>
                                                <option value="all_roles" {{ old('publish_scope') == 'all_roles' ? 'selected' : '' }}>All Roles</option>
                                                <option value="mixed" {{ old('publish_scope') == 'mixed' ? 'selected' : '' }}>Departments & Roles (Mixed)</option>
                                            </select>
                                            @error('publish_scope')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Role Selection -->
                            <div class="row mb-4" id="role-selection" style="display: none;">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="mdi mdi-account-group"></i> Select Roles</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($roles as $role)
                                                    <div class="col-md-4 col-sm-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="publish_targets[]" value="{{ $role->id }}" 
                                                                   id="role_{{ $role->id }}"
                                                                   {{ in_array($role->id, old('publish_targets', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                                {{ $role->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Department Selection -->
                            <div class="row mb-4" id="department-selection" style="display: none;">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="mdi mdi-domain"></i> Select Departments</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($departments as $dept)
                                                    <div class="col-md-4 col-sm-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="publish_targets[]" value="{{ $dept->id }}" 
                                                                   id="dept_{{ $dept->id }}"
                                                                   {{ in_array($dept->id, old('publish_targets', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="dept_{{ $dept->id }}">
                                                                {{ $dept->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>

                            <!-- Mixed Selection Info -->
                            <div class="row mb-4" id="mixed-selection" style="display: none;">
                                <div class="col-12">
                                        <div class="alert alert-info">
                                        <h6><i class="mdi mdi-information"></i> Mixed Selection</h6>
                                        <p class="mb-0">You can select both roles and departments. Use the checkboxes above to choose specific roles and departments.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-file-upload"></i> Document File
                                    </h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="document_file" class="form-label fw-bold">Select File *</label>
                                        <div class="file-input-wrapper">
                                            <input type="file" class="form-control form-control-lg @error('document_file') is-invalid @enderror" 
                                                   id="document_file" name="document_file" required>
                                            <label for="document_file" class="file-input-label">
                                                <i class="mdi mdi-file-upload"></i>
                                                <span id="document_file_text">Choose a file or drag it here</span>
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT (Max: 10MB)</small>
                                        @error('document_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end" style="gap: 1rem;">
                                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-lg">
                                            <i class="mdi mdi-cancel"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="mdi mdi-content-save"></i> Create Document
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Bulk Upload Form -->
                    <div id="bulk-upload-form" style="display: none;">
                        <form action="{{ route('documents.bulk-store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-information"></i> Basic Information
                                    </h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bulk_location" class="form-label fw-bold">Document Type *</label>
                                        <select class="form-select form-select-lg @error('location') is-invalid @enderror" 
                                                id="bulk_location" name="location" required>
                                            <option value="">Select Document Type...</option>
                                            @foreach($folderTree as $node)
                                                <option value="{{ $node->id }}" {{ old('location') == $node->id ? 'selected' : '' }} class="{{ isset($node->is_type) && $node->is_type ? 'fw-bold' : '' }}" data-type-id="{{ $node->document_type_id }}">
                                                    {!! $node->name !!}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bulk_version" class="form-label fw-bold">Version *</label>
                                        <input type="number" class="form-control form-control-lg @error('version') is-invalid @enderror" 
                                               id="bulk_version" name="version" value="{{ old('version', '1') }}" min="0.1" step="0.1" 
                                               placeholder="Enter version" required>
                                        <small class="form-text text-muted">Version for all uploaded documents</small>
                                        @error('version')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bulk_validity_period" class="form-label fw-bold">Validity Period</label>
                                        <input type="date" class="form-control form-control-lg @error('validity_period') is-invalid @enderror" 
                                               id="bulk_validity_period" name="validity_period" 
                                               value="{{ old('validity_period') }}">
                                        <small class="form-text text-muted">Expiry date for all uploaded documents (optional)</small>
                                        @error('validity_period')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bulk_status" class="form-label fw-bold">Status *</label>
                                        <select class="form-select form-select-lg @error('status') is-invalid @enderror" 
                                                id="bulk_status" name="status" required>
                                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Expiry Notifications and Description -->
                            <div class="row mb-4">
                                <!-- Expiry Notifications -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-bell-ring"></i> Expiry Notifications
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="bulk_notifications_enabled" 
                                                   name="notifications_enabled" value="1" 
                                                   {{ old('bulk_notifications_enabled', '1') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="bulk_notifications_enabled">
                                                Enable expiry notifications
                                            </label>
                                        </div>
                                    </div>

                                    <div id="bulk-notification-settings" style="display: {{ old('bulk_notifications_enabled', '1') ? 'block' : 'none' }};">
                                        <div class="mb-3">
                                            <label for="bulk_notification_frequency_id" class="form-label fw-bold">Notification Frequency</label>
                                            <select class="form-select form-select-lg @error('bulk_notification_frequency_id') is-invalid @enderror" 
                                                    id="bulk_notification_frequency_id" name="notification_frequency_id">
                                                <option value="">Select Frequency...</option>
                                                @foreach($notificationFrequencies as $frequency)
                                                    <option value="{{ $frequency->id }}" {{ old('bulk_notification_frequency_id') == $frequency->id ? 'selected' : '' }}>
                                                        {{ $frequency->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('bulk_notification_frequency_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bulk_notification_days_before_expiry" class="form-label fw-bold">Days Before Expiry</label>
                                            <input type="number" class="form-control form-control-lg @error('bulk_notification_days_before_expiry') is-invalid @enderror" 
                                                   id="bulk_notification_days_before_expiry" name="notification_days_before_expiry" 
                                                   value="{{ old('bulk_notification_days_before_expiry', '30') }}" min="1" max="365" 
                                                   placeholder="Enter days">
                                            @error('bulk_notification_days_before_expiry')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-text"></i> Description
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="bulk_description" class="form-label fw-bold">Document Description</label>
                                        <textarea class="form-control form-control-lg @error('bulk_description') is-invalid @enderror" 
                                                  id="bulk_description" name="description" rows="6" 
                                                  placeholder="Enter description for all uploaded documents">{{ old('bulk_description') }}</textarea>
                                        <small class="form-text text-muted">Description for all uploaded documents</small>
                                        @error('bulk_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Document Publishing -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-share-variant"></i> Document Publishing
                                    </h6>
                                </div>
                                
                                <div class="col-md-6">
                                        <div class="mb-3">
                                        <label for="bulk_publish_scope" class="form-label fw-bold">Publish To</label>
                                        <select class="form-select form-select-lg @error('bulk_publish_scope') is-invalid @enderror" 
                                                id="bulk_publish_scope" name="publish_scope">
                                            <option value="">Not Published (Draft)</option>
                                            <option value="role" {{ old('bulk_publish_scope') == 'role' ? 'selected' : '' }}>Specific Roles</option>
                                            <option value="department" {{ old('bulk_publish_scope') == 'department' ? 'selected' : '' }}>Specific Departments</option>
                                            <option value="all_departments" {{ old('bulk_publish_scope') == 'all_departments' ? 'selected' : '' }}>All Departments</option>
                                            <option value="all_roles" {{ old('bulk_publish_scope') == 'all_roles' ? 'selected' : '' }}>All Roles</option>
                                            <option value="mixed" {{ old('bulk_publish_scope') == 'mixed' ? 'selected' : '' }}>Departments & Roles (Mixed)</option>
                                        </select>
                                        @error('bulk_publish_scope')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Role Selection -->
                            <div class="row mb-4" id="bulk-role-selection" style="display: none;">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="mdi mdi-account-group"></i> Select Roles</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($roles as $role)
                                                    <div class="col-md-4 col-sm-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="publish_targets[]" value="{{ $role->id }}" 
                                                                   id="bulk_role_{{ $role->id }}"
                                                                   {{ in_array($role->id, old('publish_targets', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="bulk_role_{{ $role->id }}">
                                                                {{ $role->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                            </div>
                                        </div>
                                        
                            <!-- Department Selection -->
                            <div class="row mb-4" id="bulk-department-selection" style="display: none;">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="mdi mdi-domain"></i> Select Departments</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($departments as $dept)
                                                    <div class="col-md-4 col-sm-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="publish_targets[]" value="{{ $dept->id }}" 
                                                                   id="bulk_dept_{{ $dept->id }}"
                                                                   {{ in_array($dept->id, old('publish_targets', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="bulk_dept_{{ $dept->id }}">
                                                                {{ $dept->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mixed Selection Info -->
                            <div class="row mb-4" id="bulk-mixed-selection" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6><i class="mdi mdi-information"></i> Mixed Selection</h6>
                                        <p class="mb-0">You can select both roles and departments. Use the checkboxes above to choose specific roles and departments.</p>
                                </div>
                            </div>
                        </div>

                            <!-- File Upload -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-file-upload"></i> Document Files
                                    </h6>
                        </div>

                                <div class="col-md-6">
                        <div class="mb-3">
                                        <label for="bulk_document_files" class="form-label fw-bold">Select Files *</label>
                                        <div class="file-input-wrapper">
                                            <input type="file" class="form-control form-control-lg @error('bulk_document_files') is-invalid @enderror" 
                                                   id="bulk_document_files" name="document_files[]" multiple required>
                                            <label for="bulk_document_files" class="file-input-label">
                                                <i class="mdi mdi-file-upload"></i>
                                                <span id="bulk_document_files_text">Choose files or drag them here</span>
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Select up to 900 files (Max: 10MB each)</small>
                                        @error('bulk_document_files')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                                    <!-- File Preview -->
                                    <div id="file-preview" style="display: none;">
                                        <div class="alert alert-info">
                                            <strong>Selected Files: <span id="file-count">0</span></strong>
                                        </div>
                                        <div id="file-list" class="border rounded p-3 bg-light">
                                            <!-- File list will be populated here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end" style="gap: 1rem;">
                                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-lg">
                                            <i class="mdi mdi-cancel"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="mdi mdi-upload-multiple"></i> Upload Documents
                            </button>
                                    </div>
                                </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>
@endsection

@section('script')
<style>
    .form-control-lg, .form-select-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control-lg:focus, .form-select-lg:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-label.fw-bold {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }
    
    .card {
        border: 2px solid #e9ecef;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #e9ecef;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    
    .text-primary {
        color: #667eea !important;
    }
    
    .btn {
        transition: all 0.15s ease-in-out;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* File Input Styling */
    input[type="file"] {
        position: relative;
        display: inline-block;
        cursor: pointer;
        outline: none;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        background: #fff;
        transition: all 0.3s ease;
        width: 100%;
        min-height: 3.5rem;
    }
    
    input[type="file"]:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    input[type="file"]::-webkit-file-upload-button {
        background: #667eea;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        margin-right: 1rem;
        transition: all 0.2s ease;
    }
    
    input[type="file"]::-webkit-file-upload-button:hover {
        background: #5a67d8;
        transform: translateY(-1px);
    }
    
    input[type="file"]::file-selector-button {
        background: #667eea;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        margin-right: 1rem;
        transition: all 0.2s ease;
    }
    
    input[type="file"]::file-selector-button:hover {
        background: #5a67d8;
        transform: translateY(-1px);
    }
    
    /* Custom file input wrapper */
    .file-input-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .file-input-wrapper input[type="file"] {
        opacity: 0;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-input-wrapper .file-input-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1rem;
        background: #fff;
        border: 2px dashed #e9ecef;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 3.5rem;
        font-size: 1rem;
        color: #6c757d;
    }
    
    .file-input-wrapper .file-input-label:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }
    
    .file-input-wrapper .file-input-label i {
        margin-right: 0.5rem;
        font-size: 1.25rem;
        color: #667eea;
    }
    
    .file-input-wrapper input[type="file"]:focus + .file-input-label {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    @media (max-width: 768px) {
        .form-control-lg, .form-select-lg {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        
        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    }
</style>
<script>
    $(document).ready(function() {
        // Show single upload form by default
        showSingleUpload();
        
        // File input change handlers
        $('#document_file').on('change', function() {
            const file = this.files[0];
            if (file) {
                $('#document_file_text').text(file.name);
            } else {
                $('#document_file_text').text('Choose a file or drag it here');
            }
        });
        
        $('#bulk_document_files').on('change', function() {
            const files = this.files;
            if (files.length > 0) {
                $('#bulk_document_files_text').text(`${files.length} file(s) selected`);
                $('#file-count').text(files.length);
                $('#file-preview').show();
                
                // Update file list
                const fileList = $('#file-list');
                fileList.empty();
                for (let i = 0; i < Math.min(files.length, 10); i++) {
                    fileList.append(`<div class="mb-1"><i class="mdi mdi-file"></i> ${files[i].name}</div>`);
                }
                if (files.length > 10) {
                    fileList.append(`<div class="text-muted">... and ${files.length - 10} more files</div>`);
                }
            } else {
                $('#bulk_document_files_text').text('Choose files or drag them here');
                $('#file-preview').hide();
            }
        });
        
        // Drag and drop functionality
        $('.file-input-label').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('border-primary');
        });
        
        $('.file-input-label').on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('border-primary');
        });
        
        $('.file-input-label').on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('border-primary');
            const input = $(this).siblings('input[type="file"]');
            const files = e.originalEvent.dataTransfer.files;
            input[0].files = files;
            input.trigger('change');
        });
    });
    
    function showSingleUpload() {
        $('#bulk-upload-form').hide();
        $('#single-upload-form').show();
        $('#single-btn').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#bulk-btn').removeClass('btn-success').addClass('btn-outline-success');
    }
    
    function showBulkUpload() {
        $('#single-upload-form').hide();
        $('#bulk-upload-form').show();
        $('#bulk-btn').removeClass('btn-outline-success').addClass('btn-success');
        $('#single-btn').removeClass('btn-primary').addClass('btn-outline-primary');
    }
    
    // Notification settings toggle
    $('#notifications_enabled').change(function() {
        const isEnabled = $(this).is(':checked');
        $('#notification-settings').toggle(isEnabled);
    });
    
    $('#bulk_notifications_enabled').change(function() {
            const isEnabled = $(this).is(':checked');
        $('#bulk-notification-settings').toggle(isEnabled);
    });
    
    // Publish scope change handlers
    $('#publish_scope').change(function() {
        const value = $(this).val();
        $('#role-selection, #department-selection, #mixed-selection').hide();
        
        if (value === 'role') {
            $('#role-selection').show();
        } else if (value === 'department') {
            $('#department-selection').show();
        } else if (value === 'mixed') {
            $('#role-selection, #department-selection, #mixed-selection').show();
        }
    });
    
    $('#bulk_publish_scope').change(function() {
        const value = $(this).val();
        $('#bulk-role-selection, #bulk-department-selection, #bulk-mixed-selection').hide();
        
        if (value === 'role') {
            $('#bulk-role-selection').show();
        } else if (value === 'department') {
            $('#bulk-department-selection').show();
        } else if (value === 'mixed') {
            $('#bulk-role-selection, #bulk-department-selection, #bulk-mixed-selection').show();
        }
    });
</script>
@endsection
