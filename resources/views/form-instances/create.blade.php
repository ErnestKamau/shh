@extends('layouts.lab.layout.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <h4 class="mb-0">
            <i class="mdi mdi-form-select"></i> {{ $submissionForm->name }}
          </h4>
          @if($submissionForm->description)
            <p class="text-muted mb-0 mt-2">{{ $submissionForm->description }}</p>
          @endif
        </div>
        
        <div class="card-body">
          @if($submissionForm->sections->count() > 0)
            <form method="POST" action="{{ route('forms.submit', $submissionForm->slug) }}" enctype="multipart/form-data">
              @csrf
              
              @foreach($submissionForm->sections as $section)
                <div class="section mb-5">
                  <h5 class="text-primary border-bottom pb-2 mb-4">
                    <i class="mdi mdi-folder"></i> {{ $section->name }}
                  </h5>
                  
                  @if($section->description)
                    <p class="text-muted mb-4">{{ $section->description }}</p>
                  @endif
                  
                  @if($section->elementHolders->count() > 0)
                    @foreach($section->elementHolders as $holder)
                      <div class="element-holder mb-4">
                        @if($holder->elements->count() > 0)
                          <div class="row">
                            @foreach($holder->elements as $element)
                              @php
                                $fieldName = "field_{$element->id}";
                                $isRequired = $element->is_required;
                                $hasError = $errors->has($fieldName);
                                $oldValue = old($fieldName);
                              @endphp
                              
                              <div class="col-md-{{ $holder->width === 'full' ? '12' : ($holder->width === 'half' ? '6' : '4') }} mb-3">
                                <div class="form-group">
                                  <label for="{{ $fieldName }}" class="form-label">
                                    {{ $element->label }}
                                    @if($isRequired)
                                      <span class="text-danger">*</span>
                                    @endif
                                  </label>
                                  
                                  @if($element->description)
                                    <small class="form-text text-muted d-block mb-2">{{ $element->description }}</small>
                                  @endif
                                  
                                  @switch($element->element_type)
                                    @case('text')
                                    @case('email')
                                    @case('number')
                                    @case('date')
                                    @case('time')
                                    @case('url')
                                      <input type="{{ $element->element_type }}" 
                                             name="{{ $fieldName }}" 
                                             id="{{ $fieldName }}" 
                                             class="form-control {{ $hasError ? 'is-invalid' : '' }}" 
                                             value="{{ $oldValue }}"
                                             @if($element->placeholder) placeholder="{{ $element->placeholder }}" @endif
                                             @if($isRequired) required @endif
                                             @if($element->validation_rules && isset($element->validation_rules['min_length'])) minlength="{{ $element->validation_rules['min_length'] }}" @endif
                                             @if($element->validation_rules && isset($element->validation_rules['max_length'])) maxlength="{{ $element->validation_rules['max_length'] }}" @endif>
                                      @break
                                      
                                    @case('textarea')
                                      <textarea name="{{ $fieldName }}" 
                                                id="{{ $fieldName }}" 
                                                class="form-control {{ $hasError ? 'is-invalid' : '' }}" 
                                                rows="{{ $element->settings['rows'] ?? 3 }}"
                                                @if($element->placeholder) placeholder="{{ $element->placeholder }}" @endif
                                                @if($isRequired) required @endif
                                                @if($element->validation_rules && isset($element->validation_rules['min_length'])) minlength="{{ $element->validation_rules['min_length'] }}" @endif
                                                @if($element->validation_rules && isset($element->validation_rules['max_length'])) maxlength="{{ $element->validation_rules['max_length'] }}" @endif>{{ $oldValue }}</textarea>
                                      @break
                                      
                                    @case('select')
                                      <select name="{{ $fieldName }}" 
                                              id="{{ $fieldName }}" 
                                              class="form-select {{ $hasError ? 'is-invalid' : '' }}"
                                              @if($isRequired) required @endif>
                                        <option value="">Choose an option...</option>
                                        @if($element->options)
                                          @foreach($element->options as $option)
                                            <option value="{{ $option['value'] }}" {{ $oldValue === $option['value'] ? 'selected' : '' }}>
                                              {{ $option['label'] }}
                                            </option>
                                          @endforeach
                                        @endif
                                      </select>
                                      @break
                                      
                                    @case('radio')
                                      @if($element->options)
                                        @foreach($element->options as $index => $option)
                                          <div class="form-check">
                                            <input type="radio" 
                                                   name="{{ $fieldName }}" 
                                                   id="{{ $fieldName }}_{{ $index }}" 
                                                   class="form-check-input {{ $hasError ? 'is-invalid' : '' }}" 
                                                   value="{{ $option['value'] }}"
                                                   {{ $oldValue === $option['value'] ? 'checked' : '' }}
                                                   @if($isRequired) required @endif>
                                            <label class="form-check-label" for="{{ $fieldName }}_{{ $index }}">
                                              {{ $option['label'] }}
                                            </label>
                                          </div>
                                        @endforeach
                                      @endif
                                      @break
                                      
                                    @case('checkbox')
                                      @if($element->options)
                                        @foreach($element->options as $index => $option)
                                          <div class="form-check">
                                            <input type="checkbox" 
                                                   name="{{ $fieldName }}[]" 
                                                   id="{{ $fieldName }}_{{ $index }}" 
                                                   class="form-check-input {{ $hasError ? 'is-invalid' : '' }}" 
                                                   value="{{ $option['value'] }}"
                                                   {{ is_array($oldValue) && in_array($option['value'], $oldValue) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $fieldName }}_{{ $index }}">
                                              {{ $option['label'] }}
                                            </label>
                                          </div>
                                        @endforeach
                                      @endif
                                      @break
                                      
                                    @case('file')
                                      <input type="file" 
                                             name="{{ $fieldName }}" 
                                             id="{{ $fieldName }}" 
                                             class="form-control {{ $hasError ? 'is-invalid' : '' }}"
                                             @if($isRequired) required @endif
                                             @if($element->validation_rules && isset($element->validation_rules['allowed_types'])) accept=".{{ implode(',.', $element->validation_rules['allowed_types']) }}" @endif>
                                      @if($element->validation_rules)
                                        <small class="form-text text-muted">
                                          @if(isset($element->validation_rules['max_size']))
                                            Max size: {{ $element->validation_rules['max_size'] }}KB.
                                          @endif
                                          @if(isset($element->validation_rules['allowed_types']))
                                            Allowed types: {{ implode(', ', $element->validation_rules['allowed_types']) }}.
                                          @endif
                                        </small>
                                      @endif
                                      @break
                                      
                                    @default
                                      <input type="text" 
                                             name="{{ $fieldName }}" 
                                             id="{{ $fieldName }}" 
                                             class="form-control {{ $hasError ? 'is-invalid' : '' }}" 
                                             value="{{ $oldValue }}"
                                             @if($isRequired) required @endif>
                                  @endswitch
                                  
                                  @if($hasError)
                                    <div class="invalid-feedback">
                                      {{ $errors->first($fieldName) }}
                                    </div>
                                  @endif
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </div>
                    @endforeach
                  @else
                    <div class="alert alert-info">
                      <i class="mdi mdi-information"></i> This section has no form elements yet.
                    </div>
                  @endif
                </div>
              @endforeach
              
              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="mdi mdi-send"></i> Submit Form
                </button>
              </div>
            </form>
          @else
            <div class="text-center py-5">
              <i class="mdi mdi-form-select display-1 text-muted"></i>
              <h5 class="text-muted mt-3">Form Not Ready</h5>
              <p class="text-muted">This form doesn't have any sections or content yet.</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection