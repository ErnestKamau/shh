@extends('layouts.sample-submissions', ['select2'=>true])

@section('title')
  <title>View Form Instance - {{ $submissionForm->name }}</title>
@endsection

@section('content')
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
          'name' => 'View Instance',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="h4 mb-2">
            <i class="mdi mdi-file-document"></i> {{ $submissionForm->name }}
            <small class="d-block text-muted mt-1">{{ $instance->form_number }}</small>
          </h2>
          @if($instance->title)
            <p class="text-muted mb-0">{{ $instance->title }}</p>
          @endif
          <div class="alert alert-info mt-2 mb-0 p-2">
            <i class="mdi mdi-information-outline"></i>
            <strong>Form Instance:</strong> View submitted form data and status information.
            <span class="badge badge-{{ $instance->getStatusBadgeColor() }} ml-2">
              {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
            </span>
          </div>
        </div>
        <div class="d-flex gap-2">
          @if($instance->isDraft())
            <a href="{{ route('submission-forms.instances.fill-sample', [$submissionForm, $instance]) }}" 
               class="btn btn-primary mr-2 btn-sm">
              <i class="mdi mdi-pencil"></i> Continue Editing
            </a>
          @else
            <button type="button" 
                    class="btn btn-warning mr-2 btn-sm" 
                    id="edit-instance-btn"
                    data-edit-url="{{ route('submission-forms.instances.fill-sample', [$submissionForm, $instance]) }}">
              <i class="mdi mdi-pencil"></i> Edit Information
            </button>
          @endif
          <a href="{{ route('sample-submissions') }}" class="btn mr-2 btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> 
            <span class="d-none d-sm-inline">Back to Sample Submissions</span>
            <span class="d-inline d-sm-none">Back</span>
          </a>
        </div>
      </div>
    </div>

    <div class="p-3 p-md-4">
      <div class="row justify-content-center">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                  <h4 class="mb-1">{{ $submissionForm->name }}</h4>
                  @if($submissionForm->description)
                    <p class="text-muted mb-0">{{ $submissionForm->description }}</p>
                  @endif
                </div>
                <div class="text-right">
                  <small class="text-muted">Form Number: <strong>{{ $instance->form_number }}</strong></small>
                </div>
              </div>
            </div>
            <div class="card-body">
              <!-- Instance Information -->
              <div class="row mb-4">
                <div class="col-md-8 mb-3 mb-md-0">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h6 class="card-title">Submission Details</h6>
                      <div class="row">
                        <div class="col-md-6">
                          <p><strong>Form Number:</strong> {{ $instance->form_number }}</p>
                          <p><strong>Status:</strong> 
                            <span class="badge badge-{{ $instance->getStatusBadgeColor() }}">
                              {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
                            </span>
                          </p>
                          <p><strong>Priority:</strong> 
                            <span class="badge badge-{{ $instance->getPriorityBadgeColor() }}">
                              {{ ucfirst($instance->priority) }}
                            </span>
                          </p>
                        </div>
                        <div class="col-md-6">
                          <p><strong>Submitted By:</strong> {{ $instance->submittedBy->name }}</p>
                          <p><strong>Created:</strong> {{ $instance->created_at->format('M d, Y H:i') }}</p>
                          @if($instance->submitted_at)
                            <p><strong>Submitted:</strong> {{ $instance->submitted_at->format('M d, Y H:i') }}</p>
                          @endif
                          @if($instance->due_date)
                            <p><strong>Due Date:</strong> 
                              {{ $instance->due_date->format('M d, Y') }}
                              @if($instance->isOverdue())
                                <span class="text-danger ml-1">(Overdue)</span>
                              @endif
                            </p>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card">
                    <div class="card-body">
                      <h6 class="card-title">Form Information</h6>
                      <p><strong>Form Name:</strong> {{ $submissionForm->name }}</p>
                      <p><strong>Version:</strong> {{ $submissionForm->version }}</p>
                      <p><strong>Description:</strong> {{ $submissionForm->description }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Form Data Display using Simple Form Display -->
              @php
                $formData = $instance->getFormDataForDisplay();
              @endphp
              
              @include('submission-forms.partials.simple-form-display', ['instance' => $instance, 'formData' => $formData])

              <!-- Sample Creation Actions -->
              @php
                $canCreateSamples = $instance->isSubmitted() && $instance->submissionForm->sections()->whereHas('elements', function($query) {
                    $query->where('is_mapped', true);
                })->exists();
                $sampleStatus = app(\App\Services\SampleCreationService::class)->getSampleCreationStatus($instance);
              @endphp

              @if($canCreateSamples)
                <div class="card mt-3">
                  <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                      <i class="mdi mdi-flask"></i> Sample Creation
                    </h6>
                  </div>
                  <div class="card-body">
                    @if($sampleStatus['status'] === 'created')
                      <div class="alert alert-success">
                        <i class="mdi mdi-check-circle"></i>
                        <strong>Samples Created Successfully!</strong>
                        <p class="mb-0 mt-2">
                          This form has been converted to sample records. 
                          @if(isset($sampleStatus['sample_headers']) && count($sampleStatus['sample_headers']) > 0)
                            <br>
                            <strong>Batch Code:</strong> {{ $sampleStatus['sample_headers']->first()->batch_code }}
                            <br>
                            <strong>Sample Count:</strong> {{ $sampleStatus['sample_headers']->first()->samples->count() }}
                          @endif
                        </p>
                      </div>
                      
                      <div class="alert alert-info mt-2">
                        <i class="mdi mdi-information"></i>
                        <strong>Note:</strong> You will be automatically redirected to Sample Submissions in a moment, or click the button below.
                      </div>
                      
                      <div class="mt-3">
                        <a href="{{ route('sample-submissions') }}" class="btn btn-success">
                          <i class="mdi mdi-arrow-left"></i> Back to Sample Submissions
                        </a>
                      </div>
                      
                    @elseif($sampleStatus['status'] === 'ready')
                      <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        <strong>Ready to Create Samples</strong>
                        <p class="mb-0 mt-2">
                          This form has mapped elements and can be converted to sample records.
                          Click the button below to create sample headers and details.
                        </p>
                      </div>
                      
                      <div class="mt-3">
                        <button type="button" 
                                class="btn btn-primary" 
                                id="create-samples-btn"
                                data-instance-id="{{ $instance->id }}">
                          <i class="mdi mdi-flask"></i> Create Batch
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-secondary ml-2" 
                                id="check-sample-status-btn"
                                data-instance-id="{{ $instance->id }}">
                          <i class="mdi mdi-refresh"></i> Check Status
                        </button>
                      </div>
                      
                    @else
                      <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i>
                        <strong>Cannot Create Samples</strong>
                        <p class="mb-0 mt-2">{{ $sampleStatus['message'] }}</p>
                      </div>
                    @endif
                  </div>
                </div>
              @endif

              <!-- Review Information (if applicable) -->
              @if($instance->reviewed_at)
                <div class="mt-4">
                  <div class="card border-{{ $instance->status === 'approved' ? 'success' : 'danger' }}">
                    <div class="card-header bg-{{ $instance->status === 'approved' ? 'success' : 'danger' }} text-white">
                      <h6 class="mb-0">
                        <i class="mdi mdi-{{ $instance->status === 'approved' ? 'check-circle' : 'close-circle' }}"></i>
                        Review Information
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                          <p><strong>Reviewed By:</strong> {{ $instance->reviewedBy->name }}</p>
                          <p><strong>Reviewed At:</strong> {{ $instance->reviewed_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                          @if($instance->review_notes)
                            <p><strong>Review Notes:</strong></p>
                            <div class="bg-light p-3 rounded">
                              {{ $instance->review_notes }}
                            </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              @endif

              <!-- Audit Trail -->
              @if($auditLogs->count() > 0)
                <div class="mt-4">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="mb-0">
                        <i class="mdi mdi-history"></i> Audit Trail
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="timeline">
                        @foreach($auditLogs as $log)
                          <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $log->getActionBadgeColor() }}"></div>
                            <div class="timeline-content">
                              <h6 class="timeline-title">
                                {{ $log->getActionDisplayName() }}
                                <span class="badge badge-{{ $log->getActionBadgeColor() }} ml-2">
                                  {{ $log->action }}
                                </span>
                              </h6>
                              <p class="timeline-text">
                                <strong>By:</strong> {{ $log->user->name }}
                                <br>
                                <strong>Date:</strong> {{ $log->created_at->format('M d, Y H:i:s') }}
                                @if($log->ip_address)
                                  <br>
                                  <strong>IP:</strong> {{ $log->ip_address }}
                                @endif
                              </p>
                              @if($log->notes)
                                <div class="bg-light p-2 rounded mt-2">
                                  <strong>Notes:</strong> {{ $log->notes }}
                                </div>
                              @endif
                              @if($log->field_changes)
                                <div class="bg-light p-2 rounded mt-2">
                                  <strong>Field Changes:</strong>
                                  <pre class="mb-0">{{ json_encode($log->field_changes, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                              @endif
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
@endsection

@push('styles')
<style>
/* Timeline styles for audit trail */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 10px;
    font-size: 1rem;
}

.timeline-text {
    margin-bottom: 0;
    font-size: 0.9rem;
    color: #6c757d;
}

/* Responsive adjustments for tablets */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline::before {
        left: 10px;
    }
    
    .timeline-marker {
        left: -17px;
    }
}
</style>
@endpush

@section('script')
<script>
$(document).ready(function() {
    // Edit instance button - prompt before navigating back to form
    $('#edit-instance-btn').on('click', function () {
        const editUrl = $(this).data('edit-url');

        Swal.fire({
            title: 'Edit Form Data?',
            text: 'You will be redirected to the form with your previously entered information so you can make updates.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Edit Form',
            cancelButtonText: 'Stay Here'
        }).then((result) => {
            if (result.isConfirmed && editUrl) {
                window.location.href = editUrl;
            }
        });
    });

    // Create samples button - Tablet version with redirect
    $('#create-samples-btn').on('click', function() {
        const instanceId = $(this).data('instance-id');
        const $btn = $(this);
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Creating...');
        
        $.ajax({
            url: '{{ route("submission-forms.instances.create-samples", ":instance") }}'.replace(':instance', instanceId),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        // Redirect to sample submissions after success
                        window.location.href = '{{ route("sample-submissions") }}';
                    });
                    
                    // Fallback redirect in case Swal doesn't work
                    setTimeout(function() {
                        window.location.href = '{{ route("sample-submissions") }}';
                    }, 2500);
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    $btn.prop('disabled', false).html('<i class="mdi mdi-flask"></i> Create Batch');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    title: 'Error!',
                    text: response?.message || 'An error occurred while creating samples.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                $btn.prop('disabled', false).html('<i class="mdi mdi-flask"></i> Create Batch');
            }
        });
    });
    
    // Check status button
    $('#check-sample-status-btn').on('click', function() {
        const instanceId = $(this).data('instance-id');
        const $btn = $(this);
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Checking...');
        
        $.ajax({
            url: '{{ route("submission-forms.instances.sample-status", ":instance") }}'.replace(':instance', instanceId),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Sample Status',
                        text: response.data.message,
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    title: 'Error!',
                    text: response?.message || 'An error occurred while checking status.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="mdi mdi-refresh"></i> Check Status');
            }
        });
    });
});
</script>

<!-- Include SweetAlert2 for better modals -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

