<div>
    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Ticket #, customer, description...">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Category</label>
                        <select wire:model.live="filters.category" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Assigned</label>
                        <select wire:model.live="filters.assigned_to" class="form-control">
                            <option value="">All</option>
                            <option value="me">Assigned to Me</option>
                            <option value="unassigned">Unassigned</option>
                            @foreach($developers as $dev)
                                <option value="{{ $dev->name }}">{{ $dev->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Priority</label>
                        <select wire:model.live="filters.priority" class="form-control">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->value }}">{{ $priority->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>&nbsp;</label>
                        <button type="button" wire:click="resetFilters" class="btn btn-secondary btn-block">
                            <i class="mdi mdi-refresh"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-body">
            @if($tickets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Priority</th>
                                <th>Archived At</th>
                                <th>Archived By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr>
                                    <td><strong>{{ $ticket->ticket_no ?: $ticket->complaint_id }}</strong></td>
                                    <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($ticket->description, 50) }}</td>
                                    <td>
                                        <span class="badge {{ $ticket->priorityBadge }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>{{ $ticket->deleted_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $ticket->created_by ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-info">
                                            <i class="mdi mdi-eye"></i> View
                                        </a>
                                        @if($isDeveloper)
                                            <span class="text-muted small d-block mt-1">
                                                <i class="mdi mdi-information"></i> Restore/Delete available in Developer App
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap"
                    wire:key="pagination-container">
                    <div class="d-flex align-items-center mb-2">
                        <select wire:model.live="perPage" wire:key="per-page-select"
                            class="form-control form-control-sm mr-2" style="width: auto;">
                            <option value="10">10 per page</option>
                            <option value="20">20 per page</option>
                            <option value="25">25 per page</option>
                            <option value="30">30 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                        <span class="text-muted small">
                            Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }} of
                            {{ $tickets->total() }} results
                        </span>
                    </div>
                    <div>
                        {{ $tickets->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="mdi mdi-information" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No archived tickets found</h5>
                    <p>No tickets match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>