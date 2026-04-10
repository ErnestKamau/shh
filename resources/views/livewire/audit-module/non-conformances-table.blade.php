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
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
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
            border-color: #dc3545;
            color: #dc3545;
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
            color: #dc3545;
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
            background: #ffe5e5 !important;
        }

        .modern-table tbody tr.overdue {
            background: #fce8e6 !important;
        }

        .modern-table tbody tr.overdue:nth-child(even) {
            background: #fce8e6 !important;
        }

        .modern-table tbody tr.overdue:hover {
            background: #f9d0cc !important;
        }

        .modern-table tbody tr:last-child {
            border-bottom: none;
        }

        .modern-table tbody td {
            padding: 16px 24px;
            font-size: 14px;
            color: #202124;
            vertical-align: middle;
        }

        .modern-table tbody td:first-child {
            font-weight: 500;
            color: #dc3545;
        }

        .modern-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.5;
        }

        .modern-badge-success {
            background: #e6f4ea;
            color: #137333;
        }

        .modern-badge-info {
            background: #e8f0fe;
            color: #1967d2;
        }

        .modern-badge-warning {
            background: #fef7e0;
            color: #ea8600;
        }

        .modern-badge-secondary {
            background: #f1f3f4;
            color: #5f6368;
        }

        .modern-badge-danger {
            background: #fce8e6;
            color: #c5221f;
        }

        .modern-badge-dark {
            background: #3c4043;
            color: #ffffff;
        }

        .modern-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid #dadce0;
            background: #ffffff;
            color: #5f6368;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin: 0 2px;
            padding: 0;
        }

        .modern-action-btn:hover {
            background: #f1f3f4;
            border-color: #dc3545;
            color: #dc3545;
            transform: scale(1.05);
        }

        .modern-action-btn.info:hover {
            background: #e8f0fe;
            border-color: #1967d2;
            color: #1967d2;
        }

        .modern-action-btn.success:hover {
            background: #e6f4ea;
            border-color: #137333;
            color: #137333;
        }

        .modern-action-btn.warning:hover {
            background: #fef7e0;
            border-color: #ea8600;
            color: #ea8600;
        }

        .modern-action-btn.danger:hover {
            background: #fce8e6;
            border-color: #c5221f;
            color: #c5221f;
        }

        .modern-empty-state {
            padding: 64px 24px;
            text-align: center;
        }

        .modern-empty-state-icon {
            font-size: 64px;
            color: #dadce0;
            margin-bottom: 16px;
        }

        .modern-empty-state-text {
            font-size: 16px;
            color: #5f6368;
            margin: 0;
        }

        .modern-pagination {
            padding: 16px 24px;
            border-top: 1px solid #e8eaed;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modern-pagination-info {
            font-size: 13px;
            color: #5f6368;
        }

        .modern-select {
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 6px 32px 6px 12px;
            font-size: 13px;
            background: #ffffff;
            color: #202124;
            transition: all 0.2s ease;
        }

        .modern-select:focus {
            outline: none;
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .modern-modal {
            border-radius: 8px;
            border: none;
            box-shadow: 0 8px 10px 1px rgba(0,0,0,0.14), 0 3px 14px 2px rgba(0,0,0,0.12);
        }

        .modern-modal-header {
            border-bottom: 1px solid #e8eaed;
            padding: 20px 24px;
        }

        .modern-modal-body {
            padding: 24px;
        }

        .modern-modal-footer {
            border-top: 1px solid #e8eaed;
            padding: 16px 24px;
        }

        .modern-btn {
            border-radius: 4px;
            font-weight: 500;
            padding: 8px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .modern-btn:hover {
            box-shadow: 0 1px 2px 0 rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15);
        }
    </style>

    <!-- Search and Filters -->
    <div class="modern-search-bar">
        <div class="row align-items-center">
            <div class="col-md-6 position-relative">
                <i class="mdi mdi-magnify modern-search-icon"></i>
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       class="modern-search-input" 
                       placeholder="Search by NC number, description, ISO clause...">
            </div>
            <div class="col-md-3">
                <select wire:model.live="perPage" class="modern-select form-control">
                    <option value="15">15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
            <div class="col-md-3">
                <button wire:click="$toggle('showFilters')" class="modern-filter-btn btn-block">
                    <i class="mdi mdi-filter-variant"></i> {{ $showFilters ? 'Hide' : 'Show' }} Filters
                </button>
            </div>
        </div>

        @if($showFilters)
        <div class="modern-filters-bar mt-3">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Origin</label>
                    <select wire:model.live="filters.origin_id" class="modern-select form-control">
                        <option value="">All Origins</option>
                        @foreach($origins as $origin)
                        <option value="{{ $origin->id }}">{{ $origin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">Risk Level</label>
                    <select wire:model.live="filters.risk_level_id" class="modern-select form-control">
                        <option value="">All Risk Levels</option>
                        @foreach($riskLevels as $level)
                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">From Date</label>
                    <input wire:model.live="filters.date_from" type="date" class="modern-select form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 500; color: #5f6368; margin-bottom: 6px;">To Date</label>
                    <input wire:model.live="filters.date_to" type="date" class="modern-select form-control">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button wire:click="clearFilters" class="modern-filter-btn btn-block">
                        <i class="mdi mdi-filter-remove"></i> Clear
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Modern Table -->
    <div class="modern-table-wrapper">
        <table class="modern-table">
            <thead>
                <tr>
                    <th wire:click="sortBy('nc_number')" class="sortable">
                        NC #
                        @if($sortField === 'nc_number')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>Description</th>
                    <th>Origin</th>
                    <th wire:click="sortBy('date_identified')" class="sortable">
                        Identified Date
                        @if($sortField === 'date_identified')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>Risk Level</th>
                    <th wire:click="sortBy('status_name')" class="sortable">
                        Status
                        @if($sortField === 'status_name')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th>CAPAs</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ncs as $nc)
                <tr class="{{ $nc->isOverdue() ? 'overdue' : '' }}">
                    <td>
                        <a href="{{ route('audit.nc.show', $nc->id) }}" style="color: #dc3545; text-decoration: none; font-weight: 500;">
                            {{ $nc->nc_number }}
                        </a>
                        @if($nc->isOverdue())
                        <span class="modern-badge modern-badge-danger ml-2">Overdue</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($nc->description ?? $nc->title ?? 'N/A', 50) }}</td>
                    <td>{{ $nc->origin_name ?? ($nc->origin?->name ?? 'N/A') }}</td>
                    <td>{{ $nc->date_identified?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>
                        @if($nc->riskLevel)
                        <span class="modern-badge" style="background-color: {{ $nc->riskLevel->color ?? '#6c757d' }}; color: white;">
                            {{ $nc->riskLevel->name }}
                        </span>
                        @else
                        <span class="modern-badge modern-badge-secondary">Not Assessed</span>
                        @endif
                    </td>
                    <td>
                        <span class="modern-badge modern-badge-{{ $nc->status_name === 'Closed' ? 'success' : ($nc->status_name === 'Identified' ? 'warning' : 'info') }}">
                            {{ $nc->status_name ?? ($nc->status?->name ?? 'N/A') }}
                        </span>
                    </td>
                    <td>
                        <span class="modern-badge modern-badge-dark">{{ $nc->correctiveActions->count() }}</span>
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('audit.nc.show', $nc->id) }}" class="modern-action-btn info" title="View">
                            <i class="mdi mdi-eye" style="font-size: 16px;"></i>
                        </a>
                        <a href="{{ route('audit.capa.create', ['nc_id' => $nc->id]) }}" class="modern-action-btn success" title="Add CAPA">
                            <i class="mdi mdi-plus" style="font-size: 16px;"></i>
                        </a>
                        <button wire:click="openStatusModal({{ $nc->id }})" class="modern-action-btn warning" title="Change Status">
                            <i class="mdi mdi-swap-horizontal" style="font-size: 16px;"></i>
                        </button>
                        <button wire:click="confirmDelete({{ $nc->id }})" class="modern-action-btn danger" title="Delete">
                            <i class="mdi mdi-delete" style="font-size: 16px;"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="modern-empty-state">
                        <i class="mdi mdi-alert-octagon-outline modern-empty-state-icon"></i>
                        <p class="modern-empty-state-text">No non-conformances found matching your criteria.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($ncs->hasPages())
    <div class="modern-pagination">
        <div class="modern-pagination-info">
            Showing {{ $ncs->firstItem() ?? 0 }} to {{ $ncs->lastItem() ?? 0 }} of {{ $ncs->total() }} results
        </div>
        <div>
            {{ $ncs->links() }}
        </div>
    </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" style="font-weight: 500; font-size: 20px;">Confirm Delete</h5>
                    <button type="button" class="close" wire:click="$set('showDeleteModal', false)" style="opacity: 0.6;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <p style="color: #5f6368; margin: 0;">Are you sure you want to delete this non-conformance? This will also delete associated corrective actions. This action cannot be undone.</p>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn btn-secondary modern-btn" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger modern-btn" wire:click="deleteNC">Delete</button>
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
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" style="font-weight: 500; font-size: 20px;">Change NC Status</h5>
                    <button type="button" class="close" wire:click="$set('showStatusModal', false)" style="opacity: 0.6;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="form-group">
                        <label style="font-size: 13px; font-weight: 500; color: #5f6368; margin-bottom: 8px;">New Status</label>
                        <select wire:model="newStatus" class="modern-select form-control">
                            <option value="Identified">Identified</option>
                            <option value="RCA In Progress">RCA In Progress</option>
                            <option value="CAPA Assigned">CAPA Assigned</option>
                            <option value="Verification Pending">Verification Pending</option>
                            <option value="Closed">Closed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size: 13px; font-weight: 500; color: #5f6368; margin-bottom: 8px;">Notes (Optional)</label>
                        <textarea wire:model="statusNotes" class="form-control" rows="3" style="border: 1px solid #dadce0; border-radius: 4px; padding: 8px 12px; font-size: 14px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn btn-secondary modern-btn" wire:click="$set('showStatusModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary modern-btn" wire:click="changeStatus">Update Status</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>
