@extends('layouts.lab.layout.app')

@section('title2')
  <title>{{ $certificateTemplate->name }} - Certificate Template</title>
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
        ),
        array(
          'link' => '#',
          'name' => $certificateTemplate->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <div>
        <h2>
          <i class="mdi mdi-certificate"></i> {{ $certificateTemplate->name }}
          <small class="text-muted">v{{ $certificateTemplate->version }}</small>
        </h2>
        @if($certificateTemplate->description)
          <p class="text-muted mb-0">{{ $certificateTemplate->description }}</p>
        @endif
      </div>
      <div class="btn-group" role="group">
        <a href="{{ route('certificate-templates.index') }}" class="btn btn-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Templates
        </a>
        
        @can('update', $certificateTemplate)
          <a href="{{ route('certificate-templates.edit', $certificateTemplate) }}" class="btn btn-warning">
            <i class="mdi mdi-pencil"></i> Edit
          </a>
        @endcan
        
        @can('build', $certificateTemplate)
          <a href="{{ route('certificate-templates.builder', $certificateTemplate) }}" class="btn btn-primary">
            <i class="mdi mdi-view-dashboard"></i> Builder
          </a>
        @endcan
      </div>
    </div>

    <div class="row">
      <!-- Template Status and Actions -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="mdi mdi-information"></i> Template Overview
            </h5>
            <div>
              @if($certificateTemplate->is_published)
                <span class="badge badge-success">Published</span>
              @else
                <span class="badge badge-warning">Draft</span>
              @endif
              
              @if($certificateTemplate->is_active)
                <span class="badge badge-outline-success">Active</span>
              @else
                <span class="badge badge-outline-danger">Inactive</span>
              @endif
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <h6>Template Statistics</h6>
                <ul class="list-unstyled">
                  <li><strong>Sections:</strong> {{ $statistics['total_sections'] }}</li>
                  <li><strong>Elements:</strong> {{ $statistics['total_elements'] }}</li>
                  <li><strong>Created:</strong> {{ $statistics['created_at']->format('M d, Y H:i') }}</li>
                  <li><strong>Last Modified:</strong> {{ $statistics['updated_at']->format('M d, Y H:i') }}</li>
                  <li><strong>Created By:</strong> {{ $certificateTemplate->creator->name ?? 'Unknown' }}</li>
                </ul>
              </div>
              <div class="col-md-6">
                <?php 
                  $pageSettings = $certificateTemplate->getPageSettings();
                  $headerSettings = $certificateTemplate->getHeaderSettings();
                  $footerSettings = $certificateTemplate->getFooterSettings();
                ?>
                <h6>Page Configuration</h6>
                <ul class="list-unstyled">
                  <li><strong>Page Size:</strong> {{ $pageSettings['page_size'] }}</li>
                  <li><strong>Orientation:</strong> {{ ucfirst($pageSettings['orientation']) }}</li>
                  <li><strong>Margins:</strong> 
                    {{ $pageSettings['margins']['top'] }} / 
                    {{ $pageSettings['margins']['right'] }} / 
                    {{ $pageSettings['margins']['bottom'] }} / 
                    {{ $pageSettings['margins']['left'] }}
                  </li>
                  <li><strong>Header:</strong> {{ $headerSettings['enabled'] ? 'Enabled' : 'Disabled' }}</li>
                  <li><strong>Footer:</strong> {{ $footerSettings['enabled'] ? 'Enabled' : 'Disabled' }}</li>
                </ul>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-4">
              <h6>Quick Actions</h6>
              <div class="btn-group" role="group">
                @can('preview', $certificateTemplate)
                  <a href="{{ route('certificate-templates.preview', $certificateTemplate) }}" 
                     class="btn btn-sm btn-outline-info">
                    <i class="mdi mdi-eye-outline"></i> Preview
                  </a>
                @endcan
                
                @can('clone', $certificateTemplate)
                  <form method="POST" action="{{ route('certificate-templates.clone', $certificateTemplate) }}" 
                        style="display: inline;" 
                        onsubmit="return confirm('Are you sure you want to clone this template?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                      <i class="mdi mdi-content-copy"></i> Clone
                    </button>
                  </form>
                @endcan
                
                @can('publish', $certificateTemplate)
                  <form method="POST" action="{{ route('certificate-templates.toggle-published', $certificateTemplate) }}" 
                        style="display: inline;">
                    @csrf
                    <button type="submit" 
                            class="btn btn-sm {{ $certificateTemplate->is_published ? 'btn-outline-danger' : 'btn-outline-success' }}">
                      <i class="mdi {{ $certificateTemplate->is_published ? 'mdi-eye-off' : 'mdi-publish' }}"></i>
                      {{ $certificateTemplate->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                  </form>
                @endcan
                
                @can('export', $certificateTemplate)
                  <a href="{{ route('certificate-templates.export', $certificateTemplate) }}" 
                     class="btn btn-sm btn-outline-info">
                    <i class="mdi mdi-download"></i> Export
                  </a>
                @endcan
                
                @can('managePermissions', $certificateTemplate)
                  <a href="{{ route('certificate-templates.permissions', $certificateTemplate) }}" 
                     class="btn btn-sm btn-outline-dark">
                    <i class="mdi mdi-account-key"></i> Permissions
                  </a>
                @endcan
              </div>
            </div>
          </div>
        </div>

        <!-- Template Structure -->
        @if($certificateTemplate->sections->count() > 0)
          <div class="card mt-4">
            <div class="card-header">
              <h5 class="mb-0">
                <i class="mdi mdi-file-tree"></i> Template Structure
              </h5>
            </div>
            <div class="card-body">
              <div class="template-structure">
                @foreach($certificateTemplate->rootSections as $section)
                  @include('certificate-templates.partials.section-tree', ['section' => $section, 'level' => 0])
                @endforeach
              </div>
            </div>
          </div>
        @else
          <div class="card mt-4">
            <div class="card-body text-center py-5">
              <i class="mdi mdi-file-tree" style="font-size: 3rem; color: #ccc;"></i>
              <h5 class="text-muted mt-3">No sections added yet</h5>
              <p class="text-muted">
                This template doesn't have any sections. Use the template builder to add sections and elements.
              </p>
              @can('build', $certificateTemplate)
                <a href="{{ route('certificate-templates.builder', $certificateTemplate) }}" class="btn btn-primary">
                  <i class="mdi mdi-view-dashboard"></i> Open Builder
                </a>
              @endcan
            </div>
          </div>
        @endif
      </div>

      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Template Actions -->
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="mdi mdi-cog"></i> Template Actions
            </h5>
          </div>
          <div class="card-body">
            @if(!$certificateTemplate->is_published && $certificateTemplate->sections->count() == 0)
              <div class="alert alert-warning">
                <i class="mdi mdi-alert"></i>
                <strong>Template is empty!</strong><br>
                Add sections and elements before publishing.
              </div>
            @endif

            @if($certificateTemplate->is_published && !$certificateTemplate->is_active)
              <div class="alert alert-info">
                <i class="mdi mdi-information"></i>
                <strong>Template is published but inactive.</strong><br>
                Activate it to use for report generation.
              </div>
            @endif

            <div class="list-group list-group-flush">
              @can('build', $certificateTemplate)
                <a href="{{ route('certificate-templates.builder', $certificateTemplate) }}" 
                   class="list-group-item list-group-item-action">
                  <i class="mdi mdi-view-dashboard"></i> Template Builder
                  <small class="text-muted d-block">Design your template layout</small>
                </a>
              @endcan
              
              @can('preview', $certificateTemplate)
                <a href="{{ route('certificate-templates.preview', $certificateTemplate) }}" 
                   class="list-group-item list-group-item-action">
                  <i class="mdi mdi-eye-outline"></i> Preview Template
                  <small class="text-muted d-block">See how your template will look</small>
                </a>
              @endcan
              
              @can('generate', $certificateTemplate)
                @if($certificateTemplate->isPublishedAndActive())
                  <a href="#" class="list-group-item list-group-item-action">
                    <i class="mdi mdi-file-pdf"></i> Generate Report
                    <small class="text-muted d-block">Create PDF from submission data</small>
                  </a>
                @endif
              @endcan
            </div>
          </div>
        </div>

        <!-- Version Management -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="mdi mdi-source-branch"></i> Version Management
            </h5>
          </div>
          <div class="card-body">
            <p><strong>Current Version:</strong> {{ $certificateTemplate->version }}</p>
            
            @can('clone', $certificateTemplate)
              <form method="POST" action="{{ route('certificate-templates.clone', $certificateTemplate) }}" 
                    onsubmit="return confirm('This will create a new version of the template. Continue?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary btn-block">
                  <i class="mdi mdi-content-copy"></i> Create New Version
                </button>
              </form>
            @endcan
            
            <small class="text-muted">
              Creating a new version will duplicate this template with version {{ $certificateTemplate->getNextVersion() }}.
            </small>
          </div>
        </div>

        <!-- Danger Zone -->
        @can('delete', $certificateTemplate)
          <div class="card mt-3 border-danger">
            <div class="card-header bg-danger text-white">
              <h5 class="mb-0">
                <i class="mdi mdi-alert"></i> Danger Zone
              </h5>
            </div>
            <div class="card-body">
              <p class="text-muted">
                Once you delete a template, there is no going back. Please be certain.
              </p>
              
              @if($certificateTemplate->is_published)
                <p class="text-warning">
                  <i class="mdi mdi-alert"></i>
                  Cannot delete published template. Unpublish it first.
                </p>
              @else
                <form method="POST" action="{{ route('certificate-templates.destroy', $certificateTemplate) }}" 
                      onsubmit="return confirm('Are you absolutely sure? This action cannot be undone.')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">
                    <i class="mdi mdi-delete"></i> Delete Template
                  </button>
                </form>
              @endif
            </div>
          </div>
        @endcan
      </div>
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