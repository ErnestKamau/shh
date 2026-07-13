@extends('layouts.lab.layout.app', ['dataTable' => false, 'select2' => false, 'datePicker' => false])

@section('hide_password_expiry_banner')
@endsection

@section('title2')
    <title>Annotate PDF - {{ $attachment->title ?? 'PDF Document' }}</title>
    <style>
        body.pdf-annotate-page {
            overflow: hidden;
        }

        body.pdf-annotate-page #main-container-body {
            display: flex;
            flex-direction: column;
            height: calc(100vh - var(--app-header-height, 56px));
            padding: 0 !important;
            overflow: hidden !important;
        }

        #pdf-annotation-container {
            flex: 1;
            min-height: 0;
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
            flex-shrink: 0;
        }

        #pdf-main {
            flex: 1;
            display: flex;
            overflow: hidden;
            min-height: 0;
            min-width: 0;
        }

        #pdf-viewer-section {
            flex: 1;
            min-width: 0;
            overflow: auto;
            background: #ecf0f1;
            position: relative;
            display: block;
            padding: 8px;
            box-sizing: border-box;
        }

        #pdf-canvas-wrapper {
            margin: 0 auto 8px;
            position: relative;
            display: block;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            box-sizing: border-box;
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
            flex-shrink: 0;
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
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            line-height: 1.4;
            overflow: visible !important;
            white-space: normal !important;
            border: 1px solid #000;
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: 2px;
            cursor: grab;
            user-select: none;
        }

        .pdf-annotation-overlay.is-dragging {
            cursor: grabbing;
            opacity: 0.92;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
            z-index: 30 !important;
        }

        .pdf-annotation-overlay.is-selected {
            border: 2px solid #ff5722 !important;
            background-color: rgba(255, 200, 100, 0.12);
        }

        .pdf-annotation-overlay.is-editing {
            border: 2px solid #4caf50 !important;
            background-color: rgba(200, 255, 200, 0.12);
        }

        .pdf-annotation-overlay .annotation-signature-block {
            width: 280px;
            max-width: 280px;
            box-sizing: border-box;
        }

        .pdf-annotation-overlay .annotation-signature-block img {
            max-height: 48px;
            max-width: 160px;
            height: auto;
            width: auto;
            display: block;
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
                        <a href="{{ route('view-batch-details', ['batch' => $batch->id]) }}#attachments" class="text-light">
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
                    <a href="{{ route('view-batch-details', $attachment->batch_id) }}#attachments" class="btn btn-secondary">
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
                <h6 class="mb-3"><i class="mdi mdi-draw-pen"></i> Signing</h6>
                <div class="alert alert-light border small mb-3" style="border-radius: 8px;">
                    To sign this PDF, click <strong>Text</strong> in the toolbar, then use
                    <strong>Insert Signature</strong> or <strong>Sign Block</strong> in the editor.
                    Drag any annotation on the page to reposition it before saving.
                </div>

                <h6 class="mb-3"><i class="mdi mdi-format-list-bulleted"></i> Manual Annotations</h6>
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
                    <p class="text-muted small mb-0 mt-2">
                        Use <strong>Insert Signature</strong> or <strong>Sign Block</strong> in the editor toolbar to sign this annotation.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-text-annotation-btn">Save Annotation</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Draw / Upload Signature for TinyMCE -->
    <div class="modal fade" id="annotationSignaturePadModal" tabindex="-1" role="dialog"
        aria-labelledby="annotationSignaturePadModalLabel" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="annotationSignaturePadModalLabel">
                        <i class="mdi mdi-draw"></i> Add Signature
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="draw-signature-tab" data-toggle="tab" href="#draw-signature-pane" role="tab">Draw</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="upload-signature-tab" data-toggle="tab" href="#upload-signature-pane" role="tab">Upload</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="draw-signature-pane" role="tabpanel">
                            <div class="border rounded bg-white" style="touch-action: none;">
                                <canvas id="annotation-signature-canvas" width="520" height="180" style="width: 100%; height: 180px; display: block;"></canvas>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">Draw your signature above</small>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-annotation-signature-pad">
                                    <i class="mdi mdi-eraser"></i> Clear
                                </button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="upload-signature-pane" role="tabpanel">
                            <input type="file" id="annotation-signature-upload" class="form-control-file" accept="image/*">
                            <div class="mt-3 text-center border rounded p-2 bg-light" id="annotation-signature-upload-preview-wrap" style="display:none;">
                                <img id="annotation-signature-upload-preview" alt="Signature preview" style="max-height: 120px; max-width: 100%;">
                            </div>
                            <small class="text-muted d-block mt-2">PNG or JPG with a transparent or white background works best.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="use-annotation-signature-btn">
                        <i class="mdi mdi-check"></i> Use Signature
                    </button>
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
        <input type="hidden" name="viewer_scale" id="save-viewer-scale" value="1.5">
    </form>

    <!-- TinyMCE CDN -->
    <script type="text/javascript" src="/tinymce/tinymce.min.js"></script>

    <!-- Signature pad for TinyMCE signing -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

    <!-- html2canvas for capturing HTML overlays -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>

    <!-- PDF Annotation Script -->
    <script src="{{ asset('js/pdf-annotator.js') }}?v=20260713b"></script>
    <script>
        // Initialize PDF Annotator
        const pdfAnnotator = new PDFAnnotator({
            pdfUrl: @json(url($attachment->attachment_url)),
            attachmentId: @json($attachment->id),
            canvasId: 'pdf-canvas',
            annotationCanvasId: 'annotation-canvas',
            getAnnotationsUrl: @json(route('get-pdf-annotations', $attachment->id)),
            deleteAnnotationsUrl: @json(route('delete-pdf-annotations', $attachment->id))
        });

        // Initialize TinyMCE for annotation text editor
        let annotationEditor = null;
        let annotationSignaturePad = null;
        let annotationSignaturePadMode = 'image'; // 'image' | 'block'
        let annotationSignaturePadTarget = 'tinymce'; // 'tinymce' | 'smart'
        let annotationUploadedSignatureDataUrl = null;
        const annotationSignerName = @json($user->name ?? '');
        const annotationSignerDate = @json(date('Y-m-d'));
        const annotationProfileSignatureUrl = @json($signatureUrl ?? null);

        function buildSignatureImageHtml(signatureUrl) {
            if (!signatureUrl) {
                return '';
            }

            return '<img src="' + signatureUrl + '" alt="Signature" style="max-height: 48px; max-width: 160px; height: auto; width: auto; display: block;" />';
        }

        function buildSignatureBlockHtml(signatureUrl) {
            const signatureHtml = signatureUrl
                ? buildSignatureImageHtml(signatureUrl)
                : '<em style="color:#999;">[signature]</em>';

            return ''
                + '<div class="annotation-signature-block" data-signature-block="1" style="width:280px; max-width:280px; box-sizing:border-box; font-family:\'Times New Roman\', Times, serif; font-size:11pt; line-height:1.35; color:#000;">'
                + '<div style="margin-bottom:6px;"><strong>Signed by:</strong> ' + (annotationSignerName || '—') + '</div>'
                + '<table style="width:100%; border:none; border-collapse:collapse; table-layout:fixed;">'
                + '<tr>'
                + '<td style="width:62%; vertical-align:bottom; padding:0;">'
                + '<div style="font-size:10pt; margin-bottom:2px;"><strong>Signature:</strong></div>'
                + signatureHtml
                + '</td>'
                + '<td style="width:38%; vertical-align:bottom; padding:0 0 0 8px; white-space:nowrap;">'
                + '<div style="font-size:10pt;"><strong>Date:</strong><br>' + annotationSignerDate + '</div>'
                + '</td>'
                + '</tr>'
                + '</table>'
                + '</div>';
        }

        function insertSignatureContent(editor, mode, signatureUrl) {
            if (!editor) {
                return;
            }

            const html = mode === 'block'
                ? buildSignatureBlockHtml(signatureUrl)
                : buildSignatureImageHtml(signatureUrl);

            if (!html) {
                editor.notificationManager.open({
                    text: 'No signature image available.',
                    type: 'warning',
                    timeout: 3000
                });
                return;
            }

            editor.insertContent(html);
            editor.focus();
        }

        function openAnnotationSignaturePad(mode, target) {
            annotationSignaturePadMode = mode || 'image';
            annotationSignaturePadTarget = target || 'tinymce';
            annotationUploadedSignatureDataUrl = null;

            const uploadInput = document.getElementById('annotation-signature-upload');
            const previewWrap = document.getElementById('annotation-signature-upload-preview-wrap');
            const previewImg = document.getElementById('annotation-signature-upload-preview');
            if (uploadInput) {
                uploadInput.value = '';
            }
            if (previewWrap) {
                previewWrap.style.display = 'none';
            }
            if (previewImg) {
                previewImg.removeAttribute('src');
            }

            const modal = document.getElementById('annotationSignaturePadModal');
            if (typeof $ !== 'undefined' && $('#annotationSignaturePadModal').modal) {
                $('#annotationSignaturePadModal').modal('show');
            } else if (modal) {
                modal.style.display = 'block';
                modal.classList.add('show');
            }

            setTimeout(function() {
                initAnnotationSignaturePad();
            }, 250);
        }

        function initAnnotationSignaturePad() {
            const canvas = document.getElementById('annotation-signature-canvas');
            if (!canvas || typeof SignaturePad === 'undefined') {
                return;
            }

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const width = canvas.offsetWidth || 520;
            const height = 180;
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            if (annotationSignaturePad) {
                annotationSignaturePad.clear();
            } else {
                annotationSignaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255,255,255)',
                    penColor: 'rgb(0,0,0)'
                });
            }

            annotationSignaturePad.clear();
        }

        function initAnnotationEditor() {
            if (typeof tinymce !== 'undefined') {
                // Remove existing instance if any
                if (tinymce.get('annotation-text-editor')) {
                    tinymce.get('annotation-text-editor').remove();
                }

                tinymce.init({
                    selector: '#annotation-text-editor',
                    height: 340,
                    menubar: false,
                    branding: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen textcolor',
                        'insertdatetime media table paste code help wordcount hr'
                    ],
                    toolbar: [
                        'undo redo | fontselect fontsizeselect | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify',
                        'bullist numlist outdent indent | hr | image insertsignature insertsignblock signmenu | removeformat | code help'
                    ],
                    font_formats: 'Times New Roman=Times New Roman,Times,serif;Arial=Arial,Helvetica,sans-serif;Courier New=Courier New,Courier,monospace',
                    fontsize_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 24pt',
                    content_style: 'body { font-family: "Times New Roman", Times, serif; font-size: 12pt; } img { max-width: 100%; height: auto; }',
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

                        editor.ui.registry.addButton('insertsignature', {
                            text: 'Insert Signature',
                            tooltip: 'Insert your electronic signature image',
                            icon: 'image',
                            onAction: function () {
                                if (annotationProfileSignatureUrl) {
                                    insertSignatureContent(editor, 'image', annotationProfileSignatureUrl);
                                    return;
                                }

                                annotationSignaturePadMode = 'image';
                                openAnnotationSignaturePad('image', 'tinymce');
                            }
                        });

                        editor.ui.registry.addButton('insertsignblock', {
                            text: 'Sign Block',
                            tooltip: 'Insert signed-by block with name, signature, and date',
                            icon: 'template',
                            onAction: function () {
                                if (annotationProfileSignatureUrl) {
                                    insertSignatureContent(editor, 'block', annotationProfileSignatureUrl);
                                    return;
                                }

                                annotationSignaturePadMode = 'block';
                                openAnnotationSignaturePad('block', 'tinymce');
                            }
                        });

                        editor.ui.registry.addMenuButton('signmenu', {
                            text: 'Sign',
                            tooltip: 'Document signing tools',
                            fetch: function (callback) {
                                callback([
                                    {
                                        type: 'menuitem',
                                        text: 'Insert signature image',
                                        onAction: function () {
                                            editor.execCommand('mceInsertSignature');
                                        }
                                    },
                                    {
                                        type: 'menuitem',
                                        text: 'Insert signature block',
                                        onAction: function () {
                                            editor.execCommand('mceInsertSignBlock');
                                        }
                                    },
                                    {
                                        type: 'menuitem',
                                        text: 'Draw / upload signature…',
                                        onAction: function () {
                                            openAnnotationSignaturePad('image', 'tinymce');
                                        }
                                    }
                                ]);
                            }
                        });

                        editor.addCommand('mceInsertSignature', function () {
                            if (annotationProfileSignatureUrl) {
                                insertSignatureContent(editor, 'image', annotationProfileSignatureUrl);
                            } else {
                                openAnnotationSignaturePad('image', 'tinymce');
                            }
                        });

                        editor.addCommand('mceInsertSignBlock', function () {
                            if (annotationProfileSignatureUrl) {
                                insertSignatureContent(editor, 'block', annotationProfileSignatureUrl);
                            } else {
                                openAnnotationSignaturePad('block', 'tinymce');
                            }
                        });
                    }
                });
            }
        }

        (function setupAnnotationSignaturePadUi() {
            const clearBtn = document.getElementById('clear-annotation-signature-pad');
            const useBtn = document.getElementById('use-annotation-signature-btn');
            const uploadInput = document.getElementById('annotation-signature-upload');

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    if (annotationSignaturePad) {
                        annotationSignaturePad.clear();
                    }
                });
            }

            if (uploadInput) {
                uploadInput.addEventListener('change', function (e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) {
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        annotationUploadedSignatureDataUrl = event.target.result;
                        const previewWrap = document.getElementById('annotation-signature-upload-preview-wrap');
                        const previewImg = document.getElementById('annotation-signature-upload-preview');
                        if (previewImg) {
                            previewImg.src = annotationUploadedSignatureDataUrl;
                        }
                        if (previewWrap) {
                            previewWrap.style.display = '';
                        }
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (useBtn) {
                useBtn.addEventListener('click', function () {
                    let signatureDataUrl = null;
                    const uploadPaneActive = document.getElementById('upload-signature-pane')
                        && document.getElementById('upload-signature-pane').classList.contains('active');

                    if (uploadPaneActive && annotationUploadedSignatureDataUrl) {
                        signatureDataUrl = annotationUploadedSignatureDataUrl;
                    } else if (annotationSignaturePad && !annotationSignaturePad.isEmpty()) {
                        signatureDataUrl = annotationSignaturePad.toDataURL('image/png');
                    } else if (annotationUploadedSignatureDataUrl) {
                        signatureDataUrl = annotationUploadedSignatureDataUrl;
                    }

                    if (!signatureDataUrl) {
                        window.alert('Please draw or upload a signature first.');
                        return;
                    }

                    if (annotationSignaturePadTarget === 'tinymce' && annotationEditor) {
                        insertSignatureContent(annotationEditor, annotationSignaturePadMode, signatureDataUrl);
                    }

                    if (typeof $ !== 'undefined' && $('#annotationSignaturePadModal').modal) {
                        $('#annotationSignaturePadModal').modal('hide');
                    } else {
                        const modal = document.getElementById('annotationSignaturePadModal');
                        if (modal) {
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                        }
                    }
                });
            }
        })();

        // Initialize on page load (only once)
        let initialized = false;

        function setupPdfAnnotatorPage() {
            document.body.classList.add('pdf-annotate-page');

            if (!initialized) {
                pdfAnnotator.init();
                initialized = true;
            }

            // Wait a bit for TinyMCE to be available
            setTimeout(function () {
                initAnnotationEditor();
            }, 500);


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