@extends('layouts.lab.layout.app', ['dataTable'=>false, 'select2'=>false, 'datePicker'=>false])

@section('title2')
    <title>Annotate PDF - {{ $attachment->title ?? 'PDF Document' }}</title>
    <style>
        body { margin: 0; padding: 0; overflow: hidden; }
        #pdf-annotation-container { height: 100vh; display: flex; flex-direction: column; }
        #pdf-toolbar { background: #2c3e50; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        #pdf-main { flex: 1; display: flex; overflow: hidden; }
        #pdf-viewer-section { flex: 1; overflow: auto; background: #ecf0f1; position: relative; }
        #pdf-canvas-wrapper { margin: 20px auto; position: relative; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        #pdf-canvas, #annotation-canvas { display: block; }
        #annotation-canvas { position: absolute; top: 0; left: 0; cursor: crosshair; }
        #pdf-canvas-wrapper .pdf-annotation-overlay { position: absolute; top: 0; left: 0; }
        #annotations-sidebar { width: 300px; background: white; border-left: 1px solid #bdc3c7; overflow-y: auto; padding: 15px; }
        .annotation-item { background: #ecf0f1; padding: 10px; margin-bottom: 10px; border-radius: 5px; font-size: 0.9rem; cursor: pointer; }
        .annotation-item:hover { background: #d5dbdb; }
        .annotation-item .badge { margin-right: 5px; }
        .btn-group-sm { margin: 0 5px; }
        .active-tool { background: #3498db !important; color: white !important; }
        #pdf-loading { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
        #textAnnotationModal .modal-body { padding: 20px; }
        #annotation-text-editor { min-height: 300px; }
        .pdf-annotation-overlay { 
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            overflow: visible !important;
            white-space: normal !important;
        }
        .pdf-annotation-overlay p { margin: 0; padding: 0; }
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
                <button type="button" class="btn btn-outline-light annotation-tool" data-tool="text" title="Add Text Comment">
                    <i class="mdi mdi-comment-text"></i> Text
                </button>
                <button type="button" class="btn btn-outline-light annotation-tool" data-tool="image" title="Add Image">
                    <i class="mdi mdi-image"></i> Image
                </button>
                <button type="button" class="btn btn-outline-warning" id="edit-annotation-btn" title="Edit Selected Annotation" disabled>
                    <i class="mdi mdi-pencil"></i> Edit
                </button>
                <button type="button" class="btn btn-outline-danger" id="delete-annotations-btn" title="Delete Selected Annotations" disabled>
                    <i class="mdi mdi-delete"></i> Delete
                </button>
            </div>
            
            <!-- Actions -->
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" id="save-annotations-btn">
                    <i class="mdi mdi-content-save"></i> Save PDF
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
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
            <h6 class="mb-3"><i class="mdi mdi-format-list-bulleted"></i> Annotations</h6>
            <div id="annotations-list">
                <p class="text-muted">Click "Text" or "Image" to add annotations to the PDF.</p>
            </div>
        </div>
    </div>
</div>

<!-- Text Annotation Modal with TinyMCE -->
<div class="modal fade" id="textAnnotationModal" tabindex="-1" role="dialog" aria-labelledby="textAnnotationModalLabel" aria-hidden="true">
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
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
                setup: function(editor) {
                    annotationEditor = editor;
                }
            });
        }
    }
    
    // Initialize on page load (only once)
    let initialized = false;
    document.addEventListener('DOMContentLoaded', function() {
        if (!initialized) {
            pdfAnnotator.init();
            initialized = true;
        }
        
        // Wait a bit for TinyMCE to be available
        setTimeout(function() {
            initAnnotationEditor();
        }, 500);
        
        // Initialize modal event handlers (using vanilla JS or jQuery if available)
        const modal = document.getElementById('textAnnotationModal');
        const saveBtn = document.getElementById('save-text-annotation-btn');
        
        if (modal) {
            // Handle modal close (Bootstrap 4/5)
            modal.addEventListener('hidden.bs.modal', function() {
                // Clear editor content when modal is closed
                if (annotationEditor) {
                    annotationEditor.setContent('');
                }
                // Reset callback
                pdfAnnotator.textAnnotationCallback = null;
            });
            
            // Fallback for older Bootstrap or if event doesn't fire
            modal.addEventListener('hidden', function() {
                if (annotationEditor) {
                    annotationEditor.setContent('');
                }
                pdfAnnotator.textAnnotationCallback = null;
            });
        }
        
        // Save button handler
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
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
    });
</script>
@endsection
