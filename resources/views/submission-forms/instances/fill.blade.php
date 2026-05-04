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
                                            <div class="form-section mb-4 {{ $section->getAlignmentClass() }}">
                                                <div class="section-header mb-3 {{ $section->getAlignmentClass() }}">
                          <h5 class="text-primary border-bottom pb-2">
                            <i class="mdi mdi-folder-outline"></i> {{ $section->title }}
                          </h5>
                                                    @include('submission-forms.partials.section-logos', ['section' => $section])
                          @if($section->description)
                            <p class="text-muted small mb-0">{{ $section->description }}</p>
                          @endif
                        </div>
                        
                        @foreach($section->elementHolders as $holder)
                          <div class="element-holder mb-3" data-holder-id="{{ $holder->id }}" data-section-id="{{ $section->id }}">
                            @if($holder->holder_type === 'field')
                              <div class="row">
                                @foreach($holder->elements as $element)
                                  <div class="col-md-{{ getColumnWidth($holder->elements->count()) }} mb-3" data-element-name="{{ $element->name }}">
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
                        <!--<button type="button" class="btn btn-outline-secondary" id="save-draft-btn">
                          <i class="mdi mdi-content-save-outline"></i> Save as Draft
                        </button>-->
                      </div>
                      <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-outline-danger mr-2" id="clear-form-btn">
                          <i class="mdi mdi-refresh"></i> Clear Form
                        </button>
                        <button type="submit" class="btn btn-primary" id="submit-form-btn" disabled>
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

@include('submission-forms.partials.add-entity-modals')
@endsection

@push('styles')
<style>
    /* Prevent horizontal overflow */
    body {
        overflow-x: hidden !important;
        max-width: 100vw;
    }
    
    html {
        overflow-x: hidden !important;
    }
    
    .container-fluid, .row, .col-md-10 {
        overflow-x: hidden;
    }

    /* Form Section Styling */
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

    /* Form Group Styling */
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

    .form-group.required label::after {
        content: " *";
        color: #dc3545;
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

    .required-field {
        color: #dc3545;
    }

    /* Form Actions */
    .form-actions {
        background-color: #f8f9fa;
        margin: 0 -1.25rem -1.25rem -1.25rem;
        padding: 1.25rem;
        border-radius: 0 0 0.375rem 0.375rem;
    }

    .progress {
        height: 8px;
    }

    /* Table Styling */
    .rows-section-table {
        margin-top: 1rem;
        max-width: 100%;
        overflow-x: auto;
    }

    .rows-section-table th {
        background-color: #f8f9fa;
        border-top: none;
    }

    .rows-section .table td {
        vertical-align: middle !important;
    }

    td {
        vertical-align: middle !important;
    }
    
    /* Ensure tables don't cause horizontal overflow */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
    }
    
    table {
        max-width: 100%;
    }

    /* Button Styling */
    .clone-row, .delete-row {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .add-row-btn {
        margin-top: 1rem;
    }

    /* Custom Element Styling */
    .custom-element {
        margin-bottom: 1rem;
        min-width: 145px;
        max-width: 100%;
    }

    .custom-element .form-control {
        border-radius: 0.375rem;
        min-width: 145px;
        max-width: 100%;
    }

    .custom-element .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Form Control Styling */
    .form-control {
        min-width: 145px;
        max-width: 100%;
    }

    select.custom-element {
        min-width: 145px;
        max-width: 100%;
    }

    /* Select2 Styling */
    .select2-container {
        width: 100% !important;
        min-width: 145px;
        max-width: 100%;
    }

    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-width: 145px;
        max-width: 100%;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        min-width: 145px;
        max-width: 100%;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .select2-dropdown {
        min-width: 145px;
        max-width: 100%;
    }

    /* Multiple Select Styling */
    select[multiple] {
        min-height: 38px !important;
    }

    select[multiple]:not(.select2-hidden-accessible) {
        height: 38px !important;
        min-height: 38px !important;
        padding: 8px;
    }

    .select2-container--default .select2-selection--multiple {
        min-height: 38px !important;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }

    /* Expand when items are selected */
    .select2-container--default .select2-selection--multiple.has-selections {
        min-height: 120px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 5px 5px 0px 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border: 1px solid #007bff;
        color: white;
        padding: 2px 8px;
        margin: 2px 5px 5px 0;
        border-radius: 3px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffc107;
    }

    /* Add icon buttons next to select fields */
    .form-group .btn-add-entity,
    .input-group-append .btn,
    .add-entity-btn {
        height: 38px !important;
        padding: 0.375rem 0.75rem;
        line-height: 1.5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Form Data Preview */
    #form-data-preview {
        font-size: 0.875em;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endpush

@section('script2')
<!-- Custom Elements Initialization Script -->
<script>
    // Emit required field specs derived from the form configuration
    $(()=>{

        window.requiredFieldSpecs = window.requiredFieldSpecs || [];
        console.log('[DEBUG] Sections being rendered:', {{ $submissionForm->sections->count() }});
        @foreach($submissionForm->sections as $section)
          console.log('[DEBUG] Rendering section {{ $section->id }}: {{ addslashes($section->title) }} ({{ $section->section_type }})');
          @php $inRows = $section->isRowsSection(); @endphp
          @foreach($section->elementHolders as $holder)
            console.log('[DEBUG]   - Holder {{ $holder->id }} ({{ $holder->holder_type }}): {{ $holder->elements->count() }} elements');
            @foreach($holder->elements as $element)
              console.log('[DEBUG]     - Element: {{ $element->name }} ({{ $element->element_type }}), required: {{ $element->is_required ? "yes" : "no" }}');
              @if($element->is_required)
                window.requiredFieldSpecs.push({
                  name: '{{ $element->name }}',
                  type: '{{ $element->element_type }}',
                  inRows: {{ $inRows ? 'true' : 'false' }}
                });
              @endif
            @endforeach
          @endforeach
        @endforeach
        console.log('[DEBUG] Total sections processed:', {{ $submissionForm->sections->count() }});
    })
</script>
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
        //console.log('Initializing custom elements...');
        
        if (!window.customElementsToInit) {
            //console.log('No custom elements to initialize');
            return;
        }
        
        //console.log('Found', window.customElementsToInit.length, 'custom elements to initialize');
        
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
        setupDependedFieldHandlers();
    }

    function initializeCustomElement(elementData) {
        const elementId = elementData.elementId;
        const elementType = elementData.elementType;
        
        //console.log('Initializing element:', elementId, 'type:', elementType);
        
        // Load initial options for non-dependent elements
        if (elementType === 'client_select' || elementType === 'sample_type_select' || elementType === 'store_select' || elementType === 'standard_select' || elementType === 'sample_condition_select') {
            //console.log('Loading initial options for:', elementType);
            let $element = $('#' + elementId);
            loadDynamicOptions($element, elementId, elementType);
        }
        
        // For client-dependent elements, ensure they start empty
        if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
            //console.log('Setting up client dependent element:', elementType);
            
            // Ensure element starts empty
            const placeholder = elementData.placeholder;
            $('#' + elementId).html('<option value="">' + placeholder + '</option>');
            
            // Load options if client is already selected
            const clientSelect = $('select[data-element-type="client_select"]');
            const currentClientId = clientSelect.val();
            //console.log('Current client ID:', currentClientId);
            
            if (currentClientId) {
                loadDynamicOptions($('#' + elementId), elementId, elementType, currentClientId);
            }
        }
        
        // For client unit dependent elements
        if (elementType === 'sample_point_select') {
            //console.log('Setting up client unit dependent element:', elementType);
            
            // Ensure element starts empty
            const placeholder = elementData.placeholder;
            $('#' + elementId).html('<option value="">' + placeholder + '</option>');
            
            // Load options if client unit is already selected
            const clientUnitSelect = $('select[data-element-type="client_unit_select"]');
            const currentClientUnitId = clientUnitSelect.val();
            //console.log('Current client unit ID:', currentClientUnitId);
            
            if (currentClientUnitId) {
                loadDynamicOptions($('#' + elementId), elementId, elementType, null, null, null, currentClientUnitId);
            }
        }
        
        // For sample type dependent elements
        if (elementType === 'analysis_type_select') {
            //console.log('Setting up sample type dependent element:', elementType);
            
            // Ensure element starts empty
            const placeholder = elementData.placeholder;
            $('#' + elementId).html('<option value="">' + placeholder + '</option>');
            
            // Load options if sample type is already selected
            const sampleTypeSelect = $('select[data-element-type="sample_type_select"]');
            const currentSampleTypeId = sampleTypeSelect.val();
            //console.log('Current sample type ID:', currentSampleTypeId);
            
            if (currentSampleTypeId) {
                loadDynamicOptions($('#' + elementId), elementId, elementType, null, currentSampleTypeId);
            }
        }
        
        // For store dependent elements
        if (elementType === 'store_slot_select') {
            //console.log('Setting up store dependent element:', elementType);
            
            // Ensure element starts empty
            const placeholder = elementData.placeholder;
            $('#' + elementId).html('<option value="">' + placeholder + '</option>');
            
            // Load options if store is already selected
            const storeSelect = $('select[data-element-type="store_select"]');
            const currentStoreId = storeSelect.val();
            //console.log('Current store ID:', currentStoreId);
            
            if (currentStoreId) {
                loadDynamicOptions($('#' + elementId), elementId, elementType, null, null, currentStoreId);
            }
        }
    }

    function setupClientChangeHandlers() {
        //console.log('Setting up client change handlers');
        
        // Remove any existing handlers
        $('select[data-element-type="client_select"]').off('change.custom-elements');
        
        // Set up client change handler
        $('select[data-element-type="client_select"]').on('change.custom-elements', function() {
            const clientId = $(this).val();
            //console.log('Client changed to:', clientId);
            
            // Find all dependent elements (only direct dependencies)
            const dependentElements = $('select[data-element-type="client_unit_select"], select[data-element-type="client_contact_select"]');
            //console.log('Found', dependentElements.length, 'client dependent elements');
            
            // Debug: Check what custom elements exist
            const allCustomElements = $('select[data-element-type]');
            //console.log('All custom elements found:', allCustomElements.length);
            allCustomElements.each(function() {
                //console.log('- Element:', $(this).attr('id'), 'Type:', $(this).data('element-type'));
            });
            
            if (clientId) {
                // Load options for each dependent element
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const dependentElementId = dependentSelect.attr('id');
                    const dependentElementType = dependentSelect.data('element-type');
                    
                    //console.log('Updating dependent element:', dependentElementType, dependentElementId);
                    
                    // Show loading state
                    dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                    
                    // Load options
                    loadDynamicOptions(dependentSelect, dependentElementId, dependentElementType, clientId);
                });
            } else {
                // Clear all dependent elements
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const placeholder = 'Select...';
                    dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                    //console.log('Cleared dependent element:', dependentSelect.attr('id'));
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
                    //console.log('Cleared client unit dependent element:', dependentSelect.attr('id'));
                });
            }
        });
    }

    function setupClientUnitChangeHandlers() {
        //console.log('Setting up client unit change handlers');
        
        // Remove any existing handlers
        $('select[data-element-type="client_unit_select"]').off('change.custom-elements');
        
        // Set up client unit change handler
        $('select[data-element-type="client_unit_select"]').on('change.custom-elements', function() {
            const clientUnitId = $(this).val();
            //console.log('Client unit changed to:', clientUnitId);
            
            // Find all dependent elements
            const dependentElements = $('select[data-element-type="sample_point_select"], select[data-element-type="company_sub_unit_select"]');
            //console.log('Found', dependentElements.length, 'client unit dependent elements');
            
            if (clientUnitId) {
                // Load options for each dependent element
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const dependentElementId = dependentSelect.attr('id');
                    const dependentElementType = dependentSelect.data('element-type');
                    
                    //console.log('Updating dependent element:', dependentElementType, dependentElementId);
                    
                    // Show loading state
                    dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                    
                    // Load options - sample_point_select depends on client_unit_select
                    loadDynamicOptions(dependentSelect, dependentElementId, dependentElementType, null, null, null, clientUnitId);
                });
            } else {
                // Clear all dependent elements
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const placeholder = 'Select...';
                    dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                    //console.log('Cleared dependent element:', dependentSelect.attr('id'));
                });
            }
        });
    }

    function setupSampleTypeChangeHandlers() {
        //console.log('Setting up sample type change handlers');
        
        // Remove any existing handlers
        $('select[data-element-type="sample_type_select"]').off('change.custom-elements');
        
        // Set up sample type change handler
        $('select[data-element-type="sample_type_select"]').on('change.custom-elements', function() {
            const sampleTypeId = $(this).val();
            //console.log('Sample type changed to:', sampleTypeId);
            
            // Find all dependent elements
            let dependentElements = $(this).closest('tr').find('select[data-element-type="analysis_type_select"]');

            if(dependentElements.length == 0){
                // alert("Danger");
                dependentElements = $(document).find('select[data-element-type="analysis_type_select"]');
            }

            //console.log('Found', dependentElements.length, 'sample type dependent elements');
            
            if (sampleTypeId) {
                // Load options for each dependent element
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const dependentElementId = dependentSelect.attr('id');
                    const dependentElementType = dependentSelect.data('element-type');
                    
                    //console.log('Updating dependent element:', dependentElementType, dependentElementId);
                    
                    // Show loading state
                    dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                    
                    // Load options
                    loadDynamicOptions(dependentSelect, dependentElementId, dependentElementType, null, sampleTypeId);
                });
            } else {
                // Clear all dependent elements
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const placeholder = 'Select...';
                    dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                    //console.log('Cleared dependent element:', dependentSelect.attr('id'));
                });
            }
        });
    }

    function setupAnalysisTypeChangeHandlers() {
        //console.log('Setting up analysis type change handlers');
        
        // Remove any existing handlers
        $('select[data-element-type="analysis_type_select"]').off('change.custom-elements');
        
        // Set up analysis type change handler
        $('select[data-element-type="analysis_type_select"]').on('change.custom-elements', function() {
            const analysisTypeId = $(this).val();
            //console.log('Analysis type changed to:', analysisTypeId);
            
            // Find all dependent elements
            let dependentElements = $(this).closest('tr').find('select[data-element-type="analysis_elements_select"]');

            if(dependentElements.length == 0){
                dependentElements = $(document).find('select[data-element-type="analysis_elements_select"]');
            }

            //console.log('Found', dependentElements.length, 'analysis type dependent elements');
            
            if (analysisTypeId) {
                // Load options for each dependent element
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const dependentElementId = dependentSelect.attr('id');
                    const dependentElementType = dependentSelect.data('element-type');
                    
                    //console.log('Updating dependent element:', dependentElementType, dependentElementId);
                    
                    // Show loading state
                    dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                    
                    // Load options
                    loadDynamicOptions(dependentSelect, dependentElementId, dependentElementType, null, null, null, null, analysisTypeId);
                });
            } else {
                // Clear all dependent elements
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const placeholder = 'Select...';
                    dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                    //console.log('Cleared dependent element:', dependentSelect.attr('id'));
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
            //console.log('Store changed to:', storeId);
            
            // Find all dependent elements
            let dependentElements = $(this).closest('tr').find('select[data-element-type="store_slot_select"]');
            //console.log('Found', dependentElements.length, 'store dependent elements');
            
            if (storeId) {
                // Load options for each dependent element
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const dependentElementId = dependentSelect.attr('id');
                    const dependentElementType = dependentSelect.data('element-type');
                    
                    //console.log('Updating dependent element:', dependentElementType, dependentElementId);
                    
                    // Show loading state
                    dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                    
                    // Load options
                    loadDynamicOptions(dependentSelect, dependentElementId, dependentElementType, null, null, storeId);
                });
            } else {
                // Clear all dependent elements
                dependentElements.each(function() {
                    const dependentSelect = $(this);
                    const placeholder = 'Select...';
                    dependentSelect.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                    //console.log('Cleared dependent element:', dependentSelect.attr('id'));
                });
            }
        });
    }

    function setupDependedFieldHandlers() {
        $('input[data-element-type="depended_field"][data-depends-on]').each(function() {
            const $input = $(this);
            const dependsOnField = $input.data('depends-on');
            const elementId = $input.data('element-id');
            if (!dependsOnField || !elementId) return;
            $('select[name="' + dependsOnField + '"]').on('change.depended-field', function() {
                const sourceId = $(this).val();
                if (!sourceId) { $input.val(''); return; }
                $.ajax({
                    url: '{{ route("submission-forms.instances.depended-field-value") }}',
                    method: 'GET',
                    data: { element_id: elementId, source_id: sourceId },
                    success: function(response) {
                        if (response.success) { $input.val(response.value || ''); }
                    }
                });
            });
        });
    }

    function loadDynamicOptions($this, elementId, elementType, clientId = null, sampleTypeId = null, storeId = null, clientUnitId = null, analysisTypeId = null) {
        const select = $this;
        const originalHtml = select.html();
        
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
        
        // Debug log specifically for sample_point_select
        if (elementType === 'sample_point_select') {
            console.log('=== LOADING SAMPLE POINTS ===');
            console.log('Element ID:', elementId);
            console.log('Client Unit ID:', clientUnitId);
            console.log('AJAX URL:', ajaxUrl);
            console.log('AJAX Data:', ajaxData);
        }
        
        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            data: ajaxData,
            success: function(response) {
                // Debug log for sample_point_select response
                if (elementType === 'sample_point_select') {
                    console.log('=== SAMPLE POINTS RESPONSE ===');
                    console.log('Response:', response);
                    console.log('Options count:', response.options ? response.options.length : 0);
                    if (response.options && response.options.length > 0) {
                        console.log('Sample Points Data:', response.options);
                    } else {
                        console.warn('NO SAMPLE POINTS RETURNED!');
                    }
                }
                
                let html = '';
                
                // Add placeholder option (always add for dependent elements)
                if (elementType === 'client_unit_select' || elementType === 'client_contact_select' || elementType === 'sample_point_select' || elementType === 'analysis_type_select') {
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
                } else {
                    if (html === '') {
                        html += '<option value="">No options available</option>';
                    }
                }
                
                select.html(html).prop('disabled', false);
                
                // Handle saved values for both single and multiple selects
                const savedValue = select.attr('data-saved-value');
                if (savedValue) {
                    if (select.prop('multiple')) {
                        // Handle multiple select saved values
                        const savedMultipleValues = select.attr('data-saved-multiple-values');
                        if (savedMultipleValues) {
                            const values = savedMultipleValues.split(',').map(v => v.trim()).filter(v => v);
                            select.val(values);
                        }
                    } else {
                        // Handle single select saved values
                        select.val(savedValue);
                    }
                    
                    // Trigger change event to update dependent elements
                    select.trigger('change.custom-elements');
                }
            },
            error: function(xhr, status, error) {
                //console.error('Error loading options for', elementType, ':', error);
                //console.error('Status:', status);
                //console.error('Response:', xhr.responseText);
                select.html(originalHtml).prop('disabled', false);
                
                // Show error message
                if (xhr.status === 403) {
                    alert('You do not have permission to access this data.');
                } else if (xhr.status === 500) {
                    //console.error('Server error loading dynamic options');
                } else {
                    //console.error('Network error loading dynamic options');
                }
            }
        });
    }

    // Form fill functionality
    const FormFill = {
            init() {
                this.bindEvents();
                this.updateFormDataPreview();
                this.updateProgress();
                this.updateSubmitButtonState();
            },

            getRequiredSpecs() {
                return Array.isArray(window.requiredFieldSpecs) ? window.requiredFieldSpecs : [];
            },

            // Build logical groups for required fields using backend specs
            resolveRequiredFieldGroups() {
                const specs = this.getRequiredSpecs();
                console.log('[DEBUG] Required Field Specs:', specs);
                
                // DEBUG: Inspect actual DOM
                console.log('[DEBUG] === DOM INSPECTION ===');
                const allFields = $('#fill-form').find('input, select, textarea').not('[type="hidden"]');
                console.log('[DEBUG] Total fields in DOM:', allFields.length);
                allFields.each(function(idx) {
                    const name = $(this).attr('name');
                    const type = $(this).attr('type') || this.tagName.toLowerCase();
                    console.log(`[DEBUG]   ${idx + 1}. name="${name}", type="${type}"`);
                });
                console.log('[DEBUG] === END DOM INSPECTION ===');
                
                if (!specs.length) {
                    // Fallback: derive groups from DOM [required]
                    console.log('[DEBUG] No specs found, using DOM fallback');
                    const groups = {};
                    this.getAllFormFields().filter('[required]').each(function() {
                        const $el = $(this);
                        const name = $el.attr('name');
                        if (!name) return;
                        groups[name] = groups[name] || [];
                        groups[name].push($el);
                    });
                    console.log('[DEBUG] DOM-based groups:', Object.keys(groups));
                    return groups;
                }
                
                const groups = {};
                const $form = $('#fill-form');
                
                specs.forEach(spec => {
                    if (!spec || !spec.name) return;
                    console.log(`[DEBUG] Processing spec: ${spec.name}, inRows: ${spec.inRows}`);
                    
                    if (spec.inRows) {
                        // For rows: find all actual row instances
                        const selector = `[name^="${spec.name}["]`;
                        const $found = $form.find(selector);
                        console.log(`[DEBUG] Row field ${spec.name}: found ${$found.length} elements`);
                        if ($found.length > 0) {
                            $found.each(function() {
                                const $el = $(this);
                                const name = $el.attr('name');
                                if (!name) return;
                                groups[name] = groups[name] || [];
                                groups[name].push($el);
                            });
                        } else {
                            // No rows exist yet - add placeholder for "at least 1 row needed"
                            groups[`${spec.name}[0]`] = [];
                            console.log(`[DEBUG] Added placeholder for row field: ${spec.name}[0]`);
                        }
                    } else {
                        // For regular fields: find by exact name
                        const exactSelector = `[name="${spec.name}"]`;
                        const $found = $form.find(exactSelector);
                        console.log(`[DEBUG] Regular field ${spec.name}: found ${$found.length} elements with selector ${exactSelector}`);
                        if ($found.length > 0) {
                            groups[spec.name] = [];
                            $found.each(function() { groups[spec.name].push($(this)); });
                        } else {
                            // Field not in DOM yet (or dynamic) - add placeholder
                            groups[spec.name] = [];
                            console.log(`[DEBUG] Added placeholder for field: ${spec.name}`);
                        }
                    }
                });
                
                console.log('[DEBUG] Final resolved groups:', Object.keys(groups));
                console.log('[DEBUG] Total required groups:', Object.keys(groups).length);
                return groups;
            },

            countRequiredGroups() {
                const groups = this.resolveRequiredFieldGroups();
                return Object.keys(groups).length;
            },

            countFilledRequiredGroups() {
                const groups = this.resolveRequiredFieldGroups();
                let filled = 0;
                console.log('[DEBUG] Counting filled groups...');
                
                Object.keys(groups).forEach(key => {
                    const elements = groups[key];
                    
                    // If no elements in group, it's not filled (placeholder or not found)
                    if (!elements || elements.length === 0) {
                        console.log(`[DEBUG] Group "${key}": No elements (placeholder) - UNFILLED`);
                        return; // unfilled
                    }
                    
                    const $first = elements[0];
                    const type = ($first.attr('type') || '').toLowerCase();
                    const isSelect = $first.is('select');
                    const isRadio = type === 'radio';
                    // Only treat as checkbox if NOT a select (multi-selects also end with [])
                    const isCheckbox = !isSelect && (type === 'checkbox' || key.endsWith('[]'));
                    
                    let isFilled = false;
                    
                    if (isRadio) {
                        isFilled = elements.some($el => $el.is(':checked'));
                    } else if (isSelect) {
                        // Handle select (including multi-select) BEFORE checkbox check
                        if ($first.prop('multiple')) {
                            const val = $first.val();
                            isFilled = Array.isArray(val) ? val.length > 0 : !!val;
                        } else {
                            const val = $first.val();
                            isFilled = val !== null && val !== '' && val !== undefined;
                        }
                    } else if (isCheckbox) {
                        isFilled = elements.some($el => $el.is(':checked'));
                    } else if (type === 'file') {
                        isFilled = !!($first[0] && $first[0].files && $first[0].files.length > 0);
                    } else {
                        const val = $first.val();
                        isFilled = typeof val === 'string' ? val.trim() !== '' : val !== null && val !== undefined && val !== '';
                    }
                    
                    console.log(`[DEBUG] Group "${key}": ${isFilled ? 'FILLED' : 'UNFILLED'} (type: ${type || 'select'}, value: ${$first.val()})`);
                    if (isFilled) filled++;
                });
                
                console.log(`[DEBUG] Total filled groups: ${filled}`);
                return filled;
            },
            
            getAllFormFields() {
                // Get all fields in the main form, excluding modals and hidden fields
                return $('#fill-form').find('input:not([type="hidden"]), select, textarea')
                    .not('.modal input, .modal select, .modal textarea')
                    .not('[name="_token"], [name="_method"]');
            },
            
            getRequiredFormFields() {
                return this.getAllFormFields().filter('[required]');
            },
            
            checkAllRequiredFieldsFilled() {
                const requiredFields = this.getRequiredFormFields();
                
                let allFilled = true;
                requiredFields.each(function() {
                    const $field = $(this);
                    const value = $field.val();
                    
                    // Check based on field type
                    if ($field.is('select') && $field.prop('multiple')) {
                        // Multiple select must have at least one selection
                        if (!value || (Array.isArray(value) && value.length === 0)) {
                            allFilled = false;
                            return false; // break the loop
                        }
                    } else {
                        // Regular fields must have a non-empty value
                        if (!value || (typeof value === 'string' && value.trim() === '')) {
                            allFilled = false;
                            return false; // break the loop
                        }
                    }
                });
                
                return allFilled;
            },
            
            updateSubmitButtonState() {
                const totalRequired = this.countRequiredGroups();
                let allRequiredFilled = false;
                if (totalRequired > 0) {
                    allRequiredFilled = this.countFilledRequiredGroups() === totalRequired;
                } else {
                    allRequiredFilled = this.checkAllRequiredFieldsFilled();
                }
                const $submitBtn = $('#submit-form-btn');
                if (allRequiredFilled) {
                    $submitBtn.prop('disabled', false).removeClass('disabled');
                } else {
                    $submitBtn.prop('disabled', true).addClass('disabled');
                }
            },
            
            bindEvents() {
                // Update form data preview AND submit button state on input change
                $('#fill-form').on('input change', 'input, select, textarea', () => {
                    this.updateFormDataPreview();
                    this.updateProgress();
                    this.updateSubmitButtonState();
                });
                
                // Special handling for Select2 change events
                $('#fill-form').on('select2:select select2:unselect', 'select', () => {
                    this.updateFormDataPreview();
                    this.updateProgress();
                    this.updateSubmitButtonState();
                });
                
                // Form submission
                $('#fill-form').on('submit', (e) => {
                    e.preventDefault();
                    this.validateAndSubmit();
                });
                
                // Save draft
                //$('#save-draft-btn').on('click', () => {
                  //  this.saveDraft();
                //});
                
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
                
                this.getAllFormFields().each(function() {
                    const $element = $(this);
                    const name = $element.attr('name');
                    const type = $element.attr('type');
                    
                    if (!name) return;
                    
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
                    } else if ($element.is('select') && $element.prop('multiple')) {
                        value = $element.val() || [];
                    } else {
                        value = $element.val();
                    }
                    
                    data[name] = value;
                });
                
                return data;
            },
            
            updateProgress() {
                const totalRequired = this.countRequiredGroups();
                console.log('[DEBUG] updateProgress - Total required groups:', totalRequired);
                if (totalRequired > 0) {
                    const filledRequired = this.countFilledRequiredGroups();
                    console.log('[DEBUG] updateProgress - Filled required groups:', filledRequired);
                    const progress = Math.round((filledRequired / totalRequired) * 100);
                    console.log('[DEBUG] updateProgress - Progress:', progress + '%');
                    $('#progressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                    $('#progressText').text(progress + '% Complete');
                    if (progress < 25) {
                        $('#progressBar').removeClass('bg-success bg-warning').addClass('bg-danger');
                    } else if (progress < 75) {
                        $('#progressBar').removeClass('bg-danger bg-success').addClass('bg-warning');
                    } else {
                        $('#progressBar').removeClass('bg-danger bg-warning').addClass('bg-success');
                    }
                    return;
                }
                
                // Fallback: original behavior counting all fields
                const allFields = this.getAllFormFields();
                const totalFields = allFields.length;
                const filledFields = allFields.filter(function() {
                    const $this = $(this);
                    const value = $this.val();
                    if ($this.is('select') && $this.prop('multiple')) {
                        return value && value.length > 0;
                    }
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
                
                this.getRequiredFormFields().each(function() {
                    const $element = $(this);
                    const label = $element.closest('.form-group').find('label').text().replace(' *', '').trim() || 'This field';
                    const value = $element.val();

                    // Special handling for multiple select elements
                    if ($element.is('select') && $element.prop('multiple')) {
                        if (!value || (Array.isArray(value) && value.length === 0)) {
                            errors.push(`${label} is required`);
                            $element.addClass('is-invalid');
                        } else {
                            $element.removeClass('is-invalid');
                        }
                    } else {
                        // Regular field validation
                        if (!value || (typeof value === 'string' && value.trim() === '')) {
                            errors.push(`${label} is required`);
                            $element.addClass('is-invalid');
                        } else {
                            $element.removeClass('is-invalid');
                        }
                    }
                });
                
                // Email validation
                this.getAllFormFields().filter('[type="email"]').each(function() {
                    const $element = $(this);
                    const value = $element.val();
                    const label = $element.closest('.form-group').find('label').text().replace(' *', '').trim() || 'This field';
                    
                    if (value && !this.checkValidity()) {
                        errors.push(`${label} must be a valid email address`);
                        $element.addClass('is-invalid');
                    }
                });
                
                // Number validation
                this.getAllFormFields().filter('[type="number"]').each(function() {
                    const $element = $(this);
                    const value = $element.val();
                    const label = $element.closest('.form-group').find('label').text().replace(' *', '').trim() || 'This field';
                    
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
                    this.updateSubmitButtonState();
                    
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

    // Wait for jQuery before initializing
    (function initFormFill() {
        if (typeof $ === 'undefined') {
            setTimeout(initFormFill, 50);
            return;
        }
        
        $(document).ready(function() {
            // Set route URL for dynamic options
            window.dynamicOptionsRoute = "{{ auth()->check() ? route('submission-forms.dynamic-options') : route('forms.dynamic-options') }}";
            
            // Initialize form fill
            FormFill.init();
            
            // Initialize custom elements with dependency management
            initializeAllCustomElements();
        });
    })(); // End initFormFill
</script>

<!-- Include SweetAlert2 for better modals -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
