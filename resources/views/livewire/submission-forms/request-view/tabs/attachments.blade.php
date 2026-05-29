<div class="workflow-board-panel-body flush-top px-0">
    <div class="p-3 border-bottom bg-light">
        <form wire:submit.prevent="uploadAttachment">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold text-muted mb-1" for="newAttachment">File</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="newAttachment" wire:model="newAttachment">
                        <label class="custom-file-label" for="newAttachment">Choose document...</label>
                    </div>
                    @error('newAttachment') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1" for="newAttachmentType">Attachment Type</label>
                    <select class="form-control" id="newAttachmentType" wire:model="newAttachmentType">
                        <option value="">— Select Type —</option>
                        <option value="permit">Permit</option>
                        <option value="invoice">Invoice</option>
                        <option value="packing_list">Packing List</option>
                        <option value="report">Report</option>
                        <option value="certificate">Certificate</option>
                        <option value="authorization_letter">Authorization Letter</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1" for="newAttachmentHeading">Attachment Heading</label>
                    <input type="text" class="form-control" id="newAttachmentHeading" wire:model="newAttachmentHeading" placeholder="e.g. Sample Import Permit">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled" wire:target="newAttachment, uploadAttachment">
                        <span wire:loading wire:target="uploadAttachment" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                        Upload
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($attachmentInstances->isEmpty() && (!isset($batchAttachments) || $batchAttachments->isEmpty()) && (!isset($customAttachments) || $customAttachments->isEmpty()) && (!isset($formMediaAttachments) || $formMediaAttachments->isEmpty()))
        <p class="text-muted mb-0 py-3 px-3">No linked attachments for this request.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover workflow-table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Attachment Type</th>
                        <th>Attachment Heading</th>
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
                                <td>{{ $cAttachment->original_name }}</td>
                                <td>
                                    @if($cAttachment->attachment_type)
                                        <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $cAttachment->attachment_type)) }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>{{ $cAttachment->attachment_heading ?? '—' }}</td>
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
</div>
