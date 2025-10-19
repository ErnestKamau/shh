@extends('layouts.lab.layout.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
          <i class="mdi mdi-file-document"></i> Submission Details
        </h1>
        <div>
          <a href="{{ route('submission-forms.instances.print', [$instance->submissionForm, $instance]) }}" 
             class="btn btn-outline-info" 
             target="_blank">
            <i class="mdi mdi-printer"></i> Print Form
          </a>
          <a href="{{ route('form-instances.export', $instance) }}" class="btn btn-outline-info">
            <i class="mdi mdi-download"></i> Export
          </a>
          <a href="{{ route('form-instances.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left"></i> Back to Submissions
          </a>
        </div>
      </div>

      <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
          <!-- Form Information -->
          <div class="card mb-4">
            <div class="card-header">
              <h5 class="mb-0">
                <i class="mdi mdi-information"></i> Form Information
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <strong>Form Name:</strong><br>
                  <span class="text-muted">{{ $instance->submissionForm->name }}</span>
                </div>
                <div class="col-md-6">
                  <strong>Submission ID:</strong><br>
                  <span class="text-muted">#{{ $instance->id }}</span>
                </div>
              </div>
              @if($instance->submissionForm->description)
                <div class="mt-3">
                  <strong>Form Description:</strong><br>
                  <span class="text-muted">{{ $instance->submissionForm->description }}</span>
                </div>
              @endif
            </div>
          </div>

          <!-- Submitted Data -->
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">
                <i class="mdi mdi-form-select"></i> Submitted Data
              </h5>
            </div>
            <div class="card-body">
              @if($instance->values->count() > 0)
                @php
                  $groupedValues = $instance->values->groupBy('element.holder.section.name');
                @endphp
                
                @foreach($groupedValues as $sectionName => $sectionValues)
                  <div class="mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                      <i class="mdi mdi-folder"></i> {{ $sectionName }}
                    </h6>
                    
                    <div class="row">
                      @foreach($sectionValues as $value)
                        <div class="col-md-6 mb-3">
                          <div class="border rounded p-3 h-100">
                            <strong class="d-block mb-2">{{ $value->element->label }}</strong>
                            
                            @if($value->element->element_type === 'file')
                              @php
                                $fileData = json_decode($value->value, true);
                              @endphp
                              @if($fileData && isset($fileData['original_name']))
                                <div class="d-flex align-items-center">
                                  <i class="mdi mdi-file-outline me-2"></i>
                                  <div>
                                    <div>{{ $fileData['original_name'] }}</div>
                                    <small class="text-muted">
                                      {{ number_format($fileData['size'] / 1024, 2) }} KB
                                    </small>
                                  </div>
                                </div>
                                @if(Storage::disk('public')->exists($fileData['path']))
                                  <a href="{{ Storage::url($fileData['path']) }}" 
                                     class="btn btn-sm btn-outline-primary mt-2" 
                                     target="_blank">
                                    <i class="mdi mdi-download"></i> Download
                                  </a>
                                @endif
                              @else
                                <span class="text-muted">File not available</span>
                              @endif
                            @elseif($value->element->element_type === 'checkbox')
                              @php
                                $checkboxValues = is_array($value->value) ? $value->value : json_decode($value->value, true);
                              @endphp
                              @if($checkboxValues)
                                <ul class="list-unstyled mb-0">
                                  @foreach($checkboxValues as $checkValue)
                                    <li><i class="mdi mdi-check text-success"></i> {{ $checkValue }}</li>
                                  @endforeach
                                </ul>
                              @else
                                <span class="text-muted">No options selected</span>
                              @endif
                            @elseif($value->element->element_type === 'textarea')
                              <div class="border rounded p-2 bg-light">
                                {!! nl2br(e($value->value)) !!}
                              </div>
                            @else
                              <span class="text-muted">{{ $value->value ?: 'No value provided' }}</span>
                            @endif
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endforeach
              @else
                <div class="text-center py-4">
                  <i class="mdi mdi-information-outline display-4 text-muted"></i>
                  <h6 class="text-muted mt-2">No Data Submitted</h6>
                  <p class="text-muted">This submission contains no field values.</p>
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
          <!-- Status Management -->
          <div class="card mb-4">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-cog"></i> Status Management
              </h6>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('form-instances.update-status', $instance) }}">
                @csrf
                @method('PATCH')
                
                <div class="mb-3">
                  <label for="status" class="form-label">Status</label>
                  <select name="status" id="status" class="form-select" required>
                    <option value="submitted" {{ $instance->status === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="under_review" {{ $instance->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="approved" {{ $instance->status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $instance->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                  </select>
                </div>
                
                <div class="mb-3">
                  <label for="review_notes" class="form-label">Review Notes</label>
                  <textarea name="review_notes" id="review_notes" class="form-control" rows="3" 
                            placeholder="Add notes about this review...">{{ $instance->review_notes }}</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                  <i class="mdi mdi-check"></i> Update Status
                </button>
              </form>
            </div>
          </div>

          <!-- Submission Details -->
          <div class="card mb-4">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-information"></i> Submission Details
              </h6>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <strong>Current Status:</strong><br>
                @php
                  $statusClasses = [
                      'submitted' => 'bg-info',
                      'under_review' => 'bg-warning',
                      'approved' => 'bg-success',
                      'rejected' => 'bg-danger'
                  ];
                  $statusClass = $statusClasses[$instance->status] ?? 'bg-secondary';
                @endphp
                <span class="badge {{ $statusClass }}">
                  {{ ucwords(str_replace('_', ' ', $instance->status)) }}
                </span>
              </div>
              
              <div class="mb-3">
                <strong>Submitted By:</strong><br>
                @if($instance->submittedBy)
                  <span class="text-muted">{{ $instance->submittedBy->name }}</span><br>
                  <small class="text-muted">{{ $instance->submittedBy->email }}</small>
                @else
                  <span class="text-muted">Unknown User</span>
                @endif
              </div>
              
              <div class="mb-3">
                <strong>Submitted At:</strong><br>
                <span class="text-muted">{{ $instance->submitted_at->format('M j, Y g:i A') }}</span>
              </div>
              
              @if($instance->reviewed_by)
                <div class="mb-3">
                  <strong>Reviewed By:</strong><br>
                  <span class="text-muted">{{ $instance->reviewedBy->name ?? 'Unknown' }}</span><br>
                  @if($instance->reviewed_at)
                    <small class="text-muted">{{ $instance->reviewed_at->format('M j, Y g:i A') }}</small>
                  @endif
                </div>
              @endif
              
              @if($instance->approved_by)
                <div class="mb-3">
                  <strong>{{ $instance->status === 'approved' ? 'Approved' : 'Rejected' }} By:</strong><br>
                  <span class="text-muted">{{ $instance->approvedBy->name ?? 'Unknown' }}</span><br>
                  @if($instance->approved_at)
                    <small class="text-muted">{{ $instance->approved_at->format('M j, Y g:i A') }}</small>
                  @endif
                </div>
              @endif
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-lightning-bolt"></i> Quick Actions
              </h6>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('submission-forms.show', $instance->submissionForm) }}" 
                   class="btn btn-outline-primary btn-sm">
                  <i class="mdi mdi-form-select"></i> View Form Template
                </a>
                
                <a href="{{ route('form-instances.export', $instance) }}" 
                   class="btn btn-outline-info btn-sm">
                  <i class="mdi mdi-download"></i> Export Data
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection