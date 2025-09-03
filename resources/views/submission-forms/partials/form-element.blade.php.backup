<div class="form-group">
    <label for="{{ $element->name }}" class="{{ $element->is_required ? 'required' : '' }}">
        {{ $element->label }}
    </label>
    
    @switch($element->element_type)
        @case('text')
            <input type="text" 
                   class="form-control" 
                   id="{{ $element->name }}" 
                   name="{{ $element->name }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('number')
            <input type="number" 
                   class="form-control" 
                   id="{{ $element->name }}" 
                   name="{{ $element->name }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('email')
            <input type="email" 
                   class="form-control" 
                   id="{{ $element->name }}" 
                   name="{{ $element->name }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('date')
            <input type="date" 
                   class="form-control" 
                   id="{{ $element->name }}" 
                   name="{{ $element->name }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('datetime')
            <input type="datetime-local" 
                   class="form-control" 
                   id="{{ $element->name }}" 
                   name="{{ $element->name }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('textarea')
            <textarea class="form-control" 
                      id="{{ $element->name }}" 
                      name="{{ $element->name }}"
                      rows="3"
                      placeholder="{{ $element->placeholder }}"
                      {{ $element->is_required ? 'required' : '' }}
                      {{ $element->is_readonly ? 'readonly' : '' }}>{{ $element->default_value }}</textarea>
            @break
            
        @case('select')
            <select class="form-control" 
                    id="{{ $element->name }}" 
                    name="{{ $element->name }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select an option...' }}</option>
                @endif
                @if($element->options)
                    @foreach($element->options as $option)
                        <option value="{{ $option['value'] ?? $option }}" 
                                {{ ($element->default_value == ($option['value'] ?? $option)) ? 'selected' : '' }}>
                            {{ $option['label'] ?? $option }}
                        </option>
                    @endforeach
                @endif
            </select>
            @break
            
        @case('radio')
            @if($element->options)
                <div class="form-check-container">
                    @foreach($element->options as $index => $option)
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="radio" 
                                   id="{{ $element->name }}_{{ $index }}" 
                                   name="{{ $element->name }}"
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
            @endif
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
                           id="{{ $element->name }}" 
                           name="{{ $element->name }}"
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
                   class="form-control-file" 
                   id="{{ $element->name }}" 
                   name="{{ $element->name }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'disabled' : '' }}>
            @if($element->help_text)
                <small class="form-text text-muted">{{ $element->help_text }}</small>
            @endif
            @break
            
        @case('signature')
            <div class="signature-pad-container">
                <canvas id="{{ $element->name }}_canvas" 
                        class="signature-pad border" 
                        width="400" 
                        height="150"
                        style="border: 1px solid #ced4da; border-radius: 0.25rem; background: white;"></canvas>
                <input type="hidden" 
                       id="{{ $element->name }}" 
                       name="{{ $element->name }}"
                       {{ $element->is_required ? 'required' : '' }}>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary clear-signature" data-target="{{ $element->name }}_canvas">
                        <i class="mdi mdi-refresh"></i> Clear
                    </button>
                </div>
            </div>
            @break

        @case('client_select')
            <select class="form-control custom-element" 
                    id="{{ $element->name }}" 
                    name="{{ $element->name }}"
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
                    id="{{ $element->name }}" 
                    name="{{ $element->name }}"
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
                    id="{{ $element->name }}" 
                    name="{{ $element->name }}"
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
                    id="{{ $element->name }}" 
                    name="{{ $element->name }}"
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
            
        @default
            <input type="text" 
                   class="form-control" 
                   id="{{ $element->name }}" 
                   name="{{ $element->name }}"
                   placeholder="{{ $element->placeholder }}"
                   value="{{ $element->default_value }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
    @endswitch
    
    @if($element->help_text && $element->element_type !== 'file')
        <small class="form-text text-muted">{{ $element->help_text }}</small>
    @endif
    
    {{-- Validation feedback placeholder --}}
    <div class="invalid-feedback"></div>
</div>

@if($element->element_type === 'signature')
    @push('scripts')
    <script>
    $(document).ready(function() {
        // Initialize signature pad for {{ $element->name }}
        const canvas = document.getElementById('{{ $element->name }}_canvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        
        // Set up canvas
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        
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
        $('.clear-signature[data-target="{{ $element->name }}_canvas"]').on('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('{{ $element->name }}').value = '';
        });
    });
    </script>
    @endpush
@endif

@if(in_array($element->element_type, ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select']))
    @push('scripts')
    <script>
    $(document).ready(function() {
        const elementId = '{{ $element->name }}';
        const elementType = '{{ $element->element_type }}';
        
        // Load initial options for non-dependent elements
        if (elementType === 'client_select' || elementType === 'sample_type_select') {
            loadDynamicOptions(elementId, elementType);
        }
        
        // Handle client selection change for dependent elements
        if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
            // Find the client select element in the same form
            const clientSelect = $('select[data-element-type="client_select"]');
            
            if (clientSelect.length > 0) {
                // Load options when client changes
                clientSelect.on('change', function() {
                    const clientId = $(this).val();
                    if (clientId) {
                        loadDynamicOptions(elementId, elementType, clientId);
                    } else {
                        // Clear dependent options
                        $('#' + elementId).html('<option value="">{{ $element->placeholder ?: "Select..." }}</option>');
                    }
                });
                
                // Load options if client is already selected
                const currentClientId = clientSelect.val();
                if (currentClientId) {
                    loadDynamicOptions(elementId, elementType, currentClientId);
                }
            }
        }
        
        function loadDynamicOptions(elementId, elementType, clientId = null) {
            const select = $('#' + elementId);
            const originalHtml = select.html();
            
            // Show loading state
            select.html('<option value="">Loading...</option>').prop('disabled', true);
            
            // Make AJAX request
            $.ajax({
                url: '{{ route("submission-forms.dynamic-options") }}',
                method: 'GET',
                data: {
                    element_type: elementType,
                    client_id: clientId
                },
                success: function(response) {
                    let html = '';
                    
                    // Add placeholder option if not required
                    @if(!$element->is_required)
                        const placeholder = select.data('placeholder') || '{{ $element->placeholder ?: "Select..." }}';
                        html += '<option value="">' + placeholder + '</option>';
                    @endif
                    
                    // Add options from response
                    if (response.options && response.options.length > 0) {
                        response.options.forEach(function(option) {
                            html += '<option value="' + option.value + '">' + option.label + '</option>';
                        });
                    }
                    
                    select.html(html).prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading options:', error);
                    select.html(originalHtml).prop('disabled', false);
                    
                    // Show error message
                    if (xhr.status === 403) {
                        alert('You do not have permission to access this data.');
                    } else {
                        alert('Error loading options. Please try again.');
                    }
                }
            });
        }
    });
    </script>
    @endpush
@endif