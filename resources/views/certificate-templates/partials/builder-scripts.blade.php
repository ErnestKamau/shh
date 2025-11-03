<script>
// Visual Builder JavaScript
const VisualBuilder = {
    templateId: {{ $template->id }},
    csrfToken: '{{ csrf_token() }}',
    canvas: null,
    zoom: 1,
    grid: 20,
    snapEnabled: true,
    selectedElement: null,
    selectedHolder: null,
    selectedElementType: null,
    autoSaveTimeout: null,
    
    init() {
        console.log('VisualBuilder initializing...');
        this.canvas = document.getElementById('designer-canvas');
        this.bindEvents();
        this.initInteractions();
        this.initCanvasControls();
    },
    
    bindEvents() {
        // Section management
        $('#add-section-btn, #add-first-section').on('click', () => this.showSectionModal());
        $('#save-section').on('click', () => this.saveSection());
        $(document).on('click', '.edit-section', (e) => {
            const sectionId = $(e.currentTarget).data('id');
            this.showSectionModal(sectionId);
        });
        $(document).on('click', '.delete-section', (e) => {
            const sectionId = $(e.currentTarget).data('id');
            this.deleteSection(sectionId);
        });
        
        // Holder management
        $(document).on('click', '.add-holder-btn', (e) => {
            const sectionId = $(e.currentTarget).data('section-id');
            this.showHolderModal(sectionId);
        });
        $('#save-holder').on('click', () => this.saveHolder());
        $(document).on('click', '.btn-holder-edit', (e) => {
            const holderId = $(e.currentTarget).data('id');
            this.showHolderModal(null, holderId);
        });
        $(document).on('click', '.btn-holder-delete', (e) => {
            e.stopPropagation();
            const holderId = $(e.currentTarget).data('id');
            this.deleteHolder(holderId);
        });
        
        // Element management
        $('.element-type-btn').on('click', (e) => {
            const elementType = $(e.currentTarget).data('type');
            this.selectedElementType = elementType;
            toastr.info('Click on a holder to add the element');
            $('.element-type-btn').removeClass('active');
            $(e.currentTarget).addClass('active');
        });
        
        $(document).on('click', '.element-holder-container', (e) => {
            if (this.selectedElementType && !$(e.target).closest('.canvas-element').length) {
                const holderId = $(e.currentTarget).data('holder-id');
                this.addElementToHolder(holderId, e.offsetX, e.offsetY);
            }
        });
        
        $(document).on('click', '.btn-element-edit', (e) => {
            e.stopPropagation();
            const elementId = $(e.currentTarget).data('id');
            this.showElementModal(elementId);
        });
        
        $(document).on('click', '.btn-element-delete', (e) => {
            e.stopPropagation();
            const elementId = $(e.currentTarget).data('id');
            this.deleteElement(elementId);
        });
        
        $('#save-element-properties').on('click', () => this.saveElementProperties());
        
        // Template actions
        $('#save-template').on('click', () => this.showMessage('All changes are auto-saved', 'info'));
        $('#preview-template').on('click', () => {
            window.open(`/certificate-templates/${this.templateId}/preview`, '_blank');
        });
    },
    
    initCanvasControls() {
        // Zoom controls
        $('#zoom-in').on('click', () => this.adjustZoom(0.1));
        $('#zoom-out').on('click', () => this.adjustZoom(-0.1));
        $('#reset-zoom').on('click', () => this.setZoom(1));
        
        // Grid toggle
        $('#toggle-grid').on('click', (e) => {
            $(this.canvas).toggleClass('grid-hidden');
            $(e.currentTarget).toggleClass('active');
        });
        
        // Snap toggle
        $('#toggle-snap').on('click', (e) => {
            this.snapEnabled = !this.snapEnabled;
            $(e.currentTarget).toggleClass('active');
            toastr.info(this.snapEnabled ? 'Snap enabled' : 'Snap disabled');
        });
    },
    
    adjustZoom(delta) {
        this.setZoom(Math.max(0.25, Math.min(2, this.zoom + delta)));
    },
    
    setZoom(level) {
        this.zoom = level;
        $(this.canvas).css('transform', `scale(${level})`);
        $(this.canvas).css('transform-origin', 'top left');
        $('.zoom-level').text(`${Math.round(level * 100)}%`);
    },
    
    initInteractions() {
        // Initialize Interact.js for element holders
        interact('.element-holder-container')
            .draggable({
                inertia: true,
                modifiers: [
                    interact.modifiers.snap({
                        targets: [
                            interact.snappers.grid({ x: this.grid, y: this.grid })
                        ],
                        range: Infinity,
                        relativePoints: [ { x: 0, y: 0 } ],
                        enabled: () => this.snapEnabled
                    }),
                    interact.modifiers.restrict({
                        restriction: 'parent',
                        elementRect: { top: 0, left: 0, bottom: 1, right: 1 },
                        endOnly: true
                    })
                ],
                listeners: {
                    move: this.dragMoveListener.bind(this),
                    end: (event) => {
                        const holderId = $(event.target).data('holder-id');
                        this.saveHolderPosition(holderId, event.target);
                    }
                }
            })
            .resizable({
                edges: { left: true, right: true, bottom: true, top: true },
                modifiers: [
                    interact.modifiers.restrictSize({
                        min: { width: 100, height: 80 }
                    })
                ],
                inertia: true,
                listeners: {
                    move: (event) => {
                        let { x, y } = event.target.dataset;
                        x = (parseFloat(x) || 0) + event.deltaRect.left;
                        y = (parseFloat(y) || 0) + event.deltaRect.top;
                        
                        Object.assign(event.target.style, {
                            width: `${event.rect.width}px`,
                            height: `${event.rect.height}px`,
                            transform: `translate(${x}px, ${y}px)`
                        });
                        
                        Object.assign(event.target.dataset, { x, y });
                    },
                    end: (event) => {
                        const holderId = $(event.target).data('holder-id');
                        this.saveHolderPosition(holderId, event.target);
                    }
                }
            });
        
        // Initialize Interact.js for canvas elements
        interact('.canvas-element')
            .draggable({
                inertia: true,
                modifiers: [
                    interact.modifiers.snap({
                        targets: [
                            interact.snappers.grid({ x: this.grid, y: this.grid })
                        ],
                        range: Infinity,
                        relativePoints: [ { x: 0, y: 0 } ],
                        enabled: () => this.snapEnabled
                    }),
                    interact.modifiers.restrict({
                        restriction: 'parent',
                        elementRect: { top: 0, left: 0, bottom: 1, right: 1 },
                        endOnly: true
                    })
                ],
                listeners: {
                    move: this.dragMoveListener.bind(this),
                    end: (event) => {
                        const elementId = $(event.target).data('element-id');
                        this.saveElementPosition(elementId, event.target);
                    }
                }
            })
            .resizable({
                edges: { left: true, right: true, bottom: true, top: true },
                modifiers: [
                    interact.modifiers.restrictSize({
                        min: { width: 50, height: 30 }
                    })
                ],
                inertia: true,
                listeners: {
                    move: (event) => {
                        let { x, y } = event.target.dataset;
                        x = (parseFloat(x) || 0) + event.deltaRect.left;
                        y = (parseFloat(y) || 0) + event.deltaRect.top;
                        
                        Object.assign(event.target.style, {
                            width: `${event.rect.width}px`,
                            height: `${event.rect.height}px`,
                            transform: `translate(${x}px, ${y}px)`
                        });
                        
                        Object.assign(event.target.dataset, { x, y });
                    },
                    end: (event) => {
                        const elementId = $(event.target).data('element-id');
                        this.saveElementPosition(elementId, event.target);
                    }
                }
            });
    },
    
    dragMoveListener(event) {
        const target = event.target;
        const x = (parseFloat(target.dataset.x) || 0) + event.dx;
        const y = (parseFloat(target.dataset.y) || 0) + event.dy;
        
        target.style.transform = `translate(${x}px, ${y}px)`;
        target.dataset.x = x;
        target.dataset.y = y;
    },
    
    // Section Management
    showSectionModal(sectionId = null) {
        $('#section-form')[0].reset();
        $('#section-id').val('');
        
        if (sectionId) {
            // Fetch and populate section data
            $.get(`/certificate-template-sections/${sectionId}`)
                .done((response) => {
                    if (response.success) {
                        const section = response.section;
                        $('#section-id').val(sectionId);
                        $('#section-title').val(section.title);
                        $('#section-description').val(section.description);
                        $('#section-collapsible').prop('checked', section.is_collapsible);
                    }
                })
                .fail(() => {
                    toastr.error('Failed to load section data');
                });
        }
        
        $('#section-modal').modal('show');
    },
    
    saveSection() {
        const sectionId = $('#section-id').val();
        const isEdit = sectionId !== '';
        
        const data = {
            title: $('#section-title').val(),
            description: $('#section-description').val(),
            is_collapsible: $('#section-collapsible').is(':checked') ? 1 : 0,
            _token: this.csrfToken
        };
        
        const url = isEdit 
            ? `/certificate-template-sections/${sectionId}`
            : `/certificate-templates/${this.templateId}/sections`;
        const method = isEdit ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: data,
            success: (response) => {
                this.showMessage(response.message, 'success');
                $('#section-modal').modal('hide');
                location.reload();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    deleteSection(sectionId) {
        if (!confirm('Delete this section and all its holders and elements?')) return;
        
        $.ajax({
            url: `/certificate-template-sections/${sectionId}`,
            method: 'DELETE',
            data: { _token: this.csrfToken },
            success: (response) => {
                this.showMessage(response.message, 'success');
                location.reload();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    // Holder Management
    showHolderModal(sectionId = null, holderId = null) {
        $('#holder-form')[0].reset();
        $('#holder-id').val('');
        $('#holder-section-id').val(sectionId || '');
        $('#holder-max-elements').val(10);
        
        if (holderId) {
            $.get(`/certificate-template-holders/${holderId}`)
                .done((response) => {
                    if (response.success) {
                        const holder = response.holder;
                        $('#holder-id').val(holderId);
                        $('#holder-section-id').val(holder.certificate_template_section_id);
                        $('#holder-type').val(holder.holder_type);
                        $('#holder-max-elements').val(holder.max_elements);
                    }
                })
                .fail(() => toastr.error('Failed to load holder data'));
        }
        
        $('#holder-modal').modal('show');
    },
    
    saveHolder() {
        const holderId = $('#holder-id').val();
        const sectionId = $('#holder-section-id').val();
        const isEdit = holderId !== '';
        
        const data = {
            holder_type: $('#holder-type').val(),
            max_elements: $('#holder-max-elements').val(),
            _token: this.csrfToken,
            canvas_width: $(this.canvas).data('width'),
            canvas_height: $(this.canvas).data('height')
        };
        
        const url = isEdit 
            ? `/certificate-template-holders/${holderId}`
            : `/certificate-template-sections/${sectionId}/holders`;
        const method = isEdit ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: data,
            success: (response) => {
                this.showMessage(response.message, 'success');
                $('#holder-modal').modal('hide');
                location.reload();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    deleteHolder(holderId) {
        if (!confirm('Delete this holder and all its elements?')) return;
        
        $.ajax({
            url: `/certificate-template-holders/${holderId}`,
            method: 'DELETE',
            data: { _token: this.csrfToken },
            success: (response) => {
                this.showMessage(response.message, 'success');
                location.reload();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    saveHolderPosition(holderId, target) {
        const rect = target.getBoundingClientRect();
        const canvasRect = this.canvas.getBoundingClientRect();
        
        const x = parseFloat(target.dataset.x) || parseFloat(target.style.left) || 0;
        const y = parseFloat(target.dataset.y) || parseFloat(target.style.top) || 0;
        const width = parseFloat(target.style.width);
        const height = parseFloat(target.style.height);
        
        const canvasWidth = $(this.canvas).data('width');
        const canvasHeight = $(this.canvas).data('height');
        
        $.ajax({
            url: `/certificate-template-holders/${holderId}/position`,
            method: 'PUT',
            data: {
                position_x: x,
                position_y: y,
                width: width,
                height: height,
                position_x_percent: (x / canvasWidth) * 100,
                position_y_percent: (y / canvasHeight) * 100,
                width_percent: (width / canvasWidth) * 100,
                height_percent: (height / canvasHeight) * 100,
                _token: this.csrfToken
            },
            success: () => {
                this.showAutoSave();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    // Element Management
    addElementToHolder(holderId, x, y) {
        if (!this.selectedElementType) {
            toastr.warning('Please select an element type first');
            return;
        }
        
        $.ajax({
            url: `/certificate-template-holders/${holderId}/elements`,
            method: 'POST',
            data: {
                element_type: this.selectedElementType,
                position_x: x || 10,
                position_y: y || 10,
                width: 200,
                height: 100,
                _token: this.csrfToken
            },
            success: (response) => {
                this.showMessage(response.message, 'success');
                this.selectedElementType = null;
                $('.element-type-btn').removeClass('active');
                location.reload();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    showElementModal(elementId) {
        $.get(`/certificate-template-elements/${elementId}`)
            .done((response) => {
                if (response.success) {
                    const element = response.element;
                    const propertiesHtml = this.generateElementPropertiesForm(element);
                    $('#element-properties-content').html(propertiesHtml);
                    $('#element-modal').modal('show');
                }
            })
            .fail(() => toastr.error('Failed to load element'));
    },
    
    generateElementPropertiesForm(element) {
        let html = `
            <input type="hidden" id="element-id" value="${element.id}">
            <div class="form-group">
                <label>Element Type</label>
                <input type="text" class="form-control" value="${element.element_type.toUpperCase()}" readonly>
            </div>
        `;
        
        switch(element.element_type) {
            case 'heading':
                html += this.generateHeadingProperties(element);
                break;
            case 'paragraph':
                html += this.generateParagraphProperties(element);
                break;
            case 'image':
                html += this.generateImageProperties(element);
                break;
            case 'table':
                html += this.generateTableProperties(element);
                break;
            case 'data_field':
                html += this.generateDataFieldProperties(element);
                break;
            case 'signature':
                html += this.generateSignatureProperties(element);
                break;
            case 'date':
                html += this.generateDateProperties(element);
                break;
            default:
                html += `
                    <div class="form-group">
                        <label>Content</label>
                        <textarea class="form-control" id="element-content" rows="3">${element.content || ''}</textarea>
                    </div>
                `;
        }
        
        return html;
    },
    
    generateHeadingProperties(element) {
        const props = element.properties || {};
        return `
            <div class="form-group">
                <label>Heading Text</label>
                <input type="text" class="form-control" id="element-content" value="${element.content || ''}">
            </div>
            <div class="form-group">
                <label>Heading Level</label>
                <select class="form-control" id="heading-level">
                    <option value="h1" ${(props.level || 'h2') === 'h1' ? 'selected' : ''}>H1 - Largest</option>
                    <option value="h2" ${(props.level || 'h2') === 'h2' ? 'selected' : ''}>H2 - Large</option>
                    <option value="h3" ${(props.level || 'h2') === 'h3' ? 'selected' : ''}>H3 - Medium</option>
                    <option value="h4" ${(props.level || 'h2') === 'h4' ? 'selected' : ''}>H4 - Small</option>
                </select>
            </div>
            <div class="form-group">
                <label>Alignment</label>
                <select class="form-control" id="element-alignment">
                    <option value="left" ${(props.alignment || 'left') === 'left' ? 'selected' : ''}>Left</option>
                    <option value="center" ${(props.alignment || 'left') === 'center' ? 'selected' : ''}>Center</option>
                    <option value="right" ${(props.alignment || 'left') === 'right' ? 'selected' : ''}>Right</option>
                </select>
            </div>
        `;
    },
    
    generateParagraphProperties(element) {
        const props = element.properties || {};
        return `
            <div class="form-group">
                <label>Content</label>
                <textarea class="form-control" id="element-content" rows="5">${element.content || ''}</textarea>
            </div>
            <div class="form-group">
                <label>Alignment</label>
                <select class="form-control" id="element-alignment">
                    <option value="left" ${(props.alignment || 'left') === 'left' ? 'selected' : ''}>Left</option>
                    <option value="center" ${(props.alignment || 'left') === 'center' ? 'selected' : ''}>Center</option>
                    <option value="right" ${(props.alignment || 'left') === 'right' ? 'selected' : ''}>Right</option>
                    <option value="justify" ${(props.alignment || 'left') === 'justify' ? 'selected' : ''}>Justify</option>
                </select>
            </div>
        `;
    },
    
    generateImageProperties(element) {
        const props = element.properties || {};
        return `
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" class="form-control" id="element-content" value="${element.content || ''}" placeholder="Enter image URL">
            </div>
            <div class="form-group">
                <label>Alt Text</label>
                <input type="text" class="form-control" id="image-alt" value="${props.alt_text || ''}" placeholder="Alternative text">
            </div>
        `;
    },
    
    generateTableProperties(element) {
        return `
            <div class="form-group">
                <label>Table Type</label>
                <select class="form-control" id="table-type">
                    <option value="data_table" ${element.content === 'data_table' ? 'selected' : ''}>Data Table</option>
                    <option value="custom_table" ${element.content === 'custom_table' ? 'selected' : ''}>Custom Table</option>
                    <option value="analysis_results" ${element.content === 'analysis_results' ? 'selected' : ''}>Analysis Results</option>
                </select>
            </div>
        `;
    },
    
    generateDataFieldProperties(element) {
        return `
            <div class="form-group">
                <label>Data Field Name</label>
                <input type="text" class="form-control" id="element-content" value="${element.content || ''}" placeholder="field_name">
            </div>
        `;
    },
    
    generateSignatureProperties(element) {
        return `
            <div class="form-group">
                <label>Signature Label</label>
                <input type="text" class="form-control" id="element-content" value="${element.content || 'Signature'}">
            </div>
        `;
    },
    
    generateDateProperties(element) {
        return `
            <div class="form-group">
                <label>Date Type</label>
                <select class="form-control" id="element-content">
                    <option value="current_date" ${element.content === 'current_date' ? 'selected' : ''}>Current Date</option>
                    <option value="analysis_date" ${element.content === 'analysis_date' ? 'selected' : ''}>Analysis Date</option>
                    <option value="report_date" ${element.content === 'report_date' ? 'selected' : ''}>Report Date</option>
                </select>
            </div>
        `;
    },
    
    saveElementProperties() {
        const elementId = $('#element-id').val();
        const content = $('#element-content').val();
        
        const properties = {};
        if ($('#heading-level').length) {
            properties.level = $('#heading-level').val();
        }
        if ($('#element-alignment').length) {
            properties.alignment = $('#element-alignment').val();
        }
        if ($('#image-alt').length) {
            properties.alt_text = $('#image-alt').val();
        }
        
        $.ajax({
            url: `/certificate-template-elements/${elementId}`,
            method: 'PUT',
            data: {
                content: content,
                properties: properties,
                _token: this.csrfToken
            },
            success: (response) => {
                this.showMessage(response.message, 'success');
                $('#element-modal').modal('hide');
                location.reload();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    deleteElement(elementId) {
        if (!confirm('Delete this element?')) return;
        
        $.ajax({
            url: `/certificate-template-elements/${elementId}`,
            method: 'DELETE',
            data: { _token: this.csrfToken },
            success: (response) => {
                this.showMessage(response.message, 'success');
                location.reload();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    saveElementPosition(elementId, target) {
        const x = parseFloat(target.dataset.x) || parseFloat(target.style.left) || 0;
        const y = parseFloat(target.dataset.y) || parseFloat(target.style.top) || 0;
        const width = parseFloat(target.style.width);
        const height = parseFloat(target.style.height);
        
        const canvasWidth = $(this.canvas).data('width');
        const canvasHeight = $(this.canvas).data('height');
        
        $.ajax({
            url: `/certificate-template-elements/${elementId}/position`,
            method: 'PUT',
            data: {
                position_x: x,
                position_y: y,
                width: width,
                height: height,
                position_x_percent: (x / canvasWidth) * 100,
                position_y_percent: (y / canvasHeight) * 100,
                width_percent: (width / canvasWidth) * 100,
                height_percent: (height / canvasHeight) * 100,
                _token: this.csrfToken
            },
            success: () => {
                this.showAutoSave();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    // Utility Functions
    showMessage(message, type) {
        toastr[type](message);
    },
    
    showAutoSave() {
        $('#auto-save-indicator').addClass('show');
        setTimeout(() => {
            $('#auto-save-indicator').removeClass('show');
        }, 2000);
    },
    
    handleError(xhr) {
        console.error('AJAX Error:', xhr);
        let message = 'An error occurred';
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            message = errors.join(', ');
        }
        
        toastr.error(message);
    }
};

// Initialize when document is ready
$(document).ready(function() {
    VisualBuilder.init();
    
    // Reinitialize interactions after dynamic content is added
    window.reinitializeInteractions = function() {
        VisualBuilder.initInteractions();
    };
});
</script>

