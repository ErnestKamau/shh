<div>
    <!-- Main Card -->
    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
        <div class="card-header" style="background: #f8f9fa; border: none; border-left: 6px solid #dc3545; padding: 18px 24px;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0" style="font-size: 1.35rem; font-weight: 600; color: #222;">
                    <i class="mdi mdi-alert-circle"></i> Non-Conformance Register
                </h4>
                <div>
                    <button wire:click="exportExcel" class="btn btn-sm btn-success me-2">
                        <i class="mdi mdi-download"></i> Export Excel
                    </button>
                    <a href="{{ route('audit.reports.index') }}" class="btn btn-sm btn-secondary">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Search and Basic Filters -->
            <div class="card mb-3 mx-3 mt-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="mdi mdi-magnify"></i> Search & Basic Filters</h5>
                        <button type="button" class="btn btn-outline-primary" wire:click="$toggle('showAdvancedFilters')">
                            <i class="mdi {{ $showAdvancedFilters ? 'mdi-filter-variant-minus' : 'mdi-filter-variant-plus' }}"></i> 
                            Advanced Filters
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Bar and Per Page -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="mdi mdi-magnify text-info"></i>
                                </span>
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-lg border-start-0" 
                                       placeholder="Search by NC number, title, or description...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label me-2 mb-0">Show:</label>
                                <select wire:model.live="perPage" class="form-select form-select-lg" id="perPage" style="width: auto;">
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            @if($showAdvancedFilters)
            <div class="card mb-3 mx-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="mdi mdi-filter-variant"></i> Advanced Filters</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" wire:model.live="startDate" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" wire:model.live="endDate" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Status</label>
                            <select wire:model.live="statusFilter" class="form-select form-select-lg">
                                <option value="">All</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Origin</label>
                            <select wire:model.live="originFilter" class="form-select form-select-lg">
                                <option value="">All</option>
                                @foreach($origins as $origin)
                                    <option value="{{ $origin }}">{{ $origin }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Risk Level</label>
                            <select wire:model.live="riskLevelFilter" class="form-select form-select-lg">
                                <option value="">All</option>
                                @foreach($riskLevels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button wire:click="clearFilters" class="btn btn-warning">
                                <i class="mdi mdi-refresh"></i> Clear All Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>NC #</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Origin</th>
                            <th>Risk</th>
                            <th>Status</th>
                            <th>Identified By</th>
                            <th>CAPAs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ncs as $nc)
                        <tr>
                            <td><strong>{{ $nc->nc_number }}</strong></td>
                            <td>{{ Str::limit($nc->title ?? $nc->description, 40) }}</td>
                            <td>{{ $nc->date_identified?->format('M d, Y') }}</td>
                            <td><span class="badge badge-secondary">{{ $nc->origin_name ?? 'N/A' }}</span></td>
                            <td>
                                @if($nc->riskLevel)
                                    <span class="badge" style="background-color: {{ $nc->riskLevel->color_code ?? '#6c757d' }}">
                                        {{ $nc->riskLevel->name }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = ['Identified' => 'danger', 'RCA In Progress' => 'warning',
                                        'CAPA Assigned' => 'info', 'Verification Pending' => 'primary', 'Closed' => 'success'];
                                    $badgeClass = $statusColors[$nc->status_name] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $badgeClass }}">{{ $nc->status_name }}</span>
                            </td>
                            <td>{{ $nc->identifiedByUser?->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $nc->correctiveActions->count() }}</td>
                            <td>
                                <a href="{{ route('audit.nc.show', $nc->id) }}" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="mdi mdi-information text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No non-conformances found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($ncs->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing {{ $ncs->firstItem() }} to {{ $ncs->lastItem() }} of {{ $ncs->total() }} results
                    </div>
                    <div>
                        {{ $ncs->links('livewire::bootstrap') }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
