@extends('layouts.documents.layout.app')

@section('title2')
    <title>Documents Dashboard - Imara LIMS</title>
    <style>
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            min-height: 70px;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .kpi-card-body {
            padding: 0.75rem;
        }

        .kpi-card-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            justify-content: space-between;
        }

        .kpi-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .kpi-card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
            color: #2d3748;
            line-height: 1;
        }

        .kpi-card-label {
            font-size: 1rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0;
        }

        .kpi-card-icon {
            font-size: 2rem;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .quick-actions-horizontal {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Ensure proper alignment for dashboard content */
        .documents-subtitle {
            margin-left: 0;
            padding-left: 0;
        }
        
        /* Fix breadcrumb alignment */
        .breadcrumb {
            margin-left: 0;
            padding-left: 0;
        }
        
        /* Ensure consistent spacing for all dashboard sections */
        .dashboard-section {
            margin-left: 0;
            padding-left: 0;
        }
    </style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/home',
            'name' => 'Home',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Documents Dashboard',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- View Toggle -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-book-open-page-variant"></i> Documents
                <span class="text-muted">- {{ optional(Auth::user()->department)->name ?? 'All Departments' }}</span>
            </h2>
        </div>
        <div class="d-flex" style="gap: 1rem;">
            <a href="{{ route('documents.dashboard') }}" class="btn btn-primary">
                <i class="mdi mdi-view-dashboard"></i> Dashboard
            </a>
            <a href="{{ route('documents.index') }}" class="btn btn-outline-primary">
                <i class="mdi mdi-view-list"></i> Table View
            </a>
            @if(Auth::user()->canAddDocuments())
            <a href="{{ route('documents.create') }}" class="btn btn-success">
                <i class="mdi mdi-plus"></i> Upload New Document
            </a>
            @endif
        </div>
    </div>
    


    <!-- Dashboard Subtitle -->
    <div class="documents-subtitle dashboard-section mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="text-muted mb-0">Comprehensive overview of document management system</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0">
                    <i class="mdi mdi-calendar"></i> 
                    {{ now()->format('l, F j, Y') }}
                </p>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4 dashboard-section">
        <div class="col-md-3">
            <div class="kpi-card h-100" style="border-left: 4px solid #667eea;">
                <div class="kpi-card-body h-100">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $totalDocuments }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-book-open-page-variant" style="color: #667eea;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row mt-2">
                            <p class="kpi-card-label">Total Documents</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card h-100" style="border-left: 4px solid #ffa726;">
                <div class="kpi-card-body h-100">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $totalDocumentTypes }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-tag" style="color: #ffa726;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row mt-2">
                            <p class="kpi-card-label">Document Types</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card h-100" style="border-left: 4px solid #4facfe;">
                <div class="kpi-card-body h-100">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $expiringDocuments }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-calendar-alert" style="color: #4facfe;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row mt-2">
                            <p class="kpi-card-label">Expiring in 30 Days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card h-100" style="border-left: 4px solid #ff6b6b;">
                <div class="kpi-card-body h-100">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $unpublishedDocuments }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-file-document-outline" style="color: #ff6b6b;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row mt-2">
                            <p class="kpi-card-label">Unpublished Docs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Row -->
    <div class="row mb-4 dashboard-section">
        <div class="col-12">
            <div class="quick-actions-horizontal" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-lightning-bolt"></i> Quick Actions
                </h5>
                <div class="row justify-content-center">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-view-list text-primary"></i> View All Documents
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('documents.types.index') }}" class="btn btn-sm btn-outline-primary action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-tag text-info"></i> Manage Document Types
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('documents.unpublished') }}" class="btn btn-sm btn-outline-primary action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-file-document-outline text-warning"></i> Unpublished Documents
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('documents.expired') }}" class="btn btn-sm btn-outline-primary action-btn quick-action-btn" style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-calendar-alert text-danger"></i> Expired Documents
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents by Type -->
    <div class="row dashboard-section">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title">Documents by Type</h4>
                            <a href="{{ route('documents.types.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-view-list"></i> View All Types ({{ $totalDocumentTypes }})
                            </a>
                    </div>
                    <div class="folder-grid">
                        @forelse($documentTypes->take(24) as $type)
                            @php
                                $count = $type->documents_count;
                            @endphp
                            <div class="folder-item" onclick="window.location.href='{{ route('documents.index', ['document_type_id' => $type->id]) }}'" title="{{ $type->name }} ({{ $count }} documents)">
                                <div class="folder-icon">
                                    <i class="mdi mdi-file-document-multiple"></i>
                                </div>
                                <div class="folder-name">{{ $type->name }}</div>
                                <div class="folder-count">{{ $count }} docs</div>
                                <div class="folder-label">{{ $type->name }}</div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center">
                                    <i class="mdi mdi-information-outline text-muted" style="font-size: 48px;"></i>
                                    <h5 class="text-muted mt-2">No document types found</h5>
                                    <p class="text-muted">Create document types to start organizing your documents.</p>
                                    <a href="{{ route('documents.types.create') }}" class="btn btn-primary">
                                        Create Document Type
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    
                    @if($totalDocumentTypes > 24)
                        <div class="text-center mt-3">
                            <p class="text-muted mb-0 small">Showing top 24 document types by document count</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Documents -->
    <div class="row dashboard-section">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title">Recent Documents</h4>
                        <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-centered table-hover mb-0 recent-documents-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Published</th>
                                    <th>Validity</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $user = Auth::user();
                                    $recentDocumentsQuery = \App\Models\Document::currentVersion()
                                        ->active()
                                        ->published()
                                        ->with(['documentType', 'creator']);
                                    
                                    // Admin users can see all documents
                                    if (!$user->hasRole('Admin')) {
                                        $recentDocumentsQuery->where(function($q) use ($user) {
                                            $q->where('publish_scope', 'all_departments')
                                              ->orWhere('publish_scope', 'all_roles')
                                              ->orWhere(function($subQ) use ($user) {
                                                  $subQ->where('publish_scope', 'department')
                                                       ->whereJsonContains('publish_targets', $user->department_id);
                                              })
                                              ->orWhere(function($subQ) use ($user) {
                                                  $userRoleIds = $user->roles->pluck('role_id')->toArray();
                                                  if (!empty($userRoleIds)) {
                                                      $subQ->where('publish_scope', 'role')
                                                           ->where(function($roleQ) use ($userRoleIds) {
                                                               foreach ($userRoleIds as $roleId) {
                                                                   $roleQ->orWhereJsonContains('publish_targets', $roleId);
                                                               }
                                                           });
                                                  }
                                              })
                                              ->orWhere(function($subQ) use ($user) {
                                                  $userRoleIds = $user->roles->pluck('role_id')->toArray();
                                                  $subQ->where('publish_scope', 'mixed')
                                                       ->where(function($mixedQ) use ($user, $userRoleIds) {
                                                           $mixedQ->whereJsonContains('publish_targets->departments', $user->department_id)
                                                                  ->orWhere(function($roleQ) use ($userRoleIds) {
                                                                      if (!empty($userRoleIds)) {
                                                                          foreach ($userRoleIds as $roleId) {
                                                                              $roleQ->orWhereJsonContains('publish_targets->roles', $roleId);
                                                                          }
                                                                      }
                                                                  });
                                                       });
                                              });
                                        });
                                    }
                                    
                                    $recentDocuments = $recentDocumentsQuery
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                
                                @forelse($recentDocuments as $index => $document)
                                    <tr>
                                        <td>
                                            @if($document->validity_period && $document->isExpired())
                                                <i class="mdi mdi-flag text-danger" title="Expired Document"></i>
                                            @elseif($document->validity_period && $document->isExpiringSoon())
                                                <i class="mdi mdi-flag text-warning" title="Expiring Soon"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="font-14 mb-1">
                                                    <a href="{{ route('documents.show', $document->id) }}" 
                                                       class="text-body">
                                                        @if($document->validity_period && $document->isExpired())
                                                            <i class="mdi mdi-alert-circle text-danger me-1" title="Expired"></i>
                                                        @elseif($document->validity_period && $document->isExpiringSoon())
                                                            <i class="mdi mdi-clock-alert text-warning me-1" title="Expiring Soon"></i>
                                                        @endif
                                                        {{ $document->name }}
                                                    </a>
                                                </h6>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ optional($document->documentType)->name ?? 'Unknown' }}</span>
                                        </td>
                                        <td>
                                            <code>{{ $document->version }}</code>
                                        </td>
                                        <td>
                                            @if($document->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($document->status === 'draft')
                                                <span class="badge bg-warning">Draft</span>
                                            @else
                                                <span class="badge bg-secondary">Archived</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($document->is_published)
                                                <span class="badge bg-success">
                                                    <i class="mdi mdi-check"></i> Published
                                                </span>
                                                @if($document->publish_scope)
                                                    <br><small class="text-muted">{{ $document->publish_scope_label }}</small>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="mdi mdi-clock"></i> Not Published
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($document->validity_period)
                                                @if($document->isExpired())
                                                    <span class="badge bg-danger">
                                                        <i class="mdi mdi-alert"></i> EXPIRED
                                                    </span>
                                                    <br><small class="text-muted">{{ $document->validity_period->format('M d, Y') }} ({{ $document->formatted_time_remaining }})</small>
                                                @elseif($document->isExpiringSoon())
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="mdi mdi-clock-alert"></i> EXPIRING SOON
                                                    </span>
                                                    <br><small class="text-muted">{{ $document->validity_period->format('M d, Y') }} ({{ $document->formatted_time_remaining }} left)</small>
                                                @else
                                                    <span class="text-success">
                                                        <i class="mdi mdi-check-circle"></i> {{ $document->validity_period->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">
                                                    <i class="mdi mdi-infinity"></i> No expiry
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <span class="text-muted">{{ $document->created_at->format('M d, Y') }}</span>
                                                <br><small class="text-muted">by {{ optional($document->creator)->name ?? 'Unknown' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                            <a href="{{ route('documents.show', $document->id) }}" 
                                                   class="btn btn-outline-info" title="View">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ $document->file_url }}" 
                                                   class="btn btn-outline-success" 
                                               target="_blank"
                                               title="Open File">
                                                <i class="mdi mdi-file-document"></i>
                                            </a>
                                                @if((Auth::user()->hasRole('Admin') || Auth::user()->canEditDocuments()) && !$document->is_published)
                                                    <a href="{{ route('documents.publish', $document->id) }}" 
                                                       class="btn btn-outline-success" title="Publish">
                                                        <i class="mdi mdi-share-variant"></i>
                                                    </a>
                                                @endif
                                                @if(Auth::user()->hasRole('Admin') || Auth::user()->canEditDocuments())
                                                <a href="{{ route('documents.edit', $document->id) }}" 
                                                   class="btn btn-outline-warning" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                @endif
                                                @if(Auth::user()->hasRole('Admin') || Auth::user()->canEditDocuments())
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteDocument({{ $document->id }})" title="Delete">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="mdi mdi-file-document-outline" style="font-size: 3rem;"></i>
                                                <p class="mt-2">No documents found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>
@endsection

<style>
    /* File Explorer Style Folder Grid */
    .folder-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 8px;
        padding: 16px;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .folder-item {
        background: transparent;
        border: none;
        padding: 8px 4px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
        height: 90px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        border-radius: 4px;
    }

    .folder-item:hover {
        background: rgba(0, 123, 255, 0.1);
        transform: none;
        box-shadow: none;
    }

    .folder-item:active {
        background: rgba(0, 123, 255, 0.2);
    }

    .folder-icon {
        font-size: 28px;
        color: #ffc107;
        margin-bottom: 4px;
        transition: all 0.15s ease;
    }

    .folder-item:hover .folder-icon {
        transform: scale(1.05);
        color: #007bff;
    }

    .folder-name {
        font-weight: 600;
        color: #007bff;
        font-size: 12px;
        line-height: 1.2;
        margin-bottom: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        max-width: 100%;
    }

    .folder-count {
        font-size: 10px;
        color: #6c757d;
        font-weight: 400;
        background: rgba(108, 117, 125, 0.1);
        border-radius: 8px;
        padding: 1px 4px;
        min-width: 16px;
        margin-bottom: 2px;
    }

    .folder-item:hover .folder-count {
        background: rgba(0, 123, 255, 0.2);
        color: #007bff;
    }

    .folder-label {
        font-size: 9px;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.7;
    }

    .folder-item:hover .folder-label {
        opacity: 1;
        color: #495057;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .folder-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 6px;
            padding: 12px;
        }
        
        .folder-item {
            height: 80px;
            padding: 6px 3px;
        }
        
        .folder-icon {
            font-size: 24px;
            margin-bottom: 3px;
        }
        
        .folder-name {
            font-size: 10px;
        }
        
        .folder-count {
            font-size: 9px;
        }
        
        .folder-label {
            font-size: 7px;
        }
    }

    @media (max-width: 576px) {
        .folder-grid {
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 4px;
            padding: 8px;
        }
        
        .folder-item {
            height: 70px;
            padding: 4px 2px;
        }
        
        .folder-icon {
            font-size: 20px;
            margin-bottom: 2px;
        }
        
        .folder-name {
            font-size: 9px;
        }
        
        .folder-count {
            font-size: 8px;
        }
        
        .folder-label {
            font-size: 6px;
        }
    }

    /* Recent Documents Table Styles - Compact */
    .recent-documents-table {
        font-size: 0.875rem;
    }

    .recent-documents-table th {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.5rem 0.25rem;
        border-bottom: 2px solid #dee2e6;
    }

    .recent-documents-table td {
        font-size: 0.8rem;
        padding: 0.5rem 0.25rem;
        vertical-align: middle;
    }

    .recent-documents-table .font-14 {
        font-size: 0.875rem !important;
    }

    .recent-documents-table .font-16 {
        font-size: 0.875rem !important;
    }

    .recent-documents-table h6 {
        font-size: 0.875rem !important;
        margin-bottom: 0.25rem;
    }

    .recent-documents-table small {
        font-size: 0.75rem !important;
    }

    .recent-documents-table .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    .recent-documents-table .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .recent-documents-table code {
        font-size: 0.75rem;
    }
</style>

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize counter animation
        $('[data-plugin="counterup"]').each(function() {
            var $this = $(this);
            $this.text($this.text());
        });
    });
</script>
@endsection
