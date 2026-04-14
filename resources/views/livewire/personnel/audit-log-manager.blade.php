<div class="container-fluid">
    <div class="card tab-card">
        <div class="card-header tab-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-file-search"></i> Audit Logs</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search user, email, event, entity, id, ip, url, user-agent, tags...">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-primary w-100" wire:click="toggleAdvancedFilters">
                        <i class="mdi mdi-tune"></i> Filters
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters">
                        <i class="mdi mdi-refresh"></i> Clear
                    </button>
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.live="perPage">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">Show {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($showAdvancedFilters)
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="small text-muted">User</label>
                        <select class="form-control" wire:model.live="userFilter">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Event</label>
                        <select class="form-control" wire:model.live="eventFilter">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Entity</label>
                        <select class="form-control" wire:model.live="entityFilter">
                            <option value="">All Entities</option>
                            @foreach($entities as $entity)
                                <option value="{{ $entity }}">{{ class_basename($entity) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">IP Address</label>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="ipFilter" placeholder="Filter IP">
                    </div>
                    <div class="col-md-1">
                        <label class="small text-muted">From</label>
                        <input type="date" class="form-control" wire:model.live="dateFrom">
                    </div>
                    <div class="col-md-1">
                        <label class="small text-muted">To</label>
                        <input type="date" class="form-control" wire:model.live="dateTo">
                    </div>
                </div>
            @endif

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>#</th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('u.name')">
                                    User <i class="{{ $this->sortIcon('u.name') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('u.email')">
                                    Email <i class="{{ $this->sortIcon('u.email') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.event')">
                                    Event <i class="{{ $this->sortIcon('audits.event') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.auditable_type')">
                                    Entity <i class="{{ $this->sortIcon('audits.auditable_type') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.auditable_id')">
                                    Entity ID <i class="{{ $this->sortIcon('audits.auditable_id') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.ip_address')">
                                    IP Address <i class="{{ $this->sortIcon('audits.ip_address') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.url')">
                                    URL <i class="{{ $this->sortIcon('audits.url') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.created_at')">
                                    Date <i class="{{ $this->sortIcon('audits.created_at') }}"></i>
                                </button>
                            </th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->audits as $item)
                            <tr>
                                <td>{{ $this->audits->firstItem() + $loop->index }}</td>
                                <td>{{ $item->user_name ?? '-' }}</td>
                                <td>{{ $item->user_email ?? '-' }}</td>
                                <td>{{ $item->event }}</td>
                                <td>{{ class_basename($item->auditable_type ?? '') }}</td>
                                <td>{{ $item->auditable_id }}</td>
                                <td>{{ $item->ip_address }}</td>
                                <td>{{ $item->url }}</td>
                                <td>{{ $item->created_at }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-info btn-sm" wire:click="openChangesModal({{ $item->id }})" title="View Changes">
                                        <i class="mdi mdi-alert-decagram"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div wire:loading.flex wire:target="search,perPage,userFilter,eventFilter,entityFilter,ipFilter,dateFrom,dateTo,sortBy" class="small text-muted mt-2">
                <i class="mdi mdi-loading mdi-spin"></i> Loading audit logs...
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    Showing {{ $this->audits->firstItem() ?? 0 }} to {{ $this->audits->lastItem() ?? 0 }} of {{ $this->audits->total() }} records
                </small>
                {{ $this->audits->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    @if($showChangesModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-alert-decagram"></i> Audit Changes</h4>
                        <button type="button" class="close" wire:click="closeChangesModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table-condensed table table-sm table-banded table-hover table-xs table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>New</th>
                                        <th>Old</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($changeColumns as $column)
                                        <tr>
                                            <th nowrap>{{ $column }}</th>
                                            <td nowrap>{{ $newValues[$column] ?? '-' }}</td>
                                            <td nowrap>{{ $oldValues[$column] ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">
                                                <i class="mdi mdi-information"></i> No Data Available
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" wire:click="closeChangesModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
