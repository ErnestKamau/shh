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
        <h3>
          <i class="mdi mdi-file-document"></i> {{ $submissionForm->name }}
          <small class="text-muted">{{ $instance->form_number }}</small>
        </h3>
        @if($instance->title)
          <p class="text-muted mb-0">{{ $instance->title }}</p>
        @endif
      </div>
      <div class="d-flex gap-2">
        <span class="badge badge-{{ $instance->getStatusBadgeColor() }}">
          {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
        </span>
        @if($instance->isDraft())
          <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" 
             class="btn btn-sm btn-primary">
            <i class="mdi mdi-pencil"></i> Continue Editing
          </a>
        @endif
        <a href="{{ route('submission-forms.instances.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to My Submissions
        </a>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-file-document"></i> Form Instance Details
              </h6>
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

                    <!-- Form Data Display -->
                    @if($submissionForm->sections->count() > 0)
                        @foreach($submissionForm->sections as $sectionIndex => $section)
                            <div class="form-section mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="mdi mdi-{{ $section->isRowsSection() ? 'table' : 'view-list' }}"></i>
                                            {{ $section->title }}
                                            @if($section->isRowsSection())
                                                <span class="badge badge-info ml-2">Rows Section</span>
                                            @endif
                                        </h5>
                                        @if($section->description)
                                            <p class="text-muted mb-0 mt-2">{{ $section->description }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="card-body">
                                        @if($section->isRowsSection())
                                            @include('submission-forms.partials.rows-section-display', [
                                                'section' => $section,
                                                'existingValues' => $existingValues
                                            ])
                                        @else
                                            @include('submission-forms.partials.regular-section-display', [
                                                'section' => $section,
                                                'existingValues' => $existingValues
                                            ])
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i>
                            This form doesn't have any sections configured yet.
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
    </div>
  </main>
@endsection

@section('script')
<style>
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

.form-section {
    scroll-margin-top: 100px;
}

.required-field {
    color: #dc3545;
}

/* Display specific styles */
.field-display {
    margin-bottom: 1rem;
}

.field-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.field-value {
    padding: 0.5rem;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    min-height: 2.5rem;
}

.field-value.empty {
    color: #6c757d;
    font-style: italic;
}

/* Rows section display */
.rows-section-table {
    margin-top: 1rem;
}

.rows-section-table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}

.rows-section-table td {
    vertical-align: middle;
}

.row-number {
    font-weight: 600;
    color: #495057;
}
</style>
@endsection
