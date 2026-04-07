<div>


    <!-- Search and Basic Filters -->
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="mdi mdi-magnify"></i> Search & Basic Filters</h5>
                <div>
                    <a href="{{ route('documents.unpublished') }}" class="btn btn-warning me-2">
                        <i class="mdi mdi-file-document-outline"></i> Unpublished Documents
                    </a>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="toggleAdvancedFilters()" id="advancedFiltersToggleBtn">
                        <i class="mdi mdi-filter-variant" id="advancedFiltersIcon"></i> 
                        <span id="advancedFiltersText">Advanced Filters</span>
                    </button>
                    <button class="btn btn-success">
                        <i class="mdi mdi-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar and Per Page -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="mdi mdi-magnify text-info"></i>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-lg border-start-0" 
                               placeholder="Search by document name, number, description...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label me-2 mb-0">Show:</label>
                        <select wire:model.live="perPage" class="form-select form-select-lg" id="perPage" style="width: auto;">
                            <option value="15">15 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters (Collapsible) -->
    <div class="advanced-filters-section" id="advancedFiltersSection" style="display: {{ $showAdvancedFilters ? 'block' : 'none' }}; transition: all 0.3s ease;">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="mdi mdi-filter-variant"></i> Advanced Filters</h6>
            </div>
            <div class="card-body">
                <!-- Filters Row 1 -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="document_type_id" class="form-label fw-bold">Document Type</label>
                        <select wire:model.live="filters.document_type_id" class="form-select form-select-lg" id="document_type_id">
                            <option value="">All Types</option>
                            @foreach($availableDocumentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select wire:model.live="filters.status" class="form-select form-select-lg" id="status">
                            <option value="">All Status</option>
                            @foreach($availableStatuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="created_by" class="form-label fw-bold">Created By</label>
                        <select wire:model.live="filters.created_by" class="form-select form-select-lg" id="created_by">
                            <option value="">All Users</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="created_date_from" class="form-label fw-bold">Created From</label>
                        <input wire:model.live="filters.created_date_from" type="date" class="form-control form-control-lg" id="created_date_from">
                    </div>
                </div>

                <!-- Filters Row 2 -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="created_date_to" class="form-label fw-bold">Created To</label>
                        <input wire:model.live="filters.created_date_to" type="date" class="form-control form-control-lg" id="created_date_to">
                    </div>
                    <div class="col-md-3">
                        <label for="updated_date_from" class="form-label fw-bold">Updated From</label>
                        <input wire:model.live="filters.updated_date_from" type="date" class="form-control form-control-lg" id="updated_date_from">
                    </div>
                    <div class="col-md-3">
                        <label for="updated_date_to" class="form-label fw-bold">Updated To</label>
                        <input wire:model.live="filters.updated_date_to" type="date" class="form-control form-control-lg" id="updated_date_to">
                    </div>
                    <div class="col-md-3">
                        <label for="validity_from" class="form-label fw-bold">Validity From</label>
                        <input wire:model.live="filters.validity_from" type="date" class="form-control form-control-lg" id="validity_from">
                    </div>
                </div>

                <!-- Filters Row 3 -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="validity_to" class="form-label fw-bold">Validity To</label>
                        <input wire:model.live="filters.validity_to" type="date" class="form-control form-control-lg" id="validity_to">
                    </div>
                    <div class="col-md-3">
                        <label for="version" class="form-label fw-bold">Version</label>
                        <input wire:model.live="filters.version" type="text" class="form-control form-control-lg" id="version" 
                               placeholder="e.g., 1.0, 2.1">
                    </div>
                    <div class="col-md-3">
                        <label for="publishing_status" class="form-label fw-bold">Publishing Status</label>
                        <select wire:model.live="filters.publishing_status" class="form-select form-select-lg" id="publishing_status">
                            <option value="">Published Only</option>
                            <option value="published">Published</option>
                            <option value="unpublished">Not Published</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="expiry_status" class="form-label fw-bold">Expiry Status</label>
                        <select wire:model.live="filters.expiry_status" class="form-select form-select-lg" id="expiry_status">
                            <option value="">All</option>
                            <option value="expired">Expired</option>
                            <option value="expiring_soon">Expiring Soon (≤30 days)</option>
                            <option value="valid">Valid</option>
                        </select>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="row">
                    <div class="col-md-12 d-flex align-items-end">
                        <div class="d-flex" style="gap: 1rem;">
                            <button wire:click="clearFilters" class="btn btn-outline-secondary btn-lg">
                                <i class="mdi mdi-refresh"></i> Clear Filters
                            </button>
                            <button type="button" class="btn btn-outline-info btn-lg" onclick="toggleAdvancedFilters()">
                                <i class="mdi mdi-eye-off"></i> Hide Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Folder Navigation & Breadcrumb -->
    <div class="card mb-3" style="position: relative; z-index: 15;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="mdi mdi-folder-multiple text-warning"></i> 
                <a href="#" wire:click.prevent="navigateHome()" class="text-decoration-none text-dark">Root</a>
                @foreach($this->folderPath as $folder)
                    <span class="mx-1">/</span>
                    <a href="#" wire:click.prevent="navigateNode('{{ $folder->id }}')" class="text-decoration-none text-dark">{{ $folder->name }}</a>
                @endforeach
            </h5>
        </div>
        @if($folders->count() > 0 || $currentNode)
        <div class="card-body bg-light">
            <div class="row g-2">
                @if(Auth::user()->canAddDocuments() && $currentNode)
                <div class="col-auto mb-2" style="min-width: 180px;">
                    <div class="card border-dashed h-100 create-folder-card" style="cursor: pointer; border: 2px dashed #667eea; background-color: #f8f9fa;" onclick="showCreateFolderModal()">
                        <div class="card-body p-2 d-flex align-items-center justify-content-center text-primary">
                            <i class="mdi mdi-folder-plus fs-4 me-2"></i>
                            <span class="fw-bold">Create Folder</span>
                        </div>
                    </div>
                </div>
                @endif
                @if($currentNode)
                <div class="col-auto">
                    <a href="#" wire:click.prevent="navigateUp()" class="btn btn-light border-secondary text-dark px-3 py-2 shadow-sm rounded">
                        <i class="mdi mdi-arrow-up-bold text-muted me-1"></i> .. (Up)
                    </a>
                </div>
                @endif
                @foreach($folders as $folder)
                <div class="col-auto mb-2" style="min-width: 180px;" x-data="{ open: false }" :style="open ? 'position: relative; z-index: 50;' : 'position: relative; z-index: 1;'">
                    <div class="card shadow-sm border h-100 folder-card" style="cursor: pointer;" @click.away="open = false">
                        <div class="card-body p-2 d-flex align-items-center justify-content-between">
                            <a href="#" wire:click.prevent="navigateNode('{{ $folder->id }}')" class="text-dark text-decoration-none d-flex align-items-center flex-grow-1 overflow-hidden" title="{{ $folder->name }}">
                                <i class="mdi {{ isset($folder->is_type) && $folder->is_type ? 'mdi-folder-text' : 'mdi-folder' }} text-warning fs-4 me-2"></i> 
                                <span class="text-truncate fw-medium" style="max-width: 120px;">{{ $folder->name }}</span>
                            </a>
                            
                            @if((Auth::user()->hasRole('Admin') || Auth::user()->canEditDocuments()) && (!isset($folder->is_type) || !$folder->is_type))
                            <div class="ms-1 position-static">
                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0" @click.stop="open = !open">
                                    <i class="mdi mdi-dots-vertical fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0" :class="{ 'show': open }" style="margin-top: 5px; min-width: 150px; z-index: 1050; position: absolute;">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2" href="#" wire:click.prevent="editFolder({{ str_replace('folder_', '', $folder->id) }}); open = false">
                                            <i class="mdi mdi-pencil-outline text-primary me-2 fs-5"></i> Rename
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger d-flex align-items-center py-2" href="#" onclick="confirmDeleteFolder({{ str_replace('folder_', '', $folder->id) }}, '{{ addslashes($folder->name) }}'); open = false">
                                            <i class="mdi mdi-delete-outline me-2 fs-5"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Documents Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>
                    <i class="mdi mdi-file-document-multiple"></i> Documents List - {{ Auth::user()->department->name ?? 'All Departments' }}
                    @if($filters['expiry_status'] === 'expiring_soon')
                        <span class="badge bg-warning ms-2">
                            <i class="mdi mdi-clock-alert"></i> Expiring Soon (30 days)
                        </span>
                    @endif
                </h5>
                <div class="d-flex align-items-center">
                    <span class="me-3">Total: {{ $documents->total() }} documents</span>
                    @if(Auth::user()->canAddDocuments())
                    <a href="{{ route('documents.create') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Upload Document
                    </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body" wire:loading.class="opacity-50">
            <div class="table-responsive">
                <table class="table table-condensed table-sm table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('name')" class="text-dark text-decoration-none">
                                    Document Name
                                    @if($sortField === 'name')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('document_type_id')" class="text-dark text-decoration-none">
                                    Type
                                    @if($sortField === 'document_type_id')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('version')" class="text-dark text-decoration-none">
                                    Version
                                    @if($sortField === 'version')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('status')" class="text-dark text-decoration-none">
                                    Status
                                    @if($sortField === 'status')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('is_published')" class="text-dark text-decoration-none">
                                    Published
                                    @if($sortField === 'is_published')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('validity_period')" class="text-dark text-decoration-none">
                                    Validity
                                    @if($sortField === 'validity_period')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('created_at')" class="text-dark text-decoration-none">
                                    Created
                                    @if($sortField === 'created_at')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $index => $document)
                        <tr>
                            <td>
                                @if($document->validity_period && $document->isExpired())
                                    <i class="mdi mdi-flag text-danger" title="Expired Document"></i>
                                @elseif($document->validity_period && $document->isExpiringSoon())
                                    <i class="mdi mdi-flag text-warning" title="Expiring Soon"></i>
                                @else
                                    {{ ($documents->currentPage() - 1) * $documents->perPage() + $index + 1 }}
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
                                            @if($document->folder)
                                                <span class="badge bg-secondary me-1" title="In Subfolder">{{ $document->folder->name }}</span>
                                            @endif
                                            {{ $document->name }}
                                        </a>
                                    </h6>
                                </div>
                            </td>
                            <td>
                                <small>{{ $document->full_path }}</small>
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
                                    <br><small class="text-muted">by {{ $document->creator?->name ?? 'Unknown' }}</small>
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
                                    @if($search || array_filter($filters))
                                        <p class="small">Try adjusting your search criteria or filters</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($documents->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $documents->firstItem() }} to {{ $documents->lastItem() }} of {{ $documents->total() }} results
                    </div>
                    <div>
                        {{ $documents->links('livewire::bootstrap') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading wire:target="navigateNode, navigateUp, navigateHome, createFolder, sortBy, clearFilters, updateFolder" class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="createFolder">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createFolderModalLabel"><i class="mdi mdi-folder-plus text-primary me-2"></i>Create New Folder</h5>
                        <button type="button" class="btn-close" aria-label="Close" onclick="hideCreateFolderModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="newFolderName" class="form-label fw-bold">Folder Name</label>
                            <input type="text" class="form-control" id="newFolderName" wire:model.defer="newFolderName" placeholder="Enter a name for the new folder..." required>
                            @error('newFolderName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideCreateFolderModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Folder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Folder Modal -->
    <div class="modal fade" id="editFolderModal" tabindex="-1" aria-labelledby="editFolderModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="updateFolder">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFolderModalLabel"><i class="mdi mdi-folder-edit text-primary me-2"></i>Rename Folder</h5>
                        <button type="button" class="btn-close" aria-label="Close" onclick="hideEditFolderModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editFolderName" class="form-label fw-bold">Folder Name</label>
                            <input type="text" class="form-control" id="editFolderName" wire:model.defer="editFolderName" required>
                            @error('editFolderName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideEditFolderModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Enhanced Filter Styling */
        .form-select-lg, .form-control-lg {
            font-size: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-select-lg:focus, .form-control-lg:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .form-label.fw-bold {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        /* Advanced Filters Card */
        .advanced-filters-section .card {
            border: 2px solid #e9ecef;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.3s ease;
        }
        
        .advanced-filters-section .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #e9ecef;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        
        /* Button Styling */
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }
        
        .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
        }
        
        .btn-outline-primary:hover {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .btn-outline-info {
            border-color: #17a2b8;
            color: #17a2b8;
        }
        
        .btn-outline-info:hover {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }
        
        /* Search Bar Enhancement */
        .input-group .form-control-lg {
            border-left: none;
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid black;
            color: black;
            font-weight: 500;
        }
        
        /* Advanced Filters Animation */
        .advanced-filters-section {
            transition: all 0.2s ease-in-out;
            overflow: hidden;
        }
        
        .advanced-filters-section.show {
            animation: slideDown 0.2s ease-in-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-5px);
                max-height: 0;
            }
            to {
                opacity: 1;
                transform: translateY(0);
                max-height: 1000px;
            }
        }
        
        /* Smooth button transitions */
        .btn {
            transition: all 0.15s ease-in-out;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        
        /* Folder Card Hover */
        .folder-card {
            transition: all 0.2s ease-in-out;
            background-color: #ffffff;
        }
        .folder-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
            border-color: #aebfd6 !important;
            background-color: #f8fbff;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-select-lg, .form-control-lg {
                font-size: 0.9rem;
                padding: 0.5rem 0.75rem;
            }
            
            .btn-lg {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>

    <script>
        function deleteDocument(documentId) {
            if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/documents/${documentId}`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function confirmDeleteFolder(folderId, folderName) {
            if (confirm(`Are you sure you want to delete the folder "${folderName}"?\n\nThis will only work if the folder is completely empty (no subfolders and no active documents).`)) {
                @this.call('deleteFolder', folderId);
            }
        }

        // Fast Advanced Filters Toggle
        function toggleAdvancedFilters() {
            const section = document.getElementById('advancedFiltersSection');
            const icon = document.getElementById('advancedFiltersIcon');
            const text = document.getElementById('advancedFiltersText');
            const isVisible = section.style.display !== 'none';
            
            if (isVisible) {
                // Hide filters
                section.style.display = 'none';
                icon.className = 'mdi mdi-filter-variant';
                text.textContent = 'Advanced Filters';
                
                // Update Livewire state without re-rendering
                @this.set('showAdvancedFilters', false);
            } else {
                // Show filters
                section.style.display = 'block';
                icon.className = 'mdi mdi-filter-variant-plus';
                text.textContent = 'Hide Filters';
                
                // Update Livewire state without re-rendering
                @this.set('showAdvancedFilters', true);
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const section = document.getElementById('advancedFiltersSection');
            const icon = document.getElementById('advancedFiltersIcon');
            const text = document.getElementById('advancedFiltersText');
            
            // Set initial state based on Livewire property
            if (@json($showAdvancedFilters)) {
                section.style.display = 'block';
                icon.className = 'mdi mdi-filter-variant-plus';
                text.textContent = 'Hide Filters';
            } else {
                section.style.display = 'none';
                icon.className = 'mdi mdi-filter-variant';
                text.textContent = 'Advanced Filters';
            }
        });
        
        let editModal = null;
        let createModal = null;

        function hideEditFolderModal() {
            if (editModal) {
                editModal.hide();
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                let m = bootstrap.Modal.getInstance(document.getElementById('editFolderModal'));
                if (m) m.hide();
            } else if (typeof $ !== 'undefined') {
                $('#editFolderModal').modal('hide');
            }
        }
        
        function hideCreateFolderModal() {
            if (createModal) {
                createModal.hide();
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                let m = bootstrap.Modal.getInstance(document.getElementById('createFolderModal'));
                if (m) m.hide();
            } else if (typeof $ !== 'undefined') {
                $('#createFolderModal').modal('hide');
            }
        }

        function showCreateFolderModal() {
            if (!createModal) {
                createModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
            }
            createModal.show();
        }

        // Add smooth animation for filter changes
        document.addEventListener('livewire:load', function() {
            editModal = new bootstrap.Modal(document.getElementById('editFolderModal'));
            createModal = new bootstrap.Modal(document.getElementById('createFolderModal'));

            Livewire.hook('message.processed', (message, component) => {
                // Add smooth transition when filters are applied
                const filterInputs = document.querySelectorAll('.form-select-lg, .form-control-lg');
                filterInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        this.style.transition = 'all 0.3s ease';
                        this.style.transform = 'scale(1.02)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 200);
                    });
                });
            });
        });

        // Event listeners for modal
        window.addEventListener('show-edit-folder-modal', event => {
            if (!editModal) {
                editModal = new bootstrap.Modal(document.getElementById('editFolderModal'));
            }
            editModal.show();
        });

        window.addEventListener('hide-edit-folder-modal', event => {
            hideEditFolderModal();
        });
        
        window.addEventListener('hide-create-folder-modal', event => {
            hideCreateFolderModal();
            // Clear input after creating
            const input = document.getElementById('newFolderName');
            if (input) input.value = '';
        });
    </script>
</div>
