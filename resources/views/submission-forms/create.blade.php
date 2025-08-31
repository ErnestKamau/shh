@extends('layouts.lab.layout.app')

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

@section('scripts')
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