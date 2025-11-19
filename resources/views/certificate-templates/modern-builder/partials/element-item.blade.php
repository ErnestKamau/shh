<div class="canvas-element border rounded p-2 mb-2 bg-white" data-element-id="{{ $element->id }}" data-element-type="{{ $element->element_type }}">
    <div class="element-header d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted">
            <i class="mdi mdi-{{ $element->element_type === 'heading' ? 'format-header-1' : ($element->element_type === 'image' ? 'image' : 'text') }}"></i>
            {{ ucfirst($element->element_type) }}
        </small>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-sm btn-outline-primary edit-element-btn" data-element-id="{{ $element->id }}">
                <i class="mdi mdi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-element-btn" data-element-id="{{ $element->id }}">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </div>
    
    <div class="element-content">
        @if($element->element_type === 'image')
            <img src="{{ $element->content ?? '/placeholder-image.png' }}" alt="" style="max-width: 100%; height: auto;">
        @elseif($element->element_type === 'divider')
            <hr>
        @else
            <div>{{ Str::limit($element->content ?? 'Empty element', 50) }}</div>
        @endif
    </div>
</div>




