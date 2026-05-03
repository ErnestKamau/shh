<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-search text-primary"></i>
                                {{ __('personnel.audit_logs') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('personnel.audit_logs_overview') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.search_audit_logs') }}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-primary w-100" wire:click="toggleAdvancedFilters">
                        <i class="mdi mdi-tune"></i> {{ __('personnel.filters') }}
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters">
                        <i class="mdi mdi-refresh"></i> {{ __('personnel.clear') }}
                    </button>
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.live="perPage">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ __('personnel.show') }} {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($showAdvancedFilters)
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="small text-muted">{{ __('personnel.user') }}</label>
                        <select class="form-control" wire:model.live="userFilter">
                            <option value="">{{ __('personnel.all_users') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">{{ __('personnel.event') }}</label>
                        <select class="form-control" wire:model.live="eventFilter">
                            <option value="">{{ __('personnel.all_events') }}</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">{{ __('personnel.entity') }}</label>
                        <select class="form-control" wire:model.live="entityFilter">
                            <option value="">{{ __('personnel.all_entities') }}</option>
                            @foreach($entities as $entity)
                                <option value="{{ $entity }}">{{ class_basename($entity) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">{{ __('personnel.ip_address') }}</label>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="ipFilter" placeholder="{{ __('personnel.filter_ip') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="small text-muted">{{ __('personnel.from') }}</label>
                        <input type="date" class="form-control" wire:model.live="dateFrom">
                    </div>
                    <div class="col-md-1">
                        <label class="small text-muted">{{ __('personnel.to') }}</label>
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
                                    {{ __('personnel.user') }} <i class="{{ $this->sortIcon('u.name') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('u.email')">
                                    {{ __('personnel.email') }} <i class="{{ $this->sortIcon('u.email') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.event')">
                                    {{ __('personnel.event') }} <i class="{{ $this->sortIcon('audits.event') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.auditable_type')">
                                    {{ __('personnel.entity') }} <i class="{{ $this->sortIcon('audits.auditable_type') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.auditable_id')">
                                    {{ __('personnel.entity_id') }} <i class="{{ $this->sortIcon('audits.auditable_id') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.ip_address')">
                                    {{ __('personnel.ip_address') }} <i class="{{ $this->sortIcon('audits.ip_address') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.url')">
                                    {{ __('personnel.url') }} <i class="{{ $this->sortIcon('audits.url') }}"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" class="btn btn-link p-0 text-dark" wire:click="sortBy('audits.created_at')">
                                    {{ __('personnel.date') }} <i class="{{ $this->sortIcon('audits.created_at') }}"></i>
                                </button>
                            </th>
                            <th>{{ __('personnel.changes') }}</th>
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
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--info" wire:click="openChangesModal({{ $item->id }})" title="{{ __('personnel.view_changes') }}">
                                        <i class="mdi mdi-alert-decagram"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">{{ __('personnel.no_audit_logs_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div wire:loading.flex wire:target="search,perPage,userFilter,eventFilter,entityFilter,ipFilter,dateFrom,dateTo,sortBy" class="small text-muted mt-2">
                <i class="mdi mdi-loading mdi-spin"></i> {{ __('personnel.loading_audit_logs') }}
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    {{ __('personnel.showing_to_of', ['from' => $this->audits->firstItem() ?? 0, 'to' => $this->audits->lastItem() ?? 0, 'total' => $this->audits->total()]) }}
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
                        <h4 class="modal-title"><i class="mdi mdi-alert-decagram"></i> {{ __('personnel.audit_changes') }}</h4>
                        <button type="button" class="close" wire:click="closeChangesModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table-condensed table table-sm table-banded table-hover table-xs table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('personnel.field') }}</th>
                                        <th>{{ __('personnel.new') }}</th>
                                        <th>{{ __('personnel.old') }}</th>
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
                                                <i class="mdi mdi-information"></i> {{ __('personnel.no_data_available') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" wire:click="closeChangesModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .pm-act-btn { border-radius: 7px; padding: 4px 8px; margin-right: 3px; font-size: 12px; }
        .pm-act-btn:last-child { margin-right: 0; }
        .pm-act-btn--info { border: 1px solid #bae6fd; color: #0369a1; background: #f0f9ff; }
        .pm-act-btn--info:hover { background: #e0f2fe; border-color: #7dd3fc; }
    </style>
</div>
