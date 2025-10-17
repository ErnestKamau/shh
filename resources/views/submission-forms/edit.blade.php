@extends('layouts.lab.layout.app')

@section('title2')
  <title>Edit Submission Form - {{ $submissionForm->name }}</title>
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
          'name' => 'Edit',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <h2>
        <i class="mdi mdi-pencil"></i> Edit Submission Form
      </h2>
      <div>
        <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-info">
          <i class="mdi mdi-eye"></i> View Form
        </a>
        <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Forms
        </a>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Form Details</h5>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('submission-forms.update', $submissionForm) }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                  <label for="name" class="required">Form Name</label>
                  <input type="text" 
                         class="form-control @error('name') is-invalid @enderror" 
                         id="name" 
                         name="name" 
                         value="{{ old('name', $submissionForm->name) }}" 
                         required 
                         maxlength="255"
                         placeholder="Enter a descriptive name for your form">
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">
                    This will be displayed to users when they access the form.
                  </small>
                </div>

                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3" 
                            maxlength="1000"
                            placeholder="Provide a brief description of what this form is used for">{{ old('description', $submissionForm->description) }}</textarea>
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
                             value="{{ old('naming_convention_prefix', $submissionForm->naming_convention_prefix) }}" 
                             required 
                             maxlength="50"
                             placeholder="SF">
                      @error('naming_convention_prefix')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                      <small class="form-text text-muted">
                        Used to generate unique form numbers (e.g., SF for Submission Form).
                      </small>
                      @if($submissionForm->instances()->exists())
                        <div class="alert alert-warning small mt-2">
                          <i class="mdi mdi-alert"></i>
                          <strong>Warning:</strong> This form has existing instances. Changing the prefix may affect future form numbering.
                        </div>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="naming_convention_format" class="required">Form Number Format</label>
                      <select class="form-control @error('naming_convention_format') is-invalid @enderror" 
                              id="naming_convention_format" 
                              name="naming_convention_format" 
                              required>
                        <option value="{prefix}/{year}/{sequence}" {{ old('naming_convention_format', $submissionForm->naming_convention_format) == '{prefix}/{year}/{sequence}' ? 'selected' : '' }}>
                          SF/2025/001
                        </option>
                        <option value="{prefix}-{year}-{sequence}" {{ old('naming_convention_format', $submissionForm->naming_convention_format) == '{prefix}-{year}-{sequence}' ? 'selected' : '' }}>
                          SF-2025-001
                        </option>
                        <option value="{prefix}{year}{sequence}" {{ old('naming_convention_format', $submissionForm->naming_convention_format) == '{prefix}{year}{sequence}' ? 'selected' : '' }}>
                          SF2025001
                        </option>
                        <option value="{prefix}/{sequence}" {{ old('naming_convention_format', $submissionForm->naming_convention_format) == '{prefix}/{sequence}' ? 'selected' : '' }}>
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
                    <option value="submission-forms.print.default" {{ old('print_template_name', $submissionForm->print_template_name) == 'submission-forms.print.default' ? 'selected' : '' }}>
                      Default Template
                    </option>
                    <option value="submission-forms.print.microbiology" {{ old('print_template_name', $submissionForm->print_template_name) == 'submission-forms.print.microbiology' ? 'selected' : '' }}>
                      Microbiology Template
                    </option>
                    <option value="submission-forms.print.serology" {{ old('print_template_name', $submissionForm->print_template_name) == 'submission-forms.print.serology' ? 'selected' : '' }}>
                      Serology Template
                    </option>
                    <!-- Add your new template here -->
                    <option value="submission-forms.print.your-template-name" {{ old('print_template_name', $submissionForm->print_template_name) == 'submission-forms.print.your-template-name' ? 'selected' : '' }}>
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
                  <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="is_active" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $submissionForm->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                      Active
                    </label>
                  </div>
                  <small class="form-text text-muted">
                    Inactive forms cannot be used to create new instances.
                  </small>
                  @if($submissionForm->instances()->exists() && !$submissionForm->is_active)
                    <div class="alert alert-info small mt-2">
                      <i class="mdi mdi-information-outline"></i>
                      This form has existing instances but is currently inactive.
                    </div>
                  @endif
                </div>

                <div class="form-group mb-0">
                  <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-check"></i> Update Form
                  </button>
                  <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-secondary ml-2">
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
                <i class="mdi mdi-information-outline"></i> Form Information
              </h6>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <h6>Current Status</h6>
                <div>
                  @if($submissionForm->is_published)
                    <span class="badge badge-success">Published</span>
                  @else
                    <span class="badge badge-warning">Draft</span>
                  @endif
                  
                  @if($submissionForm->is_active)
                    <span class="badge badge-outline-success ml-1">Active</span>
                  @else
                    <span class="badge badge-outline-danger ml-1">Inactive</span>
                  @endif
                </div>
              </div>
              
              <div class="mb-3">
                <h6>Statistics</h6>
                <ul class="list-unstyled small">
                  <li><strong>Version:</strong> {{ $submissionForm->version }}</li>
                  <li><strong>Created:</strong> {{ $submissionForm->created_at->format('M d, Y') }}</li>
                  <li><strong>Last Updated:</strong> {{ $submissionForm->updated_at->format('M d, Y') }}</li>
                  <li><strong>Sections:</strong> {{ $submissionForm->sections()->count() }}</li>
                  <li><strong>Instances:</strong> {{ $submissionForm->instances()->count() }}</li>
                </ul>
              </div>
              
              @if($submissionForm->instances()->exists())
                <div class="alert alert-warning small">
                  <i class="mdi mdi-alert"></i>
                  <strong>Note:</strong> This form has {{ $submissionForm->instances()->count() }} existing instance(s). 
                  Be careful when making changes that might affect data integrity.
                </div>
              @endif
              
              <div class="alert alert-info small">
                <i class="mdi mdi-lightbulb-outline"></i>
                <strong>Tip:</strong> After updating form details, you can modify the form structure by adding or editing sections and fields.
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

  // Initialize preview on page load
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('naming_convention_format').dispatchEvent(new Event('change'));
  });
</script>

<style>
  .required::after {
    content: " *";
    color: red;
  }
  
  .d-grid {
    display: grid;
  }
  
  .gap-2 {
    gap: 0.5rem;
  }
</style>
@endsection