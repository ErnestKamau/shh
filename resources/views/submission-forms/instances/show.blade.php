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
    
    <!-- Enhanced Header Card (Imara Clinical Design) -->
    <div class="mx-4 mt-4" style="background: #f8f9fa;">
      <div style="background: #ffffff; border-radius: 4px; margin-bottom: 1px;">
        <div class="p-4">
          <div class="row align-items-start mb-3">
            <div class="col-lg-8">
              <div class="d-flex align-items-start">
                <div class="mr-3">
                  <span class="btn btn-primary btn-circle btn-lg pointer-events-none" style="width: 56px; height: 56px;">
                    <i class="mdi mdi-file-document mdi-24px"></i>
                  </span>
                </div>
                <div class="flex-grow-1">
                  <h2 class="mb-1 font-weight-bold" style="color: #191c1d; font-size: 1.5rem;">
                    {{ $submissionForm->name }}
                  </h2>
                  @if($instance->title)
                    <p class="text-muted mb-2" style="font-size: 0.95rem;">{{ $instance->title }}</p>
                  @endif
                  <p class="text-muted mb-0 small" style="color: #414754;">
                    <i class="mdi mdi-information-outline mr-1"></i>
                    Form #{{ $instance->getDocumentControlNumber() ?? 'Pending' }} • Submitted by {{ $instance->submittedBy->name ?? 'N/A' }}
                  </p>
                </div>
              </div>
            </div>
            
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
              <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" style="border-radius: 20px;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-menu-down mr-1"></i> Actions
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

    <!-- KPI Strip (Clinical Status Dashboard) -->
    <div class="mx-4" style="background: #ffffff; border-radius: 4px; margin-bottom: 0; padding: 0 1.5rem;">
      <div class="row py-3" style="border-bottom: 1px solid #e1e3e4; gap: 1rem;">
        <div class="col-6 col-md-3 py-2" style="border-right: 1px solid #e1e3e4;">
          <label class="text-muted small mb-1 d-block" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Status</label>
          <span class="badge badge-{{ $instance->getStatusBadgeColor() }} badge-pill px-2" style="font-size: 0.8rem;">
            {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
          </span>
        </div>
        <div class="col-6 col-md-3 py-2" style="border-right: 1px solid #e1e3e4;">
          <label class="text-muted small mb-1 d-block" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Priority</label>
          <span class="badge badge-{{ $instance->getPriorityBadgeColor() }} badge-pill px-2" style="font-size: 0.8rem;">
            {{ ucfirst($instance->priority) }}
          </span>
        </div>
        <div class="col-6 col-md-3 py-2" style="border-right: 1px solid #e1e3e4;">
          <label class="text-muted small mb-1 d-block" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Linked Batches</label>
          <span class="font-weight-bold" style="color: #191c1d; font-size: 1rem;">
            {{ $instance->batches->count() }}
          </span>
        </div>
        <div class="col-6 col-md-3 py-2">
          <label class="text-muted small mb-1 d-block" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Sync Status</label>
          <span class="badge badge-{{ ($linkedBatchesOutOfSyncWithForm ?? false) ? 'warning' : 'success' }} badge-pill px-2" style="font-size: 0.8rem;">
            {{ ($linkedBatchesOutOfSyncWithForm ?? false) ? 'Out of sync' : 'In sync' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Section Navigation Chips -->
    <div class="mx-4" style="background: #ffffff; padding: 1rem 1.5rem; border-radius: 4px;">
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <span class="text-muted small" style="font-weight: 600; margin-right: 1rem;">Navigate:</span>
        <a href="#submission-details" class="badge badge-pill px-3 py-2" style="background-color: #d8e1ea; color: #5b646b; text-decoration: none; cursor: pointer; font-weight: 500; font-size: 0.8rem;">
          <i class="mdi mdi-information-outline mr-1" style="font-size: 0.8rem;"></i> Summary
        </a>
        <a href="#form-sections" class="badge badge-pill px-3 py-2" style="background-color: #e1e3e4; color: #414754; text-decoration: none; cursor: pointer; font-weight: 500; font-size: 0.8rem;">
          <i class="mdi mdi-file-document-outline mr-1" style="font-size: 0.8rem;"></i> Form Data
        </a>
        @if($auditLogs->count() > 0)
        <a href="#audit-trail" class="badge badge-pill px-3 py-2" style="background-color: #e1e3e4; color: #414754; text-decoration: none; cursor: pointer; font-weight: 500; font-size: 0.8rem;">
          <i class="mdi mdi-history mr-1" style="font-size: 0.8rem;"></i> Audit Trail
        </a>
        @endif
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row justify-content-center">
        <div class="col-md-10">
          <div class="card" id="submission-details">
           
            <div class="card-body">
              <!-- Instance Information -->
              <!-- Instance Information Summary (Imara Design) -->
              <div class="row mb-4">
                <div class="col-12">
                   <div style="background: #f3f4f5; border-radius: 4px; overflow: hidden;">
                      <div class="row m-0">
                          <div class="col-md-4 p-4" style="background: #ffffff;">
                              <h6 class="text-uppercase text-muted small font-weight-bold mb-4" style="font-size: 0.65rem; letter-spacing: 0.5px;">Submission Details</h6>
                              <div class="mb-3">
                                  <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Form Number</label>
                                  <span class="font-weight-bold text-dark" style="color: #191c1d; font-size: 0.95rem;">{{ $instance->getDocumentControlNumber() ?? 'Pending' }}</span>
                              </div>
                              <div class="mb-3">
                                  <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Submitted By</label>
                                  <div class="d-flex align-items-center">
                                      <i class="mdi mdi-account-circle mr-2" style="color: #0059bb; font-size: 1rem;"></i>
                                      <span class="font-weight-medium" style="color: #191c1d; font-size: 0.9rem;">{{ $instance->submittedBy->name }}</span>
                                  </div>
                              </div>
                              <div>
                                  <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Priority</label>
                                  <span class="badge badge-{{ $instance->getPriorityBadgeColor() }} badge-pill px-2" style="font-size: 0.75rem;">
                                    {{ ucfirst($instance->priority) }}
                                  </span>
                              </div>
                          </div>
                          
                          <div class="col-md-4 p-4" style="background: #f3f4f5;">
                              <h6 class="text-uppercase text-muted small font-weight-bold mb-4" style="font-size: 0.65rem; letter-spacing: 0.5px;">Timeline</h6>
                              <div class="mb-3">
                                  <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Created On</label>
                                  <span class="text-dark" style="color: #191c1d; font-size: 0.9rem;"><i class="mdi mdi-calendar-blank mr-1"></i> {{ $instance->created_at->format('M d, Y H:i') }}</span>
                              </div>
                              @if($instance->submitted_at)
                              <div class="mb-3">
                                  <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Submitted On</label>
                                  <span class="text-dark" style="color: #191c1d; font-size: 0.9rem;"><i class="mdi mdi-send mr-1"></i> {{ $instance->submitted_at->format('M d, Y H:i') }}</span>
                              </div>
                              @endif
                              @if($instance->due_date)
                              <div>
                                  <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Due Date</label>
                                  <span class="{{ $instance->isOverdue() ? 'text-danger font-weight-bold' : 'text-dark' }}" style="font-size: 0.9rem;">
                                    <i class="mdi mdi-clock-alert mr-1"></i> {{ $instance->due_date->format('M d, Y') }}
                                    @if($instance->isOverdue()) (Overdue) @endif
                                  </span>
                              </div>
                              @endif
                          </div>

                          <div class="col-md-4 p-4" style="background: #ffffff;">
                              <h6 class="text-uppercase text-muted small font-weight-bold mb-4" style="font-size: 0.65rem; letter-spacing: 0.5px;">Form Context</h6>
                              <div class="mb-3">
                                  <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Definition</label>
                                  <a href="#" class="font-weight-medium text-dark border-bottom border-dark pb-1 text-decoration-none" style="color: #191c1d; font-size: 0.9rem;">
                                    {{ $submissionForm->name }} <small class="text-muted">(v{{ $submissionForm->version }})</small>
                                  </a>
                              </div>
                              @if($submissionForm->description)
                              <div class="mb-3">
                                  <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Description</label>
                                  <p class="small text-muted mb-0" style="font-size: 0.85rem;">{{ Str::limit($submissionForm->description, 100) }}</p>
                              </div>
                              @endif
                              <div>
                                   <label class="text-muted small mb-1 d-block" style="font-size: 0.75rem; color: #414754;">Status</label>
                                   <span class="badge badge-{{ $instance->getStatusBadgeColor() }} badge-pill px-3 py-1" style="font-size: 0.75rem;">
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
                $linkedAttachmentInstances = $instance->attachmentInstances()
                  ->with('submissionForm')
                  ->whereIn('status', ['submitted', 'in_review', 'approved', 'rejected'])
                  ->latest()
                  ->get();
                $workflowForms = $instance->workflowForms()->get();
              @endphp
              
              <div id="form-sections">
                @include('submission-forms.partials.simple-form-display-clinical', ['instance' => $instance, 'formData' => $formData])
              </div>

              @if($linkedAttachmentInstances->count() > 0)
                <div class="card mt-3 border-info">
                  <div class="card-header bg-light">
                    <h6 class="mb-0 text-info">
                      <i class="mdi mdi-link-variant mr-1"></i> Linked Attachment Forms
                    </h6>
                  </div>
                  <div class="card-body">
                    <p class="text-muted mb-3">Filled attachment forms linked to this template are shown below as a continuation.</p>
                    @foreach($linkedAttachmentInstances as $attachmentInstance)
                      @php
                        $attachmentFormData = $attachmentInstance->getFormDataForDisplay();
                      @endphp
                      <div class="card mb-3 shadow-none border">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-white">
                          <div>
                            <h6 class="mb-1">{{ $attachmentInstance->submissionForm->name ?? 'Attachment Form' }}</h6>
                            <small class="text-muted">{{ $attachmentInstance->getDocumentControlNumber() ?? $attachmentInstance->form_number ?? 'Pending' }}</small>
                          </div>
                          <span class="badge badge-{{ $attachmentInstance->getStatusBadgeColor() }}">
                            {{ ucfirst(str_replace('_', ' ', $attachmentInstance->status ?? 'submitted')) }}
                          </span>
                        </div>
                        <div class="card-body pb-0">
                          @include('submission-forms.partials.simple-form-display-clinical', ['instance' => $attachmentInstance, 'formData' => $attachmentFormData])
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif

              @if($workflowForms->count() > 0)
                <div class="card mt-3 border-primary">
                  <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <h6 class="mb-0 text-primary">
                      <i class="mdi mdi-file-document-multiple-outline mr-1"></i> Workflow Decision Forms
                    </h6>
                    <span class="badge badge-primary">{{ $workflowForms->count() }}</span>
                  </div>
                  <div class="card-body">
                    <p class="text-muted mb-3">Laboratory Analysis Acceptance and Sample Rejection forms linked to this request.</p>
                    <div class="table-responsive">
                      <table class="table table-sm table-hover mb-0">
                        <thead>
                          <tr>
                            <th>Form Type</th>
                            <th>Reference</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($workflowForms as $workflowForm)
                            <tr>
                              <td>
                                {{ $workflowForm->form_type === 'laboratory_analysis_acceptance' ? 'Laboratory Analysis Acceptance Form' : 'Sample Rejection Form' }}
                              </td>
                              <td>{{ $workflowForm->request_reference ?: ($workflowForm->batch_code ?: '—') }}</td>
                              <td>{{ optional($workflowForm->submitted_at)->format('Y-m-d H:i') ?: optional($workflowForm->created_at)->format('Y-m-d H:i') }}</td>
                              <td>
                                @if($workflowForm->pdf_path)
                                  <a href="{{ $workflowForm->pdf_path }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="mdi mdi-file-pdf-box"></i> View PDF
                                  </a>
                                @else
                                  <span class="text-muted small">PDF unavailable</span>
                                @endif
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              @endif

              <!-- Sample Creation Actions -->
              @if($linkedAttachmentInstances->count() === 0)
                @include('submission-forms.partials.sample-creation-actions', ['instance' => $instance, 'linkedBatchesOutOfSyncWithForm' => $linkedBatchesOutOfSyncWithForm ?? false])
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
                        <div class="mt-4" id="audit-trail">
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
/* Imara Clinical Design System - Submission Form Instance View */

/* Smooth scroll behavior for section navigation */
html {
  scroll-behavior: smooth;
}

/* Navigation chips hover state */
.badge {
  transition: all 0.2s ease;
}

.badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Section chips link styling */
a.badge {
  cursor: pointer;
  text-decoration: none !important;
}

a.badge:hover {
  opacity: 0.9;
}

/* KPI Strip responsive adjustments */
@media (max-width: 768px) {
  .badge-pill {
    font-size: 0.7rem !important;
  }
}

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

/* Summary section improvements - Imara design */
.summary-section {
  background: #ffffff;
  border-radius: 4px;
  padding: 1.5rem;
  transition: box-shadow 0.2s ease;
}

.summary-section:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.summary-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  color: #414754;
  margin-bottom: 0.5rem;
}

.summary-value {
  color: #191c1d;
  font-size: 0.95rem;
}

/* Improved visual hierarchy for form data sections */
#form-sections .card {
  border: none;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
  border-radius: 4px;
}

#form-sections .card-header {
  background: #f3f4f5;
  border-bottom: 1px solid #e1e3e4;
  border-radius: 4px 4px 0 0;
}

/* Tonal layering for better visual hierarchy */
.bg-light {
  background-color: #f8f9fa !important;
}

/* Linked batches warning card styling */
.alert-warning {
  background-color: #fffbf0;
  border: 1px solid #ffe0b2;
  border-radius: 4px;
  color: #7c2e00;
}

/* Sticky action toolbar placeholder (for future enhancement) */
.sticky-actions {
  position: sticky;
  top: 100px;
  z-index: 100;
  background: #ffffff;
  padding: 1rem;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
}
</style>
@endsection

