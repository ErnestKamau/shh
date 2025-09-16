@extends('layouts.lab.layout.app', ['select2'=>true])

@section('title2')
  <title>Fill Form - {{ $submissionForm->name }}</title>
@endsection

@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
          'icon' => null
        ),
        array(
          'link' => route('submission-forms.index'),
          'name' => 'Submission Forms',
          'icon' => null
        ),
        array(
          'link' => route('submission-forms.instances.index'),
          'name' => 'My Submissions',
          'icon' => null
        ),
        array(
          'link' => '#',
          'name' => 'Fill Form',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <div>
        <h2>
          <i class="mdi mdi-file-document-edit"></i> Fill Form
          <small class="text-muted">{{ $submissionForm->name }}</small>
        </h2>
        <div class="alert alert-info mt-2 mb-0">
          <i class="mdi mdi-information-outline"></i>
          <strong>Form Instance:</strong> {{ $instance->form_number }} - {{ $instance->title }}
          <span class="badge badge-{{ $instance->getStatusBadgeColor() }} ml-2">
            {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
          </span>
        </div>
      </div>
      <div>
        <a href="{{ route('submission-forms.instances.index') }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to My Submissions
        </a>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row justify-content-center">
        <div class="col-md-10">
          <div class="card">
            <div class="card-header">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h4 class="mb-1">{{ $submissionForm->name }}</h4>
                  @if($submissionForm->description)
                    <p class="text-muted mb-0">{{ $submissionForm->description }}</p>
                  @endif
                </div>
                <div class="text-right">
                  <small class="text-muted">Form Number: <strong>{{ $instance->form_number }}</strong></small>
                </div>
              </div>
            </div>
            <div class="card-body">
              @if($submissionForm->sections->count() > 0)
                <form id="fill-form" method="POST" action="{{ route('submission-forms.instances.update', [$submissionForm, $instance]) }}" enctype="multipart/form-data" novalidate>
                  @csrf
                  @method('PUT')
                  
                  <!-- Progress Bar -->
                  <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="text-muted">Form Progress</span>
                      <span class="text-muted" id="progressText">0% Complete</span>
                    </div>
                    <div class="progress">
                      <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                  
                  @foreach($submissionForm->sections as $section)
                    @if($section->isRowsSection())
                      @include('submission-forms.partials.rows-section', ['section' => $section])
                    @else
                      <div class="form-section mb-4">
                        <div class="section-header mb-3">
                          <h5 class="text-primary border-bottom pb-2">
                            <i class="mdi mdi-folder-outline"></i> {{ $section->title }}
                          </h5>
                          @if($section->description)
                            <p class="text-muted small mb-0">{{ $section->description }}</p>
                          @endif
                        </div>
                        
                        @foreach($section->elementHolders as $holder)
                          <div class="element-holder mb-3">
                            @if($holder->holder_type === 'field')
                              <div class="row">
                                @foreach($holder->elements as $element)
                                  <div class="col-md-{{ getColumnWidth($holder->elements->count()) }} mb-3">
                                    @include('submission-forms.partials.form-element', ['element' => $element])
                                  </div>
                                @endforeach
                              </div>
                            @else
                              {{-- Text holder - for static content --}}
                              @foreach($holder->elements as $element)
                                <div class="text-element mb-3">
                                  <div class="alert alert-light">
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
                    @endif
                  @endforeach
                  
                  <div class="form-actions mt-4 pt-3 border-top">
                    <div class="row">
                      <div class="col-md-6">
                        <button type="button" class="btn btn-outline-secondary" id="save-draft-btn">
                          <i class="mdi mdi-content-save-outline"></i> Save as Draft
                        </button>
                      </div>
                      <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-outline-danger mr-2" id="clear-form-btn">
                          <i class="mdi mdi-refresh"></i> Clear Form
                        </button>
                        <button type="submit" class="btn btn-primary" id="submit-form-btn">
                          <i class="mdi mdi-check"></i> Submit Form
                        </button>
                      </div>
                    </div>
                  </div>
                </form>
              @else
                <div class="text-center py-5">
                  <i class="mdi mdi-file-outline" style="font-size: 4rem; color: #ccc;"></i>
                  <h5 class="text-muted mt-3">No Form Content</h5>
                  <p class="text-muted">This form doesn't have any sections or elements yet.</p>
                  <a href="{{ route('submission-forms.instances.index') }}" class="btn btn-primary mt-2">
                    <i class="mdi mdi-arrow-left"></i> Back to My Submissions
                  </a>
                </div>
              @endif
            </div>
          </div>
          
          @if($submissionForm->sections->count() > 0)
            <!-- Form Validation Summary -->
            <div class="card mt-3" id="validation-summary" style="display: none;">
              <div class="card-header bg-danger text-white">
                <h6 class="mb-0">
                  <i class="mdi mdi-alert"></i> Please correct the following errors:
                </h6>
              </div>
              <div class="card-body">
                <ul id="validation-errors" class="mb-0"></ul>
              </div>
            </div>
            
            <!-- Form Data Preview -->
            <div class="card mt-3">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="mdi mdi-code-json"></i> Form Data Preview
                  <small class="text-muted">(for testing purposes)</small>
                </h6>
              </div>
              <div class="card-body">
                <pre id="form-data-preview" class="bg-light p-3 rounded"><code>{}</code></pre>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Form Submission</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit this form? Once submitted, you won't be able to make changes unless it's returned for revision.</p>
                <div class="alert alert-info">
                    <i class="mdi mdi-information"></i>
                    <strong>Note:</strong> Please review all your answers before submitting.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSubmitBtn">
                    <i class="mdi mdi-send"></i> Yes, Submit Form
                </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection

@push('styles')
<style>
.form-section {
    border-left: 3px solid #007bff;
    padding-left: 20px;
    scroll-margin-top: 100px;
}

.section-header h5 {
    color: #007bff;
}

.element-holder {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Form group spacing */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label.required::after {
    content: " *";
    color: red;
}

.is-invalid {
    border-color: #dc3545;
}

.file-info {
    font-size: 0.875em;
}

.text-element .alert {
    border-left: 4px solid #17a2b8;
}

.form-actions {
    background-color: #f8f9fa;
    margin: 0 -1.25rem -1.25rem -1.25rem;
    padding: 1.25rem;
    border-radius: 0 0 0.375rem 0.375rem;
}

.progress {
    height: 8px;
}

.required-field {
    color: #dc3545;
}

.form-group.required label::after {
    content: " *";
    color: #dc3545;
}

.rows-section-table {
    margin-top: 1rem;
}

.rows-section-table th {
    background-color: #f8f9fa;
    border-top: none;
}

.clone-row, .delete-row {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.add-row-btn {
    margin-top: 1rem;
}

/* Custom element styling */
.custom-element {
    margin-bottom: 1rem;
    min-width: 145px !important;
}

.custom-element .form-control {
    border-radius: 0.375rem;
    min-width: 145px !important;
}

.custom-element .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Ensure all form controls have minimum width */
.form-control {
    min-width: 145px !important;
}

/* Select2 styling - ensure minimum width */
.select2-container {
    width: 100% !important;
    min-width: 145px !important;
}

.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-width: 145px !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
    min-width: 145px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

/* Ensure custom element selects have minimum width */
select.custom-element {
    min-width: 145px !important;
}

/* Select2 dropdown minimum width */
.select2-dropdown {
    min-width: 145px !important;
}
</style>
@endpush

@section('script2')
<!-- Custom Elements Initialization Script -->
<script>
// Wait for jQuery and DOM to be ready
function waitForJQuery(callback) {
    if (typeof $ !== 'undefined') {
        $(document).ready(callback);
    } else {
        setTimeout(function() { waitForJQuery(callback); }, 100);
    }
}

// Initialize all custom elements
function initializeAllCustomElements() {
    console.log('Initializing custom elements...');
    
    if (!window.customElementsToInit) {
        console.log('No custom elements to initialize');
        return;
    }
    
    console.log('Found', window.customElementsToInit.length, 'custom elements to initialize');
    
    // Initialize each element
    window.customElementsToInit.forEach(function(elementData) {
        initializeCustomElement(elementData);
    });
    
    // Set up change handlers
    setupClientChangeHandlers();
    setupClientUnitChangeHandlers();
    setupSampleTypeChangeHandlers();
    setupAnalysisTypeChangeHandlers();
    setupStoreChangeHandlers();
}

function initializeCustomElement(elementData) {
    const elementId = elementData.elementId;
    const elementType = elementData.elementType;
    
    console.log('Initializing element:', elementId, 'type:', elementType);
    
    // Load initial options for non-dependent elements
    if (elementType === 'client_select' || elementType === 'sample_type_select' || elementType === 'store_select' || elementType === 'standard_select' || elementType === 'sample_condition_select') {
        console.log('Loading initial options for:', elementType);
        loadDynamicOptions(elementId, elementType);
    }
    
    // For client-dependent elements, ensure they start empty
    if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
        console.log('Setting up client dependent element:', elementType);
        
        // Ensure element starts empty
        const placeholder = elementData.placeholder;
        $('#' + elementId).html('<option value="">' + placeholder + '</option>');
        
        // Load options if client is already selected
        const clientSelect = $('select[data-element-type="client_select"]');
        const currentClientId = clientSelect.val();
        console.log('Current client ID:', currentClientId);
        
        if (currentClientId) {
            loadDynamicOptions(elementId, elementType, currentClientId);
        }
    }
    
    // For client unit dependent elements
    if (elementType === 'sample_point_select') {
        console.log('Setting up client unit dependent element:', elementType);
        
        // Ensure element starts empty
        const placeholder = elementData.placeholder;
        $('#' + elementId).html('<option value="">' + placeholder + '</option>');
        
        // Load options if client unit is already selected
        const clientUnitSelect = $('select[data-element-type="client_unit_select"]');
        const currentClientUnitId = clientUnitSelect.val();
        console.log('Current client unit ID:', currentClientUnitId);
        
        if (currentClientUnitId) {
            loadDynamicOptions(elementId, elementType, null, null, null, currentClientUnitId);
        }
    }
    
    // For sample type dependent elements
    if (elementType === 'analysis_type_select') {
        console.log('Setting up sample type dependent element:', elementType);
        
        // Ensure element starts empty
        const placeholder = elementData.placeholder;
        $('#' + elementId).html('<option value="">' + placeholder + '</option>');
        
        // Load options if sample type is already selected
        const sampleTypeSelect = $('select[data-element-type="sample_type_select"]');
        const currentSampleTypeId = sampleTypeSelect.val();
        console.log('Current sample type ID:', currentSampleTypeId);
        
        if (currentSampleTypeId) {
            loadDynamicOptions(elementId, elementType, null, currentSampleTypeId);
        }
    }
    
    // For store dependent elements
    if (elementType === 'store_slot_select') {
        console.log('Setting up store dependent element:', elementType);
        
        // Ensure element starts empty
        const placeholder = elementData.placeholder;
        $('#' + elementId).html('<option value="">' + placeholder + '</option>');
        
        // Load options if store is already selected
        const storeSelect = $('select[data-element-type="store_select"]');
        const currentStoreId = storeSelect.val();
        console.log('Current store ID:', currentStoreId);
        
        if (currentStoreId) {
            loadDynamicOptions(elementId, elementType, null, null, currentStoreId);
        }
    }
}

function setupClientChangeHandlers() {
    console.log('Setting up client change handlers');
    
    // Remove any existing handlers
    $('select[data-element-type="client_select"]').off('change.custom-elements');
    
    // Set up client change handler
    $('select[data-element-type="client_select"]').on('change.custom-elements', function() {
        const clientId = $(this).val();
        console.log('Client changed to:', clientId);
        
        // Find all dependent elements (only direct dependencies)
        const dependentElements = $('select[data-element-type="client_unit_select"], select[data-element-type="client_contact_select"]');
        console.log('Found', dependentElements.length, 'client dependent elements');
        
        // Debug: Check what custom elements exist
        const allCustomElements = $('select[data-element-type]');
        console.log('All custom elements found:', allCustomElements.length);
        allCustomElements.each(function() {
            console.log('- Element:', $(this).attr('id'), 'Type:', $(this).data('element-type'));
        });
        
        if (clientId) {
            // Load options for each dependent element
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const dependentElementId = dependentSelect.attr('id');
                const dependentElementType = dependentSelect.data('element-type');
                
                console.log('Updating dependent element:', dependentElementType, dependentElementId);
                
                // Show loading state
                dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                
                // Load options
                loadDynamicOptions(dependentElementId, dependentElementType, clientId);
            });
        } else {
            // Clear all dependent elements
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const placeholder = 'Select...';
                dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                console.log('Cleared dependent element:', dependentSelect.attr('id'));
            });
            
            // Also clear elements that depend on client_unit_select
            const clientUnitDependentElements = $(this).closest('tr').find('select[data-element-type="sample_point_select"]');

            if(clientUnitDependentElements.length == 0){
                const clientUnitDependentElements = $('select[data-element-type="sample_point_select"]');
            }

            clientUnitDependentElements.each(function() {
                const dependentSelect = $(this);
                const placeholder = 'Select...';
                dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                console.log('Cleared client unit dependent element:', dependentSelect.attr('id'));
            });
        }
    });
}

function setupClientUnitChangeHandlers() {
    console.log('Setting up client unit change handlers');
    
    // Remove any existing handlers
    $('select[data-element-type="client_unit_select"]').off('change.custom-elements');
    
    // Set up client unit change handler
    $('select[data-element-type="client_unit_select"]').on('change.custom-elements', function() {
        const clientUnitId = $(this).val();
        console.log('Client unit changed to:', clientUnitId);
        
        // Find all dependent elements
        const dependentElements = $('select[data-element-type="sample_point_select"]');
        console.log('Found', dependentElements.length, 'client unit dependent elements');
        
        if (clientUnitId) {
            // Load options for each dependent element
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const dependentElementId = dependentSelect.attr('id');
                const dependentElementType = dependentSelect.data('element-type');
                
                console.log('Updating dependent element:', dependentElementType, dependentElementId);
                
                // Show loading state
                dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                
                // Load options - sample_point_select depends on client_unit_select
                loadDynamicOptions(dependentElementId, dependentElementType, null, null, null, clientUnitId);
            });
        } else {
            // Clear all dependent elements
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const placeholder = 'Select...';
                dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                console.log('Cleared dependent element:', dependentSelect.attr('id'));
            });
        }
    });
}

function setupSampleTypeChangeHandlers() {
    console.log('Setting up sample type change handlers');
    
    // Remove any existing handlers
    $('select[data-element-type="sample_type_select"]').off('change.custom-elements');
    
    // Set up sample type change handler
    $('select[data-element-type="sample_type_select"]').on('change.custom-elements', function() {
        const sampleTypeId = $(this).val();
        console.log('Sample type changed to:', sampleTypeId);
        
        // Find all dependent elements
        let dependentElements = $(this).closest('tr').find('select[data-element-type="analysis_type_select"]');

        if(dependentElements.length == 0){
            // alert("Danger");
            dependentElements = $(document).find('select[data-element-type="analysis_type_select"]');
        }

        console.log('Found', dependentElements.length, 'sample type dependent elements');
        
        if (sampleTypeId) {
            // Load options for each dependent element
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const dependentElementId = dependentSelect.attr('id');
                const dependentElementType = dependentSelect.data('element-type');
                
                console.log('Updating dependent element:', dependentElementType, dependentElementId);
                
                // Show loading state
                dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                
                // Load options
                loadDynamicOptions(dependentElementId, dependentElementType, null, sampleTypeId);
            });
        } else {
            // Clear all dependent elements
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const placeholder = 'Select...';
                dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                console.log('Cleared dependent element:', dependentSelect.attr('id'));
            });
        }
    });
}

function setupAnalysisTypeChangeHandlers() {
    console.log('Setting up analysis type change handlers');
    
    // Remove any existing handlers
    $('select[data-element-type="analysis_type_select"]').off('change.custom-elements');
    
    // Set up analysis type change handler
    $('select[data-element-type="analysis_type_select"]').on('change.custom-elements', function() {
        const analysisTypeId = $(this).val();
        console.log('Analysis type changed to:', analysisTypeId);
        
        // Find all dependent elements
        let dependentElements = $(this).closest('tr').find('select[data-element-type="analysis_elements_select"]');

        if(dependentElements.length == 0){
            dependentElements = $(document).find('select[data-element-type="analysis_elements_select"]');
        }

        console.log('Found', dependentElements.length, 'analysis type dependent elements');
        
        if (analysisTypeId) {
            // Load options for each dependent element
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const dependentElementId = dependentSelect.attr('id');
                const dependentElementType = dependentSelect.data('element-type');
                
                console.log('Updating dependent element:', dependentElementType, dependentElementId);
                
                // Show loading state
                dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                
                // Load options
                loadDynamicOptions(dependentElementId, dependentElementType, null, null, null, null, analysisTypeId);
            });
        } else {
            // Clear all dependent elements
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const placeholder = 'Select...';
                dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                console.log('Cleared dependent element:', dependentSelect.attr('id'));
            });
        }
    });
}

function setupStoreChangeHandlers() {
    
    // Remove any existing handlers
    $('select[data-element-type="store_select"]').off('change.custom-elements');
    
    // Set up store change handler
    $('select[data-element-type="store_select"]').on('change.custom-elements', function() {
        const storeId = $(this).val();
        console.log('Store changed to:', storeId);
        
        // Find all dependent elements
        let dependentElements = $(this).closest('tr').find('select[data-element-type="store_slot_select"]');
        console.log('Found', dependentElements.length, 'store dependent elements');
        
        if (storeId) {
            // Load options for each dependent element
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const dependentElementId = dependentSelect.attr('id');
                const dependentElementType = dependentSelect.data('element-type');
                
                console.log('Updating dependent element:', dependentElementType, dependentElementId);
                
                // Show loading state
                dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                
                // Load options
                loadDynamicOptions(dependentElementId, dependentElementType, null, null, storeId);
            });
        } else {
            // Clear all dependent elements
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const placeholder = 'Select...';
                dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                console.log('Cleared dependent element:', dependentSelect.attr('id'));
            });
        }
    });
}

function loadDynamicOptions(elementId, elementType, clientId = null, sampleTypeId = null, storeId = null, clientUnitId = null, analysisTypeId = null) {
    const select = $('#' + elementId);
    const originalHtml = select.html();
    
    console.log('Loading options for element:', elementId, 'type:', elementType, 'clientId:', clientId, 'sampleTypeId:', sampleTypeId, 'storeId:', storeId, 'clientUnitId:', clientUnitId, 'analysisTypeId:', analysisTypeId);
    
    // Show loading state
    select.html('<option value="">Loading...</option>').prop('disabled', true);
    
    // Make AJAX request
    const ajaxUrl = '{{ auth()->check() ? route("submission-forms.dynamic-options") : route("forms.dynamic-options") }}';
    const ajaxData = {
        element_type: elementType,
        client_id: clientId,
        sample_type_id: sampleTypeId,
        store_id: storeId,
        client_unit_id: clientUnitId,
        analysis_type_id: analysisTypeId
    };
    
    console.log('Making AJAX request to:', ajaxUrl, 'with data:', ajaxData);
    
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        data: ajaxData,
        success: function(response) {
            console.log('Received response for', elementType, ':', response);
            let html = '';
            
            // Add placeholder option (always add for dependent elements)
            if (elementType === 'client_unit_select' || elementType === 'client_contact_select' || elementType === 'sample_point_select') {
                html += '<option value="">Select...</option>';
            } else {
                // For non-dependent elements, check if required
                const elementData = window.customElementsToInit.find(e => e.elementId === elementId);
                if (!elementData || !elementData.isRequired) {
                    const placeholder = elementData ? elementData.placeholder : 'Select...';
                    html += '<option value="">' + placeholder + '</option>';
                }
            }
            
            // Add options from response
            if (response.options && response.options.length > 0) {
                response.options.forEach(function(option) {
                    html += '<option value="' + option.value + '">' + option.label + '</option>';
                });
                console.log('Added', response.options.length, 'options to', elementType);
            } else {
                console.warn('No options returned for element type:', elementType);
                if (html === '') {
                    html += '<option value="">No options available</option>';
                }
            }
            
            select.html(html).prop('disabled', false);
        },
        error: function(xhr, status, error) {
            console.error('Error loading options for', elementType, ':', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            select.html(originalHtml).prop('disabled', false);
            
            // Show error message
            if (xhr.status === 403) {
                alert('You do not have permission to access this data.');
            } else if (xhr.status === 500) {
                console.error('Server error loading dynamic options');
            } else {
                console.error('Network error loading dynamic options');
            }
        }
    });
}

// // Initialize when jQuery is ready
// waitForJQuery(function() {
//     console.log('jQuery is ready, initializing custom elements');
//     initializeAllCustomElements();
// });
</script>

<script>
$(document).ready(function() {
    console.log('Fill form page loaded, initializing...');
    
    // Set route URL for dynamic options
    window.dynamicOptionsRoute = "{{ auth()->check() ? route('submission-forms.dynamic-options') : route('forms.dynamic-options') }}";
    
    // Form fill functionality
    const FormFill = {
        init() {
            this.bindEvents();
            this.updateFormDataPreview();
            this.updateProgress();
        },
        
        bindEvents() {
            // Update form data preview on input change
            $('#fill-form').on('input change', 'input, select, textarea', () => {
                this.updateFormDataPreview();
                this.updateProgress();
            });
            
            // Form submission
            $('#fill-form').on('submit', (e) => {
                e.preventDefault();
                this.validateAndSubmit();
            });
            
            // Save draft
            $('#save-draft-btn').on('click', () => {
                this.saveDraft();
            });
            
            // Clear form
            $('#clear-form-btn').on('click', () => {
                this.clearForm();
            });
            
            // File upload handling
            $('input[type="file"]').on('change', function() {
                const file = this.files[0];
                if (file) {
                    $(this).next('.file-info').remove();
                    $(this).after(`<small class="file-info text-muted d-block mt-1">Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)</small>`);
                }
            });
        },
        
        updateFormDataPreview() {
            const formData = this.getFormData();
            $('#form-data-preview code').text(JSON.stringify(formData, null, 2));
        },
        
        getFormData() {
            const data = {};
            
            $('#fill-form').find('input, select, textarea').each(function() {
                const $element = $(this);
                const name = $element.attr('name');
                const type = $element.attr('type');
                
                if (!name || name === '_token') return;
                
                let value = null;
                
                if (type === 'checkbox') {
                    value = $element.is(':checked');
                } else if (type === 'radio') {
                    if ($element.is(':checked')) {
                        value = $element.val();
                    } else {
                        return; // Skip unchecked radio buttons
                    }
                } else if (type === 'file') {
                    const file = $element[0].files[0];
                    value = file ? {
                        name: file.name,
                        size: file.size,
                        type: file.type
                    } : null;
                } else {
                    value = $element.val();
                }
                
                data[name] = value;
            });
            
            return data;
        },
        
        updateProgress() {
            const totalFields = $('#fill-form input, #fill-form select, #fill-form textarea').length;
            const filledFields = $('#fill-form input, #fill-form select, #fill-form textarea').filter(function() {
                const value = $(this).val();
                return value !== null && value !== '' && value !== undefined;
            }).length;
            
            const progress = totalFields > 0 ? Math.round((filledFields / totalFields) * 100) : 0;
            
            $('#progressBar').css('width', progress + '%').attr('aria-valuenow', progress);
            $('#progressText').text(progress + '% Complete');
            
            // Change color based on progress
            if (progress < 25) {
                $('#progressBar').removeClass('bg-success bg-warning').addClass('bg-danger');
            } else if (progress < 75) {
                $('#progressBar').removeClass('bg-danger bg-success').addClass('bg-warning');
            } else {
                $('#progressBar').removeClass('bg-danger bg-warning').addClass('bg-success');
            }
        },
        
        validateForm() {
            const errors = [];
            
            $('#fill-form').find('input[required], select[required], textarea[required]').each(function() {
                const $element = $(this);
                const label = $element.closest('.form-group').find('label').text().replace(' *', '');
                const value = $element.val();
                
                if (!value || value.trim() === '') {
                    errors.push(`${label} is required`);
                    $element.addClass('is-invalid');
                } else {
                    $element.removeClass('is-invalid');
                }
            });
            
            // Email validation
            $('#fill-form').find('input[type="email"]').each(function() {
                const $element = $(this);
                const value = $element.val();
                const label = $element.closest('.form-group').find('label').text().replace(' *', '');
                
                if (value && !this.checkValidity()) {
                    errors.push(`${label} must be a valid email address`);
                    $element.addClass('is-invalid');
                }
            });
            
            // Number validation
            $('#fill-form').find('input[type="number"]').each(function() {
                const $element = $(this);
                const value = $element.val();
                const label = $element.closest('.form-group').find('label').text().replace(' *', '');
                
                if (value && !this.checkValidity()) {
                    errors.push(`${label} must be a valid number`);
                    $element.addClass('is-invalid');
                }
            });
            
            return errors;
        },
        
        validateAndSubmit() {
            const errors = this.validateForm();
            
            if (errors.length > 0) {
                this.showValidationErrors(errors);
                return;
            }
            
            this.hideValidationErrors();
            this.submitForm();
        },
        
        showValidationErrors(errors) {
            const $errorsList = $('#validation-errors');
            $errorsList.empty();
            
            errors.forEach(error => {
                $errorsList.append(`<li>${error}</li>`);
            });
            
            $('#validation-summary').show();
            $('html, body').animate({
                scrollTop: $('#validation-summary').offset().top - 100
            }, 500);
        },
        
        hideValidationErrors() {
            $('#validation-summary').hide();
            $('#fill-form').find('.is-invalid').removeClass('is-invalid');
        },
        
        submitForm() {
            // Add action hidden input
            if ($('#action').length === 0) {
                $('#fill-form').append('<input type="hidden" name="action" id="action">');
            }
            $('#action').val('submit');
            
            // Disable submit buttons
            $('#submit-form-btn, #save-draft-btn').prop('disabled', true);
            
            // Show loading state
            $('#submit-form-btn').html('<i class="mdi mdi-loading mdi-spin"></i> Submitting...');
            
            // Submit the form
            $('#fill-form')[0].submit();
        },
        
        saveDraft() {
            // Add action hidden input
            if ($('#action').length === 0) {
                $('#fill-form').append('<input type="hidden" name="action" id="action">');
            }
            $('#action').val('draft');
            
            // Disable submit buttons
            $('#submit-form-btn, #save-draft-btn').prop('disabled', true);
            
            // Show loading state
            $('#save-draft-btn').html('<i class="mdi mdi-loading mdi-spin"></i> Saving...');
            
            // Submit the form
            $('#fill-form')[0].submit();
        },
        
        clearForm() {
            if (confirm('Are you sure you want to clear all form data?')) {
                $('#fill-form')[0].reset();
                $('#fill-form').find('.is-invalid').removeClass('is-invalid');
                $('#fill-form').find('.file-info').remove();
                this.hideValidationErrors();
                this.updateFormDataPreview();
                this.updateProgress();
                
                // Reinitialize custom elements after clearing
                initializeAllCustomElements();
                
                // Show success message
                Swal.fire({
                    title: 'Form Cleared',
                    text: 'All form data has been cleared.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    };
    
    // Initialize form fill
    FormFill.init();
    
    // Initialize custom elements with dependency management
    initializeAllCustomElements();
});
</script>

<!-- Include SweetAlert2 for better modals -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.form-section {
    border-left: 3px solid #007bff;
    padding-left: 20px;
}

.section-header h5 {
    color: #007bff;
}

.element-holder {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
}

/* Ensure all form controls have minimum width */
.form-control {
    min-width: 145px !important;
}

/* Custom element styling */
.custom-element {
    margin-bottom: 1rem;
    min-width: 145px !important;
}

.custom-element .form-control {
    border-radius: 0.375rem;
    min-width: 145px !important;
}

/* Select2 styling - ensure minimum width */
.select2-container {
    width: 100% !important;
    min-width: 145px !important;
}

.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-width: 145px !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
    min-width: 145px !important;
}

/* Ensure custom element selects have minimum width */
select.custom-element {
    min-width: 145px !important;
}

/* Select2 dropdown minimum width */
.select2-dropdown {
    min-width: 145px !important;
}

.form-group label.required::after {
    content: " *";
    color: red;
}

.is-invalid {
    border-color: #dc3545;
}

.file-info {
    font-size: 0.875em;
}

#form-data-preview {
    font-size: 0.875em;
    max-height: 300px;
    overflow-y: auto;
}

.text-element .alert {
    border-left: 4px solid #17a2b8;
}

.form-actions {
    background-color: #f8f9fa;
    margin: 0 -1.25rem -1.25rem -1.25rem;
    padding: 1.25rem;
    border-radius: 0 0 0.375rem 0.375rem;
}
</style>
@endsection

@php
function getColumnWidth($elementCount) {
    switch($elementCount) {
        case 1:
            return 12;
        case 2:
            return 6;
        case 3:
            return 4;
        case 4:
            return 3;
        default:
            return 12 / min($elementCount, 6);
    }
}
@endphp
