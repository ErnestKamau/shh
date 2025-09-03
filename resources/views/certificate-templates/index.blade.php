@extends('layouts.lab.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Certificate Templates</title>
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
          'link' => route('certificate-templates.index'),
          'name' => 'Certificate Templates',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <h2>
        <i class="mdi mdi-certificate"></i> Certificate Templates
      </h2>
      @can('create', App\Models\CertificateTemplate::class)
        <a href="{{ route('certificate-templates.create') }}" class="btn btn-primary">
          <i class="mdi mdi-plus"></i> Create Template
        </a>
      @endcan
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-light p-4 mb-3">
      <form method="GET" action="{{ route('certificate-templates.index') }}" class="row">
        <div class="col-md-3">
          <input type="text" name="search" class="form-control form-control-sm" 
                 placeholder="Search templates..." value="{{ request('search') }}">
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
          <a href="{{ route('certificate-templates.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="mdi mdi-refresh"></i> Clear
          </a>
        </div>
        <div class="col-md-5 text-right">
          <small class="text-muted">{{ $templates->total() }} template(s) found</small>
        </div>
      </form>
    </div>

    <!-- Templates Table -->
    <div class="table-responsive bg-light p-4">
      @if($templates->count() > 0)
        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
          <thead class="bg-light p-2">
            <tr>
              <th>Name</th>
              <th>Description</th>
              <th>Sections</th>
              <th>Version</th>
              <th>Status</th>
              <th>Created By</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($templates as $template)
              <tr>
                <td>
                  <strong>{{ $template->name }}</strong>
                  @if($template->page_settings)
                    <br>
                    <small class="text-muted">
                      {{ $template->page_settings['page_size'] ?? 'A4' }} - 
                      {{ ucfirst($template->page_settings['orientation'] ?? 'portrait') }}
                    </small>
                  @endif
                </td>
                <td>
                  <div style="max-width: 200px;">
                    {{ Str::limit($template->description, 100) }}
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge badge-info">{{ $template->sections_count }}</span>
                </td>
                <td class="text-center">
                  <span class="badge badge-secondary">v{{ $template->version }}</span>
                </td>
                <td>
                  <div>
                    @if($template->is_published)
                      <span class="badge badge-success">Published</span>
                    @else
                      <span class="badge badge-warning">Draft</span>
                    @endif
                  </div>
                  <div class="mt-1">
                    @if($template->is_active)
                      <span class="badge badge-outline-success">Active</span>
                    @else
                      <span class="badge badge-outline-danger">Inactive</span>
                    @endif
                  </div>
                </td>
                <td>
                  {{ $template->creator->name ?? 'Unknown' }}
                </td>
                <td>
                  <small>{{ $template->created_at->format('M d, Y') }}</small>
                </td>
                <td nowrap>
                  <div class="btn-group" role="group">
                    @can('view', $template)
                      <a href="{{ route('certificate-templates.show', $template) }}" 
                         class="btn btn-sm btn-outline-primary" title="View">
                        <i class="mdi mdi-eye"></i>
                      </a>
                    @endcan
                    
                    @can('update', $template)
                      <a href="{{ route('certificate-templates.edit', $template) }}" 
                         class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="mdi mdi-pencil"></i>
                      </a>
                    @endcan
                    
                    @can('preview', $template)
                      <a href="{{ route('certificate-templates.preview', $template) }}" 
                         class="btn btn-sm btn-outline-info" title="Preview">
                        <i class="mdi mdi-eye-outline"></i>
                      </a>
                    @endcan
                    
                    @can('clone', $template)
                      <form method="POST" action="{{ route('certificate-templates.clone', $template) }}" 
                            style="display: inline;" 
                            onsubmit="return confirm('Are you sure you want to clone this template?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Clone">
                          <i class="mdi mdi-content-copy"></i>
                        </button>
                      </form>
                    @endcan
                    
                    @can('publish', $template)
                      <form method="POST" action="{{ route('certificate-templates.toggle-published', $template) }}" 
                            style="display: inline;">
                        @csrf
                        <button type="submit" 
                                class="btn btn-sm {{ $template->is_published ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                title="{{ $template->is_published ? 'Unpublish' : 'Publish' }}">
                          <i class="mdi {{ $template->is_published ? 'mdi-eye-off' : 'mdi-publish' }}"></i>
                        </button>
                      </form>
                    @endcan
                    
                    @can('export', $template)
                      <a href="{{ route('certificate-templates.export', $template) }}" 
                         class="btn btn-sm btn-outline-info" title="Export">
                        <i class="mdi mdi-download"></i>
                      </a>
                    @endcan
                    
                    @can('managePermissions', $template)
                      <a href="{{ route('certificate-templates.permissions', $template) }}" 
                         class="btn btn-sm btn-outline-dark" title="Permissions">
                        <i class="mdi mdi-account-key"></i>
                      </a>
                    @endcan
                    
                    @can('delete', $template)
                      <form method="POST" action="{{ route('certificate-templates.destroy', $template) }}" 
                            style="display: inline;" 
                            onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                          <i class="mdi mdi-delete"></i>
                        </button>
                      </form>
                    @endcan
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
              Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{ $templates->total() }} results
            </small>
          </div>
          <div>
            {{ $templates->links() }}
          </div>
        </div>
      @else
        <div class="text-center py-5">
          <i class="mdi mdi-certificate" style="font-size: 4rem; color: #ccc;"></i>
          <h4 class="text-muted mt-3">No certificate templates found</h4>
          <p class="text-muted">
            @if(request()->hasAny(['search', 'status', 'creator']))
              Try adjusting your search criteria or 
              <a href="{{ route('certificate-templates.index') }}">clear filters</a>.
            @else
              Get started by creating your first certificate template.
            @endif
          </p>
          
          @can('create', App\Models\CertificateTemplate::class)
            <a href="{{ route('certificate-templates.create') }}" class="btn btn-primary mt-3">
              <i class="mdi mdi-plus"></i> Create Your First Template
            </a>
          @endcan
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

@section('scripts')
<script>
  // Auto-hide alerts after 5 seconds
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);
</script>
@endsection