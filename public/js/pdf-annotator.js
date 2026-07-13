/**
 * PDF Annotator Class
 * Handles PDF rendering, annotation drawing, and saving annotated PDFs
 */
class PDFAnnotator {
    constructor(config) {
        this.pdfUrl = config.pdfUrl;
        this.attachmentId = config.attachmentId;
        this.canvas = document.getElementById(config.canvasId);
        this.annotationCanvas = document.getElementById(config.annotationCanvasId);
        this.getAnnotationsUrl = config.getAnnotationsUrl;

        this.ctx = this.canvas.getContext('2d');
        this.annotationCtx = this.annotationCanvas.getContext('2d');

        this.pdfDoc = null;
        this.currentPage = 1;
        this.totalPages = 0;
        this.currentTool = null;
        this.annotations = {}; // Organized by page number
        this.selectedAnnotations = new Set(); // Track selected annotation IDs
        this.annotationIdCounter = 0; // For generating unique IDs for new annotations
        this.editingAnnotation = null; // Currently editing annotation
        this.pendingRepositionAnnotation = null; // Annotation being repositioned
        this.scale = 1.5;
        this.resizeObserver = null;
        this.resizeDebounceTimer = null;
        this.isDrawing = false;
        this.tempAnnotation = null;
        this.deleteAnnotationsUrl = config.deleteAnnotationsUrl || null;
        this.annotationsBakedIntoPdf = false; // Flag to track if annotations are already in PDF
        this.textAnnotationCallback = null; // Callback for text annotation modal
        this.pendingTextAnnotation = null; // Store pending annotation data while modal is open
        this.initialized = false; // Track if already initialized
        this.eventListenersSetup = false; // Track if event listeners are set up
        this.annotationDivs = new Map(); // Track HTML overlay divs for annotations
        this.captureMode = false; // True when capturing for save (hide UI chrome)
    }

    async init() {
        // Prevent multiple initializations
        if (this.initialized) {
            console.warn('PDFAnnotator already initialized, skipping...');
            return;
        }

        const loadingEl = document.getElementById('pdf-loading');

        try {
            if (!this.pdfUrl) {
                throw new Error('PDF URL is missing for this attachment.');
            }

            // Load PDF
            const loadingTask = pdfjsLib.getDocument({
                url: this.pdfUrl,
                withCredentials: true,
            });
            this.pdfDoc = await loadingTask.promise;
            this.totalPages = this.pdfDoc.numPages;

            document.getElementById('pdf-page-count').textContent = this.totalPages;
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }

            // Clear annotations before loading to ensure clean state
            this.annotations = {};
            this.selectedAnnotations.clear();
            this.editingAnnotation = null;

            // Check if we're coming from a successful save (annotations are baked into PDF)
            // Check URL parameters or session storage for save success
            const urlParams = new URLSearchParams(window.location.search);
            const hasSuccessMessage = document.querySelector('.alert-success') !== null;

            // Load existing annotations from database first
            await this.loadExistingAnnotations();

            // Only mark as baked if we just successfully saved.
            // Otherwise, render overlays even if annotations exist in DB.
            if (hasSuccessMessage) {
                this.annotationsBakedIntoPdf = true;
            } else {
                this.annotationsBakedIntoPdf = false;
            }

            // Render first page
            await this.renderPage(1);

            // Re-fit once the lab sidebar and annotation panel have finished layout
            requestAnimationFrame(() => {
                if (this.pdfDoc) {
                    this.renderPage(this.currentPage);
                }
            });

            // Setup event listeners (only once)
            if (!this.eventListenersSetup) {
                this.setupEventListeners();
                this.eventListenersSetup = true;
            }

            // Initialize delete and edit button states
            this.updateDeleteButtonState();
            this.updateEditButtonState();

            // Mark as initialized
            this.initialized = true;

        } catch (error) {
            console.error('Error loading PDF:', error);
            if (loadingEl) {
                loadingEl.innerHTML = '<p class="text-danger mb-0">Failed to load PDF. Please check that the file exists and try again.</p>';
            }
            alert('Failed to load PDF. Please try again.');
        }
    }

    async loadExistingAnnotations() {
        try {
            const response = await fetch(this.getAnnotationsUrl);
            if (response.ok) {
                const data = await response.json();
                if (data.annotations && data.annotations.length > 0) {
                    // Clear existing annotations before loading to prevent duplicates
                    this.annotations = {};

                    // Track unique annotation IDs to prevent duplicates
                    const seenIds = new Set();

                    // Group annotations by page
                    data.annotations.forEach(ann => {
                        // Create unique ID for this annotation
                        const uniqueId = ann.uniqueId || ('db_' + ann.id);

                        // Skip if we've already seen this annotation
                        if (seenIds.has(uniqueId)) {
                            console.warn('Duplicate annotation detected and skipped:', uniqueId);
                            return;
                        }

                        // Mark as seen
                        seenIds.add(uniqueId);

                        if (!this.annotations[ann.page_number]) {
                            this.annotations[ann.page_number] = [];
                        }

                        // Add unique ID if not present (for existing annotations from DB)
                        ann.uniqueId = uniqueId;

                        // Ensure imageData is set for image annotations
                        if (ann.annotation_type === 'image' && ann.content && !ann.imageData) {
                            ann.imageData = ann.content;
                        }

                        // For text annotations, preserve HTML content and recalculate width
                        if (ann.annotation_type === 'text') {
                            // Content from DB should be HTML (stored when saving)
                            // Check if it looks like HTML (contains tags)
                            const contentStr = String(ann.content || '');
                            const isHTML = /<[^>]+>/.test(contentStr);

                            if (isHTML) {
                                // Content is HTML, use it as htmlContent
                                ann.htmlContent = contentStr;
                                // Extract plain text from style_data or strip HTML tags
                                if (ann.style_data && ann.style_data.plainText) {
                                    ann.content = ann.style_data.plainText;
                                } else {
                                    ann.content = contentStr.replace(/<[^>]*>/g, '').trim();
                                }
                            } else {
                                // Content is plain text (legacy data), use as both
                                ann.htmlContent = contentStr;
                                ann.content = contentStr;
                            }

                            if (this.isSignatureBlockAnnotation(ann)) {
                                ann.style_data = Object.assign({}, ann.style_data || {}, {
                                    isSignatureBlock: true,
                                });
                                ann.width = Math.min(280, this.getMaxAnnotationWidth(ann.x_position || 0));
                            } else if (!ann.width || ann.width < 250) {
                                // Force recalculation for regular text so content is not clipped.
                                ann.width = null;
                            }
                        }

                        // Mark as saved (baked into PDF) - annotations from DB are always baked in
                        ann.isBakedIntoPdf = true;

                        // Check for duplicates based on position and content before adding
                        const isDuplicate = this.annotations[ann.page_number].some(existingAnn =>
                            existingAnn.uniqueId === uniqueId ||
                            (existingAnn.id === ann.id && existingAnn.id) ||
                            (Math.abs(existingAnn.x_position - ann.x_position) < 1 &&
                                Math.abs(existingAnn.y_position - ann.y_position) < 1 &&
                                existingAnn.content === ann.content &&
                                existingAnn.annotation_type === ann.annotation_type)
                        );

                        if (!isDuplicate) {
                            this.annotations[ann.page_number].push(ann);
                        } else {
                            console.warn('Duplicate annotation by position/content detected and skipped:', ann);
                        }
                    });
                } else {
                    // Clear annotations if no annotations exist in database
                    this.annotations = {};
                    this.annotationsBakedIntoPdf = false;
                }
            }
        } catch (error) {
            console.error('Error loading annotations:', error);
        }
    }

    calculateFitScale(page) {
        const viewer = document.getElementById('pdf-viewer-section');
        if (!viewer) {
            return this.scale || 1.25;
        }

        const styles = window.getComputedStyle(viewer);
        const paddingX = parseFloat(styles.paddingLeft) + parseFloat(styles.paddingRight);
        const paddingY = parseFloat(styles.paddingTop) + parseFloat(styles.paddingBottom);
        const availableWidth = Math.max(viewer.clientWidth - paddingX, 100);
        const availableHeight = Math.max(viewer.clientHeight - paddingY, 100);
        const baseViewport = page.getViewport({ scale: 1 });

        const scaleByWidth = availableWidth / baseViewport.width;
        const scaleByHeight = availableHeight / baseViewport.height;

        // Fill the viewer column width (landscape TRFs, COA merges, reports, etc.).
        let fitScale = scaleByWidth;

        // Portrait pages that fit entirely in the viewport: scale up to remove side gutters.
        const heightAtWidthFit = baseViewport.height * scaleByWidth;
        if (heightAtWidthFit <= availableHeight) {
            fitScale = Math.min(scaleByHeight, scaleByWidth);
        }

        return Math.max(0.25, Math.min(fitScale, 3));
    }

    async renderPage(pageNum) {
        try {
            const page = await this.pdfDoc.getPage(pageNum);
            this.scale = this.calculateFitScale(page);
            const viewport = page.getViewport({ scale: this.scale });

            // Set canvas dimensions
            this.canvas.width = viewport.width;
            this.canvas.height = viewport.height;
            this.annotationCanvas.width = viewport.width;
            this.annotationCanvas.height = viewport.height;

            // Clear canvases
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.annotationCtx.clearRect(0, 0, this.annotationCanvas.width, this.annotationCanvas.height);

            // Remove all annotation HTML overlays
            this.removeAllAnnotationOverlays();

            // Render PDF page
            const renderContext = {
                canvasContext: this.ctx,
                viewport: viewport
            };
            await page.render(renderContext).promise;

            const wrapper = document.getElementById('pdf-canvas-wrapper');
            if (wrapper) {
                wrapper.style.width = `${viewport.width}px`;
                wrapper.style.height = `${viewport.height}px`;
            }

            // Render annotations for this page
            this.renderAnnotations(pageNum);

            // Update UI
            this.currentPage = pageNum;
            document.getElementById('pdf-page-num').textContent = pageNum;
            this.updateAnnotationsList();

        } catch (error) {
            console.error('Error rendering page:', error);
        }
    }

    renderAnnotations(pageNum) {
        const pageAnnotations = this.annotations[pageNum] || [];

        pageAnnotations.forEach(ann => {
            // Skip rendering annotations that are already baked into the PDF
            // (They're already visible in the PDF image itself)
            // Only render if:
            // 1. It's a new unsaved annotation (starts with 'new_')
            // 2. OR annotationsBakedIntoPdf is false (first time loading, not after save)
            // 3. OR it's being edited (user clicked edit, so render it for editing)
            const isUnsaved = ann.uniqueId && ann.uniqueId.startsWith('new_');
            const isBeingEdited = this.editingAnnotation && (this.editingAnnotation.uniqueId === ann.uniqueId || this.editingAnnotation.id === ann.id);

            if (this.annotationsBakedIntoPdf && !isUnsaved && !isBeingEdited) {
                // Annotation is baked into PDF, don't render as overlay
                return;
            }

            const isSelected = this.selectedAnnotations.has(this.getAnnotationId(ann));
            if (ann.annotation_type === 'text') {
                if (!isUnsaved && this.isSignatureBlockAnnotation(ann)) {
                    ann.style_data = Object.assign({}, ann.style_data || {}, { isSignatureBlock: true });
                    ann.width = Math.min(280, this.getMaxAnnotationWidth(ann.x_position || 0));
                } else if (!isUnsaved && (!ann.width || ann.width < 250)) {
                    // Recalculate width before rendering for regular text
                    this.recalculateAnnotationWidth(ann);
                }
                this.drawTextAnnotation(ann, isSelected, isBeingEdited);
            } else if (ann.annotation_type === 'image' && ann.imageData) {
                this.drawImageAnnotation(ann, isSelected, isBeingEdited);
            }
        });
    }

    recalculateAnnotationDimensions(annotation) {
        // Recalculate width and height based on actual content
        const htmlContent = annotation.htmlContent || annotation.content || '';
        if (!htmlContent) {
            return;
        }

        const maxWidth = this.getMaxAnnotationWidth(annotation.x_position);
        const isSignatureBlock = this.isSignatureBlockAnnotation(annotation, htmlContent);

        // Create temporary div to measure content
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlContent;
        tempDiv.style.position = 'absolute';
        tempDiv.style.visibility = 'hidden';
        tempDiv.style.top = '-9999px';
        tempDiv.style.left = '-9999px';
        tempDiv.style.fontSize = isSignatureBlock ? '11pt' : '14px';
        tempDiv.style.fontFamily = '"Times New Roman", Times, serif';
        tempDiv.style.lineHeight = '1.35';
        tempDiv.style.padding = isSignatureBlock ? '8px 10px' : '5px';
        tempDiv.style.border = '1px solid #000';
        tempDiv.style.boxSizing = 'border-box';
        tempDiv.style.wordWrap = 'break-word';
        tempDiv.style.overflowWrap = 'break-word';
        tempDiv.style.whiteSpace = 'normal';
        tempDiv.style.display = 'block';

        if (isSignatureBlock) {
            const preferredWidth = Math.min(280, maxWidth);
            tempDiv.style.width = preferredWidth + 'px';
            tempDiv.style.maxWidth = preferredWidth + 'px';
            tempDiv.style.minWidth = preferredWidth + 'px';
        } else {
            tempDiv.style.width = 'auto';
            tempDiv.style.maxWidth = maxWidth + 'px';
            tempDiv.style.minWidth = '200px';
        }

        document.body.appendChild(tempDiv);

        // Force a reflow to get accurate measurements
        tempDiv.offsetHeight;

        // Measure actual dimensions
        const scrollWidth = tempDiv.scrollWidth || tempDiv.offsetWidth || 200;
        const scrollHeight = tempDiv.scrollHeight || tempDiv.offsetHeight || 30;

        if (isSignatureBlock) {
            annotation.width = Math.min(280, maxWidth);
            annotation.height = Math.max(70, scrollHeight);
        } else {
            annotation.width = Math.max(200, Math.min(scrollWidth + 12, maxWidth));
            annotation.height = Math.max(30, scrollHeight + 12);
        }

        document.body.removeChild(tempDiv);
    }

    recalculateAnnotationWidth(annotation) {
        this.recalculateAnnotationDimensions(annotation);
    }

    finalizeAllAnnotationDimensions() {
        Object.keys(this.annotations).forEach((pageNum) => {
            this.annotations[pageNum].forEach((ann) => {
                if (ann.annotation_type === 'text') {
                    this.recalculateAnnotationDimensions(ann);
                }
            });
        });
    }

    drawTextAnnotation(ann, isSelected = false, isEditing = false) {
        // Use HTML overlay instead of canvas for rich text rendering
        const annotationId = 'annotation-' + this.getAnnotationId(ann);
        let annotationDiv = document.getElementById(annotationId);
        const htmlContent = ann.htmlContent || ann.content || '';
        const textContent = ann.content || '';
        const displayContent = htmlContent || textContent;
        const isSignatureBlock = this.isSignatureBlockAnnotation(ann, displayContent);

        if (!annotationDiv) {
            // Create new annotation div
            annotationDiv = document.createElement('div');
            annotationDiv.id = annotationId;
            annotationDiv.className = 'pdf-annotation-overlay';
            annotationDiv.style.position = 'absolute';
            annotationDiv.style.pointerEvents = 'auto';
            annotationDiv.style.zIndex = '10';

            // Append to canvas wrapper
            const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
            if (canvasWrapper) {
                canvasWrapper.appendChild(annotationDiv);
            }
        }

        // Set position and size (relative to canvas, which is inside canvas-wrapper)
        annotationDiv.style.left = ann.x_position + 'px';
        annotationDiv.style.top = ann.y_position + 'px';

        const maxWidth = this.getMaxAnnotationWidth(ann.x_position);
        if (isSignatureBlock) {
            const preferredWidth = Math.min(280, maxWidth);
            ann.width = preferredWidth;
            annotationDiv.style.minWidth = preferredWidth + 'px';
            annotationDiv.style.width = preferredWidth + 'px';
            annotationDiv.style.maxWidth = preferredWidth + 'px';
            annotationDiv.style.height = 'auto';
            annotationDiv.style.minHeight = '0';
        } else {
            const baseWidth = ann.width || 200;
            annotationDiv.style.minWidth = Math.min(baseWidth, maxWidth) + 'px';
            annotationDiv.style.width = 'auto';
            annotationDiv.style.maxWidth = maxWidth + 'px';
            annotationDiv.style.minHeight = (ann.height || 30) + 'px';
        }

        // Set styling based on state
        annotationDiv.classList.toggle('is-selected', !!(isSelected && !this.captureMode));
        annotationDiv.classList.toggle('is-editing', !!(isEditing && !this.captureMode));
        if (this.captureMode) {
            annotationDiv.style.border = 'none';
            annotationDiv.style.backgroundColor = 'transparent';
            annotationDiv.style.borderRadius = '0';
            annotationDiv.style.boxShadow = 'none';
        } else if (!isSelected && !isEditing) {
            annotationDiv.style.border = '';
            annotationDiv.style.backgroundColor = '';
            annotationDiv.style.borderRadius = '';
        }

        annotationDiv.style.padding = isSignatureBlock ? '8px 10px' : '5px';
        annotationDiv.style.boxSizing = 'border-box';
        annotationDiv.style.fontSize = isSignatureBlock ? '11pt' : '14px';
        annotationDiv.style.lineHeight = '1.35';
        annotationDiv.style.wordWrap = 'break-word';
        annotationDiv.style.overflowWrap = 'break-word';
        annotationDiv.style.overflow = 'visible';
        annotationDiv.style.whiteSpace = 'normal';
        annotationDiv.style.color = '#000';
        annotationDiv.style.display = 'block';
        annotationDiv.style.cursor = this.captureMode ? 'default' : 'grab';

        annotationDiv.innerHTML = displayContent;

        // Measure actual content dimensions after layout
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                const scrollWidth = annotationDiv.scrollWidth;
                const offsetWidth = annotationDiv.offsetWidth;
                const scrollHeight = annotationDiv.scrollHeight;
                const offsetHeight = annotationDiv.offsetHeight;

                if (isSignatureBlock) {
                    const preferredWidth = Math.min(280, this.getMaxAnnotationWidth(ann.x_position));
                    annotationDiv.style.width = preferredWidth + 'px';
                    ann.width = preferredWidth;
                    const neededHeight = Math.max(offsetHeight || 0, scrollHeight || 0, 70);
                    annotationDiv.style.height = neededHeight + 'px';
                    ann.height = neededHeight;
                    return;
                }

                const baseWidth = ann.width || 200;
                const actualWidth = Math.max(
                    scrollWidth || 0,
                    offsetWidth || 0,
                    baseWidth,
                    200
                );
                const actualHeight = Math.max(
                    scrollHeight || 0,
                    offsetHeight || 0,
                    ann.height || 30,
                    30
                );

                const neededWidth = Math.min(actualWidth + 12, maxWidth);
                annotationDiv.style.width = neededWidth + 'px';
                ann.width = neededWidth;

                const neededHeight = actualHeight + 8;
                annotationDiv.style.minHeight = neededHeight + 'px';
                annotationDiv.style.height = neededHeight + 'px';
                ann.height = neededHeight;
            });
        });

        this.attachAnnotationOverlayInteractions(annotationDiv, ann);

        // Store reference for cleanup
        if (!this.annotationDivs) {
            this.annotationDivs = new Map();
        }
        this.annotationDivs.set(annotationId, annotationDiv);
    }

    isSignatureBlockAnnotation(ann, html = null) {
        const content = html || ann?.htmlContent || ann?.content || '';
        if (ann?.style_data?.isSignatureBlock) {
            return true;
        }

        return typeof content === 'string' && (
            content.includes('annotation-signature-block')
            || content.includes('data-signature-block')
            || (content.includes('Signed by:') && content.includes('Signature:'))
        );
    }

    attachAnnotationOverlayInteractions(annotationDiv, ann) {
        if (annotationDiv.dataset.interactionHandlersAdded === 'true') {
            return;
        }

        let dragging = false;
        let dragMoved = false;
        let startX = 0;
        let startY = 0;
        let originLeft = 0;
        let originTop = 0;

        const onMouseMove = (e) => {
            if (!dragging) {
                return;
            }

            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
                dragMoved = true;
            }

            const width = parseFloat(ann.width) || annotationDiv.offsetWidth || 100;
            const height = parseFloat(ann.height) || annotationDiv.offsetHeight || 40;
            const maxX = Math.max(0, (this.canvas?.width || 600) - width);
            const maxY = Math.max(0, (this.canvas?.height || 800) - height);
            const nextX = Math.max(0, Math.min(maxX, originLeft + dx));
            const nextY = Math.max(0, Math.min(maxY, originTop + dy));

            annotationDiv.style.left = nextX + 'px';
            annotationDiv.style.top = nextY + 'px';
            ann.x_position = nextX;
            ann.y_position = nextY;
        };

        const onMouseUp = () => {
            if (!dragging) {
                return;
            }

            dragging = false;
            annotationDiv.classList.remove('is-dragging');
            annotationDiv.style.cursor = 'grab';
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);

            if (dragMoved) {
                if (!this.isUnsavedAnnotation(ann)) {
                    ann.uniqueId = 'new_' + (++this.annotationIdCounter);
                    delete ann.id;
                }

                // Image annotations are painted on canvas — re-render so the image follows the handle.
                if (ann.annotation_type === 'image') {
                    this.renderPage(this.currentPage);
                } else {
                    this.updateAnnotationsList();
                }

                this.updateDeleteButtonState();
                this.updateEditButtonState();
                return;
            }

            const annId = this.getAnnotationId(ann);
            this.selectedAnnotations.clear();
            this.selectedAnnotations.add(annId);
            this.refreshOverlaySelectionStyles();
            this.updateDeleteButtonState();
            this.updateEditButtonState();
        };

        annotationDiv.addEventListener('mousedown', (e) => {
            if (this.captureMode || e.button !== 0) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            dragging = true;
            dragMoved = false;
            startX = e.clientX;
            startY = e.clientY;
            originLeft = parseFloat(annotationDiv.style.left) || ann.x_position || 0;
            originTop = parseFloat(annotationDiv.style.top) || ann.y_position || 0;

            annotationDiv.classList.add('is-dragging');
            annotationDiv.style.cursor = 'grabbing';

            // Select while dragging so Delete is available.
            const annId = this.getAnnotationId(ann);
            this.selectedAnnotations.clear();
            this.selectedAnnotations.add(annId);
            this.refreshOverlaySelectionStyles();
            this.updateDeleteButtonState();
            this.updateEditButtonState();

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        annotationDiv.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        annotationDiv.dataset.interactionHandlersAdded = 'true';
    }

    refreshOverlaySelectionStyles() {
        if (!this.annotationDivs) {
            return;
        }

        this.annotationDivs.forEach((div, annotationId) => {
            const id = annotationId.replace(/^annotation-/, '');
            const selected = this.selectedAnnotations.has(this.normalizeAnnotationId(id));
            const editing = this.editingAnnotation
                && this.getAnnotationId(this.editingAnnotation) === this.normalizeAnnotationId(id);
            div.classList.toggle('is-selected', selected && !editing);
            div.classList.toggle('is-editing', !!editing);
        });
    }

    drawImageAnnotation(ann, isSelected = false, isEditing = false) {
        if (ann.imageData) {
            const img = new Image();
            img.onload = () => {
                this.annotationCtx.drawImage(img, ann.x_position, ann.y_position, ann.width || 100, ann.height || 100);

                // Draw border if selected or editing
                if (this.captureMode) {
                    return;
                }
                if (isEditing) {
                    this.annotationCtx.strokeStyle = '#4caf50'; // Green border when editing
                    this.annotationCtx.lineWidth = 3;
                } else if (isSelected) {
                    this.annotationCtx.strokeStyle = '#ff5722';
                    this.annotationCtx.lineWidth = 3;
                }
                if (isSelected || isEditing) {
                    this.annotationCtx.strokeRect(ann.x_position, ann.y_position, ann.width || 100, ann.height || 100);
                }
            };
            img.src = ann.imageData;
        }

        // Transparent HTML handle so image annotations can be dragged like text blocks.
        this.ensureImageDragHandle(ann, isSelected, isEditing);
    }

    ensureImageDragHandle(ann, isSelected = false, isEditing = false) {
        if (this.captureMode) {
            return;
        }

        const annotationId = 'annotation-image-handle-' + this.getAnnotationId(ann);
        let handle = document.getElementById(annotationId);
        if (!handle) {
            handle = document.createElement('div');
            handle.id = annotationId;
            handle.className = 'pdf-annotation-overlay pdf-image-drag-handle';
            handle.style.position = 'absolute';
            handle.style.pointerEvents = 'auto';
            handle.style.zIndex = '11';
            handle.style.backgroundColor = 'transparent';
            handle.style.padding = '0';
            handle.title = 'Drag to reposition';

            const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
            if (canvasWrapper) {
                canvasWrapper.appendChild(handle);
            }
        }

        handle.style.left = (ann.x_position || 0) + 'px';
        handle.style.top = (ann.y_position || 0) + 'px';
        handle.style.width = (ann.width || 100) + 'px';
        handle.style.height = (ann.height || 100) + 'px';
        handle.style.minWidth = '0';
        handle.style.minHeight = '0';
        handle.style.border = isEditing
            ? '2px solid #4caf50'
            : (isSelected ? '2px solid #ff5722' : '1px dashed transparent');
        handle.classList.toggle('is-selected', !!isSelected);
        handle.classList.toggle('is-editing', !!isEditing);

        this.attachAnnotationOverlayInteractions(handle, ann);

        if (!this.annotationDivs) {
            this.annotationDivs = new Map();
        }
        this.annotationDivs.set(annotationId, handle);
    }

    setupEventListeners() {
        // Tool selection
        document.querySelectorAll('.annotation-tool').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.annotation-tool').forEach(b => b.classList.remove('active-tool'));
                e.currentTarget.classList.add('active-tool');
                this.currentTool = e.currentTarget.dataset.tool;

                if (this.currentTool === 'image') {
                    document.getElementById('annotation-image-input').click();
                }
            });
        });

        // Page navigation
        document.getElementById('pdf-prev-page').addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.renderPage(this.currentPage - 1);
            }
        });

        document.getElementById('pdf-next-page').addEventListener('click', () => {
            if (this.currentPage < this.totalPages) {
                this.renderPage(this.currentPage + 1);
            }
        });

        // Unified canvas click handler for annotation selection or creation
        this.annotationCanvas.addEventListener('click', (e) => {
            const rect = this.annotationCanvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Handle repositioning of image annotation
            if (this.currentTool === 'reposition_image' && this.pendingRepositionAnnotation) {
                this.pendingRepositionAnnotation.x_position = x;
                this.pendingRepositionAnnotation.y_position = y;
                // Ensure it's marked as unsaved after repositioning
                if (!this.isUnsavedAnnotation(this.pendingRepositionAnnotation)) {
                    this.pendingRepositionAnnotation.uniqueId = 'new_' + (++this.annotationIdCounter);
                    delete this.pendingRepositionAnnotation.id;
                }
                this.renderPage(this.currentPage);
                this.updateAnnotationsList();
                this.currentTool = null;
                this.pendingRepositionAnnotation = null;
                this.editingAnnotation = null;
                this.updateEditButtonState();
                return;
            }

            // First check if clicking on an existing annotation (for selection)
            const clickedAnnotation = this.getAnnotationAtPosition(x, y, this.currentPage);
            if (clickedAnnotation) {
                // Toggle selection (Ctrl/Cmd for multi-select)
                if (e.ctrlKey || e.metaKey) {
                    this.toggleAnnotationSelection(clickedAnnotation);
                } else {
                    // Single select - clear others and select this one
                    this.selectedAnnotations.clear();
                    this.selectedAnnotations.add(this.getAnnotationId(clickedAnnotation));
                }
                this.renderPage(this.currentPage);
                this.updateDeleteButtonState();
                this.updateEditButtonState();
                return;
            }

            // If clicking on empty space and image tool is active with pending image
            if (this.currentTool === 'image' && this.pendingImageData) {
                this.addImageAnnotation(x, y, this.pendingImageData);
                this.pendingImageData = null;
                this.currentTool = null;
                document.querySelectorAll('.annotation-tool').forEach(b => b.classList.remove('active-tool'));
                return;
            }

            // If no annotation clicked and text tool is selected, create new annotation
            if (this.currentTool === 'text') {
                this.addTextAnnotation(x, y);
            } else if (!this.currentTool) {
                // Clear selection if clicking on empty space without tool
                this.selectedAnnotations.clear();
                this.editingAnnotation = null;
                this.refreshOverlaySelectionStyles();
                this.updateDeleteButtonState();
                this.updateEditButtonState();
            }
        });

        // Double-click handler for editing annotations
        this.annotationCanvas.addEventListener('dblclick', (e) => {
            const rect = this.annotationCanvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const clickedAnnotation = this.getAnnotationAtPosition(x, y, this.currentPage);
            if (clickedAnnotation && this.isUnsavedAnnotation(clickedAnnotation)) {
                this.startEditingAnnotation(clickedAnnotation);
            }
        });

        // Image upload
        document.getElementById('annotation-image-input').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.pendingImageData = event.target.result;
                    alert('Click on the PDF where you want to place the image');
                    this.currentTool = 'image';
                };
                reader.readAsDataURL(file);
            }
            e.target.value = ''; // Reset input
        });

        // Edit button
        const editBtn = document.getElementById('edit-annotation-btn');
        if (editBtn) {
            editBtn.addEventListener('click', () => {
                this.editSelectedAnnotation();
            });
        }

        // Delete button
        const deleteBtn = document.getElementById('delete-annotations-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
                this.deleteSelectedAnnotations();
            });
        }

        // Save button
        document.getElementById('save-annotations-btn').addEventListener('click', () => {
            this.saveAnnotations();
        });

        this.setupViewerResizeHandling();
    }

    setupViewerResizeHandling() {
        const viewer = document.getElementById('pdf-viewer-section');
        const main = document.getElementById('pdf-main');
        if (!viewer) {
            return;
        }

        let resizeTimeout = null;
        const rerenderCurrentPage = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (this.pdfDoc && this.currentPage) {
                    this.renderPage(this.currentPage);
                }
            }, 120);
        };

        window.addEventListener('resize', rerenderCurrentPage);

        if (typeof ResizeObserver !== 'undefined') {
            this.resizeObserver = new ResizeObserver(rerenderCurrentPage);
            this.resizeObserver.observe(viewer);
            if (main) {
                this.resizeObserver.observe(main);
            }
        }
    }

    getAnnotationAtPosition(x, y, pageNum) {
        const pageAnnotations = this.annotations[pageNum] || [];

        // Check annotations in reverse order (top-most first)
        for (let i = pageAnnotations.length - 1; i >= 0; i--) {
            const ann = pageAnnotations[i];
            const annX = ann.x_position;
            const annY = ann.y_position;
            const annWidth = parseFloat(ann.width) || (ann.annotation_type === 'text' ? 150 : 100);
            const annHeight = parseFloat(ann.height) || (ann.annotation_type === 'text' ? 30 : 100);

            if (x >= annX && x <= annX + annWidth && y >= annY && y <= annY + annHeight) {
                return ann;
            }
        }
        return null;
    }

    toggleAnnotationSelection(annotation) {
        const id = this.getAnnotationId(annotation);
        if (!id) {
            return;
        }
        if (this.selectedAnnotations.has(id)) {
            this.selectedAnnotations.delete(id);
        } else {
            this.selectedAnnotations.add(id);
        }
    }

    updateDeleteButtonState() {
        const deleteBtn = document.getElementById('delete-annotations-btn');
        if (deleteBtn) {
            deleteBtn.disabled = this.selectedAnnotations.size === 0;
        }
    }

    updateEditButtonState() {
        const editBtn = document.getElementById('edit-annotation-btn');
        if (editBtn) {
            // Enable if exactly one annotation is selected (both saved and unsaved can be edited)
            editBtn.disabled = this.selectedAnnotations.size !== 1;
        }
    }

    isUnsavedAnnotation(annotation) {
        // Check if annotation is unsaved (has uniqueId starting with "new_")
        return annotation && annotation.uniqueId && annotation.uniqueId.startsWith('new_');
    }

    normalizeAnnotationId(value) {
        if (value === null || value === undefined) {
            return null;
        }
        return String(value);
    }

    getAnnotationId(annotation) {
        if (!annotation) {
            return null;
        }
        return this.normalizeAnnotationId(annotation.uniqueId ?? annotation.id);
    }

    getMaxAnnotationWidth(xPosition = 0) {
        const canvasWidth = this.canvas?.width || 600;
        const remaining = canvasWidth - xPosition - 10;
        return Math.max(150, Math.min(600, remaining));
    }

    findAnnotationById(id) {
        const normalizedId = this.normalizeAnnotationId(id);
        // Find annotation by uniqueId or id across all pages
        for (const pageNum in this.annotations) {
            const annotation = this.annotations[pageNum].find(ann =>
                this.getAnnotationId(ann) === normalizedId
            );
            if (annotation) return annotation;
        }
        return null;
    }

    startEditingAnnotation(annotation) {
        // Allow editing of both saved and unsaved annotations
        // When editing a saved annotation, we need to render it as an overlay
        this.editingAnnotation = annotation;
        this.selectedAnnotations.clear();
        this.selectedAnnotations.add(this.getAnnotationId(annotation));

        // Render the page (will show the annotation being edited)
        this.renderPage(this.currentPage);
        this.updateEditButtonState();

        if (annotation.annotation_type === 'text') {
            this.editTextAnnotation(annotation);
        } else if (annotation.annotation_type === 'image') {
            this.editImageAnnotation(annotation);
        }
    }

    editTextAnnotation(annotation) {
        // Show modal with TinyMCE editor pre-filled with existing HTML content
        // Prefer htmlContent, but fallback to content (which might be HTML)
        const existingContent = annotation.htmlContent || annotation.content || '';
        this.showTextAnnotationModal(existingContent, (textContent, htmlContent) => {
            if (textContent !== null && htmlContent !== null) {
                // Update both content and htmlContent
                annotation.content = textContent; // Plain text for search/fallback
                annotation.htmlContent = htmlContent; // HTML content for rendering

                const isSignatureBlock = this.isSignatureBlockAnnotation(annotation, htmlContent);
                annotation.style_data = Object.assign({}, annotation.style_data || {}, {
                    isSignatureBlock: isSignatureBlock,
                });
                if (isSignatureBlock) {
                    annotation.width = 280;
                }

                // Re-render the annotation with new content
                this.removeAnnotationOverlay(annotation);
                this.drawTextAnnotation(annotation, this.selectedAnnotations.has(this.getAnnotationId(annotation)), false);
                // Mark as unsaved if it was a saved annotation
                if (!this.isUnsavedAnnotation(annotation)) {
                    // Convert saved annotation to unsaved for re-saving
                    annotation.uniqueId = 'new_' + (++this.annotationIdCounter);
                    // Remove the old database ID reference
                    delete annotation.id;
                }
                // Recalculate height based on content if needed
                // Estimate height based on HTML content
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlContent;
                tempDiv.style.position = 'absolute';
                tempDiv.style.visibility = 'hidden';
                tempDiv.style.width = (annotation.width || 200) + 'px';
                document.body.appendChild(tempDiv);
                const estimatedHeight = Math.max(30, tempDiv.offsetHeight + 10);
                document.body.removeChild(tempDiv);
                annotation.height = estimatedHeight;

                this.renderPage(this.currentPage);
                this.updateAnnotationsList();
            }
            this.editingAnnotation = null;
            this.updateEditButtonState();
        });
    }

    removeAnnotationOverlay(annotation) {
        const annotationId = 'annotation-' + this.getAnnotationId(annotation);
        const overlay = document.getElementById(annotationId);
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
        if (this.annotationDivs) {
            this.annotationDivs.delete(annotationId);
        }
    }

    async renderAnnotationsToCanvas(pageNum, options = {}) {
        const includeText = options.includeText !== false;
        const includeBorders = options.includeBorders !== false;
        // Render HTML annotations as styled text on the annotation canvas
        // This ensures annotations are always captured when saving
        const pageAnnotations = this.annotations[pageNum] || [];

        pageAnnotations.forEach(ann => {
            if (ann.annotation_type === 'text') {
                if (!includeText) {
                    return;
                }
                const ctx = this.annotationCtx;
                const htmlContent = ann.htmlContent || ann.content || '';

                if (!htmlContent) {
                    console.warn('No content for annotation:', ann);
                    return;
                }

                // Extract plain text from HTML (preserve line breaks)
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlContent;
                const textContent = tempDiv.textContent || tempDiv.innerText || '';

                if (!textContent.trim()) {
                    console.warn('Empty text content after parsing HTML:', htmlContent);
                    return;
                }

                // Calculate dimensions - measure text first to get accurate width
                const padding = 5;
                const maxWidth = this.getMaxAnnotationWidth(ann.x_position);

                // Create temp div to measure actual content dimensions
                const measureDiv = document.createElement('div');
                measureDiv.innerHTML = htmlContent;
                measureDiv.style.position = 'absolute';
                measureDiv.style.visibility = 'hidden';
                measureDiv.style.fontSize = '14px';
                measureDiv.style.fontFamily = '"Times New Roman", Times, serif';
                measureDiv.style.lineHeight = '1.4';
                measureDiv.style.padding = padding + 'px';
                measureDiv.style.width = 'auto';
                measureDiv.style.maxWidth = maxWidth + 'px';
                measureDiv.style.wordWrap = 'break-word';
                measureDiv.style.whiteSpace = 'normal';
                document.body.appendChild(measureDiv);

                const actualWidth = Math.max(ann.width || 200, Math.min(measureDiv.offsetWidth, maxWidth));
                const actualHeight = Math.max(30, measureDiv.offsetHeight);
                const computedLineHeight = parseFloat(window.getComputedStyle(measureDiv).lineHeight) || 18;
                const renderedText = measureDiv.innerText || '';
                document.body.removeChild(measureDiv);

                // Update annotation dimensions
                ann.width = actualWidth;
                ann.height = actualHeight;

                // Draw border only when requested
                if (includeBorders) {
                    ctx.strokeStyle = '#ff9800';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(ann.x_position, ann.y_position, actualWidth, actualHeight);
                }

                // Draw text
                ctx.fillStyle = '#000';
                ctx.font = '14px "Times New Roman"';
                ctx.textBaseline = 'top';
                ctx.textAlign = 'left';

                // Check for basic formatting in HTML
                const hasBold = /<strong>|<b>/i.test(htmlContent);
                const hasItalic = /<em>|<i>/i.test(htmlContent);

                if (hasBold && hasItalic) {
                    ctx.font = 'bold italic 14px "Times New Roman"';
                } else if (hasBold) {
                    ctx.font = 'bold 14px "Times New Roman"';
                } else if (hasItalic) {
                    ctx.font = 'italic 14px "Times New Roman"';
                }

                // Render text with proper word wrapping
                const lines = renderedText.split('\n');
                let yOffset = ann.y_position + padding;
                const maxWidthText = actualWidth - (padding * 2);

                lines.forEach((line) => {
                    // Clean HTML entities
                    const cleanLine = line
                        .replace(/&nbsp;/g, ' ')
                        .replace(/&amp;/g, '&')
                        .replace(/&lt;/g, '<')
                        .replace(/&gt;/g, '>')
                        .replace(/&quot;/g, '"')
                        .trim();

                    if (cleanLine) {
                        // Word wrap if needed
                        const words = cleanLine.split(' ');
                        let currentLine = '';
                        let currentY = yOffset;

                        words.forEach((word) => {
                            const testLine = currentLine + (currentLine ? ' ' : '') + word;
                            const metrics = ctx.measureText(testLine);

                            if (metrics.width > maxWidthText && currentLine) {
                                // Draw current line and start new line
                                ctx.fillText(currentLine, ann.x_position + padding, currentY);
                                currentLine = word;
                                currentY += computedLineHeight;
                            } else {
                                currentLine = testLine;
                            }
                        });

                        // Draw the last line (or only line if no wrapping needed)
                        if (currentLine) {
                            ctx.fillText(currentLine, ann.x_position + padding, currentY);
                            yOffset = currentY + computedLineHeight;
                        }
                    } else {
                        yOffset += computedLineHeight; // Empty line
                    }
                });
            }
        });
    }

    editImageAnnotation(annotation) {
        const choice = confirm('Choose an option:\n\nOK - Replace image\nCancel - Reposition image');
        const self = this;

        if (choice) {
            // Replace image
            const input = document.getElementById('annotation-image-input');
            input.click();

            const replaceImageHandler = function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        annotation.content = event.target.result;
                        annotation.imageData = event.target.result;
                        // Mark as unsaved if it was a saved annotation
                        if (!self.isUnsavedAnnotation(annotation)) {
                            // Convert saved annotation to unsaved for re-saving
                            annotation.uniqueId = 'new_' + (++self.annotationIdCounter);
                            // Remove the old database ID reference
                            delete annotation.id;
                        }
                        self.renderPage(self.currentPage);
                        self.updateAnnotationsList();
                        self.editingAnnotation = null;
                        self.updateEditButtonState();
                    };
                    reader.readAsDataURL(file);
                }
                e.target.value = '';
                input.removeEventListener('change', replaceImageHandler);
            };

            input.addEventListener('change', replaceImageHandler, { once: true });
        } else {
            // Reposition image - allow dragging
            alert('Click on the PDF where you want to move this image annotation.');
            // Mark as unsaved if it was a saved annotation
            if (!this.isUnsavedAnnotation(annotation)) {
                annotation.uniqueId = 'new_' + (++this.annotationIdCounter);
                delete annotation.id;
            }
            this.currentTool = 'reposition_image';
            this.pendingRepositionAnnotation = annotation;
        }
    }

    editSelectedAnnotation() {
        if (this.selectedAnnotations.size !== 1) {
            alert('Please select exactly one annotation to edit.');
            return;
        }

        const selectedId = Array.from(this.selectedAnnotations)[0];
        const annotation = this.findAnnotationById(selectedId);

        if (!annotation) {
            alert('Annotation not found.');
            return;
        }

        // Allow editing both saved and unsaved annotations
        this.startEditingAnnotation(annotation);
    }

    async deleteSelectedAnnotations() {
        if (this.selectedAnnotations.size === 0) {
            return;
        }

        if (!confirm(`Are you sure you want to delete ${this.selectedAnnotations.size} annotation(s)?`)) {
            return;
        }

        try {
            // Collect annotation IDs to delete (both DB IDs and temporary IDs)
            const annotationsToDelete = [];
            const selectedIds = Array.from(this.selectedAnnotations);

            // Remove from local annotations object and remove overlays
            Object.keys(this.annotations).forEach(pageNum => {
                this.annotations[pageNum] = this.annotations[pageNum].filter(ann => {
                    const annId = this.getAnnotationId(ann);
                    if (selectedIds.includes(annId)) {
                        // Remove the HTML overlay
                        this.removeAnnotationOverlay(ann);
                        annotationsToDelete.push({
                            id: ann.id, // Database ID if exists
                            uniqueId: ann.uniqueId || ann.id
                        });
                        return false; // Remove from array
                    }
                    return true;
                });
            });

            // If we have database IDs, delete from server
            const dbIds = annotationsToDelete.filter(a => a.id && typeof a.id === 'number').map(a => a.id);
            if (dbIds.length > 0 && this.deleteAnnotationsUrl) {
                const response = await fetch(this.deleteAnnotationsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ annotation_ids: dbIds })
                });

                if (!response.ok) {
                    throw new Error('Failed to delete annotations from server');
                }
            }

            // Clear selection
            this.selectedAnnotations.clear();
            this.editingAnnotation = null;

            // Re-render current page
            await this.renderPage(this.currentPage);

            // Update button states
            this.updateDeleteButtonState();
            this.updateEditButtonState();

            // Update annotations list
            this.updateAnnotationsList();

        } catch (error) {
            console.error('Error deleting annotations:', error);
            alert('Failed to delete annotations. Please try again.');
        }
    }

    removeAllAnnotationOverlays() {
        // Remove all annotation overlay divs
        if (this.annotationDivs) {
            this.annotationDivs.forEach((div, id) => {
                if (div && div.parentNode) {
                    div.parentNode.removeChild(div);
                }
            });
            this.annotationDivs.clear();
        }

        // Also remove any orphaned annotation divs
        const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
        if (canvasWrapper) {
            const overlays = canvasWrapper.querySelectorAll('.pdf-annotation-overlay');
            overlays.forEach(overlay => {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            });
        }
    }

    addTextAnnotation(x, y) {
        // Store pending annotation data
        this.pendingTextAnnotation = { x, y };

        // Show modal with TinyMCE editor
        this.showTextAnnotationModal(null, (textContent, htmlContent) => {
            if (!textContent) return;

            const uniqueId = 'new_' + (++this.annotationIdCounter);
            const isSignatureBlock = this.isSignatureBlockAnnotation(null, htmlContent);
            const annotation = {
                uniqueId: uniqueId,
                page_number: this.currentPage,
                annotation_type: 'text',
                content: textContent, // Store plain text for search/display
                htmlContent: htmlContent, // Store HTML content for rendering
                x_position: x,
                y_position: y,
                width: isSignatureBlock ? 280 : 200,
                height: isSignatureBlock ? 90 : 30,
                style_data: {
                    fontSize: isSignatureBlock ? 11 : 14,
                    color: '#000',
                    isSignatureBlock: isSignatureBlock,
                }
            };

            if (!this.annotations[this.currentPage]) {
                this.annotations[this.currentPage] = [];
            }

            // Check for duplicates before adding (based on position and content)
            const isDuplicate = this.annotations[this.currentPage].some(ann =>
                ann.annotation_type === 'text' &&
                Math.abs(ann.x_position - annotation.x_position) < 5 &&
                Math.abs(ann.y_position - annotation.y_position) < 5 &&
                ann.content === annotation.content
            );

            if (!isDuplicate) {
                this.annotations[this.currentPage].push(annotation);
                this.drawTextAnnotation(annotation);
                this.updateAnnotationsList();
            }

            this.currentTool = null;
            document.querySelectorAll('.annotation-tool').forEach(b => b.classList.remove('active-tool'));
            this.pendingTextAnnotation = null;
        });
    }

    showTextAnnotationModal(existingContent, callback) {
        this.textAnnotationCallback = callback;
        const modal = document.getElementById('textAnnotationModal');

        // Set content in editor
        if (typeof tinymce !== 'undefined' && tinymce.get('annotation-text-editor')) {
            const editor = tinymce.get('annotation-text-editor');
            if (existingContent) {
                // If editing, set the existing content
                editor.setContent(existingContent);
            } else {
                editor.setContent('');
            }
        } else {
            // If TinyMCE not ready, wait a bit
            setTimeout(() => {
                if (typeof tinymce !== 'undefined' && tinymce.get('annotation-text-editor')) {
                    const editor = tinymce.get('annotation-text-editor');
                    editor.setContent(existingContent || '');
                }
            }, 100);
        }

        // Show modal (try jQuery/bootstrap first, then vanilla JS)
        if (typeof $ !== 'undefined' && $('#textAnnotationModal').modal) {
            $('#textAnnotationModal').modal('show');
        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal && modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else if (modal) {
            // Fallback: show modal manually
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }

        // Focus on editor after modal is shown
        setTimeout(() => {
            if (typeof tinymce !== 'undefined' && tinymce.get('annotation-text-editor')) {
                tinymce.get('annotation-text-editor').focus();
            }
        }, 300);
    }

    addImageAnnotation(x, y, imageData) {
        const uniqueId = 'new_' + (++this.annotationIdCounter);
        const annotation = {
            uniqueId: uniqueId,
            page_number: this.currentPage,
            annotation_type: 'image',
            content: imageData, // Store base64 data
            imageData: imageData,
            x_position: x,
            y_position: y,
            width: 100,
            height: 100
        };

        if (!this.annotations[this.currentPage]) {
            this.annotations[this.currentPage] = [];
        }

        // Check for duplicates before adding (based on position)
        const isDuplicate = this.annotations[this.currentPage].some(ann =>
            ann.annotation_type === 'image' &&
            Math.abs(ann.x_position - annotation.x_position) < 5 &&
            Math.abs(ann.y_position - annotation.y_position) < 5
        );

        if (!isDuplicate) {
            this.annotations[this.currentPage].push(annotation);
            this.drawImageAnnotation(annotation);
            this.updateAnnotationsList();
        }
    }

    updateAnnotationsList() {
        const listContainer = document.getElementById('annotations-list');
        const allAnnotations = [];
        const seenIds = new Set(); // Track unique IDs to prevent duplicates

        // Flatten all annotations and deduplicate
        Object.keys(this.annotations).forEach(pageNum => {
            this.annotations[pageNum].forEach(ann => {
                const annId = this.getAnnotationId(ann);
                // Skip if we've already added this annotation
                if (!seenIds.has(annId)) {
                    seenIds.add(annId);
                    allAnnotations.push({ ...ann, page: pageNum });
                }
            });
        });

        if (allAnnotations.length === 0) {
            listContainer.innerHTML = '<p class="text-muted">No annotations yet.</p>';
            return;
        }

        const self = this;
        listContainer.innerHTML = allAnnotations.map((ann, idx) => {
            const annId = this.getAnnotationId(ann);
            const isUnsaved = this.isUnsavedAnnotation(ann);
            const isSelected = this.selectedAnnotations.has(annId);
            const editButton = isUnsaved ? `
                <button class="btn btn-sm btn-outline-primary mt-1 edit-annotation-btn" data-annotation-id="${annId}" title="Edit annotation">
                    <i class="mdi mdi-pencil"></i> Edit
                </button>
            ` : '<span class="badge badge-secondary mt-1">Saved</span>';

            return `
            <div class="annotation-item ${isSelected ? 'border border-primary' : ''}" data-annotation-id="${annId}">
                <div class="mb-1">
                    <span class="badge badge-${ann.annotation_type === 'text' ? 'warning' : 'info'}">
                        ${this.isSignatureBlockAnnotation(ann) ? 'Signature' : (ann.annotation_type === 'text' ? 'Text' : 'Image')}
                    </span>
                    <span class="badge badge-secondary">Page ${ann.page}</span>
                    ${!isUnsaved ? '<span class="badge badge-success">Saved</span>' : ''}
                </div>
                <div style="font-size: 0.85rem; margin-bottom: 5px;">
                    ${ann.annotation_type === 'text' ? (ann.content || '').substring(0, 50) + (ann.content && ann.content.length > 50 ? '...' : '') : 'Image annotation'}
                </div>
                ${editButton}
            </div>
        `;
        }).join('');

        // Add click handlers to annotation items for selection
        listContainer.querySelectorAll('.annotation-item').forEach((item, idx) => {
            item.addEventListener('click', (e) => {
                // Handle edit button clicks
                if (e.target.closest('.edit-annotation-btn')) {
                    const btn = e.target.closest('.edit-annotation-btn');
                    const annId = btn.getAttribute('data-annotation-id');
                    const annotation = self.findAnnotationById(annId);
                    if (annotation) {
                        // Navigate to the page if needed
                        if (annotation.page_number !== self.currentPage) {
                            self.renderPage(annotation.page_number);
                        }
                        self.startEditingAnnotation(annotation);
                    }
                    return;
                }

                // Don't select when clicking edit button
                if (e.target.tagName === 'BUTTON' || e.target.closest('button')) return;

                const annId = item.getAttribute('data-annotation-id');
                const annotation = self.findAnnotationById(annId);
                if (annotation) {
                    // Navigate to the page if needed
                    if (annotation.page_number !== self.currentPage) {
                        self.renderPage(annotation.page_number);
                    }

                    // Select the annotation
                    if (e.ctrlKey || e.metaKey) {
                        self.toggleAnnotationSelection(annotation);
                    } else {
                        self.selectedAnnotations.clear();
                        self.selectedAnnotations.add(self.normalizeAnnotationId(annId));
                    }
                    self.renderPage(self.currentPage);
                    self.updateDeleteButtonState();
                    self.updateEditButtonState();
                }
            });
        });
    }

    async saveAnnotations() {
        if (confirm('Save all annotations and create a new annotated PDF? \n\nIMPORTANT: Once saved, annotations are permanently merged into the document and CANNOT be edited or deleted. To make changes, you would need to re-upload the original document.')) {
            try {
                // Show loading state
                const saveBtn = document.getElementById('save-annotations-btn');
                const originalText = saveBtn.innerHTML;
                saveBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';
                saveBtn.disabled = true;

                this.finalizeAllAnnotationDimensions();

                // Snapshot text/signature overlays as images so the baked PDF matches the preview.
                await this.rasterizeTextAnnotationsForSave();

                // Prepare annotations data - include all annotations
                const annotationsData = [];
                Object.keys(this.annotations).forEach(pageNum => {
                    this.annotations[pageNum].forEach(ann => {
                        const hasRaster = !!(ann.rasterData);
                        annotationsData.push({
                            page_number: parseInt(pageNum, 10),
                            annotation_type: hasRaster ? 'image' : ann.annotation_type,
                            content: hasRaster ? (ann.rasterData || ann.content) : ann.content,
                            imageData: hasRaster ? ann.rasterData : (ann.imageData || null),
                            htmlContent: hasRaster ? null : (ann.htmlContent || ann.content),
                            x_position: parseFloat(ann.x_position),
                            y_position: parseFloat(ann.y_position),
                            width: ann.width ? parseFloat(ann.width) : null,
                            height: ann.height ? parseFloat(ann.height) : null,
                            style_data: Object.assign({}, ann.style_data || {}, {
                                rasterizedFromText: hasRaster,
                            }),
                        });
                    });
                });

                // Remove duplicates safety check
                const uniqueAnnotations = [];
                const seen = new Set();
                annotationsData.forEach(ann => {
                    const key = `${ann.page_number}_${ann.x_position}_${ann.y_position}_${String(ann.content || '').substring(0, 50)}`;
                    if (!seen.has(key)) {
                        seen.add(key);
                        uniqueAnnotations.push(ann);
                    }
                });

                const scaleInput = document.getElementById('save-viewer-scale');
                if (scaleInput) {
                    scaleInput.value = String(this.scale || 1.5);
                }

                document.getElementById('save-annotations-data').value = JSON.stringify(uniqueAnnotations);
                document.getElementById('save-pdf-pages-data').value = JSON.stringify([]);

                // Submit form
                const form = document.getElementById('save-annotations-form');
                form.submit();

            } catch (error) {
                console.error('Error saving annotations:', error);
                alert('Failed to save annotations. Please try again.');
                const saveBtn = document.getElementById('save-annotations-btn');
                if (saveBtn) {
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }
            }
        }
    }

    async rasterizeTextAnnotationsForSave() {
        if (typeof html2canvas !== 'function') {
            console.warn('html2canvas unavailable; falling back to server HTML rendering.');
            return;
        }

        const originalPage = this.currentPage;
        const originalSelection = new Set(this.selectedAnnotations);
        const originalEditing = this.editingAnnotation;
        const pageNumbers = Object.keys(this.annotations)
            .map((n) => parseInt(n, 10))
            .filter((n) => !Number.isNaN(n))
            .sort((a, b) => a - b);

        this.selectedAnnotations.clear();
        this.editingAnnotation = null;
        this.captureMode = true;

        try {
            for (const pageNum of pageNumbers) {
                const textAnnotations = (this.annotations[pageNum] || []).filter(
                    (ann) => ann.annotation_type === 'text'
                );

                if (textAnnotations.length === 0) {
                    continue;
                }

                await this.renderPage(pageNum);
                await new Promise((resolve) => {
                    requestAnimationFrame(() => requestAnimationFrame(resolve));
                });
                // Allow signature images inside overlays to settle.
                await new Promise((resolve) => setTimeout(resolve, 50));

                for (const ann of textAnnotations) {
                    const annotationId = 'annotation-' + this.getAnnotationId(ann);
                    const overlay = (this.annotationDivs && this.annotationDivs.get(annotationId))
                        || document.getElementById(annotationId);

                    if (!overlay) {
                        continue;
                    }

                    overlay.classList.remove('is-selected', 'is-editing', 'is-dragging');
                    overlay.style.border = '1px solid #000';
                    overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.92)';
                    overlay.style.boxShadow = 'none';

                    try {
                        const canvas = await html2canvas(overlay, {
                            backgroundColor: '#ffffff',
                            scale: 2,
                            logging: false,
                            useCORS: true,
                        });
                        ann.rasterData = canvas.toDataURL('image/png');
                        ann.x_position = parseFloat(overlay.style.left) || ann.x_position;
                        ann.y_position = parseFloat(overlay.style.top) || ann.y_position;
                        ann.width = overlay.offsetWidth || ann.width;
                        ann.height = overlay.offsetHeight || ann.height;
                    } catch (rasterError) {
                        console.warn('Could not rasterize annotation overlay; server will render HTML fallback.', rasterError);
                    }
                }
            }
        } finally {
            this.captureMode = false;
            this.selectedAnnotations = originalSelection;
            this.editingAnnotation = originalEditing;
            if (originalPage) {
                await this.renderPage(originalPage);
            }
        }
    }
}

// Initialize when page is ready
if (typeof window.PDFAnnotator === 'undefined') {
    window.PDFAnnotator = PDFAnnotator;
}
