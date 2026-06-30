@php
    $wirePrefix = $wirePrefix ?? ('formData.' . ($field['name'] ?? ''));
    $fieldId = $fieldId ?? ($field['name'] ?? 'field');
    $rowIndex = $rowIndex ?? null;
    $fieldName = $field['name'] ?? '';
    $compact = $compact ?? false;
    $controlClass = $compact ? 'form-control form-control-xs' : 'form-control form-control-sm';
    $compactStyle = $compact ? 'padding: 2px 5px; height: auto; font-size: 11px;' : '';
@endphp
@if(! $compact)
    <label for="field_{{ $fieldId }}" class="font-weight-bold text-secondary small">
        {{ $field['label'] ?? $fieldName }}
        @if($field['required'] ?? false)
            <span class="text-danger">*</span>
        @endif
    </label>
@endif

@if($fieldName === 'job_number' || $fieldName === 'crm_contact_id')
    {{-- Hidden in walk-in TRF modal --}}
@elseif(in_array($fieldName, ['customer_name', 'client_name', 'customer', 'client'], true))
    <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        @if($compactStyle) style="{{ $compactStyle }}" @endif>
        <option value="">-- Select Customer --</option>
        @foreach($this->customers as $cust)
            <option value="{{ $cust->name }}">{{ $cust->name }}</option>
        @endforeach
    </select>
@elseif(in_array($fieldName, ['analysis_type', 'analysis_types'], true))
    <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        @if($compactStyle) style="{{ $compactStyle }}" @endif>
        <option value="">-- Select Sample Type --</option>
        @foreach($this->analysisTypes as $at)
            <option value="{{ $at->name }}">{{ $at->name }}</option>
        @endforeach
    </select>
@elseif(($field['type'] ?? '') === 'analysis_type_select' || $fieldName === 'analysis_type_id')
    <select id="field_{{ $fieldId }}" wire:model.live="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        @if($compactStyle) style="{{ $compactStyle }}" @endif>
        <option value="">-- Select Sample Type --</option>
        @foreach($this->analysisTypes as $at)
            <option value="{{ $at->id }}">{{ $at->name }}</option>
        @endforeach
    </select>
@elseif(in_array($fieldName, ['parameter', 'parameters'], true) || ($field['type'] ?? '') === 'analysis_elements_select')
    @if($rowIndex !== null)
        @include('livewire.partials.walk-in-trf-parameters-cell', [
            'fieldId' => $fieldId,
            'rowIndex' => $rowIndex,
            'wirePrefix' => $wirePrefix,
            'compact' => $compact,
        ])
    @else
        <select id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
            class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
            @if($compactStyle) style="{{ $compactStyle }}" @endif>
            <option value="">-- Select Parameter --</option>
            @foreach($this->parametersForRow($rowIndex) as $param)
                <option value="{{ $param->name }}">{{ $param->name }}</option>
            @endforeach
        </select>
    @endif
@elseif(($field['type'] ?? '') === 'signature')
    @php
        $sigFieldName = $fieldName ?: 'customer_rep_signature';
        $canvasId = 'trf-sig-'.$fieldId;
    @endphp
    <div class="acc-signature-pad trf-signature-pad" wire:ignore>
        <canvas id="{{ $canvasId }}-canvas" class="trf-signature-canvas" data-field="{{ $sigFieldName }}" data-livewire-model="{{ $wirePrefix }}" style="width: 100%; height: {{ $compact ? '80' : '120' }}px; touch-action: none;"></canvas>
        <div class="acc-signature-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary trf-signature-clear" data-canvas="{{ $canvasId }}-canvas" data-input="field_{{ $fieldId }}">Clear</button>
        </div>
    </div>
    <input type="hidden" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}">
@elseif(($field['type'] ?? '') === 'textarea')
    <textarea id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        placeholder="Enter {{ strtolower($field['label'] ?? $fieldName) }}" rows="{{ $compact ? 2 : 2 }}"
        @if($compactStyle) style="{{ $compactStyle }}" @endif
        @if($field['readonly'] ?? false) readonly @endif></textarea>
@elseif(($field['type'] ?? '') === 'radio')
    @foreach(($field['options'] ?? []) as $opt)
        @php
            $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
            $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
        @endphp
        <div class="custom-control custom-radio {{ $compact ? 'mb-0' : '' }}">
            <input type="radio" id="field_{{ $fieldId }}_{{ $loop->index }}" wire:model="{{ $wirePrefix }}"
                value="{{ $optValue }}" class="custom-control-input @error($wirePrefix) is-invalid @enderror">
            <label class="custom-control-label {{ $compact ? 'small' : 'small' }}" style="{{ $compact ? 'font-size: 10px;' : '' }}" for="field_{{ $fieldId }}_{{ $loop->index }}">{{ $optLabel }}</label>
        </div>
    @endforeach
@elseif(($field['type'] ?? '') === 'select')
    @php
        $isMulti = in_array($fieldName, ['sampling_apparatus', 'method_of_sampling', 'reason_of_collection', 'transport_condition', 'sampling_source', 'sample_types_ww', 'sampling_technique', 'field_data_requirements'], true);
    @endphp
    @if($isMulti)
        <div class="row pt-1">
            @foreach(($field['options'] ?? []) as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? $opt['label'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <div class="col-6 mb-1">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="field_{{ $fieldId }}_{{ Str::slug($optValue) }}"
                            wire:model="{{ $wirePrefix }}.{{ $optValue }}" class="custom-control-input">
                        <label class="custom-control-label small font-weight-normal text-muted" for="field_{{ $fieldId }}_{{ Str::slug($optValue) }}">{{ $optLabel }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <select id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
            class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
            @if($compactStyle) style="{{ $compactStyle }}" @endif>
            <option value="">Select option</option>
            @foreach(($field['options'] ?? []) as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? $opt['label'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <option value="{{ $optValue }}">{{ $optLabel }}</option>
            @endforeach
        </select>
    @endif
@elseif(($field['type'] ?? '') === 'checkbox' && $fieldName === 'test_requirements' && $rowIndex !== null)
    @foreach(($field['options'] ?? []) as $opt)
        @php
            $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
            $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
        @endphp
        <div class="custom-control custom-radio {{ $compact ? 'mb-0' : '' }}">
            <input type="radio" id="field_{{ $fieldId }}_{{ $loop->index }}" wire:model="{{ $wirePrefix }}"
                value="{{ $optValue }}" class="custom-control-input @error($wirePrefix) is-invalid @enderror">
            <label class="custom-control-label {{ $compact ? 'small' : 'small' }}" style="{{ $compact ? 'font-size: 10px;' : '' }}" for="field_{{ $fieldId }}_{{ $loop->index }}">{{ $optLabel }}</label>
        </div>
    @endforeach
@elseif(($field['type'] ?? '') === 'checkbox')
    @if(!empty($field['options']) && is_array($field['options']))
        <div class="{{ $compact ? '' : 'row pt-1' }}">
            @foreach($field['options'] as $opt)
                @php
                    $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                    $optLabel = is_array($opt) ? ($opt['label'] ?? $optValue) : $opt;
                @endphp
                <div class="{{ $compact ? 'mb-1' : 'col-6 mb-1' }}">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="field_{{ $fieldId }}_{{ Str::slug($optValue) }}"
                            wire:model="{{ $wirePrefix }}.{{ $optValue }}" class="custom-control-input">
                        <label class="custom-control-label small font-weight-normal text-muted" style="{{ $compact ? 'font-size: 10px;' : '' }}" for="field_{{ $fieldId }}_{{ Str::slug($optValue) }}">{{ $optLabel }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="custom-control custom-checkbox pt-1">
            <input type="checkbox" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
                class="custom-control-input @error($wirePrefix) is-invalid @enderror">
            <label class="custom-control-label small" for="field_{{ $fieldId }}">{{ $field['label'] ?? $fieldName }}</label>
        </div>
    @endif
@elseif(($field['type'] ?? '') === 'date')
    <input type="date" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        @if($compact) style="font-size: 10px; height: auto; padding: 2px 4px;" @endif>
@elseif(($field['type'] ?? '') === 'datetime-local')
    <input type="datetime-local" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        @if($compactStyle) style="{{ $compactStyle }}" @endif>
@elseif(($field['type'] ?? '') === 'number')
    <input type="number" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        placeholder="{{ $compact ? '' : 'Enter ' . strtolower($field['label'] ?? $fieldName) }}"
        @if($compactStyle) style="{{ $compactStyle }}" @endif>
@else
    <input type="text" id="field_{{ $fieldId }}" wire:model="{{ $wirePrefix }}"
        class="{{ $controlClass }} @error($wirePrefix) is-invalid @enderror"
        placeholder="{{ $compact ? '' : 'Enter ' . strtolower($field['label'] ?? $fieldName) }}"
        @if($compactStyle) style="{{ $compactStyle }}" @endif
        @if($field['readonly'] ?? false) readonly @endif>
@endif

@error($wirePrefix)
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
@enderror
