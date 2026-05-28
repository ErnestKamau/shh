<div class="container-fluid">
    @include('layouts.registry.partials.page-header', [
        'title' => 'Registry Requests',
        'description' => 'Search, filter, and manage all correspondence requests in the registry.',
        'icon' => 'mdi-file-document-multiple',
        'actions' => view('livewire.registry.partials.request-table-header-actions')->render(),
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
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Search</label>
                            <input wire:model.live.debounce.300ms="search" class="form-control" placeholder="Reference, subject...">
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Status</label>
                            <select wire:model.live="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="open">Open</option>
                                <option value="pending_approval">Pending Approval</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Category</label>
                            <select wire:model.live="categoryId" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Direction</label>
                            <select wire:model.live="direction" class="form-control">
                                <option value="">All Directions</option>
                                <option value="incoming">Incoming</option>
                                <option value="outgoing">Outgoing</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="button" wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                <i class="mdi mdi-refresh"></i> Clear
                            </button>
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
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Stage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $r)
                                    <tr>
                                        <td>{{ $r->reference_no }}</td>
                                        <td>{{ Str::limit($r->subject, 50) }}</td>
                                        <td>{{ $r->category?->name }}</td>
                                        <td>
                                            @include('layouts.registry.partials.status-badge', ['status' => $r->status])
                                        </td>
                                        <td>
                                            @include('layouts.registry.partials.stage-badge', ['stage' => $r->current_stage])
                                        </td>
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
                                        <td colspan="6" class="text-center text-muted py-4">No requests found.</td>
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
