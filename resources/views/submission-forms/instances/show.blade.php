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
    @php
        $canCreateSamples = $instance->isSubmitted() && $instance->submissionForm->sections()->whereHas('elements', function($query) {
            $query->where('is_mapped', true);
        })->exists();
        $sampleStatus = app(\App\Services\SampleCreationService::class)->getSampleCreationStatus($instance);
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @if(is_array(session('apply_batches_warnings')) && count(session('apply_batches_warnings')) > 0)
      <div class="alert alert-warning mx-4 mt-3 mb-0">
        <strong>Please note:</strong>
        <ul class="mb-0 pl-3 mt-2">
          @foreach(session('apply_batches_warnings') as $w)
            <li>{{ $w }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    
    <div class="card border-0 shadow-sm mx-4 mt-4 mb-0">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <div class="d-flex align-items-start">
              <div class="mr-3">
                <span class="btn btn-primary btn-circle btn-lg pointer-events-none">
                  <i class="mdi mdi-file-document mdi-24px"></i>
                </span>
              </div>
              <div>
                <h4 class="mb-1 font-weight-bold">
                  {{ $submissionForm->name }}
                  <span class="text-muted font-weight-normal mx-2">-</span>
                  <span class="text-muted small">{{ $instance->form_number ?? 'Pending' }}</span>
                  <span class="badge badge-{{ $instance->getStatusBadgeColor() }} ml-2 align-middle" style="font-size: 0.7em;">
                    {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
                  </span>
                </h4>
                
                @if($instance->title)
                  <h5 class="text-muted mb-2">{{ $instance->title }}</h5>
                @endif
                
                <p class="text-muted mb-0 small">
                  <i class="mdi mdi-information-outline mr-1"></i>
                  View submitted form data and status information.
                </p>
              </div>
            </div>
          </div>
          
          <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" style="border-radius: 20px;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Actions
                </button>
                <div class="dropdown-menu dropdown-menu-right">
    @unless($instance->isDraft())
                        <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" class="dropdown-item">
                            <i class="mdi mdi-pencil mr-2"></i> Edit Information
                        </a>

                        @if($canCreateSamples && $sampleStatus['status'] === 'ready')
                            <a href="#" class="dropdown-item create-samples-btn" data-instance-id="{{ $instance->id }}">
                                <i class="mdi mdi-flask mr-2"></i> Create Batch
                            </a>
                        @endif

                        @if($instance->batches()->exists() && ($linkedBatchesOutOfSyncWithForm ?? false))
                            <form method="POST" action="{{ route('submission-forms.instances.apply-to-batches', $instance->id) }}" class="d-inline w-100" onsubmit="return confirm('Update all linked batches from the current saved form data? Customer, company unit, and unprocessed staging will be refreshed.');">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="mdi mdi-sync mr-2"></i> Apply form to linked batches
                                </button>
                            </form>
                        @endif
                    @endunless

                    @if($instance->isDraft())
                        <a href="{{ route('submission-forms.instances.fill', [$submissionForm, $instance]) }}" class="dropdown-item">
                            <i class="mdi mdi-pencil mr-2"></i> Continue Editing
                        </a>
                    @endif

                    @php
                        $firstBatch = $instance->batches->first();
                    @endphp
                    @if($firstBatch)
                        <a href="{{ route('view-batch-details', ['batch' => $firstBatch->id, 'client' => 0, 'portal' => 0, 'status' => $firstBatch->status]) }}" class="dropdown-item">
                            <i class="mdi mdi-flask mr-2"></i> View Sample Batch
                        </a>
                    @endif

                    <div class="dropdown-divider"></div>
                    
                    <a href="{{ route('submission-forms.instances.index') }}" class="dropdown-item">
                        <i class="mdi mdi-arrow-left mr-2"></i> Back to My Submissions
                    </a>

                    <form
                        action="{{ route('submission-forms.instances.destroy', [$submissionForm->id, $instance->id]) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this submission and all its batches and samples? This cannot be undone.');"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="mdi mdi-delete-empty mr-2"></i> Delete Submission & Batches
                        </button>
                    </form>

                </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row justify-content-center">
        <div class="col-md-10">
          <div class="card">
           
            <div class="card-body">
              <!-- Instance Information -->
              <!-- Instance Information Summary -->
              <div class="row mb-4">
                <div class="col-12">
                   <div class="bg-light rounded p-4 border table-responsive">
                      <div class="row">
                          <div class="col-md-4 border-right">
                              <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Submission Details</h6>
                              <div class="mb-3">
                                  <label class="text-muted small mb-0 d-block">Form Number</label>
                                  <span class="font-weight-bold text-dark">{{ $instance->form_number ?? 'Pending' }}</span>
                              </div>
                              <div class="mb-3">
                                  <label class="text-muted small mb-0 d-block">Submitted By</label>
                                  <div class="d-flex align-items-center">
                                      <i class="mdi mdi-account-circle mr-1 text-primary"></i>
                                      <span class="font-weight-medium">{{ $instance->submittedBy->name }}</span>
                                  </div>
                              </div>
                              <div>
                                  <label class="text-muted small mb-0 d-block">Priority</label>
                                  <span class="badge badge-{{ $instance->getPriorityBadgeColor() }} badge-pill px-2">
                                    {{ ucfirst($instance->priority) }}
                                  </span>
                              </div>
                          </div>
                          
                          <div class="col-md-4 border-right">
                              <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Timeline</h6>
                              <div class="mb-3">
                                  <label class="text-muted small mb-0 d-block">Created On</label>
                                  <span class="text-dark"><i class="mdi mdi-calendar-blank mr-1"></i> {{ $instance->created_at->format('M d, Y H:i') }}</span>
                              </div>
                              @if($instance->submitted_at)
                              <div class="mb-3">
                                  <label class="text-muted small mb-0 d-block">Submitted On</label>
                                  <span class="text-dark"><i class="mdi mdi-send mr-1"></i> {{ $instance->submitted_at->format('M d, Y H:i') }}</span>
                              </div>
                              @endif
                              @if($instance->due_date)
                              <div>
                                  <label class="text-muted small mb-0 d-block">Due Date</label>
                                  <span class="{{ $instance->isOverdue() ? 'text-danger font-weight-bold' : 'text-dark' }}">
                                    <i class="mdi mdi-clock-alert mr-1"></i> {{ $instance->due_date->format('M d, Y') }}
                                    @if($instance->isOverdue()) (Overdue) @endif
                                  </span>
                              </div>
                              @endif
                          </div>

                          <div class="col-md-4">
                              <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Form Context</h6>
                              <div class="mb-3">
                                  <label class="text-muted small mb-0 d-block">Definition</label>
                                  <a href="#" class="font-weight-medium text-dark border-bottom border-dark pb-1 text-decoration-none">
                                    {{ $submissionForm->name }} <small class="text-muted">(v{{ $submissionForm->version }})</small>
                                  </a>
                              </div>
                              @if($submissionForm->description)
                              <div class="mb-3">
                                  <label class="text-muted small mb-0 d-block">Description</label>
                                  <p class="small text-muted mb-0">{{ Str::limit($submissionForm->description, 100) }}</p>
                              </div>
                              @endif
                              <div>
                                   <label class="text-muted small mb-0 d-block">Status</label>
                                   <span class="badge badge-{{ $instance->getStatusBadgeColor() }} badge-pill px-3 py-1">
                                      {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
                                   </span>
                              </div>
                          </div>
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
              @include('submission-forms.partials.sample-creation-actions', ['instance' => $instance, 'linkedBatchesOutOfSyncWithForm' => $linkedBatchesOutOfSyncWithForm ?? false])

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

