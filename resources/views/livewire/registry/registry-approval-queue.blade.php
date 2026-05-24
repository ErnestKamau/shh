<div class="container-fluid">
    @include('layouts.registry.partials.page-header', [
        'title' => 'Approval Queue',
        'description' => 'Review and action requests awaiting approval.',
        'icon' => 'mdi-check-decagram',
    ])

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>Reference</th>
                                    <th>Subject</th>
                                    <th>Stage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $r)
                                    <tr>
                                        <td>{{ $r->reference_no }}</td>
                                        <td>{{ Str::limit($r->subject, 60) }}</td>
                                        <td>
                                            @include('layouts.registry.partials.stage-badge', ['stage' => $r->current_stage])
                                        </td>
                                        <td>
                                            <a href="{{ route('registry.requests.show', $r->id) }}"
                                               class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                               title="Review">
                                                <i class="mdi mdi-clipboard-check"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No pending approvals.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
