@extends('layouts.documents.layout.app')

@section('title2')
    <title>Publish Document - Imara LIMS</title>
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
            'link' => route('documents.show', $document->id),
            'name' => $document->name,
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Publish Document',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="mdi mdi-share-variant text-success"></i> Publish Document
        </h2>
        <a href="{{ route('documents.show', $document->id) }}" class="btn btn-secondary">
            <i class="mdi mdi-arrow-left"></i> Back to Document
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="mdi mdi-share-variant text-primary"></i> Publishing Options
                    </h5>
                    <small class="text-muted">Choose who can access this document</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('documents.store-publish', $document->id) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="publish_scope" class="form-label">Publish To <span class="text-danger">*</span></label>
                                    <select class="form-control @error('publish_scope') is-invalid @enderror" 
                                            id="publish_scope" name="publish_scope" required>
                                        <option value="">Select Publishing Scope</option>
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
                        <div class="row" id="role-selection" style="display: none;">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Select Roles <span class="text-danger">*</span></label>
                                    <div class="row">
                                        @foreach($roles as $role)
                                            <div class="col-md-4 mb-2">
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
                                    @error('publish_targets')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Department Selection -->
                        <div class="row" id="department-selection" style="display: none;">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Select Departments <span class="text-danger">*</span></label>
                                    <div class="row">
                                        @foreach($departments as $dept)
                                            <div class="col-md-4 mb-2">
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
                                    @error('publish_targets')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
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
                                                           {{ in_array($dept->id, old('publish_targets.departments', [])) ? 'checked' : '' }}>
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
                                                           {{ in_array($role->id, old('publish_targets.roles', [])) ? 'checked' : '' }}>
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

                        <div class="text-end">
                            <a href="{{ route('documents.show', $document->id) }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="mdi mdi-share-variant"></i> Publish Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="mdi mdi-file-document text-info"></i> Document Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong><br>
                        {{ $document->name }}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Type:</strong><br>
                        <span class="badge bg-info">{{ $document->documentType->name }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Version:</strong><br>
                        <span class="badge bg-secondary">v{{ $document->version }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        @switch($document->status)
                            @case('draft')
                                <span class="badge bg-warning">Draft</span>
                                @break
                            @case('active')
                                <span class="badge bg-success">Active</span>
                                @break
                            @case('archived')
                                <span class="badge bg-secondary">Archived</span>
                                @break
                            @default
                                <span class="badge bg-light text-dark">{{ ucfirst($document->status) }}</span>
                        @endswitch
                    </div>
                    
                    <div class="mb-3">
                        <strong>Department:</strong><br>
                        {{ $document->department->name }}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Created By:</strong><br>
                        {{ $document->creator->name }}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Created Date:</strong><br>
                        {{ $document->created_at->format('M d, Y H:i') }}
                    </div>
                    
                    @if($document->description)
                        <div class="mb-3">
                            <strong>Description:</strong><br>
                            {{ $document->description }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script>
    $(document).ready(function() {
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
    });
</script>
@endsection
