<div class="workflow-board-panel-body flush-top px-0">
    <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
        <div>
            <span class="small font-weight-bold text-muted text-uppercase">Attachments</span>
            <p class="mb-0 small text-muted">Upload supporting documents for this request.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="openAttachmentModal">
            <i class="mdi mdi-upload mr-1"></i> Upload Attachment
        </button>
    </div>

    @if($attachmentInstances->isEmpty() && (!isset($batchAttachments) || $batchAttachments->isEmpty()) && (!isset($customAttachments) || $customAttachments->isEmpty()) && (!isset($formMediaAttachments) || $formMediaAttachments->isEmpty()))
        <p class="text-muted mb-0 py-3 px-3">No attachments yet. Click <strong>Upload Attachment</strong> to add one.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover workflow-table mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Attachment Type</th>
                        <th>Description</th>
                        <th>Type / Source</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($customAttachments))
                        @foreach($customAttachments as $cAttachment)
                            <tr>
                                <td>
                                    <span class="font-weight-bold">{{ $cAttachment->attachment_heading ?: $cAttachment->original_name }}</span>
                                    @if($cAttachment->attachment_heading && $cAttachment->original_name !== $cAttachment->attachment_heading)
                                        <small class="d-block text-muted">{{ $cAttachment->original_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($cAttachment->attachment_type)
                                        <span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $cAttachment->attachment_type)) }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>{{ $cAttachment->description ?: '—' }}</td>
                                <td>Uploaded Document</td>
                                <td><span class="badge badge-success">Uploaded</span></td>
                                <td class="text-nowrap">{{ optional($cAttachment->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    @if($cAttachment->file_url)
                                        <a href="{{ $cAttachment->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            Download
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @if(isset($formMediaAttachments))
                        @foreach($formMediaAttachments as $fAttachment)
                            <tr>
                                <td>{{ $fAttachment->original_name }}</td>
                                <td>
                                    @if(isset($fAttachment->attachment_type) && $fAttachment->attachment_type)
                                        <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $fAttachment->attachment_type)) }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>{{ $fAttachment->attachment_heading ?? '—' }}</td>
                                <td>Form Attachment</td>
                                <td><span class="badge badge-success">Submitted</span></td>
                                <td class="text-nowrap">{{ optional($fAttachment->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    @if($fAttachment->file_url)
                                        <a href="{{ $fAttachment->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            Download
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @foreach($attachmentInstances as $attachment)
                        <tr>
                            <td>{{ $attachment->submissionForm->name ?? 'Attachment' }}</td>
                            <td><span class="text-muted small">—</span></td>
                            <td><span class="text-muted small">—</span></td>
                            <td>{{ $attachment->getDocumentControlNumber() ?? $attachment->form_number ?? '—' }} (Form)</td>
                            <td>
                                <span class="badge badge-{{ $attachment->getStatusBadgeColor() }}">
                                    {{ ucfirst(str_replace('_', ' ', $attachment->status ?? 'draft')) }}
                                </span>
                            </td>
                            <td class="text-nowrap">{{ optional($attachment->submitted_at)->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>
                                @if($attachment->isDraft())
                                    <a href="{{ route('submission-forms.instances.fill', [$attachment->submissionForm, $attachment]) }}" class="btn btn-sm btn-outline-primary">
                                        Continue
                                    </a>
                                @else
                                    <a href="{{ route('submission-forms.instances.show', [$attachment->submissionForm, $attachment]) }}" class="btn btn-sm btn-outline-secondary">
                                        View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    @if(isset($batchAttachments))
                        @foreach($batchAttachments as $bAttachment)
                            <tr>
                                <td>{{ $bAttachment->title }}</td>
                                <td><span class="text-muted small">—</span></td>
                                <td><span class="text-muted small">—</span></td>
                                <td>Batch {{ \App\SampleHeader::find($bAttachment->batch_id)?->batch_code ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-success">Generated</span>
                                </td>
                                <td class="text-nowrap">{{ optional($bAttachment->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    @if($bAttachment->attachment_url)
                                        <a href="{{ $bAttachment->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            Download PDF
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    @endif

    @if($showAttachmentModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-paperclip mr-1"></i> Upload Attachment</h5>
                        <button type="button" class="close" wire:click="closeAttachmentModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="uploadAttachment">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="newAttachmentTitle" placeholder="e.g. Sample Import Permit">
                                @error('newAttachmentTitle') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Attachment Type <span class="text-danger">*</span></label>
                                <select class="form-control" wire:model.live="newAttachmentType">
                                    <option value="">— Select Type —</option>
                                    @foreach($availableAttachmentTypes as $typeOption)
                                        <option value="{{ $typeOption }}">{{ $typeOption }}</option>
                                    @endforeach
                                    <option value="Other">Other (specify below)</option>
                                </select>
                                @error('newAttachmentType') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>

                            @if($newAttachmentType === 'Other')
                                <div class="form-group">
                                    <label class="font-weight-bold">New Attachment Type <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="newCustomAttachmentType" placeholder="Enter attachment type">
                                    @error('newCustomAttachmentType') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                </div>
                            @endif

                            <div class="form-group">
                                <label class="font-weight-bold">File <span class="text-danger">*</span></label>
                                <div
                                    class="request-attachment-dropzone border rounded p-4 text-center bg-light"
                                    x-data="{ isDragging: false }"
                                    :class="{ 'border-primary bg-white': isDragging }"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length) { $wire.upload('newAttachment', $event.dataTransfer.files[0]) }"
                                    @click="$refs.attachmentInput.click()"
                                    style="cursor: pointer; border-style: dashed !important;"
                                >
                                    <i class="mdi mdi-cloud-upload-outline text-muted" style="font-size: 2rem;"></i>
                                    <p class="mb-1 mt-2">Drag and drop your file here, or click to browse</p>
                                    <p class="text-muted small mb-0">PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, WEBP, TXT — max 10MB</p>
                                    <input
                                        type="file"
                                        x-ref="attachmentInput"
                                        wire:model="newAttachment"
                                        class="d-none"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp,.txt"
                                    >
                                </div>
                                <div wire:loading wire:target="newAttachment" class="text-primary small mt-2">
                                    Uploading file...
                                </div>
                                @if($newAttachment)
                                    <div class="mt-2 small text-success">
                                        <i class="mdi mdi-check-circle"></i>
                                        {{ $newAttachment->getClientOriginalName() }}
                                    </div>
                                @endif
                                @error('newAttachment') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Description</label>
                                <textarea class="form-control" rows="3" wire:model="newAttachmentDescription" placeholder="Optional notes about this attachment"></textarea>
                                @error('newAttachmentDescription') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" wire:click="closeAttachmentModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="newAttachment, uploadAttachment">
                                <span wire:loading wire:target="uploadAttachment" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                                Save Attachment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
