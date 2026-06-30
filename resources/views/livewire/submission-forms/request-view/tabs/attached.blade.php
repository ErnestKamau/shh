<div class="workflow-board-panel-body flush-top px-0">
    @php
        $isTrf = $this->isTrfForm();
        $storagePath = $isTrf ? 'test-request-forms/trf-sfi-' . $instance->id . '.pdf' : null;
        $pdfExists = $isTrf && \Illuminate\Support\Facades\Storage::disk('public')->exists($storagePath);
        $sampleLineCount = $isTrf ? count($this->sampleLines) : 0;
    @endphp

    <div class="p-3 border-bottom bg-light">
        <span class="small font-weight-bold text-muted text-uppercase">Attached forms</span>
        <p class="mb-0 small text-muted">Test request forms linked to this submission. Use <strong>Actions → Generate Test Request Form</strong> to create or refresh the PDF.</p>
    </div>

    @if($isTrf)
        @php
            $trfTitle = $instance->submissionForm?->name ?? 'Test Request Form';
            $trfCode = $instance->submissionForm?->document_code ?? null;
            $descriptionParts = array_filter([
                $trfCode ? 'Code: ' . $trfCode : null,
                $sampleLineCount > 0 ? $sampleLineCount . ' sample ' . ($sampleLineCount === 1 ? 'line' : 'lines') : null,
                $instance->form_number ? 'Ref: ' . $instance->form_number : null,
            ]);
        @endphp

        <div class="table-responsive">
            <table class="table table-hover workflow-table mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Submitted by</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span class="font-weight-bold">{{ $trfTitle }}</span>
                            @if($trfCode)
                                <small class="d-block text-muted">{{ $trfCode }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">Test Request Form</span>
                        </td>
                        <td class="small text-muted">
                            {{ !empty($descriptionParts) ? implode(' · ', $descriptionParts) : '—' }}
                        </td>
                        <td class="small">{{ $instance->submittedBy?->name ?? '—' }}</td>
                        <td class="text-nowrap small">{{ $instance->submitted_at?->format('Y-m-d H:i') ?? $instance->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('test-request-form.preview', $instance->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mr-1">
                                View
                            </a>
                            @if($pdfExists)
                                <a href="{{ route('test-request-form.pdf', $instance->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary mr-1">
                                    PDF
                                </a>
                                <a href="{{ route('test-request-form.download', $instance->id) }}" class="btn btn-sm btn-outline-secondary">
                                    Download
                                </a>
                            @else
                                <span class="text-muted small">PDF not generated yet</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted mb-0 py-3 px-3">No test request form is attached to this request.</p>
    @endif
</div>
