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
        #annotations-sidebar { width: 300px; background: white; border-left: 1px solid #bdc3c7; overflow-y: auto; padding: 15px; }
        .annotation-item { background: #ecf0f1; padding: 10px; margin-bottom: 10px; border-radius: 5px; font-size: 0.9rem; }
        .annotation-item .badge { margin-right: 5px; }
        .btn-group-sm { margin: 0 5px; }
        .active-tool { background: #3498db !important; color: white !important; }
        #pdf-loading { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
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

<!-- Hidden Form for Saving -->
<form id="save-annotations-form" method="POST" action="{{ route('save-annotated-pdf') }}">
    @csrf
    <input type="hidden" name="attachment_id" value="{{ $attachment->id }}">
    <input type="hidden" name="annotations_data" id="save-annotations-data">
    <input type="hidden" name="pdf_pages_data" id="save-pdf-pages-data">
</form>

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
        getAnnotationsUrl: '{{ route('get-pdf-annotations', $attachment->id) }}'
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        pdfAnnotator.init();
    });
</script>
@endsection
