<div class="section-item" style="margin-left: {{ $level * 20 }}px;">
  <div class="d-flex align-items-center py-2">
    <div class="flex-grow-1">
      <div class="d-flex align-items-center">
        @if($level == 0)
          <i class="mdi mdi-folder text-primary mr-2"></i>
        @elseif($level == 1)
          <i class="mdi mdi-folder-outline text-info mr-2"></i>
        @else
          <i class="mdi mdi-file-outline text-muted mr-2"></i>
        @endif
        
        <strong>{{ $section->title }}</strong>
        
        @if($section->description)
          <small class="text-muted ml-2">- {{ Str::limit($section->description, 50) }}</small>
        @endif
      </div>
      
      @if($section->elements->count() > 0)
        <div class="mt-1" style="margin-left: 20px;">
          @foreach($section->elements->sortBy('sort_order') as $element)
            <div class="element-item py-1">
              <small class="text-muted">
                @switch($element->element_type)
                  @case('heading')
                    <i class="mdi mdi-format-header-1"></i> Heading
                    @break
                  @case('paragraph')
                    <i class="mdi mdi-format-paragraph"></i> Paragraph
                    @break
                  @case('text')
                    <i class="mdi mdi-format-text"></i> Text
                    @break
                  @case('strong_text')
                    <i class="mdi mdi-format-bold"></i> Strong Text
                    @break
                  @case('image')
                    <i class="mdi mdi-image"></i> Image
                    @break
                  @case('data_field')
                    <i class="mdi mdi-database"></i> Data Field
                    @break
                  @case('table')
                    <i class="mdi mdi-table"></i> Table
                    @break
                  @case('spacer')
                    <i class="mdi mdi-minus"></i> Spacer
                    @break
                  @default
                    <i class="mdi mdi-help-circle"></i> {{ ucfirst($element->element_type) }}
                @endswitch
                
                @if($element->element_type == 'heading' && isset($element->properties['text']))
                  - {{ Str::limit($element->properties['text'], 30) }}
                @elseif($element->element_type == 'text' && $element->content)
                  - {{ Str::limit(strip_tags($element->content), 30) }}
                @elseif($element->element_type == 'data_field' && isset($element->properties['field_name']))
                  - {{ $element->properties['field_name'] }}
                @endif
                
                @if($element->is_conditional)
                  <span class="badge badge-warning badge-sm ml-1">Conditional</span>
                @endif
              </small>
            </div>
          @endforeach
        </div>
      @endif
    </div>
    
    <div class="text-right">
      <small class="text-muted">
        {{ $section->elements->count() }} element(s)
        @if($section->children->count() > 0)
          | {{ $section->children->count() }} subsection(s)
        @endif
      </small>
    </div>
  </div>
  
  @if($section->children->count() > 0 && $level < 2)
    @foreach($section->children->sortBy('sort_order') as $childSection)
      @include('certificate-templates.partials.section-tree', ['section' => $childSection, 'level' => $level + 1])
    @endforeach
  @endif
</div>