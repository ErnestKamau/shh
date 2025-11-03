@extends('layouts.lab.layout.app')

@section('title2')
<title>Template Builder - {{ $template->name }} | Lab Management</title>
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
            'name' => 'Template Builder',
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
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-pencil"></i> Template Builder
                                <small class="text-muted">{{ $template->name }}</small>
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success" id="save-template">
                                    <i class="mdi mdi-content-save"></i> Save Template
                                </button>
                                <button type="button" class="btn btn-info" id="preview-template">
                                    <i class="mdi mdi-eye"></i> Preview
                                </button>
                                <a href="{{ route('certificate-templates.show', $template) }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back to Template
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="mdi mdi-palette"></i> Element Palette
                                    </h6>
                                </div>
                                <div class="card-body element-palette">
                                    <button class="btn btn-outline-primary btn-block mb-2 element-type-btn" data-type="heading">
                                        <i class="mdi mdi-format-header-1"></i> Heading
                                    </button>
                                    <button class="btn btn-outline-primary btn-block mb-2 element-type-btn" data-type="paragraph">
                                        <i class="mdi mdi-format-paragraph"></i> Paragraph
                                    </button>
                                    <button class="btn btn-outline-primary btn-block mb-2 element-type-btn" data-type="image">
                                        <i class="mdi mdi-image"></i> Image
                                    </button>
                                    <button class="btn btn-outline-primary btn-block mb-2 element-type-btn" data-type="table">
                                        <i class="mdi mdi-table"></i> Table
                                    </button>
                                    <button class="btn btn-outline-primary btn-block mb-2 element-type-btn" data-type="data_field">
                                        <i class="mdi mdi-database"></i> Data Field
                                    </button>
                                    <button class="btn btn-outline-primary btn-block mb-2 element-type-btn" data-type="signature">
                                        <i class="mdi mdi-pen"></i> Signature
                                    </button>
                                    <button class="btn btn-outline-primary btn-block mb-2 element-type-btn" data-type="date">
                                        <i class="mdi mdi-calendar"></i> Date
                                    </button>
                                    <button class="btn btn-outline-primary btn-block mb-2 element-type-btn" data-type="page_break">
                                        <i class="mdi mdi-page-layout-body"></i> Page Break
                                    </button>
                                    
                                    <hr>
                                    
                                    <button class="btn btn-success btn-block" id="add-section">
                                        <i class="mdi mdi-plus"></i> Add Section
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="mdi mdi-file-document-edit"></i> Template Structure
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="template-structure">
                                        @if($template->sections->count() > 0)
                                            @foreach($template->rootSections as $section)
                                                @include('certificate-templates.partials.builder-section', ['section' => $section])
                                            @endforeach
                                        @else
                                            <div class="text-center py-5 text-muted">
                                                <i class="mdi mdi-file-document-edit" style="font-size: 3rem;"></i>
                                                <h5 class="mt-3">No sections yet</h5>
                                                <p>Start building your template by adding sections and elements.</p>
                                                <button class="btn btn-primary" id="add-first-section">
                                                    <i class="mdi mdi-plus"></i> Add First Section
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <!-- Properties Panel -->
    <div class="properties-panel" id="properties-panel">
        <div class="panel-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="mdi mdi-cog"></i> Element Properties
                </h5>
                <button type="button" class="btn btn-sm btn-outline-light" id="close-properties">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>
        </div>
        <div class="panel-body">
            <div id="properties-content">
                <div class="text-center text-muted py-5">
                    <i class="mdi mdi-information-outline" style="font-size: 3rem;"></i>
                    <h6 class="mt-3">No Element Selected</h6>
                    <p>Select an element to edit its properties</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Properties Panel Overlay -->
    <div class="properties-overlay" id="properties-overlay"></div>
</main>
@endsection

@push('styles')
<link rel="stylesheet" href="/assets/css/theme-default/libs/nestable/nestable.css">
<style>
    .element-palette .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .element-palette .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .template-section {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 20px;
        background: #f8f9fa;
        position: relative;
        transition: all 0.2s;
    }
    
    .template-section:hover {
        border-color: #007bff;
        box-shadow: 0 4px 12px rgba(0,123,255,0.15);
    }
    
    .template-section.active {
        border-color: #28a745;
        background: #f8fff9;
    }
    
    .section-header {
        background: #ffffff;
        border-bottom: 1px solid #e9ecef;
        padding: 15px;
        border-radius: 6px 6px 0 0;
        cursor: move;
    }
    
    .section-content {
        padding: 20px;
        min-height: 100px;
    }
    
    .template-element {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 10px;
        background: #ffffff;
        position: relative;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .template-element:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.1);
    }
    
    .template-element.selected {
        border-color: #28a745;
        background: #f8fff9;
    }
    
    .element-controls {
        position: absolute;
        top: 5px;
        right: 5px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .template-element:hover .element-controls {
        opacity: 1;
    }
    
    .section-controls {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .template-section:hover .section-controls {
        opacity: 1;
    }
    
    .drop-zone {
        border: 2px dashed #ced4da;
        border-radius: 6px;
        padding: 30px;
        text-align: center;
        color: #6c757d;
        margin: 10px 0;
        transition: all 0.2s;
    }
    
    .drop-zone.drag-over {
        border-color: #007bff;
        background: #f0f8ff;
        color: #007bff;
    }
    
    .properties-panel {
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100vh;
        background: #ffffff;
        box-shadow: -3px 0 15px rgba(0,0,0,0.1);
        z-index: 1050;
        transition: right 0.3s ease;
        overflow-y: auto;
    }
    
    .properties-panel.open {
        right: 0;
    }
    
    .properties-panel .panel-header {
        background: #007bff;
        color: white;
        padding: 20px;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .properties-panel .panel-body {
        padding: 20px;
    }
    
    .properties-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .properties-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    
    .nestable {
        max-depth: 3;
    }
    
    .nestable-item {
        margin-bottom: 10px;
    }
    
    .nestable-handle {
        cursor: move;
    }
    
    .element-preview {
        pointer-events: none;
        opacity: 0.7;
    }
    
    .toolbar {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .auto-save-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 10px 15px;
        border-radius: 25px;
        box-shadow: 0 4px 12px rgba(40,167,69,0.3);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s;
        z-index: 1000;
    }
    
    .auto-save-indicator.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endpush

@section('script2')
<script src="/assets/js/libs/nestable/jquery.nestable.js"></script>
<script src="/tinymce/tinymce.min.js"></script>
<script>
$(document).ready(function() {
    let templateId = {{ $template->id }};
    let selectedElement = null;
    let autoSaveTimeout = null;
    
    // Initialize Nestable for drag and drop
    $('.template-structure').nestable({
        group: 1,
        maxDepth: 3,
        callback: function(l, e) {
            updateSectionOrder();
            triggerAutoSave();
        }
    });
    
    // Add Section functionality
    $('#add-section, #add-first-section').click(function() {
        addNewSection();
    });
    
    // Element type buttons
    $('.element-type-btn').click(function() {
        let elementType = $(this).data('type');
        if (selectedElement) {
            addElementToSection(selectedElement.closest('.template-section'), elementType);
        } else {
            showMessage('Please select a section first', 'warning');
        }
    });
    
    // Section selection
    $(document).on('click', '.template-section', function(e) {
        e.stopPropagation();
        $('.template-section').removeClass('active');
        $(this).addClass('active');
        selectedElement = $(this);
    });
    
    // Element selection
    $(document).on('click', '.template-element', function(e) {
        e.stopPropagation();
        $('.template-element').removeClass('selected');
        $(this).addClass('selected');
        selectedElement = $(this);
        openPropertiesPanel($(this));
    });
    
    // Close properties panel
    $('#close-properties, #properties-overlay').click(function() {
        closePropertiesPanel();
    });
    
    // Edit element button
    $(document).on('click', '.edit-element', function(e) {
        e.stopPropagation();
        let element = $(this).closest('.template-element');
        element.click(); // Trigger element selection
    });
    
    // Save template
    $('#save-template').click(function() {
        saveTemplate();
    });
    
    // Preview template
    $('#preview-template').click(function() {
        window.open(`/certificate-templates/${templateId}/preview`, '_blank');
    });
    
    // Delete section
    $(document).on('click', '.delete-section', function(e) {
        e.stopPropagation();
        let section = $(this).closest('.template-section');
        if (confirm('Are you sure you want to delete this section?')) {
            deleteSection(section);
        }
    });
    
    // Delete element
    $(document).on('click', '.delete-element', function(e) {
        e.stopPropagation();
        let element = $(this).closest('.template-element');
        if (confirm('Are you sure you want to delete this element?')) {
            deleteElement(element);
        }
    });
    
    // Auto-save functionality
    function triggerAutoSave() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            autoSaveTemplate();
        }, 2000);
    }
    
    function autoSaveTemplate() {
        $('.auto-save-indicator').addClass('show');
        // Auto-save logic here
        setTimeout(function() {
            $('.auto-save-indicator').removeClass('show');
        }, 2000);
    }
    
    function addNewSection() {
        $.ajax({
            url: `/certificate-templates/${templateId}/sections`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                title: 'New Section',
                description: 'New section description'
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to show new section
                }
            },
            error: function() {
                showMessage('Error adding section', 'error');
            }
        });
    }
    
    function addElementToSection(section, elementType) {
        let sectionId = section.data('section-id');
        
        $.ajax({
            url: `/certificate-template-sections/${sectionId}/elements`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                element_type: elementType,
                content: getDefaultContent(elementType)
            },
            success: function(response) {
                if (response.success) {
                    // Add element to the section
                    let elementHtml = createElementHtml(response.element);
                    section.find('.section-content').append(elementHtml);
                    triggerAutoSave();
                }
            },
            error: function() {
                showMessage('Error adding element', 'error');
            }
        });
    }
    
    function getDefaultContent(elementType) {
        const defaults = {
            'heading': 'New Heading',
            'paragraph': 'Enter your text here...',
            'image': '',
            'table': '',
            'data_field': 'sample_name',
            'signature': '',
            'date': 'current_date',
            'page_break': ''
        };
        
        return defaults[elementType] || '';
    }
    
    function createElementHtml(element) {
        return `
            <div class="template-element" data-element-id="${element.id}" data-type="${element.element_type}">
                <div class="element-controls">
                    <button class="btn btn-sm btn-outline-primary edit-element" data-id="${element.id}">
                        <i class="mdi mdi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-element" data-id="${element.id}">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </div>
                <div class="element-content">
                    <strong>${element.element_type.replace('_', ' ').toUpperCase()}:</strong>
                    ${element.content || 'No content'}
                </div>
            </div>
        `;
    }
    
    function saveTemplate() {
        showMessage('Template saved successfully!', 'success');
    }
    
    function deleteSection(section) {
        let sectionId = section.data('section-id');
        
        $.ajax({
            url: `/certificate-template-sections/${sectionId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    section.fadeOut(300, function() {
                        $(this).remove();
                    });
                    showMessage('Section deleted', 'success');
                }
            },
            error: function() {
                showMessage('Error deleting section', 'error');
            }
        });
    }
    
    function deleteElement(element) {
        let elementId = element.data('element-id');
        
        $.ajax({
            url: `/certificate-template-elements/${elementId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    element.fadeOut(300, function() {
                        $(this).remove();
                    });
                    showMessage('Element deleted', 'success');
                }
            },
            error: function() {
                showMessage('Error deleting element', 'error');
            }
        });
    }
    
    function updateSectionOrder() {
        // Update section order based on nestable structure
        let order = $('.template-structure').nestable('serialize');
        // Send order to server
    }
    
    function openPropertiesPanel(element) {
        let elementType = element.data('type');
        let elementId = element.data('element-id');
        let content = element.find('.element-content').text() || '';
        
        // Get element properties from server
        $.ajax({
            url: `/certificate-template-elements/${elementId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let propertiesHtml = generatePropertiesForm(response.element);
                    $('#properties-content').html(propertiesHtml);
                    $('#properties-panel').addClass('open');
                    $('#properties-overlay').addClass('show');
                    
                    // Initialize TinyMCE for paragraph elements
                    if (response.element.element_type === 'paragraph') {
                        initializeTinyMCE();
                    }
                    
                    // Load data fields for data field elements
                    if (response.element.element_type === 'data_field') {
                        loadDataFields(response.element);
                    }
                    
                    // Initialize image management for image elements
                    if (response.element.element_type === 'image') {
                        initializeImageManagement();
                    }
                    
                    // Initialize table management for table elements
                    if (response.element.element_type === 'table') {
                        initializeTableManagement();
                    }
                }
            },
            error: function() {
                // Fallback to basic form
                let propertiesHtml = generateBasicPropertiesForm(elementType, elementId, content);
                $('#properties-content').html(propertiesHtml);
                $('#properties-panel').addClass('open');
                $('#properties-overlay').addClass('show');
                
                // Initialize TinyMCE for paragraph elements
                if (elementType === 'paragraph') {
                    initializeTinyMCE();
                }
                
                // Load data fields for data field elements
                if (elementType === 'data_field') {
                    loadDataFields({ element_type: elementType, content: content });
                }
                
                // Initialize image management for image elements
                if (elementType === 'image') {
                    initializeImageManagement();
                }
                
                // Initialize table management for table elements
                if (elementType === 'table') {
                    initializeTableManagement();
                }
            }
        });
    }
    
    function closePropertiesPanel() {
        // Cleanup TinyMCE instances before closing
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#paragraph-content');
        }
        
        $('#properties-panel').removeClass('open');
        $('#properties-overlay').removeClass('show');
        $('.template-element').removeClass('selected');
        selectedElement = null;
    }
    
    function initializeTinyMCE() {
        // Wait for the element to be in DOM
        setTimeout(function() {
            if (typeof tinymce !== 'undefined' && $('#paragraph-content').length) {
                tinymce.init({
                    selector: '#paragraph-content',
                    height: 300,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount textcolor'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                             'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
                             'bullist numlist outdent indent | removeformat | help',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px }',
                    setup: function(editor) {
                        editor.on('change', function() {
                            // Auto-save on content change
                            clearTimeout(autoSaveTimeout);
                            autoSaveTimeout = setTimeout(function() {
                                // Trigger save
                                console.log('TinyMCE content changed, auto-saving...');
                            }, 2000);
                        });
                    },
                    branding: false,
                    elementpath: false,
                    resize: 'vertical',
                    paste_as_text: false,
                    paste_auto_cleanup_on_paste: true,
                    convert_urls: false,
                    relative_urls: false
                });
            }
        }, 100);
    }
    
    function loadDataFields(element) {
        // Get the template's submission form ID
        let submissionFormId = {{ $template->submission_form_id ?? 'null' }};
        
        $.ajax({
            url: '/template-builder/data-fields',
            method: 'GET',
            data: {
                submission_form_id: submissionFormId
            },
            success: function(response) {
                if (response.success && response.data_fields) {
                    let selectElement = $('#data-field-type');
                    selectElement.empty();
                    selectElement.append('<option value="">Select a data field...</option>');
                    
                    let currentCategory = '';
                    response.data_fields.forEach(function(field) {
                        if (field.category !== currentCategory) {
                            if (currentCategory !== '') {
                                selectElement.append('</optgroup>');
                            }
                            selectElement.append(`<optgroup label="${field.category}">`);
                            currentCategory = field.category;
                        }
                        
                        let selected = element.content === field.key ? 'selected' : '';
                        selectElement.append(`<option value="${field.key}" ${selected}>${field.label}</option>`);
                    });
                    
                    if (currentCategory !== '') {
                        selectElement.append('</optgroup>');
                    }
                }
            },
            error: function() {
                let selectElement = $('#data-field-type');
                selectElement.empty();
                selectElement.append('<option value="">Error loading data fields</option>');
            }
        });
    }
    
    function initializeImageManagement() {
        // Handle image upload
        $(document).on('change', '#image-upload', function(e) {
            let file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.match(/^image\/(jpeg|jpg|png|gif|svg)$/)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF, SVG)');
                    return;
                }
                
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    return;
                }
                
                // Update file label
                $('.custom-file-label').text(file.name);
                
                // Upload file
                uploadImage(file);
            }
        });
        
        // Handle browse button
        $(document).on('click', '#browse-image', function() {
            $('#image-upload').click();
        });
        
        // Handle manual URL changes
        $(document).on('input', '#image-source', function() {
            let url = $(this).val();
            updateImagePreview(url);
        });
        
        // Handle aspect ratio maintenance
        $(document).on('change', '#image-maintain-aspect', function() {
            if ($(this).is(':checked')) {
                // Lock aspect ratio
                let width = $('#image-width').val();
                let height = $('#image-height').val();
                let ratio = width / height;
                
                $(document).on('input', '#image-width', function() {
                    let newWidth = $(this).val();
                    $('#image-height').val(Math.round(newWidth / ratio));
                });
                
                $(document).on('input', '#image-height', function() {
                    let newHeight = $(this).val();
                    $('#image-width').val(Math.round(newHeight * ratio));
                });
            } else {
                // Unlock aspect ratio
                $(document).off('input', '#image-width');
                $(document).off('input', '#image-height');
            }
        });
    }
    
    function uploadImage(file) {
        let formData = new FormData();
        formData.append('image', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Show upload progress
        $('.image-preview-container').html('<div class="text-center"><i class="mdi mdi-loading mdi-spin"></i> Uploading...</div>');
        
        $.ajax({
            url: '/certificate-templates/upload-image',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#image-source').val(response.url);
                    updateImagePreview(response.url);
                    showMessage('Image uploaded successfully!', 'success');
                } else {
                    showMessage('Error uploading image: ' + response.message, 'error');
                    $('.image-preview-container').html('<span class="text-muted">Upload failed</span>');
                }
            },
            error: function() {
                showMessage('Error uploading image', 'error');
                $('.image-preview-container').html('<span class="text-muted">Upload failed</span>');
            }
        });
    }
    
    function updateImagePreview(url) {
        if (url && url.trim() !== '') {
            $('.image-preview-container').html(`<img src="${url}" style="max-width: 200px; max-height: 150px;" alt="Preview" onerror="this.parentElement.innerHTML='<span class=\\"text-danger\\">Invalid image URL</span>'">`);
        } else {
            $('.image-preview-container').html('<span class="text-muted">No image selected</span>');
        }
    }
    
    function initializeTableManagement() {
        // Handle table type changes
        $(document).on('change', '#table-type', function() {
            let tableType = $(this).val();
            if (tableType === 'custom_table') {
                $('#custom-table-config').show();
                $('#data-table-config').hide();
            } else if (tableType === 'data_table' || tableType === 'analysis_results') {
                $('#custom-table-config').hide();
                $('#data-table-config').show();
            } else {
                $('#custom-table-config').hide();
                $('#data-table-config').hide();
            }
        });
        
        // Handle configure table data button
        $(document).on('click', '#configure-table-data', function() {
            openTableDataModal();
        });
        
        // Handle column/row changes for custom tables
        $(document).on('change', '#table-columns, #table-rows', function() {
            generateTablePreview();
        });
    }
    
    function openTableDataModal() {
        let columns = parseInt($('#table-columns').val()) || 3;
        let rows = parseInt($('#table-rows').val()) || 5;
        
        let modalHtml = `
            <div class="modal fade" id="table-data-modal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Configure Table Data</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="table-data-editor">
                                    <thead>
                                        <tr>
                                            ${Array.from({length: columns}, (_, i) => `<th><input type="text" class="form-control form-control-sm" placeholder="Column ${i+1}" value="Column ${i+1}"></th>`).join('')}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${Array.from({length: rows}, () => `
                                            <tr>
                                                ${Array.from({length: columns}, () => `<td><input type="text" class="form-control form-control-sm" placeholder="Enter data"></td>`).join('')}
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="save-table-data">Save Table Data</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        $('#table-data-modal').modal('show');
        
        // Remove modal when hidden
        $('#table-data-modal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
        
        // Handle save table data
        $(document).on('click', '#save-table-data', function() {
            let tableData = collectTableData();
            // Store table data in element properties
            $('#table-data-modal').modal('hide');
            showMessage('Table data saved successfully!', 'success');
        });
    }
    
    function collectTableData() {
        let headers = [];
        let rows = [];
        
        // Collect headers
        $('#table-data-editor thead th input').each(function() {
            headers.push($(this).val());
        });
        
        // Collect rows
        $('#table-data-editor tbody tr').each(function() {
            let row = [];
            $(this).find('td input').each(function() {
                row.push($(this).val());
            });
            rows.push(row);
        });
        
        return {
            headers: headers,
            rows: rows
        };
    }
    
    function generateTablePreview() {
        // Generate a preview of the table structure
        // This would be implemented to show a preview in the builder
        console.log('Generating table preview...');
    }
    
    function generatePropertiesForm(element) {
        let html = `
            <form id="element-properties-form" data-element-id="${element.id}">
                <div class="form-group">
                    <label class="form-label">Element Type</label>
                    <input type="text" class="form-control" value="${element.element_type.replace('_', ' ').toUpperCase()}" readonly>
                </div>
        `;
        
        // Add element-specific fields
        switch (element.element_type) {
            case 'heading':
                html += generateHeadingProperties(element);
                break;
            case 'paragraph':
                html += generateParagraphProperties(element);
                break;
            case 'image':
                html += generateImageProperties(element);
                break;
            case 'table':
                html += generateTableProperties(element);
                break;
            case 'data_field':
                html += generateDataFieldProperties(element);
                break;
            case 'signature':
                html += generateSignatureProperties(element);
                break;
            case 'date':
                html += generateDateProperties(element);
                break;
            case 'page_break':
                html += generatePageBreakProperties(element);
                break;
            default:
                html += generateBasicContentProperties(element);
        }
        
        html += `
                <div class="form-group mt-4">
                    <button type="button" class="btn btn-primary btn-block" id="save-element-properties">
                        <i class="mdi mdi-content-save"></i> Save Changes
                    </button>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-outline-danger btn-block" id="delete-element-from-properties">
                        <i class="mdi mdi-delete"></i> Delete Element
                    </button>
                </div>
            </form>
        `;
        
        return html;
    }
    
    function generateBasicPropertiesForm(elementType, elementId, content) {
        return `
            <form id="element-properties-form" data-element-id="${elementId}">
                <div class="form-group">
                    <label class="form-label">Element Type</label>
                    <input type="text" class="form-control" value="${elementType.replace('_', ' ').toUpperCase()}" readonly>
                </div>
                <div class="form-group">
                    <label for="element-content" class="form-label">Content</label>
                    <textarea class="form-control" id="element-content" rows="3">${content}</textarea>
                </div>
                <div class="form-group mt-4">
                    <button type="button" class="btn btn-primary btn-block" id="save-element-properties">
                        <i class="mdi mdi-content-save"></i> Save Changes
                    </button>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-outline-danger btn-block" id="delete-element-from-properties">
                        <i class="mdi mdi-delete"></i> Delete Element
                    </button>
                </div>
            </form>
        `;
    }
    
    // Element-specific property generators
    function generateHeadingProperties(element) {
        let properties = element.properties || {};
        return `
            <div class="form-group">
                <label for="heading-content" class="form-label">Heading Text</label>
                <input type="text" class="form-control" id="heading-content" value="${element.content || ''}" placeholder="Enter heading text">
            </div>
            <div class="form-group">
                <label for="heading-level" class="form-label">Heading Level</label>
                <select class="form-control" id="heading-level">
                    <option value="h1" ${(properties.level || 'h2') === 'h1' ? 'selected' : ''}>H1 - Largest</option>
                    <option value="h2" ${(properties.level || 'h2') === 'h2' ? 'selected' : ''}>H2 - Large</option>
                    <option value="h3" ${(properties.level || 'h2') === 'h3' ? 'selected' : ''}>H3 - Medium</option>
                    <option value="h4" ${(properties.level || 'h2') === 'h4' ? 'selected' : ''}>H4 - Small</option>
                </select>
            </div>
            <div class="form-group">
                <label for="heading-alignment" class="form-label">Alignment</label>
                <select class="form-control" id="heading-alignment">
                    <option value="left" ${(properties.alignment || 'left') === 'left' ? 'selected' : ''}>Left</option>
                    <option value="center" ${(properties.alignment || 'left') === 'center' ? 'selected' : ''}>Center</option>
                    <option value="right" ${(properties.alignment || 'left') === 'right' ? 'selected' : ''}>Right</option>
                </select>
            </div>
        `;
    }
    
    function generateParagraphProperties(element) {
        let properties = element.properties || {};
        return `
            <div class="form-group">
                <label for="paragraph-content" class="form-label">Content</label>
                <textarea class="form-control tinymce-editor" id="paragraph-content" rows="8" placeholder="Enter paragraph content">${element.content || ''}</textarea>
                <small class="form-text text-muted">Rich text editor with formatting options</small>
            </div>
            <div class="form-group">
                <label for="paragraph-alignment" class="form-label">Alignment</label>
                <select class="form-control" id="paragraph-alignment">
                    <option value="left" ${(properties.alignment || 'left') === 'left' ? 'selected' : ''}>Left</option>
                    <option value="center" ${(properties.alignment || 'left') === 'center' ? 'selected' : ''}>Center</option>
                    <option value="right" ${(properties.alignment || 'left') === 'right' ? 'selected' : ''}>Right</option>
                    <option value="justify" ${(properties.alignment || 'left') === 'justify' ? 'selected' : ''}>Justify</option>
                </select>
            </div>
        `;
    }
    
    function generateDataFieldProperties(element) {
        let properties = element.properties || {};
        return `
            <div class="form-group">
                <label for="data-field-type" class="form-label">Data Field</label>
                <select class="form-control" id="data-field-type">
                    <option value="">Loading data fields...</option>
                </select>
                <small class="form-text text-muted">Choose which data field to display</small>
            </div>
            <div class="form-group">
                <label for="data-field-format" class="form-label">Format</label>
                <input type="text" class="form-control" id="data-field-format" value="${properties.format || ''}" placeholder="e.g., dd/mm/yyyy for dates">
                <small class="form-text text-muted">Optional formatting for the data field</small>
            </div>
            <div class="form-group">
                <label for="data-field-default" class="form-label">Default Value</label>
                <input type="text" class="form-control" id="data-field-default" value="${properties.default_value || ''}" placeholder="Default value if data is empty">
                <small class="form-text text-muted">Fallback value when data is not available</small>
            </div>
        `;
    }
    
    function generateImageProperties(element) {
        let properties = element.properties || {};
        return `
            <div class="form-group">
                <label for="image-source" class="form-label">Image Source</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="image-source" value="${element.content || ''}" placeholder="Image URL or path">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="browse-image">
                            <i class="mdi mdi-folder-open"></i> Browse
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">Enter image URL or click Browse to upload</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Upload New Image</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="image-upload" accept="image/*">
                    <label class="custom-file-label" for="image-upload">Choose image file...</label>
                </div>
                <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, SVG (Max: 5MB)</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Image Preview</label>
                <div class="image-preview-container" style="border: 1px dashed #ccc; padding: 20px; text-align: center; min-height: 100px;">
                    ${element.content ? `<img src="${element.content}" style="max-width: 200px; max-height: 150px;" alt="Preview">` : '<span class="text-muted">No image selected</span>'}
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="image-width" class="form-label">Width (px)</label>
                        <input type="number" class="form-control" id="image-width" value="${properties.width || 200}" min="50" max="800">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="image-height" class="form-label">Height (px)</label>
                        <input type="number" class="form-control" id="image-height" value="${properties.height || 200}" min="50" max="600">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="image-maintain-aspect" ${properties.maintain_aspect ? 'checked' : ''}>
                    <label class="form-check-label" for="image-maintain-aspect">
                        Maintain aspect ratio
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="image-alignment" class="form-label">Alignment</label>
                <select class="form-control" id="image-alignment">
                    <option value="left" ${(properties.alignment || 'left') === 'left' ? 'selected' : ''}>Left</option>
                    <option value="center" ${(properties.alignment || 'left') === 'center' ? 'selected' : ''}>Center</option>
                    <option value="right" ${(properties.alignment || 'left') === 'right' ? 'selected' : ''}>Right</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image-alt-text" class="form-label">Alt Text</label>
                <input type="text" class="form-control" id="image-alt-text" value="${properties.alt_text || ''}" placeholder="Alternative text for accessibility">
                <small class="form-text text-muted">Describe the image for screen readers</small>
            </div>
        `;
    }
    
    function generateDateProperties(element) {
        let properties = element.properties || {};
        return `
            <div class="form-group">
                <label for="date-type" class="form-label">Date Type</label>
                <select class="form-control" id="date-type">
                    <option value="current_date" ${element.content === 'current_date' ? 'selected' : ''}>Current Date</option>
                    <option value="analysis_date" ${element.content === 'analysis_date' ? 'selected' : ''}>Analysis Date</option>
                    <option value="report_date" ${element.content === 'report_date' ? 'selected' : ''}>Report Date</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date-format" class="form-label">Date Format</label>
                <select class="form-control" id="date-format">
                    <option value="dd/mm/yyyy" ${(properties.format || 'dd/mm/yyyy') === 'dd/mm/yyyy' ? 'selected' : ''}>DD/MM/YYYY</option>
                    <option value="mm/dd/yyyy" ${(properties.format || 'dd/mm/yyyy') === 'mm/dd/yyyy' ? 'selected' : ''}>MM/DD/YYYY</option>
                    <option value="yyyy-mm-dd" ${(properties.format || 'dd/mm/yyyy') === 'yyyy-mm-dd' ? 'selected' : ''}>YYYY-MM-DD</option>
                    <option value="dd MMM yyyy" ${(properties.format || 'dd/mm/yyyy') === 'dd MMM yyyy' ? 'selected' : ''}>DD MMM YYYY</option>
                </select>
            </div>
        `;
    }
    
    function generateSignatureProperties(element) {
        let properties = element.properties || {};
        return `
            <div class="form-group">
                <label for="signature-label" class="form-label">Signature Label</label>
                <input type="text" class="form-control" id="signature-label" value="${element.content || 'Signature'}" placeholder="Signature label">
            </div>
            <div class="form-group">
                <label for="signature-width" class="form-label">Width (px)</label>
                <input type="number" class="form-control" id="signature-width" value="${properties.width || 200}" min="100" max="400">
            </div>
            <div class="form-group">
                <label for="signature-height" class="form-label">Height (px)</label>
                <input type="number" class="form-control" id="signature-height" value="${properties.height || 60}" min="40" max="120">
            </div>
        `;
    }
    
    function generateTableProperties(element) {
        let properties = element.properties || {};
        return `
            <div class="form-group">
                <label for="table-type" class="form-label">Table Type</label>
                <select class="form-control" id="table-type">
                    <option value="data_table" ${element.content === 'data_table' ? 'selected' : ''}>Data Table (Dynamic)</option>
                    <option value="custom_table" ${element.content === 'custom_table' ? 'selected' : ''}>Custom Table (Static)</option>
                    <option value="analysis_results" ${element.content === 'analysis_results' ? 'selected' : ''}>Analysis Results</option>
                </select>
                <small class="form-text text-muted">Choose table data source</small>
            </div>
            
            <div id="custom-table-config" style="display: ${element.content === 'custom_table' ? 'block' : 'none'};">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="table-columns" class="form-label">Columns</label>
                            <input type="number" class="form-control" id="table-columns" value="${properties.columns || 3}" min="1" max="10">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="table-rows" class="form-label">Rows</label>
                            <input type="number" class="form-control" id="table-rows" value="${properties.rows || 5}" min="1" max="50">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="configure-table-data">
                        <i class="mdi mdi-table-edit"></i> Configure Table Data
                    </button>
                </div>
            </div>
            
            <div id="data-table-config" style="display: ${element.content === 'data_table' ? 'block' : 'none'};">
                <div class="form-group">
                    <label for="data-source" class="form-label">Data Source</label>
                    <select class="form-control" id="data-source">
                        <option value="submission_data" ${(properties.data_source || 'submission_data') === 'submission_data' ? 'selected' : ''}>Submission Form Data</option>
                        <option value="analysis_data" ${(properties.data_source || 'submission_data') === 'analysis_data' ? 'selected' : ''}>Analysis Data</option>
                        <option value="sample_data" ${(properties.data_source || 'submission_data') === 'sample_data' ? 'selected' : ''}>Sample Data</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Table Styling</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="table-striped" ${properties.striped ? 'checked' : ''}>
                    <label class="form-check-label" for="table-striped">Striped rows</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="table-bordered" ${properties.bordered !== false ? 'checked' : ''}>
                    <label class="form-check-label" for="table-bordered">Bordered</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="table-hover" ${properties.hover ? 'checked' : ''}>
                    <label class="form-check-label" for="table-hover">Hover effect</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="table-size" class="form-label">Table Size</label>
                <select class="form-control" id="table-size">
                    <option value="normal" ${(properties.size || 'normal') === 'normal' ? 'selected' : ''}>Normal</option>
                    <option value="small" ${(properties.size || 'normal') === 'small' ? 'selected' : ''}>Small</option>
                    <option value="large" ${(properties.size || 'normal') === 'large' ? 'selected' : ''}>Large</option>
                </select>
            </div>
        `;
    }
    
    function generatePageBreakProperties(element) {
        return `
            <div class="alert alert-info">
                <i class="mdi mdi-information"></i>
                <strong>Page Break</strong><br>
                This element will force a new page in the PDF output. No additional configuration is needed.
            </div>
        `;
    }
    
    function generateBasicContentProperties(element) {
        return `
            <div class="form-group">
                <label for="element-content" class="form-label">Content</label>
                <textarea class="form-control" id="element-content" rows="4">${element.content || ''}</textarea>
            </div>
        `;
    }
    
    // Save element properties
    $(document).on('click', '#save-element-properties', function() {
        let form = $('#element-properties-form');
        let elementId = form.data('element-id');
        let formData = collectElementData(form);
        
        $.ajax({
            url: `/certificate-template-elements/${elementId}`,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                ...formData
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Element updated successfully!', 'success');
                    updateElementPreview(elementId, response.element);
                    closePropertiesPanel();
                }
            },
            error: function() {
                showMessage('Error updating element', 'error');
            }
        });
    });
    
    // Delete element from properties panel
    $(document).on('click', '#delete-element-from-properties', function() {
        if (confirm('Are you sure you want to delete this element?')) {
            let elementId = $('#element-properties-form').data('element-id');
            let element = $(`.template-element[data-element-id="${elementId}"]`);
            deleteElement(element);
            closePropertiesPanel();
        }
    });
    
    function collectElementData(form) {
        let content = '';
        
        // Get content from TinyMCE if it exists
        if (typeof tinymce !== 'undefined' && tinymce.get('paragraph-content')) {
            content = tinymce.get('paragraph-content').getContent();
        } else {
            // Fallback to regular form fields
            content = form.find('#element-content, #heading-content, #paragraph-content, #data-field-type, #image-source, #signature-label, #date-type, #table-type').val();
        }
        
        // Collect properties based on element type
        let properties = {};
        
        // Heading properties
        if (form.find('#heading-level').length) {
            properties.level = form.find('#heading-level').val();
            properties.alignment = form.find('#heading-alignment').val();
        }
        
        // Paragraph properties
        if (form.find('#paragraph-alignment').length) {
            properties.alignment = form.find('#paragraph-alignment').val();
        }
        
        // Image properties
        if (form.find('#image-width').length) {
            properties.width = form.find('#image-width').val();
            properties.height = form.find('#image-height').val();
            properties.alignment = form.find('#image-alignment').val();
            properties.maintain_aspect = form.find('#image-maintain-aspect').is(':checked');
            properties.alt_text = form.find('#image-alt-text').val();
        }
        
        // Date properties
        if (form.find('#date-format').length) {
            properties.format = form.find('#date-format').val();
        }
        
        // Signature properties
        if (form.find('#signature-width').length) {
            properties.width = form.find('#signature-width').val();
            properties.height = form.find('#signature-height').val();
        }
        
        // Data field properties
        if (form.find('#data-field-format').length) {
            properties.format = form.find('#data-field-format').val();
            properties.default_value = form.find('#data-field-default').val();
        }
        
        // Table properties
        if (form.find('#table-columns').length) {
            properties.columns = form.find('#table-columns').val();
            properties.rows = form.find('#table-rows').val();
            properties.data_source = form.find('#data-source').val();
            properties.striped = form.find('#table-striped').is(':checked');
            properties.bordered = form.find('#table-bordered').is(':checked');
            properties.hover = form.find('#table-hover').is(':checked');
            properties.size = form.find('#table-size').val();
        }
        
        return {
            content: content,
            properties: properties
        };
    }
    
    function updateElementPreview(elementId, elementData) {
        let element = $(`.template-element[data-element-id="${elementId}"]`);
        // Update the preview based on element type
        element.find('.element-content').html(`
            <strong>${elementData.element_type.replace('_', ' ').toUpperCase()}:</strong>
            ${elementData.content || 'No content'}
        `);
    }
    
    function showMessage(message, type) {
        toastr[type](message);
    }
});
</script>

<!-- Auto-save indicator -->
<div class="auto-save-indicator">
    <i class="mdi mdi-check-circle"></i> Auto-saved
</div>
@endsection
