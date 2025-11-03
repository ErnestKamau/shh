{{-- Enhanced Form Display with Custom Field Loading --}}
<div class="enhanced-form-display" data-form-instance-id="{{ $instance->id }}">
    <div class="form-header mb-4">
        <h4 class="text-primary">
            <i class="mdi mdi-file-document"></i> {{ $instance->submissionForm->name }}
        </h4>
        <p class="text-muted">{{ $instance->submissionForm->description }}</p>
        
        <div class="form-meta">
            <span class="badge badge-{{ $instance->getStatusBadgeColor() }}">{{ ucfirst($instance->status) }}</span>
            <span class="badge badge-{{ $instance->getPriorityBadgeColor() }} ml-2">{{ ucfirst($instance->priority) }}</span>
            <small class="text-muted ml-3">
                Submitted by {{ $instance->submittedBy->name ?? 'Unknown' }} on {{ $instance->submitted_at ? $instance->submitted_at->format('M d, Y H:i') : 'N/A' }}
            </small>
        </div>
    </div>

    @php
        $formData = $instance->getFormDataForDisplay();
    @endphp

    {{-- Form Data for JavaScript --}}
    <script type="application/json" id="form-data-{{ $instance->id }}">
    {!! json_encode($formData) !!}
    </script>

    {{-- Custom Field Loader will be loaded after jQuery --}}

    {{-- Process each section --}}
    @foreach($formData['sections'] as $section)
        <div class="section-container mb-4" data-section-id="{{ $section['id'] }}">
            <div class="section-header">
                <h5 class="text-secondary border-bottom pb-2">
                    <i class="mdi mdi-{{ $section['section_type'] === 'rows' ? 'table' : 'form-select' }}"></i> 
                    {{ $section['title'] }}
                </h5>
                @if($section['description'])
                    <p class="text-muted small mb-0">{{ $section['description'] }}</p>
                @endif
            </div>

            {{-- Process element holders --}}
            @foreach($section['element_holders'] as $holder)
                @if($holder['holder_type'] === 'rows')
                    {{-- Use enhanced rows section display --}}
                    @include('submission-forms.partials.rows-section-display-enhanced', [
                        'section' => $instance->submissionForm->sections->find($section['id']),
                        'instance' => $instance,
                        'existingValues' => $instance->values
                    ])
                @else
                    {{-- Regular section display --}}
                    <div class="regular-section-display">
                        <h6 class="mb-3">{{ $holder['title'] ?? 'Form Fields' }}</h6>
                        
                        <div class="row">
                            @foreach($holder['elements'] as $elementData)
                                <div class="col-md-6 mb-3">
                                    @include('submission-forms.partials.form-element-display', [
                                        'element' => $instance->submissionForm->sections
                                            ->find($section['id'])
                                            ->elementHolders
                                            ->find($holder['id'])
                                            ->elements
                                            ->find($elementData['id']),
                                        'existingValues' => $instance->values
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach
</div>
@section('script2')
<script>
$(document).ready(function() {
    const formInstanceId = {{ $instance->id }};
    const formData = JSON.parse($('#form-data-' + formInstanceId).html());
    
    console.log('Initializing enhanced form display for instance:', formInstanceId);
    console.log('Form data:', formData);
    
    // Initialize the custom field loader
    window.customFieldLoader.initializeFormData(formData);
    
    // Initialize any additional UI components
    initializeFormUI();
});

function initializeFormUI() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Add loading states
    $('.section-container').each(function() {
        const $section = $(this);
        const sectionId = $section.data('section-id');
        
        // Add loading indicator
        $section.prepend('<div class="loading-indicator" style="display: none;"><i class="mdi mdi-loading mdi-spin"></i> Loading data...</div>');
        
        // Show loading indicator
        $section.find('.loading-indicator').show();
        
        // Hide loading indicator after a short delay (simulating data loading)
        setTimeout(() => {
            $section.find('.loading-indicator').fadeOut();
        }, 1000);
    });
}
</script>
@endsection

<style>
.enhanced-form-display {
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

.loading-indicator {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.field-display-value {
    margin-top: 5px;
    padding: 8px 12px;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-weight: 500;
    color: #495057;
}

.field-display-value.empty {
    color: #6c757d;
    font-style: italic;
}

.file-display {
    padding: 8px 12px;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.required::after {
    content: " *";
    color: #dc3545;
}

.badge-outline-info {
    color: #17a2b8;
    border: 1px solid #17a2b8;
    background-color: transparent;
}
</style>
