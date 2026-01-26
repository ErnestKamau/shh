<!-- PDF Annotation Modal -->
<div class="modal fade" id="pdf-annotation-modal" tabindex="-1" role="dialog" aria-labelledby="pdfAnnotationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfAnnotationModalLabel">
                    <i class="mdi mdi-file-pdf"></i> Annotate PDF: <spam id="pdf-title"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row no-gutters" style="height: 80vh;">
                    <!-- PDF Viewer Section -->
                    <div class="col-md-9 p-3" style="border-right: 1px solid #dee2e6; overflow: auto;">
                        <!-- Toolbar -->
                        <div class="mb-3 p-2 bg-light rounded">
                            <div class="btn-group mr-2" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="pdf-prev-page" title="Previous Page">
                                    <i class="mdi mdi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary disabled" id="pdf-page-info" disabled>
                                    <span id="pdf-page-num">1</span> / <span id="pdf-page-count">-</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="pdf-next-page" title="Next Page">
                                    <i class="mdi mdi-chevron-right"></i>
                                </button>
                            </div>
                            
                            <div class="btn-group mr-2" role="group">
                                <button type="button" class="btn btn btn-sm btn-outline-primary annotation-tool" data-tool="text" title="Add Text Comment">
                                    <i class="mdi mdi-comment-text"></i> Text
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info annotation-tool" data-tool="image" title="Add Image">
                                    <i class="mdi mdi-image"></i> Image
                                </button>
                            </div>
                            
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" id="save-annotations-btn">
                                    <i class="mdi mdi-content-save"></i> Save PDF
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
                                    <i class="mdi mdi-close"></i> Cancel
                                </button>
                            </div>
                            
                            <input type="file" id="annotation-image-input" accept="image/*" style="display: none;">
                        </div>
                        
                        <!-- PDF Canvas Container -->
                        <div id="pdf-canvas-container" style="position: relative; background: #f5f5f5; min-height: 400px;">
                            <canvas id="pdf-canvas" style="position: absolute; top: 0; left: 0;"></canvas>
                            <canvas id="annotation-canvas" style="position: absolute; top: 0; left: 0; cursor: crosshair;"></canvas>
                            <div id="pdf-loading" class="text-center p-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading PDF...</span>
                                </div>
                                <p>Loading PDF...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Annotations List Sidebar -->
                    <div class="col-md-3 p-3" style="overflow-y: auto;">
                        <h6 class="mb-3"><i class="mdi mdi-format-list-bulleted"></i> Annotations</h6>
                        <div id="annotations-list">
                            <p class="text-muted text-sm">No annotations yet. Click "Add Text" or "Add Image" to start annotating.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for saving annotations -->
<form id="save-annotations-form" method="POST" action="{{ route('save-annotated-pdf') }}" style="display: none;">
    @csrf
    <input type="hidden" name="attachment_id" id="save-attachment-id">
    <input type="hidden" name="annotations_data" id="save-annotations-data">
    <input type="hidden" name="pdf_pages_data" id="save-pdf-pages-data">
</form>
