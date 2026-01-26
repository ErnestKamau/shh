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
        this.scale = 1.5;
        this.isDrawing = false;
        this.tempAnnotation = null;
    }

    async init() {
        try {
            // Load PDF
            const loadingTask = pdfjsLib.getDocument(this.pdfUrl);
            this.pdfDoc = await loadingTask.promise;
            this.totalPages = this.pdfDoc.numPages;

            document.getElementById('pdf-page-count').textContent = this.totalPages;
            document.getElementById('pdf-loading').style.display = 'none';

            // Load existing annotations from database
            await this.loadExistingAnnotations();

            // Render first page
            await this.renderPage(1);

            // Setup event listeners
            this.setupEventListeners();

        } catch (error) {
            console.error('Error loading PDF:', error);
            alert('Failed to load PDF. Please try again.');
        }
    }

    async loadExistingAnnotations() {
        try {
            const response = await fetch(this.getAnnotationsUrl);
            if (response.ok) {
                const data = await response.json();
                if (data.annotations && data.annotations.length > 0) {
                    // Group annotations by page
                    data.annotations.forEach(ann => {
                        if (!this.annotations[ann.page_number]) {
                            this.annotations[ann.page_number] = [];
                        }
                        this.annotations[ann.page_number].push(ann);
                    });
                }
            }
        } catch (error) {
            console.error('Error loading annotations:', error);
        }
    }

    async renderPage(pageNum) {
        try {
            const page = await this.pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: this.scale });

            // Set canvas dimensions
            this.canvas.width = viewport.width;
            this.canvas.height = viewport.height;
            this.annotationCanvas.width = viewport.width;
            this.annotationCanvas.height = viewport.height;

            // Clear canvases
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.annotationCtx.clearRect(0, 0, this.annotationCanvas.width, this.annotationCanvas.height);

            // Render PDF page
            const renderContext = {
                canvasContext: this.ctx,
                viewport: viewport
            };
            await page.render(renderContext).promise;

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
            if (ann.annotation_type === 'text') {
                this.drawTextAnnotation(ann);
            } else if (ann.annotation_type === 'image' && ann.imageData) {
                this.drawImageAnnotation(ann);
            }
        });
    }

    drawTextAnnotation(ann) {
        const ctx = this.annotationCtx;

        // Draw text box background
        ctx.fillStyle = 'rgba(255, 255, 153, 0.8)';
        ctx.fillRect(ann.x_position, ann.y_position, ann.width || 150, ann.height || 30);

        // Draw border
        ctx.strokeStyle = '#ff9800';
        ctx.lineWidth = 2;
        ctx.strokeRect(ann.x_position, ann.y_position, ann.width || 150, ann.height || 30);

        // Draw text
        ctx.fillStyle = '#000';
        ctx.font = '14px Arial';
        ctx.fillText(ann.content, ann.x_position + 5, ann.y_position + 20);
    }

    drawImageAnnotation(ann) {
        if (ann.imageData) {
            const img = new Image();
            img.onload = () => {
                this.annotationCtx.drawImage(img, ann.x_position, ann.y_position, ann.width || 100, ann.height || 100);
            };
            img.src = ann.imageData;
        }
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

        // Canvas click for annotation
        this.annotationCanvas.addEventListener('click', (e) => {
            if (!this.currentTool) {
                alert('Please select an annotation tool first (Text or Image)');
                return;
            }

            const rect = this.annotationCanvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            if (this.currentTool === 'text') {
                this.addTextAnnotation(x, y);
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

        // Canvas click for image placement
        this.annotationCanvas.addEventListener('click', (e) => {
            if (this.currentTool === 'image' && this.pendingImageData) {
                const rect = this.annotationCanvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                this.addImageAnnotation(x, y, this.pendingImageData);
                this.pendingImageData = null;
                this.currentTool = null;
                document.querySelectorAll('.annotation-tool').forEach(b => b.classList.remove('active-tool'));
            }
        });

        // Save button
        document.getElementById('save-annotations-btn').addEventListener('click', () => {
            this.saveAnnotations();
        });
    }

    addTextAnnotation(x, y) {
        const text = prompt('Enter annotation text:');
        if (!text) return;

        const annotation = {
            page_number: this.currentPage,
            annotation_type: 'text',
            content: text,
            x_position: x,
            y_position: y,
            width: 150,
            height: 30,
            style_data: { fontSize: 14, color: '#000' }
        };

        if (!this.annotations[this.currentPage]) {
            this.annotations[this.currentPage] = [];
        }
        this.annotations[this.currentPage].push(annotation);

        this.drawTextAnnotation(annotation);
        this.updateAnnotationsList();
        this.currentTool = null;
        document.querySelectorAll('.annotation-tool').forEach(b => b.classList.remove('active-tool'));
    }

    addImageAnnotation(x, y, imageData) {
        const annotation = {
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
        this.annotations[this.currentPage].push(annotation);

        this.drawImageAnnotation(annotation);
        this.updateAnnotationsList();
    }

    updateAnnotationsList() {
        const listContainer = document.getElementById('annotations-list');
        const allAnnotations = [];

        // Flatten all annotations
        Object.keys(this.annotations).forEach(pageNum => {
            this.annotations[pageNum].forEach(ann => {
                allAnnotations.push({ ...ann, page: pageNum });
            });
        });

        if (allAnnotations.length === 0) {
            listContainer.innerHTML = '<p class="text-muted">No annotations yet.</p>';
            return;
        }

        listContainer.innerHTML = allAnnotations.map((ann, idx) => `
            <div class="annotation-item">
                <div class="mb-1">
                    <span class="badge badge-${ann.annotation_type === 'text' ? 'warning' : 'info'}">
                        ${ann.annotation_type === 'text' ? 'Text' : 'Image'}
                    </span>
                    <span class="badge badge-secondary">Page ${ann.page}</span>
                </div>
                <div style="font-size: 0.85rem;">
                    ${ann.annotation_type === 'text' ? ann.content : 'Image annotation'}
                </div>
            </div>
        `).join('');
    }

    async saveAnnotations() {
        if (confirm('Save all annotations and create a new annotated PDF? This will replace the current attachment.')) {
            try {
                // Show loading state
                const saveBtn = document.getElementById('save-annotations-btn');
                const originalText = saveBtn.innerHTML;
                saveBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';
                saveBtn.disabled = true;

                // Prepare annotations data
                const annotationsData = [];
                Object.keys(this.annotations).forEach(pageNum => {
                    this.annotations[pageNum].forEach(ann => {
                        annotationsData.push({
                            page_number: pageNum,
                            annotation_type: ann.annotation_type,
                            content: ann.content,
                            x_position: ann.x_position,
                            y_position: ann.y_position,
                            width: ann.width,
                            height: ann.height,
                            style_data: ann.style_data
                        });
                    });
                });

                // Capture each page with annotations as image data
                const pdfPagesData = [];
                for (let pageNum = 1; pageNum <= this.totalPages; pageNum++) {
                    await this.renderPage(pageNum);

                    // Create a temporary canvas to merge both layers
                    const mergedCanvas = document.createElement('canvas');
                    mergedCanvas.width = this.canvas.width;
                    mergedCanvas.height = this.canvas.height;
                    const mergedCtx = mergedCanvas.getContext('2d');

                    // Draw PDF page
                    mergedCtx.drawImage(this.canvas, 0, 0);
                    // Draw annotations
                    mergedCtx.drawImage(this.annotationCanvas, 0, 0);

                    // Convert to base64
                    pdfPagesData.push({
                        page_number: pageNum,
                        image_data: mergedCanvas.toDataURL('image/jpeg', 0.95)
                    });
                }

                // Populate hidden form fields
                document.getElementById('save-annotations-data').value = JSON.stringify(annotationsData);
                document.getElementById('save-pdf-pages-data').value = JSON.stringify(pdfPagesData);

                // Submit form
                document.getElementById('save-annotations-form').submit();

            } catch (error) {
                console.error('Error saving annotations:', error);
                alert('Failed to save annotations. Please try again.');
                const saveBtn = document.getElementById('save-annotations-btn');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }
    }
}

// Initialize when page is ready
if (typeof window.PDFAnnotator === 'undefined') {
    window.PDFAnnotator = PDFAnnotator;
}
