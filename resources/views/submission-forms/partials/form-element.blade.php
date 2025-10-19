@php
    $fieldName = isset($isArrayField) && $isArrayField ? $element->name . '[' . $rowIndex . ']' : $element->name;
    $fieldId = isset($isArrayField) && $isArrayField ? $element->name . '_' . $rowIndex : $element->name;
    
    // Get existing value
    $existingValue = null;
    if (isset($existingValues) && $existingValues) {
        if (isset($isArrayField) && $isArrayField && isset($rowIndex)) {
            // For array fields, find value by element ID and array index
            $existingValue = $existingValues->where('submission_form_element_id', $element->id)
                                          ->where('array_index', $rowIndex)
                                          ->first();
        } else {
            // For regular fields, find value by element ID
            $existingValue = $existingValues->where('submission_form_element_id', $element->id)->first();
        }
    }
    
    $fieldValue = $existingValue ? $existingValue->value : $element->default_value;
@endphp

<div class="form-group">
    @if(!isset($hideLabel) || !$hideLabel)
        <label for="{{ $fieldId }}" class="{{ $element->is_required ? 'required' : '' }}">
            {{ $element->label }}
        </label>
    @endif
    
    @switch($element->element_type)
        @case('text')
            <input type="text" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $fieldValue }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('number')
            <input type="number" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $fieldValue }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('email')
            <input type="email" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $fieldValue }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('date')
            <input type="date" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   value="{{ $fieldValue ?: date('Y-m-d') }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('datetime')
            <input type="datetime-local" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   value="{{ $fieldValue ?: date('Y-m-d\TH:i') }}"
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
                      {{ $element->is_readonly ? 'readonly' : '' }}>{{ $fieldValue }}</textarea>
            @break
            
        @case('plain_text')
            <div class="plain-text-element" 
                 id="{{ $fieldId }}" 
                 style="min-width: 145px !important; padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; white-space: pre-wrap;">
                {{ $fieldValue ?: $element->default_value ?: $element->placeholder ?: 'Plain text content' }}
            </div>
            <input type="hidden" 
                   name="{{ $fieldName }}" 
                   value="{{ $fieldValue ?: $element->default_value ?: '' }}">
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
                <div class="signature-pad-wrapper">
                    <canvas id="{{ $element->name }}_canvas" 
                            class="signature-canvas"
                            style="width: 100%; height: 150px;"></canvas>
                    <div class="signature-placeholder">
                        <i class="mdi mdi-pen"></i>
                        <span>Sign here</span>
                    </div>
                </div>
                <div class="signature-controls mt-3">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSignature('{{ $element->name }}')">
                        <i class="mdi mdi-refresh"></i> Clear Signature
                    </button>
                    <small class="text-muted ml-2">
                        <i class="mdi mdi-information-outline"></i> Use mouse or touch to sign
                    </small>
                </div>
                <input type="hidden" 
                       id="{{ $fieldId }}" 
                       name="{{ $fieldName }}"
                       value="{{ $fieldValue ?: $element->default_value ?: '' }}"
                       {{ $element->is_required ? 'required' : '' }}>
            </div>
            @break
            
        @case('client_select')
            <div class="custom-element-wrapper position-relative">
                <select class="form-control custom-element" 
                        id="{{ $fieldId }}" 
                        name="{{ $fieldName }}"
                        data-element-type="client_select"
                        data-saved-value="{{ $fieldValue }}"
                        {{ $element->is_required ? 'required' : '' }}
                        {{ $element->is_readonly ? 'disabled' : '' }}>
                    @if(!$element->is_required)
                        <option value="">{{ $element->placeholder ?: 'Select a client...' }}</option>
                    @endif
                    @if(isset($isArrayField) && $isArrayField)
                        {{-- Load static data for rows-section --}}
                        @php
                            $clientOptions = $element->getDynamicOptions();
                        @endphp
                        @foreach($clientOptions as $option)
                            <option value="{{ $option['value'] }}" {{ ($fieldValue == $option['value']) ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    @endif
                </select>
                <button type="button" class="btn btn-sm btn-primary floating-add-btn" data-toggle="modal" data-target="#addClientModal" title="Add New Client">
                    <i class="mdi mdi-plus"></i>
                </button>
            </div>
            @break
            
        @case('sample_type_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="sample_type_select"
                    data-saved-value="{{ $fieldValue }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a sample type...' }}</option>
                @endif
                @if(isset($isArrayField) && $isArrayField)
                    {{-- Load static data for rows-section --}}
                    @php
                        $sampleTypeOptions = $element->getDynamicOptions();
                    @endphp
                    @foreach($sampleTypeOptions as $option)
                        <option value="{{ $option['value'] }}" {{ ($fieldValue == $option['value']) ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                @endif
            </select>
            @break
            
        @case('client_unit_select')
            <div class="custom-element-wrapper position-relative">
                <select class="form-control custom-element" 
                        id="{{ $fieldId }}" 
                        name="{{ $fieldName }}"
                        data-element-type="client_unit_select"
                        data-depends-on="client_select"
                        data-saved-value="{{ $fieldValue }}"
                        {{ $element->is_required ? 'required' : '' }}
                        {{ $element->is_readonly ? 'disabled' : '' }}>
                    @if(!$element->is_required)
                        <option value="">{{ $element->placeholder ?: 'Select a client unit...' }}</option>
                    @endif
                    {{-- Options will be loaded dynamically based on selected client --}}
                </select>
                <button type="button" class="btn btn-sm btn-primary floating-add-btn" data-toggle="modal" data-target="#addClientUnitModal" title="Add New Client Unit">
                    <i class="mdi mdi-plus"></i>
                </button>
            </div>
            @break
            
        @case('client_contact_select')
            <div class="custom-element-wrapper position-relative">
                <select class="form-control custom-element" 
                        id="{{ $fieldId }}" 
                        name="{{ $fieldName }}"
                        data-element-type="client_contact_select"
                        data-depends-on="client_select"
                        data-saved-value="{{ $fieldValue }}"
                        {{ $element->is_required ? 'required' : '' }}
                        {{ $element->is_readonly ? 'disabled' : '' }}>
                    @if(!$element->is_required)
                        <option value="">{{ $element->placeholder ?: 'Select a client contact...' }}</option>
                    @endif
                    {{-- Options will be loaded dynamically based on selected client --}}
                </select>
                <button type="button" class="btn btn-sm btn-primary floating-add-btn" data-toggle="modal" data-target="#addClientContactModal" title="Add New Client Contact">
                    <i class="mdi mdi-plus"></i>
                </button>
            </div>
            @break
            
        @case('analysis_type_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="analysis_type_select"
                    data-depends-on="sample_type_select"
                    data-saved-value="{{ $fieldValue }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select an analysis type...' }}</option>
                @endif
                {{-- Options will be loaded dynamically based on selected sample type --}}
            </select>
            @break
            
        @case('analysis_elements_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="analysis_elements_select"
                    data-depends-on="analysis_type_select"
                    data-saved-value="{{ $fieldValue }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select analysis elements...' }}</option>
                @endif
                {{-- Options will be loaded dynamically based on selected analysis type --}}
            </select>
            @break
            
        @case('store_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="store_select"
                    data-saved-value="{{ $fieldValue }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a store...' }}</option>
                @endif
                @if(isset($isArrayField) && $isArrayField)
                    {{-- Load static data for rows-section --}}
                    @php
                        $storeOptions = $element->getDynamicOptions();
                    @endphp
                    @foreach($storeOptions as $option)
                        <option value="{{ $option['value'] }}" {{ ($fieldValue == $option['value']) ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                @endif
            </select>
            @break
            
        @case('store_slot_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="store_slot_select"
                    data-depends-on="store_select"
                    data-saved-value="{{ $fieldValue }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a store slot...' }}</option>
                @endif
                {{-- Options will be loaded dynamically based on selected store --}}
            </select>
            @break
            
        @case('sample_condition_select')
            <div class="custom-element-wrapper position-relative">
                <select class="form-control custom-element" 
                        id="{{ $fieldId }}" 
                        name="{{ $fieldName }}"
                        data-element-type="sample_condition_select"
                        data-saved-value="{{ $fieldValue }}"
                        {{ $element->is_required ? 'required' : '' }}
                        {{ $element->is_readonly ? 'disabled' : '' }}>
                    @if(!$element->is_required)
                        <option value="">{{ $element->placeholder ?: 'Select a sample condition...' }}</option>
                    @endif
                    @if(isset($isArrayField) && $isArrayField)
                        {{-- Load static data for rows-section --}}
                        @php
                            $sampleConditionOptions = $element->getDynamicOptions();
                        @endphp
                        @foreach($sampleConditionOptions as $option)
                            <option value="{{ $option['value'] }}" {{ ($fieldValue == $option['value']) ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    @endif
                </select>
                <button type="button" class="btn btn-sm btn-primary floating-add-btn" data-toggle="modal" data-target="#addSampleConditionModal" title="Add New Sample Condition">
                    <i class="mdi mdi-plus"></i>
                </button>
            </div>
            @break
            
        @case('standard_select')
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $fieldName }}"
                    data-element-type="standard_select"
                    data-saved-value="{{ $fieldValue }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a standard...' }}</option>
                @endif
                @if(isset($isArrayField) && $isArrayField)
                    {{-- Load static data for rows-section --}}
                    @php
                        $standardOptions = $element->getDynamicOptions();
                    @endphp
                    @foreach($standardOptions as $option)
                        <option value="{{ $option['value'] }}" {{ ($fieldValue == $option['value']) ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                @endif
            </select>
            @break
            
        @case('sample_point_select')
            @php
                $selectName = $fieldName . '[]';
                $savedValues = is_string($fieldValue) ? explode(',', $fieldValue) : (array) $fieldValue;
            @endphp
            
            <div class="custom-element-wrapper position-relative">
                <select class="form-control custom-element" 
                        id="{{ $fieldId }}" 
                        name="{{ $selectName }}"
                        multiple
                        data-element-type="sample_point_select"
                        data-depends-on="client_unit_select"
                        data-saved-value="{{ $fieldValue }}"
                        data-saved-multiple-values="{{ implode(',', $savedValues) }}"
                        {{ $element->is_required ? 'required' : '' }}
                        {{ $element->is_readonly ? 'disabled' : '' }}>
                    @if(!$element->is_required)
                        <option value="">{{ $element->placeholder ?: 'Select a sample point...' }}</option>
                    @endif
                    {{-- Options will be loaded dynamically based on selected client unit --}}
                    {{-- For multiple selects with saved values, store the values to be set after options load --}}
                </select>
                <button type="button" class="btn btn-sm btn-primary floating-add-btn" data-toggle="modal" data-target="#addSamplePointModal" title="Add New Sample Point">
                    <i class="mdi mdi-plus"></i>
                </button>
            </div>
            @break
            
        @case('calculation')
            <input type="text" 
                   class="form-control" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $fieldValue }}"
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
@if(in_array($element->element_type, ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select', 'analysis_type_select', 'analysis_elements_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select', 'sample_point_select']))
<script>
// Store element data for initialization when jQuery is ready
window.customElementsToInit = window.customElementsToInit || [];
window.customElementsToInit.push({
    elementId: '{{ $fieldId }}',
    elementType: '{{ $element->element_type }}',
    isRequired: {{ $element->is_required ? 'true' : 'false' }},
    placeholder: '{{ $element->placeholder ?: "Select..." }}'
});


</script>
@endif

<style>
/* Form group spacing for form elements */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    margin-bottom: 0.5rem;
    display: block;
    font-weight: 500;
    color: #495057;
}

.form-control {
    margin-top: 0.5rem;
}

.form-check-container {
    margin-top: 0.5rem;
}

.form-check {
    margin-bottom: 0.5rem;
}

.form-check:last-child {
    margin-bottom: 0;
}

/* Floating Add Button Styles */
.custom-element-wrapper {
    position: relative;
}

.floating-add-btn {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    border: none;
}

.floating-add-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.floating-add-btn i {
    font-size: 16px;
    line-height: 1;
}

/* Ensure select doesn't overlap with button */
.custom-element-wrapper .form-control {
    padding-right: 40px;
}
</style>

{{-- Signature pad JavaScript --}}
@if($element->element_type === 'signature')
<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize signature pad for {{ $element->name }}
    const canvas = document.getElementById('{{ $element->name }}_canvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const placeholder = canvas.parentElement.querySelector('.signature-placeholder');
    let isDrawing = false;
    let hasSignature = false;
    let resizeTimeout;
    let savedSignatureData = null; // Store signature data for redrawing after resize
    
    // Debounced resize function
    function debouncedResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(resizeCanvas, 100);
    }
    
    // Set up responsive canvas
    function resizeCanvas() {
        const container = canvas.parentElement;
        const wrapper = container.parentElement;
        
        // Get available width considering padding and borders
        const availableWidth = wrapper.clientWidth - 16; // Account for 8px padding on each side
        const containerWidth = Math.max(200, Math.min(availableWidth, 800)); // Min 200px, max 800px
        const containerHeight = 150; // Fixed height as requested
        
        // Set display size
        canvas.style.width = containerWidth + 'px';
        canvas.style.height = containerHeight + 'px';
        canvas.style.maxWidth = '100%';
        
        // Set actual canvas size (for drawing)
        canvas.width = containerWidth;
        canvas.height = containerHeight;
        
        // Set drawing properties
        ctx.strokeStyle = '#2c3e50';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.shadowColor = 'rgba(0, 0, 0, 0.1)';
        ctx.shadowBlur = 1;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 1;
        
        // Redraw signature if it exists
        if (savedSignatureData) {
            redrawSignature();
        }
    }
    
    // Initial resize
    resizeCanvas();
    
    // Load existing signature if available
    loadExistingSignature();
    
    // Function to redraw saved signature
    function redrawSignature() {
        if (savedSignatureData) {
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            };
            img.src = savedSignatureData;
        }
    }
    
    // Function to load existing signature data
    function loadExistingSignature() {
        const hiddenInput = document.getElementById('{{ $element->name }}');
        const existingValue = hiddenInput ? hiddenInput.value : null;
        
        if (existingValue && existingValue.trim() !== '') {
            // Check if it's a data URL (base64 image)
            if (existingValue.startsWith('data:image/')) {
                // Store the signature data for redrawing after resize
                savedSignatureData = existingValue;
                
                const img = new Image();
                img.onload = function() {
                    // Clear canvas first
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    
                    // Draw the existing signature
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    
                    // Update state
                    hasSignature = true;
                    
                    // Hide placeholder
                    if (placeholder) {
                        placeholder.style.opacity = '0';
                    }
                    
                    // Update visual state
                    canvas.style.borderColor = '#28a745';
                    canvas.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.25)';
                };
                img.onerror = function() {
                    console.warn('Failed to load existing signature image');
                };
                img.src = existingValue;
            }
        }
    }
    
    // Resize on window resize (debounced)
    window.addEventListener('resize', debouncedResize);
    
    // Use ResizeObserver for better responsiveness
    if (window.ResizeObserver) {
        const resizeObserver = new ResizeObserver(debouncedResize);
        resizeObserver.observe(canvas.parentElement.parentElement);
    }
    
    // Mouse events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Touch events for mobile
    canvas.addEventListener('touchstart', handleTouch);
    canvas.addEventListener('touchmove', handleTouch);
    canvas.addEventListener('touchend', stopDrawing);
    
    // Hover effects
    canvas.addEventListener('mouseenter', function() {
        if (!hasSignature) {
            canvas.style.borderColor = '#007bff';
            canvas.style.boxShadow = '0 0 0 2px rgba(0, 123, 255, 0.25)';
        }
    });
    
    canvas.addEventListener('mouseleave', function() {
        if (!hasSignature) {
            canvas.style.borderColor = '#dee2e6';
            canvas.style.boxShadow = 'none';
        }
    });
    
    function startDrawing(e) {
        isDrawing = true;
        hasSignature = true;
        
        // Hide placeholder
        if (placeholder) {
            placeholder.style.opacity = '0';
        }
        
        // Add visual feedback
        canvas.style.borderColor = '#28a745';
        canvas.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.25)';
        
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        ctx.beginPath();
        ctx.moveTo((e.clientX - rect.left) * scaleX, (e.clientY - rect.top) * scaleY);
    }
    
    function draw(e) {
        if (!isDrawing) return;
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        ctx.lineTo((e.clientX - rect.left) * scaleX, (e.clientY - rect.top) * scaleY);
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
        // Store signature data for redrawing after resize
        savedSignatureData = dataURL;
    }
    
    // Clear signature
    window.clearSignature = function(elementName) {
        const canvas = document.getElementById(elementName + '_canvas');
        const ctx = canvas.getContext('2d');
        const placeholder = canvas.parentElement.querySelector('.signature-placeholder');
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById(elementName).value = '';
        
        // Clear saved signature data
        savedSignatureData = null;
        hasSignature = false;
        
        // Show placeholder
        if (placeholder) {
            placeholder.style.opacity = '1';
        }
        
        // Reset visual state
        canvas.style.borderColor = '#dee2e6';
        canvas.style.boxShadow = 'none';
    };
});
</script>

<style>
.signature-container {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.signature-pad-wrapper {
    position: relative;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

.signature-pad-wrapper:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
}

.signature-canvas {
    display: block;
    border-radius: 6px;
    cursor: crosshair;
    transition: all 0.3s ease;
    background: #ffffff;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.signature-placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
    pointer-events: none;
    transition: opacity 0.3s ease;
    z-index: 1;
}

.signature-placeholder i {
    font-size: 24px;
    margin-bottom: 4px;
    opacity: 0.7;
}

.signature-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

.signature-controls .btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.signature-controls .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.signature-controls small {
    display: flex;
    align-items: center;
    font-size: 12px;
}

.signature-controls small i {
    margin-right: 4px;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .signature-controls {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .signature-controls small {
        margin-top: 4px;
    }
}
</style>
@endif

{{-- Modals for adding new custom elements --}}
@if(in_array($element->element_type, ['sample_point_select', 'sample_condition_select', 'client_select', 'client_unit_select', 'client_contact_select']))
<!-- Add Sample Point Modal -->
<div class="modal fade" id="addSamplePointModal" tabindex="-1" role="dialog" aria-labelledby="addSamplePointModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSamplePointModalLabel">Add New Sample Point</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSamplePointForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="samplePointName">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="samplePointName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="samplePointUnit">Client Unit <span class="text-danger">*</span></label>
                        <select class="form-control" id="samplePointUnit" name="crm_company_unit_id" required>
                            <option value="">Select a client unit...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="samplePointGps">GPS Coordinates</label>
                        <input type="text" class="form-control" id="samplePointGps" name="gps" placeholder="e.g., -1.2921, 36.8219">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Sample Point</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Sample Condition Modal -->
<div class="modal fade" id="addSampleConditionModal" tabindex="-1" role="dialog" aria-labelledby="addSampleConditionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSampleConditionModalLabel">Add New Sample Condition</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSampleConditionForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sampleConditionName">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sampleConditionName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="sampleConditionType">Sample Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="sampleConditionType" name="sample_type_id" required>
                            <option value="">Select a sample type...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sampleConditionShortName">Short Name</label>
                        <input type="text" class="form-control" id="sampleConditionShortName" name="short_name">
                    </div>
                    <div class="form-group">
                        <label for="sampleConditionReportingTime">Reporting Time (days)</label>
                        <input type="number" class="form-control" id="sampleConditionReportingTime" name="reporting_time" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Sample Condition</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClientForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clientName">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clientName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clientCode">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clientCode" name="code" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clientEmail">Email</label>
                                <input type="email" class="form-control" id="clientEmail" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clientTelephone">Telephone</label>
                                <input type="text" class="form-control" id="clientTelephone" name="telephone1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="clientPostalAddress">Postal Address</label>
                        <textarea class="form-control" id="clientPostalAddress" name="postal_address" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="clientPhysicalAddress">Physical Address</label>
                        <textarea class="form-control" id="clientPhysicalAddress" name="physical_address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Client Unit Modal -->
<div class="modal fade" id="addClientUnitModal" tabindex="-1" role="dialog" aria-labelledby="addClientUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientUnitModalLabel">Add New Client Unit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClientUnitForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="clientUnitName">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clientUnitName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="clientUnitClient">Client <span class="text-danger">*</span></label>
                        <select class="form-control" id="clientUnitClient" name="crm_customer_id" required>
                            <option value="">Select a client...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Client Contact Modal -->
<div class="modal fade" id="addClientContactModal" tabindex="-1" role="dialog" aria-labelledby="addClientContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientContactModalLabel">Add New Client Contact</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClientContactForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contactFirstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactFirstName" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contactMiddleName">Middle Name</label>
                                <input type="text" class="form-control" id="contactMiddleName" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contactLastName">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactLastName" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactEmail">Email</label>
                                <input type="email" class="form-control" id="contactEmail" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactTelephone">Telephone</label>
                                <input type="text" class="form-control" id="contactTelephone" name="telephone">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactMobile">Mobile</label>
                                <input type="text" class="form-control" id="contactMobile" name="mobile">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactJobOccupation">Job Occupation</label>
                                <input type="text" class="form-control" id="contactJobOccupation" name="job_occupation">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="contactClient">Client <span class="text-danger">*</span></label>
                        <select class="form-control" id="contactClient" name="crm_customer_id" required>
                            <option value="">Select a client...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Notification function to replace toastr
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="mdi ${iconClass}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Remove existing notifications
    $('.alert[style*="position: fixed"]').remove();
    
    // Add new notification
    $('body').append(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('.alert[style*="position: fixed"]').fadeOut();
    }, 5000);
}

$(document).ready(function() {
    // Prevent duplicate event handlers
    if (window.customElementModalsInitialized) {
        return;
    }
    window.customElementModalsInitialized = true;

    // Load clients for dependent dropdowns
    function loadClients() {
        $.get('/api/clients', function(data) {
            $('#clientUnitClient, #contactClient').empty().append('<option value="">Select a client...</option>');
            $.each(data, function(index, client) {
                $('#clientUnitClient, #contactClient').append('<option value="' + client.id + '">' + client.name + '</option>');
            });
        });
    }

    // Load sample types for sample condition
    function loadSampleTypes() {
        $.get('/api/sample-types', function(data) {
            $('#sampleConditionType').empty().append('<option value="">Select a sample type...</option>');
            $.each(data, function(index, type) {
                $('#sampleConditionType').append('<option value="' + type.id + '">' + type.name + '</option>');
            });
        });
    }

    // Load client units for sample point from existing page data
    function loadClientUnits() {
        // Get client units from existing client_unit_select on the page
        var clientUnitSelect = $('select[data-element-type="client_unit_select"]');
        if (clientUnitSelect.length > 0) {
            $('#samplePointUnit').empty().append('<option value="">Select a client unit...</option>');
            clientUnitSelect.find('option').each(function() {
                var value = $(this).val();
                var text = $(this).text();
                if (value && value !== '') {
                    $('#samplePointUnit').append('<option value="' + value + '">' + text + '</option>');
                }
            });
        } else {
            // Fallback to API if no client_unit_select found on page
            $.get('/api/client-units', function(data) {
                $('#samplePointUnit').empty().append('<option value="">Select a client unit...</option>');
                $.each(data, function(index, unit) {
                    $('#samplePointUnit').append('<option value="' + unit.id + '">' + unit.name + '</option>');
                });
            });
        }
    }

    // Initialize dropdowns
    loadClients();
    loadSampleTypes();
    loadClientUnits();

    // Refresh sample point modal client units when modal is shown
    $('#addSamplePointModal').on('show.bs.modal', function() {
        loadClientUnits();
    });

    // Remove existing event handlers to prevent duplicates
    $('#addSamplePointForm').off('submit');
    $('#addSampleConditionForm').off('submit');
    $('#addClientForm').off('submit');
    $('#addClientUnitForm').off('submit');
    $('#addClientContactForm').off('submit');

    // Handle form submissions
    $('#addSamplePointForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/sample-points',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to sample point select
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                $('select[data-element-type="sample_point_select"]').append(newOption);
                $('#addSamplePointModal').modal('hide');
                $('#addSamplePointForm')[0].reset();
                showNotification('success', 'Sample point added successfully!');
            },
            error: function(xhr) {
                showNotification('error', 'Error adding sample point: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addSamplePointForm').data('submitting', false);
            }
        });
    });

    $('#addSampleConditionForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/sample-conditions',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to sample condition select
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                $('select[data-element-type="sample_condition_select"]').append(newOption);
                $('#addSampleConditionModal').modal('hide');
                $('#addSampleConditionForm')[0].reset();
                showNotification('success', 'Sample condition added successfully!');
            },
            error: function(xhr) {
                showNotification('error', 'Error adding sample condition: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addSampleConditionForm').data('submitting', false);
            }
        });
    });

    $('#addClientForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/clients',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to client select
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                $('select[data-element-type="client_select"]').append(newOption);
                $('#addClientModal').modal('hide');
                $('#addClientForm')[0].reset();
                showNotification('success', 'Client added successfully!');
                // Reload dependent dropdowns
                loadClientUnits();
            },
            error: function(xhr) {
                showNotification('error', 'Error adding client: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addClientForm').data('submitting', false);
            }
        });
    });

    $('#addClientUnitForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/client-units',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to client unit select
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                $('select[data-element-type="client_unit_select"]').append(newOption);
                $('#addClientUnitModal').modal('hide');
                $('#addClientUnitForm')[0].reset();
                showNotification('success', 'Client unit added successfully!');
                // Reload sample points and refresh sample point modal dropdown
                loadClientUnits();
                // Also refresh the sample point modal's client unit dropdown
                var clientUnitSelect = $('select[data-element-type="client_unit_select"]');
                if (clientUnitSelect.length > 0) {
                    $('#samplePointUnit').empty().append('<option value="">Select a client unit...</option>');
                    clientUnitSelect.find('option').each(function() {
                        var value = $(this).val();
                        var text = $(this).text();
                        if (value && value !== '') {
                            $('#samplePointUnit').append('<option value="' + value + '">' + text + '</option>');
                        }
                    });
                }
            },
            error: function(xhr) {
                showNotification('error', 'Error adding client unit: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addClientUnitForm').data('submitting', false);
            }
        });
    });

    $('#addClientContactForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/client-contacts',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to client contact select
                var newOption = '<option value="' + response.id + '">' + response.first_name + ' ' + response.last_name + '</option>';
                $('select[data-element-type="client_contact_select"]').append(newOption);
                $('#addClientContactModal').modal('hide');
                $('#addClientContactForm')[0].reset();
                showNotification('success', 'Client contact added successfully!');
            },
            error: function(xhr) {
                showNotification('error', 'Error adding client contact: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addClientContactForm').data('submitting', false);
            }
        });
    });
});
</script>
@endif