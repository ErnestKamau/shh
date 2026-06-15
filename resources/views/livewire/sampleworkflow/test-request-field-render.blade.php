<label for="field_{{ $field['name'] }}" class="font-weight-bold text-secondary small">
    {{ $field['label'] ?? $field['name'] }}
    @if($field['required'] ?? false)
        <span class="text-danger">*</span>
    @endif
</label>

@if(in_array($field['name'], ['customer_name', 'client_name', 'customer', 'client'], true))
    <select id="field_{{ $field['name'] }}" wire:model.live="formData.{{ $field['name'] }}"
        class="form-control form-control-sm @error('formData.' . $field['name']) is-invalid @enderror">
        <option value="">-- Select Customer --</option>
        @foreach($this->customers as $cust)
            <option value="{{ $cust->name }}">{{ $cust->name }}</option>
        @endforeach
    </select>
@elseif(in_array($field['name'], ['analysis_type', 'analysis_types'], true))
    <select id="field_{{ $field['name'] }}" wire:model.live="formData.{{ $field['name'] }}"
        class="form-control form-control-sm @error('formData.' . $field['name']) is-invalid @enderror">
        <option value="">-- Select Analysis Type --</option>
        @foreach($this->analysisTypes as $at)
            <option value="{{ $at->name }}">{{ $at->name }}</option>
        @endforeach
    </select>
@elseif(in_array($field['name'], ['parameter', 'parameters'], true))
    <select id="field_{{ $field['name'] }}" wire:model="formData.{{ $field['name'] }}"
        class="form-control form-control-sm @error('formData.' . $field['name']) is-invalid @enderror">
        <option value="">-- Select Parameter --</option>
        @foreach($this->parameters as $param)
            <option value="{{ $param->name }}">{{ $param->name }}</option>
        @endforeach
    </select>
@elseif(($field['type'] ?? '') === 'textarea')
    <textarea id="field_{{ $field['name'] }}" wire:model="formData.{{ $field['name'] }}"
        class="form-control form-control-sm @error('formData.' . $field['name']) is-invalid @enderror"
        placeholder="Enter {{ strtolower($field['label'] ?? $field['name']) }}" rows="2"></textarea>
@elseif(($field['type'] ?? '') === 'select')
    @php
        $isMulti = in_array($field['name'], ['sampling_apparatus', 'method_of_sampling', 'reason_of_collection', 'transport_condition', 'sampling_source', 'sample_types_ww', 'sampling_technique', 'field_data_requirements'], true);
    @endphp
    @if($isMulti)
        <div class="row pt-1">
            @foreach(($field['options'] ?? []) as $opt)
                <div class="col-6 mb-1">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="field_{{ $field['name'] }}_{{ Str::slug($opt) }}" 
                            wire:model="formData.{{ $field['name'] }}.{{ $opt }}" class="custom-control-input">
                        <label class="custom-control-label small font-weight-normal text-muted" for="field_{{ $field['name'] }}_{{ Str::slug($opt) }}">{{ $opt }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <select id="field_{{ $field['name'] }}" wire:model="formData.{{ $field['name'] }}"
            class="form-control form-control-sm @error('formData.' . $field['name']) is-invalid @enderror">
            <option value="">Select option</option>
            @foreach(($field['options'] ?? []) as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>
    @endif
@elseif(($field['type'] ?? '') === 'checkbox')
    <div class="custom-control custom-checkbox pt-1">
        <input type="checkbox" id="field_{{ $field['name'] }}" wire:model="formData.{{ $field['name'] }}"
            class="custom-control-input @error('formData.' . $field['name']) is-invalid @enderror">
        <label class="custom-control-label small" for="field_{{ $field['name'] }}">{{ $field['label'] ?? $field['name'] }}</label>
    </div>
@elseif(($field['type'] ?? '') === 'date')
    <input type="date" id="field_{{ $field['name'] }}" wire:model="formData.{{ $field['name'] }}"
        class="form-control form-control-sm @error('formData.' . $field['name']) is-invalid @enderror">
@elseif(($field['type'] ?? '') === 'datetime-local')
    <input type="datetime-local" id="field_{{ $field['name'] }}" wire:model="formData.{{ $field['name'] }}"
        class="form-control form-control-sm @error('formData.' . $field['name']) is-invalid @enderror">
@else
    <input type="text" id="field_{{ $field['name'] }}" wire:model="formData.{{ $field['name'] }}"
        class="form-control form-control-sm @error('formData.' . $field['name']) is-invalid @enderror"
        placeholder="Enter {{ strtolower($field['label'] ?? $field['name']) }}">
@endif

@error('formData.' . $field['name'])
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
@enderror
