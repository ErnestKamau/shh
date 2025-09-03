@extends('layouts.lab.layout.app')

@section('title2')
  <title>Create Certificate Template</title>
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
          'link' => route('certificate-templates.index'),
          'name' => 'Certificate Templates',
          'icon' => null
        ),
        array(
          'link' => '#',
          'name' => 'Create Template',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <h2>
        <i class="mdi mdi-certificate"></i> Create Certificate Template
      </h2>
      <a href="{{ route('certificate-templates.index') }}" class="btn btn-secondary">
        <i class="mdi mdi-arrow-left"></i> Back to Templates
      </a>
    </div>

    <div class="bg-light p-4">
      <form method="POST" action="{{ route('certificate-templates.store') }}">
        @csrf
        
        <div class="row">
          <!-- Basic Information -->
          <div class="col-md-8">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">
                  <i class="mdi mdi-information"></i> Basic Information
                </h5>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <label for="name" class="form-label required">Template Name</label>
                  <input type="text" 
                         class="form-control @error('name') is-invalid @enderror" 
                         id="name" 
                         name="name" 
                         value="{{ old('name') }}" 
                         required 
                         maxlength="255"
                         placeholder="Enter template name">
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">
                    Choose a descriptive name for your certificate template.
                  </small>
                </div>

                <div class="form-group">
                  <label for="description" class="form-label">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3" 
                            maxlength="1000"
                            placeholder="Describe the purpose and usage of this template">{{ old('description') }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">
                    Optional description to help identify the template's purpose.
                  </small>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input @error('is_active') is-invalid @enderror" 
                           id="is_active" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                      Active Template
                    </label>
                    @error('is_active')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <small class="form-text text-muted">
                    Active templates can be used for report generation when published.
                  </small>
                </div>
              </div>
            </div>
          </div>

          <!-- Page Settings -->
          <div class="col-md-4">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">
                  <i class="mdi mdi-file-document-outline"></i> Page Settings
                </h5>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <label for="page_size" class="form-label">Page Size</label>
                  <select class="form-control" id="page_size" name="page_settings[page_size]">
                    <option value="A4" {{ old('page_settings.page_size', 'A4') == 'A4' ? 'selected' : '' }}>A4</option>
                    <option value="Letter" {{ old('page_settings.page_size') == 'Letter' ? 'selected' : '' }}>Letter</option>
                    <option value="Legal" {{ old('page_settings.page_size') == 'Legal' ? 'selected' : '' }}>Legal</option>
                    <option value="Custom" {{ old('page_settings.page_size') == 'Custom' ? 'selected' : '' }}>Custom</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="orientation" class="form-label">Orientation</label>
                  <select class="form-control" id="orientation" name="page_settings[orientation]">
                    <option value="portrait" {{ old('page_settings.orientation', 'portrait') == 'portrait' ? 'selected' : '' }}>Portrait</option>
                    <option value="landscape" {{ old('page_settings.orientation') == 'landscape' ? 'selected' : '' }}>Landscape</option>
                  </select>
                </div>

                <div class="form-group">
                  <label class="form-label">Margins (mm)</label>
                  <div class="row">
                    <div class="col-6">
                      <input type="number" 
                             class="form-control form-control-sm" 
                             name="page_settings[margins][top]" 
                             value="{{ old('page_settings.margins.top', 20) }}" 
                             placeholder="Top" 
                             min="0" 
                             max="50">
                    </div>
                    <div class="col-6">
                      <input type="number" 
                             class="form-control form-control-sm" 
                             name="page_settings[margins][right]" 
                             value="{{ old('page_settings.margins.right', 15) }}" 
                             placeholder="Right" 
                             min="0" 
                             max="50">
                    </div>
                  </div>
                  <div class="row mt-2">
                    <div class="col-6">
                      <input type="number" 
                             class="form-control form-control-sm" 
                             name="page_settings[margins][bottom]" 
                             value="{{ old('page_settings.margins.bottom', 20) }}" 
                             placeholder="Bottom" 
                             min="0" 
                             max="50">
                    </div>
                    <div class="col-6">
                      <input type="number" 
                             class="form-control form-control-sm" 
                             name="page_settings[margins][left]" 
                             value="{{ old('page_settings.margins.left', 15) }}" 
                             placeholder="Left" 
                             min="0" 
                             max="50">
                    </div>
                  </div>
                </div>

                <hr>

                <div class="form-group">
                  <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="header_enabled" 
                           name="header_settings[enabled]" 
                           value="1" 
                           {{ old('header_settings.enabled') ? 'checked' : '' }}>
                    <label class="form-check-label" for="header_enabled">
                      Enable Header
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="footer_enabled" 
                           name="footer_settings[enabled]" 
                           value="1" 
                           {{ old('footer_settings.enabled') ? 'checked' : '' }}>
                    <label class="form-check-label" for="footer_enabled">
                      Enable Footer
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="show_page_numbers" 
                           name="footer_settings[show_page_numbers]" 
                           value="1" 
                           {{ old('footer_settings.show_page_numbers') ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_page_numbers">
                      Show Page Numbers
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="d-flex justify-content-between">
              <a href="{{ route('certificate-templates.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Cancel
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-content-save"></i> Create Template
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </main>
@endsection

@section('scripts')
<script>
  // Show/hide custom dimensions based on page size selection
  document.getElementById('page_size').addEventListener('change', function() {
    const customDimensions = document.getElementById('custom_dimensions');
    if (this.value === 'Custom') {
      if (!customDimensions) {
        // Add custom dimensions fields if they don't exist
        const customDiv = document.createElement('div');
        customDiv.id = 'custom_dimensions';
        customDiv.className = 'form-group';
        customDiv.innerHTML = `
          <label class="form-label">Custom Dimensions (mm)</label>
          <div class="row">
            <div class="col-6">
              <input type="number" class="form-control form-control-sm" 
                     name="page_settings[custom_dimensions][width]" 
                     placeholder="Width" min="50" max="500" value="210">
            </div>
            <div class="col-6">
              <input type="number" class="form-control form-control-sm" 
                     name="page_settings[custom_dimensions][height]" 
                     placeholder="Height" min="50" max="500" value="297">
            </div>
          </div>
        `;
        this.parentNode.parentNode.appendChild(customDiv);
      }
    } else {
      if (customDimensions) {
        customDimensions.remove();
      }
    }
  });
</script>
@endsection