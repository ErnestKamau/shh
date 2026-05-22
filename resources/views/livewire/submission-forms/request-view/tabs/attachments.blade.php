<div class="workflow-board-panel-body flush-top px-0">
    @if($attachmentInstances->isEmpty() && (!isset($batchAttachments) || $batchAttachments->isEmpty()))
        <p class="text-muted mb-0 py-3">No linked attachment forms for this request.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover workflow-table mb-0">
                <thead>
                    <tr>
                        <th>Form name</th>
                        <th>Document code</th>
                        <th>Status</th>
                        <th>Submitted at</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attachmentInstances as $attachment)
                        <tr>
                            <td>{{ $attachment->submissionForm->name ?? 'Attachment' }}</td>
                            <td>{{ $attachment->getDocumentControlNumber() ?? $attachment->form_number ?? '—' }}</td>
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
