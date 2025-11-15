@extends('layouts.lab.layout.app', ['select2'=>true])

@section('title2')
  <title>View Form Instance - {{ $submissionForm->name }}</title>
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
          'link' => route('submission-forms.instances.index'),
          'name' => 'My Submissions',
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
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <div>
        <h2>
          <i class="mdi mdi-file-document"></i> {{ $submissionForm->name }}
          <small class="text-muted">{{ $instance->form_number }}</small>
        </h2>
        @if($instance->title)
          <p class="text-muted mb-0">{{ $instance->title }}</p>
        @endif
        <div class="alert alert-info mt-2 mb-0">
          <i class="mdi mdi-information-outline"></i>
          <strong>Form Instance:</strong> View submitted form data and status information.
          <span class="badge badge-{{ $instance->getStatusBadgeColor() }} ml-2">
            {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
          </span>
        </div>
      </div>
      <div class="d-flex flex-wrap align-items-center">
        @unless($instance->isDraft())
          <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" 
             class="btn btn-warning btn-sm mr-2 mb-2">
            <i class="mdi mdi-pencil"></i> Edit Information
          </a>
        @endunless
        <a href="{{ route('submission-forms.instances.print', [$submissionForm, $instance]) }}" 
           class="btn btn-outline-info btn-sm mr-2 mb-2" 
           target="_blank">
          <i class="mdi mdi-printer"></i> Print Form
        </a>
        @if($instance->isDraft())
          <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" 
             class="btn btn-primary btn-sm">
            <i class="mdi mdi-pencil"></i> Continue Editing
          </a>
        @endif
        <a href="{{ route('submission-forms.instances.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
          <i class="mdi mdi-arrow-left"></i> Back to My Submissions
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
                  <small class="text-muted">Form Number: <strong>{{ $instance->form_number }}</strong></small>
                </div>
              </div>
            </div>
            <div class="card-body">
              <!-- Instance Information -->
              <div class="row mb-4">
                <div class="col-md-8">
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
              @include('submission-forms.partials.sample-creation-actions', ['instance' => $instance])

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
    </div>
  </main>
@endsection

@section('script2')
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
</style>
@endsection

