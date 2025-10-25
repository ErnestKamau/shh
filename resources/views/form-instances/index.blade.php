@extends('layouts.lab.layout.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
          <i class="mdi mdi-file-document-multiple"></i> Form Submissions
        </h1>
      </div>

      <!-- Filters -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('form-instances.index') }}" class="row g-3">
            <div class="col-md-3">
              <label for="form_id" class="form-label">Filter by Form</label>
              <select name="form_id" id="form_id" class="form-select">
                <option value="">All Forms</option>
                @foreach($forms as $form)
                  <option value="{{ $form->id }}" {{ request('form_id') == $form->id ? 'selected' : '' }}>
                    {{ $form->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="search" class="form-label">Search</label>
              <input type="text" name="search" id="search" class="form-control" 
                     placeholder="Search by form name or submitter..." 
                     value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                  <i class="mdi mdi-magnify"></i> Filter
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Results -->
      @if($instances->count() > 0)
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Form</th>
                    <th>Submitted By</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($instances as $instance)
                    <tr>
                      <td>
                        <div class="fw-bold">{{ $instance->submissionForm->name }}</div>
                        <small class="text-muted">ID: {{ $instance->id }}</small>
                      </td>
                      <td>
                        @if($instance->submittedBy)
                          <div>{{ $instance->submittedBy->name }}</div>
                          <small class="text-muted">{{ $instance->submittedBy->email }}</small>
                        @else
                          <span class="text-muted">Unknown User</span>
                        @endif
                      </td>
                      <td>
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
                      </td>
                      <td>
                        <div>{{ $instance->submitted_at->format('M j, Y') }}</div>
                        <small class="text-muted">{{ $instance->submitted_at->format('g:i A') }}</small>
                      </td>
                      <td nowrap>
                        <div class="btn-group" role="group">
                          <a href="{{ route('form-instances.show', $instance) }}" 
                             class="btn btn-sm btn-outline-primary" title="View">
                            <i class="mdi mdi-eye"></i>
                          </a>
                          <a href="{{ route('form-instances.export', $instance) }}" 
                             class="btn btn-sm btn-outline-info" title="Export">
                            <i class="mdi mdi-download"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          {{ $instances->links() }}
        </div>
      @else
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="mdi mdi-file-document-outline display-1 text-muted"></i>
            <h5 class="text-muted mt-3">No Submissions Found</h5>
            <p class="text-muted">
              @if(request()->hasAny(['search', 'status', 'form_id']))
                No submissions match your current filters.
              @else
                No form submissions have been received yet.
              @endif
            </p>
            @if(request()->hasAny(['search', 'status', 'form_id']))
              <a href="{{ route('form-instances.index') }}" class="btn btn-outline-primary">
                <i class="mdi mdi-filter-remove"></i> Clear Filters
              </a>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection