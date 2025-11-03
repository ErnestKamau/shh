@switch($element->element_type)
    @case('heading')
        @php
            $level = $element->properties['level'] ?? 'h2';
            $alignment = $element->properties['alignment'] ?? 'left';
        @endphp
        <{{ $level }} style="text-align: {{ $alignment }};">{{ $element->content }}</{{ $level }}>
        @break
        
    @case('paragraph')
        @php
            $alignment = $element->properties['alignment'] ?? 'left';
        @endphp
        <p style="text-align: {{ $alignment }};">{!! nl2br(e($element->content)) !!}</p>
        @break
        
    @case('image')
        @if($element->content)
            @php
                $alignment = $element->properties['alignment'] ?? 'left';
            @endphp
            <div style="text-align: {{ $alignment }};">
                <img src="{{ $element->content }}" 
                     alt="{{ $element->properties['alt_text'] ?? '' }}" 
                     class="image-preview"
                     style="max-width: 100%; height: auto;">
            </div>
        @endif
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
            $dateStr = match($element->content) {
                'current_date' => date($format),
                'analysis_date' => date($format),
                'report_date' => date($format),
                default => date($format)
            };
        @endphp
        <div class="data-field">[Date: {{ $dateStr }}]</div>
        @break
        
    @case('page_break')
        <div class="page-break" style="page-break-before: always; border-top: 2px dashed #ccc; margin: 20px 0; padding-top: 20px;">
            <small class="text-muted">--- Page Break ---</small>
        </div>
        @break
        
    @default
        <div class="data-field">[{{ ucfirst(str_replace('_', ' ', $element->element_type)) }}: {{ $element->content }}]</div>
@endswitch

