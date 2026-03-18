@extends('layouts.lab.layout.app', ['dataTable' => false, 'select2' => false, 'datePicker' => false])

@section('title2')
    <title>Annotate PDF - {{ $attachment->title ?? 'PDF Document' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        #pdf-annotation-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #pdf-toolbar {
            background: #2c3e50;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        #pdf-main {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        #pdf-viewer-section {
            flex: 1;
            overflow: auto;
            background: #ecf0f1;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        #pdf-canvas-wrapper {
            margin: 20px;
            position: relative;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #pdf-canvas,
        #annotation-canvas {
            display: block;
        }

        #annotation-canvas {
            position: absolute;
            top: 0;
            left: 0;
            cursor: crosshair;
        }

        #pdf-canvas-wrapper .pdf-annotation-overlay {
            position: absolute;
            top: 0;
            left: 0;
        }

        #annotations-sidebar {
            width: 300px;
            background: white;
            border-left: 1px solid #bdc3c7;
            overflow-y: auto;
            padding: 15px;
        }

        .annotation-item {
            background: #ecf0f1;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .annotation-item:hover {
            background: #d5dbdb;
        }

        .annotation-item .badge {
            margin-right: 5px;
        }

        .btn-group-sm {
            margin: 0 5px;
        }

        .active-tool {
            background: #3498db !important;
            color: white !important;
        }

        #pdf-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        #textAnnotationModal .modal-body {
            padding: 20px;
        }

        #annotation-text-editor {
            min-height: 300px;
        }

        .pdf-annotation-overlay {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            overflow: visible !important;
            white-space: normal !important;
            border: 1px solid #000 !important;
            /* Issue 2: Black thin border */
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 2px;
        }

        .pdf-annotation-overlay p {
            margin: 0;
            padding: 0;
        }

        .pdf-annotation-overlay * {
            max-width: 100%;
            overflow: visible;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
@endsection

@section('content2')
    <div id="pdf-annotation-container">
        <!-- Toolbar -->
        <div id="pdf-toolbar">
            <div>
                <h5 class="mb-0">
                    <i class="mdi mdi-file-pdf"></i> {{ $attachment->title ?? 'PDF Document' }}
                </h5>
                @if(isset($batch))
                    <div class="small text-muted mt-1">
                        <a href="{{ route('view-batch-details', ['batch' => $batch->id]) }}" class="text-light">
                            <i class="mdi mdi-chevron-left"></i> Back to Batch {{ $batch->batch_code ?? $batch->id }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="d-flex align-items-center">
                <!-- Page Navigation -->
                <div class="btn-group btn-group-sm mr-3">
                    <button type="button" class="btn btn-outline-light" id="pdf-prev-page" title="Previous Page">
                        <i class="mdi mdi-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-outline-light disabled" disabled>
                        <span id="pdf-page-num">1</span> / <span id="pdf-page-count">-</span>
                    </button>
                    <button type="button" class="btn btn-outline-light" id="pdf-next-page" title="Next Page">
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                </div>

                <!-- Annotation Tools -->
                <div class="btn-group btn-group-sm mr-3">
                    <button type="button" class="btn btn-outline-light annotation-tool" data-tool="text"
                        title="Add Text Comment">
                        <i class="mdi mdi-comment-text"></i> Text
                    </button>
                    <button type="button" class="btn btn-outline-light annotation-tool" data-tool="image" title="Add Image">
                        <i class="mdi mdi-image"></i> Image
                    </button>
                    <button type="button" class="btn btn-outline-warning" id="edit-annotation-btn"
                        title="Edit Selected Annotation" disabled>
                        <i class="mdi mdi-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="delete-annotations-btn"
                        title="Delete Selected Annotations" disabled>
                        <i class="mdi mdi-delete"></i> Delete
                    </button>
                </div>

                <!-- Actions -->
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-success" id="save-annotations-btn">
                        <i class="mdi mdi-content-save"></i> Save PDF
                    </button>
                    <a href="{{ route('view-batch-details', $attachment->batch_id) }}" class="btn btn-secondary">
                        <i class="mdi mdi-close"></i> Close
                    </a>
                </div>

                <input type="file" id="annotation-image-input" accept="image/*" style="display: none;">
            </div>
        </div>

        <!-- Main Content Area -->
        <div id="pdf-main">
            <!-- PDF Viewer -->
            <div id="pdf-viewer-section">
                <div id="pdf-canvas-wrapper">
                    <canvas id="pdf-canvas"></canvas>
                    <canvas id="annotation-canvas"></canvas>
                </div>
                <div id="pdf-loading">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading PDF...</span>
                    </div>
                    <p class="mt-3">Loading PDF...</p>
                </div>
            </div>

            <!-- Annotations Sidebar -->
            <div id="annotations-sidebar">
                <h6 class="mb-3"><i class="mdi mdi-auto-fix"></i> Smart-Assist Annotation</h6>
                <div class="form-group mb-3">
                    <label>Report Type</label>
                    <select class="form-control form-control-sm" id="report-type-select">
                        <option value="">Select Type</option>
                        <option value="table">Table Report (Default)</option>
                        <option value="graph">Graph Report</option>
                    </select>
                </div>

                <div id="smart-annotation-form" style="display: none;">
                    <div class="form-group mb-2">
                        <label class="small text-muted font-weight-bold">Vet Remarks</label>
                        <textarea class="form-control form-control-sm" id="smart-vet-remarks" rows="3" placeholder="Enter remarks..."></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small text-muted font-weight-bold">Owner Name</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $user->name ?? '' }}" readonly>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small text-muted font-weight-bold">Date</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ date('Y-m-d') }}" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small text-muted font-weight-bold">Signature</label>
                        @if(isset($signatureUrl))
                            <div class="border p-1 bg-light text-center">
                                <img src="{{ $signatureUrl }}" alt="Signature" id="smart-signature-img" style="max-height: 40px; max-width: 100%;">
                            </div>
                            <input type="hidden" id="smart-signature-url" value="{{ $signatureUrl }}">
                        @else
                            <div class="alert alert-warning p-1 small mb-0">No signature found in your profile.</div>
                        @endif
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-block btn-sm" id="apply-smart-annotation-btn">
                        Apply to PDF
                    </button>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">After applying, you can drag the block to adjust.</small>
                </div>

                <hr>

                <h6 class="mb-3 mt-3"><i class="mdi mdi-format-list-bulleted"></i> Manual Annotations</h6>
                <div id="annotations-list">
                    <p class="text-muted small">Click "Text" or "Image" above to add manual annotations.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Text Annotation Modal with TinyMCE -->
    <div class="modal fade" id="textAnnotationModal" tabindex="-1" role="dialog" aria-labelledby="textAnnotationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="textAnnotationModalLabel">Enter Annotation Text</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea id="annotation-text-editor" rows="10" style="width: 100%;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-text-annotation-btn">Save Annotation</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Saving -->
    <form id="save-annotations-form" method="POST" action="{{ route('save-annotated-pdf') }}">
        @csrf
        <input type="hidden" name="attachment_id" value="{{ $attachment->id }}">
        <input type="hidden" name="annotations_data" id="save-annotations-data">
        <input type="hidden" name="pdf_pages_data" id="save-pdf-pages-data">
    </form>

    <!-- TinyMCE CDN -->
    <script type="text/javascript" src="/tinymce/tinymce.min.js"></script>

    <!-- html2canvas for capturing HTML overlays -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- interact.js for draggable elements -->
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>

    <!-- PDF Annotation Script -->
    <script src="{{ asset('js/pdf-annotator.js') }}"></script>
    <script>
        // Initialize PDF Annotator
        const pdfAnnotator = new PDFAnnotator({
            pdfUrl: '{{ $attachment->attachment_url }}',
            attachmentId: {{ $attachment->id }},
            canvasId: 'pdf-canvas',
            annotationCanvasId: 'annotation-canvas',
            getAnnotationsUrl: '{{ route('get-pdf-annotations', $attachment->id) }}',
            deleteAnnotationsUrl: '{{ route('delete-pdf-annotations', $attachment->id) }}'
        });

        // Initialize TinyMCE for annotation text editor
        let annotationEditor = null;

        function initAnnotationEditor() {
            if (typeof tinymce !== 'undefined') {
                // Remove existing instance if any
                if (tinymce.get('annotation-text-editor')) {
                    tinymce.get('annotation-text-editor').remove();
                }

                tinymce.init({
                    selector: '#annotation-text-editor',
                    height: 300,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image | help',
                    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
                    images_upload_handler: function (blobInfo, success, failure, progress) {
                        var xhr, formData;

                        xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', '{{ route('upload-annotation-image') }}');

                        // Set headers
                        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                        xhr.onload = function() {
                            var json;

                            if (xhr.status != 200) {
                                failure('HTTP Error: ' + xhr.status);
                                return;
                            }

                            try {
                                json = JSON.parse(xhr.responseText);

                                if (!json || typeof json.location != 'string') {
                                    failure('Invalid JSON: ' + xhr.responseText);
                                    return;
                                }

                                success(json.location);
                            } catch (e) {
                                failure('JSON parse error: ' + xhr.responseText);
                            }
                        };

                        xhr.onerror = function () {
                            failure('Image upload failed due to a network error.');
                        };

                        formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());

                        xhr.send(formData);
                    },
                    automatic_uploads: true,
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true,
                    setup: function (editor) {
                        annotationEditor = editor;
                    }
                });
            }
        }

        // Initialize on page load (only once)
        let initialized = false;

        function setupPdfAnnotatorPage() {
            if (!initialized) {
                pdfAnnotator.init();
                initialized = true;
            }

            // Wait a bit for TinyMCE to be available
            setTimeout(function () {
                initAnnotationEditor();
            }, 500);

            // Setup Smart Annotation UI logic
            const reportTypeSelect = document.getElementById('report-type-select');
            const smartForm = document.getElementById('smart-annotation-form');
            const applyBtn = document.getElementById('apply-smart-annotation-btn');
            const wrapper = document.getElementById('pdf-canvas-wrapper');

            // Default to "table" if nothing selected
            if (reportTypeSelect && !reportTypeSelect.value) {
                reportTypeSelect.value = 'table';
            }
            if (smartForm) {
                smartForm.style.display = 'block';
            }

            let smartBlockOverlay = null;
            const SMART_FONT_SIZE = 8; // small bump for readability
            const SMART_LINE_HEIGHT = 1.35;
            const SMART_PADDING = 6;
            const SMART_SIDE_MARGIN = 20;
            const SMART_SECTION_GAP_PX = 28;

            reportTypeSelect.addEventListener('change', function() {
                if (this.value === 'table') {
                    smartForm.style.display = 'block';
                } else {
                    smartForm.style.display = 'none';
                    if (smartBlockOverlay) {
                        smartBlockOverlay.remove();
                        smartBlockOverlay = null;
                    }
                }
            });

            applyBtn.addEventListener('click', function() {
                const remarks = document.getElementById('smart-vet-remarks').value;
                const signatureUrl = document.getElementById('smart-signature-url') ? document.getElementById('smart-signature-url').value : null;
                const ownerName = "{{ $user->name ?? '' }}";
                const dateVal = "{{ date('Y-m-d') }}";
                const reportType = reportTypeSelect ? reportTypeSelect.value : 'table';

                if (smartBlockOverlay) {
                    smartBlockOverlay.remove();
                }

                // Create the draggable overlay block
                smartBlockOverlay = document.createElement('div');
                smartBlockOverlay.id = 'smart-annotation-block';
                smartBlockOverlay.className = 'smart-annotation-block';
                smartBlockOverlay.style.position = 'absolute';
                // Use (almost) full width of the PDF canvas wrapper
                const wrapperWidth = wrapper ? wrapper.clientWidth : 540;
                const fullWidth = Math.max(280, wrapperWidth - (SMART_SIDE_MARGIN * 2));
                const blockWidth = reportType === 'graph'
                    ? Math.max(220, Math.floor(fullWidth / 3.5))
                    : fullWidth;
                smartBlockOverlay.style.left = `${SMART_SIDE_MARGIN}px`;
                smartBlockOverlay.style.top = '700px';
                smartBlockOverlay.style.width = `${blockWidth}px`;
                smartBlockOverlay.style.border = '2px dashed #3498db';
                smartBlockOverlay.style.backgroundColor = 'rgba(255,255,255,0.9)';
                smartBlockOverlay.style.padding = `${SMART_PADDING}px`;
                smartBlockOverlay.style.zIndex = '100';
                smartBlockOverlay.style.cursor = 'move';
                smartBlockOverlay.style.fontFamily = 'Arial, sans-serif';
                smartBlockOverlay.style.fontSize = `${SMART_FONT_SIZE}px`;
                smartBlockOverlay.style.lineHeight = `${SMART_LINE_HEIGHT}`;

                // Store data in dataset for extraction later
                smartBlockOverlay.dataset.remarks = remarks;
                smartBlockOverlay.dataset.owner = ownerName;
                smartBlockOverlay.dataset.date = dateVal;
                smartBlockOverlay.dataset.signatureUrl = signatureUrl;
                smartBlockOverlay.dataset.reportType = reportType;

                let sigHtml = signatureUrl ? `<img src="${signatureUrl}" style="max-height: 40px;">` : ``;

                smartBlockOverlay.innerHTML = `
                    <div class="smart-comments" style="font-size: ${SMART_FONT_SIZE}pt; line-height: ${SMART_LINE_HEIGHT}; margin-bottom: ${SMART_SECTION_GAP_PX}px;">
                        <strong>Comments by vet:</strong><br>
                        ${remarks.replace(/\n/g, '<br>')}
                    </div>
                    <table class="smart-footer" style="width: 100%; border: none; font-size: ${SMART_FONT_SIZE}pt; line-height: ${SMART_LINE_HEIGHT};">
                        <tr>
                            <td style="width: 50%; vertical-align: bottom;">${ownerName}</td>
                            <td style="width: 50%; vertical-align: bottom;">
                                <div>
                                    <strong>Signature:</strong> ${sigHtml}<br>
                                    <strong>Date:</strong> ${dateVal}
                                </div>
                            </td>
                        </tr>
                    </table>
                `;

                wrapper.appendChild(smartBlockOverlay);

                // Make draggable
                interact(smartBlockOverlay).draggable({
                    listeners: {
                        move(event) {
                            const target = event.target;
                            // keep the dragged position in the data-x/data-y attributes
                            const x = (parseFloat(target.dataset.x) || 0) + event.dx;
                            const y = (parseFloat(target.dataset.y) || 0) + event.dy;

                            // translate the element
                            target.style.transform = `translate(${x}px, ${y}px)`;

                            // update the posiion attributes
                            target.dataset.x = x;
                            target.dataset.y = y;
                        }
                    }
                });
            });

            // Intercept Save Button to convert smart block to actual FPDF annotations
            const originalSaveBtn = document.getElementById('save-annotations-btn');
            originalSaveBtn.addEventListener('click', function(e) {
                if (smartBlockOverlay) {
                    // Calculate final X and Y
                    const rect = smartBlockOverlay.getBoundingClientRect();
                    const wrapperRect = wrapper.getBoundingClientRect();
                    
                    const finalX = rect.left - wrapperRect.left;
                    const finalY = rect.top - wrapperRect.top;
                    const finalW = rect.width;
                    const finalH = rect.height;

                    // Compute dynamic layout so long comments don't overlap footer
                    const commentsEl = smartBlockOverlay.querySelector('.smart-comments');
                    const footerEl = smartBlockOverlay.querySelector('.smart-footer');
                    const commentsH = commentsEl ? commentsEl.getBoundingClientRect().height : 0;
                    const footerH = footerEl ? footerEl.getBoundingClientRect().height : 0;
                    const gap = SMART_SECTION_GAP_PX;

                    const remarks = smartBlockOverlay.dataset.remarks;
                    const owner = smartBlockOverlay.dataset.owner;
                    const date = smartBlockOverlay.dataset.date;
                    const sigUrl = smartBlockOverlay.dataset.signatureUrl;
                    const reportType = smartBlockOverlay.dataset.reportType || 'table';

                    // Ensure page array exists
                    if (!pdfAnnotator.annotations[pdfAnnotator.currentPage]) {
                        pdfAnnotator.annotations[pdfAnnotator.currentPage] = [];
                    }

                    // Add a faint border around the whole smart block only for Graph reports (baked PDF)
                    if (reportType === 'graph') {
                        pdfAnnotator.annotations[pdfAnnotator.currentPage].push({
                            uniqueId: 'new_smart_border',
                            page_number: pdfAnnotator.currentPage,
                            annotation_type: 'text',
                            content: '',
                            htmlContent: '',
                            x_position: finalX,
                            y_position: finalY,
                            width: Math.max(50, finalW),
                            height: Math.max(30, finalH),
                            style_data: { borderOnly: true }
                        });
                    }

                    // Add Remarks Text
                    pdfAnnotator.annotations[pdfAnnotator.currentPage].push({
                        uniqueId: 'new_smart_remarks',
                        page_number: pdfAnnotator.currentPage,
                        annotation_type: 'text',
                        content: `<div style="font-size: ${SMART_FONT_SIZE}pt; line-height: ${SMART_LINE_HEIGHT};"><strong>Comments by vet:</strong><br>${remarks.replace(/\n/g, '<br>')}</div>`,
                        htmlContent: `<div style="font-size: ${SMART_FONT_SIZE}pt; line-height: ${SMART_LINE_HEIGHT};"><strong>Comments by vet:</strong><br>${remarks.replace(/\n/g, '<br>')}</div>`,
                        x_position: finalX + SMART_PADDING,
                        y_position: finalY + SMART_PADDING,
                        width: Math.max(100, finalW - (SMART_PADDING * 2)),
                        height: Math.max(30, commentsH),
                        style_data: { fontSize: SMART_FONT_SIZE, color: '#000', noBorder: true }
                    });

                    // Add Owner Text
                    pdfAnnotator.annotations[pdfAnnotator.currentPage].push({
                        uniqueId: 'new_smart_owner',
                        page_number: pdfAnnotator.currentPage,
                        annotation_type: 'text',
                        content: `<div style="font-size: ${SMART_FONT_SIZE}pt; line-height: ${SMART_LINE_HEIGHT};">${owner}</div>`,
                        htmlContent: `<div style="font-size: ${SMART_FONT_SIZE}pt; line-height: ${SMART_LINE_HEIGHT};">${owner}</div>`,
                        x_position: finalX + SMART_PADDING,
                        y_position: finalY + SMART_PADDING + commentsH + gap,
                        width: Math.max(80, (finalW / 2) - SMART_PADDING),
                        height: Math.max(18, footerH),
                        style_data: { fontSize: SMART_FONT_SIZE, color: '#000', noBorder: true }
                    });

                    // Add Signature Image (if exists)
                    if (sigUrl) {
                        pdfAnnotator.annotations[pdfAnnotator.currentPage].push({
                            uniqueId: 'new_smart_sig',
                            page_number: pdfAnnotator.currentPage,
                            annotation_type: 'image',
                            imageData: sigUrl,
                            content: sigUrl,
                            x_position: finalX + (finalW / 2) + 65,
                            y_position: finalY + SMART_PADDING + commentsH + gap - 2,
                            width: 70,
                            height: 30
                        });
                    }

                    // Add Date & Label Text
                    pdfAnnotator.annotations[pdfAnnotator.currentPage].push({
                        uniqueId: 'new_smart_date',
                        page_number: pdfAnnotator.currentPage,
                        annotation_type: 'text',
                        content: `<div style="font-size: ${SMART_FONT_SIZE}pt; line-height: ${SMART_LINE_HEIGHT};"><strong>Signature:</strong><br><strong>Date:</strong> ${date}</div>`,
                        htmlContent: `<div style="font-size: ${SMART_FONT_SIZE}pt; line-height: ${SMART_LINE_HEIGHT};"><strong>Signature:</strong><br><strong>Date:</strong> ${date}</div>`,
                        x_position: finalX + (finalW / 2),
                        y_position: finalY + SMART_PADDING + commentsH + gap,
                        width: Math.max(80, (finalW / 2) - SMART_PADDING),
                        height: Math.max(18, footerH),
                        style_data: { fontSize: SMART_FONT_SIZE, color: '#000', noBorder: true }
                    });

                    // Remove the DOM element so it doesn't get captured by html2canvas if pdfAnnotator starts using it globally later
                    smartBlockOverlay.remove();
                    smartBlockOverlay = null;
                }
                
                // Note: we don't prevent default, we just injected the annotations
                // right before pdfAnnotator's save logic executes (since we added another event listener on the same button, wait, pdfAnnotator sets its click listener in setupEventListeners). 
                // Wait! If pdfAnnotator sets its listener *during* init(), and our DOMContentLoaded fires *after* its setup, our event listener might fire AFTER pdfAnnotator's! 
                // Standard addEventListener fires in the order they are attached. 
                // To guarantee we fire first, we can override pdfAnnotator.saveAnnotations.
            });

            // Override pdfAnnotator save method
            const originalSave = pdfAnnotator.saveAnnotations;
            pdfAnnotator.saveAnnotations = function() {
                if (smartBlockOverlay) {
                    originalSaveBtn.click(); // Trigger our conversion logic above (actually wait, better to just call it)
                }
                originalSave.apply(pdfAnnotator);
            };

            // Initialize modal event handlers (using vanilla JS or jQuery if available)
            const modal = document.getElementById('textAnnotationModal');
            const saveBtn = document.getElementById('save-text-annotation-btn');

            if (modal) {
                // Handle modal close (Bootstrap 4/5)
                modal.addEventListener('hidden.bs.modal', function () {
                    // Clear editor content when modal is closed
                    if (annotationEditor) {
                        annotationEditor.setContent('');
                    }
                    // Reset callback
                    pdfAnnotator.textAnnotationCallback = null;
                });

                // Fallback for older Bootstrap or if event doesn't fire
                modal.addEventListener('hidden', function () {
                    if (annotationEditor) {
                        annotationEditor.setContent('');
                    }
                    pdfAnnotator.textAnnotationCallback = null;
                });
            }

            // Save button handler
            if (saveBtn) {
                saveBtn.addEventListener('click', function () {
                    if (annotationEditor) {
                        const content = annotationEditor.getContent();
                        // Get plain text version (strip HTML tags for display)
                        const textContent = content.replace(/<[^>]*>/g, '').trim();

                        if (textContent && pdfAnnotator.textAnnotationCallback) {
                            pdfAnnotator.textAnnotationCallback(textContent, content);
                            // Hide modal (try jQuery first, then vanilla JS)
                            if (typeof $ !== 'undefined' && $('#textAnnotationModal').modal) {
                                $('#textAnnotationModal').modal('hide');
                            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                const bsModal = bootstrap.Modal.getInstance(modal);
                                if (bsModal) bsModal.hide();
                            } else {
                                modal.style.display = 'none';
                                modal.classList.remove('show');
                                document.body.classList.remove('modal-open');
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) backdrop.remove();
                            }
                        } else if (!textContent) {
                            alert('Please enter some text for the annotation.');
                        }
                    }
                });
            }
        }

        // Run setup immediately if DOM is already ready, otherwise wait
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupPdfAnnotatorPage);
        } else {
            setupPdfAnnotatorPage();
        }
    </script>
@endsection