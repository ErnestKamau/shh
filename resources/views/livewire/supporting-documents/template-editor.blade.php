<div class="container-fluid py-3 lab-panel-theme">
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <a href="{{ route('supporting-documents.templates.index') }}" class="btn btn-outline-secondary btn-action-sm">
                    <i class="mdi mdi-arrow-left"></i>
                    Back
                </a>

                <div class="d-flex flex-column">
                    <h5>
                        <i class="mdi mdi-file-document-outline"></i>
                        Supporting Documents
                    </h5>
                    <div class="text-muted small">
                        @if (!empty($document_code))
                        <strong>{{ $document_code }}</strong>
                        <span class="mx-1">•</span>
                        @endif
                        <span>{{ $title ?: 'Untitled template' }}</span>
                        <span class="mx-1">•</span>
                        Version <strong>{{ $version }}</strong>
                    </div>
                </div>

                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <span class="workflow-status-chip" style="--chip-accent: {{ $is_published ? '#16a34a' : '#64748b' }};">
                        {{ $is_published ? 'Published' : 'Draft' }}
                    </span>
                    <span class="workflow-status-chip" style="--chip-accent: {{ $is_active ? '#16a34a' : '#64748b' }};">
                        {{ $is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                @if ($is_published)
                <button class="btn btn-outline-warning btn-action-sm" wire:click="unpublish">
                    <i class="mdi mdi-eye-off-outline"></i>
                    Unpublish
                </button>
                @else
                <button class="btn btn-outline-success btn-action-sm" wire:click="publish">
                    <i class="mdi mdi-publish"></i>
                    Publish
                </button>
                @endif
                <button class="btn btn-primary btn-action-sm" wire:click="saveTemplate">
                    <i class="mdi mdi-content-save-outline"></i>
                    Save
                </button>
            </div>
        </div>
    </div>

    @if ($message)
    <div class="alert alert-{{ $messageType }} d-flex justify-content-between align-items-center">
        <div>{{ $message }}</div>
        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="dismissMessage">Dismiss</button>
    </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-lg-3">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Template</span>
                    <span class="badge {{ $is_published ? 'bg-success' : 'bg-secondary' }}">{{ $is_published ? 'Published' : 'Draft' }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Version</small>
                        <strong>{{ $version }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Active</small>
                        <span class="badge {{ $is_active ? 'bg-success' : 'bg-secondary' }}">{{ $is_active ? 'Yes' : 'No' }}</span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Form / Document code</label>
                        <input class="form-control" type="text" wire:model.defer="document_code" placeholder="e.g. DCEA 002">
                        @error('document_code') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input class="form-control" type="text" wire:model.defer="title" placeholder="e.g. CERTIFICATE OF PHOTOGRAPH/MOVING PICTURE">
                        @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subtitle</label>
                        <input class="form-control" type="text" wire:model.defer="subtitle" placeholder="e.g. Made under section 51(5)">
                        @error('subtitle') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" wire:model.defer="description"></textarea>
                        @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" wire:model.defer="is_active">
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Quick actions</div>
                <div class="card-body d-grid gap-2">
                    <button class="btn btn-outline-primary" wire:click="openAddSection">Add section</button>
                    <button class="btn btn-primary" wire:click="saveTemplate">Save changes</button>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Sections</h5>
                <button class="btn btn-sm btn-outline-primary" wire:click="openAddSection">Add section</button>
            </div>

            @forelse ($sections as $section)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $section->title ?: 'Untitled section' }}</strong>
                        <small class="text-muted ms-2">(#{{ $section->id }})</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" wire:click="openAddElement({{ $section->id }})">Add block/field</button>
                        <button class="btn btn-sm btn-outline-danger" wire:click="deleteSection({{ $section->id }})"
                            onclick="confirm('Delete this section and all its elements?') || event.stopImmediatePropagation()">
                            Delete section
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($section->elements->isEmpty())
                    <div class="text-muted">No elements yet.</div>
                    @else
                    <ul class="list-group">
                        @foreach ($section->elements as $el)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="me-3">
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge bg-secondary">{{ $el->element_type }}</span>
                                    <strong>{{ $el->label ?: ($el->element_type === 'static_text' ? 'Static text' : ($el->element_type === 'paragraph_template' ? 'Paragraph with fields' : 'Field')) }}</strong>
                                </div>
                                @if (!in_array($el->element_type, ['static_text', 'paragraph_template']))
                                <div class="text-muted small">name: <code>{{ $el->name }}</code></div>
                                @endif
                                @if ($el->element_type === 'paragraph_template' && !empty($el->default_value))
                                @php
                                $paragraphPreview = preg_replace(
                                '/\{\{([^}]+)\}\}/',
                                '<mark style="background:#dbeafe;border-radius:3px;padding:0 3px;font-style:normal;">$1</mark>',
                                e(\Illuminate\Support\Str::limit($el->default_value, 400))
                                );
                                preg_match_all('/\{\{([^}]+)\}\}/', $el->default_value, $fieldMatches);
                                @endphp
                                <div class="small mt-1" style="line-height:1.9;">{!! $paragraphPreview !!}</div>
                                @if (!empty($fieldMatches[1]))
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @foreach ($fieldMatches[1] as $fieldName)
                                    <span class="badge" style="background:#1d4ed8;font-size:0.7rem;">{{ $fieldName }}</span>
                                    @endforeach
                                </div>
                                @endif
                                @elseif (!empty($el->default_value))
                                <div class="small mt-1 text-muted" style="white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit($el->default_value, 200) }}</div>
                                @endif
                            </div>
                            <div class="text-end d-flex flex-column gap-1">
                                <button class="btn btn-sm btn-outline-secondary" wire:click="openEditElement({{ $el->id }})">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteElement({{ $el->id }})"
                                    onclick="confirm('Delete this element?') || event.stopImmediatePropagation()">
                                    Delete
                                </button>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            @empty
            <div class="card">
                <div class="card-body text-muted">No sections yet.</div>
            </div>
            @endforelse
        </div>
    </div>

    @if ($showAddSection)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add section</h5>
                    <button type="button" class="btn-close" wire:click="$set('showAddSection', false)"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Section title</label>
                    <input class="form-control" type="text" wire:model.defer="newSectionTitle" placeholder="e.g. Body">
                    @error('newSectionTitle') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('showAddSection', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="addSection">Add</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($showAddElement)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add element</h5>
                    <button type="button" class="btn-close" wire:click="$set('showAddElement', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" wire:model.live="newElementType">
                                <option value="static_text">Static text</option>
                                <option value="paragraph_template">Paragraph with fields</option>
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="date">Date</option>
                                <option value="number">Number</option>
                            </select>
                            @error('newElementType') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Label</label>
                            <input class="form-control" type="text" wire:model.defer="newElementLabel" placeholder="Optional label">
                            @error('newElementLabel') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if (!in_array($newElementType, ['static_text', 'paragraph_template']))
                        <div class="col-md-6">
                            <label class="form-label">Name (for input fields)</label>
                            <input class="form-control" type="text" wire:model.defer="newElementName" placeholder="e.g. recording_officer">
                            @error('newElementName') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Placeholder</label>
                            <input class="form-control" type="text" wire:model.defer="newElementPlaceholder">
                            @error('newElementPlaceholder') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label">Help text</label>
                            <textarea class="form-control" rows="2" wire:model.defer="newElementHelpText"></textarea>
                            @error('newElementHelpText') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if ($newElementType === 'paragraph_template')
                        <div class="col-12">
                            <div class="alert alert-info small mb-0 py-2">
                                <strong>Paragraph with fields</strong> — write the paragraph text and wrap each fillable spot with <code>@{{variable_name}}</code>.<br>
                                Example: <code>I, @{{magistrate_name}} District Magistrate, do hereby certify that pictures stored in @{{storage_form}} have been taken by @{{recording_officer}}...</code>
                            </div>
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label">
                                @if ($newElementType === 'paragraph_template')
                                Paragraph body
                                @elseif ($newElementType === 'static_text')
                                Static text content
                                @else
                                Default value
                                @endif
                            </label>
                            @php
                            $bodyPlaceholder = match($newElementType) {
                            'paragraph_template' => 'e.g. I, ' . '{{' . 'magistrate_name' . '}}' . ' District Magistrate, do hereby certify that pictures stored in ' . '{{' . 'storage_form' . '}}' . ' have been taken by ' . '{{' . 'recording_officer' . '}}' . '...',
                            'static_text' => 'Paste the paragraph here',
                            default => '',
                            };
                            @endphp
                            <textarea class="form-control" rows="6" wire:model.defer="newElementDefaultValue"
                                placeholder="{{ $bodyPlaceholder }}"></textarea>
                            @error('newElementDefaultValue') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if (!in_array($newElementType, ['static_text', 'paragraph_template']))
                        <div class="col-12">
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="req" wire:model.defer="newElementRequired">
                                    <label class="form-check-label" for="req">Required</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ro" wire:model.defer="newElementReadonly">
                                    <label class="form-check-label" for="ro">Read-only</label>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('showAddElement', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="addElement">Add</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($showEditElement)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit element</h5>
                    <button type="button" class="btn-close" wire:click="$set('showEditElement', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" wire:model.live="editElementType">
                                <option value="static_text">Static text</option>
                                <option value="paragraph_template">Paragraph with fields</option>
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="date">Date</option>
                                <option value="number">Number</option>
                            </select>
                            @error('editElementType') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Label</label>
                            <input class="form-control" type="text" wire:model.defer="editElementLabel" placeholder="Optional label">
                            @error('editElementLabel') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if (!in_array($editElementType, ['static_text', 'paragraph_template']))
                        <div class="col-md-6">
                            <label class="form-label">Name (for input fields)</label>
                            <input class="form-control" type="text" wire:model.defer="editElementName" placeholder="e.g. recording_officer">
                            @error('editElementName') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Placeholder</label>
                            <input class="form-control" type="text" wire:model.defer="editElementPlaceholder">
                            @error('editElementPlaceholder') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label">Help text</label>
                            <textarea class="form-control" rows="2" wire:model.defer="editElementHelpText"></textarea>
                            @error('editElementHelpText') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if ($editElementType === 'paragraph_template')
                        <div class="col-12">
                            <div class="alert alert-info small mb-0 py-2">
                                <strong>Paragraph with fields</strong> — wrap each fillable spot with <code>@{{variable_name}}</code>.
                            </div>
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label">
                                @if ($editElementType === 'paragraph_template')
                                Paragraph body
                                @elseif ($editElementType === 'static_text')
                                Static text content
                                @else
                                Default value
                                @endif
                            </label>
                            <textarea class="form-control" rows="6" wire:model.defer="editElementDefaultValue"></textarea>
                            @error('editElementDefaultValue') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if (!in_array($editElementType, ['static_text', 'paragraph_template']))
                        <div class="col-12">
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_req" wire:model.defer="editElementRequired">
                                    <label class="form-check-label" for="edit_req">Required</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_ro" wire:model.defer="editElementReadonly">
                                    <label class="form-check-label" for="edit_ro">Read-only</label>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('showEditElement', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="updateElement">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>