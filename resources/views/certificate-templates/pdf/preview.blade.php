<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template->name }}</title>
    <style>
        @page {
            size: {{ $template->page_settings['size'] ?? 'A4' }} {{ $template->page_settings['orientation'] ?? 'portrait' }};
            margin: {{ $template->page_settings['margins']['top'] ?? 20 }}mm 
                    {{ $template->page_settings['margins']['right'] ?? 20 }}mm 
                    {{ $template->page_settings['margins']['bottom'] ?? 20 }}mm 
                    {{ $template->page_settings['margins']['left'] ?? 20 }}mm;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }
        
        .page-content {
            position: relative;
            @if(isset($template->page_settings['size']))
                @php
                    $dimensions = [
                        'A4' => ['width' => '210mm', 'height' => '297mm'],
                        'A3' => ['width' => '297mm', 'height' => '420mm'],
                        'Letter' => ['width' => '8.5in', 'height' => '11in'],
                        'Legal' => ['width' => '8.5in', 'height' => '14in'],
                        'A5' => ['width' => '148mm', 'height' => '210mm'],
                    ];
                    $size = $template->page_settings['size'] ?? 'A4';
                    $orientation = $template->page_settings['orientation'] ?? 'portrait';
                @endphp
                @if($orientation === 'landscape')
                    width: {{ $dimensions[$size]['height'] }};
                    height: {{ $dimensions[$size]['width'] }};
                @else
                    width: {{ $dimensions[$size]['width'] }};
                    height: {{ $dimensions[$size]['height'] }};
                @endif
            @endif
        }
        
        /* Element holders as absolute positioned containers */
        .element-holder {
            position: absolute;
        }
        
        /* Canvas elements with absolute positioning */
        .canvas-element {
            position: absolute;
            overflow: hidden;
        }
        
        /* Element type specific styles */
        .element-heading h1,
        .element-heading h2,
        .element-heading h3,
        .element-heading h4 {
            margin: 0;
            padding: 0;
        }
        
        .element-paragraph {
            margin: 0;
        }
        
        .element-image img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .element-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .element-table table th,
        .element-table table td {
            border: 1px solid #000;
            padding: 5px;
        }
        
        .element-data-field {
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        .element-signature {
            border-bottom: 1px solid #000;
            min-height: 30px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        /* Header */
        @if(isset($template->header_settings['enabled']) && $template->header_settings['enabled'])
        .template-header {
            text-align: {{ $template->header_settings['alignment'] ?? 'center' }};
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        @endif
        
        /* Footer */
        @if(isset($template->footer_settings['enabled']) && $template->footer_settings['enabled'])
        .template-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: {{ $template->footer_settings['alignment'] ?? 'center' }};
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 10pt;
        }
        @endif
    </style>
</head>
<body>
    @if(isset($template->header_settings['enabled']) && $template->header_settings['enabled'])
    <div class="template-header">
        {!! nl2br(e($template->header_settings['content'] ?? '')) !!}
    </div>
    @endif
    
    <div class="page-content">
        @foreach($template->sections as $section)
            @foreach($section->elementHolders as $holder)
                <div class="element-holder" 
                     style="left: {{ $holder->position_x ?? 0 }}px; 
                            top: {{ $holder->position_y ?? 0 }}px;
                            width: {{ $holder->width ?? 300 }}px;
                            height: {{ $holder->height ?? 200 }}px;">
                    
                    @foreach($holder->elements as $element)
                        <div class="canvas-element element-{{ $element->element_type }}" 
                             style="left: {{ $element->position_x ?? 0 }}px; 
                                    top: {{ $element->position_y ?? 0 }}px;
                                    width: {{ $element->width ?? 200 }}px;
                                    height: {{ $element->height ?? 100 }}px;
                                    z-index: {{ $element->z_index ?? 1 }};">
                            
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
                                        <img src="{{ $element->content }}" alt="{{ $element->properties['alt_text'] ?? '' }}">
                                    @endif
                                    @break
                                    
                                @case('table')
                                    @php
                                        $tableData = $element->properties['data'] ?? [];
                                        $headers = $element->properties['headers'] ?? [];
                                    @endphp
                                    @if(!empty($tableData))
                                        <table>
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
                                        <div class="element-data-field">[Table: {{ $element->content }}]</div>
                                    @endif
                                    @break
                                    
                                @case('data_field')
                                    <div class="element-data-field">[{{ $element->content }}]</div>
                                    @break
                                    
                                @case('signature')
                                    <div class="element-signature">
                                        <small>{{ $element->content ?: 'Signature' }}</small>
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
                                    <div>{{ $dateStr }}</div>
                                    @break
                                    
                                @case('page_break')
                                    <div class="page-break"></div>
                                    @break
                            @endswitch
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endforeach
    </div>
    
    @if(isset($template->footer_settings['enabled']) && $template->footer_settings['enabled'])
    <div class="template-footer">
        {!! nl2br(e($template->footer_settings['content'] ?? '')) !!}
        @if(isset($template->footer_settings['page_numbers']) && $template->footer_settings['page_numbers'])
            <div class="page-number">Page <span class="pageNum"></span></div>
        @endif
    </div>
    @endif
</body>
</html>

