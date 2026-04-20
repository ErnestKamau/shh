<div class="container-fluid py-3 lab-panel-theme">
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5>
                <i class="mdi mdi-file-document-outline"></i>
                Supporting Document Templates
            </h5>
            <button class="btn btn-primary btn-action-sm" wire:click="openCreateModal">
                <i class="mdi mdi-plus"></i>
                New template
            </button>
        </div>
        <div class="workflow-board-panel-body">
            <div class="workflow-board-filter-nested mb-0">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input class="form-control" type="text" placeholder="Search by title or document code..." wire:model.live="search">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" wire:model.live="statusFilter">
                            <option value="">All</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Per page</label>
                        <select class="form-select" wire:model.live="perPage">
                            @foreach ($perPageOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }} / page</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($message)
        <div class="alert alert-{{ $messageType }} d-flex justify-content-between align-items-center">
            <div>{{ $message }}</div>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="dismissMessage">Dismiss</button>
        </div>
    @endif

    <div class="workflow-board-panel">
        <div class="table-responsive">
            <table class="table workflow-table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Doc code</th>
                        <th>Title</th>
                        <th>Version</th>
                        <th>Published</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($templates as $template)
                    <tr>
                        <td>{{ $template->document_code }}</td>
                        <td>
                            <a href="{{ route('supporting-documents.templates.edit', ['template' => $template->id]) }}">
                                {{ $template->title }}
                            </a>
                        </td>
                        <td>{{ $template->version }}</td>
                        <td>
                            @if ($template->is_published)
                                <span class="workflow-status-chip" style="--chip-accent: #16a34a;">Yes</span>
                            @else
                                <span class="workflow-status-chip" style="--chip-accent: #64748b;">No</span>
                            @endif
                        </td>
                        <td>
                            @if ($template->is_active)
                                <span class="workflow-status-chip" style="--chip-accent: #16a34a;">Yes</span>
                            @else
                                <span class="workflow-status-chip" style="--chip-accent: #64748b;">No</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a class="btn btn-outline-primary btn-action-sm" href="{{ route('supporting-documents.templates.edit', ['template' => $template->id]) }}">
                                <i class="mdi mdi-pencil-outline"></i>
                                Edit
                            </a>

                            @if ($template->is_published)
                                <button class="btn btn-outline-warning btn-action-sm" wire:click="unpublish({{ $template->id }})">
                                    <i class="mdi mdi-eye-off-outline"></i>
                                    Unpublish
                                </button>
                            @else
                                <button class="btn btn-outline-success btn-action-sm" wire:click="publish({{ $template->id }})">
                                    <i class="mdi mdi-publish"></i>
                                    Publish
                                </button>
                            @endif

                            <button class="btn btn-outline-secondary btn-action-sm" wire:click="toggleActive({{ $template->id }})">
                                <i class="mdi mdi-toggle-switch"></i>
                                {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                            </button>

                            <button class="btn btn-outline-danger btn-action-sm" wire:click="deleteTemplate({{ $template->id }})"
                                onclick="confirm('Delete this template? This cannot be undone.') || event.stopImmediatePropagation()">
                                <i class="mdi mdi-delete-outline"></i>
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No templates found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="workflow-board-panel-body flush-top">
            {{ $templates->links() }}
        </div>
    </div>

    @if ($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Supporting Document Template</h5>
                        <button type="button" class="btn-close" wire:click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Form / Document code</label>
                                <input class="form-control" type="text" wire:model.defer="newDocumentCode" placeholder="e.g. DCEA 002">
                                @error('newDocumentCode') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Title</label>
                                <input class="form-control" type="text" wire:model.defer="newTitle" placeholder="e.g. CERTIFICATE OF PHOTOGRAPH/MOVING PICTURE">
                                @error('newTitle') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subtitle</label>
                                <input class="form-control" type="text" wire:model.defer="newSubtitle" placeholder="e.g. Made under section 51(5)">
                                @error('newSubtitle') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="3" wire:model.defer="newDescription"></textarea>
                                @error('newDescription') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="closeCreateModal">Cancel</button>
                        <button class="btn btn-primary" wire:click="createTemplate">Create</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
