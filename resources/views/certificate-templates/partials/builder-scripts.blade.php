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
        // Holder panel expand/collapse (in sections panel)
        $(document).on('click', '.holder-panel-header', (e) => {
            // Don't trigger if clicking on action buttons
            if ($(e.target).closest('.holder-panel-actions, .btn').length) {
                return;
            }
            
            const $item = $(e.currentTarget).closest('.holder-panel-item');
            const $content = $item.find('.holder-panel-content');
            
            if ($item.hasClass('holder-collapsed')) {
                $item.removeClass('holder-collapsed');
                $content.slideDown(300);
            } else {
                $item.addClass('holder-collapsed');
                $content.slideUp(300);
            }
        });
        
        // Holder panel actions (from sections panel)
        $(document).on('click', '.btn-holder-edit-panel', (e) => {
            e.stopPropagation();
            const holderId = $(e.currentTarget).data('id');
            if (holderId) {
                this.showHolderModal(null, holderId);
            }
        });
        
        $(document).on('click', '.btn-holder-delete-panel', (e) => {
            e.stopPropagation();
            const holderId = $(e.currentTarget).data('id');
            if (holderId) {
                this.deleteHolder(holderId);
            }
        });
        
        $(document).on('click', '.btn-add-element-panel', (e) => {
            e.stopPropagation();
            const holderId = $(e.currentTarget).data('holder-id');
            if (holderId) {
                this.showAddElementModal(holderId);
            }
        });
        
        $(document).on('click', '.btn-add-nested-holder-panel', (e) => {
            e.stopPropagation();
            const holderId = $(e.currentTarget).data('holder-id');
            if (holderId) {
                this.showHolderModal(null, null, holderId);
            }
        });
        
        // Element panel actions (from sections panel)
        $(document).on('click', '.btn-element-edit-panel', (e) => {
            e.stopPropagation();
            const elementId = $(e.currentTarget).data('id');
            if (elementId) {
                this.showElementModal(elementId);
            }
        });
        
        $(document).on('click', '.btn-element-delete-panel', (e) => {
            e.stopPropagation();
            const elementId = $(e.currentTarget).data('id');
            if (elementId) {
                this.deleteElement(elementId);
            }
        });
        
        // Canvas holder toolbar actions (only edit and direction toggle)
        $(document).on('click', '.btn-holder-edit', (e) => {
            e.stopPropagation();
            e.preventDefault();
            const holderId = $(e.currentTarget).data('holder') || $(e.currentTarget).closest('[data-holder-id]').data('holder-id');
            if (holderId) {
                this.showHolderModal(null, holderId);
            }
        });
        $(document).on('click', '.btn-toggle-direction', (e) => {
            e.stopPropagation();
            const holderId = $(e.currentTarget).data('holder');
            if (holderId) {
                this.toggleHolderDirection(holderId);
            }
        });
        $(document).on('click', '.btn-add-holder-section', (e) => {
            e.stopPropagation();
            const sectionId = $(e.currentTarget).data('section');
            if (sectionId) {
                this.showHolderModal(sectionId);
            }
        });
        
        // Section selection (canvas and panel sync)
        $(document).on('click', '.canvas-section', (e) => {
            if (!$(e.target).closest('.canvas-element, .resize-handle').length) {
                const sectionId = $(e.currentTarget).data('section-id');
                this.selectSection(sectionId);
            }
        });
        
        // Section selection from panel
        $(document).on('click', '.section-panel-item', (e) => {
            if (!$(e.target).closest('.section-panel-actions, .btn').length) {
                const sectionId = $(e.currentTarget).data('section-id');
                this.selectSection(sectionId);
            }
        });
        
        // Section vertical resize
        if (typeof interact !== 'undefined') {
            interact('.section-resize-handle')
                .resizable({
                    edges: { bottom: true },
                    listeners: {
                        move(event) {
                            const $section = $(event.target).closest('.canvas-section');
                            const newHeight = event.rect.height;
                            $section.css('height', newHeight + 'px');
                            
                            // Auto-save section height
                            const sectionId = $section.data('section-id');
                            if (sectionId) {
                                $.ajax({
                                    url: `/certificate-template-sections/${sectionId}/resize`,
                                    method: 'POST',
                                    data: { 
                                        height: newHeight,
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                            }
                        }
                    },
                    modifiers: [
                        interact.modifiers.restrictSize({
                            min: { height: 100 }
                        })
                    ]
                });
        }
        
        // Element management - removed canvas-based element creation
        // Elements are now created from the sections panel only
        
        $(document).on('click', '.btn-element-edit, [data-element]', (e) => {
            e.stopPropagation();
            const elementId = $(e.currentTarget).data('element') || $(e.currentTarget).data('id');
            if (elementId) {
                this.showElementModal(elementId);
            }
        });
        
        // Element delete from canvas (if still exists) - but prefer panel
        $(document).on('click', '.btn-element-delete', (e) => {
            e.stopPropagation();
            const elementId = $(e.currentTarget).data('element') || $(e.currentTarget).data('id');
            if (elementId) {
                this.deleteElement(elementId);
            }
        });
        
        // Element hide/show toggle
        $(document).on('click', '.btn-element-hide', (e) => {
            e.stopPropagation();
            e.preventDefault();
            const elementId = $(e.currentTarget).data('element');
            if (elementId) {
                this.toggleElementVisibility(elementId);
            }
        });
        
        $('#save-element-properties').on('click', () => this.saveElementProperties());
        
        // Add element modal handlers
        $('#add-element-type').on('change', (e) => {
            const elementType = $(e.target).val();
            if (elementType) {
                $('#add-element-extra-fields').show();
                if (['heading', 'paragraph', 'text'].includes(elementType)) {
                    $('#add-element-content-field').show();
                } else {
                    $('#add-element-content-field').hide();
                }
            } else {
                $('#add-element-extra-fields').hide();
            }
        });
        
        $('#save-add-element').on('click', () => {
            const holderId = $('#add-element-holder-id').val();
            const elementType = $('#add-element-type').val();
            const content = $('#add-element-content').val();
            
            if (!elementType) {
                this.showMessage('Please select an element type', 'warning');
                return;
            }
            
            if (!holderId) {
                this.showMessage('Holder ID is missing', 'error');
                return;
            }
            
            this.addElementToHolder(holderId, elementType, null, null, content);
        });
        
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
            this.showMessage(this.snapEnabled ? 'Snap enabled' : 'Snap disabled', 'info');
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
        // Initialize element positions from stored values
        $('.canvas-element').each((index, element) => {
            const $el = $(element);
            const left = parseFloat($el.css('left')) || 0;
            const top = parseFloat($el.css('top')) || 0;
            
            // Set initial dataset values for interact.js
            element.dataset.x = left;
            element.dataset.y = top;
            
            // Reset transform so absolute positioning takes precedence
            $el.css('transform', 'none');
        });
        
        // Initialize holder positions from stored values
        $('.element-holder-container, .canvas-holder-root').each((index, holder) => {
            const $holder = $(holder);
            const left = parseFloat($holder.css('left')) || 0;
            const top = parseFloat($holder.css('top')) || 0;
            
            holder.dataset.x = left;
            holder.dataset.y = top;
        });
        
        // Initialize Interact.js for element holders
        const holderSelector = '.element-holder-container, .canvas-holder-root';
        interact(holderSelector)
            .draggable({
                ignoreFrom: '.resize-handle, .canvas-element',
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
                        restriction: (element) => {
                            // Ensure we have a DOM element
                            const el = element && element.nodeType ? element : (element && element.target ? element.target : this.canvas);
                            if (!el || typeof el.closest !== 'function') {
                                const canvas = document.getElementById('designer-canvas');
                                if (canvas) {
                                    return {
                                        left: 0,
                                        top: 0,
                                        right: canvas.offsetWidth,
                                        bottom: canvas.offsetHeight
                                    };
                                }
                                return null;
                            }
                            
                            const parent = el.closest('.canvas-section, .designer-canvas, .holder-content');
                            if (parent) {
                                const padding = 10; // Add padding to prevent overflow
                                return {
                                    left: padding,
                                    top: padding,
                                    right: parent.offsetWidth - padding,
                                    bottom: parent.offsetHeight - padding
                                };
                            }
                            // Fallback to canvas bounds
                            const canvas = document.getElementById('designer-canvas');
                            if (canvas) {
                                return {
                                    left: 0,
                                    top: 0,
                                    right: canvas.offsetWidth,
                                    bottom: canvas.offsetHeight
                                };
                            }
                            return null;
                        },
                        endOnly: true
                    })
                ],
                listeners: {
                    start: (event) => {
                        event.target.style.zIndex = 1000;
                    },
                    move: this.dragMoveListener.bind(this),
                    end: (event) => {
                        const holderId = $(event.target).data('holder-id');
                        if (holderId) {
                            this.saveHolderPosition(holderId, event.target);
                        }
                        event.target.style.zIndex = '';
                    }
                }
            })
            .resizable({
                edges: { 
                    left: true,
                    right: true,
                    top: true,
                    bottom: true
                },
                modifiers: [
                    interact.modifiers.restrictSize({
                        min: { width: 150, height: 100 }
                    }),
                    interact.modifiers.restrictEdges({
                        outer: 'parent'
                    })
                ],
                inertia: true,
                listeners: {
                    start: (event) => {
                        const target = event.target;
                        // Ensure initial position is stored
                        if (!target.dataset.x) {
                            target.dataset.x = parseFloat(target.style.left) || 0;
                            target.dataset.y = parseFloat(target.style.top) || 0;
                        }
                    },
                    move: (event) => {
                        const target = event.target;
                        let x = parseFloat(target.dataset.x) || parseFloat(target.style.left) || 0;
                        let y = parseFloat(target.dataset.y) || parseFloat(target.style.top) || 0;
                        
                        x += event.deltaRect.left;
                        y += event.deltaRect.top;
                        
                        Object.assign(target.style, {
                            width: `${event.rect.width}px`,
                            height: `${event.rect.height}px`,
                            left: `${x}px`,
                            top: `${y}px`,
                            transform: 'none'
                        });
                        
                        Object.assign(target.dataset, { 
                            x, 
                            y,
                            width: event.rect.width,
                            height: event.rect.height
                        });
                    },
                    end: (event) => {
                        const holderId = $(event.target).data('holder-id');
                        if (holderId) {
                            this.saveHolderPosition(holderId, event.target);
                        }
                    }
                }
            });
        
        // Initialize Interact.js for canvas elements
        interact('.canvas-element')
            .draggable({
                allowFrom: '.element-content',
                ignoreFrom: '.resize-handle, .element-toolbar',
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
                        restriction: (element) => {
                            // Ensure we have a DOM element
                            const el = element && element.nodeType ? element : (element && element.target ? element.target : this.canvas);
                            if (!el || typeof el.closest !== 'function') {
                                const canvas = document.getElementById('designer-canvas');
                                if (canvas) {
                                    return {
                                        left: 0,
                                        top: 0,
                                        right: canvas.offsetWidth,
                                        bottom: canvas.offsetHeight
                                    };
                                }
                                return null;
                            }
                            
                            const parent = el.closest('.holder-content, .canvas-section, .canvas-holder');
                            if (parent) {
                                const padding = 10; // Add padding to prevent overflow
                                return {
                                    left: padding,
                                    top: padding,
                                    right: parent.offsetWidth - padding,
                                    bottom: parent.offsetHeight - padding
                                };
                            }
                            // Fallback to canvas bounds
                            const canvas = document.getElementById('designer-canvas');
                            if (canvas) {
                                return {
                                    left: 0,
                                    top: 0,
                                    right: canvas.offsetWidth,
                                    bottom: canvas.offsetHeight
                                };
                            }
                            return null;
                        },
                        endOnly: true
                    })
                ],
                listeners: {
                    start: (event) => {
                        event.target.style.zIndex = 1000;
                    },
                    move: this.dragMoveListener.bind(this),
                    end: (event) => {
                        const elementId = $(event.target).data('element-id');
                        if (elementId) {
                            this.saveElementPosition(elementId, event.target);
                        }
                        event.target.style.zIndex = '';
                    }
                }
            })
            .resizable({
                edges: { 
                    left: true,
                    right: true,
                    top: true,
                    bottom: true
                },
                modifiers: [
                    interact.modifiers.restrictSize({
                        min: { width: 50, height: 30 }
                    }),
                    interact.modifiers.restrictEdges({
                        outer: 'parent'
                    })
                ],
                inertia: true,
                listeners: {
                    start: (event) => {
                        const target = event.target;
                        if (!target.dataset.x) {
                            target.dataset.x = parseFloat(target.style.left) || 0;
                            target.dataset.y = parseFloat(target.style.top) || 0;
                        }
                    },
                    move: (event) => {
                        const target = event.target;
                        let x = parseFloat(target.dataset.x) || parseFloat(target.style.left) || 0;
                        let y = parseFloat(target.dataset.y) || parseFloat(target.style.top) || 0;
                        
                        x += event.deltaRect.left;
                        y += event.deltaRect.top;
                        
                        Object.assign(target.style, {
                            width: `${event.rect.width}px`,
                            height: `${event.rect.height}px`,
                            left: `${x}px`,
                            top: `${y}px`,
                            transform: 'none'
                        });
                        
                        Object.assign(target.dataset, { 
                            x, 
                            y,
                            width: event.rect.width,
                            height: event.rect.height
                        });
                    },
                    end: (event) => {
                        const elementId = $(event.target).data('element-id');
                        if (elementId) {
                            this.saveElementPosition(elementId, event.target);
                        }
                    }
                }
            });
        
        // Initialize Interact.js for section resizing
        interact('.canvas-section')
            .resizable({
                edges: { 
                    left: false,
                    right: true,
                    top: false,
                    bottom: true
                },
                modifiers: [
                    interact.modifiers.restrictSize({
                        min: { width: 300, height: 200 }
                    }),
                    interact.modifiers.restrictEdges({
                        outer: function(element) {
                            const canvas = document.getElementById('designer-canvas');
                            if (canvas) {
                                return {
                                    left: 0,
                                    top: 0,
                                    right: canvas.offsetWidth,
                                    bottom: canvas.offsetHeight
                                };
                            }
                            return null;
                        }
                    })
                ],
                inertia: true,
                listeners: {
                    start: (event) => {
                        const target = event.target;
                        if (!target.dataset.width) {
                            target.dataset.width = parseFloat(target.style.width) || target.offsetWidth;
                            target.dataset.height = parseFloat(target.style.height) || target.offsetHeight;
                        }
                    },
                    move: (event) => {
                        const target = event.target;
                        Object.assign(target.style, {
                            width: `${event.rect.width}px`,
                            height: `${event.rect.height}px`
                        });
                        Object.assign(target.dataset, { 
                            width: event.rect.width,
                            height: event.rect.height
                        });
                    },
                    end: (event) => {
                        const sectionId = $(event.target).data('section-id');
                        if (sectionId) {
                            // Save section size
                            this.saveSectionSize(sectionId, event.target);
                        }
                    }
                }
            });
    },
    
    saveSectionSize(sectionId, target) {
        const width = parseFloat(target.style.width) || parseFloat(target.dataset.width);
        const height = parseFloat(target.style.height) || parseFloat(target.dataset.height);
        
        $.ajax({
            url: `/certificate-template-sections/${sectionId}`,
            method: 'PUT',
            data: {
                width: width,
                height: height,
                _token: this.csrfToken
            },
            success: () => {
                this.showAutoSave();
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    dragMoveListener(event) {
        const target = event.target;
        let x = parseFloat(target.dataset.x) || 0;
        let y = parseFloat(target.dataset.y) || 0;
        
        x += event.dx;
        y += event.dy;
        
        // Use left/top instead of transform for absolute positioning
        target.style.left = `${x}px`;
        target.style.top = `${y}px`;
        target.style.transform = 'none';
        
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
                    this.showMessage('Failed to load section data', 'error');
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
    
    selectSection(sectionId) {
        // Update canvas highlighting
        $('.canvas-section').removeClass('active');
        $(`.canvas-section[data-section-id="${sectionId}"]`).addClass('active');
        
        // Update panel highlighting
        $('.section-panel-item').removeClass('section-panel-active');
        $(`.section-panel-item[data-section-id="${sectionId}"]`).addClass('section-panel-active');
        
        // Scroll canvas to section
        const $section = $(`.canvas-section[data-section-id="${sectionId}"]`);
        if ($section.length) {
            $('#canvas-container').animate({
                scrollTop: $section.position().top
            }, 300);
        }
    },
    
    // Holder Management
    showHolderModal(sectionId = null, holderId = null, parentHolderId = null) {
        $('#holder-form')[0].reset();
        $('#holder-id').val('');
        $('#holder-section-id').val(sectionId || '');
        $('#holder-parent-id').val(parentHolderId || '');
        $('#holder-max-elements').val(10);
        $('#data-source').val('');
        $('#field-mappings-list').empty();
        $('#field-mappings-container').hide();
        
        // Toggle data source and company fields visibility based on holder type
        $('#holder-type').off('change').on('change', function() {
            const holderType = $(this).val();
            if (holderType === 'company_header') {
                $('#data-source-section').show();
                $('#max-elements-field').hide();
            } else {
                $('#data-source-section').hide();
                $('#company-information-fields').hide();
                $('#max-elements-field').show();
            }
        });
        
        // Load fields when data source changes
        $('#data-source').off('change').on('change', (e) => {
            const dataSource = $(e.target).val();
            if (dataSource) {
                this.loadDataSourceFields(dataSource);
                $('#field-mappings-container').show();
                $('#company-information-fields').hide(); // Hide legacy fields when using data source
            } else {
                $('#field-mappings-container').hide();
                $('#field-mappings-list').empty();
            }
        });
        
        if (holderId) {
            $.get(`/certificate-template-holders/${holderId}`)
                .done((response) => {
                    if (response.success) {
                        const holder = response.holder;
                        $('#holder-id').val(holderId);
                        $('#holder-section-id').val(holder.certificate_template_section_id);
                        $('#holder-type').val(holder.holder_type);
                        $('#holder-max-elements').val(holder.max_elements || 10);
                        
                        // Populate direction
                        $('#holder-direction').val(holder.direction || 'horizontal');
                        
                        // Populate parent holder ID if nested
                        if (holder.parent_holder_id) {
                            $('#holder-parent-id').val(holder.parent_holder_id);
                        }
                        
                        // Populate data source if exists
                        if (holder.data_source) {
                            $('#data-source').val(holder.data_source);
                            this.loadDataSourceFields(holder.data_source, holder.field_mappings || {});
                            $('#field-mappings-container').show();
                            $('#company-information-fields').hide();
                        } else if (holder.holder_type === 'company_header') {
                            // Legacy: show company information fields
                            $('#company-name').val(holder.company_name || '');
                            $('#company-email').val(holder.company_email || '');
                            $('#company-website').val(holder.company_website || '');
                            $('#company-phone').val(holder.company_phone || '');
                            $('#company-logo').val(holder.company_logo || '');
                            $('#form-number').val(holder.form_number || '');
                            $('#publish-date').val(holder.publish_date || '');
                            $('#qa-other-details').val(holder.qa_other_details || '');
                            $('#company-information-fields').show();
                        }
                        
                        // Show/hide sections based on holder type
                        if (holder.holder_type === 'company_header') {
                            $('#data-source-section').show();
                            $('#max-elements-field').hide();
                        } else {
                            $('#data-source-section').hide();
                            $('#company-information-fields').hide();
                            $('#max-elements-field').show();
                        }
                    }
                })
                .fail(() => this.showMessage('Failed to load holder data', 'error'));
        } else {
            // For new holders, hide fields by default
            $('#company-information-fields').hide();
            $('#data-source-section').hide();
            $('#max-elements-field').show();
            
            // If creating nested holder, we need to get section ID from parent holder
            if (parentHolderId) {
                // Fetch parent holder to get section ID
                $.get(`/certificate-template-holders/${parentHolderId}`)
                    .done((response) => {
                        if (response.success && response.holder) {
                            $('#holder-section-id').val(response.holder.certificate_template_section_id);
                        }
                    })
                    .fail(() => {
                        console.warn('Could not load parent holder data');
                    });
            }
        }
        
        $('#holder-modal').modal('show');
    },
    
    saveHolder() {
        const holderId = $('#holder-id').val();
        const sectionId = $('#holder-section-id').val();
        const isEdit = holderId !== '';
        const holderType = $('#holder-type').val();
        
        // Calculate position and size for new holders
        let positionX = 10;
        let positionY = 10;
        let width = 300;
        let height = 200;
        
        const parentHolderId = $('#holder-parent-id').val();
        if (!isEdit) {
            if (parentHolderId) {
                // Nested holder - make it fit within parent
                const $parent = $(`.canvas-holder[data-holder-id="${parentHolderId}"]`);
                if ($parent.length) {
                    const parentContent = $parent.find('.holder-content');
                    if (parentContent.length) {
                        width = parentContent.width() - 20;
                        height = 150;
                    }
                }
            } else {
                // Root holder - make it fill section width
                const $section = $(`.canvas-section[data-section-id="${sectionId}"]`);
                if ($section.length) {
                    const sectionContent = $section.find('.section-content');
                    if (sectionContent.length) {
                        width = sectionContent.width() - 40; // Account for padding
                        positionX = 10;
                        positionY = 10;
                        height = 200;
                    }
                }
            }
        }
        
        const data = {
            holder_type: holderType,
            direction: $('#holder-direction').val() || 'horizontal',
            parent_holder_id: parentHolderId || null,
            max_elements: $('#holder-max-elements').val() || 0,
            position_x: positionX,
            position_y: positionY,
            width: width,
            height: height,
            _token: this.csrfToken,
            canvas_width: $(this.canvas).data('width'),
            canvas_height: $(this.canvas).data('height')
        };
        
        // Add data source and field mappings if set
        const dataSource = $('#data-source').val();
        if (dataSource) {
            data.data_source = dataSource;
            // Collect selected field mappings
            const fieldMappings = {};
            $('#field-mappings-list input[type="checkbox"]:checked').each(function() {
                const fieldName = $(this).val();
                fieldMappings[fieldName] = fieldName;
            });
            data.field_mappings = fieldMappings;
        } else if (holderType === 'company_header') {
            // Legacy: add company information if data source is not set
            data.company_name = $('#company-name').val() || '';
            data.company_email = $('#company-email').val() || '';
            data.company_website = $('#company-website').val() || '';
            data.company_phone = $('#company-phone').val() || '';
            data.company_logo = $('#company-logo').val() || '';
            data.form_number = $('#form-number').val() || '';
            data.publish_date = $('#publish-date').val() || '';
            data.qa_other_details = $('#qa-other-details').val() || '';
        }
        
        // Determine URL based on whether it's edit, nested, or root holder
        let url;
        const method = isEdit ? 'PUT' : 'POST';
        
        if (isEdit) {
            url = `/certificate-template-holders/${holderId}`;
        } else if (parentHolderId) {
            // New nested holder - use nested holder route
            url = `/certificate-template-holders/${parentHolderId}/nested-holders`;
        } else {
            // New root holder - use section route
            if (!sectionId) {
                this.showMessage('Section ID is required for root holders', 'error');
                return;
            }
            url = `/certificate-template-sections/${sectionId}/holders`;
        }
        
        $.ajax({
            url: url,
            method: method,
            data: data,
            success: (response) => {
                this.showMessage(response.message, 'success');
                $('#holder-modal').modal('hide');
                this.updateSectionsPanel();
                setTimeout(() => location.reload(), 300);
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    updateSectionsPanel() {
        // Sections panel will update on page reload
        // This method is a placeholder for potential AJAX updates in the future
    },
    
    loadDataSourceFields(dataSource, selectedMappings = {}) {
        if (!dataSource) {
            $('#field-mappings-list').empty();
            return;
        }
        
        $.ajax({
            url: '/certificate-template-holders/data-source/fields',
            method: 'GET',
            data: { data_source: dataSource },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: (response) => {
                if (response.success && response.fields) {
                    const fieldsList = $('#field-mappings-list');
                    fieldsList.empty();
                    
                    $.each(response.fields, (fieldName, fieldLabel) => {
                        const isChecked = selectedMappings && selectedMappings[fieldName] ? 'checked' : '';
                        const checkbox = $(`
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="${fieldName}" id="field-${fieldName}" ${isChecked}>
                                <label class="form-check-label" for="field-${fieldName}">
                                    ${fieldLabel}
                                </label>
                            </div>
                        `);
                        fieldsList.append(checkbox);
                    });
                } else {
                    console.error('Invalid response format:', response);
                    this.showMessage('Invalid response from server', 'error');
                }
            },
            error: (xhr, status, error) => {
                console.error('Error loading fields:', { xhr, status, error, response: xhr.responseJSON });
                let errorMessage = 'Failed to load fields for data source';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage += ': Route not found';
                } else if (xhr.status === 422) {
                    errorMessage += ': Validation error';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        errorMessage += ' - ' + errors;
                    }
                }
                this.showMessage(errorMessage, 'error');
            }
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
        // Get position from dataset (updated by interact.js) or inline styles
        const x = parseFloat(target.dataset.x) || parseFloat($(target).css('left')) || 0;
        const y = parseFloat(target.dataset.y) || parseFloat($(target).css('top')) || 0;
        const width = parseFloat($(target).css('width')) || parseFloat(target.style.width);
        const height = parseFloat($(target).css('height')) || parseFloat(target.style.height);
        
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
    showAddElementModal(holderId) {
        // Store holder ID for later use
        $('#element-holder-id').val(holderId);
        
        // Reset form
        $('#add-element-form')[0]?.reset();
        $('#add-element-holder-id').val(holderId);
        
        // Show modal - similar to submission builder
        $('#add-element-modal').modal('show');
    },
    
    addElementToHolder(holderId, elementType = null, x = null, y = null, content = null) {
        // Use provided elementType or selectedElementType
        const selectedType = elementType || this.selectedElementType;
        
        if (!selectedType) {
            // If no type provided, show element selection
            this.showAddElementModal(holderId);
            return;
        }
        
        // Calculate element size based on holder
        let elementWidth = 200;
        let elementHeight = 100;
        
        const $holder = $(`.canvas-holder[data-holder-id="${holderId}"], .element-holder-container[data-holder-id="${holderId}"]`);
        if ($holder.length) {
            const holderContent = $holder.find('.holder-content');
            if (holderContent.length) {
                const holderDirection = $holder.data('direction') || 'horizontal';
                const existingElements = $holder.find('.canvas-element').length;
                
                if (holderDirection === 'horizontal') {
                    // In horizontal layout, divide available space
                    elementWidth = Math.max(100, (holderContent.width() - 20 - (existingElements * 8)) / (existingElements + 1));
                } else {
                    // In vertical layout, elements should take full width
                    elementWidth = Math.max(100, holderContent.width() - 20);
                }
            }
        }
        
        // Prepare data
        const data = {
            element_type: selectedType,
            position_x: x || 10,
            position_y: y || 10,
            width: elementWidth,
            height: elementHeight,
            _token: this.csrfToken
        };
        
        // Add content if provided
        if (content !== null && content !== '') {
            data.content = content;
        }
        
        $.ajax({
            url: `/certificate-template-holders/${holderId}/elements`,
            method: 'POST',
            data: data,
            success: (response) => {
                this.showMessage(response.message, 'success');
                this.selectedElementType = null;
                $('.element-type-btn').removeClass('active');
                $('#add-element-modal').modal('hide');
                // Update sections panel and reload canvas
                this.updateSectionsPanel();
                setTimeout(() => location.reload(), 300);
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    toggleHolderDirection(holderId) {
        $.ajax({
            url: `/certificate-template-holders/${holderId}/toggle-direction`,
            method: 'POST',
            data: {
                _token: this.csrfToken
            },
            success: (response) => {
                this.showMessage(response.message || 'Direction toggled', 'success');
                this.updateSectionsPanel();
                setTimeout(() => location.reload(), 300);
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
            .fail(() => this.showMessage('Failed to load element', 'error'));
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
            case 'link':
                html += this.generateLinkProperties(element);
                break;
            case 'checkbox':
                html += this.generateCheckboxProperties(element);
                break;
            case 'radio':
                html += this.generateRadioProperties(element);
                break;
            case 'blockquote':
                html += this.generateBlockquoteProperties(element);
                break;
            case 'code_block':
                html += this.generateCodeBlockProperties(element);
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

    generateLinkProperties(element) {
        const props = element.properties || {};
        return `
            <div class="form-group">
                <label>Link Text</label>
                <input type="text" class="form-control" id="element-content" value="${element.content || 'Link Text'}">
            </div>
            <div class="form-group">
                <label>URL</label>
                <input type="url" class="form-control" id="link-url" value="${props.url || '#'}" placeholder="https://example.com">
            </div>
            <div class="form-group">
                <label>Target</label>
                <select class="form-control" id="link-target">
                    <option value="_blank" ${(props.target || '_blank') === '_blank' ? 'selected' : ''}>New Window (_blank)</option>
                    <option value="_self" ${(props.target || '_blank') === '_self' ? 'selected' : ''}>Same Window (_self)</option>
                </select>
            </div>
        `;
    },

    generateCheckboxProperties(element) {
        const props = element.properties || {};
        return `
            <div class="form-group">
                <label>Label</label>
                <input type="text" class="form-control" id="element-content" value="${element.content || 'Checkbox'}">
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="checkbox-checked" ${(props.checked) ? 'checked' : ''}>
                <label class="form-check-label" for="checkbox-checked">Checked by default</label>
            </div>
        `;
    },

    generateRadioProperties(element) {
        const props = element.properties || {};
        return `
            <div class="form-group">
                <label>Label</label>
                <input type="text" class="form-control" id="element-content" value="${element.content || 'Radio Option'}">
            </div>
            <div class="form-group">
                <label>Group Name</label>
                <input type="text" class="form-control" id="radio-group" value="${props.group || 'default_group'}">
                <small class="text-muted">Radio buttons with the same group name work together.</small>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="radio-checked" ${(props.checked) ? 'checked' : ''}>
                <label class="form-check-label" for="radio-checked">Selected by default</label>
            </div>
        `;
    },

    generateBlockquoteProperties(element) {
        const props = element.properties || {};
        return `
            <div class="form-group">
                <label>Quote Content</label>
                <textarea class="form-control" id="element-content" rows="3">${element.content || ''}</textarea>
            </div>
            <div class="form-group">
                <label>Border Color</label>
                <input type="color" class="form-control" id="blockquote-border-color" value="${props.border_left_color || '#e5e7eb'}">
            </div>
        `;
    },

    generateCodeBlockProperties(element) {
        const props = element.properties || {};
        return `
            <div class="form-group">
                <label>Code Content</label>
                <textarea class="form-control" id="element-content" rows="5" style="font-family: monospace;">${element.content || ''}</textarea>
            </div>
            <div class="form-group">
                <label>Language</label>
                <input type="text" class="form-control" id="code-language" value="${props.language || 'text'}" placeholder="javascript, python, html...">
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
        if ($('#link-url').length) {
            properties.url = $('#link-url').val();
            properties.target = $('#link-target').val();
        }
        if ($('#checkbox-checked').length) {
            properties.checked = $('#checkbox-checked').is(':checked');
        }
        if ($('#radio-checked').length) {
            properties.checked = $('#radio-checked').is(':checked');
            properties.group = $('#radio-group').val();
        }
        if ($('#blockquote-border-color').length) {
            properties.border_left_color = $('#blockquote-border-color').val();
        }
        if ($('#code-language').length) {
            properties.language = $('#code-language').val();
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
    
    toggleElementVisibility(elementId) {
        const $element = $(`.canvas-element[data-element-id="${elementId}"]`);
        const isCurrentlyHidden = $element.data('hidden') === '1' || $element.hasClass('element-hidden');
        const newHiddenState = !isCurrentlyHidden;
        
        // Get current properties
        $.ajax({
            url: `/certificate-template-elements/${elementId}`,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    const element = response.element;
                    const properties = element.properties || {};
                    properties.hidden = newHiddenState;
                    
                    // Update element visibility via API
                    $.ajax({
                        url: `/certificate-template-elements/${elementId}`,
                        method: 'PUT',
                        data: {
                            properties: properties,
                            _token: this.csrfToken
                        },
                        success: (updateResponse) => {
                            // Update UI immediately
                            if (newHiddenState) {
                                $element.addClass('element-hidden');
                                $element.data('hidden', '1');
                                $element.find('.btn-element-hide i').removeClass('mdi-eye-off').addClass('mdi-eye');
                                this.showMessage('Element hidden', 'success');
                            } else {
                                $element.removeClass('element-hidden');
                                $element.data('hidden', '0');
                                $element.find('.btn-element-hide i').removeClass('mdi-eye').addClass('mdi-eye-off');
                                this.showMessage('Element shown', 'success');
                            }
                        },
                        error: (xhr) => this.handleError(xhr)
                    });
                }
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    saveElementPosition(elementId, target) {
        // Get position from dataset (updated by interact.js) or inline styles
        const x = parseFloat(target.dataset.x) || parseFloat($(target).css('left')) || 0;
        const y = parseFloat(target.dataset.y) || parseFloat($(target).css('top')) || 0;
        const width = parseFloat($(target).css('width')) || parseFloat(target.style.width);
        const height = parseFloat($(target).css('height')) || parseFloat(target.style.height);
        
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
        // Use toastr if available, otherwise use console and alert
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
            if (type === 'error') {
                alert(message);
            }
        }
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
        
        this.showMessage(message, 'error');
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
    
    toggleElementVisibility(elementId) {
        const $element = $(`.canvas-element[data-element-id="${elementId}"]`);
        const isCurrentlyHidden = $element.data('hidden') === '1' || $element.hasClass('element-hidden');
        const newHiddenState = !isCurrentlyHidden;
        
        // Get current properties
        $.ajax({
            url: `/certificate-template-elements/${elementId}`,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    const element = response.element;
                    const properties = element.properties || {};
                    properties.hidden = newHiddenState;
                    
                    // Update element visibility via API
                    $.ajax({
                        url: `/certificate-template-elements/${elementId}`,
                        method: 'PUT',
                        data: {
                            properties: properties,
                            _token: this.csrfToken
                        },
                        success: (updateResponse) => {
                            // Update UI immediately
                            if (newHiddenState) {
                                $element.addClass('element-hidden');
                                $element.data('hidden', '1');
                                $element.find('.btn-element-hide i').removeClass('mdi-eye-off').addClass('mdi-eye');
                                this.showMessage('Element hidden', 'success');
                            } else {
                                $element.removeClass('element-hidden');
                                $element.data('hidden', '0');
                                $element.find('.btn-element-hide i').removeClass('mdi-eye').addClass('mdi-eye-off');
                                this.showMessage('Element shown', 'success');
                            }
                        },
                        error: (xhr) => this.handleError(xhr)
                    });
                }
            },
            error: (xhr) => this.handleError(xhr)
        });
    },
    
    saveElementPosition(elementId, target) {
        // Get position from dataset (updated by interact.js) or inline styles
        const x = parseFloat(target.dataset.x) || parseFloat($(target).css('left')) || 0;
        const y = parseFloat(target.dataset.y) || parseFloat($(target).css('top')) || 0;
        const width = parseFloat($(target).css('width')) || parseFloat(target.style.width);
        const height = parseFloat($(target).css('height')) || parseFloat(target.style.height);
        
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
        // Use toastr if available, otherwise use console and alert
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
            if (type === 'error') {
                alert(message);
            }
        }
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
        
        this.showMessage(message, 'error');
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

