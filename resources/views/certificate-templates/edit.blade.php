@extends('layouts.lab.layout.app')

@section('title2')
<title>Edit Certificate Template | Lab Management</title>
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
            'link' => route('certificate-templates.index'),
            'name' => 'Certificate Templates',
            'icon' => null
        ),
        array(
            'link' => route('certificate-templates.show', $template),
            'name' => $template->name,
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Edit Settings',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="bg-light p-4">
        <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-1">
                                <i class="mdi mdi-edit"></i> Edit Certificate Template
                            </h5>
                            <p class="text-muted mb-0 small">{{ $template->name }}</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('certificate-templates.show', $template) }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to Template
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('certificate-templates.update', $template) }}" method="POST" id="edit-template-form">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $template->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Enter a description for this template...">{{ old('description', $template->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Template Status</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_published" 
                                                       name="is_published" value="1" {{ old('is_published', $template->is_published) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_published">
                                                    <strong>Published</strong>
                                                    <small class="d-block text-muted">Make this template available for use</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_active" 
                                                       name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    <strong>Active</strong>
                                                    <small class="d-block text-muted">Enable this template for new reports</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Page Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="page_size" class="form-label">Page Size</label>
                                            <select class="form-control" id="page_size" name="page_settings[size]">
                                                <option value="A4" {{ ($template->page_settings['size'] ?? 'A4') == 'A4' ? 'selected' : '' }}>A4</option>
                                                <option value="A3" {{ ($template->page_settings['size'] ?? '') == 'A3' ? 'selected' : '' }}>A3</option>
                                                <option value="Letter" {{ ($template->page_settings['size'] ?? '') == 'Letter' ? 'selected' : '' }}>Letter</option>
                                                <option value="Legal" {{ ($template->page_settings['size'] ?? '') == 'Legal' ? 'selected' : '' }}>Legal</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="page_orientation" class="form-label">Orientation</label>
                                            <select class="form-control" id="page_orientation" name="page_settings[orientation]">
                                                <option value="portrait" {{ ($template->page_settings['orientation'] ?? 'portrait') == 'portrait' ? 'selected' : '' }}>Portrait</option>
                                                <option value="landscape" {{ ($template->page_settings['orientation'] ?? '') == 'landscape' ? 'selected' : '' }}>Landscape</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="page_margins" class="form-label">Margins (mm)</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="number" class="form-control" name="page_settings[margins][top]" 
                                                           value="{{ $template->page_settings['margins']['top'] ?? 20 }}" placeholder="Top">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" class="form-control" name="page_settings[margins][bottom]" 
                                                           value="{{ $template->page_settings['margins']['bottom'] ?? 20 }}" placeholder="Bottom">
                                                </div>
                                                <div class="col-6 mt-2">
                                                    <input type="number" class="form-control" name="page_settings[margins][left]" 
                                                           value="{{ $template->page_settings['margins']['left'] ?? 20 }}" placeholder="Left">
                                                </div>
                                                <div class="col-6 mt-2">
                                                    <input type="number" class="form-control" name="page_settings[margins][right]" 
                                                           value="{{ $template->page_settings['margins']['right'] ?? 20 }}" placeholder="Right">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Header Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_header" 
                                                       name="header_settings[enabled]" value="1" 
                                                       {{ ($template->header_settings['enabled'] ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="include_header">
                                                    Include Header
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="header_height" class="form-label">Header Height (mm)</label>
                                            <input type="number" class="form-control" name="header_settings[height]" 
                                                   value="{{ $template->header_settings['height'] ?? 30 }}" placeholder="30">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="header_content" class="form-label">Header Content</label>
                                            <textarea class="form-control" name="header_settings[content]" 
                                                      rows="3" placeholder="Enter header content...">{{ $template->header_settings['content'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Footer Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_footer" 
                                                       name="footer_settings[enabled]" value="1" 
                                                       {{ ($template->footer_settings['enabled'] ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="include_footer">
                                                    Include Footer
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="footer_height" class="form-label">Footer Height (mm)</label>
                                            <input type="number" class="form-control" name="footer_settings[height]" 
                                                   value="{{ $template->footer_settings['height'] ?? 20 }}" placeholder="20">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="footer_content" class="form-label">Footer Content</label>
                                            <textarea class="form-control" name="footer_settings[content]" 
                                                      rows="3" placeholder="Enter footer content...">{{ $template->footer_settings['content'] ?? '' }}</textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_page_numbers" 
                                                       name="footer_settings[page_numbers]" value="1" 
                                                       {{ ($template->footer_settings['page_numbers'] ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="include_page_numbers">
                                                    Include Page Numbers
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save"></i> Update Template
                                    </button>
                                    <a href="{{ route('certificate-templates.show', $template) }}" class="btn btn-secondary">
                                        <i class="mdi mdi-cancel"></i> Cancel
                                    </a>
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

@push('styles')
<style>
    .form-actions {
        padding: 20px 0;
        border-top: 1px solid #dee2e6;
        margin-top: 20px;
    }
    
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .form-check-label strong {
        color: #495057;
    }
</style>
@endpush

@section('script2')
<script>
$(document).ready(function() {
    // Form validation
    $('#edit-template-form').on('submit', function(e) {
        let isValid = true;
        
        // Clear previous validation
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate required fields
        if (!$('#name').val().trim()) {
            $('#name').addClass('is-invalid');
            $('#name').after('<div class="invalid-feedback">Template name is required.</div>');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('Please fix the validation errors before submitting.');
        }
    });
    
    // Auto-save draft functionality (optional)
    let autoSaveTimeout;
    $('#name, #description').on('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            // Auto-save logic could be implemented here
            console.log('Auto-saving draft...');
        }, 2000);
    });
});
</script>
@endsection
