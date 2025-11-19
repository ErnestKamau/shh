@php
    $elements = $section->elements->where('parent_cell_id', $cell['id']);
@endphp

<div class="canvas-cell border rounded p-3 mb-2 bg-light" data-cell-id="{{ $cell['id'] }}">
    <div class="cell-header mb-2 d-flex justify-content-between align-items-center">
        <small class="text-muted">Cell: {{ $cell['id'] }}</small>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-sm btn-outline-success add-element-btn" data-cell-id="{{ $cell['id'] }}">
                <i class="mdi mdi-plus"></i> Add Element
            </button>
            <button class="btn btn-sm btn-outline-danger delete-cell-btn" data-cell-id="{{ $cell['id'] }}" data-section-id="{{ $section->id }}">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </div>
    
    <div class="cell-content">
        @if($elements->count() > 0)
            @foreach($elements as $element)
                @include('certificate-templates.modern-builder.partials.element-item', ['element' => $element])
            @endforeach
        @else
            <div class="text-center text-muted py-3 border-dashed">
                <small><i class="mdi mdi-cursor-pointer"></i> Click "Add Element" to add content</small>
            </div>
        @endif
        
        @if(!empty($cell['sub_sections']))
            @foreach($cell['sub_sections'] as $subSectionId)
                @php
                    $subSection = \App\CertificateTemplateSection::find($subSectionId);
                @endphp
                @if($subSection)
                    @include('certificate-templates.modern-builder.partials.canvas-section', ['section' => $subSection])
                @endif
            @endforeach
        @endif
    </div>
</div>




