<div class="workflow-board-panel-body flush-top px-0">
    <div class="p-3 border-bottom bg-light">
        <form wire:submit.prevent="uploadAttachment" class="d-flex align-items-center">
            <div class="custom-file mr-3" style="max-width: 400px;">
                <input type="file" class="custom-file-input" id="newAttachment" wire:model="newAttachment">
                <label class="custom-file-label" for="newAttachment">Choose document...</label>
            </div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="newAttachment, uploadAttachment">
                <span wire:loading wire:target="uploadAttachment" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                Upload
            </button>
        </form>
        @error('newAttachment') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
    </div>

    @if($attachmentInstances->isEmpty() && (!isset($batchAttachments) || $batchAttachments->isEmpty()) && (!isset($customAttachments) || $customAttachments->isEmpty()) && (!isset($formMediaAttachments) || $formMediaAttachments->isEmpty()))
        <p class="text-muted mb-0 py-3 px-3">No linked attachments for this request.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover workflow-table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
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
