<div class="preview-section">
    @if($section->title)
        <h3>{{ $section->title }}</h3>
    @endif
    
    @if($section->description)
        <p class="text-muted">{{ $section->description }}</p>
    @endif
    
    @if($section->elements->count() > 0)
        <div class="preview-elements">
            @foreach($section->elements as $element)
                <div class="preview-element">
                    @switch($element->element_type)
                        @case('heading')
                            @php
                                $level = $element->properties['level'] ?? 'h2';
                            @endphp
                            <{{ $level }}>{{ $element->content }}</{{ $level }}>
                            @break
                            
                        @case('paragraph')
                            <p>{!! nl2br(e($element->content)) !!}</p>
                            @break
                            
                        @case('image')
                            <img src="{{ $element->content }}" 
                                 alt="{{ $element->properties['alt'] ?? '' }}" 
                                 class="image-preview">
                            @break
                            
                        @case('table')
                            @php
                                $tableData = $element->properties['data'] ?? [];
                                $headers = $element->properties['headers'] ?? [];
                            @endphp
                            @if(!empty($tableData))
                                <table class="table-preview">
                                    @if(!empty($headers))
                                        <thead>
                                            <tr>
                                                @foreach($headers as $header)
                                                    <th>{{ $header }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                    @endif
                                    <tbody>
                                        @foreach($tableData as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td>{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="data-field">[Table: {{ $element->content }}]</div>
                            @endif
                            @break
                            
                        @case('data_field')
                            <div class="data-field">[{{ $element->content }}]</div>
                            @break
                            
                        @case('signature')
                            <div class="signature-field">
                                <small class="text-muted">{{ $element->content ?: 'Signature' }}</small>
                            </div>
                            @break
                            
                        @case('date')
                            @php
                                $format = $element->properties['format'] ?? 'Y-m-d';
                            @endphp
                            <div class="data-field">[Date: {{ $format }}]</div>
                            @break
                            
                        @case('page_break')
                            <div class="page-break" style="page-break-before: always; border-top: 2px dashed #ccc; margin: 20px 0; padding-top: 20px;">
                                <small class="text-muted">--- Page Break ---</small>
                            </div>
                            @break
                            
                        @default
                            <div class="data-field">[{{ ucfirst(str_replace('_', ' ', $element->element_type)) }}: {{ $element->content }}]</div>
                    @endswitch
                </div>
            @endforeach
        </div>
    @endif
    
    @if($section->childSections->count() > 0)
        <div class="child-sections mt-4">
            @foreach($section->childSections as $childSection)
                @include('certificate-templates.partials.preview-section', ['section' => $childSection])
            @endforeach
        </div>
    @endif
</div>
