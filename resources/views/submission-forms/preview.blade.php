@extends('layouts.lab.layout.app', ['select2'=>true])

@section('title2')
  <title>Preview - {{ $submissionForm->name }}</title>
  @include('submission-forms.partials.lab-theme-styles')
@endsection

@section('content2')
  <main>
    @php
      $fromRft = request()->query('from') === 'rft' || $submissionForm->isTestRequestTemplate();
      $items = [
        [
          'link' => route('dashboard-lab'),
          'name' => 'Dashboard',
          'icon' => null,
        ],
        [
          'link' => $fromRft ? route('sample-workflow.request-for-testing') : route('submission-forms.index'),
          'name' => $fromRft ? 'Request For Testing' : 'Submission Forms',
          'icon' => null,
        ],
        [
          'link' => route('submission-forms.show', ['submissionForm' => $submissionForm, 'from' => $fromRft ? 'rft' : null]),
          'name' => $submissionForm->name,
          'icon' => null,
        ],
        [
          'link' => '#',
          'name' => 'Preview',
          'icon' => null,
        ],
      ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid workflow-board-page rft-page-shell rft-theme lab-panel-theme sf-admin-page px-3 px-md-4 pt-2 pb-4">
      <div class="workflow-board-panel mb-3">
        <div class="workflow-board-panel-header">
          <div>
            <h5 class="sf-page-title mb-0">
              <i class="mdi mdi-eye-outline"></i> Form Preview
            </h5>
            <p class="sf-page-subtitle">{{ $submissionForm->name }}</p>
          </div>
          <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            <a href="{{ route('submission-forms.builder', $submissionForm) }}" class="btn btn-sm btn-primary btn-action-sm">
              <i class="mdi mdi-cog"></i> Edit Form
            </a>
            <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-sm btn-outline-secondary btn-action-sm">
              <i class="mdi mdi-arrow-left"></i> Back to Form
            </a>
          </div>
        </div>
        <div class="workflow-board-panel-body pt-0">
          <div class="sf-alert-soft is-info mb-0">
            <i class="mdi mdi-information-outline"></i>
            <strong>Preview Mode:</strong> This is how the form will appear to users. Test data entered here will not be saved.
          </div>
        </div>
      </div>

      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="workflow-board-panel mb-3">
            <div class="workflow-board-panel-header">
              <div>
                <h6 class="mb-0">{{ $submissionForm->name }}</h6>
                @if($submissionForm->description)
                  <p class="sf-page-subtitle mb-0">{{ $submissionForm->description }}</p>
                @endif
              </div>
              <span class="sf-meta-chip">{{ $submissionForm->naming_convention_prefix }}/PREVIEW/001</span>
            </div>
            <div class="workflow-board-panel-body">
              @if($submissionForm->sections->count() > 0)
                <form id="preview-form" novalidate>
                  @csrf

                  @foreach($submissionForm->sections as $section)
                    @continue($section->is_hidden ?? false)
                    @if($section->isRowsSection())
                      @include('submission-forms.partials.rows-section', [
                        'section' => $section,
                        'existingValues' => $existingValues
                      ])
                    @else
                      <div class="form-section mb-4 {{ $section->getAlignmentClass() }}">
                        <div class="section-header mb-3 {{ $section->getAlignmentClass() }}">
                          <h6 class="mb-2" style="color: var(--workflow-text-main);">
                            <i class="mdi mdi-folder-outline text-primary"></i> {{ $section->title }}
                          </h6>
                          @include('submission-forms.partials.section-logos', ['section' => $section])
                          @if($section->description)
                            <p class="text-muted small mb-0">{!! nl2br(e($section->description)) !!}</p>
                          @endif
                        </div>

                        @foreach($section->elementHolders as $holder)
                          <div class="element-holder mb-3">
                            @if($holder->holder_type === 'field')
                              @php
                                $visibleHolderElements = $holder->elements->reject(fn ($el) => (bool) ($el->is_hidden ?? false))->values();
                              @endphp
                              <div class="row">
                                @foreach($visibleHolderElements as $element)
                                  <div class="col-md-{{ getColumnWidth($visibleHolderElements->count()) }} mb-3">
                                    @include('submission-forms.partials.form-element', ['element' => $element, 'existingValues' => $existingValues])
                                  </div>
                                @endforeach
                              </div>
                            @else
                              @foreach($holder->elements as $element)
                                  @continue($element->is_hidden ?? false)
                                <div class="text-element mb-3">
                                  <div class="sf-alert-soft is-info mb-0">
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

                  <div class="sf-form-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-action-sm" id="save-draft-btn">
                      <i class="mdi mdi-content-save-outline"></i> Save as Draft
                    </button>
                    <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                      <button type="button" class="btn btn-sm btn-outline-secondary btn-action-sm" id="clear-form-btn">
                        <i class="mdi mdi-refresh"></i> Clear Form
                      </button>
                      <button type="submit" class="btn btn-sm btn-primary btn-action-sm" id="submit-form-btn">
                        <i class="mdi mdi-check"></i> Submit Form
                      </button>
                    </div>
                  </div>
                </form>
              @else
                <div class="text-center py-5">
                  <i class="mdi mdi-file-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                  <h6 class="text-muted mt-3">No Form Content</h6>
                  <p class="text-muted small">This form doesn't have any sections or elements yet.</p>
                  <a href="{{ route('submission-forms.builder', $submissionForm) }}" class="btn btn-sm btn-primary btn-action-sm mt-2">
                    <i class="mdi mdi-cog"></i> Add Content
                  </a>
                </div>
              @endif
            </div>
          </div>

          @if($submissionForm->sections->count() > 0)
            <div class="workflow-board-panel mb-3" id="validation-summary" style="display: none;">
              <div class="workflow-board-panel-header">
                <h6 class="mb-0 text-danger"><i class="mdi mdi-alert"></i> Please correct the following errors</h6>
              </div>
              <div class="workflow-board-panel-body">
                <ul id="validation-errors" class="mb-0"></ul>
              </div>
            </div>

            <div class="workflow-board-panel mb-3">
              <div class="workflow-board-panel-header">
                <h6 class="mb-0">
                  <i class="mdi mdi-code-json"></i> Form Data Preview
                  <span class="sf-meta-chip ml-2">testing</span>
                </h6>
              </div>
              <div class="workflow-board-panel-body">
                <pre id="form-data-preview"><code>{}</code></pre>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </main>
@endsection

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
        
        // Find all dependent elements
        const dependentElements = $('select[data-element-type="client_unit_select"], select[data-element-type="client_contact_select"], select[data-element-type="sample_point_select"]');
        console.log('Found', dependentElements.length, 'dependent elements');
        
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
        const sampleTypeVal = $(this).val();
        const sampleTypeId = Array.isArray(sampleTypeVal)
            ? sampleTypeVal.filter(Boolean).join(',')
            : sampleTypeVal;
        console.log('Sample type changed to:', sampleTypeId);
        
        // Find all dependent elements
        const dependentElements = $('select[data-element-type="analysis_type_select"]');
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
        const dependentElements = $('select[data-element-type="analysis_elements_select"]');
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
    console.log('Setting up store change handlers');
    
    // Remove any existing handlers
    $('select[data-element-type="store_select"]').off('change.custom-elements');
    
    // Set up store change handler
    $('select[data-element-type="store_select"]').on('change.custom-elements', function() {
        const storeId = $(this).val();
        console.log('Store changed to:', storeId);
        
        // Find all dependent elements
        const dependentElements = $('select[data-element-type="store_slot_select"]');
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

// Initialize when jQuery is ready
waitForJQuery(function() {
    console.log('jQuery is ready, initializing custom elements');
    initializeAllCustomElements();
});
</script>

<script>
$(document).ready(function() {
    console.log('Preview page loaded, initializing...');
    
    // Set route URL for dynamic options
    window.dynamicOptionsRoute = "{{ auth()->check() ? route('submission-forms.dynamic-options') : route('forms.dynamic-options') }}";
    
    // Form preview functionality
    const FormPreview = {
        init() {
            this.bindEvents();
            this.updateFormDataPreview();
        },
        
        bindEvents() {
            // Update form data preview on input change
            $('#preview-form').on('input change', 'input, select, textarea', () => {
                this.updateFormDataPreview();
            });
            
            // Form submission
            $('#preview-form').on('submit', (e) => {
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
            
            $('#preview-form').find('input, select, textarea').each(function() {
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
        
        validateForm() {
            const errors = [];
            
            $('#preview-form').find('input[required], select[required], textarea[required]').each(function() {
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
            $('#preview-form').find('input[type="email"]').each(function() {
                const $element = $(this);
                const value = $element.val();
                const label = $element.closest('.form-group').find('label').text().replace(' *', '');
                
                if (value && !this.checkValidity()) {
                    errors.push(`${label} must be a valid email address`);
                    $element.addClass('is-invalid');
                }
            });
            
            // Number validation
            $('#preview-form').find('input[type="number"]').each(function() {
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
            this.showPreviewSubmission();
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
            $('#preview-form').find('.is-invalid').removeClass('is-invalid');
        },
        
        showPreviewSubmission() {
            const formData = this.getFormData();
            
            Swal.fire({
                title: 'Form Submission Preview',
                html: `
                    <div class="text-left">
                        <p><strong>This is a preview submission.</strong> In the actual form, this data would be saved to the database.</p>
                        <hr>
                        <h6>Form Data:</h6>
                        <pre class="bg-light p-2 rounded text-left" style="max-height: 300px; overflow-y: auto;"><code>${JSON.stringify(formData, null, 2)}</code></pre>
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'Close Preview',
                width: '600px'
            });
        },
        
        saveDraft() {
            const formData = this.getFormData();
            
            Swal.fire({
                title: 'Draft Saved',
                html: `
                    <div class="text-left">
                        <p><strong>This is a preview of the draft save functionality.</strong></p>
                        <p>In the actual form, this data would be saved as a draft and the user could return to complete it later.</p>
                        <hr>
                        <h6>Draft Data:</h6>
                        <pre class="bg-light p-2 rounded text-left" style="max-height: 200px; overflow-y: auto;"><code>${JSON.stringify(formData, null, 2)}</code></pre>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Close',
                width: '600px'
            });
        },
        
        clearForm() {
            if (confirm('Are you sure you want to clear all form data?')) {
                $('#preview-form')[0].reset();
                $('#preview-form').find('.is-invalid').removeClass('is-invalid');
                $('#preview-form').find('.file-info').remove();
                this.hideValidationErrors();
                this.updateFormDataPreview();
                
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
    
    // Initialize form preview
    FormPreview.init();
    
    // Initialize custom elements with dependency management
    initializeAllCustomElements();
});
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