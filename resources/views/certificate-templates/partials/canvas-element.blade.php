@php
    // Check if element is inside a holder with direction
    $parentHolder = \App\Models\CertificateTemplateElementHolder::find($element->certificate_template_element_holder_id);
    $holderDirection = $parentHolder->direction ?? 'horizontal';
    
    // For elements inside holders, use relative/flex positioning
    // For standalone elements, use absolute positioning
    if ($parentHolder) {
        // Elements should fit within holder, use flexible sizing
        $elementWidth = min(($element->width ?? 100), 300); // Max 300px or use percentage
        $elementHeight = ($element->height ?? 30);
        
        $elementStyle = 'position: relative;
                        width: ' . $elementWidth . 'px;
                        max-width: 100% !important;
                        min-width: 50px;
                        height: ' . $elementHeight . 'px;
                        min-height: 30px;
                        flex: 0 0 auto;
                        box-sizing: border-box;
                        overflow: hidden;';
    } else {
        $elementStyle = 'position: absolute; 
                        left: ' . ($element->position_x ?? 0) . 'px; 
                        top: ' . ($element->position_y ?? 0) . 'px;
                        width: ' . ($element->width ?? 100) . 'px;
                        max-width: 100%;
                        height: ' . ($element->height ?? 30) . 'px;
                        box-sizing: border-box;
                        overflow: hidden;';
    }
@endphp

<div class="canvas-element {{ ($element->properties['hidden'] ?? false) ? 'element-hidden' : '' }}" 
     data-element-id="{{ $element->id }}"
     data-holder-id="{{ $element->certificate_template_element_holder_id }}"
     data-direction="{{ $holderDirection }}"
     data-hidden="{{ ($element->properties['hidden'] ?? false) ? '1' : '0' }}"
     style="{{ $elementStyle }}">
    
    {{-- Element Toolbar (Preview Only - No Delete) --}}
    <div class="element-toolbar">
        <button class="btn-toolbar btn-element-hide" data-element="{{ $element->id }}" title="{{ ($element->properties['hidden'] ?? false) ? 'Show Element' : 'Hide Element' }}">
            <i class="mdi mdi-{{ ($element->properties['hidden'] ?? false) ? 'eye' : 'eye-off' }}"></i>
        </button>
        <button class="btn-toolbar btn-element-edit" data-element="{{ $element->id }}" title="Edit Element">
            <i class="mdi mdi-cog"></i>
        </button>
    </div>
    
    {{-- Element Content --}}
    <div class="element-content">
        @if($element->element_type === 'text' || $element->element_type === 'paragraph')
            <span>{{ $element->content ?? 'Text Element' }}</span>
        @elseif($element->element_type === 'heading')
            <strong>{{ $element->content ?? 'Heading' }}</strong>
        @elseif($element->element_type === 'image')
            <img src="{{ $element->content ?? '/images/placeholder.png' }}" alt="Element Image" style="max-width: 100%; max-height: 100%;">
        @elseif($element->element_type === 'data_field')
            @php
                $dataSource = $element->properties['data_source'] ?? null;
                $fieldName = $element->properties['field_name'] ?? $element->content ?? 'field_name';
                $fieldLabel = $element->properties['field_label'] ?? $fieldName;
                
                if ($dataSource === 'Company') {
                    // Query Company where active=1 (first)
                    $company = \App\Company::where('active', 1)->first();
                    $displayValue = $company ? ($company->$fieldName ?? 'N/A') : 'No active company';
                } else {
                    // Placeholder for other datasources
                    $displayValue = '{{' . ($dataSource ?? 'data') . '.' . $fieldName . '}}';
                }
            @endphp
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <span class="field-value" style="font-weight: 600; color: #1f2937;">{{ $displayValue }}</span>
                <small class="text-muted" style="font-size: 10px;">{{ $fieldLabel }}</small>
            </div>
        @elseif($element->element_type === 'table')
            <div class="preview-table"><i class="mdi mdi-table"></i> Table</div>
        @else
            <span>{{ ucfirst(str_replace('_', ' ', $element->element_type ?? 'Element')) }}</span>
        @endif
    </div>
    
    {{-- Resize Handles for Elements --}}
    <div class="resize-handle resize-handle-nw" data-resize="nw"></div>
    <div class="resize-handle resize-handle-ne" data-resize="ne"></div>
    <div class="resize-handle resize-handle-sw" data-resize="sw"></div>
    <div class="resize-handle resize-handle-se" data-resize="se"></div>
</div>
