<div class="template-section nestable-item" data-section-id="{{ $section->id }}">
    <div class="section-header nestable-handle">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0">
                    <i class="mdi mdi-drag-horizontal"></i>
                    {{ $section->title }}
                </h6>
                <small class="text-muted">Section</small>
            </div>
            <div class="section-controls">
                <button class="btn btn-sm btn-outline-primary edit-section" data-id="{{ $section->id }}" title="Edit Section">
                    <i class="mdi mdi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-section" data-id="{{ $section->id }}" title="Delete Section">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="section-content">
        @if($section->elements->count() > 0)
            @foreach($section->elements as $element)
                <div class="template-element" data-element-id="{{ $element->id }}" data-type="{{ $element->element_type }}">
                    <div class="element-controls">
                        <button class="btn btn-sm btn-outline-primary edit-element" data-id="{{ $element->id }}" title="Edit Element">
                            <i class="mdi mdi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-element" data-id="{{ $element->id }}" title="Delete Element">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </div>
                    <div class="element-content">
                        <div class="element-type-indicator">
                            @switch($element->element_type)
                                @case('heading')
                                    <i class="mdi mdi-format-header-1 text-primary"></i>
                                    <strong>HEADING</strong>
                                    @break
                                @case('paragraph')
                                    <i class="mdi mdi-format-paragraph text-info"></i>
                                    <strong>PARAGRAPH</strong>
                                    @break
                                @case('image')
                                    <i class="mdi mdi-image text-warning"></i>
                                    <strong>IMAGE</strong>
                                    @break
                                @case('table')
                                    <i class="mdi mdi-table text-success"></i>
                                    <strong>TABLE</strong>
                                    @break
                                @case('data_field')
                                    <i class="mdi mdi-database text-dark"></i>
                                    <strong>DATA FIELD</strong>
                                    @break
                                @case('signature')
                                    <i class="mdi mdi-pen text-secondary"></i>
                                    <strong>SIGNATURE</strong>
                                    @break
                                @case('date')
                                    <i class="mdi mdi-calendar text-primary"></i>
                                    <strong>DATE</strong>
                                    @break
                                @case('page_break')
                                    <i class="mdi mdi-page-layout-body text-muted"></i>
                                    <strong>PAGE BREAK</strong>
                                    @break
                                @default
                                    <i class="mdi mdi-help-circle text-muted"></i>
                                    <strong>{{ strtoupper($element->element_type) }}</strong>
                            @endswitch
                        </div>
                        <div class="element-preview mt-2">
                            @switch($element->element_type)
                                @case('heading')
                                    <h4 class="mb-0 text-truncate">{{ $element->content ?: 'New Heading' }}</h4>
                                    @break
                                @case('paragraph')
                                    <p class="mb-0 text-truncate">{{ $element->content ?: 'Enter your text here...' }}</p>
                                    @break
                                @case('image')
                                    <div class="bg-light border rounded p-3 text-center">
                                        <i class="mdi mdi-image" style="font-size: 2rem;"></i>
                                        <br><small>{{ $element->content ?: 'No image selected' }}</small>
                                    </div>
                                    @break
                                @case('table')
                                    <div class="bg-light border rounded p-2">
                                        <small><i class="mdi mdi-table"></i> Table Configuration</small>
                                    </div>
                                    @break
                                @case('data_field')
                                    <code class="bg-light px-2 py-1 rounded">
                                        {{ '{' . ($element->content ?: 'sample_name') . '}' }}
                                    </code>
                                    @break
                                @case('signature')
                                    <div class="bg-light border rounded p-2 text-center">
                                        <i class="mdi mdi-pen"></i> Signature Area
                                    </div>
                                    @break
                                @case('date')
                                    <code class="bg-light px-2 py-1 rounded">
                                        {{ '{' . ($element->content ?: 'current_date') . '}' }}
                                    </code>
                                    @break
                                @case('page_break')
                                    <div class="border-top border-bottom py-2 text-center text-muted">
                                        <i class="mdi mdi-page-layout-body"></i> Page Break
                                    </div>
                                    @break
                                @default
                                    <div class="text-muted">
                                        {{ $element->content ?: 'No content' }}
                                    </div>
                            @endswitch
                        </div>
                        
                        @if($element->properties)
                            <div class="element-properties mt-2">
                                <small class="text-muted">
                                    @if(isset($element->properties['style']))
                                        <span class="badge badge-light">{{ $element->properties['style'] }}</span>
                                    @endif
                                    @if(isset($element->properties['alignment']))
                                        <span class="badge badge-light">{{ $element->properties['alignment'] }}</span>
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="drop-zone">
                <i class="mdi mdi-plus-circle" style="font-size: 2rem;"></i>
                <p class="mb-0">Drop elements here or select an element type from the palette</p>
            </div>
        @endif
    </div>
    
    @if($section->children && $section->children->count() > 0)
        <div class="subsections pl-4">
            @foreach($section->children as $childSection)
                @include('certificate-templates.partials.builder-section', ['section' => $childSection])
            @endforeach
        </div>
    @endif
</div>