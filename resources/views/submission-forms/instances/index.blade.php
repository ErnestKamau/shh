@extends('layouts.lab.layout.app', ['select2'=>true])

@section('title2')
  <title>My Form Submissions</title>
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
          'name' => 'My Submissions',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <div>
        <h3>
          <i class="mdi mdi-file-document-multiple"></i> My Form Submissions
        </h3>
      </div>
      <div>
        <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-primary">
          <i class="mdi mdi-arrow-left"></i> Back to Forms
        </a>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-file-document-multiple"></i> Submission List
              </h6>
            </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Search</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="search" 
                                           name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="Search by form number, title, or form name...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                        <option value="in_review" {{ request('status') == 'in_review' ? 'selected' : '' }}>In Review</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <select class="form-control" id="priority" name="priority">
                                        <option value="">All Priorities</option>
                                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="mdi mdi-magnify"></i> Filter
                                        </button>
                                        <a href="{{ route('submission-forms.instances.index') }}" class="btn btn-outline-secondary">
                                            <i class="mdi mdi-refresh"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Instances Table -->
                    @if($instances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Form Number</th>
                                        <th>Form Name</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Submitted</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($instances as $instance)
                                        <tr>
                                            <td>
                                                <strong>{{ $instance->form_number }}</strong>
                                            </td>
                                            <td>
                                                <a href="{{ route('submission-forms.show', $instance->submissionForm) }}" 
                                                   class="text-decoration-none">
                                                    {{ $instance->submissionForm->name }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $instance->title ?: 'Untitled' }}
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $instance->getStatusBadgeColor() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
                                                </span>
                                                @if($instance->isOverdue())
                                                    <span class="badge badge-danger ml-1">Overdue</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $instance->getPriorityBadgeColor() }}">
                                                    {{ ucfirst($instance->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($instance->submitted_at)
                                                    {{ $instance->submitted_at->format('M d, Y H:i') }}
                                                @else
                                                    <span class="text-muted">Not submitted</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($instance->due_date)
                                                    {{ $instance->due_date->format('M d, Y') }}
                                                    @if($instance->isOverdue())
                                                        <i class="mdi mdi-alert text-danger"></i>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No due date</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($instance->isDraft())
                                                        <a href="{{ route('submission-forms.instances.fill', [$instance->submissionForm, $instance]) }}" 
                                                           class="btn btn-sm btn-primary" title="Continue Filling">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                        <form method="POST" 
                                                              action="{{ route('submission-forms.instances.destroy', [$instance->submissionForm, $instance]) }}" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this draft?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Draft">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ route('submission-forms.instances.show', [$instance->submissionForm, $instance]) }}" 
                                                           class="btn btn-sm btn-info" title="View">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $instances->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-file-document-outline" style="font-size: 4rem; color: #ccc;"></i>
                            <h4 class="mt-3 text-muted">No Form Submissions Found</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'status', 'priority']))
                                    No submissions match your current filters.
                                @else
                                    You haven't submitted any forms yet.
                                @endif
                            </p>
                            @if(!request()->hasAny(['search', 'status', 'priority']))
                                <a href="{{ route('submission-forms.index') }}" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Browse Available Forms
                                </a>
                            @endif
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
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#status, #priority').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
