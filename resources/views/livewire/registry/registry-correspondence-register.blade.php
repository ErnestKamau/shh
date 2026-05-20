<div class="container-fluid">
    @include('layouts.registry.partials.page-header', [
        'title' => 'Correspondence Register',
        'description' => 'Browse incoming and outgoing correspondence records.',
        'icon' => 'mdi-book-open-page-variant',
    ])

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Direction</label>
                            <div class="btn-group d-flex" role="group">
                                <button type="button"
                                        class="btn btn-sm {{ $direction === 'incoming' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="$set('direction', 'incoming')">
                                    Incoming
                                </button>
                                <button type="button"
                                        class="btn btn-sm {{ $direction === 'outgoing' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="$set('direction', 'outgoing')">
                                    Outgoing
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Search</label>
                            <input wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search register...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                    <th>Party</th>
                                    <th>Received</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $r)
                                    <tr>
                                        <td>{{ $r->reference_no }}</td>
                                        <td>{{ Str::limit($r->subject, 50) }}</td>
                                        <td>{{ $r->submitting_party }}</td>
                                        <td>{{ $r->received_at?->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="{{ route('registry.requests.show', $r->id) }}"
                                               class="btn btn-sm rm-act-btn rm-act-btn--view"
                                               title="View">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No correspondence records.</td>
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
