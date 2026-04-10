<div>
    <style>
        /* Modern Table Styles - Google-inspired */
        .modern-table-wrapper {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        .modern-search-bar {
            background: #ffffff;
            border-bottom: 1px solid #e8eaed;
            padding: 16px 24px;
        }

        .modern-search-input {
            border: 1px solid #dadce0;
            border-radius: 24px;
            padding: 10px 16px 10px 44px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .modern-search-input:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .modern-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #5f6368;
            font-size: 18px;
        }

        .modern-filters-bar {
            background: #f8f9fa;
            border-bottom: 1px solid #e8eaed;
            padding: 12px 24px;
        }

        .modern-filter-btn {
            border: 1px solid #dadce0;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 500;
            color: #5f6368;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .modern-filter-btn:hover {
            background: #f1f3f4;
            border-color: #28a745;
            color: #28a745;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #ffffff;
        }

        .modern-table thead {
            background: #f8f9fa;
        }

        .modern-table thead th {
            padding: 12px 24px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #5f6368;
            border-bottom: 1px solid #e8eaed;
            white-space: nowrap;
            position: relative;
        }

        .modern-table thead th.sortable {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.2s ease;
        }

        .modern-table thead th.sortable:hover {
            background: #f1f3f4;
        }

        .modern-table thead th.sortable .sort-icon {
            margin-left: 8px;
            color: #28a745;
            font-size: 14px;
        }

        .modern-table tbody tr {
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.15s ease;
        }

        .modern-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .modern-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .modern-table tbody tr:hover {
            background: #e6f4ea !important;
        }

        .modern-table tbody tr.table-danger {
            background: #fff5f5 !important;
        }

        .modern-table tbody tr.table-danger:nth-child(even) {
            background: #fff5f5 !important;
        }

        .modern-table tbody tr.table-danger:hover {
            background: #ffe5e5 !important;
        }

        .modern-table tbody td {
            padding: 16px 24px;
            font-size: 14px;
            color: #202124;
            vertical-align: middle;
        }

        .modern-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .modern-action-btn {
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
            margin-right: 4px;
        }

        .modern-action-btn:hover {
            background: #f1f3f4;
            border-color: #28a745;
        }

        .modern-empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .modern-empty-state i {
            font-size: 64px;
            color: #dadce0;
            margin-bottom: 16px;
        }

        .modern-empty-state p {
            color: #5f6368;
            font-size: 14px;
            margin: 0;
        }

        .modern-pagination {
            padding: 16px 24px;
            border-top: 1px solid #e8eaed;
            background: #ffffff;
        }
    </style>

    <!-- Search Bar -->
    <div class="modern-search-bar">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="mdi mdi-magnify modern-search-icon"></i>
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           class="modern-search-input" 
                           placeholder="Search by CAPA number, description...">
                </div>
            </div>
            <div class="col-md-2">
                <select wire:model.live="perPage" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                    <option value="15">15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
            <div class="col-md-4 text-right">
                <button wire:click="$toggle('showFilters')" class="modern-filter-btn">
                    <i class="mdi mdi-filter-variant"></i> {{ $showFilters ? 'Hide' : 'Show' }} Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    @if($showFilters)
    <div class="modern-filters-bar">
        <div class="row">
            <div class="col-md-2">
                <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Priority</label>
                <select wire:model.live="filters.priority_id" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0; font-size: 13px;">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $priority)
                    <option value="{{ $priority->id ?? $priority }}">{{ is_object($priority) ? $priority->name : $priority }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Type</label>
                <select wire:model.live="filters.action_type_id" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0; font-size: 13px;">
                    <option value="">All Types</option>
                    @foreach($actionTypes as $type)
                    <option value="{{ $type->id ?? $type }}">{{ is_object($type) ? $type->name : $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Assigned To</label>
                <select wire:model.live="filters.action_owner_id" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0; font-size: 13px;">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Due From</label>
                <input wire:model.live="filters.date_from" type="date" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0; font-size: 13px;">
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Due To</label>
                <input wire:model.live="filters.date_to" type="date" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0; font-size: 13px;">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button wire:click="clearFilters" class="modern-filter-btn" style="width: 100%;">
                    <i class="mdi mdi-filter-remove"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="modern-table-wrapper">
        <table class="modern-table">
            <thead>
                <tr>
                    <th wire:click="sortBy('capa_number')" class="sortable">
                        CAPA # 
                        @if($sortField === 'capa_number')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>NC #</th>
                    <th>Description</th>
                    <th>Assigned To</th>
                    <th wire:click="sortBy('due_date')" class="sortable">
                        Due Date 
                        @if($sortField === 'due_date')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>Priority</th>
                    <th wire:click="sortBy('status_name')" class="sortable">
                        Status 
                        @if($sortField === 'status_name')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($capas as $capa)
                <tr class="{{ $capa->isOverdue() ? 'table-danger' : '' }}">
                    <td>
                        <strong style="color: #202124; font-size: 14px;">{{ $capa->capa_number }}</strong>
                        <small class="d-block" style="color: #5f6368; font-size: 12px; margin-top: 4px;">
                            {{ $capa->action_type_name ?? ($capa->actionType?->name ?? 'Corrective') }}
                        </small>
                    </td>
                    <td>
                        <a href="{{ route('audit.nc.show', $capa->non_conformance_id) }}" style="color: #28a745; text-decoration: none; font-weight: 500;">
                            {{ $capa->nonConformance?->nc_number ?? 'N/A' }}
                        </a>
                    </td>
                    <td style="color: #5f6368;">{{ Str::limit($capa->description ?? $capa->title ?? 'N/A', 40) }}</td>
                    <td>{{ $capa->actionOwnerUser?->name ?? ($capa->action_owner ?? 'N/A') }}</td>
                    <td>
                        <span style="color: #202124;">{{ $capa->due_date?->format('M d, Y') ?? 'Not set' }}</span>
                        @if($capa->isOverdue())
                            @php
                                $daysOverdue = $capa->due_date ? now()->diffInDays($capa->due_date) : 0;
                            @endphp
                            <br><span class="modern-badge" style="background: #fee; color: #c33; margin-top: 4px; display: inline-block;">{{ $daysOverdue }} days overdue</span>
                        @elseif($capa->due_date)
                            @php
                                $daysRemaining = now()->diffInDays($capa->due_date, false);
                            @endphp
                            @if($daysRemaining <= 3 && $daysRemaining > 0)
                            <br><span class="modern-badge" style="background: #fff4e6; color: #d97706; margin-top: 4px; display: inline-block;">{{ $daysRemaining }} days left</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @php
                            $priorityName = $capa->priority_name ?? ($capa->priority?->name ?? 'N/A');
                            $priorityColor = $priorityName === 'Critical' ? '#fee' : ($priorityName === 'High' ? '#fff4e6' : ($priorityName === 'Medium' ? '#e3f2fd' : '#f5f5f5'));
                            $priorityTextColor = $priorityName === 'Critical' ? '#c33' : ($priorityName === 'High' ? '#d97706' : ($priorityName === 'Medium' ? '#1976d2' : '#5f6368'));
                        @endphp
                        <span class="modern-badge" style="background: {{ $priorityColor }}; color: {{ $priorityTextColor }};">
                            {{ $priorityName }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusName = $capa->status_name ?? ($capa->status?->name ?? 'N/A');
                            $statusColor = $statusName === 'Closed' ? '#e8f5e9' : ($statusName === 'Open' ? '#fff4e6' : ($statusName === 'In Progress' ? '#e3f2fd' : '#f5f5f5'));
                            $statusTextColor = $statusName === 'Closed' ? '#2e7d32' : ($statusName === 'Open' ? '#d97706' : ($statusName === 'In Progress' ? '#1976d2' : '#5f6368'));
                        @endphp
                        <span class="modern-badge" style="background: {{ $statusColor }}; color: {{ $statusTextColor }};">
                            {{ $statusName }}
                        </span>
                        @if($capa->hasVerification() && $capa->latestVerification && ($capa->latestVerification->effectiveness_result_name === 'Effective' || $capa->latestVerification->effectiveness_result === 'Effective'))
                        <i class="mdi mdi-check-decagram" style="color: #28a745; margin-left: 6px; font-size: 16px;" title="Verified Effective"></i>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('audit.capa.show', $capa->id) }}" class="modern-action-btn" title="View" style="color: #1976d2;">
                                <i class="mdi mdi-eye"></i>
                            </a>
                            <button wire:click="openStatusModal({{ $capa->id }})" class="modern-action-btn" title="Change Status" style="color: #d97706;">
                                <i class="mdi mdi-swap-horizontal"></i>
                            </button>
                            <button wire:click="confirmDelete({{ $capa->id }})" class="modern-action-btn" title="Delete" style="color: #c33;">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="modern-empty-state">
                        <i class="mdi mdi-checkbox-marked-circle-outline"></i>
                        <p>No corrective actions found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="modern-pagination">
        {{ $capas->links() }}
    </div>

    <!-- Delete Modal -->
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #e8eaed;">
                    <h5 class="modal-title" style="font-weight: 600;">Confirm Delete</h5>
                    <button type="button" class="close" wire:click="$set('showDeleteModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <p style="color: #5f6368;">Are you sure you want to delete this corrective action?</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e8eaed;">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)" style="border-radius: 8px;">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteCAPA" style="border-radius: 8px;">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Status Change Modal -->
    @if($showStatusModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #e8eaed;">
                    <h5 class="modal-title" style="font-weight: 600;">Change CAPA Status</h5>
                    <button type="button" class="close" wire:click="$set('showStatusModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <div class="form-group">
                        <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">New Status</label>
                        <select wire:model="newStatus" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Implemented">Implemented</option>
                            <option value="Verification Pending">Verification Pending</option>
                            <option value="Verified">Verified</option>
                            <option value="Closed">Closed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Implementation Notes</label>
                        <textarea wire:model="implementationNotes" class="form-control" rows="3" placeholder="Describe the implementation..." style="border-radius: 8px; border: 1px solid #dadce0;"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Status Change Notes</label>
                        <textarea wire:model="statusNotes" class="form-control" rows="2" style="border-radius: 8px; border: 1px solid #dadce0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e8eaed;">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showStatusModal', false)" style="border-radius: 8px;">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="changeStatus" style="border-radius: 8px;">Update Status</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>
