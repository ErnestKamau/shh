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
        <h2>
          <i class="mdi mdi-cog"></i> Form Builder
          <small class="text-muted">{{ $submissionForm->name }}</small>
        </h2>
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
                  <div class="element-type" data-type="signature">
                    <i class="mdi mdi-draw"></i> Signature
                  </div>
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
          <form id="section-form">
            <input type="hidden" id="section-id" name="section_id">
            <div class="form-group">
              <label for="section-title" class="required">Section Title</label>
              <input type="text" class="form-control" id="section-title" name="title" required maxlength="255">
            </div>
            <div class="form-group">
              <label for="section-description">Description</label>
              <textarea class="form-control" id="section-description" name="description" rows="3" maxlength="1000"></textarea>
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
                    <option value="select">Dropdown</option>
                    <option value="radio">Radio Buttons</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="file">File Upload</option>
                    <option value="signature">Signature</option>
                    <option value="client_select">Client Select</option>
                    <option value="sample_type_select">Sample Type Select</option>
                    <option value="client_unit_select">Client Unit Select</option>
                    <option value="client_contact_select">Client Contact Select</option>
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
    formId: {{ $submissionForm->id }},
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
        // Make sections sortable
        if (document.getElementById('sections-container')) {
            new Sortable(document.getElementById('sections-container'), {
                handle: '.section-header .mdi-drag-horizontal',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => this.reorderSections()
            });
        }
        
        // Make element holders sortable within each section
        $('.sortable-holders').each(function() {
            new Sortable(this, {
                handle: '.holder-header .mdi-drag-horizontal',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => {
                    const sectionId = $(evt.to).data('section-id');
                    FormBuilder.reorderHolders(sectionId);
                }
            });
        });
        
        // Make elements sortable within each holder
        $('.sortable-elements').each(function() {
            new Sortable(this, {
                handle: '.element-item .mdi-drag-horizontal',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => {
                    const holderId = $(evt.to).data('holder-id');
                    FormBuilder.reorderElements(holderId);
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
            $('#section-modal .modal-title').text('Edit Section');
        } else {
            // Add new section
            $('#section-form')[0].reset();
            $('#section-id').val('');
            $('#section-modal .modal-title').text('Add Section');
        }
        
        console.log('Attempting to show modal...');
        modal.modal('show');
    },
    
    saveSection() {
        console.log('saveSection called');
        
        const sectionId = $('#section-id').val();
        const isEdit = sectionId !== '';
        
        const data = {
            title: $('#section-title').val(),
            description: $('#section-description').val()
        };
        
        console.log('Form data:', data);
        
        const url = isEdit 
            ? `/submission-forms/sections/${sectionId}`
            : `/submission-forms/${this.formId}/sections`;
        
        const method = isEdit ? 'PUT' : 'POST';
        
        console.log('Making AJAX request to:', url, 'with method:', method);
        
        this.showLoading();
        
        $.ajax({
            url: url,
            method: 'POST', // Always use POST for Laravel
            data: {
                ...data,
                _method: method // Laravel method spoofing
            },
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                console.log('Success response:', response);
                this.hideLoading();
                $('#section-modal').modal('hide');
                this.showMessage('success', response.message);
                // Don't load form structure on init - it's already rendered by the server
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
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
                // Don't load form structure on init - it's already rendered by the server
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
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
                // Don't load form structure on init - it's already rendered by the server
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
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            success: (response) => {
                this.hideLoading();
                this.showMessage('success', response.message);
                // Don't load form structure on init - it's already rendered by the server
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
            // Edit existing holder
            const holder = this.findHolderById(holderId);
            $('#holder-id').val(holderId);
            $('#holder-type').val(holder.holder_type);
            $('#holder-max-elements').val(holder.max_elements);
            $('.modal-title').text('Edit Element Holder');
        } else {
            // Add new holder
            $('#holder-form')[0].reset();
            $('#holder-id').val('');
            $('#holder-section-id').val(sectionId);
            $('.modal-title').text('Add Element Holder');
        }
        $('#holder-modal').modal('show');
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
        
        this.showLoading();
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-HTTP-Method-Override': method
            },
            success: (response) => {
                this.hideLoading();
                $('#holder-modal').modal('hide');
                this.showMessage('success', response.message);
                // Don't load form structure on init - it's already rendered by the server
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
        
        // Handle options for select/radio/checkbox
        if (element.options && element.options.length > 0) {
            $('#element-options-container').empty();
            element.options.forEach(option => {
                this.addOption(option.value, option.label);
            });
        }
    },
    
    handleElementTypeChange() {
        const elementType = $('#element-type').val();
        const needsOptions = ['select', 'radio', 'checkbox'].includes(elementType);
        const isCustomElement = ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select'].includes(elementType);
        
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
        
        const method = isEdit ? 'PUT' : 'POST';
        
        this.showLoading();
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-HTTP-Method-Override': method
            },
            success: (response) => {
                this.hideLoading();
                $('#element-modal').modal('hide');
                this.showMessage('success', response.message);
                // Don't load form structure on init - it's already rendered by the server
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
            is_readonly: $('#element-readonly').is(':checked') ? '1' : '0'
        };
        
        // Collect options if needed
        const needsOptions = ['select', 'radio', 'checkbox'].includes(formData.element_type);
        const isCustomElement = ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select'].includes(formData.element_type);
        
        // Only collect options for standard elements, not custom elements
        if (needsOptions && !isCustomElement) {
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
            return { title: '', description: '' };
        }
        
        const title = sectionElement.find('.section-header h6').text().trim();
        const description = sectionElement.find('.section-header small').text().trim();
        
        return { 
            title: title.replace(/^.*?\s/, ''), // Remove icon and get just the title
            description: description 
        };
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
        
        const label = elementItem.find('.font-weight-medium').text().trim();
        const isRequired = elementItem.find('.text-danger').length > 0;
        const isReadonly = elementItem.find('.badge-outline-warning').length > 0;
        const nameAndType = elementItem.find('small.text-muted').text().trim();
        
        // Extract name and type from "name (type)" format
        const matches = nameAndType.match(/^(.+?)\s*\((.+?)\)$/);
        const name = matches ? matches[1] : '';
        const elementType = matches ? matches[2] : 'text';
        
        return { 
            id: elementId,
            element_type: elementType,
            name: name,
            label: label.replace(' *', ''), // Remove required asterisk
            placeholder: '',
            default_value: '',
            help_text: '',
            is_required: isRequired,
            is_readonly: isReadonly,
            options: []
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
        $(`.sortable-elements[data-holder-id="${holderId}"] .element-item`).each(function() {
            elementIds.push($(this).data('element-id'));
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
        
        // Make element holders sortable within each section
        $('.sortable-holders').each(function() {
            // Destroy existing sortable if it exists
            if (this.sortable) {
                this.sortable.destroy();
            }
            
            new Sortable(this, {
                handle: '.holder-header .mdi-drag-horizontal',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => {
                    const sectionId = $(evt.to).data('section-id');
                    FormBuilder.reorderHolders(sectionId);
                }
            });
        });
        
        // Make elements sortable within each holder
        $('.sortable-elements').each(function() {
            // Destroy existing sortable if it exists
            if (this.sortable) {
                this.sortable.destroy();
            }
            
            new Sortable(this, {
                handle: '.element-item .mdi-drag-horizontal',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => {
                    const holderId = $(evt.to).data('holder-id');
                    FormBuilder.reorderElements(holderId);
                }
            });
        });
    }
};

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
}

.element-item:last-child {
    border-bottom: none;
}

.element-item:hover {
    background-color: #f8f9fa;
}
</style>
@endsection