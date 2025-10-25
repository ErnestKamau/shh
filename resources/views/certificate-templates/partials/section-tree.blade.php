<div class="section-item level-{{ $level }}">
    <div class="section-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                @if($section->childSections->count() > 0 || $section->elements->count() > 0)
                    <i class="mdi mdi-chevron-down text-muted mr-2"></i>
                @else
                    <i class="mdi mdi-chevron-right text-muted mr-2"></i>
                @endif
                <h6 class="mb-0">{{ $section->title }}</h6>
                @if($section->is_collapsible)
                    <span class="badge badge-info badge-sm ml-2">Collapsible</span>
                @endif
            </div>
            <div class="d-flex align-items-center">
                <span class="badge badge-secondary badge-sm mr-2">{{ $section->elements->count() }} elements</span>
                <a href="{{ route('certificate-templates.builder', $section->template) }}?section={{ $section->id }}" 
                   class="btn btn-sm btn-outline-primary" title="Edit Section">
                    <i class="mdi mdi-pencil"></i>
                </a>
            </div>
        </div>
        @if($section->description)
            <p class="text-muted mb-0 mt-1">{{ $section->description }}</p>
        @endif
    </div>
    
    <div class="section-content" style="{{ $level > 0 ? 'display: none;' : '' }}">
        @if($section->elements->count() > 0)
            <div class="elements-list mb-3">
                <h6 class="text-muted mb-2">
                    <i class="mdi mdi-format-list-bulleted"></i> Elements
                </h6>
                @foreach($section->elements as $element)
                    <div class="element-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="element-type">{{ $element->element_type_label }}</span>
                                @if($element->content)
                                    <div class="element-content">
                                        {{ Str::limit(strip_tags($element->content), 100) }}
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center">
                                @if($element->is_conditional)
                                    <span class="badge badge-warning badge-sm mr-1">Conditional</span>
                                @endif
                                <a href="{{ route('certificate-templates.builder', $section->template) }}?element={{ $element->id }}" 
                                   class="btn btn-sm btn-outline-secondary" title="Edit Element">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        @if($section->childSections->count() > 0)
            <div class="child-sections">
                <h6 class="text-muted mb-2">
                    <i class="mdi mdi-folder"></i> Subsections
                </h6>
                @foreach($section->childSections as $childSection)
                    @include('certificate-templates.partials.section-tree', ['section' => $childSection, 'level' => $level + 1])
                @endforeach
            </div>
        @endif
        
        @if($section->elements->count() == 0 && $section->childSections->count() == 0)
            <div class="text-center py-3 text-muted">
                <i class="mdi mdi-information-outline"></i>
                <p class="mb-0">No elements or subsections yet.</p>
                <a href="{{ route('certificate-templates.builder', $section->template) }}?section={{ $section->id }}" 
                   class="btn btn-sm btn-outline-primary mt-2">
                    <i class="mdi mdi-plus"></i> Add Elements
                </a>
            </div>
        @endif
    </div>
</div>
