@php
    $isInline = (bool) ($inline ?? false);
    $useLabLayout = (bool) ($use_lab_layout ?? false);
@endphp
@if($isInline)
  @extends('layouts.bare')
@elseif($useLabLayout)
  @extends('layouts.lab.layout.app', ['select2'=>true])
@else
  @extends('layouts.sample-submissions', ['select2' => true])
@endif

@if(!$isInline)
@section($useLabLayout ? 'title2' : 'title')
  <title>Fill Form - {{ $submissionForm->name }}</title>
    @if($useLabLayout)
    <style>
        main{
            margin-top: 0px !important;
            padding-top: 0px !important;
        }
        #main-container-body{
            margin-top: 0px !important;
            padding-top: 0px !important;
        }
    </style>
  @endif
@endsection
@endif

@section($isInline ? 'content' : ($useLabLayout ? 'content2' : 'content'))
    @if(!$isInline && !$useLabLayout)
    <?php
      $items = array(
        array(
          'link' => route('home'),
          'name' => 'Home',
          'icon' => null
        ),
        array(
          'link' => route('sample-submissions'),
          'name' => 'Sample Submissions',
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
    
    <div class="p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 h-md-2 mb-0">
          <i class="mdi mdi-file-document-edit"></i> Fill Form
          <small class="d-block d-md-inline text-muted mt-1 mt-md-0">{{ $submissionForm->name }}</small>
        </h2>
        <a href="{{ route('sample-submissions') }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> 
          <span class="d-none d-sm-inline">Back to Sample Submissions</span>
          <span class="d-inline d-sm-none">Back</span>
        </a>
      </div>
      
      <div class="w-100">
        
        <div class="alert alert-info mb-0 p-2 p-md-3">
          <i class="mdi mdi-information-outline"></i>
          <strong>Form Instance:</strong> 
          <span class="d-block d-sm-inline">{{ $instance->getDocumentControlNumber() ?? 'New Submission' }} - {{ $instance->title }}</span>
          <span class="badge badge-{{ $instance->getStatusBadgeColor() }} ml-0 ml-sm-2 mt-1 mt-sm-0">
            {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
          </span>
        </div>
      </div>
    </div>
    @elseif(!$isInline && $useLabLayout)
    <div class="px-3 pt-3">
      <div class="alert alert-info mb-0 p-2 p-md-3">
        <i class="mdi mdi-information-outline"></i>
        <strong>Form Instance:</strong>
        <span class="d-block d-sm-inline">{{ $instance->getDocumentControlNumber() ?? 'New Submission' }} — {{ $instance->title }}</span>
        <span class="badge badge-{{ $instance->getStatusBadgeColor() }} ml-0 ml-sm-2 mt-1 mt-sm-0">
          {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
        </span>
      </div>
    </div>
    @endif

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
                  <small class="text-muted">Form Number: <strong>{{ $instance->getDocumentControlNumber() ?? 'Pending' }}</strong></small>
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
                    @continue($section->is_hidden ?? false)
                    @if($section->isRowsSection())
                      @include('submission-forms.partials.rows-section', [
                        'section' => $section,
                        'existingValues' => $existingValues,
                        'allowedSampleTypeIds' => $allowedSampleTypeIds ?? null
                      ])
                    @else
                                            <div class="form-section mb-4 {{ $section->getAlignmentClass() }}">
                                                <div class="section-header mb-3 {{ $section->getAlignmentClass() }}">
                          <h5 class="text-primary border-bottom pb-2">
                            <i class="mdi mdi-folder-outline"></i> {{ $section->title }}
                          </h5>
                                                    @include('submission-forms.partials.section-logos', ['section' => $section])
                          @if($section->description)
                            <p class="text-muted small mb-0">{!! nl2br(e($section->description)) !!}</p>
                          @endif
                        </div>
                        
                        @foreach($section->elementHolders as $holder)
                          <div class="element-holder mb-3" data-holder-id="{{ $holder->id }}" data-section-id="{{ $section->id }}">
                            @if($holder->holder_type === 'field')
                              @php
                                $visibleHolderElements = $holder->elements->reject(fn ($el) => (bool) ($el->is_hidden ?? false))->values();
                              @endphp
                              <div class="row">
                                @foreach($visibleHolderElements as $element)
                                  <div class="col-md-{{ getColumnWidth($visibleHolderElements->count()) }} mb-3" data-element-name="{{ $element->name }}">
                                    @include('submission-forms.partials.form-element', ['element' => $element, 'existingValues' => $existingValues])
                                  </div>
                                @endforeach
                              </div>
                            @else
                              {{-- Text holder - for static content --}}
                              @foreach($holder->elements as $element)
                                  @continue($element->is_hidden ?? false)
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
                        <button type="submit" class="btn btn-primary" id="submit-form-btn" disabled>
                          <i class="mdi mdi-check"></i> Submit Form
                        </button>
                      </div>
                    </div>
                  </div>
                </form>
                
                {{-- Modals (rendered outside form to avoid nested forms) --}}
                @include('submission-forms.partials.add-entity-modals')
              @else
                <div class="text-center py-5">
                  <i class="mdi mdi-file-outline" style="font-size: 4rem; color: #ccc;"></i>
                  <h5 class="text-muted mt-3">No Form Content</h5>
                  <p class="text-muted">This form doesn't have any sections or elements yet.</p>
                  <a href="{{ route('sample-submissions') }}" class="btn btn-primary mt-2">
                    <i class="mdi mdi-arrow-left"></i> Back to Sample Submissions
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
            <div class="card mt-3 hidden">
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
        border-left: 3px solid var(--color-primary);
        padding-left: 20px;
        scroll-margin-top: 100px;
    }

    .section-header h5 {
        color: var(--color-primary);
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

    .rows-section .table {
        table-layout: auto;
        width: max-content;
        min-width: 100%;
    }

    .rows-section .table th,
    .rows-section .table td {
        min-width: 120px;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .rows-section .table th {
        vertical-align: top !important;
        line-height: 1.35;
        font-weight: 600;
    }

    .rows-section .table th:last-child,
    .rows-section .table td:last-child {
        min-width: 120px;
        width: 120px;
        white-space: nowrap;
    }

    .rows-section .table td .form-group {
        margin-bottom: 0 !important;
    }

    .rows-section .table td .form-control,
    .rows-section .table td .custom-element,
    .rows-section .table td .select2-container,
    .rows-section .table td .select2-selection--single,
    .rows-section .table td .select2-selection--multiple {
        min-width: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .rows-section .table td .custom-element-wrapper .form-control {
        padding-right: 0.75rem;
    }

    .rows-section .table td .floating-add-btn {
        position: static;
        margin-top: 0.5rem;
        width: 28px;
        height: 28px;
    }

    .plain-text-element {
        border: 0;
        background: transparent;
        padding: 0;
        min-width: 0;
        white-space: pre-wrap;
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
        min-width: 0;
        max-width: 100%;
    }

    .custom-element .form-control {
        border-radius: 0.375rem;
        min-width: 0;
        max-width: 100%;
    }

    .custom-element .form-control:focus {
        border-color: #d17578;
        box-shadow: 0 0 0 0.2rem var(--color-primary-focus);
    }

    /* Form Control Styling */
    .form-control {
        min-width: 0;
        max-width: 100%;
    }

    select.custom-element {
        min-width: 0;
        max-width: 100%;
    }

    /* Select2 Styling */
    .select2-container {
        width: 100% !important;
        min-width: 0;
        max-width: 100%;
    }

    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-width: 0;
        max-width: 100%;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        min-width: 0;
        max-width: 100%;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .select2-dropdown {
        min-width: 0;
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
        background-color: var(--color-primary);
        border: 1px solid var(--color-primary);
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

    /* User Signature Styling */
    .user-signature-container {
        margin-bottom: 1rem;
    }

    .signature-preview-wrapper {
        position: relative;
        width: 100%;
        min-height: 120px;
    }

    .signature-preview {
        max-height: 120px;
        max-width: 100%;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px;
        background-color: #f8f9fa;
        object-fit: contain;
    }

    .signature-placeholder {
        padding: 20px;
        text-align: center;
        border: 1px dashed #dee2e6;
        border-radius: 4px;
        background-color: #f8f9fa;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .signature-placeholder i {
        font-size: 2rem;
        display: block;
        margin-bottom: 8px;
        color: #6c757d;
    }

    .signature-placeholder small {
        color: #6c757d;
        font-size: 0.875rem;
    }
</style>
<!-- Custom Elements Initialization Script -->

<script>
    // Emit required field specs derived from the form configuration
    $(()=>{
        window.requiredFieldSpecs = window.requiredFieldSpecs || [];
        @foreach($submissionForm->sections as $section)
                    @continue($section->is_hidden ?? false)
          @php $inRows = $section->isRowsSection(); @endphp
          @foreach($section->elementHolders as $holder)
            @foreach($holder->elements as $element)
                                  @continue($element->is_hidden ?? false)
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
    })
</script>
<script>
// Define loadDynamicOptions globally FIRST so rows-section can use it
(function() {
    // Wait for jQuery before defining the function
    function initLoadDynamicOptions() {
        if (typeof $ === 'undefined') {
            setTimeout(initLoadDynamicOptions, 50);
            return;
        }
        
        // Make loadDynamicOptions globally accessible for rows-section and other scripts
        window.loadDynamicOptions = function($this, elementId, elementType, clientId = null, sampleTypeId = null, storeId = null, clientUnitId = null, analysisTypeId = null) {
            const select = $this;
            const originalHtml = select.html();
            
            // Only log for client_select
            if (elementType === 'client_select') {
                console.log('[CLIENT_SELECT] loadDynamicOptions called for:', elementId);
            }
            
            // Use Select2 AJAX for client_select to handle large datasets (20k+ records)
            if (elementType === 'client_select' && !select.data('select2-ajax-initialized')) {
                console.log('[CLIENT_SELECT] Initializing Select2 AJAX for:', elementId);
                
                const savedValue = select.attr('data-saved-value');
                if (savedValue) {
                    console.log('[CLIENT_SELECT] Saved value found:', savedValue);
                }
                
                // Destroy existing Select2 if it exists
                if (select.data('select2')) {
                    console.log('[CLIENT_SELECT] Destroying existing Select2');
                    select.select2('destroy');
                }
                
                const ajaxUrl = '{{ auth()->check() ? route("submission-forms.dynamic-options") : route("forms.dynamic-options") }}';
                console.log('[CLIENT_SELECT] AJAX URL:', ajaxUrl);
                
                // Initialize Select2 with AJAX
                try {
                    select.select2({
                    ajax: {
                        url: ajaxUrl,
                        dataType: 'json',
                        delay: 250,  // Debounce typing for 250ms
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        data: function (params) {
                            console.log('[CLIENT_SELECT] AJAX data function called with params:', params);
                            const requestData = {
                                element_type: 'client_select',
                                search: params.term || '',
                                page: params.page || 1,
                                per_page: 100
                            };
                            console.log('[CLIENT_SELECT] Sending request:', requestData);
                            return requestData;
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            
                            console.log('[CLIENT_SELECT] AJAX Response:', data);
                            
                            // Handle both response formats (with/without 'success' wrapper)
                            const options = data.options || (data.success ? data.options : []);
                            
                            // Map options to Select2 format
                            const results = (options || []).map(function(option) {
                                return {
                                    id: option.value || option.id,
                                    text: option.label || option.text || option.name
                                };
                            });
                            
                            console.log('[CLIENT_SELECT] Mapped ' + results.length + ' results, Has more:', data.pagination && data.pagination.has_more);
                            
                            return {
                                results: results,
                                pagination: {
                                    more: data.pagination && data.pagination.has_more
                                }
                            };
                        },
                        cache: true,
                        error: function(xhr, status, error) {
                            console.error('[CLIENT_SELECT] AJAX Error:', {
                                status: status,
                                error: error,
                                response: xhr.responseText
                            });
                        }
                    },
                    placeholder: 'Select a client...',
                    allowClear: true,
                    minimumInputLength: 0,
                    width: '100%',
                    language: {
                        inputTooShort: function() {
                            return 'Start typing to search clients...';
                        },
                        searching: function() {
                            return 'Searching...';
                        },
                        noResults: function() {
                            return 'No clients found';
                        }
                    }
                });
                console.log('[CLIENT_SELECT] Select2 AJAX initialized successfully for:', elementId);
                
                // Verify Select2 is configured with AJAX
                const select2Data = select.data('select2');
                if (select2Data) {
                    console.log('[CLIENT_SELECT] Select2 options:', select2Data.options);
                    console.log('[CLIENT_SELECT] Has AJAX config?', select2Data.options.ajax !== undefined);
                } else {
                    console.error('[CLIENT_SELECT] Select2 data not found after initialization!');
                }
                } catch (error) {
                    console.error('[CLIENT_SELECT] Error initializing Select2:', error);
                    console.error('[CLIENT_SELECT] Error details:', error.message, error.stack);
                }
                
                // Handle saved values - fetch the specific client by ID
                if (savedValue) {
                    console.log('[CLIENT_SELECT] Loading saved client ID:', savedValue);
                    $.ajax({
                        url: '/api/clients/' + savedValue,
                        dataType: 'json',
                        success: function(client) {
                            console.log('[CLIENT_SELECT] Loaded saved client:', client);
                            const option = new Option(client.name, client.id, true, true);
                            select.append(option).trigger('change');
                        },
                        error: function(xhr) {
                            console.error('[CLIENT_SELECT] Failed to load saved client:', savedValue, xhr.status);
                            select.val(savedValue).trigger('change');
                        }
                    });
                }
                
                // Mark as initialized to prevent re-initialization
                select.data('select2-ajax-initialized', true);
                
                // Add event listeners to debug Select2 behavior
                select.on('select2:opening', function() {
                    console.log('[CLIENT_SELECT] Select2 dropdown opening for:', elementId);
                });
                
                select.on('select2:open', function() {
                    console.log('[CLIENT_SELECT] Select2 dropdown opened');
                });
                
                select.on('select2:selecting', function(e) {
                    console.log('[CLIENT_SELECT] Selecting:', e.params.args.data);
                });
                
                // Trigger change handler if this is a regular field to update dependent fields
                select.trigger('change.custom-elements');
                
                return; // Exit early - Select2 handles everything from here
            }
            
            // Show loading state for non-Select2 fields
            select.html('<option value="">Loading...</option>').prop('disabled', true);
            
            // Make AJAX request
            const ajaxUrl = '{{ auth()->check() ? route("submission-forms.dynamic-options") : route("forms.dynamic-options") }}';
            const resolvedAnalysisTypeId = Array.isArray(analysisTypeId)
                ? (analysisTypeId[0] || null)
                : analysisTypeId;

            const ajaxData = {
                element_type: elementType,
                client_id: clientId,
                sample_type_id: sampleTypeId,
                store_id: storeId,
                client_unit_id: clientUnitId,
                analysis_type_id: resolvedAnalysisTypeId,
                submission_form_id: '{{ $submissionForm->id }}'
            };
            
            $.ajax({
                url: ajaxUrl,
                method: 'GET',
                data: ajaxData,
                success: function(response) {
                    
                    let html = '';
                    
                    // Add placeholder option (always add for dependent elements)
                    if (elementType === 'client_unit_select' || elementType === 'client_contact_select' || elementType === 'client_submission_officers_select' || elementType === 'sample_point_select' || elementType === 'analysis_type_select') {
                        html += '<option value="">Select...</option>';
                    } else {
                        // For non-dependent elements, check if required
                        const elementData = window.customElementsToInit && window.customElementsToInit.find(e => e.elementId === elementId);
                        if (!elementData || !elementData.isRequired) {
                            const placeholder = elementData ? elementData.placeholder : 'Select...';
                            html += '<option value="">' + placeholder + '</option>';
                        }
                    }
                    
                    // Add options from response
                    if (response.options && response.options.length > 0) {
                        response.options.forEach(function(option) {
                            const optionValue = option.value ?? option.id ?? '';
                            const optionLabel = option.label ?? option.text ?? optionValue;
                            if (!optionValue) {
                                return;
                            }
                            html += '<option value="' + optionValue + '">' + optionLabel + '</option>';
                        });
                    } else {
                        if (html === '') {
                            html += '<option value="">No options available</option>';
                        }
                    }
                    
                    select.html(html).prop('disabled', false);
                    
                    // Handle saved values for both single and multiple selects
                    const savedValue = select.attr('data-saved-value');
                    const defaultUserId = select.attr('data-default-user-id');
                    const valueToSet = savedValue || defaultUserId;
                    
                    if (valueToSet) {
                        if (select.prop('multiple')) {
                            // Handle multiple select saved values
                            const savedMultipleValues = select.attr('data-saved-multiple-values');
                            if (savedMultipleValues) {
                                const values = savedMultipleValues.split(',').map(v => v.trim()).filter(v => v);
                                select.val(values);
                            }
                        } else {
                            // Handle single select saved values
                            select.val(valueToSet);
                        }
                        
                        // Trigger change event to update dependent elements
                        select.trigger('change.custom-elements');
                        
                        // For user_select fields, trigger signature loading immediately
                        if (elementType === 'user_select') {
                            const fieldName = select.attr('name');
                            // Use valueToSet directly as it's the correct value we just set
                            const userIdToLoad = valueToSet;
                            
                            // Small delay to ensure DOM/Select2 is updated
                            setTimeout(function() {
                                // Trigger the user-signature change event (handlers should be set up by now)
                                select.trigger('change.user-signature');
                                
                                // Also manually trigger signature loading for immediate feedback
                                // This ensures signature loads even if event handlers aren't set up yet
                                if (userIdToLoad) {
                                    $('.user-signature-container').each(function() {
                                        const dependsOn = $(this).data('depends-on');
                                        if (dependsOn === fieldName) {
                                            const signatureElementId = $(this).data('element-id');
                                            loadUserSignature(userIdToLoad, signatureElementId);
                                        }
                                    });
                                }
                            }, 150); // Small delay to ensure value is set in DOM/Select2
                        }
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
        }; // End window.loadDynamicOptions
        
        console.log('[CLIENT_SELECT] loadDynamicOptions defined globally');
    }
    
    initLoadDynamicOptions();
})();
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
        if (!window.customElementsToInit) {
            return;
        }
        
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

    function initializeCustomElement(elementData) {
        const elementId = elementData.elementId;
        const elementType = elementData.elementType;
        
        // Load initial options for non-dependent elements
        if (elementType === 'client_select' || elementType === 'sample_type_select' || elementType === 'store_select' || elementType === 'standard_select' || elementType === 'sample_condition_select' || elementType === 'user_select') {
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
        
        // For client unit dependent elements (sample points)
        if (elementType === 'sample_point_select') {
            const placeholder = elementData.placeholder;
            const $element = $('#' + elementId);
            $element.html('<option value="">' + placeholder + '</option>');
            
            // Load options based on current client unit
            const clientUnitSelect = $('select[data-element-type="client_unit_select"]');
            const currentClientUnitId = clientUnitSelect.val();

            if (currentClientUnitId) {
                loadDynamicOptions($element, elementId, elementType, null, null, null, currentClientUnitId);
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
            const dependentElements = $('select[data-element-type="client_unit_select"], select[data-element-type="client_contact_select"], select[data-element-type="client_submission_officers_select"]');
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
                let clientUnitDependentElements = $(this).closest('tr').find('select[data-element-type="sample_point_select"]');

                if(clientUnitDependentElements.length === 0){
                    clientUnitDependentElements = $('select[data-element-type="sample_point_select"]');
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
            const dependentElements = $('select[data-element-type="sample_point_select"]');
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
            const sampleTypeVal = $(this).val();
            const sampleTypeId = Array.isArray(sampleTypeVal)
                ? sampleTypeVal.filter(Boolean).join(',')
                : sampleTypeVal;
            
            const $row = $(this).closest('tr');
            let dependentElements = $row.length
                ? $row.find('select[data-element-type="analysis_type_select"]')
                : $('select[data-element-type="analysis_type_select"]');

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
            const rawAnalysisTypeId = $(this).val();
            const analysisTypeId = Array.isArray(rawAnalysisTypeId) ? (rawAnalysisTypeId[0] || '') : rawAnalysisTypeId;
            
            // Find dependent elements in the same row only
            let dependentElements = $(this).closest('tr').find('select[data-element-type="analysis_elements_select"]');

            if(dependentElements.length === 0){
                dependentElements = $(this).closest('.form-group, .custom-element-wrapper, .rows-section').find('select[data-element-type="analysis_elements_select"]');
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

    // User Signature Loading Functions
    function loadUserSignature(userId, signatureElementId) {
        if (!userId) {
            // Clear signature if no user selected
            clearUserSignature(signatureElementId);
            return;
        }

        const signatureContainer = $('#' + signatureElementId + '_container');
        const signatureImage = $('#' + signatureElementId + '_image');
        const signaturePlaceholder = $('#' + signatureElementId + '_placeholder');
        const signatureInput = $('#' + signatureElementId);

        // Show loading state
        signaturePlaceholder.html('<i class="mdi mdi-loading mdi-spin" style="font-size: 2rem;"></i><br><small>Loading signature...</small>');

        $.ajax({
            url: '{{ route("submission-forms.user-signature") }}',
            method: 'GET',
            data: { user_id: userId },
            success: function(response) {
                if (response.success && response.signature_url) {
                    signatureImage.attr('src', response.signature_url).show();
                    signaturePlaceholder.hide();
                    signatureInput.val(response.signature_path);
                } else {
                    // No signature available
                    signaturePlaceholder.html('<i class="mdi mdi-account-remove" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i><small>No signature available for this user</small>').show();
                    signatureImage.hide();
                    signatureInput.val('');
                }
            },
            error: function(xhr) {
                console.error('Error loading user signature:', xhr);
                signaturePlaceholder.html('<i class="mdi mdi-alert-circle" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i><small>Error loading signature</small>').show();
                signatureImage.hide();
                signatureInput.val('');
            }
        });
    }

    function clearUserSignature(signatureElementId) {
        const signatureImage = $('#' + signatureElementId + '_image');
        const signaturePlaceholder = $('#' + signatureElementId + '_placeholder');
        const signatureInput = $('#' + signatureElementId);

        signatureImage.hide().attr('src', '');
        signaturePlaceholder.html('<i class="mdi mdi-account-check" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i><small>Signature will appear here when user is selected</small>').show();
        signatureInput.val('');
    }

    function setupUserSignatureHandlers() {
        // Set up change handlers for user_select fields that have dependent user_signature fields
        // Handle both regular change and Select2 change events
        $('select[data-element-type="user_select"]').on('change.user-signature select2:select.user-signature select2:unselect.user-signature', function() {
            const userId = $(this).val();
            const fieldName = $(this).attr('name');
            
            // Find all user_signature fields that depend on this field
            $('.user-signature-container').each(function() {
                const dependsOn = $(this).data('depends-on');
                if (dependsOn === fieldName) {
                    const signatureElementId = $(this).data('element-id');
                    loadUserSignature(userId, signatureElementId);
                }
            });
        });
    }

    // Contact Signature Loading Functions
    function loadContactSignature(contactId, signatureElementId) {
        if (!contactId) {
            // Clear signature if no contact selected
            clearContactSignature(signatureElementId);
            return;
        }

        const signatureContainer = $('#' + signatureElementId + '_container');
        const signatureImage = $('#' + signatureElementId + '_signature_image');
        const signatureDisplay = $('#' + signatureElementId + '_signature_display');
        const signaturePad = $('#' + signatureElementId + '_signature_pad');
        const signaturePlaceholder = $('#' + signatureElementId + '_placeholder');
        const signatureInput = $('#' + signatureElementId);
        
        // Store contact ID
        signatureContainer.data('contact-id', contactId);

        // Show loading state
        signaturePlaceholder.html('<i class="mdi mdi-loading mdi-spin" style="font-size: 2rem;"></i><br><small>Loading signature...</small>').show();
        signatureDisplay.hide();
        signaturePad.hide();

        $.ajax({
            url: '{{ route("submission-forms.contact-signature") }}',
            method: 'GET',
            data: { contact_id: contactId },
            success: function(response) {
                if (response.success && response.signature_url) {
                    // Contact has signature - display it
                    signatureImage.attr('src', response.signature_url).show();
                    signatureDisplay.show();
                    signaturePlaceholder.hide();
                    signaturePad.hide();
                    signatureInput.val(response.signature_path);
                } else {
                    // No signature - show signature pad
                    signaturePlaceholder.hide();
                    signatureDisplay.hide();
                    signaturePad.show();
                    initializeContactSignaturePad(signatureElementId);
                    signatureInput.val('');
                }
            },
            error: function(xhr) {
                console.error('Error loading contact signature:', xhr);
                // On error, show signature pad
                signaturePlaceholder.hide();
                signatureDisplay.hide();
                signaturePad.show();
                initializeContactSignaturePad(signatureElementId);
                signatureInput.val('');
            }
        });
    }

    function clearContactSignature(signatureElementId) {
        const signatureImage = $('#' + signatureElementId + '_signature_image');
        const signatureDisplay = $('#' + signatureElementId + '_signature_display');
        const signaturePad = $('#' + signatureElementId + '_signature_pad');
        const signaturePlaceholder = $('#' + signatureElementId + '_placeholder');
        const signatureInput = $('#' + signatureElementId);
        const signatureContainer = $('#' + signatureElementId + '_container');

        // Clear the signature image
        signatureImage.hide().attr('src', '');
        signatureDisplay.hide();
        
        // Show signature pad for new signature
        signaturePlaceholder.hide();
        signaturePad.show();
        initializeContactSignaturePad(signatureElementId);
        
        // Clear the input value
        signatureInput.val('');
        
        // Keep contact ID so we can save the new signature
        // Don't clear contact-id as we want to save to the same contact
    }

    function clearContactSignaturePad(signatureElementId) {
        const canvas = document.getElementById(signatureElementId + '_canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // Reset canvas styling
            canvas.style.borderColor = '';
            canvas.style.boxShadow = '';
        }
        const signatureInput = $('#' + signatureElementId);
        signatureInput.val('');
        
        // Show placeholder again
        const placeholder = canvas ? canvas.parentElement.querySelector('.signature-placeholder') : null;
        if (placeholder) {
            placeholder.style.opacity = '1';
        }
    }

    function initializeContactSignaturePad(signatureElementId) {
        const canvas = document.getElementById(signatureElementId + '_canvas');
        if (!canvas || canvas.dataset.initialized === 'true') return;
        
        const ctx = canvas.getContext('2d');
        const placeholder = canvas.parentElement.querySelector('.signature-placeholder');
        let isDrawing = false;
        let hasSignature = false;
        let resizeTimeout;
        let savedSignatureData = null;

        function debouncedResize() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(resizeCanvas, 100);
        }

        function resizeCanvas() {
            const container = canvas.parentElement;
            const wrapper = container.parentElement;
            const availableWidth = wrapper.clientWidth - 16;
            const containerWidth = Math.max(200, Math.min(availableWidth, 800));
            const containerHeight = 150;

            canvas.style.width = containerWidth + 'px';
            canvas.style.height = containerHeight + 'px';
            canvas.style.maxWidth = '100%';
            canvas.width = containerWidth;
            canvas.height = containerHeight;

            ctx.strokeStyle = '#2c3e50';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            if (savedSignatureData) {
                redrawSignature();
            }
        }

        function redrawSignature() {
            if (savedSignatureData) {
                const img = new Image();
                img.onload = function() {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                };
                img.src = savedSignatureData;
            }
        }

        resizeCanvas();
        window.addEventListener('resize', debouncedResize);

        if (window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(debouncedResize);
            resizeObserver.observe(canvas.parentElement.parentElement);
        }

        function getCanvasCoordinates(e) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            const coords = getCanvasCoordinates(e);
            ctx.beginPath();
            ctx.moveTo(coords.x, coords.y);
            if (placeholder) placeholder.style.opacity = '0';
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const coords = getCanvasCoordinates(e);
            ctx.lineTo(coords.x, coords.y);
            ctx.stroke();
            hasSignature = true;
            canvas.style.borderColor = '#28a745';
            canvas.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.25)';
            
            // Update hidden input with signature data
            const dataURL = canvas.toDataURL('image/png');
            $('#' + signatureElementId).val(dataURL);
            savedSignatureData = dataURL;
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                const dataURL = canvas.toDataURL('image/png');
                $('#' + signatureElementId).val(dataURL);
                savedSignatureData = dataURL;
            }
        }

        function handleTouchStart(e) {
            e.preventDefault();
            if (e.touches && e.touches.length > 0) {
                startDrawing(e.touches[0]);
            }
        }

        function handleTouchMove(e) {
            e.preventDefault();
            if (e.touches && e.touches.length > 0) {
                draw(e.touches[0]);
            }
        }

        function handleTouchEnd(e) {
            e.preventDefault();
            stopDrawing();
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        canvas.addEventListener('touchstart', handleTouchStart, { passive: false });
        canvas.addEventListener('touchmove', handleTouchMove, { passive: false });
        canvas.addEventListener('touchend', handleTouchEnd, { passive: false });

        canvas.dataset.initialized = 'true';
    }

    function setupContactSignatureHandlers() {
        // Set up change handlers for contact select fields
        $('select[data-element-type="client_contact_select"], select[data-element-type="client_submission_officers_select"]').on('change.contact-signature select2:select.contact-signature select2:unselect.contact-signature', function() {
            const contactId = $(this).val();
            const fieldName = $(this).attr('name');
            
            // Find all contact_signature fields that depend on this field
            $('.contact-signature-container').each(function() {
                const dependsOn = $(this).data('depends-on');
                if (dependsOn === fieldName) {
                    const signatureElementId = $(this).data('element-id');
                    loadContactSignature(contactId, signatureElementId);
                }
            });
        });
    }

    // Save contact signature when form is submitted (if checkbox is checked)
    $(document).on('submit', '#fill-form', function() {
        $('.contact-signature-container').each(function() {
            const signatureElementId = $(this).data('element-id');
            const saveCheckbox = $('#' + signatureElementId + '_save_signature');
            const signatureInput = $('#' + signatureElementId);
            const contactId = $(this).data('contact-id');
            
            if (saveCheckbox.is(':checked') && signatureInput.val() && contactId) {
                const signatureData = signatureInput.val();
                
                // Save signature via AJAX
                $.ajax({
                    url: '{{ route("submission-forms.save-contact-signature") }}',
                    method: 'POST',
                    data: {
                        contact_id: contactId,
                        signature: signatureData,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    async: false, // Wait for save to complete
                    success: function(response) {
                        if (response.success) {
                            console.log('Contact signature saved successfully');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error saving contact signature:', xhr);
                    }
                });
            }
        });
    });

    // loadDynamicOptions is now defined globally at the top of this script

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
                
                if (!specs.length) {
                    // Fallback: derive groups from DOM [required]
                    const groups = {};
                    this.getAllFormFields().filter('[required]').each(function() {
                        const $el = $(this);
                        const name = $el.attr('name');
                        if (!name) return;
                        groups[name] = groups[name] || [];
                        groups[name].push($el);
                    });
                    return groups;
                }
                
                const groups = {};
                const $form = $('#fill-form');
                
                specs.forEach(spec => {
                    if (!spec || !spec.name) return;
                    
                    if (spec.inRows) {
                        // For rows: find all actual row instances
                        const selector = `[name^="${spec.name}["]`;
                        const $found = $form.find(selector);
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
                        }
                    } else {
                        // For regular fields: find by exact name
                        const exactSelector = `[name="${spec.name}"]`;
                        const $found = $form.find(exactSelector);
                        if ($found.length > 0) {
                            groups[spec.name] = [];
                            $found.each(function() { groups[spec.name].push($(this)); });
                        } else {
                            // Field not in DOM yet (or dynamic) - add placeholder
                            groups[spec.name] = [];
                        }
                    }
                });
                
                return groups;
            },

            countRequiredGroups() {
                const groups = this.resolveRequiredFieldGroups();
                return Object.keys(groups).length;
            },

            countFilledRequiredGroups() {
                const groups = this.resolveRequiredFieldGroups();
                let filled = 0;
                
                Object.keys(groups).forEach(key => {
                    const elements = groups[key];
                    
                    // If no elements in group, it's not filled (placeholder or not found)
                    if (!elements || elements.length === 0) {
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
                    
                    if (isFilled) filled++;
                });
                
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
                if (totalRequired > 0) {
                    const filledRequired = this.countFilledRequiredGroups();
                    const progress = Math.round((filledRequired / totalRequired) * 100);
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
            console.log('[CLIENT_SELECT] === Checking for client_select fields ===');
            
            // Check what client_select fields exist in DOM
            const clientSelects = $('select[data-element-type="client_select"]');
            console.log('[CLIENT_SELECT] Found', clientSelects.length, 'client_select field(s) in DOM');
            clientSelects.each(function() {
                console.log('[CLIENT_SELECT] Field ID:', $(this).attr('id'), 'Name:', $(this).attr('name'));
            });
            
            // Set route URL for dynamic options
            window.dynamicOptionsRoute = "{{ auth()->check() ? route('submission-forms.dynamic-options') : route('forms.dynamic-options') }}";
            
            // Initialize form fill
            FormFill.init();
            
            // Setup user signature handlers FIRST so they're ready when values are set
            setupUserSignatureHandlers();
            
            // Setup contact signature handlers
            setupContactSignatureHandlers();
            
            // Initialize custom elements with dependency management
            initializeAllCustomElements();
            
            // Initialize user signature fields if depends field already has value
            // This runs after all elements are initialized, so we check both current value and data attributes
            setTimeout(function() {
            $('.user-signature-container').each(function() {
                const dependsOn = $(this).data('depends-on');
                if (dependsOn) {
                    // Find the depends field by name
                    const dependsField = $('select[data-element-type="user_select"][name="' + dependsOn + '"]');
                    if (dependsField.length > 0) {
                            // Check for value in the select field (after options are loaded)
                            let userId = dependsField.val();
                            
                            // If no value, check for saved value or default user ID from data attributes
                            if (!userId) {
                                userId = dependsField.attr('data-saved-value');
                            }
                            if (!userId) {
                                userId = dependsField.attr('data-default-user-id');
                            }
                            
                        if (userId) {
                            const signatureElementId = $(this).data('element-id');
                            loadUserSignature(userId, signatureElementId);
                            }
                        }
                    }
                });
            }, 1000); // Wait for all AJAX calls to complete and options to be loaded
            
            // Initialize contact signature fields if depends field already has value
            $('.contact-signature-container').each(function() {
                const dependsOn = $(this).data('depends-on');
                if (dependsOn) {
                    // Find the depends field by name (could be client_contact_select or client_submission_officers_select)
                    const dependsField = $('select[data-element-type="client_contact_select"][name="' + dependsOn + '"], select[data-element-type="client_submission_officers_select"][name="' + dependsOn + '"]');
                    if (dependsField.length > 0) {
                        const contactId = dependsField.val();
                        if (contactId) {
                            const signatureElementId = $(this).data('element-id');
                            loadContactSignature(contactId, signatureElementId);
                        }
                    }
                }
            });
            
            // FALLBACK: Initialize client_select with a delay to run AFTER any global Select2 init
            console.log('[CLIENT_SELECT] Scheduling delayed initialization...');
            setTimeout(function() {
                console.log('[CLIENT_SELECT] Running delayed initialization...');
                $('select[data-element-type="client_select"]').each(function() {
                    const $select = $(this);
                    const elementId = $select.attr('id');
                    
                    // Always force re-init to ensure AJAX config is applied
                    console.log('[CLIENT_SELECT] Force reinitializing with AJAX:', elementId);
                    
                    // Remove flag to allow re-initialization
                    $select.removeData('select2-ajax-initialized');
                    
                    // Reinitialize
                    loadDynamicOptions($select, elementId, 'client_select');
                });
                
                console.log('[CLIENT_SELECT] === Delayed initialization complete ===');
            }, 1000); // Wait 1 second for other scripts to finish
            
            console.log('[CLIENT_SELECT] === Scheduled for delayed init ===');
        });
    })(); // End initFormFill
</script>


<!-- Include SweetAlert2 for better modals -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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
@endsection

