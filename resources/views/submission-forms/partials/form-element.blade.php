@php
    $fieldName = isset($isArrayField) && $isArrayField ? $element->name . '[' . $rowIndex . ']' : $element->name;
    $fieldId = isset($isArrayField) && $isArrayField ? $element->name . '_' . $rowIndex : $element->name;
    
    // Get existing values
    $existingValue = null;
    $existingValues_all = null;
    if (isset($existingValues) && $existingValues) {
        if (isset($isArrayField) && $isArrayField && isset($rowIndex)) {
            // For array fields, find value by element ID and array index
            $existingValue = $existingValues->where('submission_form_element_id', $element->id)
                                          ->where('array_index', $rowIndex)
                                          ->first();
        } else {
            // For regular fields, find value(s) by element ID
            $existingValue = $existingValues->where('submission_form_element_id', $element->id)->first();
            // Also get all values for this element (for checkboxes with multiple selections)
            $existingValues_all = $existingValues->where('submission_form_element_id', $element->id);
        }
    }
    
    $fieldValue = $existingValue ? $existingValue->value : $element->default_value;
    // For multiple checkboxes, collect all saved values
    $allSavedValues = $existingValues_all ? $existingValues_all->pluck('value')->toArray() : [];
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
            @php
                $plainTextValue = trim((string) ($fieldValue ?: $element->default_value ?: ''));
            @endphp
            @if($plainTextValue !== '')
                <div class="plain-text-element" id="{{ $fieldId }}">
                    {!! nl2br(e($plainTextValue)) !!}
                </div>
            @endif
            <input type="hidden" 
                   name="{{ $fieldName }}" 
                   value="{{ $plainTextValue }}">
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
                            {{ ($fieldValue == ($option['value'] ?? $option)) ? 'selected' : '' }}>
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
                               {{ ($fieldValue == ($option['value'] ?? $option)) ? 'checked' : '' }}
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
                                   {{ in_array(($option['value'] ?? $option), $allSavedValues) ? 'checked' : '' }}
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
                           {{ $fieldValue ? 'checked' : '' }}
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

        @case('camera_photo')
            <input type="file"
                   class="form-control"
                   id="{{ $fieldId }}"
                   name="{{ $fieldName }}"
                   accept="image/*"
                   capture="environment"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'disabled' : '' }}>
            <small class="form-text text-muted">
                {{ $element->help_text ?: 'Take a photo with your camera or choose one from gallery.' }}
            </small>
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
                    {{-- All client_select fields now use Select2 AJAX with pagination (no static loading) --}}
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
                        @if(isset($allowedSampleTypeIds) && is_array($allowedSampleTypeIds) && !in_array($option['value'], $allowedSampleTypeIds))
                            @continue
                        @endif
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
            
        @case('client_submission_officers_select')
            <div class="custom-element-wrapper position-relative">
                <select class="form-control custom-element" 
                        id="{{ $fieldId }}" 
                        name="{{ $fieldName }}"
                        data-element-type="client_submission_officers_select"
                        data-depends-on="client_select"
                        data-saved-value="{{ $fieldValue }}"
                        {{ $element->is_required ? 'required' : '' }}
                        {{ $element->is_readonly ? 'disabled' : '' }}>
                    @if(!$element->is_required)
                        <option value="">{{ $element->placeholder ?: 'Select a submission officer...' }}</option>
                    @endif
                    {{-- Options will be loaded dynamically based on selected client (only contacts with can_submit_sample = 1) --}}
                </select>
            </div>
            @break
            
        @case('contact_signature')
            @php
                $dependsField = $element->options && isset($element->options['depends']) ? $element->options['depends'] : '';
            @endphp
            <div class="contact-signature-container" 
                 id="{{ $fieldId }}_container"
                 data-depends-on="{{ $dependsField }}"
                 data-element-id="{{ $fieldId }}"
                 data-element-name="{{ $element->name }}"
                 data-contact-id="">
                {{-- Display existing signature if available --}}
                <div id="{{ $fieldId }}_signature_display" class="signature-display-wrapper" style="display: none;">
                    <img id="{{ $fieldId }}_signature_image" 
                         class="signature-preview" 
                         src="" 
                         alt="Contact signature"
                         style="max-height: 150px; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px; background-color: #f8f9fa; width: 100%; object-fit: contain;">
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="clearContactSignature('{{ $fieldId }}')">
                        <i class="mdi mdi-refresh"></i> Clear and Sign New
                    </button>
                </div>
                
                {{-- Signature pad (shown when no signature or when cleared) --}}
                <div id="{{ $fieldId }}_signature_pad" class="signature-container" style="display: none;">
                    <div class="signature-pad-wrapper" style="position: relative; width: 100%; border: 1px solid #dee2e6; border-radius: 4px; background-color: #fff; overflow: hidden;">
                        <canvas id="{{ $fieldId }}_canvas" 
                                class="signature-canvas"
                                style="width: 100%; height: 150px; display: block; cursor: crosshair; touch-action: none;"></canvas>
                        <div class="signature-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; color: #6c757d; text-align: center; z-index: 1;">
                            <i class="mdi mdi-pen" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                            <span>Sign here</span>
                        </div>
                    </div>
                    <div class="signature-controls mt-3">
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearContactSignaturePad('{{ $fieldId }}')">
                                <i class="mdi mdi-refresh"></i> Clear Signature
                            </button>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="{{ $fieldId }}_save_signature" name="{{ $fieldName }}_save_signature">
                                <label class="form-check-label" for="{{ $fieldId }}_save_signature">
                                    <i class="mdi mdi-content-save"></i> Save for future use
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="mdi mdi-information-outline"></i> Use mouse or touch to sign
                        </small>
                    </div>
                </div>
                
                {{-- Placeholder when no contact selected --}}
                <div id="{{ $fieldId }}_placeholder" class="signature-placeholder text-muted" style="padding: 20px; text-align: center; border: 1px dashed #dee2e6; border-radius: 4px; background-color: #f8f9fa; min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <i class="mdi mdi-account-check" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                    <small>Select a contact to view or create signature</small>
                </div>
                
                <input type="hidden" 
                       id="{{ $fieldId }}" 
                       name="{{ $fieldName }}"
                       value="{{ $fieldValue ?: '' }}">
            </div>
            @break

        @case('depended_field')
            <input type="text"
                   class="form-control"
                   id="{{ $fieldId }}"
                   name="{{ $fieldName }}"
                   placeholder="{{ $element->placeholder ?? '' }}"
                   value="{{ $fieldValue ?? '' }}"
                   data-element-type="depended_field"
                   data-element-id="{{ $element->id }}"
                   data-depends-on="{{ $element->depends_on_field ?? '' }}"
                   {{ $element->is_required ? 'required' : '' }}
                   {{ $element->is_readonly ? 'readonly' : '' }}>
            @break
            
        @case('analysis_type_select')
            @php
                $selectName = $fieldName . '[]';
                $savedValues = is_string($fieldValue) ? explode(',', $fieldValue) : (array) $fieldValue;
            @endphp
            
            <select class="form-control custom-element" 
                    id="{{ $fieldId }}" 
                    name="{{ $selectName }}"
                    multiple
                    data-element-type="analysis_type_select"
                    data-depends-on="sample_type_select"
                    data-saved-value="{{ $fieldValue }}"
                    data-saved-multiple-values="{{ implode(',', $savedValues) }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select analysis types...' }}</option>
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
        
        @case('user_select')
            @php
                $defaultUserId = auth()->check() ? auth()->id() : null;
                $defaultUserName = auth()->check() ? auth()->user()->name : null;
                $resolvedValue = $fieldValue ?: $defaultUserId;
            @endphp
            <select class="form-control custom-element"
                    id="{{ $fieldId }}"
                    name="{{ $fieldName }}"
                    data-element-type="user_select"
                    data-saved-value="{{ $resolvedValue }}"
                    data-default-user-id="{{ $defaultUserId }}"
                    data-default-user-name="{{ $defaultUserName }}"
                    {{ $element->is_required ? 'required' : '' }}
                    {{ $element->is_readonly ? 'disabled' : '' }}>
                @if(!$element->is_required)
                    <option value="">{{ $element->placeholder ?: 'Select a user...' }}</option>
                @endif
                {{-- Options loaded dynamically; default user pre-selected when available --}}
            </select>
            @break
            
        @case('company_sub_unit_select')
            <div class="custom-element-wrapper position-relative">
                <select class="form-control custom-element" 
                        id="{{ $fieldId }}" 
                        name="{{ $fieldName }}"
                        data-element-type="company_sub_unit_select"
                        data-depends-on="client_unit_select"
                        data-saved-value="{{ $fieldValue }}"
                        {{ $element->is_required ? 'required' : '' }}
                        {{ $element->is_readonly ? 'disabled' : '' }}>
                    @if(!$element->is_required)
                        <option value="">{{ $element->placeholder ?: 'Select a company sub unit...' }}</option>
                    @endif
                    {{-- Options will be loaded dynamically based on selected client unit --}}
                </select>
                <button type="button" class="btn btn-sm btn-primary floating-add-btn" data-toggle="modal" data-target="#addCompanySubUnitModal" title="Add New Company Sub Unit">
                    <i class="mdi mdi-plus"></i>
                </button>
            </div>
            @break
            
        @case('user_signature')
            @php
                $dependsField = $element->options && isset($element->options['depends']) ? $element->options['depends'] : '';
            @endphp
            <div class="user-signature-container" 
                 id="{{ $fieldId }}_container"
                 data-depends-on="{{ $dependsField }}"
                 data-element-id="{{ $fieldId }}"
                 data-element-name="{{ $element->name }}">
                <div class="signature-preview-wrapper">
                    <img id="{{ $fieldId }}_image" 
                         class="signature-preview" 
                         src="" 
                         alt="User signature"
                         style="display: none; max-height: 120px; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px; background-color: #f8f9fa;">
                    <div id="{{ $fieldId }}_placeholder" class="signature-placeholder text-muted" style="padding: 20px; text-align: center; border: 1px dashed #dee2e6; border-radius: 4px; background-color: #f8f9fa;">
                        <i class="mdi mdi-account-check" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                        <small>Signature will appear here when user is selected</small>
                    </div>
                </div>
                <input type="hidden" 
                       id="{{ $fieldId }}" 
                       name="{{ $fieldName }}"
                       value="{{ $fieldValue ?: '' }}">
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
@if(in_array($element->element_type, ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select', 'client_submission_officers_select', 'analysis_type_select', 'analysis_elements_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select', 'sample_point_select', 'company_sub_unit_select', 'user_select', 'user_signature', 'contact_signature']))
@push('scripts')
<script>
// Store element data for initialization when jQuery is ready
window.customElementsToInit = window.customElementsToInit || [];
window.customElementsToInit.push({
    elementId: '{{ $fieldId }}',
    elementType: '{{ $element->element_type }}',
    isRequired: {{ $element->is_required ? 'true' : 'false' }},
    placeholder: '{{ $element->placeholder ?: "Select..." }}',
    @if($element->element_type === 'user_signature' && $element->options && isset($element->options['depends']))
    dependsOn: '{{ $element->options['depends'] }}',
    @endif
    @if($element->element_type === 'contact_signature' && $element->options && isset($element->options['depends']))
    dependsOn: '{{ $element->options['depends'] }}',
    @endif
});
</script>
@endpush
@endif

@push('styles')
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
    display: flex;
    align-items: stretch;
    gap: 0.5rem;
    width: 100%;
}

.floating-add-btn {
    position: static;
    width: 32px;
    min-width: 32px;
    height: 38px;
    border-radius: 50%;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
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
    flex: 1 1 auto;
    min-width: 0;
    padding-right: 0.75rem;
}

.custom-element-wrapper .select2-container {
    flex: 1 1 auto;
    min-width: 0;
}
</style>
@endpush

{{-- Signature pad JavaScript --}}
@if($element->element_type === 'contact_signature')
@push('styles')
<style>
.contact-signature-container {
    margin-bottom: 1rem;
}

.signature-display-wrapper {
    margin-bottom: 1rem;
}

.signature-display-wrapper img {
    display: block;
    margin: 0 auto;
}
</style>
@endpush
@endif

@if($element->element_type === 'signature')
@push('scripts')
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
@endpush

@push('styles')
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
@endpush
@endif
