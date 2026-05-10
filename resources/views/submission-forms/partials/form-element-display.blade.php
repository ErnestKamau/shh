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
    $displayValue = $existingValue ? $existingValue->getDisplayValue() : $element->default_value;
@endphp

<div class="form-group">
    <label for="{{ $fieldId }}" class="{{ $element->is_required ? 'required' : '' }}">
        {{ $element->label }}
        @if($element->is_required)
            <span class="text-danger">*</span>
        @endif
        @if($element->isMapped())
            <span class="badge badge-outline-info badge-sm ml-1" 
                  title="Mapped to {{ ucfirst(str_replace('_', ' ', $element->mapping_table)) }}.{{ ucfirst(str_replace('_', ' ', $element->mapping_field)) }}">
                <i class="mdi mdi-database"></i>
            </span>
        @endif
    </label>

    @if($element->element_type === 'text')
        <input type="text" 
               class="form-control" 
               id="{{ $fieldId }}" 
               name="{{ $fieldName }}"
               value="{{ $fieldValue }}"
               data-element-type="{{ $element->custom_element_type }}"
               data-saved-value="{{ $fieldValue }}"
               placeholder="{{ $element->placeholder ?? '' }}"
               {{ $element->is_required ? 'required' : '' }}
               readonly>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif($element->element_type === 'textarea')
        <textarea class="form-control" 
                  id="{{ $fieldId }}" 
                  name="{{ $fieldName }}"
                  data-element-type="{{ $element->custom_element_type }}"
                  data-saved-value="{{ $fieldValue }}"
                  placeholder="{{ $element->placeholder ?? '' }}"
                  {{ $element->is_required ? 'required' : '' }}
                  rows="3"
                  readonly>{{ $fieldValue }}</textarea>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif($element->element_type === 'number')
        <input type="number" 
               class="form-control" 
               id="{{ $fieldId }}" 
               name="{{ $fieldName }}"
               value="{{ $fieldValue }}"
               data-element-type="{{ $element->custom_element_type }}"
               data-saved-value="{{ $fieldValue }}"
               placeholder="{{ $element->placeholder ?? '' }}"
               {{ $element->is_required ? 'required' : '' }}
               readonly>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif($element->element_type === 'email')
        <input type="email" 
               class="form-control" 
               id="{{ $fieldId }}" 
               name="{{ $fieldName }}"
               value="{{ $fieldValue }}"
               data-element-type="{{ $element->custom_element_type }}"
               data-saved-value="{{ $fieldValue }}"
               placeholder="{{ $element->placeholder ?? '' }}"
               {{ $element->is_required ? 'required' : '' }}
               readonly>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif($element->element_type === 'date')
        <input type="date" 
               class="form-control" 
               id="{{ $fieldId }}" 
               name="{{ $fieldName }}"
               value="{{ $fieldValue }}"
               data-element-type="{{ $element->custom_element_type }}"
               data-saved-value="{{ $fieldValue }}"
               {{ $element->is_required ? 'required' : '' }}
               readonly>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif($element->element_type === 'datetime')
        <input type="datetime-local" 
               class="form-control" 
               id="{{ $fieldId }}" 
               name="{{ $fieldName }}"
               value="{{ $fieldValue }}"
               data-element-type="{{ $element->custom_element_type }}"
               data-saved-value="{{ $fieldValue }}"
               {{ $element->is_required ? 'required' : '' }}
               readonly>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif($element->element_type === 'checkbox')
        <div class="form-check">
            <input type="checkbox" 
                   class="form-check-input" 
                   id="{{ $fieldId }}" 
                   name="{{ $fieldName }}"
                   value="1"
                   data-element-type="{{ $element->custom_element_type }}"
                   data-saved-value="{{ $fieldValue }}"
                   {{ $fieldValue == '1' || $fieldValue == 'true' ? 'checked' : '' }}
                   {{ $element->is_required ? 'required' : '' }}
                   disabled>
            <label class="form-check-label" for="{{ $fieldId }}">
                {{ $element->label }}
            </label>
        </div>
        <div class="field-display-value">{{ $fieldValue == '1' || $fieldValue == 'true' ? 'Yes' : 'No' }}</div>

    @elseif($element->element_type === 'radio')
        <div class="radio-group">
            @if($element->options)
                @foreach(json_decode($element->options, true) as $option)
                    <div class="form-check">
                        <input type="radio" 
                               class="form-check-input" 
                               id="{{ $fieldId }}_{{ $loop->index }}" 
                               name="{{ $fieldName }}"
                               value="{{ $option['value'] }}"
                               data-element-type="{{ $element->custom_element_type }}"
                               data-saved-value="{{ $fieldValue }}"
                               {{ $fieldValue == $option['value'] ? 'checked' : '' }}
                               {{ $element->is_required ? 'required' : '' }}
                               disabled>
                        <label class="form-check-label" for="{{ $fieldId }}_{{ $loop->index }}">
                            {{ $option['label'] }}
                        </label>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif($element->element_type === 'select')
        @php
            // Check if this is a sample point field (multiple values)
            $isMultiple = $element->custom_element_type === 'sample_point_select';
            $fieldName = $isMultiple ? $fieldName . '[]' : $fieldName;
            $selectedValues = $isMultiple && $fieldValue ? explode(',', $fieldValue) : [$fieldValue];
        @endphp
        <select class="form-control custom-select" 
                id="{{ $fieldId }}" 
                name="{{ $fieldName }}"
                data-element-type="{{ $element->custom_element_type }}"
                data-saved-value="{{ $fieldValue }}"
                {{ $element->is_required ? 'required' : '' }}
                {{ $isMultiple ? 'multiple' : '' }}
                data-depends-on="{{ $element->dependency_info['depends_on'] ?? '' }}"
                data-dependency-level="{{ $element->dependency_info['dependency_level'] ?? 0 }}">
            @if(!$isMultiple)
                <option value="">Select...</option>
            @endif
            @if($element->options)
                @foreach(json_decode($element->options, true) as $option)
                    <option value="{{ $option['value'] }}" 
                            {{ in_array($option['value'], $selectedValues) ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            @endif
        </select>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif($element->element_type === 'multiselect')
        <select class="form-control custom-select" 
                id="{{ $fieldId }}" 
                name="{{ $fieldName }}[]"
                data-element-type="{{ $element->custom_element_type }}"
                data-saved-value="{{ $fieldValue }}"
                {{ $element->is_required ? 'required' : '' }}
                multiple
                disabled>
            @if($element->options)
                @foreach(json_decode($element->options, true) as $option)
                    <option value="{{ $option['value'] }}" 
                            {{ in_array($option['value'], explode(',', $fieldValue)) ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            @endif
        </select>
        <div class="field-display-value">{{ $displayValue }}</div>

    @elseif(in_array($element->element_type, ['file', 'camera_photo'], true))
        @if($existingValue && $existingValue->file_path)
            <div class="file-display">
                <a href="{{ Storage::url($existingValue->file_path) }}" 
                   target="_blank" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="mdi mdi-eye"></i> View {{ $element->element_type === 'camera_photo' ? 'Photo' : 'File' }}
                </a>
                <small class="text-muted d-block mt-1">
                    {{ basename($existingValue->file_path) }}
                </small>
            </div>
        @else
            <div class="file-display">
                <span class="text-muted">No {{ $element->element_type === 'camera_photo' ? 'photo' : 'file' }} uploaded</span>
            </div>
        @endif

    @else
        {{-- Fallback for unknown element types --}}
        <div class="field-display-value">{{ $displayValue }}</div>
    @endif

    @if($element->help_text)
        <small class="form-text text-muted">{{ $element->help_text }}</small>
    @endif
</div>

<style>
.field-display-value {
    margin-top: 5px;
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-weight: 500;
    color: #495057;
}

.field-display-value.empty {
    color: #6c757d;
    font-style: italic;
}

.file-display {
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.radio-group .form-check {
    margin-bottom: 5px;
}

.required::after {
    content: " *";
    color: #dc3545;
}
</style>
