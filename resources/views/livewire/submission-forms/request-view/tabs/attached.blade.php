<div class="workflow-board-panel-body flush-top px-0">
    @php
        $trfi = $instance->testRequestFormInstance;
    @endphp

    <div class="p-3 border-bottom bg-light">
        <span class="small font-weight-bold text-muted text-uppercase">Attached forms</span>
        <p class="mb-0 small text-muted">Test request forms linked to this submission. Use <strong>Actions → Generate Test Request Form</strong> to create or refresh the PDF.</p>
    </div>

    @if($trfi)
        @php
            $trfForm = $trfi->testRequestForm;
            $trfTitle = $trfForm->name ?? 'Test Request Form';
            $trfCode = $trfForm->code ?? null;
            $storagePath = 'test-request-forms/trf-' . $trfi->id . '.pdf';
            $pdfExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($storagePath);
            $sampleRowCount = count($trfi->form_data['sample_rows'] ?? []);
            $descriptionParts = array_filter([
                $trfCode ? 'Code: ' . $trfCode : null,
                $sampleRowCount > 0 ? $sampleRowCount . ' sample ' . ($sampleRowCount === 1 ? 'line' : 'lines') : null,
                $trfi->samplingSchedule ? 'Schedule: ' . $trfi->samplingSchedule->title : null,
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
                        <td class="small">{{ $trfi->creator?->name ?? '—' }}</td>
                        <td class="text-nowrap small">{{ $trfi->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('test-request-form.preview', $trfi->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mr-1">
                                View
                            </a>
                            @if($pdfExists)
                                <a href="{{ route('test-request-form.pdf', $trfi->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary mr-1">
                                    PDF
                                </a>
                                <a href="{{ route('test-request-form.download', $trfi->id) }}" class="btn btn-sm btn-outline-secondary">
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
