@extends('layouts.lab.layout.app', ['select2'=>true])

@php
if (!function_exists('getElementIcon')) {
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
            case 'signature':
                return 'draw';
            case 'client_select':
                return 'account-group';
            case 'sample_type_select':
                return 'test-tube';
            case 'client_unit_select':
                return 'office-building';
            case 'client_contact_select':
                return 'account-multiple';
            case 'analysis_type_select':
                return 'flask';
            case 'store_select':
                return 'store';
            case 'store_slot_select':
                return 'view-grid';
            case 'sample_condition_select':
                return 'thermometer';
            case 'standard_select':
                return 'certificate';
            case 'sample_point_select':
                return 'map-marker';
            case 'user_select':
                return 'account';
            case 'calculation':
                return 'calculator';
            default:
                return 'form-textbox';
        }
    }
}
@endphp

@section('title2')
  <title>Create Form Instance - {{ $submissionForm->name }}</title>
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
          'name' => 'Create Instance',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <div>
        <h2>
          <i class="mdi mdi-file-document-plus"></i> Create New Form Instance
          <small class="text-muted">{{ $submissionForm->name }}</small>
        </h2>
        <div class="alert alert-info mt-2 mb-0">
          <i class="mdi mdi-information-outline"></i>
          <strong>Create Instance:</strong> Fill out the details below to create a new form instance for submission.
        </div>
      </div>
      <div>
        <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Form
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
                  <small class="text-muted">Version: <strong>{{ $submissionForm->version }}</strong></small>
                </div>
              </div>
            </div>
            <div class="card-body">

                    <!-- Instance Creation Form -->
                    <form id="create-instance-form" method="POST" action="{{ route('submission-forms.instances.store', $submissionForm) }}" novalidate>
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-label required">Instance Title</label>
                                    <input type="text" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           id="title" 
                                           name="title" 
                                           value="{{ old('title') }}" 
                                           placeholder="Enter a title for this form instance..."
                                           required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        This will help you identify this specific submission later.
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-control @error('priority') is-invalid @enderror" 
                                            id="priority" 
                                            name="priority">
                                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" 
                                           class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" 
                                           name="due_date" 
                                           value="{{ old('due_date') }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Optional due date for this submission.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Form Information -->
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Form Details</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li><strong>Version:</strong> {{ $submissionForm->version }}</li>
                                                <li><strong>Sections:</strong> {{ $submissionForm->getSectionCount() }}</li>
                                                <li><strong>Elements:</strong> {{ $submissionForm->getElementCount() }}</li>
                                                <li><strong>Naming Convention:</strong> {{ $submissionForm->naming_convention_format }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">What happens next?</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li><i class="mdi mdi-check text-success"></i> Instance will be created with draft status</li>
                                                <li><i class="mdi mdi-check text-success"></i> You'll be redirected to fill out the form</li>
                                                <li><i class="mdi mdi-check text-success"></i> You can save as draft or submit when ready</li>
                                                <li><i class="mdi mdi-check text-success"></i> Form will be processed according to workflow</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left"></i> Cancel
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-primary" id="create-instance-btn">
                                        <i class="mdi mdi-file-document-plus"></i> Create Instance & Start Filling
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
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
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection

@section('script2')
<script>
$(document).ready(function() {
    console.log('Create instance page loaded, initializing...');
    
    // Set default due date to 7 days from now
    if (!$('#due_date').val()) {
        const today = new Date();
        const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
        $('#due_date').val(nextWeek.toISOString().split('T')[0]);
    }
    
    // Form creation functionality
    const FormCreate = {
        init() {
            this.bindEvents();
            this.updateFormDataPreview();
        },
        
        bindEvents() {
            // Update form data preview on input change
            $('#create-instance-form').on('input change', 'input, select, textarea', () => {
                this.updateFormDataPreview();
            });
            
            // Form submission
            $('#create-instance-form').on('submit', (e) => {
                e.preventDefault();
                this.validateAndSubmit();
            });
        },
        
        updateFormDataPreview() {
            const formData = this.getFormData();
            $('#form-data-preview code').text(JSON.stringify(formData, null, 2));
        },
        
        getFormData() {
            const data = {};
            
            $('#create-instance-form').find('input, select, textarea').each(function() {
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
                } else {
                    value = $element.val();
                }
                
                data[name] = value;
            });
            
            return data;
        },
        
        validateForm() {
            const errors = [];
            
            $('#create-instance-form').find('input[required], select[required], textarea[required]').each(function() {
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
            $('#create-instance-form').find('.is-invalid').removeClass('is-invalid');
        },
        
        submitForm() {
            // Disable submit button
            $('#create-instance-btn').prop('disabled', true);
            
            // Show loading state
            $('#create-instance-btn').html('<i class="mdi mdi-loading mdi-spin"></i> Creating Instance...');
            
            // Submit the form
            $('#create-instance-form')[0].submit();
        }
    };
    
    // Initialize form creation
    FormCreate.init();
});
</script>

<!-- Include SweetAlert2 for better modals -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
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

#form-data-preview {
    font-size: 0.875em;
    max-height: 300px;
    overflow-y: auto;
}

.form-actions {
    background-color: #f8f9fa;
    margin: 0 -1.25rem -1.25rem -1.25rem;
    padding: 1.25rem;
    border-radius: 0 0 0.375rem 0.375rem;
}
</style>
@endsection
