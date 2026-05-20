<div class="workflow-board-panel-body flush-top px-0">
    @if($attachmentInstances->isEmpty())
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
                </tbody>
            </table>
        </div>
    @endif
</div>
