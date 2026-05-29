@extends('layouts.lab.layout.app')

@section('title2')
  <title>{{ $submissionForm->name }} - Submission Form</title>
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
          'link' => '#',
          'name' => $submissionForm->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @include('submission-forms.partials.horizontal-gutter-styles')
    <div class="submission-forms-horizontal-gutter">

    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm border-0 bg-white" style="border-radius: 15px;">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
              <div>
                <h2 class="mb-1">
                  <i class="mdi mdi-form-select text-primary"></i> {{ $submissionForm->name }}
                  <small class="text-muted">v{{ $submissionForm->version }}</small>
                </h2>
                @if($submissionForm->description)
                  <p class="text-muted mb-0">{{ $submissionForm->description }}</p>
                @endif
              </div>
              <div class="d-flex align-items-center flex-wrap justify-content-lg-end" style="gap: 8px;">
                <a href="{{ route('submission-forms.builder', $submissionForm) }}" class="btn btn-outline-primary" style="border-radius: 9px;">
                  <i class="mdi mdi-cog"></i> Form Builder
                </a>
                <a href="{{ route('submission-forms.edit', $submissionForm) }}" class="btn btn-outline-warning" style="border-radius: 9px;">
                  <i class="mdi mdi-pencil"></i> Edit Details
                </a>
                <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-secondary" style="border-radius: 9px;">
                  <i class="mdi mdi-arrow-left"></i> Back to Forms
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row">
        <!-- Form Status and Actions -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-information-outline"></i> Form Status
              </h6>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                  <span>Publication Status:</span>
                  @if($submissionForm->is_published)
                    <span class="badge badge-success">Published</span>
                  @else
                    <span class="badge badge-warning">Draft</span>
                  @endif
                </div>
              </div>
              
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                  <span>Active Status:</span>
                  @if($submissionForm->is_active)
                    <span class="badge badge-outline-success">Active</span>
                  @else
                    <span class="badge badge-outline-danger">Inactive</span>
                  @endif
                </div>
              </div>

              <div class="d-grid gap-2">
                <form method="POST" action="{{ route('submission-forms.toggle-published', $submissionForm) }}">
                  @csrf
                  <button type="submit" 
                          class="btn btn-sm {{ $submissionForm->is_published ? 'btn-outline-danger' : 'btn-outline-success' }} w-100">
                    <i class="mdi {{ $submissionForm->is_published ? 'mdi-eye-off' : 'mdi-publish' }}"></i>
                    {{ $submissionForm->is_published ? 'Unpublish' : 'Publish' }}
                  </button>
                </form>
              </div>
            </div>
          </div>

          <div class="card mt-3">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-chart-bar"></i> Statistics
              </h6>
            </div>
            <div class="card-body">
              <div class="row text-center">
                <div class="col-6">
                  <div class="border-right">
                    <h4 class="text-primary">{{ $statistics['total_sections'] }}</h4>
                    <small class="text-muted">Sections</small>
                  </div>
                </div>
                <div class="col-6">
                  <h4 class="text-info">{{ $statistics['total_elements'] }}</h4>
                  <small class="text-muted">Elements</small>
                </div>
              </div>
              <hr>
              <div class="row text-center">
                <div class="col-4">
                  <h5 class="text-secondary">{{ $statistics['total_instances'] }}</h5>
                  <small class="text-muted">Total</small>
                </div>
                <div class="col-4">
                  <h5 class="text-warning">{{ $statistics['draft_instances'] }}</h5>
                  <small class="text-muted">Drafts</small>
                </div>
                <div class="col-4">
                  <h5 class="text-success">{{ $statistics['approved_instances'] }}</h5>
                  <small class="text-muted">Approved</small>
                </div>
              </div>
            </div>
          </div>

          <div class="card mt-3">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-cog"></i> Quick Actions
              </h6>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('submission-forms.preview', $submissionForm) }}" class="btn btn-outline-info btn-sm">
                  <i class="mdi mdi-eye-outline"></i> Preview Form
                </a>
                
                <form method="POST" action="{{ route('submission-forms.clone', $submissionForm) }}" 
                      onsubmit="return confirm('Are you sure you want to clone this form?')">
                  @csrf
                  <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="mdi mdi-content-copy"></i> Clone Form
                  </button>
                </form>
                
                <a href="{{ route('submission-forms.export', $submissionForm) }}" class="btn btn-outline-info btn-sm">
                  <i class="mdi mdi-download"></i> Export Structure
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Structure -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0">
                <i class="mdi mdi-file-tree"></i> Form Structure
              </h6>
              <a href="{{ route('submission-forms.builder', $submissionForm) }}" class="btn btn-sm btn-primary">
                <i class="mdi mdi-cog"></i> Open Builder
              </a>
            </div>
            <div class="card-body">
              @if($submissionForm->sections->count() > 0)
                <div class="form-structure">
                  @foreach($submissionForm->sections as $section)
                    <div class="section-item mb-4">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <h6 class="text-primary mb-1">
                            <i class="mdi mdi-folder-outline"></i> {{ $section->title }}
                          </h6>
                          @if($section->description)
                            <p class="text-muted small mb-2">{!! nl2br(e($section->description)) !!}</p>
                          @endif
                        </div>
                        <span class="badge badge-light">{{ $section->elementHolders->count() }} holder(s)</span>
                      </div>
                      
                      @if($section->elementHolders->count() > 0)
                        <div class="ml-3 mt-2">
                          @foreach($section->elementHolders as $holder)
                            <div class="holder-item mb-3 p-2 border-left border-info">
                              <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-info font-weight-bold">
                                  <i class="mdi mdi-{{ $holder->holder_type === 'field' ? 'form-textbox' : 'text' }}"></i>
                                  {{ ucfirst($holder->holder_type) }} Holder
                                  ({{ $holder->elements->count() }}/{{ $holder->max_elements }})
                                </small>
                                @if($holder->isAtCapacity())
                                  <span class="badge badge-warning badge-sm">Full</span>
                                @endif
                              </div>
                              
                              @if($holder->elements->count() > 0)
                                <div class="elements-list">
                                  @foreach($holder->elements as $element)
                                    <div class="element-item d-flex justify-content-between align-items-center py-1">
                                      <div class="d-flex align-items-center">
                                        <i class="mdi mdi-{{ getElementIcon($element->element_type) }} text-secondary mr-2"></i>
                                        <span class="small">{{ $element->label }}</span>
                                        @if($element->is_required)
                                          <span class="text-danger ml-1">*</span>
                                        @endif
                                      </div>
                                      <div>
                                        <span class="badge badge-outline-secondary badge-sm">{{ $element->element_type }}</span>
                                        @if($element->is_readonly)
                                          <span class="badge badge-outline-warning badge-sm">readonly</span>
                                        @endif
                                      </div>
                                    </div>
                                  @endforeach
                                </div>
                              @else
                                <div class="text-muted small">No elements added yet</div>
                              @endif
                            </div>
                          @endforeach
                        </div>
                      @else
                        <div class="ml-3 mt-2 text-muted small">No element holders added yet</div>
                      @endif
                    </div>
                  @endforeach
                </div>
              @else
                <div class="text-center py-5">
                  <i class="mdi mdi-file-tree" style="font-size: 3rem; color: #ccc;"></i>
                  <h5 class="text-muted mt-3">No sections added yet</h5>
                  <p class="text-muted">Use the Form Builder to add sections and form elements.</p>
                  <a href="{{ route('submission-forms.builder', $submissionForm) }}" class="btn btn-primary mt-2">
                    <i class="mdi mdi-cog"></i> Open Form Builder
                  </a>
                </div>
              @endif
            </div>
          </div>

          <!-- Form Details -->
          <div class="card mt-3">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-information"></i> Form Details
              </h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <dl class="row">
                    <dt class="col-sm-5">Created By:</dt>
                    <dd class="col-sm-7">{{ $submissionForm->creator->name ?? 'Unknown' }}</dd>
                    
                    <dt class="col-sm-5">Created:</dt>
                    <dd class="col-sm-7">{{ $submissionForm->created_at->format('M d, Y H:i') }}</dd>
                    
                    <dt class="col-sm-5">Last Updated:</dt>
                    <dd class="col-sm-7">{{ $submissionForm->updated_at->format('M d, Y H:i') }}</dd>
                  </dl>
                </div>
                <div class="col-md-6">
                  <dl class="row">
                    <dt class="col-sm-5">Form Prefix:</dt>
                    <dd class="col-sm-7">{{ $submissionForm->naming_convention_prefix }}</dd>
                    
                    <dt class="col-sm-5">Number Format:</dt>
                    <dd class="col-sm-7">
                      <code>{{ $submissionForm->naming_convention_format }}</code>
                    </dd>
                    
                    <dt class="col-sm-5">Version:</dt>
                    <dd class="col-sm-7">{{ $submissionForm->version }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    </div>
  </main>

  <!-- Success/Error Messages -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif
@endsection

@section('scripts')
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
    
    // Set up change handlers with a small delay to ensure elements are ready
    setTimeout(function() {
        setupClientChangeHandlers();
        setupClientUnitChangeHandlers();
        setupSampleTypeChangeHandlers();
        setupAnalysisTypeChangeHandlers();
        setupStoreChangeHandlers();
        
        // Test event delegation with a simple click handler
        $(document).on('click', 'select[data-element-type="analysis_type_select"]', function() {
            console.log('🎯 CLICK DETECTED on analysis type select:', $(this).attr('id'));
        });
        
        // Test change event with a simple handler
        $(document).on('change', 'select[data-element-type="analysis_type_select"]', function() {
            console.log('🎯 CHANGE DETECTED on analysis type select:', $(this).val());
        });
    }, 100);
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
    
    // For analysis type dependent elements
    if (elementType === 'analysis_elements_select') {
        console.log('Setting up analysis type dependent element:', elementType);
        
        // Ensure element starts empty
        const placeholder = elementData.placeholder;
        $('#' + elementId).html('<option value="">' + placeholder + '</option>');
        
        // Load options if analysis type is already selected
        const analysisTypeSelect = $('select[data-element-type="analysis_type_select"]');
        const currentAnalysisTypeId = analysisTypeSelect.val();
        console.log('Current analysis type ID:', currentAnalysisTypeId);
        
        if (currentAnalysisTypeId) {
            loadDynamicOptions(elementId, elementType, null, null, null, currentAnalysisTypeId);
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
    console.log('Setting up client change handlers with event delegation');
    
    // Remove any existing handlers
    $(document).off('change.custom-elements', 'select[data-element-type="client_select"]');
    
    // Use event delegation to handle dynamically added elements
    $(document).on('change.custom-elements', 'select[data-element-type="client_select"]', function() {
        const clientId = $(this).val();
        console.log('Client changed to:', clientId);
        
        // Find all dependent elements
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
        }
    });
}

function setupClientUnitChangeHandlers() {
    console.log('Setting up client unit change handlers with event delegation');
    
    // Remove any existing handlers
    $(document).off('change.custom-elements', 'select[data-element-type="client_unit_select"]');
    
    // Use event delegation to handle dynamically added elements
    $(document).on('change.custom-elements', 'select[data-element-type="client_unit_select"]', function() {
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
    console.log('Setting up sample type change handlers with event delegation');
    
    // Remove any existing handlers
    $(document).off('change.custom-elements', 'select[data-element-type="sample_type_select"]');
    
    // Use event delegation to handle dynamically added elements
    $(document).on('change.custom-elements', 'select[data-element-type="sample_type_select"]', function() {
        const sampleTypeId = $(this).val();
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
    console.log('Setting up analysis type change handlers with event delegation');
    // Remove any existing handlers
    $(document).off('change.custom-elements', 'select[data-element-type="analysis_type_select"]');
    
    // Use event delegation to handle dynamically added elements
    $(document).on('change.custom-elements', 'select[data-element-type="analysis_type_select"]', function() {
        const analysisTypeId = $(this).val();
        console.log('🔍 ANALYSIS TYPE CHANGED TO:', analysisTypeId);
        console.log('🔍 Element data:', $(this).data());
        console.log('🔍 Element ID:', $(this).attr('id'));
        
        // Find all dependent elements
        const dependentElements = $('select[data-element-type="analysis_elements_select"]');
        console.log('Found', dependentElements.length, 'analysis type dependent elements');
        
        if (analysisTypeId) {
            // Load options for each dependent element
            dependentElements.each(function() {
                const dependentSelect = $(this);
                const dependentElementId = dependentSelect.attr('id');
                const dependentElementType = dependentSelect.data('element-type');
                
                console.log('Updating dependent element:', dependentElementType, dependentElementId, 'with analysisTypeId:', analysisTypeId);
                
                // Show loading state
                dependentSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                
                // Load options
                loadDynamicOptions(dependentElementId, dependentElementType, null, null, null, analysisTypeId);
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
    
    // Also set up a direct handler for existing elements
    const existingElements = $('select[data-element-type="analysis_type_select"]');
    console.log('Found', existingElements.length, 'existing analysis type select elements');
    
    if (existingElements.length > 0) {
        existingElements.off('change.analysis-type-direct');
        existingElements.on('change.analysis-type-direct', function() {
            console.log('🔍 DIRECT HANDLER: Analysis type changed to:', $(this).val());
            // Trigger the delegated event
            $(this).trigger('change.custom-elements');
        });
    }
}

function setupStoreChangeHandlers() {
    console.log('Setting up store change handlers with event delegation');
    
    // Remove any existing handlers
    $(document).off('change.custom-elements', 'select[data-element-type="store_select"]');
    
    // Use event delegation to handle dynamically added elements
    $(document).on('change.custom-elements', 'select[data-element-type="store_select"]', function() {
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

function loadDynamicOptions(elementId, elementType, clientId = null, sampleTypeId = null, storeId = null, analysisTypeId = null, clientUnitId = null) {
    const select = $('#' + elementId);
    const originalHtml = select.html();
    
    console.log('Loading options for element:', elementId, 'type:', elementType, 'clientId:', clientId, 'sampleTypeId:', sampleTypeId, 'storeId:', storeId, 'analysisTypeId:', analysisTypeId, 'clientUnitId:', clientUnitId);
    
    // Show loading state
    select.html('<option value="">Loading...</option>').prop('disabled', true);
    
    // Make AJAX request
    const ajaxUrl = '{{ auth()->check() ? route("submission-forms.dynamic-options") : route("forms.dynamic-options") }}';
    const ajaxData = {
        element_type: elementType,
        client_id: clientId,
        sample_type_id: sampleTypeId,
        store_id: storeId,
        analysis_type_id: analysisTypeId,
        client_unit_id: clientUnitId
    };
    
    console.log('🚀 MAKING AJAX REQUEST TO:', ajaxUrl, 'with data:', ajaxData);
    console.log('🚀 Analysis Type ID being sent:', analysisTypeId);
    
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        data: ajaxData,
        success: function(response) {
            console.log('✅ RECEIVED RESPONSE FOR', elementType, ':', response);
            console.log('✅ Response success:', response.success);
            console.log('✅ Response options count:', response.options ? response.options.length : 'No options');
            console.log('✅ Full response:', JSON.stringify(response, null, 2));
            let html = '';
            
            // Add placeholder option (always add for dependent elements)
            if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
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
            console.error('❌ ERROR LOADING OPTIONS FOR', elementType, ':', error);
            console.error('❌ Status:', status);
            console.error('❌ Response:', xhr.responseText);
            console.error('❌ XHR:', xhr);
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
  // Auto-hide alerts after 5 seconds
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);
</script>

<style>
  .d-grid {
    display: grid;
  }
  
  .gap-2 {
    gap: 0.5rem;
  }
  
  .form-structure .section-item {
    border-left: 3px solid #007bff;
    padding-left: 15px;
  }
  
  .holder-item {
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
  
  .element-item:hover {
    background-color: #e9ecef;
    border-radius: 3px;
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
  
  .badge-sm {
    font-size: 0.7em;
  }
</style>
@endsection

@php
function getElementIcon($elementType) {
    switch($elementType) {
        case 'text':
            return 'form-textbox';
        case 'number':
            return 'numeric';
        case 'email':
            return 'email-outline';
        case 'date':
            return 'calendar';
        case 'datetime':
            return 'calendar-clock';
        case 'textarea':
            return 'text-box-outline';
        case 'select':
            return 'form-dropdown';
        case 'radio':
            return 'radiobox-marked';
        case 'checkbox':
            return 'checkbox-marked';
        case 'file':
            return 'file-upload-outline';
        case 'camera_photo':
          return 'camera';
        case 'image_upload':
            return 'image-plus';
        case 'zone_select':
            return 'map-marker-radius';
        case 'signature':
            return 'draw';
        case 'calculation':
            return 'calculator';
        default:
            return 'form-textbox';
    }
}
@endphp