@extends('layouts.lab.layout.app')

@section('title2')
<title>Modern Template Builder - {{ $template->name }} | Lab Management</title>
@endsection

@section('content2')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/modern-builder/builder.css') }}">
<link rel="stylesheet" href="{{ asset('css/modern-builder/components.css') }}">
@endpush

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
            'name' => 'Modern Builder',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- Top Toolbar -->
    <div class="builder-toolbar">
        <div class="container-fluid">
            <div class="row align-items-center py-3">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="mdi mdi-pencil"></i> Modern Template Builder
                        <small class="text-muted">{{ $template->name }}</small>
                    </h5>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success" id="save-layout">
                            <i class="mdi mdi-content-save"></i> Save Layout
                        </button>
                        <button type="button" class="btn btn-info" id="preview-layout">
                            <i class="mdi mdi-eye"></i> Preview
                        </button>
                        <a href="{{ route('certificate-templates.show', $template) }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Builder Container -->
    <div class="modern-builder-container">
        <!-- Left Sidebar: Sections Panel -->
        <div class="builder-sidebar" id="sections-panel">
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="mdi mdi-folder-multiple"></i> Sections</h6>
                    <button class="btn btn-sm btn-primary" id="add-section-btn">
                        <i class="mdi mdi-plus"></i> Add Section
                    </button>
                </div>
                <div id="sections-list">
                    @forelse($template->sections as $section)
                        @include('certificate-templates.modern-builder.partials.section-item', ['section' => $section])
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="mdi mdi-folder-outline" style="font-size: 3rem;"></i>
                            <p class="mt-2">No sections yet</p>
                            <p class="small">Click "Add Section" to get started</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Center: Builder Canvas -->
        <div class="builder-canvas" id="builder-canvas">
            <div id="canvas-content">
                @forelse($template->sections as $section)
                    @include('certificate-templates.modern-builder.partials.canvas-section', ['section' => $section])
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-file-document-outline" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">Start Building Your Template</h5>
                        <p>Add a section from the left panel to begin</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Sidebar: Properties Panel -->
        <div class="builder-properties" id="properties-panel">
            <div class="p-3">
                <h6 class="mb-3"><i class="mdi mdi-cog"></i> Properties</h6>
                <div id="properties-content">
                    <div class="text-center text-muted py-4">
                        <i class="mdi mdi-cursor-pointer" style="font-size: 2rem;"></i>
                        <p class="mt-2 small">Select an element or section to edit properties</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Element Palette (Floating) -->
    <div class="element-palette" id="element-palette" style="display: none;">
        <div class="palette-header">
            <h6 class="mb-0"><i class="mdi mdi-palette"></i> Elements</h6>
            <button class="btn btn-sm btn-link text-white" id="close-palette">
                <i class="mdi mdi-close"></i>
            </button>
        </div>
        <div class="palette-body">
            <div class="element-type" data-type="text">
                <i class="mdi mdi-text"></i> Text
            </div>
            <div class="element-type" data-type="heading">
                <i class="mdi mdi-format-header-1"></i> Heading
            </div>
            <div class="element-type" data-type="paragraph">
                <i class="mdi mdi-format-paragraph"></i> Paragraph
            </div>
            <div class="element-type" data-type="image">
                <i class="mdi mdi-image"></i> Image
            </div>
            <div class="element-type" data-type="button">
                <i class="mdi mdi-button-cursor"></i> Button
            </div>
            <div class="element-type" data-type="table">
                <i class="mdi mdi-table"></i> Table
            </div>
            <div class="element-type" data-type="divider">
                <i class="mdi mdi-minus"></i> Divider
            </div>
            <div class="element-type" data-type="list">
                <i class="mdi mdi-format-list-bulleted"></i> List
            </div>
            <div class="element-type" data-type="icon">
                <i class="mdi mdi-emoticon"></i> Icon
            </div>
            <div class="element-type" data-type="custom_html">
                <i class="mdi mdi-code-tags"></i> Custom HTML
            </div>
            <h6 style="margin-top: 10px; margin-bottom: 5px; font-size: 12px; color: #6c757d;">Form Inputs</h6>
            <div class="element-type" data-type="input_text">
                <i class="mdi mdi-form-textbox"></i> Text Input
            </div>
            <div class="element-type" data-type="input_email">
                <i class="mdi mdi-email"></i> Email Input
            </div>
            <div class="element-type" data-type="input_number">
                <i class="mdi mdi-numeric"></i> Number Input
            </div>
            <div class="element-type" data-type="input_date">
                <i class="mdi mdi-calendar"></i> Date Input
            </div>
            <div class="element-type" data-type="textarea">
                <i class="mdi mdi-text-box"></i> Textarea
            </div>
            <div class="element-type" data-type="select">
                <i class="mdi mdi-menu-down"></i> Select
            </div>
        </div>
    </div>
</main>

@include('certificate-templates.modern-builder.modals.section-config')
@include('certificate-templates.modern-builder.modals.element-config')
@include('certificate-templates.modern-builder.modals.css-config')
@include('certificate-templates.modern-builder.modals.data-config')
@include('certificate-templates.modern-builder.modals.query-builder')

@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.19/dist/interact.min.js"></script>
<script src="{{ asset('js/modern-builder/drag-drop.js') }}"></script>
<script src="{{ asset('js/modern-builder/layout-manager.js') }}"></script>
<script src="{{ asset('js/modern-builder/properties-editor.js') }}"></script>
<script src="{{ asset('js/modern-builder/query-builder.js') }}"></script>
<script src="{{ asset('js/modern-builder/preview-engine.js') }}"></script>
<script>
const ModernBuilder = {
    templateId: {{ $template->id }},
    csrfToken: '{{ csrf_token() }}',
    selectedElement: null,
    selectedSection: null,
    
    init() {
        this.bindEvents();
        this.initDragDrop();
    },
    
    bindEvents() {
        // Add section
        $('#add-section-btn').on('click', () => this.showSectionModal());
        
        // Save layout
        $('#save-layout').on('click', () => this.saveLayout());
        
        // Preview
        $('#preview-layout').on('click', () => this.previewLayout());
        
        // Element palette
        $(document).on('click', '.add-element-btn', (e) => {
            const cellId = $(e.currentTarget).data('cell-id');
            this.showElementPalette(cellId);
        });
        
        $('#close-palette').on('click', () => {
            $('#element-palette').hide();
        });
        
        // Element type selection
        $(document).on('click', '.element-type', function() {
            const elementType = $(this).data('type');
            const cellId = $(this).closest('#element-palette').data('target-cell-id');
            ModernBuilder.addElement(cellId, elementType);
        });
        
        // Add row/column/cell buttons
        $(document).on('click', '.add-row-btn', (e) => {
            const sectionId = $(e.currentTarget).data('section-id');
            if (window.LayoutManager) {
                new window.LayoutManager().addRow(sectionId);
            }
        });
        
        $(document).on('click', '.add-column-btn', (e) => {
            const sectionId = $(e.currentTarget).data('section-id');
            const rowId = $(e.currentTarget).data('row-id');
            if (window.LayoutManager) {
                new window.LayoutManager().addColumn(sectionId, rowId);
            }
        });
        
        $(document).on('click', '.add-cell-btn', (e) => {
            const sectionId = $(e.currentTarget).data('section-id');
            const columnId = $(e.currentTarget).data('column-id');
            if (window.LayoutManager) {
                new window.LayoutManager().addCell(sectionId, columnId);
            }
        });
        
        // Delete buttons
        $(document).on('click', '.delete-row-btn', (e) => {
            const sectionId = $(e.currentTarget).data('section-id');
            const rowId = $(e.currentTarget).data('row-id');
            if (window.LayoutManager) {
                new window.LayoutManager().removeRow(sectionId, rowId);
            }
        });
        
        $(document).on('click', '.delete-column-btn', (e) => {
            const sectionId = $(e.currentTarget).data('section-id');
            const columnId = $(e.currentTarget).data('column-id');
            if (window.LayoutManager) {
                new window.LayoutManager().removeColumn(sectionId, columnId);
            }
        });
        
        $(document).on('click', '.delete-cell-btn', (e) => {
            const sectionId = $(e.currentTarget).data('section-id');
            const cellId = $(e.currentTarget).data('cell-id');
            if (window.LayoutManager) {
                new window.LayoutManager().removeCell(sectionId, cellId);
            }
        });
        
        // Element selection
        $(document).on('click', '.canvas-element', function() {
            $('.canvas-element').removeClass('selected');
            $(this).addClass('selected');
            const elementId = $(this).data('element-id');
            ModernBuilder.selectedElement = elementId;
            if (window.PropertiesEditor) {
                new window.PropertiesEditor().selectElement(elementId);
            }
        });
        
        // Section selection
        $(document).on('click', '.canvas-section', function() {
            $('.canvas-section').removeClass('selected');
            $(this).addClass('selected');
            const sectionId = $(this).data('section-id');
            ModernBuilder.selectedSection = sectionId;
            if (window.PropertiesEditor) {
                new window.PropertiesEditor().selectSection(sectionId);
            }
        });
        
        // Configure CSS/Data buttons
        $(document).on('click', '.configure-css, #configure-css-btn', function() {
            const targetId = $(this).data('id') || ModernBuilder.selectedElement;
            const targetType = 'element';
            $('#css-config-modal').data('target-id', targetId).data('target-type', targetType).modal('show');
        });
        
        $(document).on('click', '.configure-data, #configure-data-btn', function() {
            const targetId = $(this).data('id') || ModernBuilder.selectedElement;
            $('#data-config-modal').data('target-id', targetId).modal('show');
        });
        
        // Query builder
        $(document).on('click', '#open-query-builder-btn', function() {
            const targetId = $('#data-config-modal').data('target-id');
            $('#query-builder-modal').data('target-id', targetId).modal('show');
            if (window.QueryBuilder) {
                new window.QueryBuilder();
            }
        });
        
        // Data type change
        $('#data-type').on('change', function() {
            const type = $(this).val();
            $('.data-config-section').hide();
            if (type === 'static') {
                $('#static-data-config').show();
            } else if (type === 'dynamic_model') {
                $('#dynamic-model-config').show();
            } else if (type === 'dynamic_derived') {
                $('#dynamic-derived-config').show();
            }
        });
        
        // Save section
        $('#save-section').on('click', () => this.saveSection());
        
        // Save element
        $('#save-element').on('click', () => this.saveElement());
        
        // Save CSS config
        $('#save-css-config').on('click', () => this.saveCssConfig());
        
        // Save data config
        $('#save-data-config').on('click', () => this.saveDataConfig());
        
        // Delete section
        $(document).on('click', '.delete-section', (e) => {
            const sectionId = $(e.currentTarget).data('id');
            if (confirm('Are you sure you want to delete this section? This will also delete all its content.')) {
                this.deleteSection(sectionId);
            }
        });
        
        // Delete element
        $(document).on('click', '.delete-element-btn', (e) => {
            const elementId = $(e.currentTarget).data('element-id');
            if (confirm('Are you sure you want to delete this element?')) {
                this.deleteElement(elementId);
            }
        });
        
        // Edit section
        $(document).on('click', '.edit-section, .edit-section-btn', (e) => {
            const sectionId = $(e.currentTarget).data('id') || $(e.currentTarget).data('section-id');
            this.showSectionModal(sectionId);
        });
        
        // Edit element
        $(document).on('click', '.edit-element-btn', (e) => {
            const elementId = $(e.currentTarget).data('element-id');
            this.showElementModal(elementId);
        });
    },
    
    deleteSection(sectionId) {
        $.ajax({
            url: `/certificate-templates/modern-sections/${sectionId}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    this.showMessage('success', response.message);
                    location.reload();
                }
            },
            error: (xhr) => {
                this.showMessage('error', 'Failed to delete section');
            }
        });
    },
    
    deleteElement(elementId) {
        $.ajax({
            url: `/certificate-templates/modern-elements/${elementId}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    this.showMessage('success', response.message);
                    location.reload();
                }
            },
            error: (xhr) => {
                this.showMessage('error', 'Failed to delete element');
            }
        });
    },
    
    showElementModal(elementId) {
        $.ajax({
            url: `/certificate-templates/modern-elements/${elementId}`,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    const element = response.element;
                    $('#element-id').val(element.id);
                    $('#element-cell-id').val(element.parent_cell_id);
                    $('#element-type').val(element.element_type);
                    $('#element-content').val(element.content || '');
                    
                    if (element.element_type === 'heading') {
                        $('#element-heading-level-group').show();
                        $('#heading-level').val(element.properties?.level || 1);
                    } else {
                        $('#element-heading-level-group').hide();
                    }
                    
                    $('#element-modal').modal('show');
                }
            }
        });
    },
    
    initDragDrop() {
        // Will be implemented in drag-drop.js
    },
    
    showSectionModal(sectionId = null) {
        if (sectionId) {
            // Load section data
            $.ajax({
                url: `/certificate-templates/modern-sections/${sectionId}`,
                method: 'GET',
                success: (response) => {
                    if (response.success) {
                        $('#section-id').val(sectionId);
                        $('#section-title').val(response.section.title);
                        $('#section-description').val(response.section.description || '');
                    }
                }
            });
        } else {
            $('#section-form')[0].reset();
            $('#section-id').val('');
        }
        $('#section-modal').modal('show');
    },
    
    saveSection() {
        const sectionId = $('#section-id').val();
        const data = {
            title: $('#section-title').val(),
            description: $('#section-description').val()
        };
        
        const url = sectionId 
            ? `/certificate-templates/modern-sections/${sectionId}`
            : `/certificate-templates/${this.templateId}/modern-sections`;
        
        $.ajax({
            url: url,
            method: sectionId ? 'PUT' : 'POST',
            data: {
                ...data,
                _method: sectionId ? 'PUT' : 'POST'
            },
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    $('#section-modal').modal('hide');
                    this.showMessage('success', response.message);
                    location.reload();
                }
            },
            error: (xhr) => {
                this.showMessage('error', 'Failed to save section');
            }
        });
    },
    
    saveElement() {
        const elementId = $('#element-id').val();
        const cellId = $('#element-cell-id').val();
        const elementType = $('#element-type').val();
        const data = {
            element_type: elementType,
            content: $('#element-content').val(),
            parent_cell_id: cellId,
            properties: {}
        };
        
        // Add heading level if it's a heading
        if (elementType === 'heading') {
            data.properties.level = parseInt($('#heading-level').val()) || 1;
        }
        
        const sectionId = $('.canvas-section.selected').data('section-id');
        if (!sectionId) {
            this.showMessage('error', 'Please select a section first');
            return;
        }
        
        const url = elementId
            ? `/certificate-templates/modern-elements/${elementId}`
            : `/certificate-templates/modern-sections/${sectionId}/elements`;
        
        $.ajax({
            url: url,
            method: elementId ? 'PUT' : 'POST',
            data: {
                ...data,
                _method: elementId ? 'PUT' : 'POST'
            },
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    $('#element-modal').modal('hide');
                    this.showMessage('success', response.message);
                    location.reload();
                }
            },
            error: (xhr) => {
                this.showMessage('error', 'Failed to save element');
            }
        });
    },
    
    saveCssConfig() {
        const targetId = $('#css-config-modal').data('target-id');
        const targetType = $('#css-config-modal').data('target-type') || 'element';
        
        const cssConfig = {
            margin: {
                top: $('#margin-top').val() || '0',
                right: $('#margin-right').val() || '0',
                bottom: $('#margin-bottom').val() || '0',
                left: $('#margin-left').val() || '0'
            },
            padding: {
                top: $('#padding-top').val() || '0',
                right: $('#padding-right').val() || '0',
                bottom: $('#padding-bottom').val() || '0',
                left: $('#padding-left').val() || '0'
            },
            dimensions: {
                width: $('#width').val() || 'auto',
                height: $('#height').val() || 'auto'
            },
            alignment: $('#alignment').val() || 'left',
            border: {
                width: $('#border-width').val() || '0',
                style: 'solid',
                color: '#000000'
            },
            background_color: $('#background-color').val() || null,
            text_color: $('#text-color').val() || null,
            custom_class: $('#custom-class').val() || null,
            custom_css: $('#custom-css').val() || null
        };
        
        if (targetType === 'element') {
            $.ajax({
                url: `/certificate-templates/modern-elements/${targetId}/css-config`,
                method: 'PUT',
                data: {
                    css_config: cssConfig,
                    _method: 'PUT'
                },
                headers: { 'X-CSRF-TOKEN': this.csrfToken },
                success: (response) => {
                    if (response.success) {
                        $('#css-config-modal').modal('hide');
                        this.showMessage('success', response.message);
                        if (window.PropertiesEditor) {
                            new window.PropertiesEditor().updatePreview();
                        }
                    }
                }
            });
        }
    },
    
    saveDataConfig() {
        const targetId = $('#data-config-modal').data('target-id');
        const dataType = $('#data-type').val();
        
        let dataConfig = { type: dataType };
        
        if (dataType === 'static') {
            dataConfig.value = $('#static-value').val();
        } else if (dataType === 'dynamic_model') {
            dataConfig.model_config = {
                model: $('#model-class').val(),
                field: $('#model-field').val(),
                row_id: $('#model-row-id').val() || null
            };
        } else if (dataType === 'dynamic_derived') {
            // Check if we have a query config from the query builder
            const queryConfig = $('#data-config-modal').data('query-config');
            if (queryConfig) {
                dataConfig.query_config = queryConfig;
            } else {
                // Use raw SQL if provided
                dataConfig.query_config = {
                    raw_sql: $('#raw-sql').val() || null
                };
            }
        }
        
        $.ajax({
            url: `/certificate-templates/modern-elements/${targetId}/data-config`,
            method: 'PUT',
            data: {
                data_config: dataConfig,
                _method: 'PUT'
            },
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    $('#data-config-modal').modal('hide');
                    this.showMessage('success', response.message);
                    if (window.PropertiesEditor) {
                        new window.PropertiesEditor().updatePreview();
                    }
                }
            }
        });
    },
    
    saveLayout() {
        // Collect layout structure from DOM
        const layout = this.collectLayout();
        
        $.ajax({
            url: `/certificate-templates/${this.templateId}/save-layout`,
            method: 'POST',
            data: { layout },
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    this.showMessage('success', response.message);
                }
            },
            error: (xhr) => {
                this.showMessage('error', 'Failed to save layout');
            }
        });
    },
    
    collectLayout() {
        // Collect layout structure from canvas
        const sections = [];
        $('#canvas-content .canvas-section').each(function() {
            const sectionId = $(this).data('section-id');
            // Collect rows/columns/cells structure
            sections.push({
                id: sectionId,
                layout_structure: ModernBuilder.collectSectionStructure($(this))
            });
        });
        return { sections };
    },
    
    collectSectionStructure($section) {
        // Collect rows/columns/cells from section
        const rows = [];
        $section.find('.canvas-row').each(function() {
            const row = {
                id: $(this).data('row-id'),
                columns: []
            };
            $(this).find('.canvas-column').each(function() {
                const column = {
                    id: $(this).data('column-id'),
                    width: $(this).css('width'),
                    cells: []
                };
                $(this).find('.canvas-cell').each(function() {
                    const cell = {
                        id: $(this).data('cell-id'),
                        elements: [],
                        sub_sections: []
                    };
                    $(this).find('.canvas-element').each(function() {
                        cell.elements.push($(this).data('element-id'));
                    });
                    column.cells.push(cell);
                });
                row.columns.push(column);
            });
            rows.push(row);
        });
        return { rows };
    },
    
    previewLayout() {
        const layout = this.collectLayout();
        
        $.ajax({
            url: `/certificate-templates/${this.templateId}/preview`,
            method: 'POST',
            data: { layout },
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    // Open preview in new window
                    const previewWindow = window.open('', '_blank');
                    previewWindow.document.write(response.html);
                    previewWindow.document.close();
                }
            }
        });
    },
    
    showElementPalette(cellId) {
        $('#element-palette').data('target-cell-id', cellId).show();
    },
    
    addElement(cellId, elementType) {
        $('#element-palette').hide();
        // Open element config modal
        $('#element-id').val('');
        $('#element-cell-id').val(cellId);
        $('#element-type').val(elementType);
        $('#element-content').val('');
        
        // Show/hide heading level selector
        if (elementType === 'heading') {
            $('#element-heading-level-group').show();
        } else {
            $('#element-heading-level-group').hide();
        }
        
        $('#element-modal').modal('show');
    },
    
    showMessage(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alert = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>`;
        $('main').prepend(alert);
        setTimeout(() => $('.alert').fadeOut(), 5000);
    }
};

$(document).ready(() => {
    ModernBuilder.init();
});
</script>
@endsection

