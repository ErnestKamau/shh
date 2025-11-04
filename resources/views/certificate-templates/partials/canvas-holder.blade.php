@php
    $holderDirection = $holder->direction ?? 'horizontal';
    $isNested = $holder->parent_holder_id !== null;
@endphp

@if($isNested)
    {{-- Nested holders: render children directly in parent's flow (no wrapper) --}}
    @foreach($holder->childHolders as $childHolder)
        @include('certificate-templates.partials.canvas-holder', ['holder' => $childHolder])
    @endforeach
    
    @foreach($holder->elements as $element)
        @include('certificate-templates.partials.canvas-element', ['element' => $element])
    @endforeach
@else
    {{-- Root holder: render with visual container and flex layout --}}
    @php
        $holderStyle = 'position: absolute; 
                        left: ' . ($holder->position_x ?? 50) . 'px; 
                        top: ' . ($holder->position_y ?? 50) . 'px;
                        width: ' . ($holder->width ?? 300) . 'px;
                        min-width: 150px;
                        height: ' . ($holder->height ?? 200) . 'px;
                        min-height: 60px;
                        display: flex;
                        flex-direction: ' . ($holderDirection === 'horizontal' ? 'row' : 'column') . ';
                        flex-wrap: ' . ($holderDirection === 'horizontal' ? 'wrap' : 'nowrap') . ';
                        align-items: flex-start;
                        gap: 8px;
                        padding: 8px;
                        box-sizing: border-box;';
        
        if ($holder->flex_grow) {
            $holderStyle .= ' flex-grow: ' . $holder->flex_grow . ';';
        }
        if ($holder->flex_shrink !== null) {
            $holderStyle .= ' flex-shrink: ' . $holder->flex_shrink . ';';
        }
        if ($holder->flex_basis) {
            $holderStyle .= ' flex-basis: ' . $holder->flex_basis . ';';
        }
    @endphp

    <div class="canvas-holder-root" 
         data-holder-id="{{ $holder->id }}"
         data-section-id="{{ $holder->certificate_template_section_id }}"
         data-direction="{{ $holderDirection }}"
         data-max-elements="{{ $holder->max_elements ?? 0 }}"
         style="{{ $holderStyle }}">
        
        {{-- Recursively render all nested holders and elements (flattened) --}}
        @foreach($holder->childHolders as $childHolder)
            @include('certificate-templates.partials.canvas-holder', ['holder' => $childHolder])
        @endforeach
        
        @foreach($holder->elements as $element)
            @include('certificate-templates.partials.canvas-element', ['element' => $element])
        @endforeach
        
        {{-- Empty State --}}
        @if($holder->elements->isEmpty() && $holder->childHolders->isEmpty())
            <div class="holder-empty-state">
                <p class="text-muted">Empty holder</p>
            </div>
        @endif
        
        {{-- Resize Handles --}}
        <div class="resize-handle resize-handle-nw" data-resize="nw"></div>
        <div class="resize-handle resize-handle-ne" data-resize="ne"></div>
        <div class="resize-handle resize-handle-sw" data-resize="sw"></div>
        <div class="resize-handle resize-handle-se" data-resize="se"></div>
        @if($holderDirection === 'horizontal')
            <div class="resize-handle resize-handle-n" data-resize="n"></div>
            <div class="resize-handle resize-handle-s" data-resize="s"></div>
        @else
            <div class="resize-handle resize-handle-w" data-resize="w"></div>
            <div class="resize-handle resize-handle-e" data-resize="e"></div>
        @endif
    </div>
@endif
