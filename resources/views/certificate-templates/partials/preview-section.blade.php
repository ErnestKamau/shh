<div class="template-section" style="margin-bottom: 20px;">
  @if($section->title)
    <h3 style="margin-bottom: 10px; color: #333;">{{ $section->title }}</h3>
  @endif
  
  @if($section->description)
    <p style="margin-bottom: 15px; color: #666; font-style: italic;">{{ $section->description }}</p>
  @endif

  @if($section->elements->count() > 0)
    <div class="section-elements">
      @foreach($section->elements->sortBy('sort_order') as $element)
        <div class="template-element" style="margin-bottom: 10px;">
          @switch($element->element_type)
            @case('heading')
              @php
                $level = $element->properties['level'] ?? 'h2';
                $text = $element->properties['text'] ?? 'Heading Text';
                $alignment = $element->properties['alignment'] ?? 'left';
                
                // Replace placeholders with sample data
                foreach($sampleData as $key => $value) {
                  $text = str_replace(['{{'.$key.'}}', '{submission.'.$key.'}'], 
                    '<span class="field-placeholder sample-data">'.$value.'</span><span class="field-placeholder placeholder-data" style="display:none;">{{'.$key.'}}</span>', 
                    $text);
                }
              @endphp
              
              @if($level === 'h1')
                <h1 style="text-align: {{ $alignment }}; margin: 10px 0;">{!! $text !!}</h1>
              @elseif($level === 'h2')
                <h2 style="text-align: {{ $alignment }}; margin: 10px 0;">{!! $text !!}</h2>
              @elseif($level === 'h3')
                <h3 style="text-align: {{ $alignment }}; margin: 10px 0;">{!! $text !!}</h3>
              @elseif($level === 'h4')
                <h4 style="text-align: {{ $alignment }}; margin: 10px 0;">{!! $text !!}</h4>
              @elseif($level === 'h5')
                <h5 style="text-align: {{ $alignment }}; margin: 10px 0;">{!! $text !!}</h5>
              @else
                <h6 style="text-align: {{ $alignment }}; margin: 10px 0;">{!! $text !!}</h6>
              @endif
              @break

            @case('paragraph')
              @php
                $content = $element->content ?? '<p>Paragraph content</p>';
                
                // Replace placeholders with sample data
                foreach($sampleData as $key => $value) {
                  $content = str_replace(['{{'.$key.'}}', '{submission.'.$key.'}'], 
                    '<span class="field-placeholder sample-data">'.$value.'</span><span class="field-placeholder placeholder-data" style="display:none;">{{'.$key.'}}</span>', 
                    $content);
                }
              @endphp
              <div style="margin: 10px 0; line-height: 1.6;">{!! $content !!}</div>
              @break

            @case('text')
              @php
                $content = $element->content ?? 'Text content';
                
                // Replace placeholders with sample data
                foreach($sampleData as $key => $value) {
                  $content = str_replace(['{{'.$key.'}}', '{submission.'.$key.'}'], 
                    '<span class="field-placeholder sample-data">'.$value.'</span><span class="field-placeholder placeholder-data" style="display:none;">{{'.$key.'}}</span>', 
                    $content);
                }
              @endphp
              <div style="margin: 5px 0;">{!! $content !!}</div>
              @break

            @case('strong_text')
              @php
                $content = $element->content ?? 'Strong text content';
                
                // Replace placeholders with sample data
                foreach($sampleData as $key => $value) {
                  $content = str_replace(['{{'.$key.'}}', '{submission.'.$key.'}'], 
                    '<span class="field-placeholder sample-data">'.$value.'</span><span class="field-placeholder placeholder-data" style="display:none;">{{'.$key.'}}</span>', 
                    $content);
                }
              @endphp
              <div style="margin: 5px 0; font-weight: bold;">{!! $content !!}</div>
              @break

            @case('data_field')
              @php
                $fieldName = $element->properties['field_name'] ?? 'field_name';
                $displayLabel = $element->properties['display_label'] ?? '';
                $defaultValue = $element->properties['default_value'] ?? 'N/A';
                $sampleValue = $sampleData[$fieldName] ?? $defaultValue;
              @endphp
              <div style="margin: 5px 0;">
                @if($displayLabel)
                  <strong>{{ $displayLabel }}</strong> 
                @endif
                <span class="field-placeholder sample-data">{{ $sampleValue }}</span>
                <span class="field-placeholder placeholder-data" style="display:none;">{{{{ $fieldName }}}}</span>
              </div>
              @break

            @case('image')
              @php
                $imagePath = $element->properties['image_path'] ?? null;
                $altText = $element->properties['alt_text'] ?? 'Image';
                $width = $element->properties['width'] ?? '200px';
                $height = $element->properties['height'] ?? 'auto';
                $alignment = $element->properties['alignment'] ?? 'left';
              @endphp
              <div style="text-align: {{ $alignment }}; margin: 10px 0;">
                @if($imagePath && file_exists(public_path($imagePath)))
                  <img src="{{ asset($imagePath) }}" alt="{{ $altText }}" 
                       style="width: {{ $width }}; height: {{ $height }}; max-width: 100%;">
                @else
                  <div style="
                    width: {{ $width }}; 
                    height: 100px; 
                    background: #f8f9fa; 
                    border: 2px dashed #dee2e6; 
                    display: inline-flex; 
                    align-items: center; 
                    justify-content: center;
                    color: #6c757d;
                  ">
                    <i class="mdi mdi-image" style="font-size: 2rem;"></i>
                    <span style="margin-left: 10px;">{{ $altText }}</span>
                  </div>
                @endif
              </div>
              @break

            @case('table')
              @php
                $headers = $element->properties['headers'] ?? ['Column 1', 'Column 2', 'Column 3'];
                $showBorders = $element->properties['show_borders'] ?? true;
                $alternateRows = $element->properties['alternate_rows'] ?? true;
              @endphp
              <div style="margin: 15px 0;">
                <table style="
                  width: 100%; 
                  border-collapse: collapse;
                  {{ $showBorders ? 'border: 1px solid #dee2e6;' : '' }}
                ">
                  <thead>
                    <tr style="background: #f8f9fa;">
                      @foreach($headers as $header)
                        <th style="
                          padding: 8px 12px; 
                          text-align: left; 
                          font-weight: bold;
                          {{ $showBorders ? 'border: 1px solid #dee2e6;' : '' }}
                        ">{{ $header }}</th>
                      @endforeach
                    </tr>
                  </thead>
                  <tbody>
                    @for($i = 0; $i < 3; $i++)
                      <tr style="{{ $alternateRows && $i % 2 === 1 ? 'background: #f8f9fa;' : '' }}">
                        @foreach($headers as $index => $header)
                          <td style="
                            padding: 8px 12px;
                            {{ $showBorders ? 'border: 1px solid #dee2e6;' : '' }}
                          ">
                            @if($index === 0)
                              Sample Row {{ $i + 1 }}
                            @else
                              Data {{ $i + 1 }}.{{ $index }}
                            @endif
                          </td>
                        @endforeach
                      </tr>
                    @endfor
                  </tbody>
                </table>
              </div>
              @break

            @case('spacer')
              @php
                $height = $element->properties['height'] ?? '20px';
              @endphp
              <div style="height: {{ $height }};"></div>
              @break

            @default
              <div style="
                padding: 10px; 
                background: #f8f9fa; 
                border: 1px dashed #dee2e6; 
                color: #6c757d;
                margin: 5px 0;
              ">
                <i class="mdi mdi-help-circle"></i> 
                Unknown element type: {{ $element->element_type }}
              </div>
          @endswitch
        </div>
      @endforeach
    </div>
  @endif

  @if($section->children->count() > 0)
    <div class="subsections" style="margin-left: 20px; margin-top: 15px;">
      @foreach($section->children->sortBy('sort_order') as $childSection)
        @include('certificate-templates.partials.preview-section', ['section' => $childSection, 'sampleData' => $sampleData])
      @endforeach
    </div>
  @endif
</div>