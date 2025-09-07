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
        <h3>
          <i class="mdi mdi-file-document-plus"></i> Create New Form Instance
          <small class="text-muted">{{ $submissionForm->name }}</small>
        </h3>
      </div>
      <div>
        <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Form
        </a>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-file-document-plus"></i> Instance Details
              </h6>
            </div>

                <div class="card-body">
                    <!-- Form Information -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5>{{ $submissionForm->name }}</h5>
                            <p class="text-muted">{{ $submissionForm->description }}</p>
                        </div>
                        <div class="col-md-4">
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
                    </div>

                    <!-- Instance Creation Form -->
                    <form method="POST" action="{{ route('submission-forms.instances.store', $submissionForm) }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-label">Instance Title <span class="text-danger">*</span></label>
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

                        <!-- Form Preview -->
                        <div class="mt-4">
                            <h6>Form Structure Preview</h6>
                            <div class="card">
                                <div class="card-body">
                                    @if($submissionForm->sections->count() > 0)
                                        <div class="accordion" id="formPreviewAccordion">
                                            @foreach($submissionForm->sections as $index => $section)
                                                <div class="card">
                                                    <div class="card-header" id="heading{{ $index }}">
                                                        <h6 class="mb-0">
                                                            <button class="btn btn-link" 
                                                                    type="button" 
                                                                    data-toggle="collapse" 
                                                                    data-target="#collapse{{ $index }}" 
                                                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                                                    aria-controls="collapse{{ $index }}">
                                                                <i class="mdi mdi-{{ $section->isRowsSection() ? 'table' : 'view-list' }}"></i>
                                                                {{ $section->title }}
                                                                @if($section->isRowsSection())
                                                                    <span class="badge badge-info ml-2">Rows Section</span>
                                                                @endif
                                                            </button>
                                                        </h6>
                                                    </div>
                                                    <div id="collapse{{ $index }}" 
                                                         class="collapse {{ $index === 0 ? 'show' : '' }}" 
                                                         aria-labelledby="heading{{ $index }}" 
                                                         data-parent="#formPreviewAccordion">
                                                        <div class="card-body">
                                                            @if($section->description)
                                                                <p class="text-muted">{{ $section->description }}</p>
                                                            @endif
                                                            
                                                            @if($section->elementHolders->count() > 0)
                                                                <div class="row">
                                                                    @foreach($section->elementHolders as $holder)
                                                                        <div class="col-md-6 mb-3">
                                                                            <div class="card border-light">
                                                                                <div class="card-body">
                                                                                    <h6 class="card-title">
                                                                                        {{ $holder->holder_type === 'field' ? 'Field Holder' : 'Text Holder' }}
                                                                                        @if($holder->max_elements > 1)
                                                                                            <span class="badge badge-secondary ml-2">{{ $holder->max_elements }} elements max</span>
                                                                                        @endif
                                                                                    </h6>
                                                                                    
                                                                                    @if($holder->elements->count() > 0)
                                                                                        <ul class="list-unstyled mb-0">
                                                                                            @foreach($holder->elements as $element)
                                                                                                <li>
                                                                                                    <i class="mdi mdi-{{ getElementIcon($element->element_type) }} text-muted"></i>
                                                                                                    {{ $element->label }}
                                                                                                    @if($element->is_required)
                                                                                                        <span class="text-danger">*</span>
                                                                                                    @endif
                                                                                                    @if($element->isMapped())
                                                                                                        <span class="badge badge-outline-info badge-sm ml-1" 
                                                                                                              title="Mapped to {{ ucfirst(str_replace('_', ' ', $element->mapping_table)) }}.{{ ucfirst(str_replace('_', ' ', $element->mapping_field)) }}">
                                                                                                            <i class="mdi mdi-database"></i> mapped
                                                                                                        </span>
                                                                                                    @endif
                                                                                                </li>
                                                                                            @endforeach
                                                                                        </ul>
                                                                                    @else
                                                                                        <p class="text-muted mb-0">No elements configured yet.</p>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <p class="text-muted">No element holders configured yet.</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="mdi mdi-alert"></i>
                                            This form doesn't have any sections configured yet. Please contact the form administrator.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-secondary">
                                <i class="mdi mdi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-file-document-plus"></i> Create Instance & Start Filling
                            </button>
                        </div>
                    </form>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Set default due date to 7 days from now
    if (!$('#due_date').val()) {
        const today = new Date();
        const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
        $('#due_date').val(nextWeek.toISOString().split('T')[0]);
    }
});
</script>
@endsection
