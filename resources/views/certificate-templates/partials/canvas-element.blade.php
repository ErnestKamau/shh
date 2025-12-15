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
        @elseif($element->element_type === 'checkbox')
            <div class="form-check">
                <input class="form-check-input" type="checkbox" {{ ($element->properties['checked'] ?? false) ? 'checked' : '' }} disabled>
                <label class="form-check-label">{{ $element->content ?? 'Checkbox' }}</label>
            </div>
        @elseif($element->element_type === 'radio')
            <div class="form-check">
                <input class="form-check-input" type="radio" {{ ($element->properties['checked'] ?? false) ? 'checked' : '' }} disabled>
                <label class="form-check-label">{{ $element->content ?? 'Radio' }}</label>
            </div>
        @elseif($element->element_type === 'link')
            <a href="{{ $element->properties['url'] ?? '#' }}" target="{{ $element->properties['target'] ?? '_blank' }}" style="color: {{ $element->properties['color'] ?? '#4f46e5' }}; text-decoration: underline; pointer-events: none;">{{ $element->content ?? 'Link' }}</a>
        @elseif($element->element_type === 'blockquote')
            <blockquote style="border-left: 4px solid {{ $element->properties['border_left_color'] ?? '#e5e7eb' }}; padding-left: 1rem; color: #4b5563; font-style: italic; margin: 0;">
                {{ $element->content ?? 'Blockquote' }}
            </blockquote>
        @elseif($element->element_type === 'code_block')
            <pre style="background: #f3f4f6; padding: 0.5rem; border-radius: 0.25rem; font-family: monospace; font-size: 0.875rem; overflow-x: auto; margin: 0;"><code class="language-{{ $element->properties['language'] ?? 'text' }}">{{ $element->content ?? '// Code block' }}</code></pre>
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
