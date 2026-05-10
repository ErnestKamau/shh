@extends('layouts.lab.layout.app')

@section('title2')
  <title>Form Builder - {{ $submissionForm->name }}</title>
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
          'link' => route('submission-forms.show', $submissionForm),
          'name' => $submissionForm->name,
          'icon' => null
        ),
        array(
          'link' => '#',
          'name' => 'Form Builder',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <div>
        <h3>
          <i class="mdi mdi-cog"></i> Form Builder
          <small class="text-muted">{{ $submissionForm->name }}</small>
        </h3>
      </div>
      <div>
        <button class="btn btn-success" id="save-form">
          <i class="mdi mdi-content-save"></i> Save Changes
        </button>
        <a href="{{ route('submission-forms.preview', $submissionForm) }}" class="btn btn-outline-info" target="_blank">
          <i class="mdi mdi-eye-outline"></i> Preview
        </a>
        <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Form
        </a>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row">
        <!-- Form Builder Sidebar -->
        <div class="col-md-3">
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-plus"></i> Add Components
              </h6>
            </div>
            <div class="card-body">
              <button class="btn btn-primary btn-block mb-3" id="add-section-btn">
                <i class="mdi mdi-folder-plus"></i> Add Section
              </button>
              
              <div class="mb-3">
                <h6 class="text-muted">Element Types</h6>
                <p class="small text-muted mb-2">
                  <i class="mdi mdi-information-outline"></i> 
                  First select an element holder (click on it), then click an element type to add it.
                </p>
                <div class="element-types">
                  <div class="element-type" data-type="text">
                    <i class="mdi mdi-form-textbox"></i> Text Input
                  </div>
                  <div class="element-type" data-type="number">
                    <i class="mdi mdi-numeric"></i> Number
                  </div>
                  <div class="element-type" data-type="email">
                    <i class="mdi mdi-email-outline"></i> Email
                  </div>
                  <div class="element-type" data-type="date">
                    <i class="mdi mdi-calendar"></i> Date
                  </div>
                  <div class="element-type" data-type="datetime">
                    <i class="mdi mdi-calendar-clock"></i> Date & Time
                  </div>
                  <div class="element-type" data-type="textarea">
                    <i class="mdi mdi-text-box-outline"></i> Text Area
                  </div>
                  <div class="element-type" data-type="plain_text">
                    <i class="mdi mdi-text"></i> Plain Text
                  </div>
                  <div class="element-type" data-type="select">
                    <i class="mdi mdi-form-dropdown"></i> Dropdown
                  </div>
                  <div class="element-type" data-type="radio">
                    <i class="mdi mdi-radiobox-marked"></i> Radio Buttons
                  </div>
                  <div class="element-type" data-type="checkbox">
                    <i class="mdi mdi-checkbox-marked"></i> Checkbox
                  </div>
                  <div class="element-type" data-type="file">
                    <i class="mdi mdi-file-upload-outline"></i> File Upload
                  </div>
                  <div class="element-type" data-type="camera_photo">
                    <i class="mdi mdi-camera"></i> Camera Photo
                  </div>
                  <div class="element-type" data-type="signature">
                    <i class="mdi mdi-draw"></i> Signature
                  </div>
                  <div class="element-type" data-type="contact_signature">
                    <i class="mdi mdi-account-check"></i> Contact Signature
                  </div>
                  <h5 style="font-size:18px; padding: 5px; margin:0px">Custom Fields</h5>
                  <div class="element-type" data-type="client_select">
                    <i class="mdi mdi-account-group"></i> Client Select
                  </div>
                  <div class="element-type" data-type="sample_type_select">
                    <i class="mdi mdi-test-tube"></i> Sample Type Select
                  </div>
                  <div class="element-type" data-type="client_unit_select">
                    <i class="mdi mdi-office-building"></i> Client Unit Select
                  </div>
                  <div class="element-type" data-type="client_contact_select">
                    <i class="mdi mdi-account-multiple"></i> Client Contact Select
                  </div>
                  <div class="element-type" data-type="client_submission_officers_select">
                    <i class="mdi mdi-account-check"></i> Client Submission Officers
                  </div>
                  <div class="element-type" data-type="analysis_type_select">
                    <i class="mdi mdi-flask"></i> Analysis Type Select
                  </div>
                  <div class="element-type" data-type="analysis_elements_select">
                    <i class="mdi mdi-flask-empty-outline"></i> Analysis Elements Select
                  </div>
                  <div class="element-type" data-type="store_select">
                    <i class="mdi mdi-store"></i> Store Select
                  </div>
                  <div class="element-type" data-type="store_slot_select">
                    <i class="mdi mdi-package-variant"></i> Store Slot Select
                  </div>
                  <div class="element-type" data-type="sample_condition_select">
                    <i class="mdi mdi-flask-empty"></i> Sample Condition Select
                  </div>
                  <div class="element-type" data-type="standard_select">
                    <i class="mdi mdi-certificate"></i> Standard Select
                  </div>
                  <div class="element-type" data-type="sample_point_select">
                    <i class="mdi mdi-map-marker"></i> Sample Point Select
                  </div>
                  <div class="element-type" data-type="user_select">
                    <i class="mdi mdi-account"></i> User Select
                  </div>
                  <div class="element-type" data-type="user_signature">
                    <i class="mdi mdi-account-check"></i> User Signature
                  </div>
                  <div class="element-type" data-type="depended_field">
                    <i class="mdi mdi-link-variant"></i> Depended Field
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card mt-3">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-information-outline"></i> Builder Help
              </h6>
            </div>
            <div class="card-body">
              <div class="small">
                <p><strong>Sections:</strong> Organize your form into logical groups</p>
                <p><strong>Element Holders:</strong> Containers that hold form elements</p>
                <p><strong>Elements:</strong> Individual form fields</p>
                <hr>
                <p><strong>Tips:</strong></p>
                <ul class="mb-0">
                  <li>Drag elements to reorder them</li>
                  <li>Click on items to edit properties</li>
                  <li>Use preview to test your form</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Builder Canvas -->
        <div class="col-md-9">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0">
                <i class="mdi mdi-file-tree"></i> Form Structure
              </h6>
              <div>
                <button class="btn btn-sm btn-outline-secondary" id="collapse-all">
                  <i class="mdi mdi-collapse-all"></i> Collapse All
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="expand-all">
                  <i class="mdi mdi-expand-all"></i> Expand All
                </button>
              </div>
            </div>
            <div class="card-body">
              <div id="form-builder-canvas">
                <div id="sections-container" class="sortable-sections">
                  @foreach($submissionForm->sections as $section)
                    @include('submission-forms.partials.section-builder', ['section' => $section])
                  @endforeach
                </div>
                
                @if($submissionForm->sections->count() === 0)
                  <div id="empty-form-message" class="text-center py-5">
                    <i class="mdi mdi-file-tree" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="text-muted mt-3">Start Building Your Form</h5>
                    <p class="text-muted">Click "Add Section" to create your first section</p>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Section Modal -->
  <div class="modal fade" id="section-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Section Details</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="section-form" enctype="multipart/form-data">
            <input type="hidden" id="section-id" name="section_id">
            <div class="form-group">
              <label for="section-title" class="required">Section Title</label>
              <input type="text" class="form-control" id="section-title" name="title" required maxlength="255">
            </div>
            <div class="form-group">
              <label for="section-type" class="required">Section Type</label>
              <select class="form-control" id="section-type" name="section_type" required>
                <option value="regular">Static Section</option>
                <option value="rows_section">Rows Section (Dynamic Table)</option>
              </select>
              <small class="form-text text-muted">
                Rows sections allow users to add multiple rows of data in a table format.
              </small>
            </div>
            <div class="form-group">
              <label for="section-description">Description</label>
              <textarea class="form-control" id="section-description" name="description" rows="3" maxlength="1000"></textarea>
            </div>
            <div class="form-group">
              <label for="section-alignment" class="required">Section Position</label>
              <select class="form-control" id="section-alignment" name="section_alignment" required>
                <option value="left">Left</option>
                <option value="middle">Middle</option>
                <option value="right">Right</option>
              </select>
            </div>
            <div class="form-group">
              <label for="section-logos">Section Logos</label>
              <input type="file" class="form-control-file" id="section-logos" name="section_logos[]" accept="image/*" multiple>
              <small class="form-text text-muted">
                Upload one or more logos to display in this section. Use Ctrl/Cmd to select multiple files.
              </small>
              <div id="new-section-logos" class="mt-2"></div>
              <div id="existing-section-logos" class="mt-2"></div>
              <div id="existing-section-logos-inputs"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="save-section">Save Section</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Element Holder Modal -->
  <div class="modal fade" id="holder-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Element Holder Details</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="holder-form">
            <input type="hidden" id="holder-id" name="holder_id">
            <input type="hidden" id="holder-section-id" name="section_id">
            <div class="form-group">
              <label for="holder-type" class="required">Holder Type</label>
              <select class="form-control" id="holder-type" name="holder_type" required>
                <option value="field">Field Holder (for form inputs)</option>
                <option value="text">Text Holder (for static content)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="holder-max-elements" class="required">Maximum Elements</label>
              <input type="number" class="form-control" id="holder-max-elements" name="max_elements" required min="1" max="20" value="5">
              <small class="form-text text-muted">Maximum number of elements this holder can contain</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="save-holder">Save Holder</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Element Modal -->
  <div class="modal fade" id="element-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Form Element Details</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="element-form">
            <input type="hidden" id="element-id" name="element_id">
            <input type="hidden" id="element-holder-id" name="holder_id">
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="element-type" class="required">Element Type</label>
                  <select class="form-control" id="element-type" name="element_type" required>
                    <option value="text">Text Input</option>
                    <option value="number">Number</option>
                    <option value="email">Email</option>
                    <option value="date">Date</option>
                    <option value="datetime">Date & Time</option>
                    <option value="textarea">Text Area</option>
                    <option value="plain_text">Plain Text</option>
                    <option value="select">Dropdown</option>
                    <option value="radio">Radio Buttons</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="file">File Upload</option>
                    <option value="camera_photo">Camera Photo</option>
                    <option value="signature">Signature</option>
                    <option value="contact_signature">Contact Signature</option>
                    <option value="client_select">Client Select</option>
                    <option value="sample_type_select">Sample Type Select</option>
                    <option value="client_unit_select">Client Unit Select</option>
                    <option value="client_contact_select">Client Contact Select</option>
                    <option value="analysis_type_select">Analysis Type Select</option>
                    <option value="analysis_elements_select">Analysis Elements Select</option>
                    <option value="store_select">Store Select</option>
                    <option value="store_slot_select">Store Slot Select</option>
                    <option value="sample_condition_select">Sample Condition Select</option>
                    <option value="standard_select">Standard Select</option>
                    <option value="sample_point_select">Sample Point Select</option>
                    <option value="company_sub_unit_select">Company Sub Unit Select</option>
                    <option value="user_select">User Select</option>
                    <option value="user_signature">User Signature</option>
                    <option value="depended_field">Depended Field</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="element-name" class="required">Field Name</label>
                  <input type="text" class="form-control" id="element-name" name="name" required maxlength="255" pattern="[a-zA-Z][a-zA-Z0-9_]*">
                  <small class="form-text text-muted">Must start with a letter, can contain letters, numbers, and underscores</small>
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <label for="element-label" class="required">Label</label>
              <input type="text" class="form-control" id="element-label" name="label" required maxlength="255">
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="element-placeholder">Placeholder</label>
                  <input type="text" class="form-control" id="element-placeholder" name="placeholder" maxlength="255">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="element-default-value">Default Value</label>
                  <input type="text" class="form-control" id="element-default-value" name="default_value">
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <label for="element-help-text">Help Text</label>
              <textarea class="form-control" id="element-help-text" name="help_text" rows="2" maxlength="1000"></textarea>
            </div>
            
            <!-- Field Mapping Configuration -->
            <div class="card mt-3">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="mdi mdi-database"></i> Field Mapping Configuration
                  <small class="text-muted">(Optional)</small>
                </h6>
              </div>
              <div class="card-body">
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="element-mapped" name="is_mapped">
                  <label class="form-check-label" for="element-mapped">
                    Map this field to a database table
                  </label>
                </div>
                
                <div id="mapping-config" style="display: none;">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="mapping-table">Target Table</label>
                        <select class="form-control" id="mapping-table" name="mapping_table">
                          <option value="">Select a table...</option>
                          <option value="sample_headers">Sample Headers</option>
                          <option value="sample_details">Sample Details</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="mapping-field">Target Field</label>
                        <select class="form-control" id="mapping-field" name="mapping_field">
                          <option value="">Select a field...</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="alert alert-info">
                    <i class="mdi mdi-information"></i>
                    <strong>Note:</strong> When this form is submitted, the value of this field will be automatically mapped to the selected database field.
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Depends On Configuration (for user_signature and contact_signature) -->
            <div class="card mt-3" id="depends-config" style="display: none;">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="mdi mdi-link"></i> Dependency Configuration
                  <small class="text-muted">(Required for User/Contact Signature)</small>
                </h6>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <label for="element-depends" class="required">Depends On Field</label>
                  <select class="form-control" id="element-depends" name="depends">
                    <option value="">Select a field...</option>
                    <!-- Options will be dynamically populated -->
                  </select>
                  <small class="form-text text-muted" id="depends-help-text">
                    Select which field this signature should depend on. The signature will automatically load when that field is filled.
                  </small>
                </div>
              </div>
            </div>

            <!-- Depended Field Configuration -->
            <div class="card mt-3" id="depended-config" style="display: none;">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="mdi mdi-link-variant"></i> Depended Field Configuration
                </h6>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <label for="depended-depends-on-field" class="required">Depends On Field</label>
                  <select class="form-control" id="depended-depends-on-field">
                    <option value="">Select a select-type field...</option>
                  </select>
                  <small class="form-text text-muted">Select which "select" field this element should depend on. The value will automatically load when that field changes.</small>
                </div>
                <div class="form-group">
                  <label for="depended-source-table">Source Table</label>
                  <input type="text" class="form-control" id="depended-source-table" readonly placeholder="Auto-resolved from the selected field">
                </div>
                <div class="form-group mb-0">
                  <label for="depended-source-field" class="required">Source Field (Column)</label>
                  <select class="form-control" id="depended-source-field">
                    <option value="">Select a field above first...</option>
                  </select>
                  <small class="form-text text-muted">The column whose value will populate this element when the dependent select changes.</small>
                </div>
              </div>
            </div>
            
            <div class="form-group" id="element-options-group" style="display: none;">
              <label>Options</label>
              <div id="element-options-container">
                <!-- Options will be dynamically added here -->
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" id="add-option">
                <i class="mdi mdi-plus"></i> Add Option
              </button>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="element-required" name="is_required">
                  <label class="form-check-label" for="element-required">Required Field</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="element-readonly" name="is_readonly">
                  <label class="form-check-label" for="element-readonly">Read Only</label>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="save-element">Save Element</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="loading-overlay" style="display: none;">
    <div class="loading-spinner">
      <i class="mdi mdi-loading mdi-spin"></i>
      <p>Saving changes...</p>
    </div>
  </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Form Builder JavaScript
const FormBuilder = {
  formId: @json($submissionForm->id),
    csrfToken: '{{ csrf_token() }}',
    
    init() {
        console.log('FormBuilder.init() called');
        this.bindEvents();
        this.initSortable();
        // Don't load form structure on init - it's already rendered by the server
    },
    
    bindEvents() {
        console.log('Binding events...');
        
        // Check if button exists
        const addSectionBtn = $('#add-section-btn');
        console.log('Add section button found:', addSectionBtn.length > 0);
        
        // Add section button
        addSectionBtn.on('click', (e) => {
            e.preventDefault();
            console.log('Add section button clicked');
            this.showSectionModal();
        });
        
        // Save section
        $('#save-section').on('click', (e) => {
            e.preventDefault();
            console.log('Save section button clicked');
            this.saveSection();
        });
        
        // Save holder
        $('#save-holder').on('click', () => this.saveHolder());
        
        // Save element
        $('#save-element').on('click', () => this.saveElement());
        
        // Element type change
        $('#element-type').on('change', () => this.handleElementTypeChange());
        
        // Add option button
        $('#add-option').on('click', () => this.addOption());
        
        // Collapse/Expand all
        $('#collapse-all').on('click', () => this.collapseAll());
        $('#expand-all').on('click', () => this.expandAll());
        
        // Element name validation
        $('#element-name').on('blur', () => this.validateElementName());
        
        // Save form button
        $('#save-form').on('click', (e) => {
            e.preventDefault();
            this.saveFormChanges();
        });

        // Section logos selection
        $('#section-logos').on('change', (e) => {
          this.renderNewSectionLogos(e.target.files || []);
        });
        
        // Holder selection
        $(document).on('click', '.holder-item', function(e) {
            // Don't trigger if clicking on buttons
            if ($(e.target).closest('.btn, .btn-group').length > 0) {
                return;
            }
            
            // Remove active class from all holders
            $('.holder-item').removeClass('active');
            
            // Add active class to clicked holder
            $(this).addClass('active');
            
            // Visual feedback
            $('.holder-item').removeClass('border-primary').addClass('border-light');
            $(this).removeClass('border-light').addClass('border-primary');
        });
    },
    
    initSortable() {
        // Make sections sortable with enhanced configuration
        if (document.getElementById('sections-container')) {
            new Sortable(document.getElementById('sections-container'), {
                handle: '.section-header .mdi-drag-horizontal',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                forceFallback: true,
                fallbackClass: 'sortable-fallback',
                onStart: (evt) => this.onDragStart(evt, 'section'),
                onEnd: (evt) => this.onDragEnd(evt, 'section'),
                onMove: (evt) => this.onDragMove(evt, 'section'),
                onUpdate: (evt) => this.reorderSections()
            });
        }
        
        // Make element holders sortable with cross-container support
        $('.sortable-holders').each(function() {
            new Sortable(this, {
                group: 'holders', // Allow cross-container dragging
                handle: '.holder-header .mdi-drag-horizontal',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                forceFallback: true,
                fallbackClass: 'sortable-fallback',
                onStart: (evt) => FormBuilder.onDragStart(evt, 'holder'),
                onEnd: (evt) => FormBuilder.onDragEnd(evt, 'holder'),
                onMove: (evt) => FormBuilder.onDragMove(evt, 'holder'),
                onAdd: (evt) => FormBuilder.onHolderMoved(evt),
                onUpdate: (evt) => FormBuilder.onHolderReordered(evt)
            });
        });
        
        // Make elements sortable with cross-container support
        $('.sortable-elements').each(function() {
            console.log('Initializing SortableJS for elements container:', this);
            console.log('Container has', $(this).find('.element-item').length, 'elements');
            
            new Sortable(this, {
                group: 'elements', // Allow cross-container dragging
                handle: '.element-item .mdi-drag-horizontal',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                forceFallback: true,
                fallbackClass: 'sortable-fallback',
                onStart: (evt) => {
                    console.log('Element drag started:', evt);
                    FormBuilder.onDragStart(evt, 'element');
                },
                onEnd: (evt) => {
                    console.log('Element drag ended:', evt);
                    FormBuilder.onDragEnd(evt, 'element');
                },
                onMove: (evt) => {
                    console.log('Element drag move:', evt);
                    FormBuilder.onDragMove(evt, 'element');
                },
                onAdd: (evt) => {
                    console.log('Element added to new container:', evt);
                    FormBuilder.onElementMoved(evt);
                },
                onUpdate: (evt) => {
                    console.log('Element reordered within container:', evt);
                    FormBuilder.onElementReordered(evt);
                }
            });
        });
    },
    
    showSectionModal(sectionId = null) {
        console.log('showSectionModal called with sectionId:', sectionId);
        
        // Check if modal exists
        const modal = $('#section-modal');
        console.log('Modal found:', modal.length > 0);
        
        if (sectionId) {
            // Edit existing section
            const section = this.findSectionById(sectionId);
            $('#section-id').val(sectionId);
            $('#section-title').val(section.title);
            $('#section-description').val(section.description);
            $('#section-type').val(section.section_type || 'regular');
          $('#section-alignment').val(section.section_alignment || 'left');
          this.renderSectionLogoPreview(section.section_logos || []);
          this.renderNewSectionLogos([]);
            $('#section-modal .modal-title').text('Edit Section');
        } else {
            // Add new section
            $('#section-form')[0].reset();
            $('#section-id').val('');
            $('#section-type').val('regular');
          $('#section-alignment').val('left');
          this.renderSectionLogoPreview([]);
          this.renderNewSectionLogos([]);
            $('#section-modal .modal-title').text('Add Section');
        }
        
        console.log('Attempting to show modal...');
        modal.modal('show');
    },
    
    saveSection() {
        console.log('saveSection called');
        
        const sectionId = $('#section-id').val();
        const isEdit = sectionId !== '';
        
        const formData = new FormData(document.getElementById('section-form'));
        formData.set('title', $('#section-title').val());
        formData.set('description', $('#section-description').val());
        formData.set('section_type', $('#section-type').val());
        formData.set('section_alignment', $('#section-alignment').val() || 'left');

        console.log('Form data prepared for section save');
        
        const url = isEdit 
            ? `/submission-forms/sections/${sectionId}`
            : `/submission-forms/${this.formId}/sections`;
        
        const method = isEdit ? 'PUT' : 'POST';
        
        console.log('Making AJAX request to:', url, 'with method:', method);
        
        this.showLoading();
        
        $.ajax({
            url: url,
            method: 'POST', // Always use POST for Laravel
          data: formData,
          processData: false,
          contentType: false,
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
          beforeSend: () => {
            formData.set('_method', isEdit ? 'PUT' : 'POST');
          },
            success: (response) => {
                console.log('Success response:', response);
                this.hideLoading();
                $('#section-modal').modal('hide');
                this.showMessage('success', response.message);
                // Reload the page to show updates
                location.reload();
            },
            error: (xhr) => {
                console.error('Error response:', xhr);
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },
    
    deleteSection(sectionId) {
      if (!confirm('Are you sure you want to delete this section? This will also delete all element holders and elements within it.')) {
            return;
        }
        
        this.showLoading();
        
        $.ajax({
          url: `/submission-forms/sections/${sectionId}`,
          method: 'POST',
          data: {
            _method: 'DELETE'
          },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
            location.reload();
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },
    
    deleteHolder(holderId) {
      if (!confirm('Are you sure you want to delete this element holder? This will also delete all elements within it.')) {
            return;
        }
        
        this.showLoading();
        
        $.ajax({
          url: `/submission-forms/holders/${holderId}`,
          method: 'POST',
          data: {
            _method: 'DELETE'
          },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
            location.reload();
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },
    
    deleteElement(elementId) {
        if (!confirm('Are you sure you want to delete this form element?')) {
            return;
        }
        
        this.showLoading();
        
        $.ajax({
          url: `/submission-forms/elements/${elementId}`,
          method: 'POST',
          data: {
            _method: 'DELETE'
          },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
            location.reload();
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },
    
    showHolderModal(sectionId, holderId = null) {
        $('#holder-section-id').val(sectionId);
        
        if (holderId) {
            // Edit existing holder - fetch data from server
            this.showLoading();
            $.ajax({
                url: `/submission-forms/holders/${holderId}`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken
                },
                success: (response) => {
                    this.hideLoading();
                    if (response.success) {
                        const holder = response.holder;
                        $('#holder-id').val(holderId);
                        $('#holder-type').val(holder.holder_type);
                        $('#holder-max-elements').val(holder.max_elements);
                        $('.modal-title').text('Edit Element Holder');
                        $('#holder-modal').modal('show');
                    } else {
                        this.showMessage('error', 'Failed to load holder data');
                    }
                },
                error: (xhr) => {
                    this.hideLoading();
                    this.handleError(xhr);
                }
            });
        } else {
            // Add new holder
            $('#holder-form')[0].reset();
            $('#holder-id').val('');
            $('#holder-section-id').val(sectionId);
            $('.modal-title').text('Add Element Holder');
            $('#holder-modal').modal('show');
        }
    },
    
    saveHolder() {
        const formData = new FormData($('#holder-form')[0]);
        const holderId = $('#holder-id').val();
        const sectionId = $('#holder-section-id').val();
        const isEdit = holderId !== '';
        
        const url = isEdit 
            ? `/submission-forms/holders/${holderId}`
            : `/submission-forms/sections/${sectionId}/holders`;
        
        const method = isEdit ? 'PUT' : 'POST';
        
        // Add method spoofing for Laravel
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        this.showLoading();
        
        $.ajax({
            url: url,
            method: 'POST', // Always use POST for Laravel
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                $('#holder-modal').modal('hide');
                this.showMessage('success', response.message);
                // Reload the page to show updates
                location.reload();
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },
    
    showElementModal(holderId, elementId = null, elementType = 'text') {
        $('#element-holder-id').val(holderId);
        
        if (elementId) {
            // Edit existing element
            const element = this.findElementById(elementId);
            this.populateElementForm(element);
            $('.modal-title').text('Edit Form Element');
        } else {
            // Add new element
            $('#element-form')[0].reset();
            $('#element-id').val('');
            $('#element-holder-id').val(holderId);
            $('#element-type').val(elementType);
            $('.modal-title').text('Add Form Element');
        }
        
        this.handleElementTypeChange();
        $('#element-modal').modal('show');
    },
    
    populateElementForm(element) {
        $('#element-id').val(element.id);
        $('#element-type').val(element.element_type);
        $('#element-name').val(element.name);
        $('#element-label').val(element.label);
        $('#element-placeholder').val(element.placeholder);
        $('#element-default-value').val(element.default_value);
        $('#element-help-text').val(element.help_text);
        $('#element-required').prop('checked', element.is_required);
        $('#element-readonly').prop('checked', element.is_readonly);
        
        // Handle mapping configuration
        const isMapped = element.is_mapped === true || element.is_mapped === 1;
        const mappingTable = element.mapping_table || '';
        const mappingField = element.mapping_field || '';
        
        console.log('Populating mapping config:', {
            is_mapped: isMapped,
            mapping_table: mappingTable,
            mapping_field: mappingField
        });
        
        $('#element-mapped').prop('checked', isMapped);
        $('#mapping-table').val(mappingTable);
        this.toggleMappingConfig();
        
        // Load mapping fields if table is selected and preserve the selected field
        if (mappingTable) {
            console.log('Loading mapping fields for table:', mappingTable, 'with field:', mappingField);
            this.loadMappingFields(mappingTable, mappingField);
        } else {
            $('#mapping-field').val('').html('<option value="">Select a field...</option>');
        }
        
        // Handle options for select/radio/checkbox
        if (element.options && element.options.length > 0) {
            $('#element-options-container').empty();
            element.options.forEach(option => {
                this.addOption(option.value, option.label);
            });
        }
        
        // Handle depends for user_signature
        if (element.element_type === 'user_signature' && element.options && element.options.depends) {
            $('#element-depends').val(element.options.depends);
            this.loadUserSelectFields();
        }
        
        // Handle depends for contact_signature
        if (element.element_type === 'contact_signature' && element.options && element.options.depends) {
            $('#element-depends').val(element.options.depends);
            this.loadContactSelectFields();
        }

        // Handle depended_field configuration
        if (element.element_type === 'depended_field') {
            this.loadSelectTypeFields(element.depends_on_field);
            if (element.depends_on_type) {
                this.loadSourceFields(element.depends_on_type, element.source_field);
                $('#depended-source-table').val(element.source_table || '');
            }
        }
    },
    
    loadUserSelectFields() {
        const dependsSelect = $('#element-depends');
        const currentValue = dependsSelect.val();
        const userSelectFields = [];
        
        // Find all user_select elements in the form builder DOM
        $('#sections-container .element-item').each(function() {
            const elementTypeText = $(this).find('small.text-muted').text().trim();
            // Extract type from format like "name (type)"
            const typeMatch = elementTypeText.match(/\((.+?)\)$/);
            if (typeMatch && typeMatch[1] === 'user_select') {
                const label = $(this).find('.font-weight-medium').text().trim().replace(' *', '');
                // Extract name from the text before the parenthesis
                const nameMatch = elementTypeText.match(/^(.+?)\s*\(/);
                if (nameMatch) {
                    const name = nameMatch[1].trim();
                    userSelectFields.push({ name, label });
                }
            }
        });
        
        // Populate dropdown
        dependsSelect.html('<option value="">Select a user field...</option>');
        userSelectFields.forEach(field => {
            const selected = (currentValue === field.name) ? ' selected' : '';
            dependsSelect.append(`<option value="${field.name}"${selected}>${field.label} (${field.name})</option>`);
        });
    },
    
    loadContactSelectFields() {
        const dependsSelect = $('#element-depends');
        const currentValue = dependsSelect.val();
        const contactSelectFields = [];
        
        // Find all client_contact_select and client_submission_officers_select elements in the form builder DOM
        $('#sections-container .element-item').each(function() {
            const elementTypeText = $(this).find('small.text-muted').text().trim();
            // Extract type from format like "name (type)"
            const typeMatch = elementTypeText.match(/\((.+?)\)$/);
            if (typeMatch && (typeMatch[1] === 'client_contact_select' || typeMatch[1] === 'client_submission_officers_select')) {
                const label = $(this).find('.font-weight-medium').text().trim().replace(' *', '');
                // Extract name from the text before the parenthesis
                const nameMatch = elementTypeText.match(/^(.+?)\s*\(/);
                if (nameMatch) {
                    const name = nameMatch[1].trim();
                    contactSelectFields.push({ name, label, type: typeMatch[1] });
                }
            }
        });
        
        // Populate dropdown
        dependsSelect.html('<option value="">Select a contact field...</option>');
        contactSelectFields.forEach(field => {
            const selected = (currentValue === field.name) ? ' selected' : '';
            dependsSelect.append(`<option value="${field.name}"${selected}>${field.label} (${field.name})</option>`);
        });
    },

    loadSelectTypeFields(currentFieldName = '') {
        const SELECT_TYPES = ['client_select', 'client_unit_select', 'client_contact_select',
            'client_submission_officers_select', 'sample_type_select', 'analysis_type_select',
            'analysis_elements_select', 'store_select', 'store_slot_select', 'sample_condition_select',
            'standard_select', 'sample_point_select', 'user_select'];

        const dependsSelect = $('#depended-depends-on-field');
        const foundFields = [];

        $('#sections-container .element-item').each(function() {
            const elementTypeText = $(this).find('small.text-muted').text().trim();
            const typeMatch = elementTypeText.match(/\((.+?)\)$/);
            if (typeMatch && SELECT_TYPES.includes(typeMatch[1])) {
                const label = $(this).find('.font-weight-medium').text().trim().replace(' *', '');
                const nameMatch = elementTypeText.match(/^(.+?)\s*\(/);
                if (nameMatch) {
                    foundFields.push({ name: nameMatch[1].trim(), label, type: typeMatch[1] });
                }
            }
        });

        dependsSelect.html('<option value="">Select a select-type field...</option>');
        foundFields.forEach(field => {
            const selected = (currentFieldName === field.name) ? ' selected' : '';
            dependsSelect.append(`<option value="${field.name}" data-element-type="${field.type}"${selected}>${field.label} (${field.name})</option>`);
        });

        // When a field is chosen, auto-fill source_table and source_field options
        dependsSelect.off('change.depended-config').on('change.depended-config', () => {
            const selectedType = dependsSelect.find('option:selected').data('element-type');
            this.loadSourceFields(selectedType);
        });
    },

    loadSourceFields(elementType, currentSourceField = '') {
        const SOURCE_FIELD_MAP = {
            client_select: {
                table: 'crm_customers',
                fields: [
                    { value: 'name', label: 'Company Name' },
                    { value: 'code', label: 'Code' },
                    { value: 'email', label: 'Email' },
                    { value: 'telephone1', label: 'Telephone 1' },
                    { value: 'telephone2', label: 'Telephone 2' },
                    { value: 'fax', label: 'Fax' },
                    { value: 'postal_address', label: 'Postal Address' },
                    { value: 'physical_address', label: 'Physical Address' },
                    { value: 'website', label: 'Website' },
                ]
            },
            client_unit_select: {
                table: 'crm_company_units',
                fields: [{ value: 'name', label: 'Name' }]
            },
            client_contact_select: {
                table: 'crm_customer_contacts',
                fields: [
                    { value: 'first_name', label: 'First Name' },
                    { value: 'middle_name', label: 'Middle Name' },
                    { value: 'last_name', label: 'Last Name' },
                    { value: 'email', label: 'Email' },
                    { value: 'telephone', label: 'Telephone' },
                    { value: 'mobile', label: 'Mobile' },
                    { value: 'job_occupation', label: 'Job Occupation' },
                ]
            },
            client_submission_officers_select: {
                table: 'crm_customer_contacts',
                fields: [
                    { value: 'first_name', label: 'First Name' },
                    { value: 'middle_name', label: 'Middle Name' },
                    { value: 'last_name', label: 'Last Name' },
                    { value: 'email', label: 'Email' },
                    { value: 'telephone', label: 'Telephone' },
                    { value: 'mobile', label: 'Mobile' },
                ]
            },
            sample_type_select: {
                table: 'sample_types',
                fields: [{ value: 'name', label: 'Name' }]
            },
            analysis_type_select: {
                table: 'analysis_types',
                fields: [{ value: 'name', label: 'Name' }]
            },
            store_select: {
                table: 'inventory_stores',
                fields: [{ value: 'name', label: 'Name' }]
            },
            store_slot_select: {
                table: 'inventory_store_slots',
                fields: [{ value: 'name', label: 'Name' }]
            },
            user_select: {
                table: 'users',
                fields: [
                    { value: 'name', label: 'Name' },
                    { value: 'email', label: 'Email' },
                ]
            },
            standard_select: {
                table: 'standards',
                fields: [{ value: 'name', label: 'Name' }]
            },
            sample_condition_select: {
                table: 'sample_conditions',
                fields: [{ value: 'name', label: 'Name' }]
            },
            sample_point_select: {
                table: 'sample_points',
                fields: [{ value: 'name', label: 'Name' }]
            },
        };

        const config = SOURCE_FIELD_MAP[elementType];
        if (!config) {
            $('#depended-source-table').val('');
            $('#depended-source-field').html('<option value="">Select a field above first...</option>');
            return;
        }

        $('#depended-source-table').val(config.table);
        const sourceFieldSelect = $('#depended-source-field');
        sourceFieldSelect.html('<option value="">Select a column...</option>');
        config.fields.forEach(f => {
            const selected = (currentSourceField === f.value) ? ' selected' : '';
            sourceFieldSelect.append(`<option value="${f.value}"${selected}>${f.label} (${f.value})</option>`);
        });
    },

    handleElementTypeChange() {
        const elementType = $('#element-type').val();
        const needsOptions = ['select', 'radio', 'checkbox'].includes(elementType);
        const isCustomElement = ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select', 'client_submission_officers_select', 'analysis_type_select', 'analysis_elements_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select', 'sample_point_select', 'user_select', 'user_signature', 'contact_signature', 'depended_field'].includes(elementType);
        
        // Show/hide depends configuration for user_signature and contact_signature
        if (elementType === 'user_signature') {
            $('#depends-config').show();
            $('#depended-config').hide();
            $('#depends-help-text').text('Select which user_select field this signature should depend on. The signature will automatically load when that field is filled.');
            this.loadUserSelectFields();
        } else if (elementType === 'contact_signature') {
            $('#depends-config').show();
            $('#depended-config').hide();
            $('#depends-help-text').text('Select which client_contact_select or client_submission_officers_select field this signature should depend on. The signature will automatically load when that field is filled.');
            this.loadContactSelectFields();
        } else if (elementType === 'depended_field') {
            $('#depends-config').hide();
            $('#depended-config').show();
            this.loadSelectTypeFields($('#depended-depends-on-field').val()|| '');
        } else {
            $('#depends-config').hide();
            $('#depended-config').hide();
        }
        
        // Hide options for custom elements as they are loaded dynamically
        if (isCustomElement) {
            $('#element-options-group').hide();
        } else if (needsOptions) {
            $('#element-options-group').show();
            if ($('#element-options-container').children().length === 0) {
                this.addOption();
            }
        } else {
            $('#element-options-group').hide();
        }
    },
    
    addOption(value = '', label = '') {
        const optionHtml = `
            <div class="option-item mb-2">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm option-value" placeholder="Value" value="${value}">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm option-label" placeholder="Label" value="${label}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-option">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#element-options-container').append(optionHtml);
        
        // Bind remove option event
        $('.remove-option').off('click').on('click', function() {
            $(this).closest('.option-item').remove();
        });
    },
    
    saveElement() {
        const formData = this.getElementFormData();
        const elementId = $('#element-id').val();
        const holderId = $('#element-holder-id').val();
        const isEdit = elementId !== '';
        
        console.log('Saving element with data:', formData);
        
        const url = isEdit 
            ? `/submission-forms/elements/${elementId}`
            : `/submission-forms/holders/${holderId}/elements`;
        
        // Add method spoofing for Laravel
        if (isEdit) {
            formData._method = 'PUT';
        }
        
        this.showLoading();
        
        $.ajax({
            url: url,
            method: 'POST', // Always use POST for Laravel
            data: formData,
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                $('#element-modal').modal('hide');
                this.showMessage('success', response.message);
                // Reload the page to show updates
                location.reload();
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },
    
    getElementFormData() {
        const formData = {
            element_type: $('#element-type').val(),
            name: $('#element-name').val(),
            label: $('#element-label').val(),
            placeholder: $('#element-placeholder').val(),
            default_value: $('#element-default-value').val(),
            help_text: $('#element-help-text').val(),
            is_required: $('#element-required').is(':checked') ? '1' : '0',
            is_readonly: $('#element-readonly').is(':checked') ? '1' : '0',
            is_mapped: $('#element-mapped').is(':checked') ? '1' : '0',
            mapping_table: $('#mapping-table').val() || null,
            mapping_field: $('#mapping-field').val() || null
        };
        
        console.log('Form data being sent:', formData);
        console.log('Mapping fields:', {
            is_mapped: $('#element-mapped').is(':checked'),
            mapping_table: $('#mapping-table').val(),
            mapping_field: $('#mapping-field').val()
        });
        
        // Collect options if needed
        const needsOptions = ['select', 'radio', 'checkbox'].includes(formData.element_type);
        const isCustomElement = ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select', 'client_submission_officers_select', 'analysis_type_select', 'analysis_elements_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select', 'sample_point_select', 'user_select', 'user_signature', 'depended_field'].includes(formData.element_type);
        
        // Handle user_signature and contact_signature - save depends in options
        if (formData.element_type === 'user_signature' || formData.element_type === 'contact_signature') {
            const depends = $('#element-depends').val();
            formData.options = depends ? { depends: depends } : {};
        } else if (formData.element_type === 'depended_field') {
            // Save depended_field-specific config as top-level fields
            const selectedOption = $('#depended-depends-on-field option:selected');
            formData.depends_on_type  = selectedOption.data('element-type') || null;
            formData.depends_on_field = $('#depended-depends-on-field').val() || null;
            formData.source_table     = $('#depended-source-table').val() || null;
            formData.source_field     = $('#depended-source-field').val() || null;
            formData.options = [];
        } else if (needsOptions && !isCustomElement) {
        // Only collect options for standard elements, not custom elements
            const options = [];
            $('#element-options-container .option-item').each(function() {
                const value = $(this).find('.option-value').val();
                const label = $(this).find('.option-label').val();
                if (value && label) {
                    options.push({ value, label });
                }
            });
            formData.options = options;
        } else if (isCustomElement) {
            // Custom elements don't need manually configured options
            formData.options = [];
        }
        
        return formData;
    },
    
    validateElementName() {
        const name = $('#element-name').val();
        const elementId = $('#element-id').val();
        
        if (!name) return;
        
        $.ajax({
            url: `/submission-forms/${this.formId}/validate-element-name`,
            method: 'GET',
            data: { name, exclude_id: elementId },
            success: (response) => {
                if (!response.available) {
                    $('#element-name').addClass('is-invalid');
                    $('#element-name').after('<div class="invalid-feedback">Element name already exists in this form</div>');
                } else {
                    $('#element-name').removeClass('is-invalid');
                    $('#element-name').siblings('.invalid-feedback').remove();
                }
            }
        });
    },
    
    loadFormStructure() {
        $.ajax({
            url: `/submission-forms/${this.formId}/structure`,
            method: 'GET',
            success: (response) => {
                this.renderFormStructure(response.form);
                this.hideEmptyMessage();
            },
            error: (xhr) => {
                this.handleError(xhr);
            }
        });
    },
    
    renderFormStructure(form) {
        // This would render the form structure
        // For now, we'll reload the page to show updates
        location.reload();
    },
    
    hideEmptyMessage() {
        $('#empty-form-message').hide();
    },
    
    collapseAll() {
        $('.section-content').collapse('hide');
    },
    
    expandAll() {
        $('.section-content').collapse('show');
    },
    
    showLoading() {
        $('#loading-overlay').show();
    },
    
    hideLoading() {
        $('#loading-overlay').hide();
    },
    
    showMessage(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert to the top of the main content
        $('main').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    },
    handleError(xhr) {
        console.error('AJAX Error:', xhr);
        let message = 'An error occurred';
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            message = errors.join(', ');
        } else if (xhr.responseText) {
            message = xhr.responseText;
        }
        
        this.showMessage('error', message);
    },
    
    findSectionById(sectionId) {
        // Get section data from the DOM
        const sectionElement = $(`.section-item[data-section-id="${sectionId}"]`);
        if (sectionElement.length === 0) {
        return { title: '', description: '', section_type: 'regular', section_alignment: 'left', section_logos: [] };
        }

      let logos = [];
      const logosJson = sectionElement.attr('data-section-logos') || '[]';
      try {
        logos = JSON.parse(logosJson);
      } catch (e) {
        logos = [];
      }

        return { 
        title: sectionElement.attr('data-section-title') || '',
        description: sectionElement.attr('data-section-description') || '',
        section_type: sectionElement.attr('data-section-type') || 'regular',
        section_alignment: sectionElement.attr('data-section-alignment') || 'left',
        section_logos: logos
        };
    },

    renderSectionLogoPreview(logos) {
      const preview = $('#existing-section-logos');
      const inputs = $('#existing-section-logos-inputs');

      preview.empty();
      inputs.empty();

      if (!Array.isArray(logos) || logos.length === 0) {
        preview.append('<small class="text-muted">No existing logos.</small>');
        return;
      }

      const container = $('<div class="d-flex flex-wrap"></div>');

      logos.forEach((path, index) => {
        const logoPath = typeof path === 'string' ? path : (path.path || '');
        const logoPosition = typeof path === 'object' && path ? (path.position || 'left') : 'left';

        if (!logoPath) {
          return;
        }

        const item = $(
          `<div class="border rounded p-2 mr-2 mb-2 text-center" data-logo-index="${index}">
            <img src="/storage/${logoPath}" alt="Section logo" style="width: 56px; height: 56px; object-fit: contain; display: block; margin: 0 auto 6px auto;">
            <select class="form-control form-control-sm mb-2 existing-logo-position" data-logo-index="${index}">
              <option value="left" ${logoPosition === 'left' ? 'selected' : ''}>Left</option>
              <option value="middle" ${logoPosition === 'middle' ? 'selected' : ''}>Middle</option>
              <option value="right" ${logoPosition === 'right' ? 'selected' : ''}>Right</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger remove-section-logo" data-logo-index="${index}">
              Remove
            </button>
          </div>`
        );
        container.append(item);
        inputs.append(`<input type="hidden" name="existing_section_logos[]" value="${logoPath}" data-logo-index="${index}">`);
        inputs.append(`<input type="hidden" name="existing_section_logo_positions[]" value="${logoPosition}" data-logo-index="${index}" data-position-input="1">`);
      });

      preview.append(container);

      preview.find('.remove-section-logo').off('click').on('click', function() {
        const logoIndex = $(this).attr('data-logo-index');
        preview.find(`[data-logo-index="${logoIndex}"]`).remove();
        inputs.find(`input[data-logo-index="${logoIndex}"]`).remove();

        if (preview.find('.remove-section-logo').length === 0) {
          preview.html('<small class="text-muted">No existing logos.</small>');
        }
      });

      preview.find('.existing-logo-position').off('change').on('change', function() {
        const logoIndex = $(this).attr('data-logo-index');
        const newPosition = $(this).val();
        inputs.find(`input[data-logo-index="${logoIndex}"][data-position-input="1"]`).val(newPosition);
      });
    },

    renderNewSectionLogos(files) {
      const container = $('#new-section-logos');
      container.empty();

      if (!files || files.length === 0) {
        return;
      }

      const wrapper = $('<div class="border rounded p-2"></div>');
      wrapper.append('<small class="text-muted d-block mb-2">New logo positions</small>');

      Array.from(files).forEach((file, index) => {
        const row = $(
          `<div class="d-flex align-items-center mb-2">
            <span class="small text-truncate mr-2" style="max-width: 220px;">${file.name}</span>
            <select class="form-control form-control-sm" name="section_logo_positions[]" style="max-width: 130px;">
              <option value="left">Left</option>
              <option value="middle">Middle</option>
              <option value="right">Right</option>
            </select>
          </div>`
        );
        wrapper.append(row);
      });

      container.append(wrapper);
    },
    
    findHolderById(holderId) {
        // Get holder data from the DOM
        const holderElement = $(`.holder-item[data-holder-id="${holderId}"]`);
        if (holderElement.length === 0) {
            return { holder_type: 'field', max_elements: 5 };
        }
        
        const holderText = holderElement.find('.holder-header small').text();
        const holderType = holderText.toLowerCase().includes('field') ? 'field' : 'text';
        
        // Extract max elements from badge (format: "current/max")
        const badge = holderElement.find('.badge-light').text();
        const maxElements = badge.includes('/') ? parseInt(badge.split('/')[1]) : 5;
        
        return { 
            holder_type: holderType,
            max_elements: maxElements 
        };
    },
    
    findElementById(elementId) {
        // Get element data from the DOM
        const elementItem = $(`.element-item[data-element-id="${elementId}"]`);
        if (elementItem.length === 0) {
            return { 
                id: elementId,
                element_type: 'text',
                name: '',
                label: '',
                placeholder: '',
                default_value: '',
                help_text: '',
                is_required: false,
                is_readonly: false,
                options: []
            };
        }
        
        const label = elementItem.data('element-label') || elementItem.find('.font-weight-medium').text().trim();
        const isRequired = elementItem.find('.text-danger').length > 0;
        const isReadonly = elementItem.find('.badge-outline-warning').length > 0;
        const nameAndType = elementItem.find('small.text-muted').text().trim();
        const storedOptions = elementItem.data('options');
        const normalizedOptions = Array.isArray(storedOptions)
          ? storedOptions
          : (storedOptions && typeof storedOptions === 'object' ? Object.values(storedOptions) : []);
        
        // Extract name and type from "name (type)" format
        const matches = nameAndType.match(/^(.+?)\s*\((.+?)\)$/);
        const name = elementItem.data('element-name') || (matches ? matches[1] : '');
        const elementType = elementItem.data('element-type') || (matches ? matches[2] : 'text');
        
        // Extract mapping information from the DOM
        const mappingInfo = elementItem.find('small.text-info');
        let isMapped = false;
        let mappingTable = '';
        let mappingField = '';
        
        if (mappingInfo.length > 0) {
            isMapped = true;
            const mappingText = mappingInfo.text().trim();
            // Extract from "Mapped to: Sample Headers → Batch Code" format
            const mappingMatch = mappingText.match(/Mapped to:\s*([^→]+)→\s*(.+)/);
            if (mappingMatch) {
                mappingTable = mappingMatch[1].trim().toLowerCase().replace(/\s+/g, '_');
                mappingField = mappingMatch[2].trim().toLowerCase().replace(/\s+/g, '_');
            }
        }
        
        return { 
            id: elementId,
            element_type: elementType,
            name: name,
          label: label,
          placeholder: elementItem.data('placeholder') || '',
          default_value: elementItem.data('default-value') || '',
          help_text: elementItem.data('help-text') || '',
            is_required: isRequired,
            is_readonly: isReadonly,
            is_mapped: isMapped,
            mapping_table: mappingTable,
            mapping_field: mappingField,
          options: normalizedOptions,
            depends_on_type:  elementItem.data('depends-on-type')  || null,
            depends_on_field: elementItem.data('depends-on-field') || null,
            source_table:     elementItem.data('source-table')     || null,
            source_field:     elementItem.data('source-field')     || null,
        };
    },

    reorderSections() {
        // Get the current order of sections
        const sectionIds = [];
        $('#sections-container .section-item').each(function() {
            sectionIds.push($(this).data('section-id'));
        });
        
        console.log('Reordering sections:', sectionIds);
        
        $.ajax({
            url: `/submission-forms/${this.formId}/sections/reorder`,
            method: 'POST',
            data: {
                section_ids: sectionIds
            },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                console.log('Sections reordered successfully');
            },
            error: (xhr) => {
                console.error('Failed to reorder sections:', xhr);
                this.handleError(xhr);
            }
        });
    },
    
    reorderHolders(sectionId) {
        // Get the current order of holders within the section
        const holderIds = [];
        $(`.sortable-holders[data-section-id="${sectionId}"] .holder-item`).each(function() {
            holderIds.push($(this).data('holder-id'));
        });
        
        console.log('Reordering holders for section', sectionId, ':', holderIds);
        
        $.ajax({
            url: `/submission-forms/sections/${sectionId}/holders/reorder`,
            method: 'POST',
            data: {
                holder_ids: holderIds
            },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                console.log('Holders reordered successfully');
            },
            error: (xhr) => {
                console.error('Failed to reorder holders:', xhr);
                this.handleError(xhr);
            }
        });
    },
    
    reorderElements(holderId) {
        // Get the current order of elements within the holder
        const elementIds = [];
        const container = $(`.sortable-elements[data-holder-id="${holderId}"]`);
        console.log('Found container for holder', holderId, ':', container.length);
        
        container.find('.element-item').each(function() {
            const elementId = $(this).data('element-id');
            elementIds.push(elementId);
            console.log('Found element with ID:', elementId);
        });
        
        console.log('Reordering elements for holder', holderId, ':', elementIds);
        
        $.ajax({
            url: `/submission-forms/holders/${holderId}/elements/reorder`,
            method: 'POST',
            data: {
                element_ids: elementIds
            },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                console.log('Elements reordered successfully');
            },
            error: (xhr) => {
                console.error('Failed to reorder elements:', xhr);
                this.handleError(xhr);
            }
        });
    },
    
    reinitializeSortables() {
        // Reinitialize sortables for dynamically added content
        // Note: The main initSortable() function handles all sortable initialization
        // This function is kept for compatibility but doesn't override the enhanced configuration
        console.log('reinitializeSortables called - using enhanced configuration from initSortable()');
    },
    
    toggleMappingConfig() {
        const isMapped = $('#element-mapped').is(':checked');
        if (isMapped) {
            $('#mapping-config').show();
        } else {
            $('#mapping-config').hide();
            $('#mapping-table').val('');
            $('#mapping-field').val('').html('<option value="">Select a field...</option>');
        }
    },
    
    loadMappingFields(table, selectedField = null) {
        console.log('loadMappingFields called with:', { table, selectedField });
        
        if (!table) {
            $('#mapping-field').html('<option value="">Select a field...</option>');
            return;
        }
        
        $.ajax({
            url: '/submission-forms/mapping-fields',
            method: 'GET',
            data: { table: table },
            success: (response) => {
                console.log('Mapping fields response:', response);
                if (response.success) {
                    let options = '<option value="">Select a field...</option>';
                    Object.entries(response.fields).forEach(([value, label]) => {
                        const selected = (selectedField && value === selectedField) ? ' selected' : '';
                        options += `<option value="${value}"${selected}>${label}</option>`;
                    });
                    $('#mapping-field').html(options);
                    
                    // Ensure the field is selected after loading
                    if (selectedField) {
                        console.log('Setting selected field to:', selectedField);
                        $('#mapping-field').val(selectedField);
                        console.log('Field value after setting:', $('#mapping-field').val());
                    }
                }
            },
            error: (xhr) => {
                console.error('Error loading mapping fields:', xhr);
                this.showMessage('error', 'Failed to load mapping fields');
            }
        });
    },

    // Clone functionality
    cloneSection(sectionId) {
        if (!confirm('Are you sure you want to clone this section? This will create a copy with all its element holders and elements.')) {
            return;
        }

        this.showLoading();
        
        $.ajax({
            url: `/submission-forms/sections/${sectionId}/clone`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
                // Reload the page to show the cloned section
                location.reload();
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },

    cloneElementHolder(holderId) {
        if (!confirm('Are you sure you want to clone this element holder? This will create a copy with all its elements.')) {
            return;
        }

        this.showLoading();
        
        $.ajax({
            url: `/submission-forms/holders/${holderId}/clone`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
                // Reload the page to show the cloned holder
                location.reload();
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },

    cloneElement(elementId) {
        if (!confirm('Are you sure you want to clone this form element?')) {
            return;
        }

        this.showLoading();
        
        $.ajax({
            url: `/submission-forms/elements/${elementId}/clone`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
                // Reload the page to show the cloned element
                location.reload();
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
            }
        });
    },

    // Enhanced drag and drop functionality
    onDragStart(evt, type) {
        console.log(`Drag started for ${type}:`, evt.item);
        $(evt.item).addClass('dragging');
        
        // Add visual feedback
        if (type === 'holder') {
            this.showDropZones('holders');
        } else if (type === 'element') {
            this.showDropZones('elements');
        }
    },

    onDragEnd(evt, type) {
        console.log(`Drag ended for ${type}:`, evt.item);
        $(evt.item).removeClass('dragging');
        
        // Remove visual feedback
        this.hideDropZones();
    },

    onDragMove(evt, type) {
        // Validate if the move is allowed
        if (type === 'element') {
            return this.validateElementMove(evt);
        } else if (type === 'holder') {
            return this.validateHolderMove(evt);
        }
        return true;
    },

    onHolderMoved(evt) {
        const holderId = $(evt.item).data('holder-id');
        const targetSectionId = $(evt.to).data('section-id');
        const position = evt.newIndex + 1;
        
        console.log(`Holder ${holderId} moved to section ${targetSectionId} at position ${position}`);
        
        this.showLoading();
        
        $.ajax({
            url: `/submission-forms/holders/${holderId}/move`,
            method: 'POST',
            data: {
                target_section_id: targetSectionId,
                position: position
            },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
                // Revert the move on error
                this.revertMove(evt);
            }
        });
    },

    onElementMoved(evt) {
        const elementId = $(evt.item).data('element-id');
        const targetHolderId = $(evt.to).data('holder-id');
        const position = evt.newIndex + 1;
        
        console.log(`Element ${elementId} moved to holder ${targetHolderId} at position ${position}`);
        
        this.showLoading();
        
        $.ajax({
            url: `/submission-forms/elements/${elementId}/move`,
            method: 'POST',
            data: {
                target_holder_id: targetHolderId,
                position: position
            },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
            },
            error: (xhr) => {
                this.hideLoading();
                this.handleError(xhr);
                // Revert the move on error
                this.revertMove(evt);
            }
        });
    },

    validateElementMove(evt) {
        const targetHolder = $(evt.to);
        const targetHolderId = targetHolder.data('holder-id');
        
        // Check if target holder has capacity
        const currentCount = targetHolder.find('.element-item').length;
        const maxElements = targetHolder.data('max-elements') || 5;
        
        if (currentCount >= maxElements) {
            this.showMessage('error', `Target holder is at maximum capacity (${maxElements} elements)`);
            return false;
        }
        
        return true;
    },

    validateHolderMove(evt) {
        // For now, allow all holder moves
        // Could add validation here if needed
        return true;
    },

    showDropZones(type) {
        if (type === 'holders') {
            $('.sortable-holders').addClass('drop-zone-active');
        } else if (type === 'elements') {
            $('.sortable-elements').addClass('drop-zone-active');
        }
    },

    hideDropZones() {
        $('.sortable-holders, .sortable-elements').removeClass('drop-zone-active');
    },

    revertMove(evt) {
        // Revert the DOM change
        if (evt.from !== evt.to) {
            evt.from.appendChild(evt.item);
        }
    },

    // Save form changes
    saveFormChanges() {
        this.showMessage('info', 'All changes are automatically saved when you make them. No additional save is needed.');
    },

    // Handle element reordering within the same holder
    onElementReordered(evt) {
        const holderId = $(evt.to).data('holder-id');
        console.log(`Elements reordered within holder ${holderId}`, evt);
        console.log('Element moved from index', evt.oldIndex, 'to index', evt.newIndex);
        this.reorderElements(holderId);
    },

    // Handle holder reordering within the same section
    onHolderReordered(evt) {
        const sectionId = $(evt.to).data('section-id');
        console.log(`Holders reordered within section ${sectionId}`);
        this.reorderHolders(sectionId);
    }
};

// Expose for inline onclick handlers rendered in Blade partials.
window.FormBuilder = FormBuilder;

// Initialize form builder when document is ready
$(document).ready(() => {
    console.log('Document ready, initializing FormBuilder');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Bootstrap modal available:', typeof $.fn.modal);
    FormBuilder.init();
});

// Element type click handlers
$(document).on('click', '.element-type', function() {
    const elementType = $(this).data('type');
    // This would trigger adding an element of the specified type
    // For now, we'll show the element modal
    const holderId = $('.holder-item.active').data('holder-id') || null;
    if (holderId) {
        FormBuilder.showElementModal(holderId, null, elementType);
    } else {
        alert('Please select an element holder first by clicking on it. Element holders are the containers within sections where you can add form elements.');
    }
});

// Mapping configuration event handlers
$(document).on('change', '#element-mapped', function() {
    FormBuilder.toggleMappingConfig();
});

$(document).on('change', '#mapping-table', function() {
    const table = $(this).val();
    const currentField = $('#mapping-field').val();
    FormBuilder.loadMappingFields(table, currentField);
});
</script>

<style>
.element-types {
    display: grid;
    gap: 8px;
}

.element-type {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.9em;
}

.element-type:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.element-type i {
    margin-right: 8px;
    color: #6c757d;
}

.holder-item {
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent !important;
}

.holder-item:hover {
    background-color: #f8f9fa;
}

.holder-item.active {
    border-color: #007bff !important;
    background-color: #f0f8ff;
}

.holder-item.active .holder-header {
    background-color: rgba(0, 123, 255, 0.1);
}
}

.sortable-ghost {
    opacity: 0.5;
}

.sortable-chosen {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.sortable-drag {
    opacity: 0.8;
    transform: rotate(2deg);
}

.sortable-fallback {
    display: block !important;
    background: #fff;
    border: 2px dashed #007bff;
    border-radius: 4px;
    padding: 10px;
    margin: 5px 0;
}

.dragging {
    opacity: 0.7;
    transform: scale(1.02);
    z-index: 1000;
}

.drop-zone-active {
    border: 2px dashed #28a745 !important;
    background-color: rgba(40, 167, 69, 0.1) !important;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.drop-zone-active::before {
    content: "Drop here";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #28a745;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    z-index: 1001;
    pointer-events: none;
}

.sortable-holders.drop-zone-active,
.sortable-elements.drop-zone-active {
    min-height: 60px;
    position: relative;
}

.required::after {
    content: " *";
    color: red;
}

#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-spinner {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.loading-spinner i {
    font-size: 2rem;
    color: #007bff;
}

.section-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 16px;
    background: white;
}

.section-header {
    padding: 12px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
    border-radius: 8px 8px 0 0;
}

.holder-item {
    border: 1px solid #e9ecef;
    border-radius: 4px;
    margin: 8px 0;
    background: #fafafa;
}

.holder-header {
    padding: 8px 12px;
    background: #f1f3f4;
    border-bottom: 1px solid #e9ecef;
}

.element-item {
    padding: 6px 12px;
    border-bottom: 1px solid #f0f0f0;
    min-width: 145px;
}

.element-item:last-child {
    border-bottom: none;
}

.element-item:hover {
    background-color: #f8f9fa;
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
</style>
@endsection