@php
    $fieldName = isset($isArrayField) && $isArrayField ? $element->name . '[' . $rowIndex . ']' : $element->name;
    $fieldId = isset($isArrayField) && $isArrayField ? $element->name . '_' . $rowIndex : $element->name;
@endphp

<div class="form-group">
    <label for="{{ $fieldId }}" class="{{ $element->is_required ? 'required' : '' }}">
        {{ $element->label }}
    </label>
    
    @switch($element->element_type)
        @case('text')
            <input type="text" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('number')
            <input type="number" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('email')
            <input type="email" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('date')
            <input type="date" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('datetime')
            <input type="datetime-local" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('textarea')
            <textarea class="form-control" 
                      id="{{ $fieldId }}" 
                      name="{{ $fieldName }}"
                      rows="3"
                      placeholder="{{ $element->placeholder }}"
                      {{ $element->is_required ? 'required' : '' }}
                      {{ $element->is_readonly ? 'readonly' : '' }}>{{ $element->default_value }}</textarea>
            @break
            
        @case('select')
            <select class="form-control" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select an option...' }}</option>
                @endif
                @foreach($element->options as $option)
                    <option value="{{ $option['value'] ?? $option }}" 
                            {{ ($element->default_value == ($option['value'] ?? $option)) ? 'selected' : '' }}>
                        {{ $option['label'] ?? $option }}
                    </option>
                @endforeach
            </select>
            @break
            
        @case('radio')
            <div class="form-check-container">
                @foreach($element->options as $index => $option)
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="radio" 
                               id="{{ $element->name }}_{{ $index }}" 
                               name="{{ $fieldName }}"
                               value="{{ $option['value'] ?? $option }}"
                               {{ ($element->default_value == ($option['value'] ?? $option)) ? 'checked' : '' }}
                               {{ $element->is_required ? 'required' : '' }}
                               {{ $element->is_readonly ? 'disabled' : '' }}>
                        <label class="form-check-label" for="{{ $element->name }}_{{ $index }}">
                            {{ $option['label'] ?? $option }}
                        </label>
                    </div>
                @endforeach
            </div>
            @break
            
        @case('checkbox')
            @if($element->options && count($element->options) > 1)
                {{-- Multiple checkboxes --}}
                <div class="form-check-container">
                    @foreach($element->options as $index => $option)
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="{{ $element->name }}_{{ $index }}" 
                                   name="{{ $element->name }}[]"
                                   value="{{ $option['value'] ?? $option }}"
                                   {{ $element->is_readonly ? 'disabled' : '' }}>
                            <label class="form-check-label" for="{{ $element->name }}_{{ $index }}">
                                {{ $option['label'] ?? $option }}
                            </label>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Single checkbox --}}
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="{{ $fieldId }}" 
                           name="{{ $fieldName }}"
                           value="1"
                           {{ $element->default_value ? 'checked' : '' }}
                           {{ $element->is_readonly ? 'disabled' : '' }}>
                    <label class="form-check-label" for="{{ $element->name }}">
                        {{ $element->options[0]['label'] ?? 'Yes' }}
                    </label>
                </div>
            @endif
            @break
            
        @case('file')
            <input type="file" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'disabled' : '' }}>
            @if($element->help_text)
                <small class="form-text text-muted">{{ $element->help_text }}</small>
            @endif
            @break
            
        @case('signature')
            <div class="signature-container">
                <canvas id="{{ $element->name }}_canvas" 
                        width="400" 
                        height="200" 
                        style="border: 1px solid #ccc; cursor: crosshair;"></canvas>
                <div class="signature-controls mt-2">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearSignature('{{ $element->name }}')">
                        Clear Signature
                    </button>
                </div>
                <input type="hidden" 
                       id="{{ $fieldId }}" 
                       name="{{ $fieldName }}"
                       {{ $element->is_required ? 'required' : '' }}>
            </div>
            @break
            
        @case('client_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="client_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a client...' }}</option>
                @endif
                {{-- Options will be loaded dynamically --}}
            </select>
            @break
            
        @case('sample_type_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="sample_type_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a sample type...' }}</option>
                @endif
                {{-- Options will be loaded dynamically --}}
            </select>
            @break
            
        @case('client_unit_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="client_unit_select"
                    data-depends-on="client_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a client unit...' }}</option>
                @endif
                {{-- Options will be loaded dynamically based on selected client --}}
            </select>
            @break
            
        @case('client_contact_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="client_contact_select"
                    data-depends-on="client_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a client contact...' }}</option>
                @endif
                {{-- Options will be loaded dynamically based on selected client --}}
            </select>
            @break
            
        @case('analysis_type_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="analysis_type_select"
                    data-depends-on="sample_type_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select an analysis type...' }}</option>
                @endif
                {{-- Options will be loaded dynamically based on selected sample type --}}
            </select>
            @break
            
        @case('store_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="store_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a store...' }}</option>
                @endif
                {{-- Options will be loaded dynamically --}}
            </select>
            @break
            
        @case('store_slot_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="store_slot_select"
                    data-depends-on="store_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a store slot...' }}</option>
                @endif
                {{-- Options will be loaded dynamically based on selected store --}}
            </select>
            @break
            
        @case('sample_condition_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="sample_condition_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a sample condition...' }}</option>
                @endif
                {{-- Options will be loaded dynamically --}}
            </select>
            @break
            
        @case('standard_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="standard_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a standard...' }}</option>
                @endif
                {{-- Options will be loaded dynamically --}}
            </select>
            @break
            
        @case('sample_point_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="sample_point_select"
                    data-depends-on="client_unit_select"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a sample point...' }}</option>
                @endif
                {{-- Options will be loaded dynamically based on selected client unit --}}
            </select>
            @break
            
        @case('calculation')
            <input type="text" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
    @endswitch
    
    @if($element->help_text)
        <small class="form-text text-muted">{{ $element->help_text }}</small>
    @endif
    
    {{-- Validation feedback placeholder --}}
    <div class="invalid-feedback"></div>
</div>

{{-- Store element data for later initialization --}}
@if(in_array($element->element_type, ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select', 'analysis_type_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select', 'sample_point_select']))
<script>
// Store element data for initialization when jQuery is ready
window.customElementsToInit = window.customElementsToInit || [];
window.customElementsToInit.push({
    elementId: '{{ $element->name }}',
    elementType: '{{ $element->element_type }}',
    isRequired: {{ $element->is_required ? 'true' : 'false' }},
    placeholder: '{{ $element->placeholder ?: "Select..." }}'
});
</script>
@endif

{{-- Signature pad JavaScript --}}
@if($element->element_type === 'signature')
<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize signature pad for {{ $element->name }}
    const canvas = document.getElementById('{{ $element->name }}_canvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    
    // Mouse events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Touch events for mobile
    canvas.addEventListener('touchstart', handleTouch);
    canvas.addEventListener('touchmove', handleTouch);
    canvas.addEventListener('touchend', stopDrawing);
    
    function startDrawing(e) {
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        ctx.beginPath();
        ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    }
    
    function draw(e) {
        if (!isDrawing) return;
        const rect = canvas.getBoundingClientRect();
        ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
        ctx.stroke();
        updateSignatureData();
    }
    
    function stopDrawing() {
        isDrawing = false;
        ctx.beginPath();
    }
    
    function handleTouch(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 
                                        e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }
    
    function updateSignatureData() {
        const dataURL = canvas.toDataURL();
        document.getElementById('{{ $element->name }}').value = dataURL;
    }
    
    // Clear signature
    window.clearSignature = function(elementName) {
        const canvas = document.getElementById(elementName + '_canvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById(elementName).value = '';
    };
});
</script>
@endif