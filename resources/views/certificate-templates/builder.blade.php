@extends('layouts.lab.layout.app')

@section('title2')
<title>Visual Template Builder - {{ $template->name }} | Lab Management</title>
@endsection

@section('content2')
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
            'name' => 'Visual Builder',
            'icon' => null
        )
    );

    // Calculate canvas dimensions based on page settings
    $pageSettings = $template->page_settings ?? ['size' => 'A4', 'orientation' => 'portrait'];
    $canvasDimensions = [
        'A4' => ['width' => 794, 'height' => 1123], // pixels at 96dpi
        'A3' => ['width' => 1123, 'height' => 1587],
        'Letter' => ['width' => 816, 'height' => 1056],
        'Legal' => ['width' => 816, 'height' => 1344],
        'A5' => ['width' => 559, 'height' => 794],
    ];
    
    $size = $pageSettings['size'] ?? 'A4';
    $orientation = $pageSettings['orientation'] ?? 'portrait';
    
    if ($orientation === 'landscape') {
        $canvasWidth = $canvasDimensions[$size]['height'];
        $canvasHeight = $canvasDimensions[$size]['width'];
    } else {
        $canvasWidth = $canvasDimensions[$size]['width'];
        $canvasHeight = $canvasDimensions[$size]['height'];
    }
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- Top Toolbar -->
    <div class="builder-toolbar">
        <div class="container-fluid">
            <div class="row align-items-center py-3">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="mdi mdi-pencil"></i> Visual Template Builder
                                <small class="text-muted">{{ $template->name }}</small>
                            </h5>
                        </div>
                <div class="col-md-6 text-right">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success" id="save-template">
                            <i class="mdi mdi-content-save"></i> Save
                                </button>
                                <button type="button" class="btn btn-info" id="preview-template">
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

    <!-- Sections Panel (Left Sidebar) -->
    <div class="sections-panel" id="sections-panel">
        <div class="panel-header">
            <h6 class="mb-0"><i class="mdi mdi-folder-multiple"></i> Sections</h6>
            <button class="btn btn-sm btn-primary" id="add-section-btn">
                                        <i class="mdi mdi-plus"></i> Add Section
                                    </button>
                                </div>
        <div class="panel-body">
            @forelse($template->sections as $section)
                <div class="section-panel-item" data-section-id="{{ $section->id }}">
                    <div class="section-panel-header" data-toggle="collapse" data-target="#section-{{ $section->id }}-holders">
                        <i class="mdi mdi-chevron-down"></i>
                        <span class="section-title">{{ $section->title }}</span>
                        <div class="section-panel-actions">
                            <button class="btn btn-xs btn-outline-primary edit-section" data-id="{{ $section->id }}" title="Edit">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button class="btn btn-xs btn-outline-danger delete-section" data-id="{{ $section->id }}" title="Delete">
                                <i class="mdi mdi-delete"></i>
                            </button>
                            </div>
                        </div>
                    <div class="section-holders collapse show" id="section-{{ $section->id }}-holders">
                        @forelse($section->elementHolders as $holder)
                            <div class="holder-badge" data-holder-id="{{ $holder->id }}" data-section-id="{{ $section->id }}">
                                <i class="mdi mdi-cube-outline"></i>
                                Holder {{ $loop->iteration }}
                                <small>({{ $holder->elements->count() }}/{{ $holder->max_elements }})</small>
                                <button class="btn-holder-delete" data-id="{{ $holder->id }}" title="Delete Holder">
                                    <i class="mdi mdi-close"></i>
                                </button>
                                </div>
                        @empty
                            <div class="text-muted small px-3 py-2">No holders yet</div>
                        @endforelse
                        <button class="btn btn-sm btn-block btn-outline-success add-holder-btn mt-2" data-section-id="{{ $section->id }}">
                            <i class="mdi mdi-plus"></i> Add Holder
                                                </button>
                                            </div>
                                    </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="mdi mdi-folder-open" style="font-size: 2rem;"></i>
                    <p class="small mb-0">No sections yet</p>
                                </div>
            @endforelse
    </div>
    </div>
    
    <!-- Element Palette (Right Sidebar) -->
    <div class="element-palette-panel" id="element-palette">
        <div class="panel-header">
            <h6 class="mb-0"><i class="mdi mdi-palette"></i> Elements</h6>
        </div>
        <div class="panel-body">
            <div class="element-types">
                <button class="element-type-btn" data-type="heading">
                    <i class="mdi mdi-format-header-1"></i>
                    <span>Heading</span>
                </button>
                <button class="element-type-btn" data-type="paragraph">
                    <i class="mdi mdi-format-paragraph"></i>
                    <span>Paragraph</span>
                </button>
                <button class="element-type-btn" data-type="image">
                    <i class="mdi mdi-image"></i>
                    <span>Image</span>
                </button>
                <button class="element-type-btn" data-type="table">
                    <i class="mdi mdi-table"></i>
                    <span>Table</span>
                </button>
                <button class="element-type-btn" data-type="data_field">
                    <i class="mdi mdi-database"></i>
                    <span>Data Field</span>
                </button>
                <button class="element-type-btn" data-type="signature">
                    <i class="mdi mdi-pen"></i>
                    <span>Signature</span>
                </button>
                <button class="element-type-btn" data-type="date">
                    <i class="mdi mdi-calendar"></i>
                    <span>Date</span>
                </button>
                <button class="element-type-btn" data-type="page_break">
                    <i class="mdi mdi-page-layout-body"></i>
                    <span>Page Break</span>
                </button>
                </div>
            <div class="palette-help mt-3 p-2 bg-light small">
                <strong>How to add elements:</strong>
                <ol class="mb-0 pl-3">
                    <li>Click an element type</li>
                    <li>Click on a holder in the canvas</li>
                    <li>Drag & resize as needed</li>
                </ol>
            </div>
        </div>
    </div>
    
    <!-- Visual Designer Canvas (Center) -->
    <div class="designer-canvas-wrapper">
        <div class="canvas-toolbar">
            <div class="canvas-info">
                <span class="badge badge-secondary">{{ $size }} - {{ ucfirst($orientation) }}</span>
                <span class="badge badge-light">{{ $canvasWidth }}x{{ $canvasHeight }}px</span>
            </div>
            <div class="canvas-controls">
                <button class="btn btn-sm btn-outline-secondary" id="zoom-out" title="Zoom Out">
                    <i class="mdi mdi-minus"></i>
                    </button>
                <span class="zoom-level">100%</span>
                <button class="btn btn-sm btn-outline-secondary" id="zoom-in" title="Zoom In">
                    <i class="mdi mdi-plus"></i>
                    </button>
                <button class="btn btn-sm btn-outline-secondary" id="reset-zoom" title="Reset Zoom">
                    <i class="mdi mdi-backup-restore"></i>
                </button>
                <div class="divider-v"></div>
                <button class="btn btn-sm btn-outline-secondary" id="toggle-grid" title="Toggle Grid">
                    <i class="mdi mdi-grid"></i> Grid
                </button>
                <button class="btn btn-sm btn-outline-secondary active" id="toggle-snap" title="Toggle Snap">
                    <i class="mdi mdi-magnet-on"></i> Snap
                            </button>
                        </div>
                            </div>

        <div class="canvas-container" id="canvas-container">
            <div class="designer-canvas" id="designer-canvas" 
                 data-width="{{ $canvasWidth }}" 
                 data-height="{{ $canvasHeight }}"
                 style="width: {{ $canvasWidth }}px; height: {{ $canvasHeight }}px;">
                
                @foreach($template->sections as $section)
                    @foreach($section->elementHolders as $holder)
                        <div class="element-holder-container" 
                             data-holder-id="{{ $holder->id }}"
                             data-section-id="{{ $section->id }}"
                             data-max-elements="{{ $holder->max_elements }}"
                             style="position: absolute; 
                                    left: {{ $holder->position_x ?? 50 }}px; 
                                    top: {{ $holder->position_y ?? 50 }}px;
                                    width: {{ $holder->width ?? 300 }}px;
                                    height: {{ $holder->height ?? 200 }}px;">
                            
                            <div class="holder-header">
                                <span class="holder-title">{{ $section->title }} - Holder {{ $loop->parent->iteration }}</span>
                                <span class="holder-capacity">{{ $holder->elements->count() }}/{{ $holder->max_elements }}</span>
                                <div class="holder-actions">
                                    <button class="btn-holder-edit" data-id="{{ $holder->id }}" title="Edit Holder">
                                        <i class="mdi mdi-cog"></i>
                    </button>
                </div>
                </div>
                            
                            <div class="holder-content">
                                @foreach($holder->elements as $element)
                                    <div class="canvas-element" 
                                         data-element-id="{{ $element->id }}"
                                         data-holder-id="{{ $holder->id }}"
                                         data-type="{{ $element->element_type }}"
                                         style="position: absolute; 
                                                left: {{ $element->position_x ?? 10 }}px; 
                                                top: {{ $element->position_y ?? 10 }}px;
                                                width: {{ $element->width ?? 200 }}px;
                                                height: {{ $element->height ?? 100 }}px;
                                                z-index: {{ $element->z_index ?? 1 }};">
                                        
                                        <div class="element-header">
                                            <span class="element-type-icon">
                                                @switch($element->element_type)
                                                    @case('heading')
                                                        <i class="mdi mdi-format-header-1"></i>
                                                        @break
                                                    @case('paragraph')
                                                        <i class="mdi mdi-format-paragraph"></i>
                                                        @break
                                                    @case('image')
                                                        <i class="mdi mdi-image"></i>
                                                        @break
                                                    @case('table')
                                                        <i class="mdi mdi-table"></i>
                                                        @break
                                                    @case('data_field')
                                                        <i class="mdi mdi-database"></i>
                                                        @break
                                                    @case('signature')
                                                        <i class="mdi mdi-pen"></i>
                                                        @break
                                                    @case('date')
                                                        <i class="mdi mdi-calendar"></i>
                                                        @break
                                                    @default
                                                        <i class="mdi mdi-square"></i>
                                                @endswitch
                                            </span>
                                            <span class="element-actions">
                                                <button class="btn-element-edit" data-id="{{ $element->id }}">
                                                    <i class="mdi mdi-pencil"></i>
                    </button>
                                                <button class="btn-element-delete" data-id="{{ $element->id }}">
                                                    <i class="mdi mdi-delete"></i>
                    </button>
                                            </span>
                </div>
                                        
                                        <div class="element-preview">
                                            @switch($element->element_type)
                                                @case('heading')
                                                    <div class="preview-heading">{{ $element->content ?: 'Heading' }}</div>
                                                    @break
                                                @case('paragraph')
                                                    <div class="preview-paragraph">{{ Str::limit($element->content ?: 'Paragraph text...', 50) }}</div>
                                                    @break
                                                @case('image')
                                                    @if($element->content)
                                                        <img src="{{ $element->content }}" alt="Preview" class="preview-image">
                                                    @else
                                                        <div class="preview-placeholder"><i class="mdi mdi-image"></i></div>
                                                    @endif
                                                    @break
                                                @case('table')
                                                    <div class="preview-table"><i class="mdi mdi-table"></i> Table</div>
                                                    @break
                                                @case('data_field')
                                                    <div class="preview-data">{{ '{' . ($element->content ?: 'field') . '}' }}</div>
                                                    @break
                                                @case('signature')
                                                    <div class="preview-signature"><i class="mdi mdi-pen"></i> Signature</div>
                                                    @break
                                                @case('date')
                                                    <div class="preview-date"><i class="mdi mdi-calendar"></i> {{ date('Y-m-d') }}</div>
                                                    @break
                                            @endswitch
            </div>
            </div>
                                @endforeach
                                
                                @if($holder->elements->count() == 0)
                                    <div class="holder-empty-state">
                                        <i class="mdi mdi-cube-outline"></i>
                                        <p>Click an element type<br>then click here to add</p>
            </div>
                                @endif
            </div>
            </div>
                    @endforeach
                @endforeach
                
                @if($template->sections->count() == 0 || $template->sections->sum(function($s) { return $s->elementHolders->count(); }) == 0)
                    <div class="canvas-empty-state">
                        <i class="mdi mdi-file-document-edit" style="font-size: 4rem;"></i>
                        <h5>Start Building Your Template</h5>
                        <p>Add a section first, then add holders to it</p>
                        <button class="btn btn-primary" id="add-first-section">
                            <i class="mdi mdi-plus"></i> Add First Section
                        </button>
                    </div>
                @endif
                </div>
            </div>
                </div>
</main>

<!-- Section Modal -->
<div class="modal fade" id="section-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Section Properties</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                </div>
            <div class="modal-body">
                <form id="section-form">
                    <input type="hidden" id="section-id">
                    <div class="form-group">
                        <label for="section-title">Section Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="section-title" required>
                    </div>
                    <div class="form-group">
                        <label for="section-description">Description</label>
                        <textarea class="form-control" id="section-description" rows="3"></textarea>
                    </div>
                <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="section-collapsible">
                        <label class="form-check-label" for="section-collapsible">
                            Collapsible Section
                    </label>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-section">Save Section</button>
            </div>
            </div>
            </div>
            </div>

<!-- Element Holder Modal -->
<div class="modal fade" id="holder-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Element Holder Properties</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="holder-form">
                    <input type="hidden" id="holder-id">
                    <input type="hidden" id="holder-section-id">
            <div class="form-group">
                        <label for="holder-type">Holder Type</label>
                        <select class="form-control" id="holder-type">
                            <option value="field">Field Holder (for form data)</option>
                            <option value="text">Text Holder (for static content)</option>
                </select>
            </div>
                        <div class="form-group">
                        <label for="holder-max-elements">Maximum Elements <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="holder-max-elements" min="1" max="50" value="10" required>
                        <small class="form-text text-muted">Maximum number of elements this holder can contain</small>
                        </div>
                </form>
                    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-holder">Save Holder</button>
            </div>
                        </div>
                    </div>
                </div>
                
<!-- Element Properties Modal -->
<div class="modal fade" id="element-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Element Properties</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div id="element-properties-content">
                    <!-- Properties content will be loaded here dynamically -->
            </div>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-element-properties">Save Changes</button>
            </div>
                </div>
                </div>
            </div>
            
<!-- Auto-save Indicator -->
<div class="auto-save-indicator" id="auto-save-indicator">
    <i class="mdi mdi-check-circle"></i> Saved
            </div>
@endsection

@push('styles')
@include('certificate-templates.partials.builder-styles')
@endpush

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.19/dist/interact.min.js"></script>
@include('certificate-templates.partials.builder-scripts')
@endsection
