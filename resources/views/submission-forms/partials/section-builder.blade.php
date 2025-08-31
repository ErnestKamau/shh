<div class="section-item" data-section-id="{{ $section->id }}">
    <div class="section-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="mdi mdi-drag-horizontal text-muted mr-2" style="cursor: move;"></i>
            <h6 class="mb-0">
                <i class="mdi mdi-folder-outline text-primary"></i>
                {{ $section->title }}
            </h6>
            @if($section->description)
                <small class="text-muted ml-2">{{ Str::limit($section->description, 50) }}</small>
            @endif
        </div>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary" onclick="FormBuilder.showSectionModal({{ $section->id }})" title="Edit Section">
                <i class="mdi mdi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="FormBuilder.showHolderModal({{ $section->id }})" title="Add Element Holder">
                <i class="mdi mdi-plus"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#section-{{ $section->id }}" title="Toggle Section">
                <i class="mdi mdi-chevron-down"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="FormBuilder.deleteSection({{ $section->id }})" title="Delete Section">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </div>
    
    <div class="collapse show" id="section-{{ $section->id }}">
        <div class="section-content p-3">
            <div class="holders-container sortable-holders" data-section-id="{{ $section->id }}">
                @forelse($section->elementHolders as $holder)
                    <div class="holder-item mb-3" data-holder-id="{{ $holder->id }}">
                        <div class="holder-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-drag-horizontal text-muted mr-2" style="cursor: move;"></i>
                                <small class="font-weight-bold text-info">
                                    <i class="mdi mdi-{{ $holder->holder_type === 'field' ? 'form-textbox' : 'text' }}"></i>
                                    {{ ucfirst($holder->holder_type) }} Holder
                                    <span class="badge badge-light ml-1">{{ $holder->elements->count() }}/{{ $holder->max_elements }}</span>
                                </small>
                                @if($holder->isAtCapacity())
                                    <span class="badge badge-warning badge-sm ml-2">Full</span>
                                @endif
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="FormBuilder.showHolderModal({{ $section->id }}, {{ $holder->id }})" title="Edit Holder">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="FormBuilder.showElementModal({{ $holder->id }})" title="Add Element" {{ $holder->isAtCapacity() ? 'disabled' : '' }}>
                                    <i class="mdi mdi-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#holder-{{ $holder->id }}" title="Toggle Holder">
                                    <i class="mdi mdi-chevron-down"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="FormBuilder.deleteHolder({{ $holder->id }})" title="Delete Holder">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="collapse show" id="holder-{{ $holder->id }}">
                            <div class="holder-content">
                                <div class="elements-container sortable-elements p-2" data-holder-id="{{ $holder->id }}">
                                    @forelse($holder->elements as $element)
                                        <div class="element-item d-flex justify-content-between align-items-center py-2 px-3 mb-1 border rounded" data-element-id="{{ $element->id }}">
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-drag-horizontal text-muted mr-2" style="cursor: move;"></i>
                                                <i class="mdi mdi-{{ getElementIcon($element->element_type) }} text-secondary mr-2"></i>
                                                <div>
                                                    <span class="font-weight-medium">{{ $element->label }}</span>
                                                    @if($element->is_required)
                                                        <span class="text-danger ml-1">*</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">{{ $element->name }} ({{ $element->element_type }})</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                @if($element->is_readonly)
                                                    <span class="badge badge-outline-warning badge-sm mr-2">readonly</span>
                                                @endif
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="FormBuilder.showElementModal({{ $holder->id }}, {{ $element->id }})" title="Edit Element">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="FormBuilder.deleteElement({{ $element->id }})" title="Delete Element">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-3 text-muted">
                                            <i class="mdi mdi-plus-circle-outline" style="font-size: 2rem;"></i>
                                            <p class="mb-0 small">No elements added yet</p>
                                            <button class="btn btn-sm btn-outline-primary mt-2" onclick="FormBuilder.showElementModal({{ $holder->id }})" {{ $holder->isAtCapacity() ? 'disabled' : '' }}>
                                                <i class="mdi mdi-plus"></i> Add Element
                                            </button>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="mdi mdi-plus-circle-outline" style="font-size: 2.5rem;"></i>
                        <p class="mb-0">No element holders added yet</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="FormBuilder.showHolderModal({{ $section->id }})">
                            <i class="mdi mdi-plus"></i> Add Element Holder
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@php
function getElementIcon($elementType) {
    switch($elementType) {
        case 'text':
            return 'form-textbox';
        case 'number':
            return 'numeric';
        case 'email':
            return 'email-outline';
        case 'date':
            return 'calendar';
        case 'datetime':
            return 'calendar-clock';
        case 'textarea':
            return 'text-box-outline';
        case 'select':
            return 'form-dropdown';
        case 'radio':
            return 'radiobox-marked';
        case 'checkbox':
            return 'checkbox-marked';
        case 'file':
            return 'file-upload-outline';
        case 'signature':
            return 'draw';
        case 'calculation':
            return 'calculator';
        default:
            return 'form-textbox';
    }
}
@endphp