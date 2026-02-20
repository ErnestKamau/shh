@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
  <title>Create Submission Form</title>
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
          'name' => 'Create Form',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <h2>
        <i class="mdi mdi-form-select"></i> Create Submission Form
      </h2>
      <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-secondary">
        <i class="mdi mdi-arrow-left"></i> Back to Forms
      </a>
    </div>

    <div class="bg-light p-4">
      <div class="row">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Form Details</h5>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('submission-forms.store') }}">
                @csrf
                
                <div class="form-group">
                  <label for="name" class="required">Form Name</label>
                  <input type="text" 
                         class="form-control @error('name') is-invalid @enderror" 
                         id="name" 
                         name="name" 
                         value="{{ old('name') }}" 
                         required 
                         maxlength="255"
                         placeholder="Enter a descriptive name for your form">
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 mt-3 mb-1 px-0">
                  <h6 class="text-primary font-weight-bold small text-uppercase">
                    <i class="mdi mdi-certificate mr-1"></i> Form Quality Control Metadata
                  </h6>
                  <p class="text-muted small mb-3">To get started, please provide the standard identification details for this form. These include the Document Control Number, Revision Number, and the official Issue Date which explain and identify this form.</p>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="document_code" class="required">Document Control Number</label>
                      <input type="text" 
                             class="form-control @error('document_code') is-invalid @enderror" 
                             id="document_code" 
                             name="document_code" 
                             value="{{ old('document_code') }}" 
                             required
                             maxlength="50"
                             placeholder="e.g. FM/QA/047">
                      @error('document_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="version" class="required">Revision Number</label>
                      <input type="text" 
                             class="form-control @error('version') is-invalid @enderror" 
                             id="version" 
                             name="version" 
                             value="{{ old('version') }}" 
                             required 
                             maxlength="50"
                             placeholder="e.g. 01">
                      @error('version')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="issue_date" class="required">Issue Date</label>
                      <input type="date" 
                             class="form-control @error('issue_date') is-invalid @enderror" 
                             id="issue_date" 
                             name="issue_date" 
                             value="{{ old('issue_date') }}"
                             required>
                      @error('issue_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3" 
                            maxlength="1000"
                            placeholder="Provide a brief description of what this form is used for">{{ old('description') }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">
                    Optional. This helps users understand the purpose of the form.
                  </small>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="naming_convention_prefix" class="required">Form Number Prefix</label>
                      <input type="text" 
                             class="form-control @error('naming_convention_prefix') is-invalid @enderror" 
                             id="naming_convention_prefix" 
                             name="naming_convention_prefix" 
                             value="{{ old('naming_convention_prefix', 'SF') }}" 
                             required 
                             maxlength="50"
                             placeholder="SF">
                      @error('naming_convention_prefix')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                      <small class="form-text text-muted">
                        Used to generate unique form numbers (e.g., SF for Submission Form).
                      </small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="naming_convention_format" class="required">Form Number Format</label>
                      <select class="form-control @error('naming_convention_format') is-invalid @enderror" 
                              id="naming_convention_format" 
                              name="naming_convention_format" 
                              required>
                        <option value="{prefix}/{year}/{sequence}" {{ old('naming_convention_format') == '{prefix}/{year}/{sequence}' ? 'selected' : '' }}>
                          SF/2025/001
                        </option>
                        <option value="{prefix}-{year}-{sequence}" {{ old('naming_convention_format') == '{prefix}-{year}-{sequence}' ? 'selected' : '' }}>
                          SF-2025-001
                        </option>
                        <option value="{prefix}{year}{sequence}" {{ old('naming_convention_format') == '{prefix}{year}{sequence}' ? 'selected' : '' }}>
                          SF2025001
                        </option>
                        <option value="{prefix}/{sequence}" {{ old('naming_convention_format') == '{prefix}/{sequence}' ? 'selected' : '' }}>
                          SF/001
                        </option>
                      </select>
                      @error('naming_convention_format')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                      <small class="form-text text-muted">
                        Format for generating unique form instance numbers.
                      </small>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="print_template_name">Print Template</label>
                  <select class="form-control @error('print_template_name') is-invalid @enderror" 
                          id="print_template_name" 
                          name="print_template_name">
                    <option value="">Use Default Template</option>
                    <option value="submission-forms.print.default" {{ old('print_template_name') == 'submission-forms.print.default' ? 'selected' : '' }}>
                      Default Template
                    </option>
                    <option value="submission-forms.print.microbiology" {{ old('print_template_name') == 'submission-forms.print.microbiology' ? 'selected' : '' }}>
                      Microbiology Template
                    </option>
                    <option value="submission-forms.print.serology" {{ old('print_template_name') == 'submission-forms.print.serology' ? 'selected' : '' }}>
                      Serology Template
                    </option>
                    <!-- Add your new template here -->
                    <option value="submission-forms.print.your-template-name" {{ old('print_template_name') == 'submission-forms.print.your-template-name' ? 'selected' : '' }}>
                      Your Template Name
                    </option>
                  </select>
                  @error('print_template_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">
                    Select a custom print template for this form. If not specified, the default template will be used.
                  </small>
                </div>

                <div class="form-group">
                  <label for="sample_analysis_stage_ids">Lab Sections</label>
                  <select class="form-control select2 @error('sample_analysis_stage_ids') is-invalid @enderror" 
                          id="sample_analysis_stage_ids" 
                          name="sample_analysis_stage_ids[]"
                          multiple>
                    @foreach($labSections as $section)
                      <option value="{{ $section->id }}" {{ in_array($section->id, old('sample_analysis_stage_ids', [])) ? 'selected' : '' }}>
                        {{ $section->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('sample_analysis_stage_ids')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">
                    Select the lab sections associated with this form.
                  </small>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="is_active" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                      Active
                    </label>
                  </div>
                  <small class="form-text text-muted">
                    Inactive forms cannot be used to create new instances.
                  </small>
                </div>

                <div class="form-group">
                  <label for="start_submission_number">Start submission from number</label>
                  <input type="number" 
                         class="form-control @error('start_submission_number') is-invalid @enderror" 
                         id="start_submission_number" 
                         name="start_submission_number" 
                         value="{{ old('start_submission_number', 1) }}" 
                         min="1"
                         placeholder="1">
                  @error('start_submission_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">
                    Provide the start submission no for this submission form.
                  </small>
                </div>

                <div class="form-group mb-0">
                  <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-check"></i> Create Form
                  </button>
                  <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-secondary ml-2">
                    <i class="mdi mdi-close"></i> Cancel
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-information-outline"></i> Getting Started
              </h6>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <h6>What happens next?</h6>
                <ol class="small">
                  <li>Create your form template</li>
                  <li>Add sections to organize your fields</li>
                  <li>Add form elements (fields) to collect data</li>
                  <li>Preview and test your form</li>
                  <li>Publish the form for users</li>
                </ol>
              </div>
              
              <div class="mb-3">
                <h6>Form Naming</h6>
                <p class="small text-muted">
                  Choose a clear, descriptive name that helps users understand the form's purpose. 
                  This name will appear in form lists and user interfaces.
                </p>
              </div>
              
              <div class="mb-3">
                <h6>Form Numbers</h6>
                <p class="small text-muted">
                  Each form submission gets a unique number based on your prefix and format settings. 
                  This helps with tracking and referencing submissions.
                </p>
              </div>
              
              <div class="alert alert-info small">
                <i class="mdi mdi-lightbulb-outline"></i>
                <strong>Tip:</strong> Start with a simple form structure. You can always add more sections and fields later.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Validation Errors -->
  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Please correct the following errors:</strong>
      <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif
@endsection

@section('script2')
<script>
  // Auto-hide alerts after 5 seconds
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);

  // Form validation
  document.getElementById('name').addEventListener('input', function() {
    const value = this.value.trim();
    if (value.length > 0) {
      this.classList.remove('is-invalid');
    }
  });

  // Preview form number format
  document.getElementById('naming_convention_format').addEventListener('change', function() {
    const prefix = document.getElementById('naming_convention_prefix').value || 'SF';
    const format = this.value;
    const year = new Date().getFullYear();
    
    let preview = format
      .replace('{prefix}', prefix)
      .replace('{year}', year)
      .replace('{sequence}', '001');
    
    // Update the selected option text to show preview
    const selectedOption = this.options[this.selectedIndex];
    const originalText = selectedOption.textContent;
    if (!originalText.includes('→')) {
      // Reset all options first
      Array.from(this.options).forEach(option => {
        option.textContent = option.textContent.split(' → ')[0];
      });
      // Add preview to selected option
      selectedOption.textContent = originalText + ' → ' + preview;
    }
  });

  // Update preview when prefix changes
  document.getElementById('naming_convention_prefix').addEventListener('input', function() {
    document.getElementById('naming_convention_format').dispatchEvent(new Event('change'));
  });
</script>

<style>
  .required::after {
    content: " *";
    color: red;
  }
</style>
@endsection