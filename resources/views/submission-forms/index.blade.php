@extends('layouts.lab.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Submission Forms</title>
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
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <h2>
        <i class="mdi mdi-form-select"></i> Submission Forms
      </h2>
      <a href="{{ route('submission-forms.create') }}" class="btn btn-primary">
        <i class="mdi mdi-plus"></i> Create Form
      </a>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-light p-4 mb-3">
      <form method="GET" action="{{ route('submission-forms.index') }}" class="row">
        <div class="col-md-3">
          <input type="text" name="search" class="form-control form-control-sm" 
                 placeholder="Search forms..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
          <select name="status" class="form-control form-control-sm">
            <option value="">All Status</option>
            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="mdi mdi-magnify"></i> Search
          </button>
          <a href="{{ route('submission-forms.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="mdi mdi-refresh"></i> Clear
          </a>
        </div>
        <div class="col-md-5 text-right">
          <small class="text-muted">{{ $forms->total() }} form(s) found</small>
        </div>
      </form>
    </div>

    <!-- Forms Table -->
    <div class="table-responsive bg-light p-4">
      @if($forms->count() > 0)
        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
          <thead class="bg-light p-2">
            <tr>
              <th>Name</th>
              <th>Description</th>
              <th>Submission Start No.</th>
              <th>Lab Sections</th>
              <th>Sections</th>
              <th>Instances</th>
              <th>Status</th>
              <th>Created By</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($forms as $form)
              <tr>
                <td>
                  <strong>{{ $form->name }}</strong>
                  <br>
                  <small class="text-muted">v{{ $form->version }}</small>
                </td>
                <td>
                  <div style="max-width: 200px;">
                    {{ Str::limit($form->description, 100) }}
                  </div>
                </td>
                <td class="text-center">
                  {{ $form->start_submission_number }}
                 </td> 
                 <td>
                   @foreach($form->sampleAnalysisStages as $stage)
                     <span class="badge badge-outline-primary mb-1">{{ $stage->name }}</span>
                   @endforeach
                 </td> 
                <td class="text-center">
                  <span class="badge badge-info">{{ $form->sections_count }}</span>
                </td>
               
                <td class="text-center">
                  <span class="badge badge-secondary">{{ $form->instances_count }}</span>
                </td>
                <td>
                  <div>
                    @if($form->is_published)
                      <span class="badge badge-success">Published</span>
                    @else
                      <span class="badge badge-warning">Draft</span>
                    @endif
                  </div>
                  <div class="mt-1">
                    @if($form->is_active)
                      <span class="badge badge-outline-success">Active</span>
                    @else
                      <span class="badge badge-outline-danger">Inactive</span>
                    @endif
                  </div>
                </td>
                <td>
                  {{ $form->creator->name ?? 'Unknown' }}
                </td>
                <td>
                  <small>{{ $form->created_at->format('M d, Y') }}</small>
                </td>
                <td nowrap>
                  <div class="btn-group" role="group">
                    <a href="{{ route('submission-forms.show', $form) }}" 
                       class="btn btn-sm btn-outline-primary" title="View">
                      <i class="mdi mdi-eye"></i>
                    </a>
                    
                    <a href="{{ route('submission-forms.edit', $form) }}" 
                       class="btn btn-sm btn-outline-warning" title="Edit">
                      <i class="mdi mdi-pencil"></i>
                    </a>
                    
                    <a href="{{ route('submission-forms.preview', $form) }}" 
                       class="btn btn-sm btn-outline-info" title="Preview">
                      <i class="mdi mdi-eye-outline"></i>
                    </a>
                    
                    <form method="POST" action="{{ route('submission-forms.clone', $form) }}" 
                          style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to clone this form?')">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-secondary" title="Clone">
                        <i class="mdi mdi-content-copy"></i>
                      </button>
                    </form>
                    
                    <form method="POST" action="{{ route('submission-forms.toggle-published', $form) }}" 
                          style="display: inline;">
                      @csrf
                      <button type="submit" 
                              class="btn btn-sm {{ $form->is_published ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                              title="{{ $form->is_published ? 'Unpublish' : 'Publish' }}">
                        <i class="mdi {{ $form->is_published ? 'mdi-eye-off' : 'mdi-publish' }}"></i>
                      </button>
                    </form>
                    
                    <a href="{{ route('submission-forms.export', $form) }}" 
                       class="btn btn-sm btn-outline-info" title="Export">
                      <i class="mdi mdi-download"></i>
                    </a>
                    
                    <form method="POST" action="{{ route('submission-forms.destroy', $form) }}" 
                          style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to delete this form? This action cannot be undone.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="mdi mdi-delete"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div>
            <small class="text-muted">
              Showing {{ $forms->firstItem() }} to {{ $forms->lastItem() }} of {{ $forms->total() }} results
            </small>
          </div>
          <div>
            {{ $forms->links() }}
          </div>
        </div>
      @else
        <div class="text-center py-5">
          <i class="mdi mdi-form-select" style="font-size: 4rem; color: #ccc;"></i>
          <h4 class="text-muted mt-3">No submission forms found</h4>
          <p class="text-muted">
            @if(request()->hasAny(['search', 'status', 'creator']))
              Try adjusting your search criteria or 
              <a href="{{ route('submission-forms.index') }}">clear filters</a>.
            @else
              Get started by creating your first submission form.
            @endif
          </p>
          
            <a href="{{ route('submission-forms.create') }}" class="btn btn-primary mt-3">
              <i class="mdi mdi-plus"></i> Create Your First Form
            </a>
          
        </div>
      @endif
    </div>
  </main>

  <!-- Success/Error Messages -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif
@endsection

@section('script2')
<script>
  // Auto-hide alerts after 5 seconds
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);
</script>
@endsection