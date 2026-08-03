<section class="form-section card border-0 bg-light mb-4 {{ $section->getAlignmentClass() }}"
         data-section-id="{{ $section->id }}">
  <div class="card-body p-3 p-md-4">
    <header class="section-header mb-4 {{ $section->getAlignmentClass() }}">
      <h5 class="text-primary border-bottom pb-2 mb-2">
        <i class="mdi mdi-folder-outline"></i> {{ $section->title }}
      </h5>
      @include('submission-forms.partials.section-logos', ['section' => $section])
      @if($section->description)
        <p class="text-muted small mb-0">{!! nl2br(e($section->description)) !!}</p>
      @endif
    </header>

    @foreach($section->elementHolders as $holder)
      <div class="element-holder mb-3"
           data-holder-id="{{ $holder->id }}"
           data-section-id="{{ $section->id }}">
        @if($holder->holder_type === 'field')
          @php
            $visibleHolderElements = $holder->elements
              ->reject(fn ($element) => (bool) ($element->is_hidden ?? false))
              ->values();
          @endphp
          <div class="row">
            @foreach($visibleHolderElements as $element)
              <div class="col-md-{{ getColumnWidth($visibleHolderElements->count()) }} mb-3"
                   data-element-name="{{ $element->name }}"
                   data-submission-form-element-id="{{ $element->id }}"
                   data-submission-form-element-name="{{ $element->name }}"
                   @if(! empty($element->conditional_logic))
                     data-submission-form-conditional="{{ base64_encode(json_encode($element->conditional_logic)) }}"
                   @endif>
                @include('submission-forms.partials.form-element', [
                  'element' => $element,
                  'existingValues' => $existingValues,
                ])
              </div>
            @endforeach
          </div>
        @else
          @foreach($holder->elements as $element)
            @continue($element->is_hidden ?? false)
            <div class="text-element mb-3"
                 data-submission-form-element-id="{{ $element->id }}"
                 data-submission-form-element-name="{{ $element->name }}"
                 @if(! empty($element->conditional_logic))
                   data-submission-form-conditional="{{ base64_encode(json_encode($element->conditional_logic)) }}"
                 @endif>
              <div class="alert alert-light border mb-0">
                <strong>{{ $element->label }}</strong>
                @if($element->help_text)
                  <p class="mb-0 mt-2">{{ $element->help_text }}</p>
                @endif
              </div>
            </div>
          @endforeach
        @endif
      </div>
    @endforeach
  </div>
</section>
