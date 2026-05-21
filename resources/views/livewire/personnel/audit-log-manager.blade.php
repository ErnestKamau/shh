<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1 fw-bold text-dark" style="font-family: 'Inter', sans-serif;">
                                <i class="mdi mdi-shield-search text-primary me-2"></i>
                                @if($viewMode === 'users')
                                    {{ __('personnel.audit_logs') }}
                                @else
                                    Audit Logs: <span class="text-primary">{{ $selectedUserName }}</span>
                                @endif
                            </h2>
                            <p class="text-muted mb-0 font-size-14">
                                @if($viewMode === 'users')
                                    Select a user to view their complete audit trail and activity history.
                                @else
                                    Viewing specific audit events and changes.
                                @endif
                            </p>
                        </div>
                        @if($viewMode === 'logs')
                            <div>
                                <button type="button" class="btn btn-primary rounded-pill shadow-sm" wire:click="backToUsers">
                                    <i class="mdi mdi-arrow-left me-1"></i> Back to Users
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($viewMode === 'users')
        <!-- Users Dashboard View -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0"><i class="mdi mdi-magnify text-muted"></i></span>
                    <input type="text" class="form-control border-0 py-2" wire:model.live.debounce.300ms="userListSearch" placeholder="Search users by name or email...">
                </div>
            </div>
        </div>

        <div class="table-responsive shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #f1f5f9;">
            <table class="table table-hover align-middle mb-0 bg-white">
                <thead class="table-light text-muted">
                    <tr style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th class="ps-4 fw-medium border-0 py-3">User / Entity</th>
                        <th class="fw-medium border-0 py-3">Total Logs</th>
                        <th class="fw-medium border-0 py-3">Last Active</th>
                        <th class="text-end pe-4 fw-medium border-0 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <!-- System Row -->
                    <tr class="cursor-pointer transition-all" wire:click="viewSystemLogs">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-3" style="width: 44px; height: 44px; min-width: 44px;">
                                    <div class="rounded-circle badge-soft-secondary d-flex align-items-center justify-content-center h-100 w-100" style="font-size: 20px;">
                                        <i class="mdi mdi-robot-outline"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0 font-size-15 text-dark fw-bold">System / Automated</h6>
                                    <small class="text-muted">Background Processes</small>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border px-3 py-2 font-size-13"><i class="mdi mdi-history me-1"></i> {{ number_format($this->systemStats->total_logs ?? 0) }}</span>
                        </td>
                        <td class="py-3">
                            <div class="text-dark font-size-13">
                                @if($this->systemStats && $this->systemStats->last_active)
                                    <i class="mdi mdi-clock-outline text-muted me-1"></i> {{ \Carbon\Carbon::parse($this->systemStats->last_active)->diffForHumans() }}
                                @else
                                    <span class="text-muted fst-italic">No activity</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end pe-4 py-3">
                            <button class="btn btn-sm btn-soft-primary rounded-pill px-3 transition-all">
                                View Logs <i class="mdi mdi-arrow-right ms-1"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- User Rows -->
                    @forelse($this->usersList as $user)
                        <tr class="cursor-pointer transition-all" wire:click="viewUserLogs('{{ $user->id }}', '{{ addslashes($user->name) }}')">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3" style="width: 44px; height: 44px; min-width: 44px;">
                                        <div class="rounded-circle badge-soft-primary d-flex align-items-center justify-content-center h-100 w-100 fw-bold" style="font-size: 14px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-size-15 text-dark fw-bold">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border px-3 py-2 font-size-13"><i class="mdi mdi-history me-1"></i> {{ number_format($user->total_logs) }}</span>
                            </td>
                            <td class="py-3">
                                <div class="text-dark font-size-13">
                                    @if($user->last_active)
                                        <i class="mdi mdi-clock-outline text-muted me-1"></i> {{ \Carbon\Carbon::parse($user->last_active)->diffForHumans() }}
                                    @else
                                        <span class="text-muted fst-italic">No activity</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-4 py-3">
                                <button class="btn btn-sm btn-soft-primary rounded-pill px-3 transition-all">
                                    View Logs <i class="mdi mdi-arrow-right ms-1"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-0">
                                <div class="text-center py-5 bg-white">
                                    <i class="mdi mdi-account-search-outline text-muted" style="font-size: 48px; opacity: 0.5;"></i>
                                    <h5 class="text-muted mt-3">No users found</h5>
                                    <p class="text-muted mb-0 font-size-13">Try adjusting your search query.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->usersList->links('pagination::bootstrap-4') }}
        </div>
        
        <div wire:loading.flex wire:target="userListSearch" class="justify-content-center align-items-center position-fixed w-100 h-100" style="top: 0; left: 0; z-index: 1050; background: rgba(255,255,255,0.7);">
            <div class="bg-white p-3 rounded-pill shadow d-flex align-items-center text-primary border">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="fw-medium pe-2">Searching users...</span>
            </div>
        </div>

    @else
        <!-- Logs View -->
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4 position-relative">
                <!-- Top Controls -->
                <div class="row g-3 mb-4 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Search specific logs...">
                        </div>
                    </div>
                    <div class="col-md-7 d-flex justify-content-md-end gap-2">
                        <button type="button" class="btn btn-light" wire:click="clearFilters">
                            <i class="mdi mdi-refresh me-1"></i> {{ __('personnel.clear') }}
                        </button>
                        <button type="button" class="btn btn-{{ $showAdvancedFilters ? 'primary' : 'outline-primary' }}" wire:click="toggleAdvancedFilters">
                            <i class="mdi mdi-filter-variant me-1"></i> {{ __('personnel.filters') }}
                        </button>
                        <div style="width: 120px;">
                            <select class="form-select form-control" wire:model.live="perPage">
                                @foreach($perPageOptions as $option)
                                    <option value="{{ $option }}">{{ $option }} per page</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters -->
                @if($showAdvancedFilters)
                    <div class="bg-light p-3 rounded-3 mb-4 shadow-sm border border-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('personnel.event') }}</label>
                                <select class="form-select form-control form-control-sm" wire:model.live="eventFilter">
                                    <option value="">{{ __('personnel.all_events') }}</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('personnel.entity') }}</label>
                                <select class="form-select form-control form-control-sm" wire:model.live="entityFilter">
                                    <option value="">{{ __('personnel.all_entities') }}</option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity }}">{{ class_basename($entity) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('personnel.ip_address') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="ipFilter" placeholder="192.168...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('personnel.from') }}</label>
                                <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('personnel.to') }}</label>
                                <input type="date" class="form-control form-control-sm" wire:model.live="dateTo">
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Data Table -->
                <div class="table-responsive" style="border-radius: 8px; overflow: hidden; border: 1px solid #f1f5f9;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                <th class="ps-4 fw-medium border-0 cursor-pointer" wire:click="sortBy('audits.event')">
                                    Event <i class="{{ $this->sortIcon('audits.event') }} ms-1"></i>
                                </th>
                                <th class="fw-medium border-0 cursor-pointer" wire:click="sortBy('audits.auditable_type')">
                                    Entity <i class="{{ $this->sortIcon('audits.auditable_type') }} ms-1"></i>
                                </th>
                                <th class="fw-medium border-0 cursor-pointer" wire:click="sortBy('audits.ip_address')">
                                    IP Address <i class="{{ $this->sortIcon('audits.ip_address') }} ms-1"></i>
                                </th>
                                <th class="fw-medium border-0 cursor-pointer" wire:click="sortBy('audits.created_at')">
                                    Date <i class="{{ $this->sortIcon('audits.created_at') }} ms-1"></i>
                                </th>
                                <th class="text-end pe-4 fw-medium border-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($this->audits as $item)
                                <tr>
                                    <td class="ps-4">
                                        @php
                                            $badgeClass = 'badge-soft-secondary';
                                            $icon = 'mdi-help-circle-outline';
                                            if ($item->event === 'created') { $badgeClass = 'badge-soft-success'; $icon = 'mdi-plus-circle-outline'; }
                                            elseif ($item->event === 'updated') { $badgeClass = 'badge-soft-info'; $icon = 'mdi-pencil-outline'; }
                                            elseif ($item->event === 'deleted') { $badgeClass = 'badge-soft-danger'; $icon = 'mdi-trash-can-outline'; }
                                            elseif ($item->event === 'restored') { $badgeClass = 'badge-soft-warning'; $icon = 'mdi-restore'; }
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1 fw-medium" style="font-size: 11.5px;">
                                            <i class="mdi {{ $icon }} me-1"></i>{{ ucfirst($item->event) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-medium">{{ class_basename($item->auditable_type ?? '') }}</div>
                                        <small class="text-muted" style="font-size: 11px;">ID: {{ $item->auditable_id }}</small>
                                    </td>
                                    <td>
                                        <span class="text-muted font-size-13"><i class="mdi mdi-lan me-1 text-secondary"></i>{{ $item->ip_address }}</span>
                                    </td>
                                    <td>
                                        <div class="text-dark font-size-13">{{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y') }}</div>
                                        <small class="text-muted" style="font-size: 11px;">{{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-soft-primary rounded-pill px-3 transition-all" wire:click="openChangesModal('{{ $item->id }}')">
                                            <i class="mdi mdi-eye-outline me-1"></i> View Changes
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="mb-3 text-muted">
                                            <i class="mdi mdi-shield-off-outline" style="font-size: 48px; opacity: 0.5;"></i>
                                        </div>
                                        <h5 class="text-muted">No audit logs found for {{ $selectedUserName }}</h5>
                                        <p class="text-muted mb-0 font-size-13">Try adjusting your filters or search terms.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination & Loader -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Showing <span class="fw-semibold">{{ $this->audits->firstItem() ?? 0 }}</span> to <span class="fw-semibold">{{ $this->audits->lastItem() ?? 0 }}</span> of <span class="fw-semibold">{{ $this->audits->total() }}</span> logs
                    </div>
                    <div>
                        {{ $this->audits->links('pagination::bootstrap-4') }}
                    </div>
                </div>
                
                <div wire:loading.flex wire:target="search,perPage,eventFilter,entityFilter,ipFilter,dateFrom,dateTo,sortBy,clearFilters" class="justify-content-center align-items-center position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 10; background: rgba(255,255,255,0.7); border-radius: 12px;">
                    <div class="bg-white p-3 rounded-pill shadow-sm d-flex align-items-center text-primary border">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span class="fw-medium pe-2">Refreshing logs...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Changes Modal -->
        @if($showChangesModal)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: 85vh;">
                        <div class="modal-header bg-light border-bottom-0 pb-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                                <i class="mdi mdi-history text-primary me-2 fs-4"></i> Audit Changes Details
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeChangesModal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            @if(empty($changeColumns))
                                <div class="text-center py-5 bg-light rounded-3 border border-light">
                                    <i class="mdi mdi-information-outline text-muted fs-1 mb-2"></i>
                                    <h6 class="text-muted fw-medium">No explicit field changes recorded for this event.</h6>
                                    <p class="text-muted small mb-0">This typically happens during initial creation where tracking individual field deltas is not configured.</p>
                                </div>
                            @else
                                <div class="table-responsive rounded border border-light">
                                    <table class="table table-borderless table-hover mb-0 align-middle">
                                        <thead class="bg-light">
                                            <tr class="text-muted small text-uppercase">
                                                <th class="ps-3 w-25">Field Name</th>
                                                <th class="w-35 text-danger border-start border-light">Old Value</th>
                                                <th class="w-35 text-success border-start border-light">New Value</th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                            @foreach($changeColumns as $column)
                                                <tr class="border-bottom border-light">
                                                    <td class="ps-3 fw-medium text-dark font-size-13 bg-light" style="width: 25%; word-break: break-all;">
                                                        {{ ucwords(str_replace('_', ' ', $column)) }}
                                                        <div class="small text-muted font-monospace" style="font-size: 10px;">{{ $column }}</div>
                                                    </td>
                                                    <td class="border-start border-light" style="width: 35%;">
                                                        @if(array_key_exists($column, $oldValues))
                                                            <div class="p-2 rounded font-size-13 text-wrap text-break font-monospace diff-old" style="max-height: 200px; overflow-y: auto; white-space: pre-wrap;">{{ is_array($oldValues[$column]) ? json_encode($oldValues[$column], JSON_PRETTY_PRINT) : (is_bool($oldValues[$column]) ? ($oldValues[$column] ? 'true' : 'false') : ($oldValues[$column] === null ? 'null' : (string)$oldValues[$column])) }}</div>
                                                        @else
                                                            <span class="text-muted small fst-italic ms-2">Not recorded</span>
                                                        @endif
                                                    </td>
                                                    <td class="border-start border-light" style="width: 35%;">
                                                        @if(array_key_exists($column, $newValues))
                                                            <div class="p-2 rounded font-size-13 text-wrap text-break font-monospace diff-new" style="max-height: 200px; overflow-y: auto; white-space: pre-wrap;">{{ is_array($newValues[$column]) ? json_encode($newValues[$column], JSON_PRETTY_PRINT) : (is_bool($newValues[$column]) ? ($newValues[$column] ? 'true' : 'false') : ($newValues[$column] === null ? 'null' : (string)$newValues[$column])) }}</div>
                                                        @else
                                                            <span class="text-muted small fst-italic ms-2">Not recorded</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4 bg-white">
                            <button type="button" class="btn btn-secondary px-4 rounded-pill" wire:click="closeChangesModal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <style>
        .user-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border-color: #e2e8f0;
        }
        .btn-soft-primary {
            background-color: rgba(85, 110, 230, 0.1);
            color: #556ee6;
            border-color: transparent;
        }
        .btn-soft-primary:hover {
            background-color: #556ee6;
            color: #fff;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(241, 245, 249, 0.4);
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .cursor-pointer:hover {
            background-color: #f8f9fa;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
        .diff-old {
            background-color: rgba(220, 53, 69, 0.08);
            color: #d32f2f;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        .diff-new {
            background-color: rgba(40, 167, 69, 0.08);
            color: #2e7d32;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        .badge-soft-primary {
            background-color: rgba(85, 110, 230, 0.1);
            color: #556ee6;
            border: 1px solid rgba(85, 110, 230, 0.2);
        }
        .badge-soft-success {
            background-color: rgba(52, 195, 143, 0.1);
            color: #34c38f;
            border: 1px solid rgba(52, 195, 143, 0.2);
        }
        .badge-soft-info {
            background-color: rgba(80, 165, 241, 0.1);
            color: #50a5f1;
            border: 1px solid rgba(80, 165, 241, 0.2);
        }
        .badge-soft-warning {
            background-color: rgba(241, 180, 76, 0.1);
            color: #f1b44c;
            border: 1px solid rgba(241, 180, 76, 0.2);
        }
        .badge-soft-danger {
            background-color: rgba(244, 106, 106, 0.1);
            color: #f46a6a;
            border: 1px solid rgba(244, 106, 106, 0.2);
        }
        .badge-soft-secondary {
            background-color: rgba(116, 120, 141, 0.1);
            color: #74788d;
            border: 1px solid rgba(116, 120, 141, 0.2);
        }
    </style>
</div>
