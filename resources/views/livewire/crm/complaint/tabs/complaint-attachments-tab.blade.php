<div>
    <div x-data="{
            init() {
                Livewire.on('show-attachment-modal', () => {
                   $('#attachmentModal').modal('show');
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
                    <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.attachments') }}</small>
                    <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.supporting_documents_photos_lab_attachments') }}</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button type="button" class="btn btn-outline-success btn-sm text-nowrap" wire:click="exportToExcel">
                    <i class="mdi mdi-microsoft-excel"></i> {{ __('crm.export_to_excel') }}
                </button>
                <button type="button" class="btn btn-add btn-sm" wire:click="openAttachmentModal">
                    <i class="mdi mdi-plus"></i> {{ __('crm.add_attachment') }}
                </button>
            </div>
        </div>

        {{-- Loading Indicator --}}
        <div wire:loading
            wire:target="openAttachmentModal,editAttachment,confirmDelete,uploadAttachment,updateAttachment,deleteAttachment"
            class="crm-loading-indicator">
            <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...
        </div>

        <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
            <x-slot:header>
                <tr>
                    <th>{{ __('crm.title') }}</th>
                    <th>{{ __('crm.type') }}</th>
                    <th>{{ __('crm.description') }}</th>
                    <th>{{ __('crm.posted_by') }}</th>
                    <th>{{ __('crm.file') }}</th>
                    <th style="min-width:120px;">{{ __('crm.actions') }}</th>
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
                                            target="_blank" class="btn crm-btn crm-btn-view btn-sm" title="{{ __('crm.download_file') }}">
                                            <i class="mdi mdi-download"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td nowrap>
                                    <x-crm.action-buttons>
                                        <button type="button" class="btn crm-btn crm-btn-edit btn-sm"
                                            wire:click="editAttachment({{ $attachment->id }})" title="{{ __('crm.edit') }}">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </button>
                                        <button type="button" class="btn crm-btn crm-btn-delete btn-sm"
                                            wire:click="confirmDelete({{ $attachment->id }})" title="{{ __('crm.delete') }}">
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
                                        :message="__('crm.no_attachments_uploaded')"
                                        :help="__('crm.no_attachments_uploaded_help')"
                                    />
                                </td>
                            </tr>
                        @endforelse
        </x-crm.data-table>

        <x-crm.pagination :summary="__('crm.showing_to_of_results', ['from' => ($attachments->firstItem() ?? 0), 'to' => ($attachments->lastItem() ?? 0), 'total' => $attachments->total()])">
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
                            {{ $editingAttachmentId ? __('crm.edit_attachment') : __('crm.add_attachment') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('crm.title') }} <span class="text-danger">*</span>:</label>
                            <input type="text" wire:model="title" class="form-control" required />
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('crm.type') }} <span class="text-danger">*</span>:</label>
                            <div class="tag-select-container" wire:click.outside="$set('showTypeDropdown', false)">
                                <div class="tag-select-input">
                                    @if($this->selectedType)
                                        <span class="selected-tag">
                                            {{ $this->selectedType }}
                                            <i class="mdi mdi-close" wire:click.stop="clearType"></i>
                                        </span>
                                    @endif
                                    <input type="text" class="tag-input" placeholder="{{ __('crm.select_type') }}"
                                        wire:model.live.debounce.200ms="typeSearch"
                                        wire:focus="$set('showTypeDropdown', true)" />
                                    @if($type)
                                        <i class="mdi mdi-close-circle clear-icon" wire:click="clearType"></i>
                                    @endif
                                </div>

                                @if($showTypeDropdown)
                                    <div class="tag-dropdown">
                                        @forelse($this->filteredTypeOptions as $option)
                                            <div class="tag-dropdown-item" wire:click="selectType('{{ $option }}')">
                                                {{ $option }}
                                            </div>
                                        @empty
                                            <div class="tag-dropdown-empty">No type found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                            @error('type') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($type === 'Other')
                            <div class="form-group">
                                <label>{{ __('crm.enter_preferred_type') }} <span class="text-danger">*</span>:</label>
                                <input type="text" wire:model="customType" class="form-control"
                                    placeholder="{{ __('crm.preferred_type_placeholder') }}" required />
                                @error('customType') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div class="form-group">
                            <label>{{ __('crm.description') }}:</label>
                            <textarea wire:model="description" class="form-control" rows="2"></textarea>
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('crm.file') }} <span
                                    class="{{ $editingAttachmentId ? 'text-muted' : 'text-danger' }}">{{ $editingAttachmentId ? '(Optional)' : '*' }}</span>:</label>
                            <input type="file" wire:model="attachmentFile" class="form-control" />
                            <div wire:loading wire:target="attachmentFile" class="text-info small mt-1">{{ __('crm.uploading') }}...
                            </div>
                            @error('attachmentFile') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isPublicAttachment"
                                    wire:model="isPublic">
                                <label class="custom-control-label" for="isPublicAttachment">{{ __('crm.mark_public_visible_closure_report') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('crm.close') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove
                                wire:target="{{ $editingAttachmentId ? 'updateAttachment' : 'uploadAttachment' }}">
                                {{ $editingAttachmentId ? __('crm.update') : __('crm.upload') }}
                            </span>
                            <span wire:loading
                                wire:target="{{ $editingAttachmentId ? 'updateAttachment' : 'uploadAttachment' }}">
                                {{ $editingAttachmentId ? __('crm.updating') . '...' : __('crm.uploading') . '...' }}
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
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">{{ __('crm.confirm_deletion') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="mdi mdi-alert-circle-outline text-danger mb-3" style="font-size: 3rem;"></i>
                    <h4>{{ __('crm.are_you_sure') }}</h4>
                    <p class="text-muted">{{ __('crm.delete_attachment_warning') }}</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">{{ __('crm.cancel') }}</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="deleteAttachment"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteAttachment">{{ __('crm.confirm_delete_action') }}</span>
                        <span wire:loading wire:target="deleteAttachment">{{ __('crm.deleting') }}...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
</div>

<style>
    .tag-select-container { position: relative; }
    .tag-select-input {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        min-height: 40px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 6px 10px;
        background: #fff;
    }
    .selected-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 12px;
        background: #e8f1ff;
    }
    .selected-tag i { cursor: pointer; font-size: 14px; }
    .tag-input { border: none; outline: none; flex: 1 1 160px; min-width: 100px; }
    .clear-icon { cursor: pointer; color: #9ca3af; font-size: 18px; }
    .tag-dropdown {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        z-index: 1070;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    .tag-dropdown-item { padding: 8px 10px; cursor: pointer; }
    .tag-dropdown-item:hover { background: #f3f4f6; }
    .tag-dropdown-empty { padding: 8px 10px; color: #6b7280; font-size: 13px; }
</style>