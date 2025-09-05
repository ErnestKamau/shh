@extends('layouts.lab.layout.app')

@section('title2')
<title>Create Certificate Template | Lab Management</title>
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
            'link' => '#',
            'name' => 'Create Template',
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
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-plus-circle"></i> Create New Certificate Template
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('certificate-templates.store') }}" method="POST" id="create-template-form">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="submission_form_id" class="form-label">Submission Form <span class="text-danger">*</span></label>
                                    <select name="submission_form_id" id="submission_form_id" class="form-control @error('submission_form_id') is-invalid @enderror" required>
                                        <option value="">Select a submission form...</option>
                                        @foreach($submissionForms as $form)
                                            <option value="{{ $form->id }}" {{ old('submission_form_id', $selectedSubmissionFormId) == $form->id ? 'selected' : '' }}>
                                                {{ $form->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('submission_form_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Choose which submission form this certificate template will be used for.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Enter a description for this template...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                                <option value="A4">A4 (210 × 297 mm)</option>
                                                <option value="A3">A3 (297 × 420 mm)</option>
                                                <option value="Letter">Letter (8.5 × 11 in)</option>
                                                <option value="Legal">Legal (8.5 × 14 in)</option>
                                                <option value="A5">A5 (148 × 210 mm)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="page_orientation" class="form-label">Orientation</label>
                                            <select class="form-control" id="page_orientation" name="page_settings[orientation]">
                                                <option value="portrait">Portrait</option>
                                                <option value="landscape">Landscape</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="page_margins" class="form-label">Margins (mm)</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="number" class="form-control" name="page_settings[margins][top]" 
                                                           value="20" placeholder="Top" min="5" max="50">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" class="form-control" name="page_settings[margins][bottom]" 
                                                           value="20" placeholder="Bottom" min="5" max="50">
                                                </div>
                                                <div class="col-6 mt-2">
                                                    <input type="number" class="form-control" name="page_settings[margins][left]" 
                                                           value="20" placeholder="Left" min="5" max="50">
                                                </div>
                                                <div class="col-6 mt-2">
                                                    <input type="number" class="form-control" name="page_settings[margins][right]" 
                                                           value="20" placeholder="Right" min="5" max="50">
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">Recommended: 15-25mm for professional documents</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Background</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="enable_watermark" 
                                                       name="page_settings[watermark][enabled]" value="1">
                                                <label class="form-check-label" for="enable_watermark">
                                                    Enable watermark
                                                </label>
                                            </div>
                                            <div class="mt-2" id="watermark-settings" style="display: none;">
                                                <input type="text" class="form-control" name="page_settings[watermark][text]" 
                                                       placeholder="Watermark text" value="CONFIDENTIAL">
                                                <small class="form-text text-muted">Text to display as background watermark</small>
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
                                                       name="header_settings[enabled]" value="1" checked>
                                                <label class="form-check-label" for="include_header">
                                                    Include Header
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="header_height" class="form-label">Header Height (mm)</label>
                                            <input type="number" class="form-control" name="header_settings[height]" 
                                                   value="30" placeholder="30">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="header_logo" class="form-label">Header Logo</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="header_logo" name="header_logo" accept="image/*">
                                                <label class="custom-file-label" for="header_logo">Choose logo file...</label>
                                            </div>
                                            <small class="form-text text-muted">Upload company logo for header</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="header_content" class="form-label">Header Content</label>
                                            <textarea class="form-control" name="header_settings[content]" 
                                                      rows="3" placeholder="Enter header content..."></textarea>
                                            <small class="form-text text-muted">Company name, address, contact information</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="header_alignment" class="form-label">Header Alignment</label>
                                            <select class="form-control" name="header_settings[alignment]">
                                                <option value="left">Left</option>
                                                <option value="center" selected>Center</option>
                                                <option value="right">Right</option>
                                            </select>
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
                                                       name="footer_settings[enabled]" value="1" checked>
                                                <label class="form-check-label" for="include_footer">
                                                    Include Footer
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="footer_height" class="form-label">Footer Height (mm)</label>
                                            <input type="number" class="form-control" name="footer_settings[height]" 
                                                   value="20" placeholder="20">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="footer_content" class="form-label">Footer Content</label>
                                            <textarea class="form-control" name="footer_settings[content]" 
                                                      rows="3" placeholder="Enter footer content..."></textarea>
                                            <small class="form-text text-muted">Copyright, contact info, disclaimers</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Footer Options</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_page_numbers" 
                                                       name="footer_settings[page_numbers]" value="1" checked>
                                                <label class="form-check-label" for="include_page_numbers">
                                                    Include Page Numbers
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_generation_date" 
                                                       name="footer_settings[generation_date]" value="1">
                                                <label class="form-check-label" for="include_generation_date">
                                                    Include Generation Date
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="include_certificate_id" 
                                                       name="footer_settings[certificate_id]" value="1" checked>
                                                <label class="form-check-label" for="include_certificate_id">
                                                    Include Certificate ID
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="footer_alignment" class="form-label">Footer Alignment</label>
                                            <select class="form-control" name="footer_settings[alignment]">
                                                <option value="left">Left</option>
                                                <option value="center" selected>Center</option>
                                                <option value="right">Right</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save"></i> Create Template
                                    </button>
                                    <a href="{{ route('certificate-templates.index') }}" class="btn btn-secondary">
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
    
    /* Select2 validation styling */
    .select2-container .select2-selection--single.is-invalid {
        border-color: #dc3545;
    }
    
    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        border: 1px solid #ced4da;
    }
</style>
@endpush

@push('script2')
<script>
$(document).ready(function() {
    // Initialize Select2 for submission form dropdown
    $('#submission_form_id').select2({
        placeholder: 'Select a submission form...',
        allowClear: true,
        width: '100%'
    });

    // Handle watermark toggle
    $('#enable_watermark').on('change', function() {
        if ($(this).is(':checked')) {
            $('#watermark-settings').slideDown();
        } else {
            $('#watermark-settings').slideUp();
        }
    });
    
    // Handle file upload labels
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Form validation
    $('#create-template-form').on('submit', function(e) {
        let isValid = true;
        
        // Clear previous validation
        $('.form-control, .select2-selection').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate submission form selection
        if (!$('#submission_form_id').val()) {
            $('#submission_form_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            $('#submission_form_id').closest('.form-group').append('<div class="invalid-feedback d-block">Please select a submission form.</div>');
            isValid = false;
        }
        
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
@endpush
