<div>
    <style>
        .ticket-card {
            border-left: 4px solid #6c757d;
            transition: all 0.3s;
        }

        .ticket-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .ticket-card.priority-high {
            border-left-color: #dc3545;
        }

        .ticket-card.priority-medium {
            border-left-color: #ffc107;
        }

        .ticket-card.priority-low {
            border-left-color: #28a745;
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 0.4em 0.8em;
        }

        .substringed {
            cursor: pointer;
        }

        .substringed .hoverable {
            display: none;
        }

        .substringed:hover .hoverable {
            display: unset !important;
        }

        .substringed:hover .default-seen {
            display: none !important;
        }

        .substringed .default-seen {
            display: unset !important;
        }
    </style>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form wire:submit.prevent="resetFilters" class="row">
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label>Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Search by ticket #, description...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Status</label>
                        <select wire:model.live="status" class="form-control">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption->workflow_value }}">
                                    {{ $statusOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Priority</label>
                        <select wire:model.live="priority" class="form-control">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $priorityOption)
                                <option value="{{ $priorityOption->value }}">
                                    {{ $priorityOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" wire:click="resetFilters" class="btn btn-info btn-block">
                                <i class="mdi mdi-filter"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="card">
        <div class="card-body">
            @if($tickets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Raised By</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assigned To</th>
                                <th>TAT</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr>
                                    <td>
                                        <strong>{{ $ticket->ticket_no ?: $ticket->complaint_id }}</strong>
                                        @if(isset($unreadCounts[$ticket->id]) && $unreadCounts[$ticket->id] > 0)
                                            <span class="badge badge-danger ml-2" title="Unread messages">
                                                <i class="mdi mdi-message-text"></i> {{ $unreadCounts[$ticket->id] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->raised_by ?? $ticket->created_by }}</td>
                                    <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="substringed">
                                            <span
                                                class="default-seen">{{ \Illuminate\Support\Str::limit($ticket->description, 50) }}</span>
                                            <span class="hoverable">{{ $ticket->description }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $ticket->statusBadge }} status-badge">
                                            {{ $ticket->workflowName }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $ticket->priorityBadge }} status-badge">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->assignedUser)
                                            {{ $ticket->assignedUser->name }}
                                        @elseif($ticket->assignedDevelopers && $ticket->assignedDevelopers->count() > 0)
                                            {{ $ticket->assignedDevelopers->pluck('name')->implode(', ') }}
                                        @else
                                            Unassigned
                                        @endif
                                    </td>
                                    <td>{{ $ticket->tat_display }}</td>
                                    <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-info"
                                            title="View Ticket">
                                            <i class="mdi mdi-eye"></i> View
                                        </a>
                                        @if(in_array($ticket->complaint_workflow, [2, 3]))
                                            <a href="{{ route('tickets.show', $ticket->id) }}#chat-tab"
                                                class="btn btn-sm btn-outline-primary" title="Go to Chat">
                                                <i class="mdi mdi-message-text"></i> Chat
                                            </a>
                                        @endif
                                        @php
                                            $draftStatus = \App\Models\CRM\TicketStatus::active()
                                                ->where(function ($query) {
                                                    $query->where('name', 'like', '%Draft%');
                                                })
                                                ->where('workflow_value', $ticket->complaint_workflow)
                                                ->first();
                                            $isDraft = $draftStatus !== null;
                                        @endphp
                                        @if($isDraft)
                                            <a href="{{ route('tickets.show', $ticket->id) }}"
                                                class="btn btn-sm btn-outline-warning">
                                                <i class="mdi mdi-archive"></i> Archive
                                            </a>
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
                            <option value="15">15 per page</option>
                            <option value="20">20 per page</option>
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
                    <h5 class="mt-3">No tickets found</h5>
                    <p>You haven't submitted any tickets yet.</p>
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Create Your First Ticket
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>