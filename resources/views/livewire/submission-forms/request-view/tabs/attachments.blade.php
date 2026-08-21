<div class="rv-attachments-tab workflow-board-panel-body flush-top px-0">
    <div class="rv-tab-panel-header">
        <div>
            <h5 class="rv-sample-collection-tab__title mb-1">
                <span class="ls-icon-tile__glyph ls-icon--burgundy rv-tab-title-icon" aria-hidden="true">
                    <i class="mdi mdi-paperclip"></i>
                </span>
                Attachments
            </h5>
            <p class="rv-sample-collection-tab__hint mb-0">
                Upload supporting documents for this request.
            </p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="openAttachmentModal">
            <i class="mdi mdi-upload mr-1"></i> Upload Attachment
        </button>
    </div>

    @if($attachmentInstances->isEmpty() && (!isset($batchAttachments) || $batchAttachments->isEmpty()) && (!isset($customAttachments) || $customAttachments->isEmpty()) && (!isset($formMediaAttachments) || $formMediaAttachments->isEmpty()))
        <p class="rv-tab-empty text-muted mb-0 py-3 px-3">No attachments yet. Click <strong>Upload Attachment</strong> to add one.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover workflow-table rv-tab-table mb-0">
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
                                        <a href="{{ $cAttachment->file_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mr-1">
                                            View
                                        </a>
                                        <a href="{{ $cAttachment->file_download_url }}" class="btn btn-sm btn-outline-secondary">
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
                                        <a href="{{ $fAttachment->file_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mr-1">
                                            View
                                        </a>
                                        <a href="{{ $fAttachment->file_url }}" download class="btn btn-sm btn-outline-secondary">
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
                                        <a href="{{ $bAttachment->attachment_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mr-1">
                                            View
                                        </a>
                                        <a href="{{ $bAttachment->attachment_url }}" download class="btn btn-sm btn-outline-secondary">
                                            Download
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
        <div class="rv-attachment-modal-backdrop"
             wire:keydown.escape.window="closeAttachmentModal"
             role="presentation">
            <div class="rv-attachment-modal rv-modal"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="rv-upload-attachment-title"
                 wire:click.stop>
                <div class="rv-modal-header">
                    <h4 class="rv-modal-title mb-0" id="rv-upload-attachment-title">
                        <span class="ls-icon-tile__glyph ls-icon--burgundy rv-tab-title-icon" aria-hidden="true">
                            <i class="mdi mdi-paperclip"></i>
                        </span>
                        Upload Attachment
                    </h4>
                    <button type="button" class="rv-modal-close" wire:click="closeAttachmentModal" aria-label="Close">
                        <i class="mdi mdi-close" aria-hidden="true"></i>
                    </button>
                </div>

                <form wire:submit.prevent="uploadAttachment">
                    <div class="rv-modal-body">
                        <div class="mb-3">
                            @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                'label' => 'Title',
                                'id' => 'rv-new-attachment-title',
                                'name' => 'newAttachmentTitle',
                                'wireModel' => 'newAttachmentTitle',
                                'placeholder' => 'e.g. Sample Import Permit',
                                'required' => true,
                                'error' => $errors->first('newAttachmentTitle'),
                            ])
                        </div>

                        <div class="ls-field mb-3 {{ $errors->has('newAttachmentType') ? 'is-error' : '' }}">
                            <label class="ls-field__label" for="rv-new-attachment-type">
                                Attachment Type<span class="ls-req">*</span>
                            </label>
                            <div class="ls-field__control">
                                <select
                                    id="rv-new-attachment-type"
                                    class="ls-field__input"
                                    wire:model.live="newAttachmentType"
                                >
                                    <option value="">— Select Type —</option>
                                    @foreach($availableAttachmentTypes as $typeOption)
                                        <option value="{{ $typeOption }}">{{ $typeOption }}</option>
                                    @endforeach
                                    <option value="Other">Other (specify below)</option>
                                </select>
                            </div>
                            @error('newAttachmentType')
                                <p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        @if($newAttachmentType === 'Other')
                            <div class="mb-3">
                                @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                                    'label' => 'New Attachment Type',
                                    'id' => 'rv-new-custom-attachment-type',
                                    'name' => 'newCustomAttachmentType',
                                    'wireModel' => 'newCustomAttachmentType',
                                    'placeholder' => 'Enter attachment type',
                                    'required' => true,
                                    'error' => $errors->first('newCustomAttachmentType'),
                                ])
                            </div>
                        @endif

                        <div class="ls-field mb-3 {{ $errors->has('newAttachment') ? 'is-error' : '' }}">
                            <label class="ls-field__label">
                                File<span class="ls-req">*</span>
                            </label>
                            <div class="ls-upload">
                                <div
                                    class="ls-upload__drop"
                                    x-data="{ active: false }"
                                    :class="{ 'is-active': active }"
                                    @dragover.prevent="active = true"
                                    @dragleave.prevent="active = false"
                                    @drop.prevent="active = false; if ($event.dataTransfer.files.length) { $wire.upload('newAttachment', $event.dataTransfer.files[0]) }"
                                    @click="$refs.attachmentInput.click()"
                                >
                                    <i class="mdi mdi-cloud-upload-outline" aria-hidden="true"></i>
                                    <p class="ls-upload__drop-text">Choose a file or drag &amp; drop it here.</p>
                                    <p class="ls-upload__drop-hint">PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, WEBP, TXT — max 10MB</p>
                                    <input
                                        type="file"
                                        x-ref="attachmentInput"
                                        wire:model="newAttachment"
                                        class="d-none"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp,.txt"
                                    >
                                </div>

                                <div wire:loading wire:target="newAttachment" class="ls-upload__file mt-2">
                                    <i class="mdi mdi-loading ls-motion-spin ls-upload__file-icon" aria-hidden="true"></i>
                                    <div style="flex:1;">
                                        <p class="ls-upload__file-name">Uploading…</p>
                                        <p class="ls-upload__file-meta">Please wait</p>
                                    </div>
                                </div>

                                @if($newAttachment)
                                    <div class="ls-upload__file">
                                        <i class="mdi mdi-file-document-outline ls-upload__file-icon" aria-hidden="true"></i>
                                        <div style="flex:1;">
                                            <p class="ls-upload__file-name">{{ $newAttachment->getClientOriginalName() }}</p>
                                            <p class="ls-upload__file-meta">
                                                <span class="ls-upload__ok"><i class="mdi mdi-check-circle"></i> Ready</span>
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @error('newAttachment')
                                <p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ls-field mb-0 {{ $errors->has('newAttachmentDescription') ? 'is-error' : '' }}">
                            <label class="ls-field__label" for="rv-new-attachment-description">Description</label>
                            <textarea
                                id="rv-new-attachment-description"
                                class="ls-textarea"
                                rows="3"
                                wire:model="newAttachmentDescription"
                                placeholder="Optional notes about this attachment"
                            ></textarea>
                            @error('newAttachmentDescription')
                                <p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="rv-modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeAttachmentModal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" wire:loading.attr="disabled" wire:target="newAttachment, uploadAttachment">
                            <span wire:loading wire:target="uploadAttachment" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                            Save Attachment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
