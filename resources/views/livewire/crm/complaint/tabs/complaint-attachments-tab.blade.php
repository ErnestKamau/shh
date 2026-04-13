<div>
    <div x-data="{
            init() {
                Livewire.on('show-attachment-modal', () => {
                   $('#attachmentModal').modal('show');
                   this.initSelect2();
                });
                Livewire.on('close-attachment-modal', () => {
                   $('#attachmentModal').modal('hide');
                });
                Livewire.on('show-delete-confirmation', () => {
                   $('#deleteConfirmationModal').modal('show');
                });
                Livewire.on('close-delete-confirmation', () => {
                   $('#deleteConfirmationModal').modal('hide');
                });
                Livewire.on('attachment-types-updated', () => {
                    this.refreshSelect2();
                });
            },
            initSelect2() {
                const wire = $wire;
                const $select = $('#attachment-type-select');
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    dropdownParent: $('#attachmentModal'),
                    width: '100%',
                    placeholder: 'Select Type'
                }).on('change', (e) => {
                    if (wire && typeof wire.set === 'function') {
                        wire.set('type', e.target.value);
                    }
                });

                // Sync select to current server state (add or edit)
                $select.val(@js($type) || '').trigger('change.select2');
            },
            refreshSelect2() {
                const $select = $('#attachment-type-select');
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
                this.initSelect2();
            }
        }">

        {{-- Section Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                    style="width:32px;height:32px;background:#fffbeb;flex-shrink:0;">
                    <i class="mdi mdi-paperclip" style="font-size:1.1rem;color:#d97706;"></i>
                </span>
                <div>
                    <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Attachments</small>
                    <small class="text-muted d-block" style="font-size:0.67rem;">Supporting documents, photos &amp; lab
                        attachments</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button type="button" class="btn btn-outline-success btn-sm text-nowrap" wire:click="exportToExcel">
                    <i class="mdi mdi-microsoft-excel"></i> Export to Excel
                </button>
                <button type="button" class="btn btn-add btn-sm" wire:click="openAttachmentModal">
                    <i class="mdi mdi-plus"></i> Add Attachment
                </button>
            </div>
        </div>

        {{-- Loading Indicator --}}
        <div wire:loading
            wire:target="openAttachmentModal,editAttachment,confirmDelete,uploadAttachment,updateAttachment,deleteAttachment"
            class="crm-loading-indicator">
            <i class="mdi mdi-loading mdi-spin"></i> Loading...
        </div>

        <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
            <x-slot:header>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Posted By</th>
                    <th>File</th>
                    <th style="min-width:120px;">Actions</th>
                </tr>
            </x-slot:header>
                        @forelse($attachments as $attachment)
                            <tr wire:key="attachment-{{ $attachment->id }}">
                                <td>{{ $attachment->title }}</td>
                                <td>{{ $attachment->type }}</td>
                                <td>{{ $attachment->description ?? '-' }}</td>
                                <td>{{ $attachment->posted_by }}</td>
                                <td>
                                    @if($attachment->file_path)
                                        <a href="{{ route('crm.complaint.attachment.download', ['complaint' => $complaintId, 'attachment' => $attachment->id]) }}"
                                            target="_blank" class="btn crm-btn crm-btn-view btn-sm" title="Download">
                                            <i class="mdi mdi-download"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td nowrap>
                                    <x-crm.action-buttons>
                                        <button type="button" class="btn crm-btn crm-btn-edit btn-sm"
                                            wire:click="editAttachment({{ $attachment->id }})" title="Edit">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </button>
                                        <button type="button" class="btn crm-btn crm-btn-delete btn-sm"
                                            wire:click="confirmDelete({{ $attachment->id }})" title="Delete">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>
                                    </x-crm.action-buttons>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-crm.empty-state
                                        icon="mdi-file-hidden"
                                        message="No attachments uploaded"
                                        help="Upload supporting documents, photos or lab files for this complaint."
                                    />
                                </td>
                            </tr>
                        @endforelse
        </x-crm.data-table>

        <x-crm.pagination :summary="'Showing ' . ($attachments->firstItem() ?? 0) . ' to ' . ($attachments->lastItem() ?? 0) . ' of ' . $attachments->total() . ' results'">
            {{ $attachments->links() }}
        </x-crm.pagination>
    </div>

    <!-- Attachment Modal — using native Livewire teleport -->
    @teleport('body')
    <div wire:ignore.self class="modal fade" id="attachmentModal" tabindex="-1" role="dialog"
        aria-labelledby="attachmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="{{ $editingAttachmentId ? 'updateAttachment' : 'uploadAttachment' }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attachmentModalLabel">
                            <i class="mdi mdi-paperclip mr-1 text-warning"></i>
                            {{ $editingAttachmentId ? 'Edit Attachment' : 'Add Attachment' }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span>:</label>
                            <input type="text" wire:model="title" class="form-control" required />
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group" wire:ignore>
                            <label>Type <span class="text-danger">*</span>:</label>
                            <select id="attachment-type-select"
                                class="form-control custom-select-sm" required>
                                <option value="">Select Type</option>
                                @foreach($availableAttachmentTypes as $aType)
                                    <option value="{{ $aType }}">{{ $aType }}</option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                            @error('type') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($type === 'Other')
                            <div class="form-group">
                                <label>Enter Preferred Type <span class="text-danger">*</span>:</label>
                                <input type="text" wire:model="customType" class="form-control"
                                    placeholder="e.g. Lab Result, Legal Document" required />
                                @error('customType') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea wire:model="description" class="form-control" rows="2"></textarea>
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>File <span
                                    class="{{ $editingAttachmentId ? 'text-muted' : 'text-danger' }}">{{ $editingAttachmentId ? '(Optional)' : '*' }}</span>:</label>
                            <input type="file" wire:model="attachmentFile" class="form-control" />
                            <div wire:loading wire:target="attachmentFile" class="text-info small mt-1">Uploading...
                            </div>
                            @error('attachmentFile') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isPublicAttachment"
                                    wire:model="isPublic">
                                <label class="custom-control-label" for="isPublicAttachment">Mark as Public (Visible
                                    in Closure Report)</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove
                                wire:target="{{ $editingAttachmentId ? 'updateAttachment' : 'uploadAttachment' }}">
                                {{ $editingAttachmentId ? 'Update' : 'Upload' }}
                            </span>
                            <span wire:loading
                                wire:target="{{ $editingAttachmentId ? 'updateAttachment' : 'uploadAttachment' }}">
                                {{ $editingAttachmentId ? 'Updating...' : 'Uploading...' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endteleport

    <!-- Delete Confirmation Modal — using native Livewire teleport -->
    @teleport('body')
    <div wire:ignore.self class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="mdi mdi-alert-circle-outline text-danger mb-3" style="font-size: 3rem;"></i>
                    <h4>Are you sure?</h4>
                    <p class="text-muted">You are about to delete this attachment. This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="deleteAttachment"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteAttachment">Yes, Delete it</span>
                        <span wire:loading wire:target="deleteAttachment">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
</div>