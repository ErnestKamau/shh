<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-archive text-warning"></i>
                                Archived Documents
                            </h2>
                            <p class="text-muted mb-0">View and manage archived documents</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by title, number, or description...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Type</label>
                                <select wire:model.live="typeFilter" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Per Page</label>
                                <select wire:model.live="perPage" class="form-select">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($documents->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} entries
                                </span>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover workflow-table livewire-table ls-table">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th>Archived By</th>
                                        <th>Archived Date</th>
                                        <th>Reason</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="mdi mdi-archive text-warning" style="font-size: 24px;"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $document->title }}</strong>
                                                        <br><small class="text-muted">{{ $document->document_number }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $document->documentType->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $document->archiver->name ?? 'System' }}</td>
                                            <td>{{ $document->archived_at ? $document->archived_at->format('M d, Y H:i') : 'N/A' }}</td>
                                            <td>{{ $document->archive_reason ?? 'No reason provided' }}</td>
                                            <td>
                                                <div class="dms-actions-group">
                                                    <a href="{{ route('dms.download', $document->id) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                       title="Download">
                                                        <i class="mdi mdi-download"></i>
                                                    </a>
                                                    <button type="button"
                                                            wire:click="restoreDocument('{{ $document->id }}')"
                                                            wire:confirm="Are you sure you want to restore this document?"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="Restore">
                                                        <i class="mdi mdi-restore"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $documents->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-archive-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No archived documents found</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
