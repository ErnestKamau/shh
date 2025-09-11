@extends('layouts.lab.layout.app')

@section('title2')
<title>{{ $template->name }} | Lab Management</title>
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
            'name' => $template->name,
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="bg-light p-4">
        <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-1">
                                <i class="mdi mdi-certificate"></i> {{ $template->name }}
                                <small class="text-muted">v{{ $template->version }}</small>
                            </h5>
                            <p class="text-muted mb-0 small">{{ $template->description ?: 'No description provided' }}</p>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <a href="{{ route('certificate-templates.builder', $template) }}" class="btn btn-primary">
                                    <i class="mdi mdi-pencil"></i> Open Builder
                                </a>
                                <a href="{{ route('certificate-templates.edit', $template) }}" class="btn btn-warning">
                                    <i class="mdi mdi-edit"></i> Edit Settings
                                </a>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" 
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('certificate-templates.preview', $template) }}">
                                            <i class="mdi mdi-eye-outline"></i> Preview
                                        </a>
                                        <a class="dropdown-item" href="{{ route('certificate-templates.pdf-preview', $template) }}" target="_blank">
                                            <i class="mdi mdi-file-pdf"></i> PDF Preview
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item toggle-published" 
                                                data-id="{{ $template->id }}" 
                                                data-published="{{ $template->is_published }}">
                                            <i class="mdi mdi-{{ $template->is_published ? 'eye-off' : 'eye' }}"></i> 
                                            {{ $template->is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                        <button class="dropdown-item toggle-active" 
                                                data-id="{{ $template->id }}" 
                                                data-active="{{ $template->is_active }}">
                                            <i class="mdi mdi-{{ $template->is_active ? 'pause' : 'play' }}"></i> 
                                            {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('certificate-templates.duplicate', $template) }}"
                                           onclick="return confirm('Are you sure you want to duplicate this template?')">
                                            <i class="mdi mdi-content-copy"></i> Duplicate
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Template Status -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title text-muted">Status</h5>
                                            <div class="d-flex justify-content-center gap-2">
                                                <span class="badge badge-{{ $template->is_published ? 'success' : 'secondary' }} badge-lg">
                                                    {{ $template->is_published ? 'Published' : 'Draft' }}
                                                </span>
                                                <span class="badge badge-{{ $template->is_active ? 'primary' : 'warning' }} badge-lg">
                                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title text-muted">Statistics</h5>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <h4 class="text-primary mb-0">{{ $template->sections_count }}</h4>
                                                    <small class="text-muted">Sections</small>
                                                </div>
                                                <div class="col-4">
                                                    <h4 class="text-info mb-0">{{ $template->elements_count }}</h4>
                                                    <small class="text-muted">Elements</small>
                                                </div>
                                                <div class="col-4">
                                                    <h4 class="text-secondary mb-0">{{ $template->reports_count }}</h4>
                                                    <small class="text-muted">Reports</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Template Sections -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="mdi mdi-view-list"></i> Template Sections
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($template->sections->count() > 0)
                                        <div class="sections-tree">
                                            @foreach($template->rootSections as $section)
                                                @include('certificate-templates.partials.section-tree', ['section' => $section, 'level' => 0])
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="mdi mdi-view-list text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="text-muted mt-3">No sections yet</h5>
                                            <p class="text-muted">Start building your template by adding sections.</p>
                                            <a href="{{ route('certificate-templates.builder', $template) }}" class="btn btn-primary">
                                                <i class="mdi mdi-plus"></i> Add First Section
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Template Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Template Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td>{{ $template->creator->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $template->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Updated:</strong></td>
                                            <td>{{ $template->updated_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Version:</strong></td>
                                            <td>{{ $template->version }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Page Settings -->
                            @if($template->page_settings)
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Page Settings</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        @if(isset($template->page_settings['size']))
                                        <tr>
                                            <td><strong>Size:</strong></td>
                                            <td>{{ $template->page_settings['size'] }}</td>
                                        </tr>
                                        @endif
                                        @if(isset($template->page_settings['orientation']))
                                        <tr>
                                            <td><strong>Orientation:</strong></td>
                                            <td>{{ ucfirst($template->page_settings['orientation']) }}</td>
                                        </tr>
                                        @endif
                                        @if(isset($template->page_settings['margins']))
                                        <tr>
                                            <td><strong>Margins:</strong></td>
                                            <td>
                                                @if(is_array($template->page_settings['margins']))
                                                    {{ $template->page_settings['margins']['top'] ?? 0 }}mm (T), 
                                                    {{ $template->page_settings['margins']['right'] ?? 0 }}mm (R), 
                                                    {{ $template->page_settings['margins']['bottom'] ?? 0 }}mm (B), 
                                                    {{ $template->page_settings['margins']['left'] ?? 0 }}mm (L)
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            @endif

                            <!-- Recent Reports -->
                            @if($template->reports->count() > 0)
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Recent Reports</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        @foreach($template->reports->take(5) as $report)
                                        <div class="list-group-item px-0 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">{{ $report->submissionFormInstance->title ?? 'Report' }}</h6>
                                                    <small class="text-muted">{{ $report->created_at->format('M d, Y H:i') }}</small>
                                                </div>
                                                <span class="badge badge-{{ $report->is_completed ? 'success' : ($report->is_failed ? 'danger' : 'warning') }}">
                                                    {{ $report->status_label }}
                                                </span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @if($template->reports->count() > 5)
                                    <div class="text-center mt-2">
                                        <a href="#" class="btn btn-sm btn-outline-primary">View All Reports</a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>
@endsection

@push('styles')
<style>
    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
    
    .sections-tree {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .section-item {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
        background: #fff;
    }
    
    .section-item.level-1 {
        margin-left: 2rem;
        border-left: 3px solid #007bff;
    }
    
    .section-item.level-2 {
        margin-left: 4rem;
        border-left: 3px solid #28a745;
    }
    
    .section-item.level-3 {
        margin-left: 6rem;
        border-left: 3px solid #ffc107;
    }
    
    .section-header {
        padding: 0.75rem 1rem;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
    }
    
    .section-content {
        padding: 1rem;
    }
    
    .element-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
    }
    
    .element-type {
        font-weight: 600;
        color: #495057;
    }
    
    .element-content {
        color: #6c757d;
        font-size: 0.8rem;
    }
</style>
@endpush

@section('script2')
<script>
$(document).ready(function() {
    // Toggle section collapse
    $('.section-header').click(function() {
        const content = $(this).next('.section-content');
        const icon = $(this).find('.mdi-chevron-down, .mdi-chevron-right');
        
        content.slideToggle();
        icon.toggleClass('mdi-chevron-down mdi-chevron-right');
    });
    
    // Toggle Published Status
    $('.toggle-published').click(function() {
        const templateId = $(this).data('id');
        const isPublished = $(this).data('published');
        const button = $(this);
        
        $.ajax({
            url: `/certificate-templates/${templateId}/toggle-published`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update the badge
                    const badge = $('.badge-success, .badge-secondary').first();
                    if (response.is_published) {
                        badge.removeClass('badge-secondary').addClass('badge-success').text('Published');
                        button.find('i').removeClass('mdi-eye').addClass('mdi-eye-off');
                        button.html('<i class="mdi mdi-eye-off"></i> Unpublish');
                        button.data('published', true);
                    } else {
                        badge.removeClass('badge-success').addClass('badge-secondary').text('Draft');
                        button.find('i').removeClass('mdi-eye-off').addClass('mdi-eye');
                        button.html('<i class="mdi mdi-eye"></i> Publish');
                        button.data('published', false);
                    }
                    
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the template status.');
            }
        });
    });

    // Toggle Active Status
    $('.toggle-active').click(function() {
        const templateId = $(this).data('id');
        const isActive = $(this).data('active');
        const button = $(this);
        
        $.ajax({
            url: `/certificate-templates/${templateId}/toggle-active`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update the badge
                    const badge = $('.badge-primary, .badge-warning').first();
                    if (response.is_active) {
                        badge.removeClass('badge-warning').addClass('badge-primary').text('Active');
                        button.find('i').removeClass('mdi-play').addClass('mdi-pause');
                        button.html('<i class="mdi mdi-pause"></i> Deactivate');
                        button.data('active', true);
                    } else {
                        badge.removeClass('badge-primary').addClass('badge-warning').text('Inactive');
                        button.find('i').removeClass('mdi-pause').addClass('mdi-play');
                        button.html('<i class="mdi mdi-play"></i> Activate');
                        button.data('active', false);
                    }
                    
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the template status.');
            }
        });
    });
});
</script>
@endsection
