{{-- Simple Form Display - Shows only labels and saved values --}}
<div class="simple-form-display" data-form-instance-id="{{ $instance->id }}">
    <div class="form-header mb-4">
        <h4 class="text-primary">
            <i class="mdi mdi-file-document"></i> {{ $instance->submissionForm->name }}
        </h4>
        <p class="text-muted">{{ $instance->submissionForm->description }}</p>
        
        <div class="form-meta">
            <span class="badge badge-secondary">{{ ucfirst($instance->status) }}</span>
            @if($instance->priority)
                <span class="badge badge-info ml-2">{{ ucfirst($instance->priority) }}</span>
            @endif
            <small class="text-muted ml-3">
                Submitted by {{ $instance->submittedBy->name ?? 'Unknown' }} on {{ $instance->created_at->format('M d, Y H:i') }}
            </small>
        </div>
    </div>

    {{-- Process each section --}}
    @foreach($formData['sections'] as $section)
        <div class="section-container mb-4" data-section-id="{{ $section['id'] }}">
            <div class="section-header">
                <h5 class="text-secondary border-bottom pb-2">
                    <i class="mdi mdi-form-select"></i> 
                    {{ $section['title'] }}
                </h5>
                @if($section['description'])
                    <p class="text-muted small mb-0">{{ $section['description'] }}</p>
                @endif
            </div>

            {{-- Process element holders --}}
            @foreach($section['element_holders'] as $holder)
                @if($holder['holder_type'] === 'rows')
                    {{-- Rows Section --}}
                    <div class="rows-section-display">
                        <h6 class="mb-3">Form Data</h6>
                        
                        @if(!empty($holder['rows_data']))
                            {{-- Display as responsive table --}}
                            <div class="table-responsive-vertical">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="row-number">#</th>
                                            @foreach($holder['elements'] as $element)
                                                <th class="element-header">
                                                    {{ $element['label'] }}
                                                    @if($element['is_required'])
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($holder['rows_data'] as $arrayIndex => $rowData)
                                            <tr class="data-row" data-row-index="{{ $arrayIndex }}">
                                                <td class="row-number-cell">
                                                    <span class="badge badge-primary">{{ $arrayIndex + 1 }}</span>
                                                </td>
                                                @foreach($holder['elements'] as $element)
                                                    <td class="element-cell">
                                                        @php
                                                            $elementId = $element['id'];
                                                            $savedValue = $rowData[$elementId] ?? null;
                                                            $displayValue = $savedValue['display_value'] ?? $savedValue['value'] ?? 'N/A';
                                                            $isSignature = $element['element_type'] === 'signature' || str_contains((string)$displayValue, '/storage/personnel-signature/');
                                                        @endphp
                                                        @if($isSignature && $displayValue && $displayValue !== 'N/A')
                                                            @if(str_starts_with($displayValue, 'data:image') || $displayValue)
                                                                <img src="{{ $displayValue }}" alt="Signature" class="signature-image" style="max-width: 150px; max-height: 75px; border: 1px solid #dee2e6; border-radius: 4px;">
                                                            @else
                                                                <div class="signature-placeholder" style="text-align: center; color: #6c757d; font-size: 0.8rem;">
                                                                    <i class="mdi mdi-pen"></i>
                                                                    <span>No signature</span>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <span class="field-value">{{ $displayValue }}</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i> No data available for this section.
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Regular Section --}}
                    <div class="regular-section-display">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    @foreach($holder['elements'] as $element)
                                        <th class="form-label-cell" style="width: {{ 100 / count($holder['elements']) }}%">
                                            <label class="form-label">
                                                {{ $element['label'] }}
                                                @if($element['is_required'])
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($holder['elements'] as $element)
                                        <td class="form-value-cell" style="vertical-align: top; width: {{ 100 / count($holder['elements']) }}%">
                                            @php
                                                $savedValue = $element['saved_values'][0] ?? null;
                                                $displayValue = $savedValue['display_value'] ?? $savedValue['value'] ?? 'N/A';
                                                $isSignature = $element['element_type'] === 'signature' || str_contains((string)$displayValue, '/storage/personnel-signature/');
                                            @endphp
                                            
                                            <div class="field-value-display">
                                                @if($isSignature && $displayValue && $displayValue !== 'N/A')
                                                    @if(str_starts_with($displayValue, 'data:image') || $displayValue)
                                                        <img src="{{ $displayValue }}" alt="Signature" class="signature-image" style="max-width: 200px; max-height: 100px; border: 1px solid #dee2e6; border-radius: 4px;">
                                                    @else
                                                        <div class="signature-placeholder">
                                                            <i class="mdi mdi-pen"></i>
                                                            <span>No signature available</span>
                                                        </div>
                                                    @endif
                                                @else
                                                    {{ $displayValue }}
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach
</div>

<style>
.simple-form-display {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 20px;
    margin-bottom: 30px;
}

.form-meta {
    margin-top: 10px;
}

.section-container {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 20px;
}

.section-header h5 {
    color: #495057;
    font-weight: 600;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    display: block;
}

.field-value-display {
    padding: 10px 15px;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-weight: 500;
    color: #495057;
    min-height: 40px;
    display: flex;
    align-items: center;
}

.field-value {
    color: #495057;
    font-weight: 500;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
    padding: 12px;
}

.alert {
    border-radius: 6px;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.text-danger {
    color: #dc3545 !important;
}

.text-muted {
    color: #6c757d !important;
}

.text-secondary {
    color: #6c757d !important;
}

.text-primary {
    color: #007bff !important;
}

/* Responsive table with vertical scrolling */
.table-responsive-vertical {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table-responsive-vertical .table {
    margin-bottom: 0;
    min-width: 100%;
}

.table-responsive-vertical .table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    padding: 12px 8px;
    white-space: nowrap;
}

.table-responsive-vertical .table tbody td {
    padding: 12px 8px;
    vertical-align: middle;
    border-bottom: 1px solid #dee2e6;
    white-space: nowrap;
    min-width: 120px;
}

/* Row number styling */
.row-number {
    width: 60px;
    text-align: center;
    background-color: #e9ecef;
    font-weight: 600;
}

.row-number-cell {
    text-align: center;
    background-color: #f8f9fa;
    font-weight: 600;
}

/* Element header styling */
.element-header {
    min-width: 150px;
    text-align: left;
    font-size: 0.9rem;
}

.element-cell {
    min-width: 120px;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Field value styling */
.field-value {
    color: #495057;
    font-weight: 500;
    word-break: break-word;
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.badge-primary {
    background-color: #007bff;
    color: white;
}

/* Hover effects */
.table-hover tbody tr:hover {
    background-color: #f5f5f5;
}

/* Scrollbar styling */
.table-responsive-vertical::-webkit-scrollbar {
    width: 8px;
}

.table-responsive-vertical::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive-vertical::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-responsive-vertical::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Signature image styling */
.signature-image {
    display: block;
    margin: 0 auto;
    object-fit: contain;
    background-color: #f8f9fa;
    padding: 4px;
}

.signature-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 4px;
    color: #6c757d;
    font-size: 0.9rem;
    min-height: 80px;
}

.signature-placeholder i {
    font-size: 1.5rem;
    margin-bottom: 8px;
    opacity: 0.7;
}

.signature-placeholder span {
    font-size: 0.8rem;
    font-weight: 500;
}
</style>
