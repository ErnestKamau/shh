@extends('layouts.documents.layout.app')

@section('title2')
    <title>Edit Document - Imara LIMS</title>
@endsection

@php
    // Decode publish_targets JSON for easier handling
    $publishTargets = [];
    if ($document->publish_targets) {
        if (is_string($document->publish_targets)) {
            $publishTargets = json_decode($document->publish_targets, true) ?: [];
        } elseif (is_array($document->publish_targets)) {
            $publishTargets = $document->publish_targets;
        }
    }
    
    // Extract specific arrays for different scopes
    $selectedRoles = [];
    $selectedDepartments = [];
    $mixedDepartments = [];
    $mixedRoles = [];
    
    if ($document->publish_scope === 'role' || $document->publish_scope === 'department') {
        $selectedTargets = is_array($publishTargets) ? $publishTargets : [];
    } elseif ($document->publish_scope === 'mixed') {
        $mixedDepartments = $publishTargets['departments'] ?? [];
        $mixedRoles = $publishTargets['roles'] ?? [];
    }
@endphp

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
            'link' => '/documents/' . $document->id,
            'name' => $document->name,
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Edit',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="p-1">
        <i class="mdi mdi-book-open-page-variant text-deep-orange"></i> Edit Document: {{ $document->name }}
    </h2><br>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('documents.update', $document->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Document Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $document->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label fw-bold">Location <span class="text-danger">*</span></label>
                                    <select class="form-select @error('location') is-invalid @enderror" 
                                            id="location" name="location" required>
                                        <option value="">Select Document Type or Folder...</option>
                                        @foreach($folderTree as $node)
                                            <option value="{{ $node->id }}" {{ (old('location') ?? $currentLocation ?? '') == $node->id ? 'selected' : '' }} class="{{ isset($node->is_type) && $node->is_type ? 'fw-bold' : '' }}" data-type-id="{{ $node->document_type_id }}">
                                                {!! $node->name !!}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="version" class="form-label">Version <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('version') is-invalid @enderror" 
                                           id="version" name="version" value="{{ old('version', $document->version) }}" min="0.1" step="0.1" required>
                                    <small class="form-text text-muted">Enter version number (e.g., 1, 1.1, 2.0)</small>
                                    @error('version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="validity_period" class="form-label">Validity Period</label>
                                    <input type="date" class="form-control @error('validity_period') is-invalid @enderror" 
                                           id="validity_period" name="validity_period" 
                                           value="{{ old('validity_period', $document->validity_period ? $document->validity_period->format('Y-m-d') : '') }}">
                                    <small class="form-text text-muted">Leave empty if no expiry date</small>
                                    @error('validity_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status', $document->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="active" {{ old('status', $document->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="archived" {{ old('status', $document->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        
                        <div class="row">
                            <div class="col-md-6">
                                
                            </div>
                        </div>

                        <!-- Notification Settings Section -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="mdi mdi-bell text-warning"></i> Expiry Notification Settings
                                </h5>
                                <small class="text-muted">Configure when to send notifications about document expiry</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_enabled" name="notifications_enabled" value="1" 
                                                       {{ old('notifications_enabled', $document->notifications_enabled ? '1' : '0') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_enabled">
                                                    <strong>Enable Expiry Notifications</strong>
                                                </label>
                                                <small class="form-text text-muted d-block">Send email notifications when document is expiring or has expired</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="notification-settings" style="display: {{ old('notifications_enabled', $document->notifications_enabled ? '1' : '0') == '1' ? 'block' : 'none' }};">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="notification_frequency_id" class="form-label">Notification Frequency</label>
                                            <select class="form-control @error('notification_frequency_id') is-invalid @enderror" 
                                                    id="notification_frequency_id" name="notification_frequency_id">
                                                <option value="">Select Frequency</option>
                                                @foreach($notificationFrequencies as $frequency)
                                                    <option value="{{ $frequency->id }}" {{ old('notification_frequency_id', $document->notification_frequency_id) == $frequency->id ? 'selected' : '' }}>
                                                        {{ $frequency->name }} ({{ $frequency->days_interval }} days)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">How often to send notifications before expiry</small>
                                            @error('notification_frequency_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="notification_days_before_expiry" class="form-label">Days Before Expiry</label>
                                            <input type="number" class="form-control @error('notification_days_before_expiry') is-invalid @enderror" 
                                                   id="notification_days_before_expiry" name="notification_days_before_expiry" 
                                                   value="{{ old('notification_days_before_expiry', $document->notification_days_before_expiry ?? 30) }}" min="1" max="365">
                                            <small class="form-text text-muted">Start sending notifications this many days before expiry</small>
                                            @error('notification_days_before_expiry')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <h6><i class="mdi mdi-information"></i> Notification Behavior</h6>
                                    <ul class="mb-0">
                                        <li><strong>Before Expiry:</strong> Notifications sent based on frequency (daily/weekly/monthly)</li>
                                        <li><strong>After Expiry:</strong> Daily notifications until document is updated</li>
                                        <li><strong>Recipients:</strong> You (the document creator) will receive these notifications</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Document Publishing -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="mdi mdi-share-variant text-primary"></i> Document Publishing
                                </h5>
                                <small class="text-muted">Choose who can access this document</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="publish_scope" class="form-label">Publish To</label>
                                            <select class="form-control @error('publish_scope') is-invalid @enderror" 
                                                    id="publish_scope" name="publish_scope">
                                                <option value="">Not Published (Draft)</option>
                                                <option value="role" {{ old('publish_scope', $document->publish_scope) == 'role' ? 'selected' : '' }}>Specific Roles</option>
                                                <option value="department" {{ old('publish_scope', $document->publish_scope) == 'department' ? 'selected' : '' }}>Specific Departments</option>
                                                <option value="all_departments" {{ old('publish_scope', $document->publish_scope) == 'all_departments' ? 'selected' : '' }}>All Departments</option>
                                                <option value="all_roles" {{ old('publish_scope', $document->publish_scope) == 'all_roles' ? 'selected' : '' }}>All Roles</option>
                                                <option value="mixed" {{ old('publish_scope', $document->publish_scope) == 'mixed' ? 'selected' : '' }}>Departments & Roles (Mixed)</option>
                                            </select>
                                            <small class="form-text text-muted">Leave as "Not Published" to save as draft</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Role Selection -->
                                <div class="row" id="role-selection" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Select Roles</label>
                                            <div class="row">
                                                @foreach($roles as $role)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="publish_targets[]" value="{{ $role->id }}" 
                                                                   id="role_{{ $role->id }}"
                                                                   {{ in_array($role->id, old('publish_targets', $selectedTargets ?? [])) ? 'checked' : '' }}>
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

                                <!-- Department Selection -->
                                <div class="row" id="department-selection" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Select Departments</label>
                                            <div class="row">
                                                @foreach($departments as $dept)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="publish_targets[]" value="{{ $dept->id }}" 
                                                                   id="dept_{{ $dept->id }}"
                                                                   {{ in_array($dept->id, old('publish_targets', $selectedTargets ?? [])) ? 'checked' : '' }}>
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

                                <!-- All Departments Info -->
                                <div class="row" id="all-departments-info" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information"></i>
                                            This document will be published to all departments and will be visible to all users.
                                        </div>
                                    </div>
                                </div>

                                <!-- All Roles Info -->
                                <div class="row" id="all-roles-info" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information"></i>
                                            This document will be published to all roles and will be visible to all users with any role.
                                        </div>
                                    </div>
                                </div>

                                <!-- Mixed Selection -->
                                <div class="row" id="mixed-selection" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Select Departments</label>
                                            <div class="row">
                                                @foreach($departments as $dept)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="publish_targets[departments][]" value="{{ $dept->id }}" 
                                                                   id="mixed_dept_{{ $dept->id }}"
                                                                   {{ in_array($dept->id, old('publish_targets.departments', $mixedDepartments)) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="mixed_dept_{{ $dept->id }}">
                                                                {{ $dept->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Select Roles</label>
                                            <div class="row">
                                                @foreach($roles as $role)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="publish_targets[roles][]" value="{{ $role->id }}" 
                                                                   id="mixed_role_{{ $role->id }}"
                                                                   {{ in_array($role->id, old('publish_targets.roles', $mixedRoles)) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="mixed_role_{{ $role->id }}">
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
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $document->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document_file" class="form-label">Document File</label>
                            <input type="file" class="form-control @error('document_file') is-invalid @enderror" 
                                   id="document_file" name="document_file">
                            <small class="form-text text-muted">Maximum file size: 10MB. Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT</small>
                            @if($document->file_name)
                                <div class="mt-2">
                                    <strong>Current File:</strong> {{ $document->file_name }} 
                                    ({{ $document->file_size_formatted }})
                                </div>
                            @endif
                            @error('document_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('documents.show', $document->id) }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Update Document
                            </button>
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
<script>
    // Global functions for onclick handlers
    function suggestNewVersion() {
        // Use the suggested version from the backend response
        const documentName = $('#name').val().trim();
        const documentTypeId = $('#location').find('option:selected').data('type-id');
        const currentVersion = $('#version').val().trim();
        
        $.ajax({
            url: '{{ route("documents.check-duplicate") }}',
            method: 'POST',
            data: {
                name: documentName,
                document_type_id: documentTypeId,
                version: currentVersion,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.suggested_version) {
                    $('#version').val(response.suggested_version);
                    checkDuplicateDocumentName(); // Re-check with new version
                }
            },
            error: function() {
                console.log('Error getting suggested version');
            }
        });
    }

    function clearWarning() {
        const nameField = $('#name');
        const feedbackDiv = nameField.siblings('.invalid-feedback');
        nameField.removeClass('is-invalid');
        feedbackDiv.remove();
    }

    // Function to check for duplicate document names
    function checkDuplicateDocumentName() {
        const documentName = $('#name').val().trim();
        const documentTypeId = $('#location').find('option:selected').data('type-id');
        const version = $('#version').val().trim();
        
        // Check if we have all required fields for validation
        if (documentName && documentTypeId && version) {
            $.ajax({
                url: '{{ route("documents.check-duplicate") }}',
                method: 'POST',
                data: {
                    name: documentName,
                    document_type_id: documentTypeId,
                    version: version,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    const nameField = $('#name');
                    const feedbackDiv = nameField.siblings('.invalid-feedback');
                    
                    if (response.exists) {
                        // Show warning with suggestion to create new version
                        nameField.addClass('is-invalid');
                        const warningMessage = `A document with this name and version already exists for the selected document type. Would you like to create a new version?`;
                        
                        if (feedbackDiv.length === 0) {
                            const warningDiv = $(`<div class="invalid-feedback">
                                ${warningMessage}
                                <br><br>
                                <button type="button" class="btn btn-sm btn-warning me-2" onclick="suggestNewVersion()">
                                    <i class="mdi mdi-plus"></i> Suggest New Version
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="clearWarning()">
                                    <i class="mdi mdi-close"></i> Keep Current Version
                                </button>
                            </div>`);
                            nameField.after(warningDiv);
                        } else {
                            feedbackDiv.html(warningMessage + 
                                `<br><br>
                                <button type="button" class="btn btn-sm btn-warning me-2" onclick="suggestNewVersion()">
                                    <i class="mdi mdi-plus"></i> Suggest New Version
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="clearWarning()">
                                    <i class="mdi mdi-close"></i> Keep Current Version
                                </button>`
                            );
                        }
                    } else {
                        // Remove error
                        nameField.removeClass('is-invalid');
                        feedbackDiv.remove();
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error checking duplicate document name');
                }
            });
        }
    }

    $(document).ready(function() {
        // Check for duplicate document name when document name changes
        $('#name').on('input', function() {
            checkDuplicateDocumentName();
        });

        // Check for duplicate document name when document type changes
        $('#location').on('change', function() {
            checkDuplicateDocumentName();
        });

        // Check for duplicate document name when version changes
        $('#version').on('input', function() {
            checkDuplicateDocumentName();
        });

        // Check for duplicate document name when file is selected
        $('#document_file').on('change', function() {
            checkDuplicateDocumentName();
        });

        // Handle publishing scope changes
        $('#publish_scope').on('change', function() {
            const scope = $(this).val();
            
            // Hide all selection sections
            $('#role-selection, #department-selection, #all-departments-info, #all-roles-info, #mixed-selection').hide();
            
            // Show relevant section based on selection
            switch(scope) {
                case 'role':
                    $('#role-selection').show();
                    break;
                case 'department':
                    $('#department-selection').show();
                    break;
                case 'all_departments':
                    $('#all-departments-info').show();
                    break;
                case 'all_roles':
                    $('#all-roles-info').show();
                    break;
                case 'mixed':
                    $('#mixed-selection').show();
                    break;
            }
        });

        // Trigger change event on page load to show correct sections
        $('#publish_scope').trigger('change');

        // File size validation
        $('#document_file').on('change', function() {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const files = this.files;
            
            for (let i = 0; i < files.length; i++) {
                if (files[i].size > maxSize) {
                    alert('File "' + files[i].name + '" is too large. Maximum size is 10MB.');
                    this.value = '';
                    return;
                }
            }
            
            // After file validation passes, check for duplicate document name
            checkDuplicateDocumentName();
        });

        // Handle notification settings toggle
        $('#notifications_enabled').on('change', function() {
            if (this.checked) {
                $('#notification-settings').show();
            } else {
                $('#notification-settings').hide();
            }
        });
    });
</script>
@endsection
