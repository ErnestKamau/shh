<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $submissionForm->name }} - {{ $instance->form_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
            line-height: 1.6;
        }
        
        .print-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .form-number {
            font-size: 16px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .form-description {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }
        
        .instance-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 120px;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            background: #e9ecef;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
        }
        
        .element-group {
            margin-bottom: 20px;
        }
        
        .element-label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .element-value {
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            min-height: 20px;
        }
        
        .element-value.empty {
            color: #6c757d;
            font-style: italic;
        }
        
        .file-value {
            color: #007bff;
            text-decoration: underline;
        }
        
        .print-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .print-header {
                page-break-after: avoid;
            }
            
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <div class="form-title">{{ $submissionForm->name }}</div>
        <div class="form-number">Form Number: {{ $instance->form_number }}</div>
        @if($submissionForm->description)
            <div class="form-description">{{ $submissionForm->description }}</div>
        @endif
    </div>

    <div class="instance-info">
        <div class="info-row">
            <span class="info-label">Submitted By:</span>
            <span>{{ $instance->submittedBy->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="badge badge-{{ $instance->status === 'approved' ? 'success' : ($instance->status === 'rejected' ? 'danger' : 'warning') }}">
                {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Priority:</span>
            <span>{{ ucfirst($instance->priority) }}</span>
        </div>
        @if($instance->title)
            <div class="info-row">
                <span class="info-label">Title:</span>
                <span>{{ $instance->title }}</span>
            </div>
        @endif
        @if($instance->due_date)
            <div class="info-row">
                <span class="info-label">Due Date:</span>
                <span>{{ $instance->due_date->format('M d, Y') }}</span>
            </div>
        @endif
        <div class="info-row">
            <span class="info-label">Submitted At:</span>
            <span>{{ $instance->submitted_at ? $instance->submitted_at->format('M d, Y H:i') : 'Not submitted' }}</span>
        </div>
        @if($instance->reviewed_at)
            <div class="info-row">
                <span class="info-label">Reviewed At:</span>
                <span>{{ $instance->reviewed_at->format('M d, Y H:i') }}</span>
            </div>
        @endif
        @if($instance->reviewedBy)
            <div class="info-row">
                <span class="info-label">Reviewed By:</span>
                <span>{{ $instance->reviewedBy->name }}</span>
            </div>
        @endif
    </div>

    @foreach($submissionForm->sections as $section)
        <div class="section">
            <div class="section-title">{{ $section->title }}</div>
            
            @if($section->description)
                <p style="margin-bottom: 20px; color: #6c757d; font-style: italic;">{{ $section->description }}</p>
            @endif

            @foreach($section->elementHolders as $holder)
                @if($holder->elements->count() > 0)
                    <div class="element-group">
                        @if($holder->elements->count() > 1)
                            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                                @foreach($holder->elements as $element)
                                    <div style="flex: 1; min-width: 200px;">
                                        <div class="element-label">{{ $element->label }}</div>
                                        <div class="element-value {{ \App\Helpers\PrintHelper::getElementValue($element, $existingValues) ? '' : 'empty' }}">
                                            {!! \App\Helpers\PrintHelper::formatElementValue($element, $existingValues) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @foreach($holder->elements as $element)
                                <div class="element-label">{{ $element->label }}</div>
                                <div class="element-value {{ \App\Helpers\PrintHelper::getElementValue($element, $existingValues) ? '' : 'empty' }}">
                                    {!! \App\Helpers\PrintHelper::formatElementValue($element, $existingValues) !!}
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach

    @if($instance->review_notes)
        <div class="section">
            <div class="section-title">Review Notes</div>
            <div class="element-value">{{ $instance->review_notes }}</div>
        </div>
    @endif

    <div class="print-footer">
        <p>Printed on {{ now()->format('M d, Y H:i') }}</p>
        <p>This is a system-generated document from {{ config('app.name') }}</p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
